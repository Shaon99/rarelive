<?php

namespace App\Http\Controllers\Admin;

use App\Constants\CommonConstant;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\GeneralSetting;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sales;
use App\Models\Transaction;
use App\Models\Warehouse;
use App\Services\CarrybeeCourierService;
use App\Services\CarrybeeIntegrationService;
use App\Services\CarrybeeWebhookService;
use App\Services\RateLimiterService;
use App\Services\SaleService;
use App\Services\SteadfastCourierService;
use App\Services\StockService;
use App\Services\WebhookService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use SteadFast\SteadFastCourierLaravelPackage\Facades\SteadfastCourier;

class SalesController extends Controller
{
    protected $general;

    protected $stockService;

    protected $webhookService;

    protected $courierService;

    protected $rateLimiterService;

    protected CarrybeeCourierService $carrybeeCourierService;

    protected CarrybeeWebhookService $carrybeeWebhookService;

    public function __construct(
        StockService $stockService,
        SteadfastCourierService $courierService,
        WebhookService $webhookService,
        RateLimiterService $rateLimiterService,
        CarrybeeCourierService $carrybeeCourierService,
        CarrybeeWebhookService $carrybeeWebhookService
    ) {
        $this->general = GeneralSetting::select('id', 'opening_balance')->first();
        $this->stockService = $stockService;
        $this->courierService = $courierService;
        $this->webhookService = $webhookService;
        $this->rateLimiterService = $rateLimiterService;
        $this->carrybeeCourierService = $carrybeeCourierService;
        $this->carrybeeWebhookService = $carrybeeWebhookService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data['pageTitle'] = 'Order List';

        if ($request->query('system_status') === 'draft') {
            $data['draftActive'] = 'active';
        } else {
            $data['salesNav'] = 'active';
        }

        return view('backend.sales.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['pageTitle'] = 'Create Order';
        $data['salesNav'] = 'active';
        $data['sidebar_gone'] = 'sidebar-mini';
        $data['warehouses'] = Warehouse::all();

        $data['employee'] = Employee::select('id', 'employee_name')->get();

        $json = file_get_contents(public_path('assets/address.json'));
        $city = json_decode($json, true);
        $data['districts'] = collect($city['district'])->map(function ($district) {
            return [
                'name' => $district['name'],
                'bn_name' => $district['bn_name'] ?? null,
            ];
        })->all();

        $data['payment_account'] = PaymentMethod::select('id', 'type', 'name', 'account_number')
            ->whereNotIn('type', ['STEADFAST', 'CARRYBEE'])
            ->get()
            ->values();
        $data['carrybee_accounts'] = CarrybeeIntegrationService::accountsForDisplay();

        return view('backend.sales.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, SaleService $saleService)
    {
        $request->validate([
            'product_id' => 'required',
            'customer_phone' => 'required',
        ], [
            'product_id.required' => 'Please add some product first',
            'customer_phone.required' => 'Customer phone number is required',
        ]);

        try {
            $response = $saleService->storeSale($request);

            // If $response is a JsonResponse, handle it accordingly
            if ($response instanceof \Illuminate\Http\JsonResponse) {
                // Assume the service is already returning an appropriate response
                return $response;
            }

            // At this point, $response is assumed to be an array
            if (is_array($response) && isset($response['error'])) {
                return response()->json([
                    'message' => $response['error'],
                ], 422);
            }

            return response()->json([
                'message' => 'Order has been created',
                'sales' => $response,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Something went wrong. Please try again later.',
            ], 500);
        }
    }

    public function recentSales()
    {
        $authUser = auth()->guard('admin')->user();

        // Checking the role properly
        if ($authUser->hasRole('admin')) {
            // Admin sees all recent sales
            $query = Sales::with('customer:id,name')->latest();
        } else {
            // Non-admin user sees only their own recent sales
            $query = Sales::with('customer:id,name')
                ->where('user_id', $authUser->id)
                ->limit(10)
                ->latest();
        }

        // Get the recent sales based on the query
        $recentSales = $query->get();

        // Render the view and return as a JSON response
        $view = view('backend.recent.table', compact('recentSales'))->render();

        return response()->json([
            'view' => $view,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data['salesNav'] = 'active';
        $data['singleSale'] = Sales::with('customer', 'warehouse', 'salesProduct.product', 'paymentMethod', 'transactions.paymentMethod')->findOrFail($id);
        $data['pageTitle'] = 'Sales For - ' . @$data['singleSale']->customer->name;

        return view('backend.sales.view')->with($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['sidebar_gone'] = 'sidebar-mini';
        $data['salesNav'] = 'active';
        $data['warehouses'] = Warehouse::all();
        $data['order'] = Sales::with('customer', 'warehouse', 'salesProduct')->findOrFail($id);
        $data['pageTitle'] = 'Order Draft';
        $data['employee'] = Employee::select('id', 'employee_name')->get();

        $json = file_get_contents(public_path('assets/address.json'));
        $city = json_decode($json, true);
        $data['districts'] = collect($city['district'])->map(function ($district) {
            return [
                'name' => $district['name'],
                'bn_name' => $district['bn_name'] ?? null,
            ];
        })->all();

        $data['payment_account'] = PaymentMethod::select('id', 'type', 'name', 'account_number')
            ->whereNotIn('type', ['STEADFAST', 'CARRYBEE'])
            ->get()
            ->values();

        return view('backend.sales.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id, SaleService $saleService)
    {
        try {
            $request->validate([
                'product_id' => 'required',
                'customer_phone' => 'required',
            ], [
                'product_id.required' => 'Please add some product first',
                'customer_phone.required' => 'Customer phone number is required',
            ]);

            $saleService->updateSale($request, $id);

            return response()->json([
                'message' => 'Order has been created',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating sale: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'sale_id' => $id,
            ]);

            return response()->json([
                'message' => 'Something went wrong. Please try again later.',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Sales::findOrFail($id)->delete();

        return back()->with('success', 'Sales Deleted successfully');
    }

    public function salesRestore($id)
    {
        Sales::withTrashed()->find($id)->restore();

        return back()->with('success', 'Sales Successfully Restore');
    }

    public function salesDelete($id)
    {
        Sales::onlyTrashed()->findOrFail($id)->forceDelete();

        return back()->with('success', 'Sales Deleted successfully');
    }

    public function deleteBulk(Request $request)
    {
        $sales = $request->input('sales');

        if (empty($sales)) {
            return redirect()->back()->with('error', 'No item selected.');
        }

        $salesArray = is_string($sales) ? explode(',', $sales) : (array) $sales;

        // Delete related sale_product_returns first
        DB::table('sale_product_returns')->whereIn('sale_id', $salesArray)->delete();

        // Then delete sales
        Sales::whereIn('id', $salesArray)->delete();

        return redirect()->back()->with('success', 'Selected items deleted successfully!');
    }

    // category-response
    public function categoryProduct($id)
    {
        $products = Product::select('id', 'name', 'sale_price', 'image')->where('category_id', $id)->paginate(12);

        return view('backend.sales.category_product_response', compact('products'));
    }

    public function allProduct()
    {
        $products = Product::select('id', 'name', 'sale_price', 'image')->paginate(12);

        return view('backend.sales.category_product_response', compact('products'));
    }

    public function invoice($id)
    {
        $data['salesNav'] = 'active';
        $data['singleSale'] = Sales::with('customer', 'warehouse', 'salesProduct')->findOrFail($id);
        $data['pageTitle'] = 'Invoice #' . @$data['singleSale']->invoice_no;

        return view('backend.sales.invoice')->with($data);
    }

    public function levelPrint($id)
    {
        $data['singleSale'] = Sales::with('customer', 'warehouse', 'salesProduct.product', 'salesProduct.combo')->findOrFail($id);

        return view('backend.sales.level-print')->with($data);
    }

    public function levelPrintBulk(Request $request)
    {
        $ids = explode(',', $request->input('ids'));
        $data['sales'] = Sales::with('customer', 'warehouse', 'salesProduct.product', 'salesProduct.combo')
            ->whereIn('id', $ids)
            ->get();

        return view('backend.sales.level-print-bulk')->with($data);
    }

    public function salesDuePayment(Request $request, $id)
    {
        $request->validate([
            'payment_amount' => 'required|gte:0',
            'payment_method' => 'required|string',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $sales = Sales::findOrFail($id);

                $due = $sales->due_amount - $request->payment_amount;
                $pay = $sales->paid_amount + $request->payment_amount;

                if ($due == 0) {
                    $sales->payment_status = 1;
                    $sales->system_status = 'completed';
                }
                $sales->due_amount = $due;
                $sales->paid_amount = $pay;
                $sales->payment_method = $request->payment_method;

                $paymentAccount = PaymentMethod::findOrFail($request->payment_method);
                $customerName = optional($sales->customer)->name ?? 'Unknown Customer';

                $sales->transactions()->create([
                    'customer_id' => $sales->customer_id,
                    'amount' => $request->payment_amount,
                    'debit' => 'debit',
                    'credit' => null,
                    'transaction_type' => 'payment_received',
                    'transaction_tag' => 'due_paid',
                    'note' => 'PAYMENT RECEIVED: ' . $customerName,
                    'payment_method_id' => $paymentAccount->id,
                ]);

                // Increase the payment account's current balance
                $paymentAccount->increment('current_balance', $sales->paid_amount);

                $sales->save();
            });

            return back()->with('success', 'Due Amount paid successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while processing the payment: ' . $e->getMessage());
        }
    }

    public function searchProduct(Request $request)
    {
        $search = $request->term; // Fix here

        $products = Product::where('name', 'LIKE', "%$search%")
            ->orWhere('code', 'LIKE', "%$search%")
            ->select('id', 'name')
            ->orderBy('name')
            ->limit(20)
            ->get();

        // Format for Select2
        $results = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'text' => $product->name,
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function salesLedger(Request $request)
    {
        // Initialize view variables
        $viewData = [
            'accountsActiveClass' => 'active',
            'pageTitle' => 'Sales Ledger',
            'salesLedgerActiveClass' => 'active',
            'startDate' => $request->input('start_date', null),
            'endDate' => $request->input('end_date', null),
            'openingBalance' => 0,
            'closingBalance' => 0,
            'totals' => (object) [
                'total_debit' => 0,
                'total_credit' => 0,
            ],
            'transactions' => collect(),
        ];

        // Process date range if provided
        if ($viewData['startDate'] && $viewData['endDate']) {
            // Format dates
            $start = Carbon::parse($viewData['startDate'])->startOfDay()->format('Y-m-d H:i:s');
            $end = Carbon::parse($viewData['endDate'])->endOfDay()->format('Y-m-d H:i:s');

            // Daily/range view should reflect selected period only (no historical carry-forward).
            $viewData['openingBalance'] = 0;

            // Fetch and process grouped transactions
            $viewData['transactions'] = $this->getTransactions($start, $end);
            $viewData = $this->processTransactions($viewData);
        }

        // Handle response
        return $this->renderResponse($request, $viewData);
    }

    /**
     * Calculate opening balance before the start date
     */
    private function getOpeningBalance(string $start): float
    {
        $query = 'SELECT 
            IFNULL(SUM(CASE WHEN credit IS NOT NULL THEN amount ELSE 0 END), 0) - 
            IFNULL(SUM(CASE WHEN debit IS NOT NULL THEN amount ELSE 0 END), 0) AS balance
        FROM transactions
        WHERE transactionable_type = ? AND created_at < ?';

        $result = DB::selectOne($query, [Sales::class, $start]);
        return $result->balance ?? 0;
    }

    /**
     * Fetch transactions grouped by invoice_no for better readability
     */
    private function getTransactions(string $start, string $end): \Illuminate\Support\Collection
    {
        $query = '
        SELECT 
            MIN(t.created_at) AS date,
            MAX(s.invoice_no) AS invoice_no,
            t.transaction_type,
            MAX(t.note) AS note,
            SUM(t.amount) AS amount,
            SUM(CASE WHEN t.debit IS NOT NULL THEN t.amount ELSE 0 END) AS debit_amount,
            SUM(CASE WHEN t.credit IS NOT NULL THEN t.amount ELSE 0 END) AS credit_amount,
            t.transactionable_id,
            MAX(pm.name) AS payment_methods
        FROM transactions t
        LEFT JOIN sales s ON s.id = t.transactionable_id AND t.transactionable_type = ?
        LEFT JOIN payment_methods pm ON t.payment_method_id = pm.id
        WHERE t.transactionable_type = ? AND t.created_at BETWEEN ? AND ?
        GROUP BY t.transaction_type, t.transactionable_id
        ORDER BY MAX(s.invoice_no) ASC, MIN(t.created_at) ASC
    ';

        return collect(DB::select($query, [Sales::class, Sales::class, $start, $end]));
    }


    /**
     * Process transactions to calculate running balance and totals
     */
    private function processTransactions(array $viewData): array
    {
        $runningBalance = $viewData['openingBalance'];
        $groupedTransactions = $viewData['transactions']->groupBy('invoice_no');

        // Flatten transactions while preserving invoice_no grouping
        $processedTransactions = collect();
        foreach ($groupedTransactions as $invoiceTransactions) {
            foreach ($invoiceTransactions as $transaction) {
                [$transaction->debit_amount, $transaction->credit_amount] = $this->normalizeDrCrAmounts(
                    (float) ($transaction->debit_amount ?? 0),
                    (float) ($transaction->credit_amount ?? 0)
                );

                $runningBalance = $this->updateRunningBalance($transaction, $runningBalance);
                $transaction->balance = $runningBalance;
                $processedTransactions->push($transaction);
            }
        }

        $viewData['transactions'] = $processedTransactions;
        $viewData['totals']->total_debit = $processedTransactions->sum('debit_amount');
        $viewData['totals']->total_credit = $processedTransactions->sum('credit_amount');
        $viewData['closingBalance'] = $runningBalance;

        return $viewData;
    }

    /**
     * Convert negative debit/credit values into the opposite side
     * so ledger always shows positive numbers in proper Dr/Cr columns.
     *
     * @return array{0: float, 1: float}
     */
    private function normalizeDrCrAmounts(float $debit, float $credit): array
    {
        if ($debit < 0) {
            $credit += abs($debit);
            $debit = 0.0;
        }

        if ($credit < 0) {
            $debit += abs($credit);
            $credit = 0.0;
        }

        return [$debit, $credit];
    }

    /**
     * Update running balance based on transaction type
     */
    private function updateRunningBalance($transaction, float $runningBalance): float
    {
        switch ($transaction->transaction_type) {
            case 'sales_on_credit':
                return $runningBalance + $transaction->credit_amount;
            case 'payment_received':
            case 'courier_shipping_charge':
            case 'courier_cod_charge':
                return $runningBalance - $transaction->debit_amount;
            default:
                return $runningBalance;
        }
    }

    /**
     * Render response based on request type
     */
    private function renderResponse(Request $request, array $viewData): \Illuminate\Http\Response
    {
        if ($request->ajax()) {
            return response()->view('backend.accounts.sales-ledger-table', $viewData);
        }

        return response()->view('backend.accounts.sales-ledger', $viewData);
    }

    public function changeStatus($id, Request $request)
    {
        $sale = Sales::with('salesProduct')->findOrFail($id);
        $oldStatus = $sale->system_status;
        $newStatus = $request->query('status');

        if ($newStatus === CommonConstant::COMPLETED) {
            $this->stockService->markAsDelivered($sale);
        }

        if ($newStatus === CommonConstant::CANCELED) {
            $sale->return_status = CommonConstant::PENDING;
        }

        $sale->system_status = $newStatus;
        $sale->status = $newStatus === CommonConstant::COMPLETED ? CommonConstant::DELIVERED : CommonConstant::CANCELED;

        // Log the status change
        activity()
            ->performedOn($sale)
            ->causedBy(auth()->guard('admin')->user())
            ->event('sales_status_update')
            ->withProperties([
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => auth()->guard('admin')->user()->name,
            ])
            ->log('sales_status_changed');

        $sale->save();

        return response()->json(['success' => true, 'message' => 'Status updated successfully']);
    }

    public function cashFlow(Request $request)
    {
        $cashFlowActiveClass = 'active';
        $accountsActiveClass = 'active';
        $pageTitle = 'Cash Flow';
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $openingCash = 0;
        $totalSalesInflow = 0;
        $totalOutflow = 0;
        $closingCash = 0;

        $paymentAccounts = PaymentMethod::select('id', 'name')->get();

        $inflowByMethod = [];
        $outflowByMethod = [];

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            // Include 'add_fund' in inflows
            $inflows = Transaction::with('paymentMethod')
                ->select('payment_method_id', DB::raw('SUM(amount) as total'))
                ->whereBetween('created_at', [$start, $end])
                ->whereIn('transaction_type', ['payment_received'])
                ->groupBy('payment_method_id')
                ->get();

            $outflows = Transaction::with('paymentMethod')
                ->select('payment_method_id', DB::raw('SUM(amount) as total'))
                ->whereBetween('created_at', [$start, $end])
                ->whereIn('transaction_type', [
                    'purchases_payment_paid',
                    'expense_on_payment',
                    'salary_paid',
                ])
                ->groupBy('payment_method_id')
                ->get();

            foreach ($paymentAccounts as $method) {
                $methodId = $method->id;

                $inflowByMethod[$methodId] = optional($inflows->firstWhere('payment_method_id', $methodId))->total ?? 0;
                $outflowByMethod[$methodId] = optional($outflows->firstWhere('payment_method_id', $methodId))->total ?? 0;
            }

            $totalSalesInflow = array_sum($inflowByMethod);
            $totalOutflow = array_sum($outflowByMethod);

            $closingCash = $openingCash;
        }

        if ($request->ajax()) {
            return view('backend.accounts.cash-flow-table', compact(
                'startDate',
                'endDate',
                'openingCash',
                'totalSalesInflow',
                'totalOutflow',
                'closingCash',
                'paymentAccounts',
                'inflowByMethod',
                'outflowByMethod'
            ))->render();
        }

        return view('backend.accounts.cash_flow', compact(
            'startDate',
            'endDate',
            'cashFlowActiveClass',
            'accountsActiveClass',
            'pageTitle',
            'openingCash',
            'totalSalesInflow',
            'totalOutflow',
            'closingCash',
            'paymentAccounts',
            'inflowByMethod',
            'outflowByMethod'
        ));
    }

    public function sendToSteadFast(Request $request)
    {
        $request->validate([
            'ids' => 'required',
        ]);

        try {
            $ids = explode(',', $request->ids);
            $result = $this->courierService->sendToSteadfast($ids);

            return response()->json([
                'message' => $result['message'],
                'errors' => $result['errors'] ?? null,
                'response' => $result['response'] ?? null,
            ], $result['status']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to place orders with courier service. Please try again later.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function sendToCarrybee(Request $request)
    {
        $enabled = (int) GeneralSetting::query()->value('enable_carrybee');
        if ($enabled !== 1) {
            return response()->json(['message' => 'Carrybee is disabled.'], 403);
        }

        $request->validate([
            'ids' => 'required|string',
            'carrybee_account_key' => 'required|string',
        ]);

        $account = CarrybeeIntegrationService::accountByKey($request->carrybee_account_key);

        if (! $account) {
            return response()->json(['message' => 'Invalid Carrybee account selected.'], 422);
        }

        $saleIds = array_values(array_filter(array_map('intval', explode(',', $request->ids))));
        if ($saleIds === []) {
            return response()->json(['message' => 'No valid order IDs.'], 422);
        }

        $sales = Sales::whereIn('id', $saleIds)
            ->whereNull('consignment_id')
            ->where('system_status', '!=', 'cancelled')
            ->get();

        if ($sales->isEmpty()) {
            return response()->json([
                'message' => 'No valid sales records found, or all selected orders already have a consignment ID, or are cancelled.',
            ], 404);
        }

        try {
            $result = $this->carrybeeCourierService->sendOrders($account, $sales);

            return response()->json([
                'message' => $result['message'],
                'errors' => $result['errors'] ?? null,
            ], $result['status']);
        } catch (\Throwable $e) {
            Log::error('Carrybee bulk send failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Carrybee request failed. Please try again later.',
            ], 500);
        }
    }

    public function sendToSteadFastSingleOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:sales,id',
        ]);

        $orderId = $request->input('order_id');

        try {
            $result = $this->courierService->sendToSteadfastSingleOrder($orderId);

            return response()->json([
                'message' => $result['message'],
                'errors' => $result['errors'] ?? null,
                'response' => $result['response'] ?? null,
            ], $result['status']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to place orders with courier service. Please try again later.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function handleSteadFastWebhook(Request $request, $courier = 'steadfast')
    {
        $result = $this->webhookService->processWebhook($courier, $request->all());

        return response()->json([
            'status' => $result['status'],
            'message' => $result['message'],
        ], $result['code']);
    }

    public function handleCarrybeeWebhook(Request $request)
    {
        // URL checks may use GET/HEAD; never use $request->json() here — invalid JSON + application/json
        // triggers Symfony/Laravel and returns 400 before this code runs.
        if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
            $result = ['status' => 'success', 'message' => 'Webhook endpoint OK.', 'code' => 202];
        } else {
            $raw = $request->getContent();
            $payload = [];
            if ($raw !== '') {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $payload = $decoded;
                }
            }
            if ($payload === [] && $request->request->count() > 0) {
                $payload = $request->all();
            }

            if (isset($payload['content']) && is_string($payload['content'])) {
                $decoded = json_decode($payload['content'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $payload = $decoded;
                }
            }

            $result = $this->carrybeeWebhookService->processPayload($payload);
        }

        return response()->json([
            'status' => $result['status'],
            'message' => $result['message'],
        ], $result['code']);
    }

    public function checkSteadfastStatus(Request $request)
    {
        $ip = $request->ip();
        $key = 'status-attempts:' . $ip;

        try {
            // Check rate limit
            $rateLimit = $this->rateLimiterService->checkRateLimit($key);
            if ($rateLimit) {
                return response()->json($rateLimit, $rateLimit['status']);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'consignment_id' => 'required',
            ]);

            if ($validator->fails()) {
                // If validation fails, hit the rate limit so the failed attempt counts
                $this->rateLimiterService->hitRateLimit($key);

                return response()->json(['message' => $validator->errors()->first()], 422);
            }

            // Call the courier service API
            $response = SteadfastCourier::checkDeliveryStatusByConsignmentId($request->consignment_id);

            // Clear rate limit on success
            $this->rateLimiterService->clearRateLimit($key);

            return response()->json([
                'status' => 'success',
                'tracking_status' => $response['delivery_status'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tracking information not found.',
            ], 404);
        }
    }
}
