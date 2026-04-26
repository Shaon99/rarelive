<?php

namespace App\Http\Controllers\Admin;

use App\Constants\CommonConstant;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateExpenseReportCsvJob;
use App\Jobs\GenerateSalesReportCsvJob;
use App\Models\Admin;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\GeneralSetting;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\PurchaseReturn;
use App\Models\Purchases;
use App\Models\SalaryPayment;
use App\Models\Sales;
use App\Models\SalesProduct;
use App\Models\Transaction;
use App\Transformers\ExpenseReportTransformer;
use App\Transformers\SalesReportTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

class ReportController extends Controller
{
    protected $general;

    public function __construct()
    {
        $this->general = GeneralSetting::select('id', 'opening_balance', 'site_currency')->first();
    }

    public function salesReport(Request $request)
    {
        // Initialize variables
        $pageTitle = 'Sales Report';
        $navReportActiveClass = 'active';
        $subNavSalesReportActiveClass = 'active';

        $perPage = $request->input('per_page', 25);

        // Base query
        $query = SalesProduct::with(['product:id,name', 'sale.paymentMethod']);

        // Apply filters using the separate method
        $query = $this->applyFilters($query, $request);

        $salesData = $query->get();

        // Fetch data
        $salesReports = $query->latest()->paginate($perPage);

        // Get necessary data for dropdowns
        $products = Product::pluck('name', 'id');
        $adminUsers = Admin::pluck('name', 'id');
        $paymentMethods = PaymentMethod::select('name', 'id')->get();

        // Read JSON file from public/assets directory
        $json = file_get_contents(public_path('assets/address.json'));
        // Decode JSON to an array
        $city = json_decode($json, true);
        $districts = collect($city['district'])->map(function ($district) {
            return [
                'name' => $district['name'],
                'bn_name' => $district['bn_name'] ?? null,
            ];
        })->all();

        // Check for AJAX request
        if ($request->ajax()) {
            return response()->json([
                'salesReports' => view('backend.report.sales_report_table', compact('salesReports'))->render(),
            ]);
        }

        // Return the main view
        return view('backend.report.sales_report', compact(
            'pageTitle',
            'navReportActiveClass',
            'subNavSalesReportActiveClass',
            'salesReports',
            'products',
            'adminUsers',
            'paymentMethods',
            'districts',
        ));
    }

