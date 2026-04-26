@extends('backend.layout.master')

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/invoice.css') }}">
@endpush

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h6>
                    {{ __($pageTitle ?? '') }}
                    {{ $singleSale->customer->name ? $singleSale->customer->name : ' Walking Customer' }}
                </h6>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item">{{ __(@$pageTitle) }}</div>
                    <div class="breadcrumb-item active">
                        <a href="{{ route('admin.sales.index') }}">{{ __('Sales') }}</a>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 d-flex justify-content-center">
                                <div class="system-status pt-2 form-group w-50">
                                    <label>Change Status</label>
                                    @if ($singleSale->payment_status == 1 && $singleSale->system_status === 'completed')
                                        <select class="form-control select2 system-status-value"
                                            data-sale-id="{{ $singleSale->id }}" disabled>
                                            <option value="completed" selected>Delivered</option>
                                        </select>
                                    @elseif ($singleSale->system_status === 'cancelled')
                                        <select class="form-control select2 system-status-value"
                                            data-sale-id="{{ $singleSale->id }}" disabled>
                                            <option value="cancelled" selected>Cancelled</option>
                                        </select>
                                    @else
                                        <select class="form-control select2 system-status-value"
                                            data-sale-id="{{ $singleSale->id }}">
                                            <option value="" selected disabled>Select Status</option>
                                            <option value="pending"
                                                {{ $singleSale->system_status === 'pending' ? 'selected' : '' }}>Shipping
                                            </option>
                                            <option value="completed"
                                                {{ $singleSale->system_status === 'completed' ? 'selected' : '' }}>
                                                Delivered</option>
                                            <option value="cancelled"
                                                {{ $singleSale->system_status === 'cancelled' ? 'selected' : '' }}>
                                                Cancelled</option>
                                        </select>
                                    @endif
                                    <small class="text-danger">
                                        Note: Cancelled, Paid and Delivered Then It Cannot Be Changed
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home"
                                            role="tab" aria-controls="home" aria-selected="false">
                                            STEADFAST INVOICE
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" id="level-print-tab" data-toggle="tab" href="#level-print"
                                            role="tab" aria-controls="level-print" aria-selected="false">
                                            Level Print
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact"
                                            role="tab" aria-controls="contact" aria-selected="false">
                                            DETAILS INVOICE
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content pt-0" id="myTabContent">
                                    <!-- STEADFAST INVOICE Tab -->
                                    <div class="tab-pane fade show active" id="home" role="tabpanel"
                                        aria-labelledby="home-tab">
                                        <button class="btn btn-primary" id="print">
                                            <i class="fas fa-print"></i> Print Invoice
                                        </button>
                                        @if ($general->enable_online_deliver)
                                            @if (!$singleSale->consignment_id)
                                                <button type="button" class="btn btn-success ml-4"
                                                    id="sendToSteadfastCourier" data-id="{{ $singleSale->id }}">
                                                    <i class="fas fa-truck-fast"></i> Send to Steadfast Courier
                                                </button>
                                            @endif
                                        @endif
                                        <div class="invoice-container" id="print_section">
                                            <div class="row">
                                                <!-- Left Column - Company Info -->
                                                <div class="col-md-6">
                                                    @if ($general->invoice_logo)
                                                        <img src="{{ getFile('logo', $general->invoice_logo) }}"
                                                            alt="Logo" width="60px" height="60px"
                                                            class="img-fluid rounded">
                                                    @endif
                                                    <div class="company-name">{{ $general->sitename }}</div>
                                                    <div class="company-contact">
                                                        <i class="fas fa-phone"></i> {{ $general->site_phone }}
                                                    </div>
                                                    <div class="company-address mt-1">
                                                        <i class="fas fa-map-marker-alt"></i> {{ $general->site_address }}
                                                    </div>
                                                    <div class="ship-to mt-2">
                                                        <div class="section-titles">Ship To</div>
                                                        <div class="info-row">
                                                            <span class="info-label">Name :</span>
                                                            {{ $singleSale->customer->name ?? 'Walking Customer' }}
                                                        </div>
                                                        <div class="info-row">
                                                            <span class="info-label">Phone :</span>
                                                            <i class="fas fa-phone"></i>
                                                            {{ $singleSale->customer->phone ?? '' }}
                                                        </div>
                                                        <div class="info-row">
                                                            <span class="info-label">Address :</span>
                                                            {{ rtrim($singleSale->delivery_address ?? '', ',') }}
                                                        </div>
                                                        <div class="info-row">
                                                            <span class="info-label">Note :</span>
                                                            <strong>{{ $singleSale->note ?? 'Handel with care' }}</strong>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Right Column - Invoice Details -->
                                                <div class="col-md-6">
                                                    <div class="invoice-header">
                                                        <div class="invoice-title">Invoice</div>
                                                        <div class="info-row">
                                                            #<strong>{{ $singleSale->invoice_no }}</strong>
                                                        </div>
                                                        <div class="info-row">
                                                            <span class="info-label">Date :</span>
                                                            <strong>{{ $singleSale->created_at->format('d M, Y h:i A') }}</strong>
                                                        </div>
                                                    </div>
                                                    <div class="parcel-details mt-2">
                                                        @if ($singleSale->consignment_id)
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div class="barcode">
                                                                    <span class="info-label">Parcel ID :</span>
                                                                    #<strong>{{ $singleSale->consignment_id ?? '' }}</strong>
                                                                    {!! DNS1D::getBarcodeHTML((string) ($singleSale->consignment_id ?? ''), 'C39', 1, 30) !!}
                                                                </div>
                                                                <div class="qr-code">
                                                                    {!! DNS2D::getBarcodeHTML((string) ($singleSale->consignment_id ?? ''), 'QRCODE,M', 4, 4) !!}
                                                                </div>
                                                            </div>
                                                        @endif
                                                        <div class="mt-4 text-right">
                                                            @forelse ($singleSale->salesProduct as $item)
                                                                <p class="m-0 p-0 f-12 border-bottom">
                                                                    {{ $loop->iteration }}.
                                                                    {{ $item->product ? $item->product->name : $item->combo->name ?? '' }}
                                                                    -
                                                                    {{ number_format($item->quantity, 0) }}
                                                                    {{ $item->product && $item->product->unit ? $item->product->unit->name : '' }}
                                                                    x
                                                                    {{ number_format($item->sale_price, 2) . ' ' . ($general->site_currency ?? '') }}
                                                                    @if ($item->discount > 0)
                                                                        - ({{ number_format($item->discount, 2) }}
                                                                        {{ $item->discount_type === 'percentage' ? '%' : $general->site_currency ?? '' }})
                                                                    @endif
                                                                    =
                                                                    @php
                                                                        // Calculate line subtotal
                                                                        $lineSubtotal =
                                                                            $item->sale_price * $item->quantity;

                                                                        // Item total should be recalculated for percentage discount for clarity
                                                                        if (
                                                                            $item->discount_type === 'percentage' &&
                                                                            $item->discount > 0
                                                                        ) {
                                                                            $finalLineTotal =
                                                                                $lineSubtotal -
                                                                                $lineSubtotal * ($item->discount / 100);
                                                                        } elseif (
                                                                            $item->discount_type === 'fixed' &&
                                                                            $item->discount > 0
                                                                        ) {
                                                                            $finalLineTotal =
                                                                                $lineSubtotal - $item->discount;
                                                                        } else {
                                                                            $finalLineTotal = $lineSubtotal;
                                                                        }
                                                                    @endphp
                                                                    {{ number_format($finalLineTotal, 2) . ' ' . ($general->site_currency ?? '') }}
                                                                </p>
                                                            @empty
                                                            @endforelse

                                                            @if ($singleSale->sub_total)
                                                                <div class="text-right">
                                                                    Sub Total:
                                                                    <span
                                                                        class="text-danger">{{ number_format($singleSale->sub_total, 2) . ' ' . ($general->site_currency ?? '') }}</span>
                                                                </div>
                                                            @endif


                                                            @if ($singleSale->discount)
                                                                <div class="text-right">
                                                                    Discount:
                                                                    <span
                                                                        class="text-danger">-{{ number_format($singleSale->discount, 2) . ' ' . ($general->site_currency ?? '') }}</span>
                                                                </div>
                                                            @endif

                                                            @if ($singleSale->system_delivery_charge || $singleSale->shipping_cost)
                                                                <div class="text-right">
                                                                    Shipping:
                                                                    <span
                                                                        class="text-success">+{{ number_format($singleSale->system_delivery_charge ?? $singleSale->shipping_cost, 2) . ' ' . ($general->site_currency ?? '') }}</span>
                                                                </div>
                                                            @endif
                                                            <div class="text-right">
                                                                Grand Total:
                                                                <span
                                                                    class="text-success">+{{ number_format($singleSale->grand_total, 2) . ' ' . ($general->site_currency ?? '') }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex justify-content-end">
                                                            <div class="border border-2 mt-2 p-2 font-weight-bold">
                                                                Payment :
                                                                {{ $singleSale->due_amount > 0 ? 'COD' : 'PAID' }}
                                                            </div>
                                                            <div class="border border-2 mt-2 ml-3 p-2 font-weight-bold">
                                                                COD :
                                                                {{ number_format($singleSale->due_amount, 2) . ' ' . ($general->site_currency ?? '') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-center mt-3">
                                                <small>
                                                    {{ $general->invoice_greeting ?? 'Thank you for your support. We appreciate you!' }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- DETAILS INVOICE Tab -->
                                    <div class="tab-pane fade" id="contact" role="tabpanel"
                                        aria-labelledby="contact-tab">
                                        <!-- Print Button -->
                                        <button class="btn btn-primary" id="print-2">
                                            <i class="fas fa-print"></i> Print Invoice
                                        </button>
                                        <div class="invoice-container" id="print_section_2">
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="d-flex align-items-center print-logo"
                                                        style="display: flex; align-items: center;">
                                                        @if ($general->invoice_logo)
                                                            <div class="mr-2">
                                                                <img src="{{ getFile('logo', $general->invoice_logo) }}"
                                                                    alt="Logo" width="60px" height="60px"
                                                                    class="img-fluid rounded">
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <div class="font-weight-bold" style="font-size: 18px;">
                                                                {{ $general->sitename ?? '' }}
                                                            </div>
                                                            <div style="font-size: 14px;">
                                                                {{ $general->invoice_header_note ?? '' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-6 text-right">
                                                    <div class="invoice-title">Invoice</div>
                                                    <div class="invoice-details">Invoice# <span
                                                            class="font-weight-bold">{{ $singleSale->invoice_no }}</span>
                                                    </div>
                                                    @if ($singleSale->consignment_id)
                                                        <div class="invoice-details">Parcel ID# <span
                                                                class="font-weight-bold">{{ $singleSale->consignment_id ?? '' }}</span>
                                                        </div>
                                                    @endif
                                                    <div class="invoice-details">Date: <span
                                                            class="font-weight-bold">{{ $singleSale->created_at->format('d/F/Y') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="bill-to">
                                                        <div class="bill-to-title">BILL TO</div>
                                                        <div class="font-weight-bold">
                                                            {{ $singleSale->customer->name ?? 'Walking Customer' }}
                                                        </div>
                                                        <div>{{ $singleSale->customer->phone ?? '' }}</div>
                                                        <div>{{ $singleSale->customer->email ?? '' }}</div>
                                                        <div>{{ $singleSale->delivery_address ?? '' }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-6 text-right">
                                                    <div class="total-due">Total:</div>
                                                    <div class="total-due-amount">
                                                        {{ number_format($singleSale->grand_total, 2) . ' ' . ($general->site_currency ?? '') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    <table class="table table-borderless">
                                                        <thead>
                                                            <tr class="table-header">
                                                                <th style="width: 50%">ITEM</th>
                                                                <th style="width: 15%" class="text-right">PRICE</th>
                                                                <th style="width: 15%" class="text-right">DISCOUNT</th>
                                                                <th style="width: 15%" class="text-center">Quantity</th>
                                                                <th style="width: 20%" class="text-right">Totals</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse ($singleSale->salesProduct as $item)
                                                                @php
                                                                    // Calculate discount per row
                                                                    $discountValue = 0;
                                                                    if ($item->discount > 0) {
                                                                        if ($item->discount_type === 'percentage') {
                                                                            // Apply percentage discount to the sale_price
                                                                            $discountValue =
                                                                                (($item->sale_price * $item->discount) /
                                                                                    100) *
                                                                                $item->quantity;
                                                                        } else {
                                                                            // Flat discount per unit
                                                                            $discountValue =
                                                                                $item->discount * $item->quantity;
                                                                        }
                                                                    }
                                                                    // Row total = base - discount
                                                                    $rowTotal =
                                                                        $item->sale_price * $item->quantity -
                                                                        $discountValue;
                                                                @endphp
                                                                <tr class="table-item">
                                                                    <td>
                                                                        <div class="item-description">
                                                                            {{ $item->product ? $item->product->name : $item->combo->name ?? '' }}
                                                                        </div>
                                                                    </td>
                                                                    <td class="text-right">
                                                                        {{ number_format($item->sale_price, 2) . ' ' . ($general->site_currency ?? '') }}
                                                                    </td>
                                                                    <td class="text-right">
                                                                        @if ($item->discount > 0)
                                                                            {{ number_format($item->discount, 2) }}
                                                                            {{ $item->discount_type === 'percentage' ? '%' : $general->site_currency ?? '' }}
                                                                        @else
                                                                            0
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-center">
                                                                        {{ number_format($item->quantity, 0) }}
                                                                        {{ $item->product && $item->product->unit ? $item->product->unit->name : '' }}
                                                                    </td>
                                                                    <td class="text-right">
                                                                        {{ number_format($rowTotal, 2) . ' ' . ($general->site_currency ?? '') }}
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div class="payment-section">
                                                                <div class="section-titles">Payment Method</div>
                                                                <div>
                                                                    @php $type = $singleSale->paymentMethod->type ?? ''; @endphp
                                                                    @switch($type)
                                                                        @case('CASH')
                                                                            <span
                                                                                class="badge badge-success">{{ $singleSale->paymentMethod->name ?? '' }}</span>
                                                                        @break

                                                                        @case('STEADFAST')
                                                                            <span
                                                                                class="badge badge-warning">{{ $singleSale->paymentMethod->name ?? '' }}
                                                                                (COD)</span>
                                                                        @break

                                                                        @case('BANK')
                                                                            <span
                                                                                class="badge badge-info">{{ $singleSale->paymentMethod->name ?? '' }}</span>
                                                                        @break

                                                                        @case('MFS')
                                                                            <span
                                                                                class="badge badge-primary">{{ $singleSale->paymentMethod->name ?? '' }}</span>
                                                                        @break

                                                                        @default
                                                                            <span
                                                                                class="badge badge-dark">{{ $singleSale->paymentMethod->name ?? 'COD' }}</span>
                                                                    @endswitch
                                                                </div>
                                                                @if ($singleSale->consignment_id)
                                                                    <div class="qr-code mt-2">
                                                                        {!! DNS2D::getBarcodeHTML((string) ($singleSale->consignment_id ?? ''), 'QRCODE,M', 4, 4) !!}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="contact-section">
                                                                <div class="section-titles">Contact</div>
                                                                <div>{{ $general->site_address ?? '' }}</div>
                                                                <div>
                                                                    {{ $general->site_phone ?? '' }}
                                                                    {{ $general->email ? '| ' . $general->email : '' }}
                                                                </div>
                                                                <div>
                                                                    <a
                                                                        href="{{ $general->website ?? '#' }}">{{ $general->website ?? '' }}</a>
                                                                </div>
                                                            </div>
                                                            <div class="terms-section">
                                                                <div class="section-titles">Note</div>
                                                                <div>{{ $singleSale->note ?? 'No Remarks Here' }}</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <table class="totals-table">
                                                                <tr>
                                                                    <td class="text-right">SUB TOTAL</td>
                                                                    <td class="text-right" style="width: 120px;">
                                                                        {{ number_format($singleSale->sub_total, 2) . ' ' . ($general->site_currency ?? '') }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-right text-uppercase">Shipping Charge
                                                                        (+)</td>
                                                                    <td class="text-right" style="width: 120px;">
                                                                        {{ number_format($singleSale->system_delivery_charge ?? $singleSale->shipping_cost, 2) . ' ' . ($general->site_currency ?? '') }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-right text-uppercase">Discount (-)</td>
                                                                    <td class="text-right" style="width: 120px;">
                                                                        {{ number_format($singleSale->discount, 2) . ' ' . ($general->site_currency ?? '') }}
                                                                    </td>
                                                                </tr>
                                                                <tr class="total-row">
                                                                    <td class="text-right">Grand Total</td>
                                                                    <td class="text-right">
                                                                        {{ number_format($singleSale->grand_total, 2) . ' ' . ($general->site_currency ?? '') }}
                                                                    </td>
                                                                </tr>
                                                                <tr class="total-row">
                                                                    <td class="text-right">Paid</td>
                                                                    <td class="text-right">
                                                                        {{ number_format($singleSale->paid_amount, 2) . ' ' . ($general->site_currency ?? '') }}
                                                                    </td>
                                                                </tr>
                                                                <tr class="total-row">
                                                                    <td class="text-right">COD</td>
                                                                    <td class="text-right">
                                                                        {{ number_format($singleSale->due_amount, 2) . ' ' . ($general->site_currency ?? '') }}
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="text-center f-12 mt-4">
                                                <i>{{ $general->invoice_greeting ?? 'Thank you for your support. We appreciate you!' }}</i>
                                            </p>
                                        </div>
                                    </div>
                                    <!-- LEVEL PRINT Tab -->
                                    <div class="tab-pane fade" id="level-print" role="tabpanel"
                                        aria-labelledby="level-print-tab">
                                        <button class="btn btn-primary mb-2" id="print-level">
                                            <i class="fas fa-print"></i> Print Level
                                        </button>
                                        <div id="level-print-preview" class="mt-3">
                                            @include('backend.sales.level-print-preview')
                                        </div>
                                    </div>
                                </div> <!-- end tab-content -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div class="modal fade" id="courierConfirmModal" tabindex="-1" role="dialog" aria-labelledby="courierModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="courierModalLabel">Confirm Courier Send</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to send this order to <strong>Steadfast</strong> Courier?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmSendCourier">Yes, Send</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use Strict";
        $(document).ready(function() {

            $(document).on('click', '#print', function() {
                printDivSection('print_section');
            });

            $(document).on('click', '#print-2', function() {
                printDivSection('print_section_2');
            });

            $(document).on('click', '#print-level', function() {
                var saleId = {{ $singleSale->id }};
                var url = "{{ route('admin.sales.levelPrint', ':id') }}".replace(':id', saleId);
                window.location.href = url;
            });

            function printDivSection(sectionId) {
                var Contents_Section = document.getElementById(sectionId).innerHTML,
                    originalContents = document.body.innerHTML;
                document.body.innerHTML = Contents_Section;
                window.print();
                document.body.innerHTML = originalContents;
            }

            let previousStatus = $('.system-status-value').val();

            $('.system-status-value').change(function() {
                let newStatus = $(this).val();
                let saleId = $(this).data('sale-id');
                $('#newStatusText').text($(this).find('option:selected').text());
                $('#confirmStatusModal').modal('show');

                // On confirm, send AJAX request
                $('#confirmStatusChange').off('click').on('click', function() {
                    var button = $(this);
                    $.ajax({
                        url: `/sales-status-change/${saleId}?status=${newStatus}`,
                        beforeSend: function() {
                            button.addClass('btn-progress');
                        },
                        complete: function() {
                            button.removeClass('btn-progress');
                        },
                        success: function(response) {
                            $('#confirmStatusModal').modal('hide');
                            previousStatus = newStatus;
                            showToast('Status updated successfully!', 'success');
                            window.location.href = '/sales';
                        },
                        error: function() {
                            showToast('Failed to update status. Please try again.',
                                'error');
                            $('.system-status-value').val(previousStatus);
                        }
                    });
                });

                // If modal is closed without confirmation, revert to previous status
                $('#confirmStatusModal').on('hidden.bs.modal', function() {
                    $('.system-status-value').val(previousStatus);
                });
            });

            let selectedOrderId = null;

            $(document).on('click', '#sendToSteadfastCourier', function() {
                selectedOrderId = $(this).data('id');
                $('#courierConfirmModal').modal('show');
            });

            $('#confirmSendCourier').on('click', function() {
                if (!selectedOrderId) return;

                var $button = $(this);
                $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');

                $.ajax({
                    url: "{{ route('admin.sales.sendToSteadFastSingleOrder') }}",
                    method: "POST",
                    data: {
                        order_id: selectedOrderId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        $('#courierConfirmModal').modal('hide');
                        showToast(response.message || 'Order sent to Steadfast courier.',
                            'success');
                        $button.html('<i class="fas fa-check"></i> Sent Successfully');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    },
                    error: function(xhr) {
                        $('#courierConfirmModal').modal('hide');
                        let errorMessage = 'Something went wrong. Please try again.';
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            if (errors && errors.order_id) {
                                errorMessage = errors.order_id.join(', ');
                            }
                        } else if (xhr.status === 404 || xhr.status === 422) {
                            errorMessage = xhr.responseJSON.message || errorMessage;
                        } else if (xhr.status === 500) {
                            errorMessage = xhr.responseJSON.message || 'Server error occurred.';
                        }
                        showToast(errorMessage, 'error');
                        $button.prop('disabled', false).html('Yes, Send');
                    }
                });
            });

        });
    </script>
@endpush
