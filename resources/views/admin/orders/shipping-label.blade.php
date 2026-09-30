@php

    use App\Models\Setting;

    /*
    |--------------------------------------------------------------------------
    | STORE / SENDER
    |--------------------------------------------------------------------------
    */

    $siteName = Setting::value(
        'site_name',
        'Zalina Fashion'
    );

    $senderName = Setting::value(
        'sender_name',
        $siteName
    );

    $senderAddress = Setting::value(
        'sender_address',
        ''
    );

    $senderCity = Setting::value(
        'sender_city',
        ''
    );

    $senderProvince = Setting::value(
        'sender_province',
        ''
    );

    $senderPostal = Setting::value(
        'sender_postal_code',
        ''
    );

    $senderPhone = Setting::value(
        'sender_phone',
        ''
    );


    /*
    |--------------------------------------------------------------------------
    | ORDER DATA
    |--------------------------------------------------------------------------
    */

    $receiverName = $order->customer_name ?: '-';

    $receiverPhone = $order->customer_phone ?: '-';

    $receiverAddress = $order->shipping_address ?: '-';

    $destination = $order->destination_name ?: '-';

    $courier = $order->shipping_courier ?: '-';

    $service = $order->shipping_service ?: '-';

    $etd = $order->shipping_etd ?: '-';

    $trackingNumber = trim(
        (string) ($order->tracking_number ?? '')
    );

    $orderNumber = $order->order_number ?: '-';

    $shippingWeight = (int) (
        $order->shipping_weight ?? 0
    );


    /*
    |--------------------------------------------------------------------------
    | FALLBACK WEIGHT FROM ORDER ITEMS
    |--------------------------------------------------------------------------
    */

    if ($shippingWeight <= 0) {

        $shippingWeight = $order->items->sum(
            function ($item) {

                $weight = (int) (
                    $item->weight
                    ?? $item->product->weight
                    ?? 0
                );

                $quantity = (int) (
                    $item->quantity ?? 0
                );

                return $weight * $quantity;
            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | TOTAL ITEMS
    |--------------------------------------------------------------------------
    */

    $totalQuantity = $order->items->sum(
        fn ($item) => (int) (
            $item->quantity ?? 0
        )
    );


    /*
    |--------------------------------------------------------------------------
    | SENDER LOCATION
    |--------------------------------------------------------------------------
    */

    $senderLocation = collect([
        $senderCity,
        $senderProvince,
        $senderPostal,
    ])
        ->filter(fn ($value) => filled($value))
        ->implode(', ');


    /*
    |--------------------------------------------------------------------------
    | PAYMENT
    |--------------------------------------------------------------------------
    */

    $paymentMethod = null;

    if (
        $order->payment &&
        $order->payment->payment_method_id
    ) {

        $paymentMethod =
            $order->payment->method->name
            ?? null;

    }


    /*
    |--------------------------------------------------------------------------
    | COD DETECTION
    |--------------------------------------------------------------------------
    */

    $paymentType = strtolower(
        (string) (
            $order->payment->method->type
            ?? ''
        )
    );

    $isCod =
        str_contains($paymentType, 'cod') ||
        str_contains($paymentType, 'cash');


    /*
    |--------------------------------------------------------------------------
    | ITEM SUMMARY
    |--------------------------------------------------------------------------
    */

    $itemLines = [];

    foreach ($order->items as $item) {

        $name = $item->product_name
            ?: (
                $item->product->name
                ?? 'Produk'
            );

        $variant = $item->variant_name
            ?: (
                $item->variant->name
                ?? null
            );

        $quantity = (int) (
            $item->quantity ?? 0
        );

        $itemLines[] = [
            'name' => $name,
            'variant' => $variant,
            'quantity' => $quantity,
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | PAYMENT LABEL
    |--------------------------------------------------------------------------
    */

    $paymentLabel = '-';

    if ($isCod) {

        $paymentLabel = 'COD';

    } elseif ($paymentMethod) {

        $paymentLabel = $paymentMethod;

    } else {

        $paymentLabel = ucfirst(
            (string) (
                $order->payment_status
                ?? 'Unpaid'
            )
        );

    }


    /*
    |--------------------------------------------------------------------------
    | SAFE DISPLAY VALUES
    |--------------------------------------------------------------------------
    */

    $displaySenderName = $senderName ?: $siteName;

    $displaySenderAddress = $senderAddress ?: '-';

    $displaySenderLocation = $senderLocation ?: '-';

    $displayCourier = $courier ?: '-';

    $displayService = $service ?: '-';

    $displayDestination = $destination ?: '-';

    $displayEtd = $etd ?: '-';

@endphp


<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="theme-color"
        content="#64152d"
    >

    <title>
        Shipping Label — {{ $orderNumber }}
    </title>


    <style>

        :root {
            --maroon: #64152d;
            --maroon-dark: #3f0d1c;
            --maroon-soft: #8f3651;
            --gold: #b99658;
            --gold-light: #ead8a9;
            --ink: #171117;
            --muted: #756b71;
            --line: #d8cfd2;
            --paper: #ffffff;
            --screen: #eee9eb;
        }


        * {
            box-sizing: border-box;
        }


        html,
        body {
            margin: 0;
            padding: 0;
            background: var(--screen);
            color: var(--ink);
            font-family:
                Arial,
                Helvetica,
                sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }


        body {
            min-height: 100vh;
        }


        button,
        a {
            font: inherit;
        }


        .toolbar {
            width: 100%;
            padding: 18px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }


        .toolbar-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            min-height: 43px;
            padding: 11px 18px;
            border-radius: 14px;
            border: 1px solid transparent;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .01em;
            text-decoration: none;
            cursor: pointer;
            transition:
                transform .2s ease,
                box-shadow .2s ease,
                background .2s ease;
        }


        .toolbar-button:hover {
            transform: translateY(-1px);
        }


        .toolbar-button:focus-visible {
            outline: 3px solid rgba(185, 150, 88, .45);
            outline-offset: 3px;
        }


        .btn-print {
            color: #ffffff;
            background:
                linear-gradient(
                    135deg,
                    var(--maroon),
                    var(--maroon-dark)
                );
            box-shadow:
                0 8px 20px rgba(100, 21, 45, .22);
        }


        .btn-print:hover {
            box-shadow:
                0 12px 26px rgba(100, 21, 45, .3);
        }


        .btn-back {
            color: var(--maroon);
            background: #ffffff;
            border-color: #dbcbd1;
        }


        .btn-back:hover {
            background: #fff8fa;
            border-color: var(--gold);
        }


        .label-page {
            width: 100mm;
            min-height: 150mm;
            margin: 12px auto 35px;
            padding: 4mm;
            background: #ffffff;
            box-shadow:
                0 18px 55px rgba(63, 13, 28, .16);
        }


        .label {
            position: relative;
            width: 100%;
            overflow: hidden;
            border: 1px solid #1b1518;
            background: var(--paper);
        }


        .label::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                linear-gradient(
                    135deg,
                    rgba(185, 150, 88, .05),
                    transparent 32%
                );
        }


        .header {
            position: relative;
            padding: 4mm;
            color: #ffffff;
            background:
                linear-gradient(
                    135deg,
                    var(--maroon-dark),
                    var(--maroon)
                );
            border-bottom: 1px solid #1b1518;
        }


        .header::after {
            content: "";
            position: absolute;
            right: -13mm;
            top: -15mm;
            width: 38mm;
            height: 38mm;
            border: 1px solid rgba(234, 216, 169, .45);
            border-radius: 50%;
        }


        .brand-row {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
        }


        .brand {
            max-width: 55mm;
            font-size: 20px;
            font-weight: 900;
            line-height: 1.05;
            letter-spacing: .3px;
            overflow-wrap: anywhere;
        }


        .brand-sub {
            margin-top: 2mm;
            color: var(--gold-light);
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }


        .order-box {
            min-width: 28mm;
            text-align: right;
        }


        .order-label {
            color: var(--gold-light);
            font-size: 7px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
        }


        .order-number {
            margin-top: 1.5mm;
            color: #ffffff;
            font-size: 10px;
            font-weight: 900;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }


        .section {
            position: relative;
            padding: 3.5mm;
            border-bottom: 1px solid var(--line);
        }


        .section:last-child {
            border-bottom: 0;
        }


        .section-title {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 2mm;
            color: var(--maroon);
            font-size: 8px;
            font-weight: 900;
            letter-spacing: 1px;
            line-height: 1.2;
            text-transform: uppercase;
        }


        .section-title::before {
            content: "";
            display: inline-block;
            width: 13px;
            height: 2px;
            background: var(--gold);
        }


        .person-name {
            color: var(--maroon-dark);
            font-size: 14px;
            font-weight: 900;
            line-height: 1.2;
            overflow-wrap: anywhere;
        }


        .phone {
            margin-top: 1.5mm;
            color: #40363b;
            font-size: 9px;
            font-weight: 700;
            overflow-wrap: anywhere;
        }


        .address {
            margin-top: 2mm;
            color: #332b30;
            font-size: 9px;
            line-height: 1.5;
            white-space: pre-line;
            overflow-wrap: anywhere;
        }


        .location {
            margin-top: 1.5mm;
            color: #5f5058;
            font-size: 8.5px;
            font-weight: 700;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }


        .shipping-grid {
            position: relative;
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-bottom: 1px solid #1b1518;
        }


        .shipping-cell {
            min-width: 0;
            padding: 3mm;
            border-right: 1px solid var(--line);
        }


        .shipping-cell:nth-child(2n) {
            border-right: 0;
        }


        .shipping-cell:nth-child(n + 3) {
            border-top: 1px solid var(--line);
        }


        .cell-label {
            margin-bottom: 1.3mm;
            color: var(--muted);
            font-size: 7px;
            font-weight: 800;
            letter-spacing: .5px;
            line-height: 1.2;
            text-transform: uppercase;
        }


        .cell-value {
            color: var(--maroon-dark);
            font-size: 9.5px;
            font-weight: 900;
            line-height: 1.35;
            overflow-wrap: anywhere;
            word-break: break-word;
        }


        .tracking-section {
            position: relative;
            padding: 4mm;
            text-align: center;
            border-bottom: 1px solid #1b1518;
            background:
                linear-gradient(
                    180deg,
                    #fffdfb,
                    #ffffff
                );
        }


        .tracking-title {
            color: var(--muted);
            font-size: 7.5px;
            font-weight: 900;
            letter-spacing: 1.2px;
            line-height: 1.2;
            text-transform: uppercase;
        }


        .tracking-number {
            margin-top: 1.5mm;
            color: var(--maroon-dark);
            font-family:
                "Courier New",
                Courier,
                monospace;
            font-size: 16px;
            font-weight: 900;
            letter-spacing: .7px;
            line-height: 1.25;
            overflow-wrap: anywhere;
            word-break: break-all;
        }


        .barcode-wrap {
            width: 100%;
            min-height: 19mm;
            margin-top: 3mm;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }


        #barcode {
            display: block;
            width: 100%;
            max-width: 100%;
            height: 18mm;
        }


        .no-tracking {
            margin-top: 3mm;
            padding: 4mm;
            color: #6d6268;
            border: 1px dashed #bcaeb4;
            background: #faf7f8;
            font-size: 9px;
            font-weight: 700;
            line-height: 1.5;
        }


        .items {
            position: relative;
            padding: 3mm;
            border-bottom: 1px solid #1b1518;
        }


        .item {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
            padding: 1.7mm 0;
            border-bottom: 1px dashed #c9bec3;
            font-size: 8.5px;
            line-height: 1.4;
        }


        .item:last-child {
            border-bottom: 0;
        }


        .item-name {
            flex: 1;
            min-width: 0;
            color: #332b30;
            overflow-wrap: anywhere;
            word-break: break-word;
        }


        .item-variant {
            color: #806873;
            font-size: 7.5px;
        }


        .item-qty {
            flex: 0 0 auto;
            color: var(--maroon);
            font-weight: 900;
            white-space: nowrap;
        }


        .footer {
            position: relative;
            padding: 3mm;
            text-align: center;
            background: #fffaf5;
        }


        .footer-main {
            color: var(--maroon);
            font-size: 9px;
            font-weight: 900;
            letter-spacing: .3px;
        }


        .footer-small {
            margin-top: 1mm;
            color: #75656c;
            font-size: 7px;
            line-height: 1.4;
        }


        .footer-line {
            width: 18mm;
            height: 1px;
            margin: 2mm auto 0;
            background: var(--gold);
        }


        @media screen and (max-width: 600px) {

            .toolbar {
                padding: 14px 12px;
            }


            .toolbar-button {
                flex: 1 1 auto;
                min-width: 145px;
            }


            .label-page {
                margin-top: 5px;
                margin-bottom: 24px;
                transform-origin: top center;
            }

        }


        @media print {

            @page {
                size: 100mm 150mm;
                margin: 0;
            }


            html,
            body {
                width: 100mm;
                min-height: 150mm;
                background: #ffffff;
            }


            .toolbar {
                display: none !important;
            }


            .label-page {
                width: 100mm;
                min-height: 150mm;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }


            .label {
                width: 100mm;
                min-height: 150mm;
                border: 1px solid #111111;
            }


            .header,
            .footer,
            .tracking-section,
            .no-tracking {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

        }

    </style>

</head>


<body>


    {{-- ========================================================= --}}
    {{-- TOOLBAR --}}
    {{-- ========================================================= --}}

    <div class="toolbar">

        <button
            type="button"
            class="toolbar-button btn-print"
            onclick="window.print()"
        >
            <svg
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"
                />
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M6 14h12v7H6z"
                />
            </svg>

            Cetak Shipping Label
        </button>


        <a
            href="{{ route('admin.orders.show', $order) }}"
            class="toolbar-button btn-back"
        >
            <svg
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M15 18l-6-6 6-6"
                />
            </svg>

            Kembali
        </a>

    </div>


    {{-- ========================================================= --}}
    {{-- SHIPPING LABEL --}}
    {{-- ========================================================= --}}

    <main class="label-page">

        <div class="label">


            {{-- ================================================= --}}
            {{-- HEADER --}}
            {{-- ================================================= --}}

            <header class="header">

                <div class="brand-row">

                    <div>

                        <div class="brand">
                            {{ $siteName }}
                        </div>

                        <div class="brand-sub">
                            Official Shipping Label
                        </div>

                    </div>


                    <div class="order-box">

                        <div class="order-label">
                            Order
                        </div>

                        <div class="order-number">
                            {{ $orderNumber }}
                        </div>

                    </div>

                </div>

            </header>


            {{-- ================================================= --}}
            {{-- SENDER --}}
            {{-- ================================================= --}}

            <section class="section">

                <div class="section-title">
                    Pengirim / Sender
                </div>


                <div class="person-name">
                    {{ $displaySenderName }}
                </div>


                @if(filled($senderPhone))

                    <div class="phone">
                        Telp: {{ $senderPhone }}
                    </div>

                @endif


                <div class="address">
                    {{ $displaySenderAddress }}
                </div>


                <div class="location">
                    {{ $displaySenderLocation }}
                </div>

            </section>


            {{-- ================================================= --}}
            {{-- RECEIVER --}}
            {{-- ================================================= --}}

            <section class="section">

                <div class="section-title">
                    Penerima / Receiver
                </div>


                <div class="person-name">
                    {{ $receiverName }}
                </div>


                <div class="phone">
                    Telp: {{ $receiverPhone }}
                </div>


                <div class="address">
                    {{ $receiverAddress }}
                </div>


                @if($displayDestination !== '-')

                    <div class="location">
                        Tujuan: {{ $displayDestination }}
                    </div>

                @endif

            </section>


            {{-- ================================================= --}}
            {{-- SHIPPING INFORMATION --}}
            {{-- ================================================= --}}

            <div class="shipping-grid">


                <div class="shipping-cell">

                    <div class="cell-label">
                        Kurir
                    </div>

                    <div class="cell-value">
                        {{ $displayCourier }}
                    </div>

                </div>


                <div class="shipping-cell">

                    <div class="cell-label">
                        Layanan
                    </div>

                    <div class="cell-value">
                        {{ $displayService }}
                    </div>

                </div>


                <div class="shipping-cell">

                    <div class="cell-label">
                        Berat
                    </div>

                    <div class="cell-value">

                        @if($shippingWeight > 0)

                            {{ number_format($shippingWeight, 0, ',', '.') }}
                            gram

                        @else

                            -

                        @endif

                    </div>

                </div>


                <div class="shipping-cell">

                    <div class="cell-label">
                        Jumlah
                    </div>

                    <div class="cell-value">
                        {{ $totalQuantity }} item
                    </div>

                </div>


                @if($displayEtd !== '-')

                    <div class="shipping-cell">

                        <div class="cell-label">
                            Estimasi
                        </div>

                        <div class="cell-value">
                            {{ $displayEtd }}
                        </div>

                    </div>

                @endif


                <div class="shipping-cell">

                    <div class="cell-label">
                        Pembayaran
                    </div>

                    <div class="cell-value">
                        {{ $paymentLabel }}
                    </div>

                </div>

            </div>


            {{-- ================================================= --}}
            {{-- TRACKING / BARCODE --}}
            {{-- ================================================= --}}

            <section class="tracking-section">

                <div class="tracking-title">
                    Nomor Resi / Airwaybill
                </div>


                @if(filled($trackingNumber))

                    <div class="tracking-number">
                        {{ $trackingNumber }}
                    </div>


                    <div class="barcode-wrap">

                        <svg
                            id="barcode"
                            role="img"
                            aria-label="Barcode {{ $trackingNumber }}"
                        ></svg>

                    </div>

                @else

                    <div class="no-tracking">

                        Nomor resi belum dimasukkan.

                        <br>

                        Shipping label dapat dicetak
                        setelah nomor resi tersedia.

                    </div>

                @endif

            </section>


            {{-- ================================================= --}}
            {{-- PACKAGE CONTENT --}}
            {{-- ================================================= --}}

            @if(count($itemLines) > 0)

                <section class="items">

                    <div class="section-title">
                        Isi Paket
                    </div>


                    @foreach($itemLines as $itemLine)

                        <div class="item">

                            <div class="item-name">

                                <div>
                                    {{ $itemLine['name'] }}
                                </div>


                                @if(filled($itemLine['variant']))

                                    <div class="item-variant">
                                        Varian:
                                        {{ $itemLine['variant'] }}
                                    </div>

                                @endif

                            </div>


                            <div class="item-qty">
                                × {{ $itemLine['quantity'] }}
                            </div>

                        </div>

                    @endforeach

                </section>

            @endif


            {{-- ================================================= --}}
            {{-- FOOTER --}}
            {{-- ================================================= --}}

            <footer class="footer">

                <div class="footer-main">
                    {{ $siteName }}
                </div>

                <div class="footer-small">
                    Order {{ $orderNumber }}
                    ·
                    Terima kasih telah berbelanja.
                </div>

                <div class="footer-line"></div>

            </footer>


        </div>

    </main>


    {{-- ========================================================= --}}
    {{-- BARCODE LIBRARY --}}
    {{-- ========================================================= --}}

    @if(filled($trackingNumber))

        <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>

        <script>

            document.addEventListener(
                'DOMContentLoaded',
                function () {

                    if (
                        typeof JsBarcode === 'undefined'
                    ) {
                        return;
                    }


                    const barcodeElement =
                        document.querySelector('#barcode');


                    if (!barcodeElement) {
                        return;
                    }


                    JsBarcode(
                        barcodeElement,
                        @json($trackingNumber),
                        {
                            format: 'CODE128',
                            displayValue: false,
                            height: 65,
                            width: 2,
                            margin: 0,
                            background: '#ffffff',
                            lineColor: '#000000'
                        }
                    );

                    @if(request()->boolean('print'))
                        // Dibuka dari tombol "Cetak & Kirim Resi": langsung tampilkan dialog cetak
                        setTimeout(function () {
                            window.print();
                        }, 400);
                    @endif

                }
            );

        </script>

    @endif


</body>

</html>