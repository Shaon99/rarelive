<?php

namespace App\Http\Controllers\Admin;

use App\Constants\CommonConstant;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\PurchaseReturn;
use App\Models\Purchases;
use App\Models\SalaryPayment;
use App\Models\Sales;
use App\Services\SteadfastCourierService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    private $user;

    protected $courierService;

    public function __construct(SteadfastCourierService $courierService)
    {
        $this->courierService = $courierService;

        $this->middleware(function ($request, $next) {
            $this->user = Auth::guard('admin')->user();

            return $next($request);
        });
    }

    public function dashboard()
    {
        $data = [
            'pageTitle' => 'Dashboard',
            'navDashboardActiveClass' => 'active',
        ];

        if (is_null($this->user) || ! $this->user->can('dashboard_access')) {
            return view('backend.welcome', $data);
        }

        $todayFilter = $this->getDateFilter('today');

        // Sales data (default period: today — matches period selector + admin.filter)
        $data = array_merge($data, $this->fetchOverallSalesData($todayFilter));

        $queries = $this->initializeQueries($todayFilter);
        $data['total_expense'] = $queries['expense']->sum('amount');
        $data['total_salary'] = $queries['salary']->sum('amount_paid');
        $data['total_purchases'] = $queries['purchases']->sum('grand_total');
        $data['return_total_purchases'] = $queries['refund']->sum('total_amount');

        // Steadfast balance comes from API below; exclude STEADFAST row from DB sum to avoid double-count.
        // CARRYBEE, CASH, BANK, etc. use local current_balance (e.g. Carrybee webhooks increment CARRYBEE).
        $opening_balance = $this->sumNonSteadfastPaymentBalances();

        $data['topSellingProducts'] = $this->fetchTopSellingProducts($todayFilter);
        $data['topSellingByLocation'] = $this->fetchTopSellingByLocation($todayFilter);

        $chartYear = (int) Carbon::now()->year;
        $data['dashboardChartYear'] = $chartYear;

        $data = array_merge($data, $this->fetchMonthlySalesAndReturnsData($chartYear));
        $data = array_merge($data, $this->fetchCompletedOrdersByUser($chartYear));
        $data['salesByPlatform'] = $this->fetchSalesByPlatform($chartYear);
        $data = array_merge($data, $this->fetchCompletedOrdersByLead($chartYear));

        // Use cache to reduce load on external API
        $steadfastResponse = $this->courierService->getSteadfastCurrentBalance();
        $steadfastBalance = is_array($steadfastResponse) && isset($steadfastResponse['current_balance'])
            ? $steadfastResponse['current_balance']
            : 0;
        $data['steadFastBalance'] = [
            'current_balance' => $steadfastBalance
        ];

        $data['total_profit'] = (float) $opening_balance + (float) $steadfastBalance;


        $data['recentSales'] = Sales::with('paymentMethod')
            ->whereDate('created_at', Carbon::today())
            ->latest()
            ->take(10)
            ->get();

        $data['assetValue'] = Product::sum(DB::raw('quantity * purchase_price'));

        // Optimized expense category breakdown
        $expenseData = DB::table('expenses')
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->select('expense_categories.name as category', DB::raw('SUM(expenses.amount) as total'))
            ->whereMonth('expenses.created_at', now()->month)
            ->whereYear('expenses.created_at', now()->year)
            ->groupBy('expense_categories.name')
            ->get();

        $totalExpenses = $expenseData->sum('total');

        $expenseData = $expenseData->map(function ($item) use ($totalExpenses) {
            $item->percentage = $totalExpenses > 0 ? ($item->total / $totalExpenses) * 100 : 0;

            return $item;
        });

        $data['expenseCategories'] = $expenseData->pluck('category');
        $data['expenseTotals'] = $expenseData->pluck('total');
        $data['expensePercentages'] = $expenseData->pluck('percentage')->toArray();

        $data = array_merge($data, $this->fetchDeliveredOrderStats());

        return view('backend.dashboard', $data);
    }

    private function fetchDeliveredOrderStats(): array
    {
        $currentMonthDelivered = Sales::where('system_status', CommonConstant::COMPLETED)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();


        $lastMonth = Carbon::now()->subMonth();
        $lastMonthDelivered = Sales::where('system_status', CommonConstant::COMPLETED)
            ->whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->count();


        if ($lastMonthDelivered > 0) {
            $percentageChange = (($currentMonthDelivered - $lastMonthDelivered) / $lastMonthDelivered) * 100;
        } else {
            $percentageChange = $currentMonthDelivered > 0 ? 100 : 0;
        }

        $changeType = $percentageChange > 0 ? 'increase' : ($percentageChange < 0 ? 'decrease' : 'no_change');

        return [
            'currentMonthDelivered' => $currentMonthDelivered,
            'deliveredPercentageChange' => abs(round($percentageChange, 2)),
            'deliveredChangeType' => $changeType,
        ];
    }

    /**
     * Payment method IDs whose type is Partial (sales.payment_method stores ID, not the word "Partial").
     *
     * @return list<int>
     */
    private function partialPaymentMethodIds(): array
    {
        static $cached = null;

        if ($cached === null) {
            $cached = PaymentMethod::query()
                ->where('type', 'Partial')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        return $cached;
    }

    /**
     * Sum stored balances for all payment methods except STEADFAST (live API balance is merged separately).
     */
    private function sumNonSteadfastPaymentBalances(): float
    {
        return (float) PaymentMethod::query()
            ->where('type', '!=', CommonConstant::STEADFAST)
            ->sum('current_balance');
    }

    /**
     * Fetch overall sales data.
     */
    private function fetchOverallSalesData(?array $dateFilter = null)
    {
        $query = Sales::query();
        $this->applyDateFilterToQuery($query, $dateFilter);

        $partialIds = $this->partialPaymentMethodIds();
        $partialSql = count($partialIds) > 0
            ? 'payment_method IN (' . implode(',', $partialIds) . ')'
            : '0=1';

        return $query->selectRaw("
            SUM(grand_total) as total_sales,
            COUNT(*) as total_sales_count,
            SUM(CASE WHEN system_status = 'completed' THEN grand_total ELSE 0 END) as total_delivered,
            COUNT(CASE WHEN system_status = 'completed' THEN 1 ELSE NULL END) as total_delivered_count,
            SUM(CASE WHEN system_status = 'pending' THEN grand_total ELSE 0 END) as total_pending,
            COUNT(CASE WHEN system_status = 'pending' THEN 1 ELSE NULL END) as total_pending_count,
            SUM(CASE WHEN system_status = 'draft' THEN grand_total ELSE 0 END) as total_sales_draft,
            COUNT(CASE WHEN system_status = 'draft' THEN 1 ELSE NULL END) as total_sales_draft_count,
            SUM(CASE WHEN system_status = 'cancelled' THEN grand_total ELSE 0 END) as total_cancelled,
            COUNT(CASE WHEN system_status = 'cancelled' THEN 1 ELSE NULL END) as total_cancelled_count,
            SUM(CASE WHEN ({$partialSql}) AND system_status IN ('pending', 'cancelled') THEN paid_amount ELSE 0 END) as total_partial_sales,
            COUNT(CASE WHEN ({$partialSql}) AND system_status IN ('pending', 'cancelled') THEN 1 ELSE NULL END) as total_partial_sales_count
        ")->first()->toArray();
    }

    /**
     * Fetch top 3 selling products.
     */
    private function fetchTopSellingProducts(?array $dateFilter = null)
    {
        $query = Product::select(
            'products.id',
            'products.name',
            'products.code',
            DB::raw('SUM(sales_products.quantity) as total_quantity_sold')
        )
            ->join('sales_products', 'products.id', '=', 'sales_products.product_id')
            ->join('sales', 'sales.id', '=', 'sales_products.sales_id')
            ->where('sales.system_status', 'completed');
        $this->applyDateFilterToQuery($query, $dateFilter, 'sales.created_at');

        return $query->groupBy('products.id', 'products.name', 'products.code')
            ->orderByDesc('total_quantity_sold')
            ->limit(3)
            ->get();
    }

    public function fetchTopSellingByLocation(?array $dateFilter = null)
    {
        $query = DB::table('sales')
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->join('address_books', 'customers.id', '=', 'address_books.customer_id')
            ->join('sales_products', 'sales.id', '=', 'sales_products.sales_id')
            ->where('sales.system_status', 'completed');
        $this->applyDateFilterToQuery($query, $dateFilter, 'sales.created_at');

        return $query->select('address_books.city', DB::raw('SUM(sales_products.quantity) as total_quantity_sold'))
            ->groupBy('address_books.city')
            ->orderByDesc('total_quantity_sold')
            ->limit(3)
            ->get();
    }

    /**
     * Fetch monthly sales and returns data for a single calendar year (12 months).
     */
    private function fetchMonthlySalesAndReturnsData(int $year)
    {
        $monthlySales = Sales::selectRaw("
            MONTH(created_at) as month,
            COUNT(CASE WHEN system_status = 'completed' THEN 1 ELSE NULL END) as total_delivered_count,
            COUNT(CASE WHEN system_status = 'cancelled' THEN 1 ELSE NULL END) as total_cancelled_count
        ")
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->get();

        $monthly_sales_data = array_fill(0, 12, 0);
        $monthly_return_data = array_fill(0, 12, 0);

        foreach ($monthlySales as $sale) {
            $monthly_sales_data[$sale->month - 1] = $sale->total_delivered_count;
            $monthly_return_data[$sale->month - 1] = $sale->total_cancelled_count;
        }

        return [
            'monthlyLabels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'],
            'monthly_sales_data' => $monthly_sales_data,
            'monthly_return_data' => $monthly_return_data,
        ];
    }

    /**
     * Fetch completed orders by user per month for one calendar year.
     */
    private function fetchCompletedOrdersByUser(int $year)
    {
        $completedOrders = Sales::where('system_status', 'completed')
            ->whereYear('created_at', $year)
            ->selectRaw('
                user_id,
                MONTH(created_at) as month,
                COUNT(*) as total_completed_orders
            ')
            ->groupBy('user_id', DB::raw('MONTH(created_at)'))
            ->orderBy('month', 'asc')
            ->get();

        $groupedSales = $completedOrders->groupBy('user_id');

        $adminIds = $groupedSales->keys()->toArray();
        $admins = Admin::whereIn('id', $adminIds)->get()->keyBy('id');

        $datasets = [];
        $colors = ['#c5caff', '#ffcccb', '#caffbf', '#ffd700', '#ffb6c1', '#add8e6', '#ff69b4', '#ffa07a', '#20b2aa', '#87cefa', '#778899', '#b0c4de'];
        $colorIndex = 0;

        foreach ($groupedSales as $userId => $sales) {
            $admin = $admins->get($userId);
            if (! $admin) {
                continue;
            }

            $userData = array_fill(0, 12, 0);
            foreach ($sales as $sale) {
                $userData[$sale->month - 1] = $sale->total_completed_orders;
            }

            $datasets[] = [
                'label' => $admin->name,
                'data' => $userData,
                'backgroundColor' => $colors[$colorIndex % count($colors)],
                'borderColor' => $colors[$colorIndex % count($colors)],
                'borderRadius' => 5,
                'barThickness' => 15,
                'borderSkipped' => false,
            ];

            $colorIndex++;
        }

        return [
            'completedOrdersByUserDatasets' => $datasets,
        ];
    }

    private function fetchCompletedOrdersByLead(int $year)
    {
        $completedOrders = Sales::where('system_status', 'completed')
            ->whereYear('created_at', $year)
            ->selectRaw('
                lead_id,
                MONTH(created_at) as month,
                COUNT(*) as total_completed_orders
            ')
            ->groupBy('lead_id', DB::raw('MONTH(created_at)'))
            ->orderBy('month', 'asc')
            ->get();

        $groupedSales = $completedOrders->groupBy('lead_id');

        $adminIds = $groupedSales->keys()->toArray();
        $admins = Employee::whereIn('id', $adminIds)->get()->keyBy('id');

        $datasets = [];
        $colors = ['#c5caff', '#ffcccb', '#caffbf', '#ffd700', '#ffb6c1', '#add8e6', '#ff69b4', '#ffa07a', '#20b2aa', '#87cefa', '#778899', '#b0c4de'];
        $colorIndex = 0;

        foreach ($groupedSales as $userId => $sales) {
            $admin = $admins->get($userId);
            if (! $admin) {
                continue;
            }

            $userData = array_fill(0, 12, 0);
            foreach ($sales as $sale) {
                $userData[$sale->month - 1] = $sale->total_completed_orders;
            }

            $datasets[] = [
                'label' => $admin->employee_name,
                'data' => $userData,
                'backgroundColor' => $colors[$colorIndex % count($colors)],
                'borderColor' => $colors[$colorIndex % count($colors)],
                'borderRadius' => 5,
                'barThickness' => 15,
                'borderSkipped' => false,
            ];

            $colorIndex++;
        }

        return [
            'completedOrdersByLeadDatasets' => $datasets,
        ];
    }

    public function fetchSalesByPlatform(int $year)
    {
        $salesData = Sales::selectRaw('
            platform,
            MONTH(created_at) as month,
            COUNT(*) as total_orders
        ')
            ->where('system_status', 'completed')
            ->whereYear('created_at', $year)
            ->groupBy('platform', DB::raw('MONTH(created_at)'))
            ->orderBy('month', 'asc')
            ->get();

        // Initialize arrays for each platform
        $facebookData = array_fill(0, 12, 0); // 12 months, initialized with 0
        $whatsappData = array_fill(0, 12, 0); // 12 months, initialized with 0
        $othersData = array_fill(0, 12, 0); // 12 months, initialized with 0

        // Map sales data to the respective platform arrays
        foreach ($salesData as $sale) {
            $monthIndex = $sale->month - 1; // Convert month to array index (0-11)
            switch ($sale->platform) {
                case 'facebook':
                    $facebookData[$monthIndex] = $sale->total_orders;
                    break;
                case 'whatsapp':
                    $whatsappData[$monthIndex] = $sale->total_orders;
                    break;
                case 'others':
                    $othersData[$monthIndex] = $sale->total_orders;
                    break;
            }
        }

        // Prepare data for the frontend
        $monthlyLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'];

        return [
            'labels' => $monthlyLabels,
            'datasets' => [
                [
                    'label' => 'Facebook Orders',
                    'data' => $facebookData,
                    'borderColor' => '#4267B2', // Facebook blue
                    'backgroundColor' => 'rgba(0, 0, 0, 0)', // No background color
                    'borderRadius' => 5,
                    'barThickness' => 15,
                    'borderSkipped' => false,
                ],
                [
                    'label' => 'WhatsApp Orders',
                    'data' => $whatsappData,
                    'borderColor' => '#25D366', // WhatsApp green
                    'backgroundColor' => 'rgba(0, 0, 0, 0)', // No background color
                    'borderRadius' => 5,
                    'barThickness' => 15,
                    'borderSkipped' => false,
                ],
                [
                    'label' => 'Other Orders',
                    'data' => $othersData,
                    'borderColor' => '#FFA500', // Orange for Others
                    'backgroundColor' => 'rgba(0, 0, 0, 0)', // No background color
                    'borderRadius' => 5,
                    'barThickness' => 15,
                    'borderSkipped' => false,
                ],
            ],
        ];
    }

    public function filter(Request $request)
    {
        $dateFilter = $this->resolveDateFilter($request);

        // Initialize all queries with date filter
        $queries = $this->initializeQueries($dateFilter);

        // Get sales statistics
        $salesStats = $this->getSalesStatistics($queries['sales']);

        // Get financial statistics
        $financialStats = $this->getFinancialStatistics($queries);

        return response()->json(array_merge($salesStats, $financialStats));
    }

    /**
     * Date filter from dashboard AJAX (GET filter=…) or preset key.
     */
    private function resolveDateFilter(Request $request): ?array
    {
        $filter = (string) $request->input('filter', '');

        if ($filter === 'custom') {
            return $this->getDateFilterForCustomRange(
                $request->input('start_date'),
                $request->input('end_date')
            );
        }

        return $this->getDateFilter($filter !== '' ? $filter : 'today');
    }

    /**
     * @return array{type: 'whereBetween', start: \Carbon\Carbon, end: \Carbon\Carbon}|null
     */
    private function getDateFilterForCustomRange(?string $start, ?string $end): ?array
    {
        if ($start === null || $start === '' || $end === null || $end === '') {
            return $this->getDateFilter('today');
        }

        try {
            $s = Carbon::parse($start)->startOfDay();
            $e = Carbon::parse($end)->endOfDay();
        } catch (\Throwable) {
            return $this->getDateFilter('today');
        }

        if ($s->gt($e)) {
            [$s, $e] = [$e->copy()->startOfDay(), $s->copy()->endOfDay()];
        }

        if ($s->diffInDays($e) > 731) {
            $e = $s->copy()->addDays(731)->endOfDay();
        }

        return [
            'type' => 'whereBetween',
            'start' => $s,
            'end' => $e,
        ];
    }

    private function getDateFilter(?string $filterType): ?array
    {
        switch ($filterType) {
            case 'all':
                return null;
            case 'today':
                return ['type' => 'whereDate', 'value' => Carbon::today()];
            case 'seven':
                return ['type' => 'where', 'value' => Carbon::today()->subDays(7)];
            case 'month':
                return ['type' => 'whereMonth', 'value' => Carbon::now()->month];
            case 'last_month':
                return [
                    'type' => 'whereBetween',
                    'start' => Carbon::now()->subMonthNoOverflow()->startOfMonth(),
                    'end' => Carbon::now()->subMonthNoOverflow()->endOfMonth(),
                ];
            case 'year':
                return ['type' => 'whereYear', 'value' => Carbon::now()->year];
            default:
                return null;
        }
    }

    private function initializeQueries($dateFilter)
    {
        $queries = [
            'sales' => Sales::query(),
            'expense' => Expense::query(),
            'salary' => SalaryPayment::query(),
            'purchases' => Purchases::query(),
            'refund' => PurchaseReturn::query(),
        ];

        foreach ($queries as &$query) {
            $this->applyDateFilterToQuery($query, $dateFilter);
        }

        return $queries;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     */
    private function applyDateFilterToQuery($query, ?array $dateFilter, string $column = 'created_at'): void
    {
        if (! $dateFilter) {
            return;
        }

        if ($dateFilter['type'] === 'whereBetween') {
            $query->whereBetween($column, [$dateFilter['start'], $dateFilter['end']]);

            return;
        }

        if ($dateFilter['type'] === 'where') {
            $query->where($column, '>=', $dateFilter['value']);
        } else {
            $query->{$dateFilter['type']}($column, $dateFilter['value']);
        }
    }

    private function getSalesStatistics($query)
    {
        $baseQuery = clone $query;
        $partialIds = $this->partialPaymentMethodIds();

        $partialFiltered = function ($q) use ($partialIds) {
            if (count($partialIds) === 0) {
                $q->whereRaw('0 = 1');

                return;
            }
            $q->whereIn('payment_method', $partialIds);
        };

        return [
            'total_sales' => $baseQuery->sum('grand_total'),
            'total_sales_count' => $baseQuery->count(),
            'total_sales_draft' => (clone $baseQuery)->where('system_status', 'draft')->sum('grand_total'),
            'total_sales_draft_count' => (clone $baseQuery)->where('system_status', 'draft')->count(),
            'order_delivered' => (clone $baseQuery)->where('system_status', 'completed')->sum('grand_total'),
            'total_delivered_count' => (clone $baseQuery)->where('system_status', 'completed')->count(),
            'partial_delivered' => (clone $baseQuery)->where($partialFiltered)
                ->whereIn('system_status', ['pending', 'cancelled'])
                ->sum('paid_amount'),
            'partial_delivered_count' => (clone $baseQuery)->where($partialFiltered)
                ->whereIn('system_status', ['pending', 'cancelled'])
                ->count(),
            'order_pending' => (clone $baseQuery)->where('system_status', 'pending')->sum('grand_total'),
            'order_pending_count' => (clone $baseQuery)->where('system_status', 'pending')->count(),
            'order_cancelled' => (clone $baseQuery)->where('system_status', 'cancelled')->sum('grand_total'),
            'order_cancelled_count' => (clone $baseQuery)->where('system_status', 'cancelled')->count(),
        ];
    }

    private function getFinancialStatistics($queries)
    {

        $steadfastResponse = $this->courierService->getSteadfastCurrentBalance();
        $steadfastBalance = is_array($steadfastResponse) && isset($steadfastResponse['current_balance'])
            ? $steadfastResponse['current_balance']
            : 0;
        $data['steadFastBalance'] = [
            'current_balance' => $steadfastBalance
        ];

        $total_profit = $this->sumNonSteadfastPaymentBalances() + $steadfastBalance;

        return [
            'total_expense' => $queries['expense']->sum('amount'),
            'total_salary' => $queries['salary']->sum('amount_paid'),
            'total_purchases' => $queries['purchases']->sum('grand_total'),
            'refund' => $queries['refund']->sum('total_amount'),
            'revenue' => $total_profit ?? 0.00,
        ];
    }
}
