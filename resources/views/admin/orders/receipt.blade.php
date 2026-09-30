@php
    use App\Models\Setting;

    /*
    |--------------------------------------------------------------------------
    | NOTA PESANAN (ADMIN)
    |--------------------------------------------------------------------------
    | View ini dipanggil OrderController@printReceipt dengan variabel $order
    | (relasi items, items.product, items.variant, user, payment, payment.method
    | sudah di-load). Path: resources/views/admin/orders/receipt.blade.php
    */

    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

    $siteName = Setting::value('site_name', 'Zalina Fashion');
    $senderAddress = Setting::value('sender_address', '');
    $senderPhone = Setting::value('sender_phone', '');

    $orderNumber = $order->order_number ?: ('#' . $order->id);

    $subtotal = (float) ($order->subtotal ?? 0);
    $discount = (float) ($order->discount_total ?? 0);
    $shipping = (float) ($order->shipping_total ?? 0);
    $adminFee = (float) ($order->admin_fee ?? 0);
    $grand = (float) ($order->grand_total ?? 0);

    // Bila subtotal snapshot kosong, hitung dari item
    if ($subtotal <= 0) {
        $subtotal = (float) $order->items->sum(fn ($i) => (float) ($i->subtotal ?? 0));
    }

    $isPaid = strtolower((string) $order->payment_status) === 'paid';
    $payMethod = $order->payment?->method;

    $courier = trim(strtoupper((string) $order->shipping_courier) . ' ' . (string) $order->shipping_service);
    $trackingNumber = trim((string) ($order->tracking_number ?? ''));
    $totalQty = (int) $order->items->sum(fn ($i) => (int) ($i->quantity ?? 0));

    $address = collect([$order->shipping_address, $order->destination_name])
        ->filter(fn ($v) => filled($v))
        ->implode(', ');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Pesanan — {{ $orderNumber }}</title>

    <style>
        :root {
            --maroon: #64152d;
            --maroon-dark: #3f0d1c;
            --gold: #b99658;
            --ink: #171117;
            --muted: #756b71;
            --line: #e3d9dc;
            --screen: #eee9eb;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            background: var(--screen);
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .toolbar {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            padding: 18px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 43px;
            padding: 11px 20px;
            border-radius: 14px;
            border: 1px solid transparent;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-print { color: #fff; background: linear-gradient(135deg, var(--maroon), var(--maroon-dark)); }
        .btn-back { color: var(--maroon); background: #fff; border-color: #dbcbd1; }

        .sheet {
            width: 148mm;
            max-width: 100%;
            margin: 6px auto 35px;
            padding: 10mm;
            background: #fff;
            box-shadow: 0 18px 55px rgba(63, 13, 28, .16);
        }

        .head {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 5mm;
            border-bottom: 2px solid var(--maroon);
        }

        .brand { font-size: 20px; font-weight: 900; color: var(--maroon-dark); }
        .brand-sub { margin-top: 2mm; font-size: 9px; line-height: 1.5; color: var(--muted); }
        .doc { text-align: right; }
        .doc-title { font-size: 9px; font-weight: 800; letter-spacing: 1.2px; text-transform: uppercase; color: var(--gold); }
        .doc-number { margin-top: 1.5mm; font-size: 13px; font-weight: 900; color: var(--maroon-dark); overflow-wrap: anywhere; }
        .doc-date { margin-top: 1mm; font-size: 9px; color: var(--muted); }

        .status {
            display: inline-block;
            margin-top: 2mm;
            padding: 1.2mm 3mm;
            border-radius: 99px;
            font-size: 8px;
            font-weight: 900;
            letter-spacing: .6px;
            text-transform: uppercase;
        }
        .status.paid { color: #166534; background: #dcfce7; }
        .status.unpaid { color: #92400e; background: #fef3c7; }

        .cols { display: grid; grid-template-columns: 1fr 1fr; gap: 6mm; padding: 5mm 0; border-bottom: 1px solid var(--line); }
        .label { margin-bottom: 1.5mm; font-size: 8px; font-weight: 900; letter-spacing: 1px; text-transform: uppercase; color: var(--maroon); }
        .val { font-size: 10px; line-height: 1.5; overflow-wrap: anywhere; }
        .val b { font-size: 11px; }

        table { width: 100%; border-collapse: collapse; margin-top: 5mm; }
        th { padding: 2mm 1.5mm; text-align: left; font-size: 8px; letter-spacing: .8px; text-transform: uppercase; color: var(--muted); border-bottom: 1px solid var(--ink); }
        td { padding: 2.2mm 1.5mm; font-size: 10px; vertical-align: top; border-bottom: 1px dashed var(--line); }
        .r { text-align: right; white-space: nowrap; }
        .c { text-align: center; }
        .variant { display: block; margin-top: .6mm; font-size: 8.5px; color: #806873; }

        .totals { width: 62%; margin: 4mm 0 0 auto; }
        .totals div { display: flex; justify-content: space-between; gap: 8px; padding: 1.2mm 0; font-size: 10px; }
        .totals .grand { margin-top: 1mm; padding-top: 2.5mm; border-top: 2px solid var(--maroon); font-size: 13px; font-weight: 900; color: var(--maroon-dark); }

        .resi { margin-top: 5mm; padding: 3mm 4mm; border: 1px dashed #bcaeb4; border-radius: 3mm; background: #faf7f8; }
        .resi-no { margin-top: 1mm; font-family: "Courier New", monospace; font-size: 14px; font-weight: 900; letter-spacing: .6px; color: var(--maroon-dark); overflow-wrap: anywhere; }

        .foot { margin-top: 6mm; padding-top: 4mm; text-align: center; font-size: 9px; line-height: 1.6; color: var(--muted); border-top: 1px solid var(--line); }
        .foot b { color: var(--maroon); }

        @media print {
            @page { size: A5 portrait; margin: 0; }
            html, body { background: #fff; }
            .toolbar { display: none !important; }
            .sheet { width: 148mm; margin: 0; box-shadow: none; }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <button type="button" class="btn btn-print" onclick="window.print()">Cetak Nota</button>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-back">Kembali</a>
    </div>

    <main class="sheet">

        <header class="head">
            <div>
                <div class="brand">{{ $siteName }}</div>
                @if(filled($senderAddress) || filled($senderPhone))
                    <div class="brand-sub">
                        @if(filled($senderAddress)){{ $senderAddress }}<br>@endif
                        @if(filled($senderPhone))Telp: {{ $senderPhone }}@endif
                    </div>
                @endif
            </div>

            <div class="doc">
                <div class="doc-title">Nota Pesanan</div>
                <div class="doc-number">{{ $orderNumber }}</div>
                <div class="doc-date">{{ $order->created_at?->translatedFormat('d F Y, H:i') }}</div>
                <span class="status {{ $isPaid ? 'paid' : 'unpaid' }}">{{ $isPaid ? 'Lunas' : 'Belum Lunas' }}</span>
            </div>
        </header>

        <section class="cols">
            <div>
                <div class="label">Pembeli</div>
                <div class="val">
                    <b>{{ $order->customer_name ?: ($order->user?->name ?? '-') }}</b><br>
                    {{ $order->customer_phone ?: '-' }}
                    @if(filled($order->customer_email))<br>{{ $order->customer_email }}@endif
                </div>
            </div>

            <div>
                <div class="label">Alamat Pengiriman</div>
                <div class="val">{{ $address ?: '-' }}</div>
            </div>
        </section>

        <table>
            <thead>
                <tr>
                    <th style="width:6%">No</th>
                    <th>Produk</th>
                    <th class="c" style="width:9%">Qty</th>
                    <th class="r" style="width:20%">Harga</th>
                    <th class="r" style="width:22%">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($order->items as $index => $item)
                    @php
                        $itemName = $item->product_name ?: ($item->product->name ?? 'Produk');
                        $itemVariant = $item->variant_name ?: ($item->variant->name ?? null);
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            {{ $itemName }}
                            @if(filled($itemVariant))
                                <span class="variant">Varian/Warna: {{ $itemVariant }}</span>
                            @endif
                        </td>
                        <td class="c">{{ (int) $item->quantity }}</td>
                        <td class="r">{{ $rp($item->price) }}</td>
                        <td class="r"><b>{{ $rp($item->subtotal) }}</b></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="c">Tidak ada produk.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="totals">
            <div><span>Subtotal ({{ $totalQty }} item)</span><span>{{ $rp($subtotal) }}</span></div>
            @if($discount > 0)
                <div><span>Diskon</span><span>- {{ $rp($discount) }}</span></div>
            @endif
            <div><span>Ongkir</span><span>{{ $shipping > 0 ? $rp($shipping) : 'Gratis' }}</span></div>
            @if($adminFee > 0)
                <div><span>Biaya admin</span><span>{{ $rp($adminFee) }}</span></div>
            @endif
            <div class="grand"><span>Total</span><span>{{ $rp($grand) }}</span></div>
        </div>

        <section class="cols" style="border-bottom:0;margin-top:3mm">
            <div>
                <div class="label">Pembayaran</div>
                <div class="val">{{ $payMethod?->name ?: '-' }}</div>
            </div>

            <div>
                <div class="label">Pengiriman</div>
                <div class="val">
                    {{ trim($courier) !== '' ? $courier : '-' }}
                    @if(filled($order->shipping_etd))<br>Estimasi {{ $order->shipping_etd }}@endif
                </div>
            </div>
        </section>

        @if($trackingNumber !== '')
            <div class="resi">
                <div class="label" style="margin:0">Nomor Resi / AWB</div>
                <div class="resi-no">{{ $trackingNumber }}</div>
            </div>
        @endif

        <footer class="foot">
            <b>Terima kasih telah berbelanja di {{ $siteName }}.</b><br>
            Simpan nota ini sebagai bukti pesanan {{ $orderNumber }}.
        </footer>

    </main>

</body>
</html>