<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Packaging Slip</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Use Inter and Modern fonts only -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Modern&display=swap" rel="stylesheet">
    <style>
        /* ========== PRINT LOCK ========== */
        @media print {
            @page {
                size: 76.2mm 101mm;
                margin: 0;
            }

            html,
            body {
                margin: 0;
                padding: 0;
            }

            body {
                font-family: 'Inter', 'Modern', Arial, sans-serif;
                font-size: 10px;
                letter-spacing: 0.5px;
                box-sizing: border-box;
            }

            .packaging-slip {
                width: 76.2mm;
                height: 101mm;
                padding: 3mm;
                box-sizing: border-box;
                overflow: hidden;
                page-break-after: always;
                box-shadow: none !important;
                border: none !important;
            }

            /* Use only :not(:last-child) to prevent last slip from forcing page break */
            .packaging-slip:not(:last-child) {
                page-break-after: always;
            }
            .packaging-slip:last-child {
                page-break-after: avoid;
            }

            table,
            tr,
            td {
                page-break-inside: avoid !important;
            }

            .barcode {
                max-height: 42px;
                overflow: hidden;
            }

            .no-print,
            .print-btn {
                display: none !important;
            }
        }

        /* ========== SCREEN ========== */
        body {
            font-family: 'Inter', 'Modern', Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
            letter-spacing: 0.5px;
            text-align: center;
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-family: Arial, sans-serif;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            transition: background 0.3s;
        }

        .print-btn:hover {
            background: #0056b3;
        }

        .print-btn i {
            margin-right: 8px;
        }

        .packaging-slip {
            width: 76.2mm;
            padding: 3mm;
            box-sizing: border-box;
        }

        .header {
            display: flex;
            gap: 7px;
            margin-bottom: 5px;
        }

        .barcode-section {
            text-align: right;
        }

        .website {
            font-family: 'Modern', 'Inter', Arial, sans-serif;
            font-size: 8px;
            margin-top: -15px;
        }

        .address {
            font-size: 8px;
        }

        .invoice-table {
            width: 100%;
            border: 2px solid #000;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .invoice-table td {
            border: 2px solid #000;
            padding: 3px 4px;
            font-size: 12px;
            vertical-align: middle;
        }

        .label-cell {
            width: 35%;
            text-align: left;
            font-weight: bold;
        }

        .value-cell {
            text-align: right;
        }

        .address-value {
            line-height: 1.3;
            text-align: right;
            padding: 5px !important;
        }

        .financial-table {
            width: 100%;
            border-collapse: collapse;
        }

        .financial-table td {
            border: none;
            font-size: 13px;
            padding: 1px 0;
        }

        .financial-label {
            text-align: left;
        }

        .financial-amount {
            text-align: right;
            white-space: nowrap;
        }

        .cod-amount {
            font-size: 15px;
            font-weight: bold;
        }

        /* ===== PRODUCT INFO (SMALL) ===== */
        .product-section {
            margin-top: 10px;
            margin-bottom: 10px;
            border: 1px dashed #000;
            padding: 3px;
            font-size: 9px;
            line-height: 1.2;
            max-height: 26mm;
            overflow: hidden;
            text-align: left;
        }

        .product-title {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 2px;
        }

        .product-item {
            display: flex;
            justify-content: space-between;
            gap: 4px;
        }

        .product-name {
            max-width: 80%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-qty {
            white-space: nowrap;
        }

        /* ===== CONSIGNMENT ===== */
        .consignment {
            margin-top: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 4px;
        }

        .consignment-text {
            font-size: 11px;
            text-align: left;
            line-height: 1.2;
        }

        .qr {
            width: 38px;
            height: 38px;
        }
    </style>
</head>

<body onload="window.print()">
    <button class="print-btn" onclick="window.print()">
        <i class="fas fa-print"></i> Print All Labels
    </button>

    @foreach ($sales as $singleSale)
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

        <div class="packaging-slip">
            <!-- HEADER -->
            <div class="header">
                @if ($general->sitename)
                    <div
                        style="display: flex; flex-direction: column; align-items: flex-start; justify-content: flex-start;">
                        <h6 style="margin-top:0px;font-size:14px;line-height: .6;margin-bottom:4px;">
                            {{ $general->sitename }}</h6>
                        @if (!empty($singleSale->courier_name))
                            <small
                                style="text-transform: uppercase;font-size: 9px; letter-spacing: 1px; line-height: 1;">{{ $singleSale->courier_name }}</small>
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
                    <td class="value-cell address-value">{{ strtoupper($customer_address) }}</td>
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
    @endforeach
</body>

</html>
