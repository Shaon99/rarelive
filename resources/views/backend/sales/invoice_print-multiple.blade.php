@extends('backend.layout.master')

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/invoice.css') }}">
    <style>
        @media print {
            body * {
                visibility: hidden;
            }

            .invoice-container,
            .invoice-container * {
                visibility: visible;
            }

            .invoice-container {
                position: relative;
                left: 0;
                top: 0;
                width: 100%;
                page-break-after: always;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 15px !important;
                background: white !important;
            }

            #printAgain {
                display: none;
            }
        }
    </style>
@endpush

@section('content')
    <div class="print-invoice-wrapper">
        <button class="btn btn-primary" id="printAgain" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
            <i class="fas fa-print"></i>
            Print Invoice
        </button>
        @foreach ($invoices as $singleSale)
            <div class="invoice-container">
                <div class="row">
                    <!-- Left Column - Company Info -->
                    <div class="col-md-6">
                        @if ($general->invoice_logo)
                            <img src="{{ getFile('logo', $general->invoice_logo) }}" alt="Logo" width="60px"
                                height="60px" class="img-fluid rounded">
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
                                <span class="info-label">Phone :</span> <i class="fas fa-phone"></i>
                                {{ $singleSale->customer->phone ?? '' }}
                            </div>
                            <div class="info-row">
                                <span class="info-label">Address :</span>
                                {{ rtrim($singleSale->delivery_address ?? '', ',') }}
                            </div>
                            <div class="info-row">
                                <span class="info-label">Note :</span>
                                <strong>{{ $singleSale->note ?? 'Handle with care' }}</strong>
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
                                <div class="d-flex justify-content-between">
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
                                        {{ $item->product->name ?? $item->combo->name }} -
                                        {{ $item->quantity . ' ' . ($item->product->unit->name ?? '') }}
                                        x
                                        {{ number_format($item->sale_price, 2) . ' ' . ($general->site_currency ?? '') }}
                                        @if ($item->discount > 0)
                                            - ({{ number_format($item->discount, 2) }}
                                            {{ $item->discount_type === 'percentage' ? '%' : $general->site_currency ?? '' }})
                                        @endif
                                        =
                                        {{ number_format($item->total, 2) . ' ' . ($general->site_currency ?? '') }}
                                    </p>
                                @empty
                                @endforelse
                                <div class="text-right">
                                    Sub Total: <span
                                        class="text-success">{{ number_format($singleSale->sub_total, 2) . ' ' . ($general->site_currency ?? '') }}</span>
                                </div>
                                @if ($singleSale->discount)
                                    <div class="text-right">
                                        Discount: <span
                                            class="text-danger">-{{ number_format($singleSale->discount, 2) . ' ' . ($general->site_currency ?? '') }}</span>
                                    </div>
                                @endif
                                @if ($singleSale->system_delivery_charge || $singleSale->shipping_cost)
                                    <div class="text-right">
                                        Shipping: <span
                                            class="text-success">+{{ number_format($singleSale->system_delivery_charge ?? $singleSale->shipping_cost, 2) . ' ' . ($general->site_currency ?? '') }}</span>
                                    </div>
                                @endif
                                <div class="text-right">
                                    Grand Total: <span
                                        class="text-success">{{ number_format($singleSale->grand_total, 2) . ' ' . ($general->site_currency ?? '') }}</span>
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
        @endforeach
    </div>
@endsection
@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.print();
            const printBtn = document.getElementById('printAgain');
            if (printBtn) {
                printBtn.addEventListener('click', function() {
                    window.print();
                });
            }
        });
    </script>
@endpush
