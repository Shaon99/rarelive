<div class="flat-menu">
    <!-- Dashboard -->
    @if (auth()->guard('admin')->user()->can('dashboard'))
        <a href="{{ route('admin.home') }}" class="search-suggestion-item">
            <span><i class="fas fa-fire text-primary mr-1"></i> {{ __('Dashboard') }}</span>
        </a>
    @endif

    <!-- Admin Users -->
    @if (auth()->guard('admin')->user()->can('admin_user_list'))
        <a href="{{ route('admin.index') }}" class="search-suggestion-item">
            <span><i class="fas fa-users text-warning mr-1"></i> {{ __('Admin List') }}</span>
        </a>
    @endif

    @if (auth()->guard('admin')->user()->can('admin_user_add'))
        <a href="{{ route('admin.create') }}" class="search-suggestion-item">
            <span><i class="fas fa-user-plus text-info mr-1"></i> {{ __('Create Admin') }}</span>
        </a>
    @endif

    <!-- Roles -->
    @if (auth()->guard('admin')->user()->can('role_list'))
        <a href="{{ route('admin.roles.index') }}" class="search-suggestion-item">
            <span><i class="fas fa-user-tag text-secondary mr-1"></i> {{ __('Role List') }}</span>
        </a>
    @endif
    @if (auth()->guard('admin')->user()->can('role_add'))
        <a href="{{ route('admin.roles.create') }}" class="search-suggestion-item">
            <span><i class="fas fa-plus-circle text-success mr-1"></i> {{ __('Create Role') }}</span>
        </a>
    @endif

    <!-- Customers -->
    @if (auth()->guard('admin')->user()->can('customer_list'))
        <a href="{{ route('admin.customer.index') }}" class="search-suggestion-item">
            <span><i class="fas fa-users text-primary mr-1"></i> {{ __('Customers') }}</span>
        </a>
    @endif

    <!-- Suppliers -->
    @if (auth()->guard('admin')->user()->can('supplier_list'))
        <a href="{{ route('admin.supplier.index') }}" class="search-suggestion-item">
            <span><i class="fas fa-truck text-dark mr-1"></i> {{ __('Suppliers') }}</span>
        </a>
    @endif
    <!-- Products -->
    @if (auth()->guard('admin')->user()->can('product_list'))
        <a href="{{ route('admin.product.index') }}" class="search-suggestion-item">
            <span><i class="fas fa-box text-purple mr-1"></i> {{ __('Products') }}</span>
        </a>
    @endif
    @if (auth()->guard('admin')->user()->can('product_transfer_list'))
        <a href="{{ route('admin.product.transfer') }}" class="search-suggestion-item">
            <span><i class="fas fa-dolly-flatbed text-success mr-1"></i> {{ __('Product Transfer') }}</span>
        </a>
    @endif
    @if (auth()->guard('admin')->user()->can('purchases_list'))
        <!-- Purchases -->
        <a href="{{ route('admin.purchases.index') }}" class="search-suggestion-item">
            <span><i class="fas fa-shopping-cart text-success mr-1"></i> {{ __('Purchases') }}</span>
        </a>
    @endif
    @if (auth()->guard('admin')->user()->can('due_purchase_list'))
        <a href="{{ route('admin.purchases.index', ['duepurchases' => 'DuePurchases']) }}"
            class="search-suggestion-item">
            <span><i class="fas fa-money-check-alt text-warning mr-1"></i> {{ __('Due Purchases') }}</span>
        </a>
    @endif
    <!-- Sales -->
    @if (auth()->guard('admin')->user()->can('order_list'))
        <a href="{{ route('admin.sales.index') }}" class="search-suggestion-item">
            <span><i class="fas fa-cash-register text-danger mr-1"></i> {{ __('Orders') }}</span>
        </a>
    @endif

    <!-- Expenses -->
    @if (auth()->guard('admin')->user()->can('expense_category_list'))
        <a href="{{ route('admin.expense-category.index') }}" class="search-suggestion-item">
            <span><i class="fas fa-list text-primary mr-1"></i> {{ __('Expense Category') }}</span>
        </a>
    @endif
    @if (auth()->guard('admin')->user()->can('expense_list'))
        <a href="{{ route('admin.expense.index') }}" class="search-suggestion-item">
            <span><i class="fas fa-file-invoice-dollar text-orange mr-1"></i> {{ __('Expenses') }}</span>
        </a>
    @endif

    @if (auth()->guard('admin')->user()->can('sales_report_list'))
        <!-- Reports -->
        <a href="{{ route('admin.sales.report') }}" class="search-suggestion-item">
            <span><i class="fas fa-chart-line text-info mr-1"></i> {{ __('Sales Reports') }}</span>
        </a>
    @endif

    @if (auth()->guard('admin')->user()->can('site_setting'))
        <!-- General Settings -->
        <a href="{{ route('admin.general.setting') }}" class="search-suggestion-item">
            <span><i class="fas fa-cog text-secondary mr-1"></i> {{ __('General Settings') }}</span>
        </a>
    @endif

    @if (auth()->guard('admin')->user()->can('language_list'))
        <!-- Manage Languages -->
        <a href="{{ route('admin.language.index') }}" class="search-suggestion-item">
            <span><i class="fas fa-globe-africa text-primary mr-1"></i> {{ __('Manage Language') }}</span>
        </a>
    @endif
</div>
