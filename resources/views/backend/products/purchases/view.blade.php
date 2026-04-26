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
                                            <p class="mb-2"><strong>{{ __('Supplier:') }}</strong>
                                                <a
                                                    href="{{ route('admin.supplier.edit', $singlePurchases->supplier->id ?? '#') }}">
                                                    {{ $singlePurchases->supplier->name ?? 'N/A' }}
                                                </a>
                                            </p>

                                            <p class="mb-2"><strong>{{ __('Reference:') }}</strong>
                                                {{ $singlePurchases->reference_no ?? 'N/A' }}</p>

                                            <p class="mb-2"><strong>{{ __('Invoice:') }}</strong>
                                                {{ $singlePurchases->invoice_no ?? 'N/A' }}</p>

                                            <p class="mb-2"><strong>{{ __('Total Quantity:') }}</strong>
                                                {{ $singlePurchases->total_qty }}</p>

                                            <p class="mb-2"><strong>{{ __('Total Amount:') }}</strong>
                                                {{ number_format($singlePurchases->grand_total, 2) }}
                                                {{ $general->site_currency }}</p>

                                            <p class="mb-2"><strong>{{ __('Paid Amount:') }}</strong>
                                                {{ number_format($singlePurchases->paid_amount, 2) }}
                                                {{ $general->site_currency }}</p>

                                            <p class="mb-2"><strong>{{ __('Return Total:') }}</strong>
                                                {{ number_format($purchaseReturns->sum('total_amount') ?? 0, 2) }}
                                                {{ $general->site_currency }}
                                            </p>

                                            <p class="mb-2"><strong>{{ __('Due Amount:') }}</strong>
                                                {{ number_format($singlePurchases->due_amount, 2) }}
                                                {{ $general->site_currency }}</p>

                                            <p class="mb-2"><strong>{{ __('Created By:') }}</strong>
                                                {{ $singlePurchases->createdBy->name ?? 'N/A' }}</p>

                                            <p class="mb-2"><strong>{{ __('Warehouse:') }}</strong>
                                                {{ @$singlePurchases->warehouse->name }}</p>

                                            <p class="mb-2"><strong>{{ __('Payment Status:') }}</strong>
                                                @if ($singlePurchases->payment_status == 0)
                                                    <span class="badge badge-danger">{{ __('Due') }}</span>
                                                @elseif($singlePurchases->payment_status == 1)
                                                    <span class="badge badge-success">{{ __('Completed') }}</span>
                                                @endif
                                            </p>
                                            <p class="mb-2"><strong>{{ __('Purchase Note:') }}</strong>
                                                {{ $singlePurchases->note }}</p>

                                            <p class="mb-2"><strong>{{ __('Purchase Date:') }}</strong>
                                                {{ $singlePurchases->purchase_date }}</p>

                                            <p class="mb-2"><strong>{{ __('Created Date:') }}</strong>
                                                {{ $singlePurchases->created_at->format('Y-m-d H:i A') }}</p>
                                        </div>

                                        <div class="mt-3 d-flex justify-content-between no-print">
                                            @if (auth()->guard('admin')->user()->can('purchase_return'))
                                                <a href="{{ route('admin.purchase_returns.create', $singlePurchases->id) }}"
                                                    class="btn btn-warning btn-sm d-flex align-items-center justify-content-center"
                                                    data-toggle="tooltip" title="{{ __('Purchases Return') }}">
                                                    <i class="fas fa-rotate-left mr-2"></i> {{ __('Purchases Return') }}
                                                </a>
                                            @endif
                                            <button class="btn btn-primary btn-sm" id="print"><i
                                                    class="fas fa-print mr-2"></i> {{ __('Print Purchases') }}</button>
                                        </div>
                                    </div>

                                    <div class="col-md-9 py-4">
                                        <table class="table tab-bordered table-responsive">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('#') }}</th>
                                                    <th>{{ __('Product Name') }}</th>
                                                    <th>{{ __('Quantity') }}</th>
                                                    <th>{{ __('Previous Net Unit Price') }}</th>
                                                    <th>{{ __('Current Net Unit Price') }}</th>
                                                    <th class="text-right">{{ __('Sub Total') }}</th>
                                                </tr>

                                            </thead>

                                            <tbody>
                                                @forelse ($singlePurchases->purchasesProduct as $item)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $item->product->name }}</td>
                                                        <td>{{ number_format($item->quantity, 0) }}</td>
                                                        <td>{{ number_format($item->previous_net_unit_price, 2) }}
                                                            {{ $general->site_currency }}
                                                        </td>
                                                        <td>{{ number_format($item->current_net_unit_price, 2) }}
                                                            {{ $general->site_currency }}
                                                        </td>
                                                        <td class="text-right">
                                                            {{ number_format($item->total, 2) }}
                                                            {{ $general->site_currency }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                @endforelse
                                            </tbody>

                                            <tfoot class="border-top">
                                                <tr>
                                                    <th colspan="1">{{ __('Items') }}
                                                        {{ $singlePurchases->purchasesProduct->count() }}
                                                    </th>
                                                    <th colspan="4" class="text-right">{{ __('Subtotal') }} :
                                                    </th>
                                                    <th colspan="5" class="text-right">
                                                        {{ number_format($singlePurchases->sub_total, 2) }}
                                                        {{ $general->site_currency }}
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="5" class="text-right">{{ __('Discount') }} (-) :
                                                    </th>
                                                    <th colspan="6" class="text-right">
                                                        {{ number_format($singlePurchases->discount, 2) }}
                                                        {{ $general->site_currency }}
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="5" class="text-right">{{ __('Shipping') }} (+) :
                                                    </th>
                                                    <th colspan="6" class="text-right">
                                                        {{ number_format($singlePurchases->other_cost, 2) }}
                                                        {{ $general->site_currency }}
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="5" class="text-right">{{ __('Grand Total') }} :
                                                    </th>
                                                    <th colspan="6" class="text-right">
                                                        {{ number_format($singlePurchases->grand_total, 2) }}
                                                        {{ $general->site_currency }}
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="5" class="text-right">{{ __('Paid Amount') }} :
                                                    </th>
                                                    <th colspan="6" class="text-right">
                                                        {{ number_format($singlePurchases->paid_amount, 2) }}
                                                        {{ $general->site_currency }}
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="5" class="text-right">{{ __('Due Amount') }} :
                                                    </th>
                                                    <th colspan="6" class="text-right">
                                                        {{ number_format($singlePurchases->due_amount, 2) }} :
                                                        {{ $general->site_currency }}
                                                    </th>
                                                </tr>
                                            </tfoot>
                                        </table>

                                        @if ($singlePurchases->transactions)
                                            <div class="mt-3">
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
                                                        @forelse ($singlePurchases->transactions as $item)
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
                            @if ($purchaseReturns->isNotEmpty())
                                <div class="row mb-4 mt-4">
                                    <div class="col-md-12">
                                        <h5 class="py-2">Purchases Return List</h5>
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Details</th>
                                                    <th>Created By</th>
                                                    <th>Return Date</th>
                                                    <th>Supplier</th>
                                                    <th>Warehouse</th>
                                                    <th>Return Total</th>
                                                    <th>Note</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $returnTotalSum = 0;
                                                @endphp
                                                @forelse($purchaseReturns as $return)
                                                    @php
                                                        $amount = (float) ($return->total_amount ?? 0);
                                                        $returnTotalSum += $amount;
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>
                                                            <button
                                                                class="btn btn-success details-show btn-sm btn-icon my-2"
                                                                data-item="{{ $return->purchaseReturnItems }}"
                                                                data-toggle="tooltip" title="Details Show" type="button">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </td>
                                                        <td>{{ $return->createdBy->name ?? 'N/A' }}</td>
                                                        <td>{{ $return->return_date ?? 'N/A' }}</td>
                                                        <td>{{ $return->purchase->supplier->name ?? 'N/A' }}</td>
                                                        <td>{{ $return->purchase->warehouse->name ?? 'N/A' }}</td>
                                                        <td>{{ number_format($amount, 2) }} {{ $general->site_currency }}
                                                        </td>
                                                        <td>{{ $return->note ?? 'N/A' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="8" class="text-center">No purchase returns found.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="6" class="text-right">Total Return Amount:</th>
                                                    <th colspan="2">
                                                        {{ number_format($returnTotalSum, 2) }}
                                                        {{ $general->site_currency }}
                                                    </th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="purchaseReturnItemsModal" tabindex="-1" role="dialog"
        aria-labelledby="purchaseReturnItemsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="purchaseReturnItemsModalLabel">Purchase Return Items</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="purchaseReturnItemsContent">
                        <!-- Content will be loaded dynamically -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        "use Strict";

        $(document).on('click', '.details-show', function(e) {
            const items = $(this).data('item');
            const modal = $('#purchaseReturnItemsModal');
            const content = $('#purchaseReturnItemsContent');

            let html = `
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                             <th>Date</th>
                            <th>Product</th>
                            <th>Returned Qty</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>                           
                        </tr>
                    </thead>
                    <tbody>
            `;

            if (items.length > 0) {
                items.forEach((item, index) => {
                    html += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${new Date(item.created_at).toLocaleString('en-GB', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    })}</td>
                    <td>${item.product?.name || 'N/A'}</td>
                    <td>${item.quantity}</td>
                    <td>${parseFloat(item.unit_cost).toFixed(2)} {{ $general->site_currency }}</td>
                    <td>${(item.total_amount).toFixed(2)} {{ $general->site_currency }}</td>
                </tr>
            `;
                });
            } else {
                html += `
            <tr>
                <td colspan="5" class="text-center text-muted">No items found.</td>
            </tr>
        `;
            }

            html += `</tbody></table>`;
            content.html(html);
            modal.modal('show');
        });

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