    public function downloadCsv(Request $request)
    {
        $filters = $request->only([
            'products',
            'created_by',
            's_status',
            'courier_status',
            'platform',
            'payment_by',
            'payment_status',
            'date_range',
            'search',
        ]);

        $fileName = 'sales_report_' . now()->format('Ymd_His') . '.csv';

        // If queue is enabled, dispatch the job as batch (optional fallback)
        if (config('queue.enabled', false)) {
            $batch = Bus::batch([
                new GenerateSalesReportCsvJob($filters, $fileName, new SalesReportTransformer()),
            ])->dispatch();

            return redirect()->back()->with('message', 'CSV is being generated. You will be notified once it is ready.');
        }

        // Otherwise, return streamed CSV directly
        $transformer = new SalesReportTransformer();

        $callback = function () use ($filters, $transformer) {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, $transformer->getHeaders());

            // Apply filters (basic example; adjust for actual filter logic)
            $query = SalesProduct::with(['sale.user', 'sale.customer', 'product'])
                ->whereHas('sale', function ($q) use ($filters) {
                    if (! empty($filters['created_by'])) {
                        $q->where('created_by', $filters['created_by']);
                    }

                    // Add other filters similarly
                });

            $query->chunk(1000, function ($products) use ($handle, $transformer) {
                foreach ($products as $product) {
                    fputcsv($handle, $transformer->transform($product));
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }

    public function applyFilters($query, $request)
    {
        // Apply filters on SalesProduct
        if ($request->filled('products')) {
            $query->whereIn('product_id', $request->products);
        }

        // Apply related filters on `sale`
        $query->whereHas('sale', function ($saleQuery) use ($request) {

            if ($request->filled('created_by')) {
                $saleQuery->whereIn('user_id', $request->created_by);
            }

            if ($request->filled('s_status')) {
                $saleQuery->whereIn('system_status', $request->s_status);
            }

            if ($request->filled('courier_status')) {
                $saleQuery->whereIn('courier_status', $request->courier_status);
            }

            if ($request->filled('platform')) {
                $saleQuery->whereIn('platform', $request->platform);
            }

            if ($request->filled('payment_by')) {
                $saleQuery->whereIn('payment_method', $request->payment_by);
            }

            if ($request->filled('payment_status')) {
                $saleQuery->whereIn('payment_status', $request->payment_status);
            }

            if ($request->filled('date_range')) {
                [$startDate, $endDate] = explode(' - ', $request->date_range);
                $saleQuery->whereBetween('created_at', [Carbon::parse($startDate), Carbon::parse($endDate)]);
            }

            if ($request->filled('location')) {
                $saleQuery->whereHas('customer.addressBooks', function ($addressQuery) use ($request) {
                    $locations = $request->location;
                    $addressQuery->where(function ($query) use ($locations) {
                        foreach ((array) $locations as $location) {
                            $query->orWhere('city', 'like', "%$location%");
                        }
                    });
                });
            }

            // Apply search filter on customer info, invoice, consignment id, or tracking id
            if ($request->filled('search')) {
                $search = $request->search;

                $saleQuery->where(function ($query) use ($search) {
                    $query->whereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', "%$search%")
                            ->orWhere('phone', 'like', "%$search%");
                    })
                        ->orWhere('invoice_no', 'like', "%$search%")
                        ->orWhere('consignment_id', 'like', "%$search%")
                        ->orWhere('tracking_code', 'like', "%$search%");
                });
            }
        });

        return $query;
    }

    public function purchasesReport(Request $request)
    {
        $pageTitle = 'Purchases Report';
        $navReportActiveClass = 'active';
        $subNavPurchasesReportActiveClass = 'active';
        $perPage = $request->input('per_page', 25);

        // Base query
        $query = Purchases::with(['supplier' => function ($query) {
            return $query->select('id', 'name');
        }]);

        // Apply filters using the separate method
        $query = $this->ajaxFilter($query, $request);

        // Fetch paginated data
        $purchases = $query->latest()->paginate($perPage);

        // Check for AJAX request
        if ($request->ajax()) {
            return response()->json([
                'purchases' => view('backend.report.purchases_report_table', compact('purchases'))->render(),
            ]);
        }

        return view('backend.report.purchases_report', compact(
            'pageTitle',
            'navReportActiveClass',
            'subNavPurchasesReportActiveClass',
            'purchases',
        ));
    }

    public function ajaxFilter($query, $request)
    {
        if ($request->filled('payment_status')) {
            $query->whereIn('payment_status', $request->payment_status);
        }

        if ($request->filled('date_range')) {
            [$startDate, $endDate] = explode(' - ', $request->date_range);
            $query->whereBetween('created_at', [Carbon::parse($startDate), Carbon::parse($endDate)]);
        }

        // Apply search filter on customer info, invoice, consignment id, or tracking id
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($query) use ($search) {
                $query->whereHas('supplier', function ($supplierQuery) use ($search) {
                    $supplierQuery->where('name', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%");
                })
                    ->orWhere('invoice_no', 'like', "%$search%");
            });
        }

        return $query;
    }

    // public function dailyReport()
    // {
    //     // Define start and end of the day
    //     $startDate = Carbon::today()->startOfDay();
    //     $endDate   = Carbon::today()->endOfDay();

    //     if (! $startDate || ! $endDate) {
    //         return response()->json(['error' => 'Invalid date range'], 400);
    //     }

    //     // Convert end date to end of the day
    //     $endDate     = Carbon::parse($endDate)->endOfDay()->toDateTimeString();
    //     $openingCash = $this->general->opening_balance ?? 0;
    //     $currency    = $this->general->site_currency ?? '';

    //     // Calculate Opening Cash Balance (Before Start Date)
    //     $openingOutflow = SupplierDuePayment::where('created_at', '<', $startDate)->sum('amount') +
    //         Expense::where('created_at', '<', $startDate)->sum('amount') +
    //         SalaryPayment::where('created_at', '<', $startDate)->sum('amount_paid');

    //     $openingCash += $openingInflow - $openingOutflow;

    //     // Calculate Today's Outflows (Purchases + Expenses)
    //     $todayPurchases = SupplierDuePayment::whereBetween('created_at', [$startDate, $endDate])->sum('amount');
    //     $todayExpenses  = Expense::whereBetween('created_at', [$startDate, $endDate])->sum('amount') +
    //         SalaryPayment::whereBetween('created_at', [$startDate, $endDate])->sum('amount_paid');

