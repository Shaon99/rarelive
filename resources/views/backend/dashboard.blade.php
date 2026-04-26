@extends('backend.layout.master')
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header text-right p-2">
                <div class="mt-2">
                    <div class="d-flex w-100 flex-nowrap flex-md-wrap align-items-end overflow-auto" style="gap: 0.10rem;">
                        <div class="d-flex flex-row flex-wrap align-items-end" style="gap:0.10rem;">
                            <label class="selectgroup-item mb-0 flex-shrink-0">
                                <input type="radio" name="value" class="selectgroup-input">
                                <span class="selectgroup-button" data-report="all">{{ __('All') }}</span>
                            </label>
                            <label class="selectgroup-item mb-0 flex-shrink-0">
                                <input type="radio" name="value" class="selectgroup-input" checked>
                                <span class="selectgroup-button" data-report="today">{{ __('Today') }}</span>
                            </label>
                            <label class="selectgroup-item mb-0 flex-shrink-0">
                                <input type="radio" name="value" class="selectgroup-input">
                                <span class="selectgroup-button" data-report="seven">{{ __('Last Week') }}</span>
                            </label>
                            <label class="selectgroup-item mb-0 flex-shrink-0">
                                <input type="radio" name="value" class="selectgroup-input">
                                <span class="selectgroup-button" data-report="month">{{ __('This Month') }}</span>
                            </label>
                            <label class="selectgroup-item mb-0 flex-shrink-0">
                                <input type="radio" name="value" class="selectgroup-input">
                                <span class="selectgroup-button" data-report="last_month">{{ __('Last Month') }}</span>
                            </label>
                            <label class="selectgroup-item mb-0 flex-shrink-0">
                                <input type="radio" name="value" class="selectgroup-input">
                                <span class="selectgroup-button" data-report="year">{{ __('This Year') }}</span>
                            </label>
                        </div>
                        <div class="mt-2 mt-md-0 ml-md-3 d-flex align-items-stretch"
                            style="min-width: 280px; gap: 0.35rem;">
                            <input type="text" class="form-control flex-grow-1" id="dashboardDateRange"
                                name="dashboard_date_range" placeholder="{{ __('Select custom date range') }}"
                                autocomplete="off">
                            <button type="button" class="btn btn-outline-danger flex-shrink-0 text-nowrap"
                                id="dashboard-filter-reset">{{ __('Reset') }}</button>
                        </div>
                    </div>
                </div>
                <div class="section-header-breadcrumb d-none d-md-block">
                    <div class="breadcrumb-item custom-b ml-auto mr-2">
                        {{ __($pageTitle) }}</div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-lg-3 col-md-3 col-sm-12 col-12 mb-2 mb-lg-3">
                            <div class="metric-card d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="metric-value total_order fs-4 fw-bold">
                                        {{ currency_format($total_sales) }}
                                    </div>
                                    <div class="metric-label">{{ __('Orders') }} (<span
                                            class="total_sales_count">{{ $total_sales_count }}</span>)</div>
                                </div>
                                <div class="metric-icon">
                                    <i class="fas fa-chart-line fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-12 col-12 mb-2 mb-lg-3">
                            <div class="metric-card d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="metric-value total_sales_draft fs-4 fw-bold">
                                        {{ currency_format($total_sales_draft) }}
                                    </div>
                                    <div class="metric-label">{{ __('Orders Draft') }} (<span
                                            class="total_sales_draft_count">{{ $total_sales_draft_count }}</span>)</div>
                                </div>
                                <div class="metric-icon">
                                    <i class="fas fa-solid fa-spinner fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-12 col-12 mb-2 mb-lg-3">
                            <div class="metric-card d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="metric-value order_pending fs-4 fw-bold">
                                        {{ currency_format($total_pending) }}

                                    </div>
                                    <div class="metric-label">
                                        {{ __('Orders Pending') }} (<span
                                            class="total_pending_count">{{ $total_pending_count }}</span>)
                                    </div>
                                </div>
                                <div class="metric-icon">
                                    <i class="fas fa-solid fa-spinner fa-2x"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-12 col-12 mb-2 mb-lg-3">
                            <div class="metric-card d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="metric-value order_delivered fs-4 fw-bold">
                                        {{ currency_format($total_delivered) }}
                                    </div>
                                    <div class="metric-label">
                                        {{ __('Order Delivered') }} (<span
                                            class="total_delivered_count">{{ $total_delivered_count }}</span>)
                                    </div>
                                </div>
                                <div class="metric-icon">
                                    <i class="fas fa-solid fa-truck-fast fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-12 col-12 mb-2 mb-lg-3">
                            <div class="metric-card d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="metric-value partial_delivered fs-4 fw-bold">
                                        {{ currency_format($total_partial_sales) }}
                                    </div>
                                    <div class="metric-label">
                                        {{ __('Partial Delivered') }} (<span
                                            class="partial_delivered_count">{{ $total_partial_sales_count }}</span>)
                                    </div>
                                </div>
                                <div class="metric-icon">
                                    <i class="fas fa-solid fa-truck-fast fa-2x"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-12 col-12 mb-2 mb-lg-3">
                            <div class="metric-card d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="metric-value order_cancelled fs-4 fw-bold">
                                        {{ currency_format($total_cancelled) }}
                                    </div>
                                    <div class="metric-label">
                                        {{ __('Orders Cancelled') }} (<span
                                            class="total_cancelled_count">{{ $total_cancelled_count }}</span>)
                                    </div>
                                </div>
                                <div class="metric-icon">
                                    <i class="fas fa-solid fa-rotate-left fa-2x"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-12 col-12 mb-2 mb-lg-3">
                            <div class="metric-card d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="metric-value total_expense fs-4 fw-bold">
                                        {{ currency_format($total_expense) }}
                                    </div>
                                    <div class="metric-label">
                                        {{ __('Expense') }}
                                    </div>
                                </div>
                                <div class="metric-icon">
                                    <i class="fas fa-solid fa-dollar-sign fa-2x"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-12 col-12 mb-2 mb-lg-3">
                            <div class="metric-card d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="metric-value total_salary fs-4 fw-bold">
                                        {{ currency_format($total_salary) }}
                                    </div>
                                    <div class="metric-label">
                                        {{ __('Salary Expense') }}
                                    </div>
                                </div>
                                <div class="metric-icon">
                                    <i class="fas fa-solid fa-dollar-sign fa-2x"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-3 col-sm-12 col-12 mb-2 mb-lg-3">
                            <div class="metric-card d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="metric-value total_purchases fs-4 fw-bold">
                                        {{ currency_format($total_purchases) }}
                                    </div>
                                    <div class="metric-label">
                                        {{ __('Purchases') }}
                                    </div>
                                </div>
                                <div class="metric-icon">
                                    <i class="fas fa-solid fa-truck-fast fa-2x"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-3 col-sm-12 col-12 mb-2 mb-lg-3">
                            <div class="metric-card d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="metric-value refund fs-4 fw-bold">
                                        {{ currency_format($return_total_purchases) }}
                                    </div>
                                    <div class="metric-label">
                                        {{ __('Purchases Refund') }}
                                    </div>
                                </div>
                                <div class="metric-icon">
                                    <i class="fas fa-solid fa-dollar-sign fa-2x"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-2 col-sm-12 col-12 mb-2 mb-lg-3">
                            <div class="metric-card d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="metric-value revenue fs-4 fw-bold">
                                        {{ currency_format($total_profit) }}
                                    </div>
                                    <div class="metric-label">
                                        {{ __('Account Balance') }}
                                    </div>
                                </div>
                                <div class="metric-icon">
                                    <i class="fas fa-solid fa-dollar-sign fa-2x"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-2 col-sm-12 col-12 mb-2 mb-lg-3">
                            <div class="metric-card d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="f-s fs-4 fw-bold">
                                        {{ currency_format($assetValue) }}
                                    </div>
                                    <div class="metric-label">
                                        {{ __('Asset Value') }}
                                    </div>
                                </div>
                                <div class="metric-icon">
                                    <i class="fas fa-solid fa-dollar-sign fa-2x"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-2 col-sm-12 col-12 mb-2 mb-lg-3">
                            <div class="metric-card d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="f-s fs-4 fw-bold">
                                        {{ currency_format($total_profit + $assetValue) }}

                                    </div>
                                    <div class="metric-label">
                                        {{ __('Total Balance') }}
                                    </div>
                                </div>
                                <div class="metric-icon">
                                    <i class="fas fa-solid fa-dollar-sign fa-2x"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-4 col-sm-12 col-12 mb-2 mb-lg-3">
                            <div class="metric-card d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="f-s fs-4 fw-bold">
                                        {{ $currentMonthDelivered }}
                                    </div>
                                    <div class="metric-label">
                                        @if ($deliveredChangeType === 'increase')
                                            <span class="badge badge-success d-inline-block delivered-change-badge">
                                                <i class="bi bi-arrow-up"></i>
                                                <span>{{ $deliveredPercentageChange }}% Inc from Last Month</span>
                                            </span>
                                        @elseif ($deliveredChangeType === 'decrease')
                                            <span class="badge badge-warning d-inline-block delivered-change-badge">
                                                <i class="bi bi-arrow-down"></i>
                                                <span>{{ $deliveredPercentageChange }}% Dec from Last Month</span>
                                            </span>
                                        @else
                                            <span class="badge badge-info d-inline-block delivered-change-badge">
                                                No Change from Last Month
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="metric-icon">
                                    <i class="fas fa-solid fa-truck-fast fa-2x"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="text-capitalize">{{ __('Monthly orders delivered and orders cancelled') }}
                                        {{ $dashboardChartYear }}</h4>
                                </div>
                                <div class="card-body pt-0">
                                    <canvas id="salesReturnChart" style="height: 300px; width: 100%;"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="text-capitalize">{{ __('Monthly order delivered by employees') }}
                                        {{ $dashboardChartYear }}</h4>
                                </div>
                                <div class="card-body pt-0">
                                    <canvas id="completedOrdersChart" style="height: 300px; width: 100%;"></canvas>
                                </div>
                            </div>
                        </div>
                        @if ($general->pos_lead_on_off == 1)
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="text-capitalize">{{ __('Monthly order delivered by lead') }}
                                            {{ $dashboardChartYear }}</h4>
                                    </div>
                                    <div class="card-body pt-0">
                                        <canvas id="completedOrdersChartLead"
                                            style="height: 300px; width: 100%;"></canvas>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="text-capitalize">{{ __('Monthly Expenses') }}
                                        <?php echo date('F Y'); ?>
                                    </h4>
                                </div>
                                <div class="card-body pt-0">
                                    <canvas id="expensePieChart" style="height: 300px; width: 100%;"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="text-capitalize">{{ __('Top 3 Highest Selling product') }}</h4>
                                </div>
                                <div class="card-body pt-0 table-responsive">
                                    <table class="table tab-bordered">
                                        <thead>
                                            <tr>
                                                <th>{{ __('SL') }}</th>
                                                <th>{{ __('Product') }}</th>
                                                <th>{{ __('Product Code') }}</th>
                                                <th>{{ __('Product Sold') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($topSellingProducts as $index => $product)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $product->name }}</td>
                                                    <td>{{ $product->code }}</td>
                                                    <td>{{ $product->total_quantity_sold }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h4 class="text-capitalize">{{ __('Top 5 Highest Selling Location') }}</h4>
                                </div>
                                <div class="card-body pt-0 table-responsive">
                                    <table class="table tab-bordered">
                                        <thead>
                                            <tr>
                                                <th>{{ __('SL') }}</th>
                                                <th>{{ __('City') }}</th>
                                                <th>{{ __('Product Sold') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($topSellingByLocation as $index => $sale)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $sale->city }}</td>
                                                    <td>{{ $sale->total_quantity_sold }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @if ($general->pos_platform_on_off == 1)
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="text-capitalize">{{ __('Monthly orders by platform') }}
                                            {{ $dashboardChartYear }}</h4>
                                    </div>
                                    <div class="card-body pt-0">
                                        <canvas id="salesChartByPlatform" style="height: 300px; width: 100%;"></canvas>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="text-capitalize">{{ __('Recent Transaction') }}</h4>
                                </div>
                                <div class="card-body pt-0 table-responsive">
                                    <table class="table tab-bordered">
                                        <thead>
                                            <tr>
                                                <th>{{ __('SL') }}</th>
                                                <th>Date</th>
                                                <th>{{ __('Invoice') }}</th>
                                                <th>{{ __('Created By') }}</th>
                                                <th>{{ __('Customer') }}</th>
                                                <th>{{ __('Phone') }}</th>
                                                <th title="Status">Status</th>
                                                @if ($general->enable_online_deliver)
                                                    <th title="Consignment Id">Consignments ID</th>
                                                    <th title="Shipping Status">Shipping Status</th>
                                                @endif
                                                <th title="Cash On Delivery">Due</th>
                                                <th>Paid</th>
                                                <th>Grand Total</th>
                                                <th>Method</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <div id="loading-overlay" style="display: none;" class="loading-overlay">
                                            </div>
                                            @forelse ($recentSales as $item)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $item->created_at->format('d M, y h:i A') }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.invoice', $item->id) }}">
                                                            {{ $item->invoice_no }}
                                                        </a>
                                                    </td>

                                                    <td>{{ @$item->user->name }}</td>
                                                    <td>{{ @$item->customer->name }}</td>
                                                    <td>{{ @$item->customer->phone }}</td>
                                                    <td>
                                                        @if ($item->system_status === 'pending')
                                                            <span class="badge badge-warning">{{ __('Shipping') }}</span>
                                                        @elseif($item->system_status === 'draft')
                                                            <span class="badge badge-info">{{ __('Draft') }}</span>
                                                        @elseif($item->system_status === 'completed')
                                                            <span class="badge badge-success">{{ __('Delivered') }}</span>
                                                        @elseif($item->system_status === 'cancelled')
                                                            <span class="badge badge-danger">{{ __('Cancelled') }}</span>
                                                        @endif
                                                    </td>
                                                    @if ($general->enable_online_deliver)
                                                        <td>{{ $item->consignment_id ?? 'N/A' }}</td>
                                                        <td><span
                                                                class="badge badge-success">{{ Str::headline($item->status ?? 'N/A') }}</span>
                                                        </td>
                                                    @endif

                                                    <td>{{ currency_format($item->due_amount) }}
                                                    </td>
                                                    <td>{{ currency_format($item->paid_amount) }}
                                                    </td>
                                                    <td>{{ currency_format($item->grand_total) }}
                                                    </td>
                                                    <td class="text-center">
                                                        @php
                                                            $type = $item->paymentMethod->type ?? '';
                                                        @endphp

                                                        @switch($type)
                                                            @case('CASH')
                                                                <span
                                                                    class="badge badge-success">{{ $item->paymentMethod->name ?? 'N/A' }}</span>
                                                            @break

                                                            @case('STEADFAST')
                                                                <span
                                                                    class="badge badge-warning">{{ $item->paymentMethod->name ?? 'N/A' }}
                                                                    (COD)
                                                                </span>
                                                            @break

                                                            @case('BANK')
                                                                <span
                                                                    class="badge badge-info">{{ $item->paymentMethod->name ?? 'N/A' }}</span>
                                                            @break

                                                            @case('MFS')
                                                                <span
                                                                    class="badge badge-primary">{{ $item->paymentMethod->name ?? 'N/A' }}</span>
                                                            @break

                                                            @default
                                                                <span
                                                                    class="badge badge-dark">{{ $item->paymentMethod->name ?? 'COD' }}</span>
                                                        @endswitch
                                                    </td>
                                                </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="100%" class="text-center">no record found</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    @endsection

    @push('script')
        <script src="{{ asset('assets/admin/js/chart.min.js') }}"></script>

        <script>
            const monthlyLabels = @json($monthlyLabels);
            const monthlySalesData = @json($monthly_sales_data);
            const monthlyReturnData = @json($monthly_return_data);

            // --- Sales vs Return Bar Chart ---
            new Chart(document.getElementById('salesReturnChart'), {
                type: 'bar',
                data: {
                    labels: monthlyLabels,
                    datasets: [{
                            label: 'Orders Delivered',
                            data: monthlySalesData,
                            backgroundColor: '#c5caff',
                            borderRadius: 5,
                            barThickness: 15,
                            borderSkipped: false,
                        },
                        {
                            label: 'Orders Cancelled',
                            data: monthlyReturnData,
                            backgroundColor: '#000',
                            borderRadius: 5,
                            barThickness: 15,
                            borderSkipped: false,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        yAxes: [{
                            gridLines: {
                                drawBorder: false,
                                color: '#f2f2f2'
                            },
                            ticks: {
                                beginAtZero: true,
                                stepSize: 10000
                            }
                        }],
                        xAxes: [{
                            gridLines: {
                                display: false
                            }
                        }]
                    },
                    legend: {
                        display: true
                    }
                }
            });

            // --- Sales by Platform Line Chart ---
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('salesChartByPlatform');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: @json($salesByPlatform['labels']),
                            datasets: @json($salesByPlatform['datasets']),
                        },
                        options: {
                            responsive: true,
                            scales: {
                                yAxes: [{
                                    gridLines: {
                                        drawBorder: false,
                                        color: '#f2f2f2'
                                    },
                                    ticks: {
                                        beginAtZero: true,
                                        stepSize: 10000
                                    }
                                }],
                                xAxes: [{
                                    gridLines: {
                                        display: false
                                    }
                                }]
                            },
                            legend: {
                                display: true
                            }
                        }
                    });
                }
            });

            // --- Completed Orders Chart (User) ---
            new Chart(document.getElementById('completedOrdersChart'), {
                type: 'line',
                data: {
                    labels: monthlyLabels,
                    datasets: @json($completedOrdersByUserDatasets ?? [])
                },
                options: {
                    responsive: true,
                    scales: {
                        yAxes: [{
                            gridLines: {
                                drawBorder: false,
                                color: '#f2f2f2'
                            },
                            ticks: {
                                beginAtZero: true,
                                stepSize: 10000
                            }
                        }],
                        xAxes: [{
                            gridLines: {
                                display: false
                            }
                        }]
                    },
                    legend: {
                        display: true
                    }
                }
            });

            // --- Completed Orders Chart (Lead) ---
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('completedOrdersChartLead');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: monthlyLabels,
                            datasets: @json($completedOrdersByLeadDatasets ?? [])
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: true
                            },
                            tooltips: {
                                callbacks: {
                                    label: function(tooltipItem, data) {
                                        const dataset = data.datasets[tooltipItem.datasetIndex];
                                        const value = dataset.data[tooltipItem.index];
                                        const total = data.datasets.reduce((sum, ds) => sum + ds.data[
                                            tooltipItem.index], 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) +
                                            '%' : '0%';
                                        return `${dataset.label}: ${value} (${percentage})`;
                                    }
                                }
                            },
                            scales: {
                                yAxes: [{
                                    gridLines: {
                                        drawBorder: false,
                                        color: '#f2f2f2'
                                    },
                                    ticks: {
                                        beginAtZero: true,
                                        stepSize: 10000
                                    }
                                }],
                                xAxes: [{
                                    gridLines: {
                                        display: false
                                    }
                                }]
                            }
                        }
                    });
                } else {}
            });


            // --- Expenses Pie Chart ---
            new Chart(document.getElementById('expensePieChart'), {
                type: 'pie',
                data: {
                    labels: {!! json_encode($expenseCategories) !!},
                    datasets: [{
                        label: 'Expenses',
                        data: {!! json_encode($expenseTotals) !!},
                        backgroundColor: [
                            '#c5caff', '#99ff9c', '#ffe799', '#ff9999',
                            '#5f7bff', '#d090ff', '#b1f0ff', '#90d086'
                        ],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    legend: {
                        position: 'right'
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                const label = data.labels[tooltipItem.index] || '';
                                const value = data.datasets[0].data[tooltipItem.index];
                                const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(2) : '0.00';
                                return `${label}: {{ $general->site_currency }}${value.toLocaleString()} (${percentage}%)`;
                            }
                        }
                    }
                }
            });

            $(document).ready(function() {
                function formatDashCount(value) {
                    var n = parseInt(value, 10);
                    return isNaN(n) ? '0' : String(n);
                }

                function runDashboardFilter(requestData) {
                    $.ajax({
                        url: "{{ route('admin.filter') }}",
                        type: "GET",
                        data: requestData,
                        dataType: "json",
                        beforeSend: function() {
                            $('.metric-value').each(function() {
                                $(this).data('original', $(this).html());
                                $(this).html('<span class="skeleton-loader"></span>');
                            });
                        },
                        success: function(response) {
                            if (response) {
                                $('.total_order').text(parseFloat(response.total_sales || 0)
                                    .toFixed(2) + ' {{ $general->site_currency }}');
                                $('.total_sales_count').text(formatDashCount(response.total_sales_count));
                                $('.order_delivered').text(parseFloat(response.order_delivered || 0)
                                    .toFixed(2) + ' {{ $general->site_currency }}');
                                $('.partial_delivered').text(parseFloat(response
                                        .partial_delivered || 0).toFixed(2) +
                                    ' {{ $general->site_currency }}');
                                $('.order_pending').text(parseFloat(response.order_pending || 0)
                                    .toFixed(2) + ' {{ $general->site_currency }}');
                                $('.total_sales_draft').text(parseFloat(response
                                        .total_sales_draft || 0).toFixed(2) +
                                    ' {{ $general->site_currency }}');
                                $('.order_cancelled').text(parseFloat(response.order_cancelled || 0)
                                    .toFixed(2) + ' {{ $general->site_currency }}');

                                $('.total_delivered_count').text(formatDashCount(
                                    response.total_delivered_count));
                                $('.partial_delivered_count').text(formatDashCount(
                                    response.partial_delivered_count));
                                $('.total_pending_count').text(formatDashCount(
                                    response.order_pending_count));
                                $('.total_cancelled_count').text(formatDashCount(
                                    response.order_cancelled_count));
                                $('.total_sales_draft_count').text(formatDashCount(
                                    response.total_sales_draft_count));

                                $('.total_expense').text(parseFloat(response.total_expense || 0)
                                    .toFixed(2) + ' {{ $general->site_currency }}');
                                $('.total_salary').text(parseFloat(response.total_salary || 0)
                                    .toFixed(2) + ' {{ $general->site_currency }}');
                                $('.total_purchases').text(parseFloat(response.total_purchases || 0)
                                    .toFixed(2) + ' {{ $general->site_currency }}');
                                $('.refund').text(parseFloat(response.refund || 0).toFixed(2) +
                                    ' {{ $general->site_currency }}');
                                $('.revenue').text(parseFloat(response.revenue || 0).toFixed(2) +
                                    ' {{ $general->site_currency }}');
                            }
                        },
                        error: function() {
                            showToast('Something went wrong!', 'error');
                            $('.metric-value').each(function() {
                                $(this).html($(this).data('original'));
                            });
                        },
                        complete: function() {
                            $('.metric-value .skeleton-loader').remove();
                        }
                    });
                }

                var $dashboardDateRange = $('#dashboardDateRange');
                $dashboardDateRange.daterangepicker({
                    opens: 'right',
                    autoUpdateInput: false,
                    maxDate: moment(),
                    startDate: moment().startOf('month'),
                    endDate: moment(),
                    locale: {
                        cancelLabel: @json(__('Clear')),
                        format: 'YYYY-MM-DD'
                    }
                });
                $dashboardDateRange.on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                        'YYYY-MM-DD'));
                    runDashboardFilter({
                        filter: 'custom',
                        start_date: picker.startDate.format('YYYY-MM-DD'),
                        end_date: picker.endDate.format('YYYY-MM-DD')
                    });
                });
                $dashboardDateRange.on('cancel.daterangepicker', function() {
                    $(this).val('');
                });

                $('#dashboard-filter-reset').on('click', function() {
                    $dashboardDateRange.val('');
                    var drp = $dashboardDateRange.data('daterangepicker');
                    if (drp) {
                        drp.setStartDate(moment().startOf('month'));
                        drp.setEndDate(moment());
                    }
                    $('.selectgroup-button[data-report="today"]').prev('.selectgroup-input').prop('checked',
                        true);
                    runDashboardFilter({
                        filter: 'today'
                    });
                });

                $('.selectgroup-input').on('change', function() {
                    $dashboardDateRange.val('');
                    var drp = $dashboardDateRange.data('daterangepicker');
                    if (drp) {
                        drp.setStartDate(moment().startOf('month'));
                        drp.setEndDate(moment());
                    }
                    var filterValue = $(this).next('.selectgroup-button').data('report');
                    runDashboardFilter({
                        filter: filterValue
                    });
                });
            });
        </script>
    @endpush
