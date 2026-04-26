@extends('backend.layout.master')
@push('style')
    <style>
        tfoot th {
            height: 25px !important;
        }


        @media print {
            #print {
                display: none;
            }

            .no-print {
                display: none !important;
            }

            .print-section-flex {
                display: flex !important;
                flex-wrap: wrap;
                gap: 10px;
            }

            .print-section-flex p {
                flex: 0 0 48%;
                /* You can adjust this for 2-per-row layout */
                margin: 0 0 6px;
            }

            .border-r {
                border-right: none !important;
                /* Ensures right border is visible */
            }

            body {
                background: #fff !important;
                /* Ensures white background */
                -webkit-print-color-adjust: economy !important;
                print-color-adjust: economy !important;
            }

            .table thead th,
            .table tfoot th,
            .table tbody td {
                /* Light gray or your desired color */
                color: #000 !important;
                -webkit-print-color-adjust: exact;
            }

            .table th,
            .table td {
                border: 1px solid #ccc !important;
                /* Ensures border is visible */
            }

            .badge {
                background-color: transparent !important;
                color: black !important;
                border: none !important;
                text-transform: capitalize !important;
                font-size: 18px !important;
                font-weight: 400 !important;
            }
        }
    </style>
@endpush
@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ __($pageTitle) }}</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">{{ __($pageTitle) }}</div>
                    <div class="breadcrumb-item active"><a
                            href="{{ route('admin.purchases.index') }}">{{ __('Purchases') }}</a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-8">
                        <div class="card-body">
                            <div id="print_section">
                                <div class="row">
                                    <div class="col-md-3 border-r">
                                        <div class="print-section-flex">
                                            <p class="mb-2"><strong>{{ __('Customer:') }}</strong>
                                                {{ @$singleSale->customer->name ? $singleSale->customer->name : 'walking Customer' }}
                                            </p>
                                            <p class="mb-2"><strong>{{ __('Invoice:') }}</strong>
                                                {{ $singleSale->invoice_no ?? 'N/A' }}</p>

                                            <p class="mb-2"><strong>{{ __('Date:') }}</strong>
                                                {{ $singleSale->created_at->format('d/m/Y') }}</p>

                                            <p class="mb-2"><strong>{{ __('Branch:') }}</strong>
                                                {{ $singleSale->warehouse->name ?? 'N/A' }}</p>

                                            <p class="mb-2"><strong>{{ __('Payment Status:') }}</strong>
                                                @if ($singleSale->payment_status == 0)
                                                    <span class="badge badge-danger">{{ __('Due') }}</span>
                                                @elseif($singleSale->payment_status == 1)
                                                    <span class="badge badge-success">{{ __('Completed') }}</span>
                                                @endif
                                            </p>
                                            @php
                                                $type = $singleSale->paymentMethod->type ?? 'CASH'; // default fallback
                                            @endphp
                                            <p class="mb-2"><strong>{{ __('Payment Method:') }}</strong>
                                                @switch($type)
                                                    @case('CASH')
                                                        <span
                                                            class="badge badge-success">{{ $singleSale->paymentMethod->name ?? 'N/A' }}</span>
                                                    @break

                                                    @case('STEADFAST')
                                                        <span
                                                            class="badge badge-warning">{{ $singleSale->paymentMethod->name ?? 'N/A' }}
                                                            (COD)</span>
                                                    @break

                                                    @case('BANK')
                                                        <span
                                                            class="badge badge-info">{{ $singleSale->paymentMethod->name ?? 'N/A' }}</span>
                                                    @break

                                                    @case('MFS')
                                                        <span
                                                            class="badge badge-primary">{{ $singleSale->paymentMethod->name ?? 'N/A' }}</span>
                                                    @break

                                                    @default
                                                        <span
                                                            class="badge badge-secondary">{{ $singleSale->paymentMethod->name ?? 'N/A' }}</span>
                                                @endswitch
                                            </p>
                                            <div class="mt-3 d-flex justify-content-between no-print">
                                                <button class="btn btn-primary btn-sm" id="print"><i
                                                        class="fas fa-print mr-2"></i> {{ __('Print Sales') }}</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-9 py-4">
                                        @php
                                            function format_currency($amount, $currency)
                                            {
                                                return number_format($amount, 2) . ' ' . $currency;
                                            }

                                            $totalItemProfit = 0;

                                            foreach ($singleSale->salesProduct as $item) {
                                                $purchasePrice = $item->product->purchase_price;
                                                $salePrice = $item->sale_price;
                                                $quantity = $item->quantity;
                                                $discount =
                                                    $item->discount_type === 'percentage'
                                                        ? ($salePrice * $item->discount) / 100
                                                        : $item->discount;

                                                $netSalePrice = $salePrice - $discount;
                                                $unitProfit = $netSalePrice - $purchasePrice;
                                                $totalItemProfit += $unitProfit * $quantity;
                                            }

                                            $invoiceProfit =
                                                $totalItemProfit - $singleSale->shipping_cost - $singleSale->cod_charge;
                                        @endphp

                                        <table class="table table-bordered table-responsive">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Product Name') }}</th>
                                                    <th>{{ __('Quantity') }}</th>
                                                    <th>{{ __('Purchases Unit Price') }}</th>
                                                    <th>{{ __('Current Sale Price') }}</th>
                                                    <th>{{ __('Discount') }}</th>
                                                    <th>{{ __('Sub Total') }}</th>
                                                    <th>{{ __('Net Profit') }}</th>
                                                    <th class="text-right">{{ __('Profit') }}</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                @forelse ($singleSale->salesProduct as $item)
                                                    @php
                                                        $purchasePrice = $item->product->purchase_price;
                                                        $salePrice = $item->sale_price;
                                                        $quantity = $item->quantity;
                                                        $discount =
                                                            $item->discount_type === 'percentage'
                                                                ? ($salePrice * $item->discount) / 100
                                                                : $item->discount;
                                                        $netSalePrice = $salePrice - $discount;
                                                        $unitProfit = $netSalePrice - $purchasePrice;
                                                        $totalProfit = $unitProfit * $quantity;
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $item->product->name ?? 'N/A' }}</td>
                                                        <td>{{ $quantity }}</td>
                                                        <td>{{ format_currency($purchasePrice, $general->site_currency) }}
                                                        </td>
                                                        <td>{{ format_currency($salePrice, $general->site_currency) }}</td>
                                                        <td>{{ number_format($item->discount, 2) . ' ' . ($item->discount_type === 'percentage' ? '%' : $general->site_currency) }}
                                                        </td>
                                                        <td>{{ format_currency($item->total, $general->site_currency) }}
                                                        </td>
                                                        <td>{{ format_currency($unitProfit, $general->site_currency) }}
                                                        </td>
                                                        <td class="text-right">
                                                            {{ format_currency($totalProfit, $general->site_currency) }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="9" class="text-center">No products found.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>

                                            <tfoot class="border-top">
                                                <tr>
                                                    <th colspan="1">{{ __('Items') }} {{ $singleSale->total_qty }}
                                                    </th>
                                                    <th colspan="4" class="text-right">{{ __('Subtotal') }} :</th>
                                                    <th colspan="5" class="text-right">
                                                        {{ format_currency($singleSale->sub_total, $general->site_currency) }}
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="5" class="text-right">{{ __('Discount') }} (-) :</th>
                                                    <th colspan="6" class="text-right">
                                                        {{ format_currency($singleSale->discount, $general->site_currency) }}
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="5" class="text-right">{{ __('Shipping') }} (+) :</th>
                                                    <th colspan="6" class="text-right">
                                                        {{ format_currency($singleSale->system_delivery_charge, $general->site_currency) }}
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="5" class="text-right">{{ __('Grand Total') }} :</th>
                                                    <th colspan="6" class="text-right">
                                                        {{ format_currency($singleSale->grand_total, $general->site_currency) }}
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="5" class="text-right">{{ __('Paid Amount') }} :</th>
                                                    <th colspan="6" class="text-right">
                                                        {{ format_currency($singleSale->paid_amount, $general->site_currency) }}
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="5" class="text-right">{{ __('Due Amount') }} :</th>
                                                    <th colspan="6" class="text-right">
                                                        {{ format_currency($singleSale->due_amount, $general->site_currency) }}
                                                    </th>
                                                </tr>
                                            </tfoot>
                                        </table>

                                        <div class="my-3">
                                            <h6>Invoice Profit</h6>
                                            <p>
                                                Profit: {{ format_currency($totalItemProfit, $general->site_currency) }}
                                                &nbsp;|&nbsp;
                                                Delivery Charge:
                                                {{ format_currency($singleSale->shipping_cost, $general->site_currency) }}
                                                &nbsp;|&nbsp;
                                                COD Charge:
                                                {{ format_currency($singleSale->cod_charge, $general->site_currency) }}
                                                &nbsp;|&nbsp;
                                                <strong>Net Profit:
                                                    {{ format_currency($invoiceProfit, $general->site_currency) }}</strong>
                                            </p>
                                        </div>


                                        @if ($singleSale->transactions)
                                            <div class="mt-4">
                                                <h6>Transaction History</h6>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>SL#</th>
                                                            <th>{{ __('Transaction ID') }}</th>
                                                            <th>{{ __('Amount') }}</th>
                                                            <th>{{ __('Payment Method') }}</th>
                                                            <th>{{ __('Remark') }}</th>
                                                            <th>{{ __('Date') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($singleSale->transactions as $item)
                                                            @if ($item->paymentMethod)
                                                                <tr>
                                                                    <td>{{ $loop->iteration }}</td>
                                                                    <td>{{ $item->id }}</td>
                                                                    <td>{{ number_format($item->amount, 2) }}
                                                                        {{ $general->site_currency }}</td>
                                                                    <td>
                                                                        <span class="badge badge-primary">
                                                                            {{ __($item->paymentMethod->name ?? '') }}
                                                                        </span>
                                                                    </td>
                                                                    <td>{{ $item->note }}</td>
                                                                    <td>{{ $item->created_at->format('d M Y H:i A') }}</td>
                                                                </tr>
                                                            @endif
                                                        @empty
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
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
    <script>
        "use Strict";
        $(document).ready(function() {
            $(document).on('click', '#print', function() {
                printDivSection();
            });
        });

        function printDivSection() {
            // Get the content to be printed
            var contents = document.getElementById('print_section').innerHTML;
            // Define print-specific styles

            // Save the current content of the body
            var originalContents = document.body.innerHTML;

            var appName = "{{ $general->sitename }}";
            // Get the current date and time
            var currentDate = new Date();
            var dateString = currentDate.toLocaleDateString(); // e.g., "12/21/2024"
            var timeString = currentDate.toLocaleTimeString(); // e.g., "14:30:00"

            // Inject the print styles and the header (current date, time, app name) into the body
            document.body.innerHTML = `
                <html>
                    <div style="text-align: center; margin-bottom: 20px;">
                        <h2>${appName}</h2>
                        <p><strong>Invoice</strong> <strong>Date:</strong> ${dateString} | <strong>Time:</strong> ${timeString}</p>
                        <hr>
                    </div>
                    ${contents}
                </html>
            `;

            // Trigger the print dialog
            window.print();

            // After printing, revert back to the original page content
            document.body.innerHTML = originalContents;
        }
    </script>
@endpush