    //     $todayOutflow = $todayPurchases + $todayExpenses;

    //     // Calculate Today's Closing & Cash in Hand Balance
    //     $todayClosing = $todaySalesInflow - $todayOutflow;
    //     $cashInHand   = $openingCash + $todaySalesInflow - $todayOutflow;

    //     $addFund = Transaction::where('transaction_type', 'add_fund')->sum('amount');

    //     $refund = PurchaseReturn::whereBetween('created_at', [$startDate, $endDate])->sum('total_amount');

    //     return response()->json([
    //         'previous_balance' => number_format($openingCash + $addFund + $refund, 2) . ' ' . $currency,
    //         'today_sales'      => number_format($todaySalesInflow, 2) . ' ' . $currency,
    //         'today_expense'    => number_format($todayOutflow, 2) . ' ' . $currency,
    //         'today_balance'    => number_format($todayClosing, 2) . ' ' . $currency,
    //         'balance'          => number_format($cashInHand + $addFund + $refund, 2) . ' ' . $currency,
    //     ]);
    // }

    public function expenseReport(Request $request)
    {
        // Initialize variables
        $pageTitle = 'Expense Report';
        $navReportActiveClass = 'active';
        $subNavExpenseReportActiveClass = 'active';

        $perPage = $request->input('per_page', 25);

        // Base query
        $query = Expense::with('expenseCategory', 'paymentAccount:id,name,account_number');

        // Apply filters using the separate method
        $query = $this->applyExpenseFilters($query, $request);

        // Fetch data
        $expenseReports = $query->latest()->paginate($perPage);

        // Get necessary data for dropdowns
        $expenseCategory = ExpenseCategory::pluck('name', 'id');

        // Check for AJAX request
        if ($request->ajax()) {
            return response()->json([
                'expenseReports' => view('backend.report.expense_report_table', compact('expenseReports'))->render(),
            ]);
        }

        // Return the main view
        return view('backend.report.expense_report', compact(
            'pageTitle',
            'navReportActiveClass',
            'subNavExpenseReportActiveClass',
            'expenseReports',
            'expenseCategory',
        ));
    }

