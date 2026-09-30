<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>
        Receipt {{ $order->order_number }}
    </title>

    <style>
        @page {
            margin: 28px 34px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.55;
            color: #3d2730;
            background: #ffffff;
        }

        .page {
            width: 100%;
        }

        .top-line {
            height: 4px;
            background: #7b1e3a;
            margin-bottom: 22px;
        }

        .header-table,
        .info-table,
        .summary-table,
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .brand-column {
            width: 62%;
            vertical-align: top;
        }

        .receipt-column {
            width: 38%;
            text-align: right;
            vertical-align: top;
        }

        .brand {
            font-family: DejaVu Serif, serif;
            font-size: 26px;
            font-weight: bold;
            letter-spacing: 1.5px;
            color: #64152f;
        }

        .brand-subtitle {
            margin-top: 5px;
            font-size: 8px;
            letter-spacing: 2.4px;
            text-transform: uppercase;
            color: #ad8a58;
        }

        .brand-description {
            margin-top: 8px;
            font-size: 8.5px;
            color: #92727c;
        }

        .document-label {
            font-size: 8px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #ad8a58;
        }

        .document-title {
            margin-top: 5px;
            font-family: DejaVu Serif, serif;
            font-size: 19px;
            font-weight: bold;
            color: #64152f;
        }

        .document-number {
            margin-top: 7px;
            font-size: 9px;
            color: #80616b;
        }

        .header-divider {
            margin-top: 22px;
            border-top: 1px solid #ead9de;
        }

        .status-box {
            margin-top: 20px;
            padding: 13px 16px;
            background: #f5faf6;
            border: 1px solid #cce2d2;
            border-left: 4px solid #43815a;
        }

        .status-table {
            width: 100%;
            border-collapse: collapse;
        }

        .status-icon {
            width: 34px;
            vertical-align: middle;
            font-size: 20px;
            color: #43815a;
        }

        .status-content {
            vertical-align: middle;
        }

        .status-title {
            font-size: 11px;
            font-weight: bold;
            letter-spacing: .5px;
            color: #28663e;
        }

        .status-description {
            margin-top: 3px;
            font-size: 8.5px;
            color: #5d8068;
        }

        .section-heading {
            margin-top: 25px;
            margin-bottom: 10px;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            color: #ad8a58;
        }

        .info-table {
            border: 1px solid #ead9de;
        }

        .info-table td {
            width: 50%;
            padding: 14px 16px;
            vertical-align: top;
        }

        .info-table td:first-child {
            border-right: 1px solid #ead9de;
        }

        .info-label {
            margin-bottom: 6px;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: #a17c87;
        }

        .customer-name {
            font-family: DejaVu Serif, serif;
            font-size: 13px;
            font-weight: bold;
            color: #64152f;
        }

        .info-value {
            margin-top: 4px;
            font-size: 9px;
            color: #624852;
        }

        .info-highlight {
            font-size: 11px;
            font-weight: bold;
            color: #64152f;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #ead9de;
        }

        .items-table thead {
            background: #64152f;
        }

        .items-table th {
            padding: 10px 9px;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: .6px;
            text-transform: uppercase;
            text-align: left;
            color: #ffffff;
            border: none;
        }

        .items-table th.text-right {
            text-align: right;
        }

        .items-table td {
            padding: 11px 9px;
            vertical-align: top;
            border-bottom: 1px solid #eee2e6;
            color: #4e3540;
        }

        .items-table tbody tr:last-child td {
            border-bottom: none;
        }

        .items-table tbody tr:nth-child(even) {
            background: #fdf9fa;
        }

        .product-name {
            font-weight: bold;
            font-size: 9.5px;
            color: #54283a;
        }

        .product-variant {
            margin-top: 3px;
            font-size: 8px;
            color: #92727c;
        }

        .sku {
            margin-top: 3px;
            font-size: 7.5px;
            color: #a17c87;
        }

        .text-right {
            text-align: right;
        }

        .summary-wrapper {
            width: 100%;
            margin-top: 20px;
        }

        .summary-table {
            width: 46%;
            margin-left: auto;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 6px 0;
            font-size: 9px;
        }

        .summary-label {
            color: #80616b;
        }

        .summary-value {
            text-align: right;
            color: #4e3540;
        }

        .summary-total td {
            padding-top: 12px;
            border-top: 2px solid #64152f;
            font-size: 13px;
            font-weight: bold;
            color: #64152f;
        }

        .summary-total .summary-value {
            font-size: 14px;
        }

        .payment-note {
            margin-top: 24px;
            padding: 13px 15px;
            background: #fcf8f9;
            border: 1px solid #ead9de;
        }

        .payment-note-title {
            margin-bottom: 5px;
            font-size: 9px;
            font-weight: bold;
            color: #64152f;
        }

        .payment-note-text {
            font-size: 8.5px;
            color: #80616b;
        }

        .signature-area {
            margin-top: 25px;
            text-align: right;
        }

        .signature-label {
            font-size: 8px;
            color: #92727c;
        }

        .signature-brand {
            margin-top: 7px;
            font-family: DejaVu Serif, serif;
            font-size: 15px;
            font-style: italic;
            color: #64152f;
        }

        .footer-divider {
            margin-top: 24px;
            border-top: 1px solid #ead9de;
        }

        .footer-table {
            margin-top: 12px;
        }

        .footer-left {
            width: 65%;
            text-align: left;
            vertical-align: top;
            font-size: 8px;
            color: #a17c87;
        }

        .footer-right {
            width: 35%;
            text-align: right;
            vertical-align: top;
            font-size: 8px;
            color: #a17c87;
        }

        .footer-brand {
            font-weight: bold;
            color: #64152f;
        }

        .gold {
            color: #ad8a58;
        }
    </style>
