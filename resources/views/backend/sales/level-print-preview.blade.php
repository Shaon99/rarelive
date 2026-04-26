@php
    $customer_name = $singleSale->customer->name ?? 'Walking Customer';
    $customer_phone = $singleSale->customer->phone ?? '';
    $customer_address = rtrim($singleSale->delivery_address ?? '', ',');

    $subtotal = $singleSale->sub_total ?? 0;
    $delivery_charge = $singleSale->system_delivery_charge ?? 0;
    $discount = $singleSale->discount ?? 0;
    $advanced = $singleSale->paid_amount ?? 0;
    $cod = $singleSale->due_amount ?? 0;

    $invoice_no = $singleSale->invoice_no;
    $consignment = $singleSale->consignment_id ?? null;
@endphp

<style>
    #level-print-preview {
        font-family: 'Arial', sans-serif;
        font-size: 12px;
        max-width: 76.2mm;
        background: white;
    }

    #level-print-preview .header {
        display: flex;
        gap: 13px;
        margin-bottom: -20px;
        align-items: flex-start;
    }

    #level-print-preview .barcode-section {
        flex: 1;
    }

    #level-print-preview .website {
        font-size: 10px;
        text-align: center;
        margin: 5px 0;
        font-weight: bold;
    }

    #level-print-preview .product-section {
        margin: 10px 0;
        border: 1px dashed #000;
        padding: 5px;
        font-size: 11px;
        max-height: 120px;
        overflow-y: auto;
    }

    #level-print-preview .product-title {
        font-weight: bold;
        margin-bottom: 5px;
        text-transform: uppercase;
    }

    #level-print-preview .product-item {
        display: flex;
        justify-content: space-between;
        margin: 2px 0;
        font-size: 10px;
    }

    #level-print-preview .invoice-table {
        width: 100%;
        border: 2px solid #000;
        border-collapse: collapse;
        margin-top: 10px;
    }

    #level-print-preview .invoice-table td {
        border: 2px solid #000;
        padding: 4px 6px;
        font-size: 11px;
    }

    #level-print-preview .label-cell {
        width: 35%;
        text-align: left;
        font-weight: bold;
    }

    #level-print-preview .value-cell {
        text-align: right;
    }

    #level-print-preview .financial-table {
        width: 100%;
        border-collapse: collapse;
    }

    #level-print-preview .financial-table td {
        border: none;
        font-size: 11px;
        padding: 2px 0;
    }

    #level-print-preview .financial-label {
        text-align: left;
    }

    #level-print-preview .financial-amount {
        text-align: right;
    }

    #level-print-preview .cod-amount {
        font-size: 13px;
        font-weight: bold;
    }
</style>

<div class="packaging-slip">
    <!-- HEADER -->
    <div class="header">
        @if ($general->sitename)
                <div style="display: flex; flex-direction: column; align-items: flex-start;">
                    <h6 style="margin-top:0px;font-size:16px;line-height: .6;">{{ $general->sitename }}</h6>
                    @if (!empty($singleSale->courier_name))
                    <small style="text-transform: uppercase;">{{ $singleSale->courier_name }}</small>
                    @endif
                </div>
            @endif
        <div class="barcode-section">
            <div class="barcode">
                {!! DNS1D::getBarcodeHTML($consignment ?? $invoice_no, 'C128', 1, 28, 'black', false) !!}
            </div>
        </div>
        <div class="qr">
            {!! DNS2D::getBarcodeHTML((string) ($consignment ?? $invoice_no), 'QRCODE,M', 2, 2) !!}
        </div>
    </div>

    <div class="website">
        {{ $consignment ?? $invoice_no }}
    </div>

    <!-- PRODUCT INFO -->
    <div class="product-section">
        <div class="product-title">PRODUCT INFO</div>
        @foreach ($singleSale->salesProduct->take(10) as $item)
            <div class="product-item">
                <div class="product-name">
                    {{ strtoupper($item->product->name ?? ($item->combo->name ?? 'N/A')) }}
                </div>
                <div class="product-qty">
                    x {{ number_format($item->quantity) }}
                </div>
            </div>
        @endforeach
    </div>

    <!-- MAIN TABLE -->
    <table class="invoice-table">
        <tr>
            <td class="label-cell">Invoice No</td>
            <td class="value-cell">{{ $invoice_no }}</td>
        </tr>
        <tr>
            <td class="label-cell">Customer</td>
            <td class="value-cell">{{ strtoupper($customer_name) }}</td>
        </tr>
        <tr>
            <td class="label-cell">Mobile</td>
            <td class="value-cell">{{ $customer_phone }}</td>
        </tr>
        <tr>
            <td class="label-cell">Address</td>
            <td class="value-cell">{{ strtoupper($customer_address) }}</td>
        </tr>
        <tr>
            <td class="label-cell">Shipping</td>
            <td class="value-cell">
                <table class="financial-table">
                    <tr>
                        <td class="financial-label">Bill</td>
                        <td class="financial-amount">
                            {{ number_format($subtotal, 2) }} {{ $general->site_currency }}
                        </td>
                    </tr>
                    <tr>
                        <td class="financial-label">Discount</td>
                        <td class="financial-amount">
                            {{ number_format($discount, 2) }} {{ $general->site_currency }}
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="financial-label">Paid</td>
                        <td class="financial-amount">
                            {{ number_format($advanced, 2) }} {{ $general->site_currency }}
                        </td>
                    </tr>
                    <tr>
                        <td class="financial-label">COD</td>
                        <td class="financial-amount cod-amount">
                            {{ number_format($cod, 2) }} {{ $general->site_currency }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