    public function applyExpenseFilters($query, $request)
    {
        // Apply filters on SalesProduct
        if ($request->filled('category')) {
            $query->whereIn('expense_category_id', $request->category);
        }

        // Apply related filters on `sale`
        if ($request->filled('date_range')) {
            [$startDate, $endDate] = explode(' - ', $request->date_range);
            $query->whereBetween('created_at', [Carbon::parse($startDate), Carbon::parse($endDate)]);
        }

        // Apply search filter on customer info, invoice, consignment id, or tracking id
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($query) use ($search) {
                $query->where('amount', 'like', "%$search%")
                    ->orWhereHas('expenseCategory', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', "%$search%");
                    });
            });
        }

        return $query;
    }

    public function downloadExpenseCsv(Request $request)
    {
        // Apply filters from the request
        $filters = $request->only([
            'category',
            'date_range',
        ]);

        $fileName = 'expense_report_' . Carbon::now()->timestamp . '.csv';

        // Dispatch the job as part of a batch
        $batch = Bus::batch([
            new GenerateExpenseReportCsvJob($filters, $fileName, new ExpenseReportTransformer()),
        ])->dispatch();

        // Return a response indicating that the CSV is being generated
        return response()->json([
            'message' => 'Your CSV is being generated. You will be notified once it is ready.',
            'status' => 'processing',
            'batch_id' => $batch->id,                                                              // Send batch ID to track progress
            'file_path' => asset('storage/' . $fileName),                                           // Public URL to access the file
            'file_name' => $fileName,
        ]);
    }

    public function manageAccount(Request $request)
    {
        // Data initialization
        $data = [
            'pageTitle' => 'Manage Account',
            'accountsActiveClass' => 'active',
            'manageAccountActiveClass' => 'active',
            // Exclude the STEADFAST account from the accounts list
            'accounts' => PaymentMethod::where('type', '!=', CommonConstant::STEADFAST)->get(),
        ];

        // Determine the type of transaction to fetch
        $type = $request->get('type', 'transfer');  // Default to 'transfer'

        // Fetch transactions based on the type
        $fund_transactions = Transaction::with('toAccount')
            ->where('transaction_type', 'add_fund')
            ->paginate(10);

        $withdraw_transactions = Transaction::with('toAccount')
            ->where('transaction_type', 'withdraw_fund')
            ->paginate(10);

        $transactions = Transaction::with('fromAccount', 'toAccount')
            ->where('transaction_type', 'transfer')
            ->paginate(10);

        // Handle AJAX request
        if ($request->ajax() && $type) {
            // Return the appropriate partial views based on the type
            if ($type === 'add_fund') {
                return view('backend.accounts.add_fund_transactions_table', compact('fund_transactions'))->render();
            }

            if ($type === 'withdraw_fund') {
                return view('backend.accounts.withdraw_fund_transactions_table', compact('withdraw_transactions'))->render();
            }

            // Default to transaction table
            return view('backend.accounts.transactions_table', compact('transactions'))->render();
        }

        // For non-AJAX requests, pass the data to the main view
        $data['transactions'] = $transactions;
        $data['fund_transactions'] = $fund_transactions;
        $data['withdraw_transactions'] = $withdraw_transactions;
        $data['type'] = $type;

        return view('backend.accounts.manage', $data);
    }

    public function stockReport(Request $request)
    {
        $pageTitle = 'Stock Report';
        $navReportActiveClass = 'active';
        $subNavStockReportActiveClass = 'active';

        $productId = $request->product_id;

        $query = Product::select('id', 'name', 'average_unit_price', 'purchase_price')
            ->withSum('purchaseItems as purchased', 'quantity')
            ->withSum('salesProducts as sold', 'quantity')
            ->withSum('salesProducts as sales_returned', 'returned_quantity')
            ->with('stockTransactions');

        if ($productId) {
            $query->where('id', $productId);
        }

        $products = $query->paginate(25);

        if ($request->ajax()) {
            return response()->json([
                'table' => view('backend.report.stock_report_table', compact('products'))->render(),
            ]);
        }

        return view('backend.report.stock_report', compact(
            'products',
            'pageTitle',
            'navReportActiveClass',
            'subNavStockReportActiveClass',
            'productId'
        ));
    }

    public function generalLedger(Request $request)
    {
        $pageTitle = 'General Ledger';
        $accountsActiveClass = 'active';
        $generalLedgerActiveClass = 'active';

        // Keep server-side default aligned with UI (today - today) to avoid full-table load.
        $startDate = $request->input('start_date') ?: Carbon::today()->toDateString();
        $endDate = $request->input('end_date') ?: Carbon::today()->toDateString();
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Compute opening balance in SQL (fast), instead of loading all prior rows into PHP.
        $openingInflow = Transaction::query()
            ->where('created_at', '<', $start)
            ->whereIn('transaction_type', ['add_fund', 'payment_received'])
            ->sum('amount');

        $openingOutflow = Transaction::query()
            ->where('created_at', '<', $start)
            ->whereIn('transaction_type', ['purchases_payment_paid', 'salary_paid', 'expense_on_payment'])
            ->sum('amount');

        $openingBalance = (float) $openingInflow - (float) $openingOutflow;

        $transactions = Transaction::query()
            ->with(['paymentMethod:id,name'])
            ->select(['id', 'created_at', 'transaction_type', 'note', 'amount', 'payment_method_id'])
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at')
            ->get();

        $runningBalance = $openingBalance;

        $transactions->transform(function ($trx) use (&$runningBalance) {
            $debit = 0;
            $credit = 0;
            $amount = (float) $trx->amount;

            switch ($trx->transaction_type) {
                case 'add_fund':
                case 'payment_received':
                    $debit = $amount;
                    $runningBalance += $amount;
                    break;
                case 'courier_shipping_charge':
                    $debit = $amount;
                    break;
                case 'courier_cod_charge':
                    $debit = $amount;
                    break;
                case 'withdraw_fund':
                    $credit = $amount;
                    $runningBalance -= $amount;
                    break;
                case 'purchases_payment_paid':
                case 'salary_paid':
                    $credit = $amount;
                    $runningBalance -= $amount;
                    break;

                case 'expense_on_payment':
                    $debit = $amount;
                    $runningBalance -= $amount;
                    break;

                case 'purchases_on_due':
                case 'sales_on_credit':
                case 'salary_generated':
                    if ($trx->transaction_type === 'purchases_on_due' || $trx->transaction_type === 'salary_generated') {
                        $debit = $amount;
                    } elseif ($trx->transaction_type === 'sales_on_credit') {
                        $credit = $amount;
                    }
                    break;
            }

            // Normalize negative values so Dr/Cr columns always contain positive numbers
            // on the correct side.
            if ($debit < 0) {
                $credit += abs($debit);
                $debit = 0;
            }

            if ($credit < 0) {
                $debit += abs($credit);
                $credit = 0;
            }

            $trx->debit = $debit;
            $trx->credit = $credit;
            $trx->running_balance = $runningBalance;

            return $trx;
        });

        $totalDebit = $transactions->sum('debit');
        $totalCredit = $transactions->sum('credit');
        $closingBalance = $runningBalance;

        if ($request->ajax()) {
            return view('backend.accounts.general-ledger-table', compact(
                'transactions',
                'openingBalance',
                'totalDebit',
                'totalCredit',
                'closingBalance',
                'startDate',
                'endDate'
            ))->render();
        }

        return view('backend.accounts.general_ledger', compact(
            'pageTitle',
            'transactions',
            'openingBalance',
            'totalDebit',
            'totalCredit',
            'closingBalance',
            'startDate',
            'endDate',
            'accountsActiveClass',
            'generalLedgerActiveClass'
        ));
    }

    public function profitLossReport(Request $request)
    {
         if ($request->ajax() && $request->has(['start_date', 'end_date'])) {
            // Normalize and parse date range
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();

            // Sales completed within date range
            $sales = Sales::where('system_status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate]);

            $salesTotal = $sales->sum('grand_total');
            $courierChargeTotal = $sales->sum('shipping_cost');
            $courierCodChargeTotal = $sales->sum('cod_charge');
            $totalCourierCost = $courierChargeTotal + $courierCodChargeTotal;

            // Purchases and returns
            $purchaseTotal = Purchases::whereBetween('created_at', [$startDate, $endDate])->sum('grand_total');
            $purchaseReturn = PurchaseReturn::whereBetween('created_at', [$startDate, $endDate])->sum('total_amount');
            $purchaseCOGS = $purchaseTotal - $purchaseReturn;

            // Gross profit = Net Sales - COGS
            $grossProfit = ($salesTotal - $totalCourierCost) - $purchaseCOGS;

            // Expenses
            $generalExpenses = Expense::whereBetween('created_at', [$startDate, $endDate])->sum('amount');

            // Salary expenses
            $salaryExpenses = SalaryPayment::whereBetween('created_at', [$startDate, $endDate])->sum('amount_paid');

            $totalExpenses = $generalExpenses + $salaryExpenses;

            // Net profit = Gross profit - total expenses
            $netProfit = $grossProfit - $totalExpenses;

            $data = [
                'salesTotal' => $salesTotal,
                'totalCourierCost' => $totalCourierCost,
                'purchaseCOGS' => $purchaseCOGS,
                'generalExpenses' => $generalExpenses,
                'salaryExpenses' => $salaryExpenses,
                'grossProfit' => $grossProfit,
                'netProfit' => $netProfit,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ];

            return response()->json([
                'html' => view('backend.report.profit_loss_report_table', $data)->render(),
            ]);
        }

        // Initial page load
        return view('backend.report.profit_loss_report', [
            'pageTitle' => 'Profit/Loss Report',
            'navReportActiveClass' => 'active',
            'subNavProfitReportActiveClass' => 'active',
        ]);
    }

    public function printMultiple(Request $request)
    {
        $ids = $request->query('ids');
        if (! $ids) {
            return redirect()->back()->withErrors(['No invoice IDs provided.']);
        }

        $idsArray = array_filter(explode(',', $ids), function ($id) {
            return is_numeric($id);
        });

        if (empty($idsArray)) {
            return redirect()->back()->withErrors(['Invalid invoice IDs provided.']);
        }

        $invoices = Sales::with('customer', 'warehouse', 'salesProduct')->whereIn('id', $idsArray)->get();

        if ($invoices->isEmpty()) {
            return redirect()->back()->withErrors(['No invoices found for the provided IDs.']);
        }

        $pageTitle = 'Print Invoices';

        $hideNavSidebar = 'd-none';

        return view('backend.sales.invoice_print-multiple', compact('pageTitle', 'invoices', 'hideNavSidebar'));
    }
}
