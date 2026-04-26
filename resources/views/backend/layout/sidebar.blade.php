<div class="main-sidebar {{ $hideNavSidebar??'' }}">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <button class="sidebar-search-btn" id="searchBtn">
                <span class="placeholder-text">Search document...</span>
                <span class="shortcut-hint">⌘ K</span>
            </button>
        </div>
        <ul class="sidebar-menu">
            <li class="nav-item {{ @$navDashboardActiveClass }}">
                <a href="{{ route('admin.home') }}" class="nav-link active-nav">
                    <x-heroicon-o-home class="mr-2 hero-icon" /> <span>{{ __('Dashboard') }}</span></a>
            </li>

            @if (auth()->guard('admin')->user()->can('admin_user_list'))
                <li class="nav-item {{ @$adminListActive }}">
                    <a class="nav-link" href="{{ route('admin.index') }}">
                        <x-heroicon-o-user-circle class="mr-2 hero-icon" />
                        <span> {{ __('Admin User') }}</span>
                    </a>
                </li>
            @endif

            @if (auth()->guard('admin')->user()->canany(['role_list', 'permission_list']))
                <li class="nav-item dropdown {{ @$administration_active }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                        <x-heroicon-o-users class="mr-2 hero-icon" />
                        <span>{{ __('Role & Permission') }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        @if (auth()->guard('admin')->user()->can('role_list'))
                            <li class="{{ @$role_list_active }}">
                                <a class="nav-link" href="{{ route('admin.roles.index') }}">{{ __('Role List') }}</a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('permission_list'))
                            <li class="{{ @$permission_list_active }}">
                                <a class="nav-link" href="{{ route('admin.permission') }}">
                                    Permissions
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if (auth()->guard('admin')->user()->can('customer_list'))
                <li class="{{ @$customers }}">
                    <a href="{{ route('admin.customer.index') }}" class="nav-link">
                        <x-heroicon-o-user-group class="mr-2 hero-icon" />
                        <span>{{ __('Customers') }}</span>
                    </a>
                </li>
            @endif

            @if (auth()->guard('admin')->user()->can('supplier_list'))
                <li class="{{ @$supplierManagement }}">
                    <a href="{{ route('admin.supplier.index') }}" class="nav-link">
                        <x-heroicon-o-user-plus class="mr-2 hero-icon" />
                        <span>{{ __('Suppliers') }}</span>
                    </a>
                </li>
            @endif

            @if (auth()->guard('admin')->user()->canany([
                        'warehouse_list',
                        'unit_list',
                        'category_list',
                        'brand_list',
                        'product_list',
                        'combo_product_list',
                        'product_transfer_list',
                    ]))
                <li class="nav-item dropdown {{ @$other }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                        <x-heroicon-o-circle-stack class="mr-2 hero-icon" />
                        <span>{{ __('Products') }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        @if (auth()->guard('admin')->user()->can('product_list'))
                            <li class="{{ @$product }}">
                                <a class="nav-link" href="{{ route('admin.product.index') }}">{{ __('Products') }}</a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('warehouse_list'))
                            <li class="{{ @$branch }}">
                                <a class="nav-link"
                                    href="{{ route('admin.warehouse.index') }}">{{ __('Branch') }}</a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('unit_list'))
                            <li class="{{ @$unit }}">
                                <a class="nav-link" href="{{ route('admin.unit.index') }}">{{ __('Units') }}</a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('category_list'))
                            <li class="{{ @$category }}">
                                <a class="nav-link"
                                    href="{{ route('admin.category.index') }}">{{ __('Categories') }}</a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('brand_list'))
                            <li class="{{ @$brand }}">
                                <a class="nav-link" href="{{ route('admin.brand.index') }}">{{ __('Brands') }}</a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('attribute_list'))
                            <li class="{{ @$attribute_list }}">
                                <a class="nav-link"
                                    href="{{ route('admin.productAttribute') }}">{{ __('Product Attribute') }}</a>
                            </li>
                        @endif                        
                        @if (auth()->guard('admin')->user()->can('combo_product_list'))
                            <li class="{{ @$comboProduct }}">
                                <a class="nav-link"
                                    href="{{ route('admin.comboProduct.index') }}">{{ __('Combo Products') }}</a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('product_transfer_list'))
                            <li class="{{ @$productTransferActive }}">
                                <a href="{{ route('admin.product.transfer') }}" class="nav-link">
                                    {{ __('Product Transfer') }}
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if (auth()->guard('admin')->user()->canany(['purchases_list', 'due_purchase_list', 'stock_list']))
                <li class="nav-item dropdown {{ @$purchasesManagement }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                        <x-heroicon-o-building-storefront class="mr-2 hero-icon" />
                        <span>{{ __('Inventory') }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        @if (auth()->guard('admin')->user()->can('purchases_list'))
                            <li class="{{ @$purchasesActive }}">
                                <a class="nav-link"
                                    href="{{ route('admin.purchases.index') }}">{{ __('Purchases') }}</a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('due_purchase_list'))
                            <li class="{{ @$duepurchasesActive }}">
                                <a class="nav-link"
                                    href="{{ route('admin.purchases.index', ['duepurchases' => 'DuePurchases']) }}">
                                    {{ __('Due Purchases') }}
                                </a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('product_stock_adjustment_list'))
                            <li class="{{ @$stockAdjustmentActive }}">
                                <a href="{{ route('admin.stock.adjust.form') }}" class="nav-link">
                                    {{ __('Stock Adjustment') }}
                                </a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('stock_list'))
                            <li class="{{ @$stock }}">
                                <a class="nav-link" href="{{ route('admin.stock') }}">{{ __('Stock') }}</a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if (auth()->guard('admin')->user()->can('order_list'))
                <li class="{{ @$salesNav }}">
                    <a href="{{ route('admin.sales.index') }}" class="nav-link">
                        <x-heroicon-o-chart-bar class="mr-2 hero-icon" />
                        <span>{{ __('Orders') }}</span>
                    </a>
                </li>
            @endif

            @if (auth()->guard('admin')->user()->can('order_list'))
                <li class="{{ @$draftActive }}">
                    <a href="{{ route('admin.sales.index', ['system_status' => 'draft']) }}" class="nav-link">
                        <x-heroicon-o-chevron-double-down class="mr-2 hero-icon" />
                        <span>{{ __('Draft') }} <span
                                class="badge badge-warning mt-0 draftCount">{{ $draftSales }}</span></span>
                    </a>
                </li>
            @endif

            @if (auth()->guard('admin')->user()->can('Sale_return_adjustment'))
                <li class="{{ @$salesReturnNav }}">
                    <a href="{{ route('admin.returnList.index') }}" class="nav-link">
                        <x-heroicon-o-arrow-uturn-left class="mr-2 hero-icon" />
                        <span>{{ __('Return List') }}</span>
                    </a>
                </li>
            @endif

            @if (auth()->guard('admin')->user()->canany(['sales_ledger', 'purchases_ledger', 'cash_flow']))
                <li class="nav-item dropdown {{ @$accountsActiveClass }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                        <x-heroicon-o-book-open class="mr-2 hero-icon" />
                        <span>{{ __('Accounts') }}</span></a>
                    <ul class="dropdown-menu">
                        @if (auth()->guard('admin')->user()->can('account_management'))
                            <li class="{{ @$manageAccountActiveClass }}">
                                <a class="nav-link"
                                    href="{{ route('admin.manageAccount') }}">{{ __('Manage Account') }}</a>
                            </li>
                        @endif

                        @if (auth()->guard('admin')->user()->can('payment_method_list'))
                            <li class="{{ @$payment_method_active }}">
                                <a class="nav-link"
                                    href="{{ route('admin.accounts.index') }}">{{ __('Accounts') }}</a>
                            </li>
                        @endif

                        @if (auth()->guard('admin')->user()->can('cash_flow'))
                            <li class="{{ @$cashFlowActiveClass }}">
                                <a class="nav-link" href="{{ route('admin.cash.flow') }}">{{ __('Money Flow') }}</a>
                            </li>
                        @endif

                        @if (auth()->guard('admin')->user()->can('general_ledger'))
                            <li class="{{ @$generalLedgerActiveClass }}">
                                <a class="nav-link"
                                    href="{{ route('admin.general.ledger') }}">{{ __('General Ledger') }}</a>
                            </li>
                        @endif

                        @if (auth()->guard('admin')->user()->can('purchases_ledger'))
                            <li class="{{ @$purchasesLedgerActiveClass }}">
                                <a class="nav-link"
                                    href="{{ route('admin.purchases.ledger') }}">{{ __('Purchases Ledger') }}</a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('sales_ledger'))
                            <li class="{{ @$salesLedgerActiveClass }}">
                                <a class="nav-link"
                                    href="{{ route('admin.sales.ledger') }}">{{ __('Sales Ledger') }}</a>
                            </li>
                        @endif

                        @if (auth()->guard('admin')->user()->can('expense_ledger'))
                            <li class="{{ @$expensesLedgerActiveClass }}">
                                <a class="nav-link"
                                    href="{{ route('admin.expense.ledger') }}">{{ __('Expense Ledger') }}</a>
                            </li>
                        @endif

                        @if (auth()->guard('admin')->user()->can('purchases_ledger'))
                            <li class="{{ @$supplierLedgerActiveClass }}">
                                <a class="nav-link"
                                    href="{{ route('admin.purchases.ledger', ['supplierLedger' => 'supplierLedger']) }}">{{ __('Supplier Ledger') }}</a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if (auth()->guard('admin')->user()->can('employee_list'))
                <li class="{{ @$employeeManagementActiveClass }}">
                    <a href="{{ route('admin.employee.index') }}" class="nav-link">
                        <x-heroicon-o-users class="mr-2 hero-icon" />
                        <span>{{ __('Employees') }}</span>
                    </a>
                </li>
            @endif

            @if (auth()->guard('admin')->user()->canany(['expense_category_list', 'expense_list', 'salary_list']))
                <li class="nav-item dropdown {{ @$expenseManagement }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                        <x-heroicon-o-minus-circle class="mr-2 hero-icon" />
                        <span>{{ __('Expense') }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        @if (auth()->guard('admin')->user()->can('expense_category_list'))
                            <li class="{{ @$expenseCategory }}">
                                <a class="nav-link" href="{{ route('admin.expense-category.index') }}">
                                    {{ __('Expense Category') }}
                                </a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('expense_list'))
                            <li class="{{ @$expenseActive }}">
                                <a class="nav-link" href="{{ route('admin.expense.index') }}">
                                    {{ __('Expenses') }}
                                </a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('salary_list'))
                            <li class="{{ @$salaryActiveClass }}">
                                <a href="{{ route('admin.salary.index') }}" class="nav-link">
                                    {{ __('Salary') }}
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if (auth()->guard('admin')->user()->canany(['sales_report_list', 'purchases_report_list']))
                <li class="nav-item dropdown {{ $navReportActiveClass??'' }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                        <x-heroicon-o-document-chart-bar class="mr-2 hero-icon" />
                        <span>{{ __('Report') }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        @if (auth()->guard('admin')->user()->can('profit_loss_report'))
                            <li class="{{ $subNavProfitReportActiveClass??'' }}">
                                <a class="nav-link" href="{{ route('admin.report.profitLossReport') }}">
                                    {{ __('Profit/Loss Report') }}
                                </a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('sales_report_list'))
                            <li class="{{ $subNavSalesReportActiveClass??'' }}">
                                <a class="nav-link" href="{{ route('admin.sales.report') }}">
                                    {{ __('Sales Report') }}
                                </a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('purchases_report_list'))
                            <li class="{{ $subNavPurchasesReportActiveClass??'' }}">
                                <a class="nav-link" href="{{ route('admin.purchases.report') }}">
                                    {{ __('Purchase Report') }}
                                </a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('stock_report_list'))
                            <li class="{{ $subNavStockReportActiveClass??'' }}">
                                <a class="nav-link" href="{{ route('admin.stock.report') }}">
                                    {{ __('Stock Report') }}
                                </a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('expense_report_list'))
                            <li class="{{ $subNavExpenseReportActiveClass??'' }}">
                                <a class="nav-link" href="{{ route('admin.expense.report') }}">
                                    {{ __('Expense Report') }}
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if (auth()->guard('admin')->user()->canany([
                        'site_setting',
                        'language_list',
                        'email_configure',
                        'email_template',
                        'database_backup',
                        'cache_clear',
                    ]))
                <li class="nav-item dropdown {{ $navGeneralSettingsActiveClass??'' }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                        <x-heroicon-o-cog-6-tooth class="mr-2 hero-icon" />
                        <span>{{ __('Configuration') }}</span></a>
                    <ul class="dropdown-menu">
                        @if (auth()->guard('admin')->user()->can('site_setting'))
                            <li class="{{ $subNavGeneralSettingsActiveClass??'' }}">
                                <a class="nav-link"
                                    href="{{ route('admin.general.setting') }}">{{ __('General Settings') }}</a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('integration_access'))
                            <li class="{{ $subNavIntegrationGeneralSettingsActiveClass??'' }}">
                                <a class="nav-link"
                                    href="{{ route('admin.general.integrationSetting') }}">{{ __('Integrations') }}</a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('email_configure'))
                            <li class="{{ $subNavEmailConfigActiveClass??'' }}">
                                <a class="nav-link"
                                    href="{{ route('admin.email.config') }}">{{ __('Email Configure') }}</a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('email_template'))
                            <li class="{{ $subNavEmailTemplatesActiveClass??'' }}">
                                <a class="nav-link"
                                    href="{{ route('admin.email.templates') }}">{{ __('Notify Templates') }}</a>
                            </li>
                        @endif

                        @if (auth()->guard('admin')->user()->can('language_list'))
                            <li class="{{ $subNavManageLanguageActiveClass??'' }}">
                                <a href="{{ route('admin.language.index') }}" class="nav-link">
                                    <span>{{ __('Manage Language') }}</span>
                                </a>
                            </li>
                        @endif
                        {{-- @if (auth()->guard('admin')->user()->can('database_backup'))
                            <li>
                                <a class="nav-link"
                                    href="{{ route('admin.general.database') }}">{{ __('Database Backup') }}</a>
                            </li>
                        @endif --}}
                        @if (auth()->guard('admin')->user()->can('cache_clear'))
                            <li>
                                <a class="nav-link"
                                    href="{{ route('admin.general.cacheclear') }}">{{ __('Cache Clear') }}</a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if (auth()->guard('admin')->user()->canany(['download_manager_list', 'notification_center_list', 'recycle_bin_list', 'activity_log_list']))
                <li class="nav-item dropdown {{ @$activities_active }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                        <x-heroicon-o-lifebuoy class="mr-2 hero-icon" />
                        <span>{{ __('Activities') }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        @if (auth()->guard('admin')->user()->can('download_manager_list'))
                            <li class="{{ @$downloadManagerActiveClass }}">
                                <a href="{{ route('admin.download.manager.index') }}" class="nav-link">
                                    <span>{{ __('Download Manager') }}</span>
                                </a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('notification_center_list'))
                            <li class="{{ @$notificationCenterActiveClass }}">
                                <a href="{{ route('admin.notifications.index') }}" class="nav-link">
                                    <span>{{ __('Notifications') }}</span>
                                </a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('recycle_bin_list'))
                            <li class="{{ @$recycleBinActiveClass }}">
                                <a href="{{ route('admin.recycle_bin.index') }}" class="nav-link">
                                    <span>{{ __('Recycle Bin') }}</span>
                                </a>
                            </li>
                        @endif
                        @if (auth()->guard('admin')->user()->can('activity_log_list'))
                            <li class="{{ @$activityLogActiveClass }}">
                                <a href="{{ route('admin.activity.log') }}" class="nav-link">
                                    <span>{{ __('Activity Logs') }}</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
        </ul>
    </aside>
</div>