</head>

<body>

    <div class="page">

        <div class="top-line"></div>

        {{-- HEADER --}}
        <table class="header-table">
            <tr>
                <td class="brand-column">
                    <div class="brand">
                        ZALINA FASHION
                    </div>

                    <div class="brand-subtitle">
                        Elegance in Every Drape
                    </div>

                    <div class="brand-description">
                        Koleksi fashion muslimah dengan sentuhan elegan dan berkelas.
                    </div>
                </td>

                <td class="receipt-column">
                    <div class="document-label">
                        Official Document
                    </div>

                    <div class="document-title">
                        PAYMENT RECEIPT
                    </div>

                    <div class="document-number">
                        No. {{ $order->order_number }}
                    </div>
                </td>
            </tr>
        </table>

        <div class="header-divider"></div>

        {{-- PAYMENT STATUS --}}
        <div class="status-box">
            <table class="status-table">
                <tr>
                    <td class="status-icon">
                        ✓
                    </td>

                    <td class="status-content">
                        <div class="status-title">
                            PAYMENT VERIFIED
                        </div>

                        <div class="status-description">
                            Pembayaran pesanan telah berhasil dikonfirmasi oleh admin Zalina Fashion.
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- CUSTOMER INFORMATION --}}
        <div class="section-heading">
            Customer & Order Information
        </div>

        <table class="info-table">
            <tr>
                <td>
                    <div class="info-label">
                        Customer
                    </div>

                    <div class="customer-name">
                        {{ $order->customer_name }}
                    </div>

                    <div class="info-value">
                        {{ $order->customer_email }}
                    </div>

                    @if($order->customer_phone)
                        <div class="info-value">
                            {{ $order->customer_phone }}
                        </div>
                    @endif
                </td>

                <td>
                    <div class="info-label">
                        Order Number
                    </div>

                    <div class="info-highlight">
                        {{ $order->order_number }}
                    </div>

                    <div class="info-value">
                        @if($payment->updated_at)
                            {{ $payment->updated_at->format('d F Y, H:i') }}
                        @else
                            -
                        @endif
                    </div>

                    <div class="info-label" style="margin-top: 12px;">
                        Payment Method
                    </div>

                    <div class="info-value">
                        {{ optional($payment->method)->name ?? 'Pembayaran' }}
                    </div>

                    @if(filled($order->shipping_courier))
                        <div class="info-label" style="margin-top: 12px;">
                            Kurir
                        </div>

                        <div class="info-value">
                            {{ strtoupper($order->shipping_courier) }}
                            @if(filled($order->shipping_service))
                                &mdash; {{ $order->shipping_service }}
                            @endif
                        </div>
                    @endif

                    @if(filled($order->tracking_number))
                        <div class="info-label" style="margin-top: 12px;">
                            Nomor Resi
                        </div>

                        <div class="info-value">
                            {{ $order->tracking_number }}
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        {{-- ORDER DETAILS --}}
        <div class="section-heading">
            Order Details
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 48%;">
                        Produk
                    </th>

                    <th class="text-right" style="width: 12%;">
                        Qty
                    </th>

                    <th class="text-right" style="width: 20%;">
                        Harga
                    </th>

                    <th class="text-right" style="width: 20%;">
                        Total
                    </th>
                </tr>
            </thead>

            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>
                            <div class="product-name">
                                {{ $item->product_name }}
                            </div>

                            @if($item->variant_name ?? false)
                                <div class="product-variant">
                                    Varian: {{ $item->variant_name }}
                                </div>
                            @endif

                            @if($item->sku)
                                <div class="sku">
                                    SKU: {{ $item->sku }}
                                </div>
                            @endif
                        </td>

                        <td class="text-right">
                            {{ $item->quantity }}
                        </td>

                        <td class="text-right">
                            Rp {{ number_format($item->price, 0, ',', '.') }}
                        </td>

                        <td class="text-right">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- SUMMARY --}}
        <div class="summary-wrapper">
            <table class="summary-table">
                <tr>
                    <td class="summary-label">
                        Subtotal
                    </td>

                    <td class="summary-value">
                        Rp {{ number_format($order->subtotal, 0, ',', '.') }}
                    </td>
                </tr>

                @if((float) ($order->discount_total ?? 0) > 0)
                    <tr>
                        <td class="summary-label">
                            Diskon
                        </td>

                        <td class="summary-value">
                            - Rp {{ number_format((float) $order->discount_total, 0, ',', '.') }}
                        </td>
                    </tr>
                @endif

                <tr>
                    <td class="summary-label">
                        Pengiriman
                    </td>

                    <td class="summary-value">
                        Rp {{ number_format((float) ($order->shipping_total ?? 0), 0, ',', '.') }}
                    </td>
                </tr>

                {{-- PERBAIKAN: baris Biaya Admin sebelumnya tidak ada di PDF ini. --}}
                <tr>
                    <td class="summary-label">
                        Biaya Admin
                    </td>

                    <td class="summary-value">
                        Rp {{ number_format((float) ($order->admin_fee ?? 0), 0, ',', '.') }}
                    </td>
                </tr>

                <tr class="summary-total">
                    <td>
                        TOTAL
                    </td>

                    <td class="summary-value">
                        Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                    </td>
                </tr>
            </table>
        </div>

        {{-- NOTE --}}
        <div class="payment-note">
            <div class="payment-note-title">
                Thank you for shopping with Zalina Fashion
            </div>

            <div class="payment-note-text">
                Receipt ini merupakan bukti resmi bahwa pembayaran pesanan telah
                dikonfirmasi oleh admin Zalina Fashion. Simpan dokumen ini sebagai
                bukti transaksi Anda.
            </div>
        </div>

        {{-- SIGNATURE --}}
        <div class="signature-area">
            <div class="signature-label">
                With elegance,
            </div>

            <div class="signature-brand">
                Zalina Fashion
            </div>
        </div>

        {{-- FOOTER --}}
        <div class="footer-divider"></div>

        <table class="footer-table">
            <tr>
                <td class="footer-left">
                    <span class="footer-brand">ZALINA FASHION</span>
                    <br>
                    Elegance in Every Drape
                </td>

                <td class="footer-right">
                    Receipt generated on {{ date('d F Y') }}
                    <br>
                    <span class="gold">© {{ date('Y') }}</span>
                    · All rights reserved
                </td>
            </tr>
        </table>

    </div>

</body>
</html>