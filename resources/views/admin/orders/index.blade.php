@extends('layouts.admin')

@section('title', 'Pesanan')

@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | SHIPPING STATUS CONFIGURATION
    |--------------------------------------------------------------------------
    */

    $statusMap = [

        'waiting' => [
            'step' => 0,
            'text' => 'Menunggu proses admin',
        ],

        'packing' => [
            'step' => 1,
            'text' => 'Pesanan sedang dikemas',
        ],

        'packed' => [
            'step' => 2,
            'text' => 'Pesanan selesai dikemas',
        ],

        'handed_to_courier' => [
            'step' => 3,
            'text' => 'Paket sudah diserahkan ke kurir',
        ],

        'shipped' => [
            'step' => 4,
            'text' => 'Paket sedang dalam perjalanan',
        ],

        'delivered' => [
            'step' => 5,
            'text' => 'Pesanan telah diterima pelanggan',
        ],

    ];


    /*
    |--------------------------------------------------------------------------
    | DEFAULT DATA DARI CONTROLLER
    |--------------------------------------------------------------------------
    |
    | $statusCounts, $stats, dan $filters dikirim oleh OrderController::index().
    | Default di bawah menjaga halaman tetap jalan kalau controller belum
    | diperbarui.
    |
    */

    $statusCounts = $statusCounts ?? [];
    $stats = $stats ?? ['revenue' => 0];
    $filters = $filters ?? [
        'q' => (string) request('q', ''),
        'filter' => (string) request('filter', ''),
    ];

    $activeFilter = $filters['filter'] ?? '';
    $searchTerm = $filters['q'] ?? '';

    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

    $storeName = \App\Models\Setting::value('site_name', 'Zalina Fashion');

    $filterTabs = [
        ''            => 'Semua',
        'waiting'     => 'Perlu Diproses',
        'packing'     => 'Dikemas',
        'shipping'    => 'Dalam Pengiriman',
        'no_tracking' => 'Resi Belum Diisi',
        'delivered'   => 'Selesai',
        'cancelled'   => 'Dibatalkan',
    ];

@endphp


<div class="space-y-6">


    {{-- ========================================================= --}}
    {{-- PAGE HEADER --}}
    {{-- ========================================================= --}}

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">

        <div>

            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gold-500">
                Order Management
            </p>

            <h1 class="font-serif text-3xl text-maroon-900 mt-1">
                Pesanan Pelanggan
            </h1>

            <p class="text-sm text-maroon-500 mt-2">
                Kelola pesanan, pembayaran, pengiriman, nomor resi,
                dan progress pesanan pelanggan.
            </p>

        </div>


        <div class="bg-white border border-maroon-100 rounded-2xl px-6 py-4 shadow-sm">

            <div class="text-xs uppercase tracking-wider text-maroon-400">
                Total Pesanan
            </div>

            <div class="text-3xl font-bold text-maroon-900 mt-1">
                {{ $orders->total() }}
            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- RINGKASAN + PENCARIAN + FILTER --}}
    {{-- ========================================================= --}}

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">

        <a href="{{ route('admin.orders.index', ['filter' => 'waiting']) }}"
           class="rounded-2xl border border-amber-200 bg-amber-50 p-4 transition hover:shadow-md">
            <div class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Perlu Diproses</div>
            <div class="mt-1 text-2xl font-bold text-amber-900">{{ $statusCounts['waiting'] ?? 0 }}</div>
            <div class="mt-1 text-xs text-amber-700">Sudah dibayar, belum dikemas</div>
        </a>

        <a href="{{ route('admin.orders.index', ['filter' => 'packing']) }}"
           class="rounded-2xl border border-maroon-100 bg-white p-4 transition hover:shadow-md">
            <div class="text-[11px] font-bold uppercase tracking-wider text-maroon-500">Dikemas</div>
            <div class="mt-1 text-2xl font-bold text-maroon-900">{{ $statusCounts['packing'] ?? 0 }}</div>
            <div class="mt-1 text-xs text-maroon-400">Sedang / selesai dikemas</div>
        </a>

        <a href="{{ route('admin.orders.index', ['filter' => 'shipping']) }}"
           class="rounded-2xl border border-maroon-100 bg-white p-4 transition hover:shadow-md">
            <div class="text-[11px] font-bold uppercase tracking-wider text-maroon-500">Dalam Pengiriman</div>
            <div class="mt-1 text-2xl font-bold text-maroon-900">{{ $statusCounts['shipping'] ?? 0 }}</div>
            <div class="mt-1 text-xs text-maroon-400">Di kurir / dalam perjalanan</div>
        </a>

        <a href="{{ route('admin.orders.index', ['filter' => 'no_tracking']) }}"
           class="rounded-2xl border {{ ($statusCounts['no_tracking'] ?? 0) > 0 ? 'border-rose-200 bg-rose-50' : 'border-maroon-100 bg-white' }} p-4 transition hover:shadow-md">
            <div class="text-[11px] font-bold uppercase tracking-wider {{ ($statusCounts['no_tracking'] ?? 0) > 0 ? 'text-rose-700' : 'text-maroon-500' }}">Resi Belum Diisi</div>
            <div class="mt-1 text-2xl font-bold {{ ($statusCounts['no_tracking'] ?? 0) > 0 ? 'text-rose-800' : 'text-maroon-900' }}">{{ $statusCounts['no_tracking'] ?? 0 }}</div>
            <div class="mt-1 text-xs {{ ($statusCounts['no_tracking'] ?? 0) > 0 ? 'text-rose-600' : 'text-maroon-400' }}">Sudah dikemas, belum ada AWB</div>
        </a>

        <div class="col-span-2 lg:col-span-1 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
            <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Omzet Pesanan Paid</div>
            <div class="mt-1 text-2xl font-bold text-emerald-900">{{ $rp($stats['revenue'] ?? 0) }}</div>
            <div class="mt-1 text-xs text-emerald-700">Tidak termasuk yang dibatalkan</div>
        </div>

    </div>


    <div class="bg-white border border-maroon-100 rounded-2xl p-4 shadow-sm space-y-4">

        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-col sm:flex-row gap-3">

            @if($activeFilter !== '')
                <input type="hidden" name="filter" value="{{ $activeFilter }}">
            @endif

            <div class="relative flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-maroon-400">🔍</span>

                <input
                    type="search"
                    name="q"
                    value="{{ $searchTerm }}"
                    placeholder="Cari nomor pesanan, nama / telepon pembeli, nomor resi, nama produk, varian, atau SKU"
                    class="w-full rounded-xl border border-maroon-200 bg-white py-3 pl-11 pr-4 text-sm text-maroon-900 outline-none focus:border-maroon-600 focus:ring-2 focus:ring-maroon-100"
                >
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="px-6 py-3 rounded-xl bg-maroon-900 text-white text-sm font-bold hover:bg-maroon-800 transition">
                    Cari
                </button>

                @if($searchTerm !== '' || $activeFilter !== '')
                    <a href="{{ route('admin.orders.index') }}"
                       class="px-5 py-3 rounded-xl border border-maroon-200 bg-white text-maroon-700 text-sm font-bold hover:bg-maroon-50 transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <div class="flex flex-wrap gap-2">
            @foreach($filterTabs as $tabKey => $tabLabel)
                @php
                    $tabActive = $activeFilter === (string) $tabKey;
                    $tabCount = $tabKey === '' ? ($statusCounts['all'] ?? null) : ($statusCounts[$tabKey] ?? null);
                    $tabUrl = route('admin.orders.index', array_filter([
                        'filter' => $tabKey,
                        'q' => $searchTerm,
                    ], fn ($v) => $v !== '' && $v !== null));
                @endphp

                <a href="{{ $tabUrl }}"
                   class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-bold transition {{ $tabActive ? 'border-maroon-900 bg-maroon-900 text-white' : 'border-maroon-200 bg-white text-maroon-700 hover:bg-maroon-50' }}">
                    {{ $tabLabel }}
                    @if($tabCount !== null)
                        <span class="rounded-full px-2 py-0.5 text-[10px] {{ $tabActive ? 'bg-white/20' : 'bg-maroon-50 text-maroon-600' }}">{{ $tabCount }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        @if($searchTerm !== '' || $activeFilter !== '')
            <p class="text-xs text-maroon-500">
                Menampilkan <strong>{{ $orders->total() }}</strong> pesanan
                @if($activeFilter !== '')
                    dengan filter <strong>{{ $filterTabs[$activeFilter] ?? $activeFilter }}</strong>
                @endif
                @if($searchTerm !== '')
                    untuk pencarian <strong>"{{ $searchTerm }}"</strong>
                @endif
            </p>
        @endif

    </div>


    {{-- ========================================================= --}}
    {{-- FLASH MESSAGE --}}
    {{-- ========================================================= --}}

    @if(session('success'))

        <div class="bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-2xl text-sm">
            ✓ {{ session('success') }}
        </div>

    @endif


    @if(session('error'))

        <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl text-sm">
            ⚠ {{ session('error') }}
        </div>

    @endif


    {{-- ========================================================= --}}
    {{-- ORDER LIST --}}
    {{-- ========================================================= --}}

    <div class="space-y-6">

        @forelse($orders as $o)

            @php

                $shippingStatus =
                    $o->shipping_status ?: 'waiting';

                $currentStep =
                    $statusMap[$shippingStatus]['step'] ?? 0;

                $statusText =
                    $statusMap[$shippingStatus]['text']
                    ?? 'Menunggu proses admin';

                $items = $o->items;

                $totalQuantity =
                    $items->sum('quantity');

                $shippingWeight =
                    $o->getCalculatedShippingWeight();

                $trackingNumber =
                    $o->tracking_number ?? null;

                // Resi baru tampil di halaman pesanan pelanggan
                // setelah admin menekan "Cetak & Kirim Resi".
                $trackingSentAt =
                    $o->tracking_sent_at ?? null;

                $trackingSent =
                    filled($trackingNumber)
                    && filled($trackingSentAt);

                $trackingSentLabel = $trackingSent
                    ? \Illuminate\Support\Carbon::parse($trackingSentAt)->translatedFormat('d M Y, H:i')
                    : null;

                $courier =
                    $o->shipping_courier ?? null;

                $service =
                    $o->shipping_service ?? null;

                $destination =
                    $o->destination_name ?? null;

                $etd =
                    $o->shipping_etd ?? null;

                $isDelivered =
                    strtolower(trim((string) $o->shipping_status)) === 'delivered'
                    &&
                    strtolower(trim((string) $o->status)) === 'delivered';

                $steps = [

                    [
                        'step' => 1,
                        'status' => 'packing',
                        'title' => 'Dikemas',
                        'icon' => '📦',
                    ],

                    [
                        'step' => 2,
                        'status' => 'packed',
                        'title' => 'Selesai',
                        'icon' => '✓',
                    ],

                    [
                        'step' => 3,
                        'status' => 'handed_to_courier',
                        'title' => 'Ke Kurir',
                        'icon' => '🚚',
                    ],

                    [
                        'step' => 4,
                        'status' => 'shipped',
                        'title' => 'Dikirim',
                        'icon' => '🛣',
                    ],

                    [
                        'step' => 5,
                        'status' => 'delivered',
                        'title' => 'Diterima',
                        'icon' => '🏠',
                    ],

                ];

            
                $isCancelled = strtolower(trim((string) $o->status)) === 'cancelled';

                // Progress pengiriman terkunci sampai nomor resi disimpan
                // (pesanan yang sudah selesai tidak perlu dikunci lagi).
                $shippingLocked = !$isCancelled
                    && !filled($trackingNumber)
                    && $currentStep < 5;

                $pay = $o->payment;
                $payMethod = $pay?->method;

                // Rincian biaya (snapshot saat checkout)
                $sSubtotal = (float) ($o->subtotal ?? 0);
                $sDiscount = (float) ($o->discount_total ?? 0);
                $sShipping = (float) ($o->shipping_total ?? 0);
                $sAdminFee = (float) ($o->admin_fee ?? 0);
                $sGrand    = (float) ($o->grand_total ?? 0);
                $sGap      = (int) round($sGrand - ($sSubtotal - $sDiscount + $sShipping + $sAdminFee));

                // Pencocokan pembayaran
                $trExpected = (float) ($pay?->amount_expected ?? $sGrand);
                $trPaid = ($pay && $pay->amount_paid !== null) ? (float) $pay->amount_paid : null;
                $trDiff = $trPaid !== null ? (int) round($trPaid - $trExpected) : null;

                $proofUrl = $pay?->proof_path
                    ? asset('storage/' . ltrim($pay->proof_path, '/'))
                    : null;

                // Nomor WhatsApp pembeli (format internasional untuk wa.me)
                $waPhone = preg_replace('/\D+/', '', (string) $o->customer_phone);

                if ($waPhone !== '' && str_starts_with($waPhone, '0')) {
                    $waPhone = '62' . substr($waPhone, 1);
                } elseif ($waPhone !== '' && str_starts_with($waPhone, '8')) {
                    $waPhone = '62' . $waPhone;
                }

                // Daftar barang yang harus dikemas
                $packingRows = $items->values()->map(function ($it) {
                    return [
                        'name' => $it->product_name ?: ($it->product?->name ?? 'Produk'),
                        'variant' => $it->variant_name ?: ($it->variant?->name ?? null),
                        'qty' => (int) $it->quantity,
                        'sku' => $it->sku ?: ($it->variant?->sku ?? $it->product?->sku ?? null),
                    ];
                });

                // Teks siap-salin
                $copyAddress = collect([
                    $o->customer_name,
                    $o->customer_phone,
                    $o->shipping_address,
                    $o->destination_name,
                ])->filter(fn ($v) => filled($v))->implode("\n");

                $copyPacking = 'Pesanan ' . $o->order_number . "\n"
                    . $packingRows->map(function ($r, $idx) {
                        return ($idx + 1) . '. ' . $r['name']
                            . (filled($r['variant']) ? ' | Varian/Warna: ' . $r['variant'] : '')
                            . ' | Jumlah: ' . $r['qty']
                            . (filled($r['sku']) ? ' | SKU: ' . $r['sku'] : '');
                    })->implode("\n");

@endphp


            {{-- ================================================= --}}
            {{-- ORDER CARD --}}
            {{-- ================================================= --}}

            <div
                id="order-card-{{ $o->id }}"
                class="bg-white border border-maroon-100 rounded-3xl overflow-hidden shadow-sm transition-all duration-300 hover:shadow-xl"
            >


                {{-- ================================================= --}}
                {{-- ORDER HEADER --}}
                {{-- ================================================= --}}

                <div class="px-6 py-5 bg-gradient-to-r from-maroon-900 via-maroon-800 to-maroon-700 text-white">

                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                        <div>

                            <div class="text-[11px] uppercase tracking-[0.2em] text-maroon-200">
                                Nomor Pesanan
                            </div>

                            <div class="text-xl font-bold mt-1">
                                {{ $o->order_number }}
                            </div>

                            <div class="text-xs text-maroon-200 mt-2">
                                {{ $o->created_at?->translatedFormat('d F Y • H:i') }}
                            </div>

                        </div>


                        <div class="flex flex-wrap gap-2">

                            <span class="px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-xs font-semibold">

                                💳

                                @if($o->payment_status === 'paid')

                                    Sudah Dibayar

                                @elseif($o->payment_status === 'rejected')

                                    Pembayaran Ditolak

                                @else

                                    Menunggu Pembayaran

                                @endif

                            </span>


                            @if($isCancelled)
                                <span class="px-4 py-2 rounded-xl bg-rose-600 border border-rose-300 text-white text-xs font-bold">
                                    ✕ Dibatalkan
                                </span>
                            @endif

                            @if($payMethod?->name)
                                <span class="px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-xs font-semibold">
                                    🏦 {{ $payMethod->name }}
                                </span>
                            @endif

                            @if($sShipping <= 0)
                                <span class="px-4 py-2 rounded-xl bg-emerald-500/20 border border-emerald-300/40 text-emerald-100 text-xs font-bold">
                                    🎉 Gratis Ongkir
                                </span>
                            @endif

                            
<span
                                id="shipping-badge-{{ $o->id }}"
                                class="px-4 py-2 rounded-xl bg-gold-400 text-maroon-900 text-xs font-bold"
                            >
                                🚚 {{ $statusText }}
                            </span>

                        </div>

                    </div>

                </div>


                @if($isCancelled)
                    <div class="px-6 py-4 bg-rose-50 border-b border-rose-200 text-sm text-rose-800">
                        <span class="font-bold">Pesanan ini dibatalkan.</span>
                        Stok produk sudah dikembalikan. Progress pengiriman dinonaktifkan dan pesanan tidak boleh diproses.
                    </div>
                @endif

                {{-- ================================================= --}}
                {{-- YANG HARUS DIKEMAS --}}
                {{-- ================================================= --}}
                <div class="px-6 py-4 border-b border-maroon-100 bg-amber-50/60">

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-amber-800">
                            📦 Yang harus dikemas · {{ $totalQuantity }} pcs
                        </div>

                        <button
                            type="button"
                            data-copy="{{ $copyPacking }}"
                            class="rounded-lg border border-amber-300 bg-white px-3 py-1.5 text-xs font-bold text-amber-800 transition hover:bg-amber-100"
                        >
                            📋 Salin daftar kemas
                        </button>
                    </div>

                    <ul class="mt-3 grid grid-cols-1 gap-2 md:grid-cols-2">
                        @foreach($packingRows as $row)
                            <li class="flex items-start gap-3 rounded-xl border border-amber-200 bg-white px-3 py-2">
                                <span class="shrink-0 rounded-lg bg-amber-500 px-2.5 py-1 text-sm font-extrabold text-white">
                                    {{ $row['qty'] }}×
                                </span>

                                <span class="min-w-0 text-sm">
                                    <span class="font-semibold text-maroon-900">{{ $row['name'] }}</span>

                                    @if(filled($row['variant']))
                                        <span class="ml-1 inline-flex rounded-full border border-maroon-200 bg-maroon-50 px-2 py-0.5 text-xs font-semibold text-maroon-700">
                                            {{ $row['variant'] }}
                                        </span>
                                    @endif

                                    @if(filled($row['sku']))
                                        <span class="block text-[11px] text-maroon-400">SKU {{ $row['sku'] }}</span>
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>

                </div>


                {{-- ================================================= --}}
                {{-- MAIN ORDER CONTENT --}}
                {{-- ================================================= --}}

                <div class="grid grid-cols-1 xl:grid-cols-3">


                    {{-- ================================================= --}}
                    {{-- PRODUCTS --}}
                    {{-- ================================================= --}}

                    <div class="xl:col-span-2 p-6 border-b xl:border-b-0 xl:border-r border-maroon-100">

                        <div class="flex items-center justify-between mb-5">

                            <h2 class="font-serif text-xl text-maroon-900">
                                Produk Pesanan
                            </h2>

                            <span class="bg-maroon-50 text-maroon-700 px-3 py-1 rounded-full text-xs font-semibold">
                                {{ $items->count() }} Produk
                            </span>

                        </div>


                        <div class="space-y-4">

                            @forelse($items as $item)

                                @php

                                    $product = $item->product;
                                    $variant = $item->variant;

                                    $image =
                                        $product?->image
                                        ?? $product?->image_path
                                        ?? null;

                                    $itemVariantName = $item->variant_name ?: ($variant?->name ?? null);
                                    $itemSku = $item->sku ?: ($variant?->sku ?? $product?->sku ?? null);

                                    $itemStockLeft = $item->variant_id
                                        ? ($variant?->stock)
                                        : ($product?->stock);
                                    $itemStockLeft = $itemStockLeft !== null ? (int) $itemStockLeft : null;

                                    $itemWeightUnit = (int) ($item->weight ?? $product?->weight ?? 0);
                                    $itemWeightTotal = $itemWeightUnit * (int) $item->quantity;

                                @endphp


                                <div class="flex gap-4 p-4 rounded-2xl border border-maroon-100 transition-all duration-300 hover:bg-maroon-50/40">

                                    <div class="w-20 h-20 shrink-0 rounded-2xl overflow-hidden bg-maroon-50">

                                        @if($image)

                                            <img
                                                src="{{ asset('storage/' . $image) }}"
                                                alt="{{ $item->product_name ?? $product?->name ?? 'Produk' }}"
                                                class="w-full h-full object-cover"
                                            >

                                        @else

                                            <div class="w-full h-full flex items-center justify-center text-3xl">
                                                🧕
                                            </div>

                                        @endif

                                    </div>


                                    <div class="flex-1 min-w-0">

                                        <div class="font-bold text-maroon-900">
                                            {{ $item->product_name ?? $product?->name ?? 'Produk' }}
                                        </div>


                                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                            @if(filled($itemVariantName))
                                                <span class="rounded-full border border-maroon-200 bg-maroon-50 px-2.5 py-1 font-semibold text-maroon-700">
                                                    Varian/Warna: {{ $itemVariantName }}
                                                </span>
                                            @else
                                                <span class="rounded-full border border-maroon-100 px-2.5 py-1 text-maroon-400">
                                                    Tanpa varian
                                                </span>
                                            @endif

                                            @if(filled($itemSku))
                                                <span class="text-maroon-400">SKU {{ $itemSku }}</span>
                                            @endif
                                        </div>


                                        <div class="flex justify-between items-end gap-4 mt-4">

                                            <div class="space-y-1.5 text-xs text-maroon-500">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="inline-flex rounded-lg bg-amber-100 px-2.5 py-1 text-sm font-extrabold text-amber-800">
                                                        {{ (int) $item->quantity }}×
                                                    </span>
                                                    <span>harga satuan {{ $rp($item->price) }}</span>
                                                </div>

                                                @if($itemWeightTotal > 0)
                                                    <div>
                                                        Berat: {{ number_format($itemWeightTotal, 0, ',', '.') }} g
                                                        ({{ number_format($itemWeightUnit, 0, ',', '.') }} g/pcs)
                                                    </div>
                                                @endif

                                                @if($itemStockLeft !== null)
                                                    <div class="{{ $itemStockLeft <= 3 ? 'font-semibold text-rose-600' : '' }}">
                                                        Stok tersisa saat ini: {{ $itemStockLeft }}
                                                        @if($itemStockLeft <= 3)
                                                            (menipis)
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="font-bold text-maroon-800">

                                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            @empty

                                <div class="py-10 text-center text-sm text-maroon-400">
                                    Produk tidak ditemukan.
                                </div>

                            @endforelse

                        </div>


                        {{-- ================================================= --}}
                        {{-- ORDER SUMMARY --}}
                        {{-- ================================================= --}}

                        <div class="mt-6 rounded-2xl border border-maroon-100 bg-maroon-50 p-5">

                            <div class="mb-4 flex items-center justify-between gap-3">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-maroon-700">
                                    Rincian Biaya
                                </h3>

                                <span class="text-xs text-maroon-400">
                                    {{ $totalQuantity }} pcs · {{ $items->count() }} produk
                                </span>
                            </div>

                            <dl class="space-y-2.5 text-sm">

                                <div class="flex items-start justify-between gap-4">
                                    <dt class="text-maroon-500">Subtotal produk</dt>
                                    <dd class="font-semibold text-maroon-900">{{ $rp($sSubtotal) }}</dd>
                                </div>

                                @if($sDiscount > 0)
                                    <div class="flex items-start justify-between gap-4">
                                        <dt class="text-maroon-500">Diskon</dt>
                                        <dd class="font-semibold text-emerald-600">- {{ $rp($sDiscount) }}</dd>
                                    </div>
                                @endif

                                <div class="flex items-start justify-between gap-4">
                                    <dt class="text-maroon-500">
                                        Ongkos kirim
                                        @if($courier)
                                            <span class="block text-[11px] font-semibold uppercase tracking-wide text-maroon-400">
                                                {{ $courier }} {{ $service }}
                                            </span>
                                        @endif
                                    </dt>

                                    <dd class="text-right">
                                        @if($sShipping <= 0)
                                            <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700">
                                                GRATIS ONGKIR
                                            </span>
                                        @else
                                            <span class="font-semibold text-maroon-900">{{ $rp($sShipping) }}</span>
                                        @endif
                                    </dd>
                                </div>

                                <div class="flex items-start justify-between gap-4">
                                    <dt class="text-maroon-500">Biaya admin</dt>
                                    <dd class="font-semibold text-maroon-900">
                                        {{ $sAdminFee > 0 ? $rp($sAdminFee) : 'Gratis' }}
                                    </dd>
                                </div>

                            </dl>

                            <div class="mt-4 flex items-center justify-between gap-4 border-t border-dashed border-maroon-200 pt-4">
                                <span class="font-bold text-maroon-900">Total pesanan</span>
                                <span class="text-lg font-extrabold text-maroon-950">{{ $rp($sGrand) }}</span>
                            </div>

                            @if($sGap !== 0)
                                <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs leading-relaxed text-amber-800">
                                    <span class="font-bold">Rincian tidak cocok:</span>
                                    total pesanan berbeda {{ $rp(abs($sGap)) }} dari jumlah komponen di atas.
                                </div>
                            @endif

                        </div>
                    </div>

                    {{-- ================================================= --}}
                    {{-- CUSTOMER + SHIPPING --}}
                    {{-- ================================================= --}}

                    <div class="p-6 bg-maroon-50/40">

                        <h2 class="font-serif text-xl text-maroon-900 mb-5">
                            Informasi Pengiriman
                        </h2>


                        {{-- CUSTOMER --}}

                        <div class="mb-5">

                            <div class="text-[11px] uppercase tracking-wider text-maroon-400">
                                Penerima
                            </div>

                            <div class="font-bold text-maroon-900 mt-1">
                                {{ $o->customer_name ?? '-' }}
                            </div>


                            @if($o->customer_phone)

                                <div class="text-sm text-maroon-600 mt-2">
                                    📞 {{ $o->customer_phone }}
                                </div>

                            @endif


                            @if($o->customer_email)

                                <div class="text-xs text-maroon-500 mt-1 break-all">
                                    ✉ {{ $o->customer_email }}
                                </div>

                            @endif

                            <div class="mt-3 flex flex-wrap gap-2">
                                @if($waPhone !== '')
                                    <a
                                        href="https://wa.me/{{ $waPhone }}?text={{ rawurlencode('Halo ' . ($o->customer_name ?: 'Kak') . ', kami dari ' . $storeName . ' terkait pesanan ' . $o->order_number . '.') }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100"
                                    >
                                        💬 Chat WhatsApp
                                    </a>
                                @endif

                                <button
                                    type="button"
                                    data-copy="{{ $copyAddress }}"
                                    class="inline-flex items-center gap-2 rounded-lg border border-maroon-200 bg-white px-3 py-1.5 text-xs font-bold text-maroon-700 transition hover:bg-maroon-50"
                                >
                                    📋 Salin data penerima
                                </button>
                            </div>


                        </div>


                        {{-- ADDRESS --}}

                        <div class="bg-white border border-maroon-100 rounded-2xl p-4">

                            <div class="text-[11px] uppercase tracking-wider text-maroon-400 mb-2">
                                📍 Alamat Pengiriman
                            </div>

                            <div class="text-sm leading-relaxed text-maroon-800">

                                {{ $o->shipping_address ?? 'Alamat belum tersedia.' }}

                            </div>


                            @if($destination)

                                <div class="mt-3 pt-3 border-t border-maroon-100">

                                    <div class="text-[10px] uppercase tracking-wider text-maroon-400">
                                        Tujuan
                                    </div>

                                    <div class="text-sm font-semibold text-maroon-800 mt-1">
                                        {{ $destination }}
                                    </div>

                                </div>

                            @endif

                        </div>


                        {{-- ================================================= --}}
                        {{-- SHIPPING DETAILS --}}
                        {{-- ================================================= --}}

                        <div class="mt-5 bg-white border border-maroon-100 rounded-2xl p-4">

                            <div class="text-[11px] uppercase tracking-wider text-maroon-400 mb-3">
                                🚚 Detail Pengiriman
                            </div>


                            <div class="space-y-3 text-sm">


                                <div class="flex justify-between gap-4">

                                    <span class="text-maroon-500">
                                        Kurir
                                    </span>

                                    <span class="font-bold text-maroon-900">
                                        {{ $courier ?: '-' }}
                                    </span>

                                </div>


                                <div class="flex justify-between gap-4">

                                    <span class="text-maroon-500">
                                        Layanan
                                    </span>

                                    <span class="font-bold text-maroon-900">
                                        {{ $service ?: '-' }}
                                    </span>

                                </div>


                                <div class="flex justify-between gap-4">

                                    <span class="text-maroon-500">
                                        Berat
                                    </span>

                                    <span class="font-bold text-maroon-900">

                                        @if($shippingWeight)

                                            {{ number_format($shippingWeight / 1000, 2, ',', '.') }}
                                            kg

                                            <span class="font-normal text-xs text-maroon-400">
                                                ({{ number_format($shippingWeight, 0, ',', '.') }} g)
                                            </span>

                                        @else

                                            -

                                        @endif

                                    </span>

                                </div>


                                <div class="flex justify-between gap-4">

                                    <span class="text-maroon-500">
                                        Jumlah barang
                                    </span>

                                    <span class="font-bold text-maroon-900">
                                        {{ $totalQuantity }} pcs
                                    </span>

                                </div>


                                @if($etd)

                                    <div class="flex justify-between gap-4">

                                        <span class="text-maroon-500">
                                            Estimasi
                                        </span>

                                        <span class="font-bold text-maroon-900">
                                            {{ $etd }}
                                        </span>

                                    </div>

                                @endif


                            </div>

                        </div>


                        {{-- ================================================= --}}
                        {{-- PAYMENT INFO --}}
                        {{-- ================================================= --}}

                        <div class="mt-5 bg-white border border-maroon-100 rounded-2xl p-4">

                            <div class="text-[11px] uppercase tracking-wider text-maroon-400 mb-3">
                                💳 Informasi Pembayaran
                            </div>

                            <div class="space-y-3 text-sm">

                                <div class="flex justify-between gap-4">
                                    <span class="text-maroon-500">Metode</span>
                                    <span class="font-bold text-maroon-900 text-right">
                                        {{ $payMethod?->name ?? '-' }}
                                    </span>
                                </div>

                                <div class="flex justify-between gap-4">
                                    <span class="text-maroon-500">Ditagih</span>
                                    <span class="font-bold text-maroon-900">{{ $rp($trExpected) }}</span>
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-maroon-500">Dibayar</span>

                                    <span class="text-right">
                                        <span class="font-bold text-maroon-900">
                                            {{ $trPaid !== null ? $rp($trPaid) : '-' }}
                                        </span>

                                        @if($trDiff !== null)
                                            @if($trDiff === 0)
                                                <span class="ml-1 rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-bold text-white">Sesuai</span>
                                            @elseif($trDiff < 0)
                                                <span class="ml-1 rounded-full bg-rose-600 px-2 py-0.5 text-[10px] font-bold text-white">Kurang {{ $rp(abs($trDiff)) }}</span>
                                            @else
                                                <span class="ml-1 rounded-full bg-amber-500 px-2 py-0.5 text-[10px] font-bold text-white">Lebih {{ $rp(abs($trDiff)) }}</span>
                                            @endif
                                        @endif
                                    </span>
                                </div>

                                @if(filled($pay?->sender_name))
                                    <div class="flex justify-between gap-4">
                                        <span class="text-maroon-500">Pengirim</span>
                                        <span class="font-semibold text-maroon-900 text-right">{{ $pay->sender_name }}</span>
                                    </div>
                                @endif

                                @if(filled($pay?->sender_account))
                                    <div class="flex justify-between gap-4">
                                        <span class="text-maroon-500">Rek / e-wallet</span>
                                        <span class="font-semibold text-maroon-900 text-right">{{ $pay->sender_account }}</span>
                                    </div>
                                @endif

                                @if($pay?->paid_at)
                                    <div class="flex justify-between gap-4">
                                        <span class="text-maroon-500">Tgl transfer</span>
                                        <span class="font-semibold text-maroon-900 text-right">{{ $pay->paid_at->format('d M Y, H:i') }}</span>
                                    </div>
                                @endif

                                @if($pay?->verified_at)
                                    <div class="flex justify-between gap-4">
                                        <span class="text-maroon-500">Dikonfirmasi</span>
                                        <span class="font-semibold text-emerald-700 text-right">{{ $pay->verified_at->format('d M Y, H:i') }}</span>
                                    </div>
                                @endif

                            </div>

                            @if($proofUrl)
                                <a
                                    href="{{ $proofUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="mt-4 flex items-center gap-3 rounded-xl border border-maroon-100 bg-maroon-50/60 p-2 transition hover:bg-maroon-50"
                                >
                                    <img
                                        src="{{ $proofUrl }}"
                                        alt="Bukti transfer {{ $o->order_number }}"
                                        class="h-16 w-16 rounded-lg object-cover"
                                        loading="lazy"
                                        onerror="this.style.display='none'"
                                    >

                                    <span class="text-xs font-bold text-maroon-700">
                                        Lihat bukti transfer
                                    </span>
                                </a>
                            @endif

                        </div>


                        {{-- ================================================= --}}
                        {{-- TOTAL --}}
                        {{-- ================================================= --}}

                        <div class="mt-5 bg-maroon-900 text-white rounded-2xl p-5">

                            <div class="text-xs text-maroon-200">
                                Total Pesanan
                            </div>

                            <div class="text-2xl font-bold mt-2">
                                Rp {{ number_format($o->grand_total, 0, ',', '.') }}
                            </div>

                        </div>

                    </div>

                </div>


                {{-- ================================================= --}}
                {{-- PROSES PESANAN + WHATSAPP PEMBELI --}}
                {{-- ================================================= --}}

                @php
                    // Pesanan dianggap sudah "diproses" bila progress sudah berjalan
                    // atau resi sudah ada — panel WhatsApp langsung terbuka.
                    $waProcessed = ($currentStep > 0 || filled($trackingNumber)) && !$isCancelled;

                    $waLines = [];
                    $waLines[] = 'Halo ' . ($o->customer_name ?: 'Kak') . ', terima kasih sudah berbelanja di ' . $storeName . '. 🙏';
                    $waLines[] = '';
                    $waLines[] = 'Berikut detail pesanan Anda:';
                    $waLines[] = 'No. Pesanan: ' . $o->order_number;
                    $waLines[] = 'Tanggal: ' . ($o->created_at?->translatedFormat('d F Y, H:i') ?? '-');
                    $waLines[] = '';
                    $waLines[] = 'Produk:';

                    foreach ($packingRows as $waIndex => $waRow) {
                        $waLines[] = ($waIndex + 1) . '. ' . $waRow['name']
                            . (filled($waRow['variant']) ? ' (' . $waRow['variant'] . ')' : '')
                            . ' x' . $waRow['qty'];
                    }

                    $waLines[] = '';
                    $waLines[] = 'Subtotal: ' . $rp($sSubtotal);

                    if ($sDiscount > 0) {
                        $waLines[] = 'Diskon: -' . $rp($sDiscount);
                    }

                    $waLines[] = 'Ongkir: ' . ($sShipping > 0 ? $rp($sShipping) : 'Gratis');

                    if ($sAdminFee > 0) {
                        $waLines[] = 'Biaya admin: ' . $rp($sAdminFee);
                    }

                    $waLines[] = 'Total: ' . $rp($sGrand);
                    $waLines[] = 'Pembayaran: ' . ($payMethod?->name ?: '-')
                        . ' (' . ($o->payment_status === 'paid' ? 'Lunas' : 'Belum lunas') . ')';
                    $waLines[] = '';

                    $waCourier = trim(strtoupper((string) $courier) . ' ' . (string) $service);
                    $waLines[] = 'Pengiriman: ' . ($waCourier !== '' ? $waCourier : '-')
                        . (filled($etd) ? ' (estimasi ' . $etd . ')' : '');
                    $waLines[] = 'Alamat: ' . collect([$o->shipping_address, $destination])
                        ->filter(fn ($v) => filled($v))
                        ->implode(', ');
                    $waLines[] = 'No. Resi: ' . (filled($trackingNumber) ? $trackingNumber : '(menyusul setelah paket dikirim)');
                    $waLines[] = '';
                    $waLines[] = 'Terima kasih 🙏';

                    $waMessage = implode("\n", $waLines);
                @endphp

                <div
                    id="wa-section-{{ $o->id }}"
                    data-wa-phone="{{ $waPhone }}"
                    class="p-6 border-t border-maroon-100 bg-white"
                >

                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">

                        <div>
                            <h2 class="font-serif text-xl text-maroon-900">
                                Proses Pesanan &amp; WhatsApp Pembeli
                            </h2>
                            <p class="text-sm text-maroon-500 mt-1">
                                Klik <b>Proses Pesanan</b> untuk menyiapkan pesan WhatsApp berisi detail pesanan.
                                Nomor resi terisi otomatis setelah resi disimpan, lalu teruskan ke nomor pembeli.
                            </p>
                        </div>

                        @unless($isCancelled)
                            <button
                                type="button"
                                data-process-order="{{ $o->id }}"
                                class="{{ $waProcessed ? 'hidden' : '' }} shrink-0 px-6 py-3 rounded-xl bg-maroon-900 text-white text-sm font-bold hover:bg-maroon-800 transition"
                            >
                                🧾 Proses Pesanan
                            </button>
                        @endunless

                    </div>

                    @unless($isCancelled)

                        <div
                            id="wa-panel-{{ $o->id }}"
                            class="mt-5 {{ $waProcessed ? '' : 'hidden' }}"
                        >

                            <label
                                for="wa-message-{{ $o->id }}"
                                class="block text-xs font-bold uppercase tracking-wider text-maroon-500 mb-2"
                            >
                                Pesan WhatsApp (bisa diedit sebelum dikirim)
                            </label>

                            <textarea
                                id="wa-message-{{ $o->id }}"
                                rows="14"
                                class="w-full rounded-xl border border-maroon-200 bg-white px-4 py-3 text-sm leading-relaxed text-maroon-900 outline-none focus:border-maroon-600 focus:ring-2 focus:ring-maroon-100"
                            >{{ $waMessage }}</textarea>

                            <p
                                id="wa-note-{{ $o->id }}"
                                class="mt-2 text-xs {{ filled($trackingNumber) ? 'text-green-700' : 'text-amber-700' }}"
                            >
                                @if(filled($trackingNumber))
                                    ✓ Nomor resi {{ $trackingNumber }} sudah masuk ke pesan.
                                @else
                                    Resi belum diisi — isi dan simpan resi di bawah, pesan akan terisi otomatis.
                                @endif
                            </p>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    data-wa-open="{{ $o->id }}"
                                    data-has-tracking="{{ filled($trackingNumber) ? '1' : '0' }}"
                                    data-tracking-sent="{{ $trackingSent ? '1' : '0' }}"
                                    @if($waPhone === '' || !filled($trackingNumber)) disabled @endif
                                    class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-bold text-emerald-700 transition hover:bg-emerald-100 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    💬 Buka WhatsApp Pembeli
                                </button>

                                <button
                                    type="button"
                                    data-wa-copy="{{ $o->id }}"
                                    class="inline-flex items-center gap-2 rounded-xl border border-maroon-200 bg-white px-5 py-3 text-sm font-bold text-maroon-800 transition hover:bg-maroon-50"
                                >
                                    📋 Salin Pesan
                                </button>
                            </div>

                            {{--
                                KUNCI: resi wajib diisi & disimpan dulu sebelum admin bisa
                                meneruskan pesan ke pelanggan lewat WhatsApp. Tanpa ini,
                                admin bisa "forward" pesanan yang resinya masih kosong.
                            --}}
                            @if(!filled($trackingNumber))
                                <p id="wa-tracking-lock-{{ $o->id }}" class="mt-3 text-xs text-amber-700">
                                    ⚠ Isi dan simpan nomor resi / AWB di bawah terlebih dahulu sebelum bisa meneruskan pesan ke pelanggan.
                                </p>
                            @elseif($waPhone === '')
                                <p class="mt-3 text-xs text-rose-600">
                                    Nomor WhatsApp pembeli belum tersedia. Gunakan Salin Pesan lalu teruskan manual.
                                </p>
                            @endif

                        </div>

                    @endunless

                </div>


                {{-- ================================================= --}}
                {{-- TRACKING NUMBER --}}
                {{-- ================================================= --}}

                <div class="p-6 border-t border-maroon-100 bg-white">

                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">

                        <div>

                            <h2 class="font-serif text-xl text-maroon-900">
                                Nomor Resi / AWB
                            </h2>

                            <p class="text-sm text-maroon-500 mt-1">
                                Masukkan nomor resi yang diberikan oleh kurir.
                                Sistem tidak membuat nomor resi otomatis.
                            </p>

                        </div>


                        <div id="tracking-status-{{ $o->id }}">
                            @if($trackingNumber)
                                <div class="px-4 py-2 rounded-xl bg-green-50 border border-green-200 text-green-700 text-xs font-bold">
                                    ✓ Resi sudah tersimpan
                                </div>
                            @else
                                <div class="px-4 py-2 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-xs font-bold">
                                    ⚠ Resi belum diisi
                                </div>
                            @endif
                        </div>

                    </div>


                    <form
                        method="POST"
                        action="{{ route('admin.orders.tracking-number', $o) }}"
                        class="mt-5"
                        data-tracking-form="{{ $o->id }}"
                    >

                        @csrf


                        <div class="grid grid-cols-1 lg:grid-cols-[1fr_auto] gap-3">


                            <div>

                                <label
                                    for="tracking-number-{{ $o->id }}"
                                    class="block text-xs font-bold uppercase tracking-wider text-maroon-500 mb-2"
                                >
                                    Nomor Resi / AWB
                                </label>


                                <input
                                    id="tracking-number-{{ $o->id }}"
                                    type="text"
                                    name="tracking_number"
                                    value="{{ $trackingNumber }}"
                                    data-saved-tracking="{{ $trackingNumber }}"
                                    maxlength="100"
                                    autocomplete="off"
                                    placeholder="Contoh: JNE123456789"
                                    class="w-full rounded-xl border border-maroon-200 bg-white px-4 py-3 text-sm text-maroon-900 outline-none focus:border-maroon-600 focus:ring-2 focus:ring-maroon-100"
                                >

                            </div>


                            <div class="flex flex-wrap items-end gap-2">

                                <button
                                    type="submit"
                                    class="w-full lg:w-auto px-6 py-3 rounded-xl bg-maroon-900 text-white text-sm font-bold hover:bg-maroon-800 transition"
                                >
                                    💾 Simpan Resi
                                </button>

                                <button
                                    type="button"
                                    data-send-and-print="{{ $o->id }}"
                                    data-label-url="{{ route('admin.orders.shipping-label', $o) }}"
                                    class="w-full lg:w-auto px-5 py-3 rounded-xl border border-maroon-300 bg-white text-maroon-800 text-sm font-bold hover:bg-maroon-50 transition disabled:opacity-60 disabled:cursor-not-allowed"
                                >
                                    📨 Cetak &amp; Kirim Resi
                                </button>

                            </div>

                        </div>


                        <div
                            id="tracking-message-{{ $o->id }}"
                            class="hidden mt-3 px-4 py-3 rounded-xl text-sm"
                        ></div>


                    </form>


                    {{-- ================================================= --}}
                    {{-- RESI TERSIMPAN (tampil setelah resi berhasil disimpan) --}}
                    {{-- ================================================= --}}

                    <div
                        id="tracking-panel-{{ $o->id }}"
                        class="mt-5 rounded-2xl border border-maroon-100 bg-maroon-50/40 p-5 {{ filled($trackingNumber) ? '' : 'hidden' }}"
                    >

                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">

                            <div class="min-w-0">

                                <div class="text-[11px] font-bold uppercase tracking-wider text-maroon-400">
                                    Resi Tersimpan
                                </div>

                                <div
                                    id="tracking-panel-number-{{ $o->id }}"
                                    class="mt-1 break-all font-mono text-xl font-extrabold tracking-wide text-maroon-900"
                                >{{ $trackingNumber }}</div>

                                <div class="mt-3 max-w-sm rounded-xl border border-maroon-100 bg-white p-3">
                                    <svg
                                        id="tracking-panel-barcode-{{ $o->id }}"
                                        class="block h-14 w-full"
                                        role="img"
                                        aria-label="Barcode resi"
                                        data-barcode-value="{{ $trackingNumber }}"
                                    ></svg>
                                </div>

                            </div>

                            <div id="tracking-panel-sent-{{ $o->id }}">
                                @if($trackingSent)
                                    <div class="px-4 py-2 rounded-xl bg-green-50 border border-green-200 text-green-700 text-xs font-bold">
                                        ✓ Sudah dikirim ke pelanggan · {{ $trackingSentLabel }}
                                    </div>
                                @else
                                    <div class="px-4 py-2 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-xs font-bold">
                                        Belum dikirim ke pelanggan
                                    </div>
                                @endif
                            </div>

                        </div>

                        <dl class="mt-5 grid grid-cols-2 gap-x-6 gap-y-4 text-sm md:grid-cols-4">
                            <div>
                                <dt class="text-[11px] uppercase tracking-wider text-maroon-400">Kurir</dt>
                                <dd class="mt-0.5 font-semibold uppercase text-maroon-900">{{ $courier ?: '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-[11px] uppercase tracking-wider text-maroon-400">Layanan</dt>
                                <dd class="mt-0.5 font-semibold text-maroon-900">{{ $service ?: '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-[11px] uppercase tracking-wider text-maroon-400">Estimasi</dt>
                                <dd class="mt-0.5 font-semibold text-maroon-900">{{ $etd ?: '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-[11px] uppercase tracking-wider text-maroon-400">Berat</dt>
                                <dd class="mt-0.5 font-semibold text-maroon-900">
                                    @if((int) $shippingWeight > 0)
                                        {{ number_format((int) $shippingWeight, 0, ',', '.') }} gram
                                    @else
                                        -
                                    @endif
                                </dd>
                            </div>
                        </dl>

                        <p class="mt-4 text-xs leading-relaxed text-maroon-500">
                            Penerima:
                            <span class="font-semibold text-maroon-800">{{ $o->customer_name ?: '-' }}</span>
                            @if($destination)
                                · Tujuan: <span class="font-semibold text-maroon-800">{{ $destination }}</span>
                            @endif
                        </p>

                    </div>


                    <p
                        id="print-hint-{{ $o->id }}"
                        class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs leading-relaxed text-amber-800 {{ filled($trackingNumber) ? 'hidden' : '' }}"
                    >
                        <span class="font-bold">Isi dan simpan nomor resi dulu</span>
                        supaya shipping label memuat nomor + barcode resi yang bisa ditempel ke paket
                        sebelum diserahkan ke kurir.
                    </p>


                    {{-- ================================================= --}}
                    {{-- PRINT BUTTONS --}}
                    {{-- ================================================= --}}

                    <div class="mt-5 flex flex-col sm:flex-row flex-wrap gap-3">


                        <a
                            href="{{ route('admin.orders.receipt', $o) }}"
                            data-print-kind="receipt"
                            data-print-order="{{ $o->id }}"
                            data-has-tracking="{{ filled($trackingNumber) ? '1' : '0' }}"
                            data-tracking-value="{{ $trackingNumber }}"
                            target="_blank"
                            class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-maroon-200 bg-white text-maroon-800 text-sm font-bold hover:bg-maroon-50 transition"
                        >
                            🧾 Cetak Resi
                        </a>


                        <a
                            href="{{ route('admin.orders.shipping-label', $o) }}"
                            data-print-kind="label"
                            data-print-order="{{ $o->id }}"
                            data-has-tracking="{{ filled($trackingNumber) ? '1' : '0' }}"
                            data-tracking-value="{{ $trackingNumber }}"
                            target="_blank"
                            class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-maroon-900 text-white text-sm font-bold hover:bg-maroon-800 transition"
                        >
                            🏷 Cetak Shipping Label
                        </a>


                        {{-- ================================================= --}}
                        {{-- DELETE ORDER HISTORY --}}
                        {{-- ================================================= --}}

                        @if($isDelivered)

                            <form
                                method="POST"
                                action="{{ route('admin.orders.destroy-history', $o) }}"
                                class="inline-flex"
                                data-delete-history-form
                                data-order-number="{{ $o->order_number }}"
                            >

                                @csrf

                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-red-200 bg-red-50 text-red-700 text-sm font-bold hover:bg-red-100 hover:border-red-300 transition-all duration-200"
                                >
                                    🗑 Hapus Riwayat
                                </button>

                            </form>

                        @endif


                    </div>


                </div>


                {{-- ================================================= --}}
                {{-- SHIPPING PROGRESS --}}
                {{-- ================================================= --}}

                <div class="p-6 border-t border-maroon-100 {{ $isCancelled ? 'pointer-events-none select-none opacity-50 grayscale' : '' }}">


                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">

                        <div>

                            <h2 class="font-serif text-xl text-maroon-900">
                                Progress Pengiriman
                            </h2>

                            <div
                                id="shipping-text-{{ $o->id }}"
                                class="text-sm text-maroon-500 mt-1"
                            >
                                {{ $statusText }}
                            </div>

                        </div>


                        <div class="px-4 py-2 rounded-xl bg-maroon-50 text-maroon-700 text-xs font-bold">

                            Langkah

                            <span id="shipping-step-text-{{ $o->id }}">
                                {{ $currentStep }}
                            </span>

                            / 5

                        </div>

                    </div>


                    {{-- KUNCI: nomor resi wajib --}}
                    <div
                        id="shipping-lock-{{ $o->id }}"
                        class="mb-6 flex flex-col gap-3 rounded-2xl border border-amber-300 bg-amber-50 px-5 py-4 text-sm text-amber-900 sm:flex-row sm:items-center sm:justify-between {{ $shippingLocked ? '' : 'hidden' }}"
                    >
                        <div>
                            <span class="font-bold">🔒 Progress pengiriman terkunci.</span>
                            Isi dan simpan nomor resi / AWB terlebih dahulu. Setelah itu tombol
                            Dikemas, Selesai, Ke Kurir, Dikirim, dan Diterima terbuka.
                        </div>

                        <button
                            type="button"
                            data-focus-tracking="{{ $o->id }}"
                            class="shrink-0 rounded-xl bg-amber-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-amber-600"
                        >
                            Isi nomor resi →
                        </button>
                    </div>


                    {{-- STEPPER --}}
                    <div
                        id="shipping-container-{{ $o->id }}"
                        data-order="{{ $o->id }}"
                        data-step="{{ $currentStep }}"
                        class="relative {{ $shippingLocked ? 'pointer-events-none select-none opacity-40' : '' }}"
                    >


                        {{-- BACKGROUND LINE --}}

                        <div class="absolute top-6 left-[10%] right-[10%] h-1 bg-maroon-100 rounded-full"></div>


                        {{-- ACTIVE LINE --}}

                        <div
                            id="shipping-progress-{{ $o->id }}"
                            class="absolute top-6 left-[10%] h-1 bg-gradient-to-r from-maroon-700 to-gold-500 rounded-full transition-all duration-700 ease-out"
                            style="
                                width:
                                {{
                                    $currentStep <= 1
                                        ? '0%'
                                        : (($currentStep - 1) * 20) . '%'
                                }};
                            "
                        ></div>


                        <div class="relative z-10 grid grid-cols-5 gap-2">


                            @foreach($steps as $s)

                                @php

                                    $step = $s['step'];

                                    $isCompleted =
                                        $step <= $currentStep;

                                    $isNext =
                                        $step === ($currentStep + 1);

                                @endphp


                                <div class="text-center">


                                    <button
                                        type="button"
                                        class="
                                            shipping-step
                                            mx-auto
                                            w-12
                                            h-12
                                            rounded-full
                                            flex
                                            items-center
                                            justify-center
                                            font-bold
                                            text-lg
                                            transition-all
                                            duration-300

                                            @if($isCompleted)

                                                @if($step === 5 && $currentStep === 5)

                                                    bg-green-600 text-white shadow-lg

                                                @else

                                                    bg-maroon-700 text-white shadow-lg

                                                @endif

                                            @elseif($isNext)

                                                bg-white border-2 border-maroon-500 text-maroon-700 cursor-pointer hover:scale-110 hover:shadow-lg

                                            @else

                                                bg-gray-50 border-2 border-gray-200 text-gray-400 cursor-not-allowed

                                            @endif
                                        "
                                        data-order="{{ $o->id }}"
                                        data-step="{{ $step }}"
                                        data-status="{{ $s['status'] }}"
                                        @disabled(!$isNext)
                                    >

                                        @if($step === 5 && $currentStep === 5)

                                            ✓

                                        @else

                                            {{ $s['icon'] }}

                                        @endif

                                    </button>


                                    <div
                                        class="
                                            shipping-label
                                            mt-3
                                            text-[11px]
                                            font-bold

                                            @if($isCompleted)

                                                @if($step === 5 && $currentStep === 5)

                                                    text-green-700

                                                @else

                                                    text-maroon-900

                                                @endif

                                            @elseif($isNext)

                                                text-maroon-700

                                            @else

                                                text-gray-400

                                            @endif
                                        "
                                    >

                                        {{ $s['title'] }}

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    </div>


                    {{-- INFO --}}

                    <div
                        id="shipping-info-{{ $o->id }}"
                        class="mt-8 px-5 py-4 rounded-2xl bg-maroon-50 border border-maroon-100 text-sm text-maroon-700"
                    >

                        @if($currentStep === 0)

                            Klik <b>Dikemas</b> untuk memulai proses pesanan.

                        @elseif($currentStep === 1)

                            Pesanan sedang dikemas.
                            Klik <b>Selesai</b> jika pengemasan telah selesai.

                        @elseif($currentStep === 2)

                            Pesanan telah selesai dikemas.
                            Klik <b>Ke Kurir</b> setelah paket diserahkan.

                        @elseif($currentStep === 3)

                            Paket telah diserahkan kepada kurir.
                            Klik <b>Dikirim</b> untuk memulai pengiriman.

                        @elseif($currentStep === 4)

                            Paket sedang dalam perjalanan.
                            Klik <b>Diterima</b> jika barang sudah sampai.

                        @elseif($currentStep === 5)

                            ✓ Pesanan telah selesai dan diterima pelanggan.

                        @endif

                    </div>

                </div>


            </div>

        @empty


            {{-- ================================================= --}}
            {{-- EMPTY --}}
            {{-- ================================================= --}}

            <div class="bg-white border border-maroon-100 rounded-3xl p-16 text-center">

                <div class="text-5xl">
                    📦
                </div>

                <div class="font-serif text-xl text-maroon-900 mt-4">
                    @if($searchTerm !== '' || $activeFilter !== '')
                        Tidak Ada Pesanan yang Cocok
                    @else
                        Belum Ada Pesanan
                    @endif
                </div>

                <div class="text-sm text-maroon-400 mt-2">
                    @if($searchTerm !== '' || $activeFilter !== '')
                        Coba ubah kata kunci atau pilih filter lain.
                    @else
                        Pesanan pelanggan yang pembayarannya sudah dikonfirmasi akan muncul di sini.
                    @endif
                </div>

            </div>


        @endforelse

    </div>


    {{-- ========================================================= --}}
    {{-- PAGINATION --}}
    {{-- ========================================================= --}}

    <div class="pt-4">

        {{ $orders->withQueryString()->links() }}

    </div>

</div>


{{-- ============================================================= --}}
{{-- DELETE HISTORY CONFIRMATION MODAL --}}
{{-- ============================================================= --}}

<div
    id="delete-history-modal"
    class="fixed inset-0 z-[200] hidden"
    aria-hidden="true"
>

    {{-- BACKDROP --}}

    <div
        id="delete-history-backdrop"
        class="absolute inset-0 bg-maroon-950/60 backdrop-blur-sm opacity-0 transition-opacity duration-300"
    ></div>


    {{-- MODAL WRAPPER --}}

    <div class="relative min-h-full flex items-center justify-center p-4 sm:p-6">

        <div
            id="delete-history-dialog"
            class="relative w-full max-w-md bg-white rounded-3xl shadow-[0_30px_100px_-20px_rgba(58,15,25,0.45)] border border-maroon-100 overflow-hidden opacity-0 translate-y-5 scale-95 transition-all duration-300"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-history-title"
            aria-describedby="delete-history-description"
        >


            {{-- TOP ACCENT --}}

            <div class="h-1.5 bg-gradient-to-r from-red-700 via-red-500 to-gold-500"></div>


            {{-- CONTENT --}}

            <div class="p-6 sm:p-7">


                {{-- ICON --}}

                <div class="flex justify-center">

                    <div class="w-16 h-16 rounded-full bg-red-50 border border-red-100 flex items-center justify-center">

                        <div class="w-11 h-11 rounded-full bg-red-100 text-red-700 flex items-center justify-center text-xl">
                            🗑
                        </div>

                    </div>

                </div>


                {{-- TITLE --}}

                <div class="text-center mt-5">

                    <h3
                        id="delete-history-title"
                        class="font-serif text-2xl font-bold text-maroon-900"
                    >
                        Hapus Riwayat Pesanan?
                    </h3>


                    <p
                        id="delete-history-description"
                        class="mt-3 text-sm leading-6 text-maroon-600"
                    >
                        Riwayat pesanan yang sudah diterima pelanggan
                        akan dihapus dari daftar pesanan admin.
                    </p>

                </div>


                {{-- ORDER NUMBER --}}

                <div class="mt-5 rounded-2xl bg-maroon-50 border border-maroon-100 px-4 py-4 text-center">

                    <div class="text-[10px] uppercase tracking-[0.18em] text-maroon-400">
                        Nomor Pesanan
                    </div>

                    <div
                        id="delete-history-order-number"
                        class="mt-1 font-bold text-maroon-900 break-all"
                    >
                        -
                    </div>

                </div>


                {{-- WARNING --}}

                <div class="mt-4 rounded-2xl bg-amber-50 border border-amber-200 px-4 py-3">

                    <div class="flex gap-3">

                        <div class="shrink-0 text-amber-600">
                            ⚠
                        </div>

                        <div class="text-xs leading-5 text-amber-800">
                            Tindakan ini akan menghapus riwayat pesanan
                            dari sistem admin dan tidak dapat dibatalkan.
                        </div>

                    </div>

                </div>


                {{-- ACTIONS --}}

                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3">


                    {{-- CANCEL --}}

                    <button
                        type="button"
                        id="delete-history-cancel"
                        class="inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl border border-maroon-200 bg-white text-maroon-700 text-sm font-bold hover:bg-maroon-50 transition-all duration-200"
                    >
                        Batal
                    </button>


                    {{-- CONFIRM --}}

                    <button
                        type="button"
                        id="delete-history-confirm"
                        class="inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl bg-red-700 text-white text-sm font-bold hover:bg-red-800 transition-all duration-200 shadow-sm"
                    >
                        🗑 Hapus Riwayat
                    </button>


                </div>

            </div>

        </div>

    </div>

</div>


{{-- ============================================================= --}}
{{-- JAVASCRIPT --}}
{{-- ============================================================= --}}

<script>

document.addEventListener('DOMContentLoaded', function () {


    /*
    |--------------------------------------------------------------------------
    | ADMIN TOAST
    |--------------------------------------------------------------------------
    */

    function showAdminToast(message, type = 'error') {

        const container =
            document.querySelector('.zalina-toast-container');


        if (!container) {

            console.error(
                'Zalina toast container tidak ditemukan:',
                message
            );

            return;

        }


        const toast =
            document.createElement('div');


        toast.className =
            'zalina-toast relative overflow-hidden pointer-events-auto bg-white border border-maroon-100 rounded-2xl shadow-[0_18px_50px_-15px_rgba(58,15,25,0.28)]';


        const isSuccess =
            type === 'success';


        toast.innerHTML = `

            <div class="flex items-start gap-4 p-4">

                <div
                    class="shrink-0 w-10 h-10 rounded-full flex items-center justify-center ${
                        isSuccess
                            ? 'bg-green-100 text-green-700'
                            : 'bg-red-100 text-red-700'
                    }"
                >
                    ${isSuccess ? '✓' : '!'}
                </div>


                <div class="min-w-0 flex-1">

                    <div class="font-semibold text-maroon-900">
                        ${isSuccess ? 'Berhasil' : 'Perhatian'}
                    </div>


                    <div class="mt-1 text-sm leading-6 text-maroon-600">
                        ${message}
                    </div>

                </div>


                <button
                    type="button"
                    class="shrink-0 text-maroon-400 hover:text-maroon-800 text-xl leading-none"
                    aria-label="Tutup"
                >
                    ×
                </button>

            </div>

        `;


        container.appendChild(toast);


        const closeButton =
            toast.querySelector('button');


        if (closeButton) {

            closeButton.addEventListener(
                'click',
                function () {

                    toast.remove();

                }
            );

        }


        setTimeout(
            function () {

                if (toast.parentNode) {

                    toast.remove();

                }

            },
            5000
        );

    }


    /*
    |--------------------------------------------------------------------------
    | CSRF
    |--------------------------------------------------------------------------
    */

    const csrfToken =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');


    /*
    |--------------------------------------------------------------------------
    | SHIPPING URL
    |--------------------------------------------------------------------------
    */

    const shippingUpdateUrl =
        @json(url('/admin/orders/__ORDER_ID__/shipping-status'));


    /*
    |--------------------------------------------------------------------------
    | TRACKING URL
    |--------------------------------------------------------------------------
    */

    const trackingUpdateUrl =
        @json(url('/admin/orders/__ORDER_ID__/tracking-number'));


    /*
    |--------------------------------------------------------------------------
    | STATUS TEXT
    |--------------------------------------------------------------------------
    */

    const statusText = {

        waiting:
            'Menunggu proses admin',

        packing:
            'Pesanan sedang dikemas',

        packed:
            'Pesanan selesai dikemas',

        handed_to_courier:
            'Paket sudah diserahkan ke kurir',

        shipped:
            'Paket sedang dalam perjalanan',

        delivered:
            'Pesanan telah diterima pelanggan',

    };


    /*
    |--------------------------------------------------------------------------
    | STATUS STEP
    |--------------------------------------------------------------------------
    */

    const statusStep = {

        waiting: 0,

        packing: 1,

        packed: 2,

        handed_to_courier: 3,

        shipped: 4,

        delivered: 5,

    };


    /*
    |--------------------------------------------------------------------------
    | INFO TEXT
    |--------------------------------------------------------------------------
    */

    const infoText = {

        0:
            'Klik <b>Dikemas</b> untuk memulai proses pesanan.',

        1:
            'Pesanan sedang dikemas. Klik <b>Selesai</b> jika pengemasan telah selesai.',

        2:
            'Pesanan telah selesai dikemas. Klik <b>Ke Kurir</b> setelah paket diserahkan.',

        3:
            'Paket telah diserahkan kepada kurir. Klik <b>Dikirim</b> untuk memulai pengiriman.',

        4:
            'Paket sedang dalam perjalanan. Klik <b>Diterima</b> jika barang sudah sampai.',

        5:
            '✓ Pesanan telah selesai dan diterima pelanggan.',

    };


    /*
    |--------------------------------------------------------------------------
    | ICON
    |--------------------------------------------------------------------------
    */

    const stepIcons = {

        1: '📦',

        2: '✓',

        3: '🚚',

        4: '🛣',

        5: '🏠',

    };


    /*
    |--------------------------------------------------------------------------
    | PROGRESS WIDTH
    |--------------------------------------------------------------------------
    */

    function getProgressWidth(step) {

        if (step <= 1) {

            return '0%';

        }


        return ((step - 1) * 20) + '%';

    }


    /*
    |--------------------------------------------------------------------------
    | SHIPPING URL
    |--------------------------------------------------------------------------
    */

    function buildShippingUrl(orderId) {

        return shippingUpdateUrl.replace(
            '__ORDER_ID__',
            encodeURIComponent(orderId)
        );

    }


    /*
    |--------------------------------------------------------------------------
    | TRACKING URL
    |--------------------------------------------------------------------------
    */

    function buildTrackingUrl(orderId) {

        return trackingUpdateUrl.replace(
            '__ORDER_ID__',
            encodeURIComponent(orderId)
        );

    }


    /*
    |--------------------------------------------------------------------------
    | SAFE RESPONSE
    |--------------------------------------------------------------------------
    */

    async function readResponse(response) {

        const contentType =
            response.headers.get('content-type') || '';


        const rawText =
            await response.text();


        let json =
            null;


        if (contentType.includes('application/json')) {

            try {

                json =
                    JSON.parse(rawText);

            } catch (error) {

                json =
                    null;

            }

        }


        return {

            contentType,

            rawText,

            json

        };

    }


    /*
    |--------------------------------------------------------------------------
    | FRIENDLY ERROR
    |--------------------------------------------------------------------------
    */

    function getFriendlyError(response, result) {

        if (
            result.json
            &&
            result.json.message
        ) {

            return result.json.message;

        }


        if (response.status === 401) {

            return 'Sesi login tidak valid. Silakan login kembali.';

        }


        if (response.status === 403) {

            return 'Anda tidak memiliki izin untuk melakukan tindakan ini.';

        }


        if (response.status === 419) {

            return 'Sesi halaman telah kedaluwarsa. Silakan refresh halaman kemudian coba kembali.';

        }


        if (response.status === 422) {

            return 'Data yang dikirim tidak valid.';

        }


        if (response.status >= 500) {

            return 'Terjadi kesalahan pada server. Silakan cek log Laravel.';

        }


        if (
            result.rawText
            &&
            result.rawText
                .toLowerCase()
                .includes('<!doctype html')
        ) {

            return 'Server mengembalikan halaman HTML. Kemungkinan terjadi redirect atau error Laravel.';

        }


        return 'Terjadi kesalahan.';

    }


    /*
    |--------------------------------------------------------------------------
    | RESET BUTTON
    |--------------------------------------------------------------------------
    */

    function resetButton(button) {

        button.className =
            'shipping-step mx-auto w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg transition-all duration-300';

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE LABELS
    |--------------------------------------------------------------------------
    */

    function updateLabels(container, currentStep) {

        const labels =
            container.querySelectorAll('.shipping-label');


        labels.forEach(function (label, index) {

            const step =
                index + 1;


            label.classList.remove(
                'text-maroon-900',
                'text-maroon-700',
                'text-green-700',
                'text-gray-400'
            );


            if (step <= currentStep) {

                if (
                    step === 5
                    &&
                    currentStep === 5
                ) {

                    label.classList.add(
                        'text-green-700'
                    );

                } else {

                    label.classList.add(
                        'text-maroon-900'
                    );

                }

            }

            else if (
                step === currentStep + 1
            ) {

                label.classList.add(
                    'text-maroon-700'
                );

            }

            else {

                label.classList.add(
                    'text-gray-400'
                );

            }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE BUTTONS
    |--------------------------------------------------------------------------
    */

    function updateButtons(container, currentStep) {

        const buttons =
            container.querySelectorAll('.shipping-step');


        buttons.forEach(function (button) {

            const step =
                Number(button.dataset.step);


            const icon =
                stepIcons[step] || '•';


            resetButton(button);


            if (step <= currentStep) {

                button.disabled =
                    true;


                if (
                    step === 5
                    &&
                    currentStep === 5
                ) {

                    button.classList.add(
                        'bg-green-600',
                        'text-white',
                        'shadow-lg',
                        'cursor-default'
                    );

                    button.textContent =
                        '✓';

                }

                else {

                    button.classList.add(
                        'bg-maroon-700',
                        'text-white',
                        'shadow-lg',
                        'cursor-default'
                    );

                    button.textContent =
                        icon;

                }

            }

            else if (
                step === currentStep + 1
            ) {

                button.disabled =
                    false;


                button.classList.add(
                    'bg-white',
                    'border-2',
                    'border-maroon-500',
                    'text-maroon-700',
                    'cursor-pointer',
                    'hover:scale-110',
                    'hover:shadow-lg'
                );


                button.textContent =
                    icon;

            }

            else {

                button.disabled =
                    true;


                button.classList.add(
                    'bg-gray-50',
                    'border-2',
                    'border-gray-200',
                    'text-gray-400',
                    'cursor-not-allowed'
                );


                button.textContent =
                    icon;

            }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE SHIPPING UI
    |--------------------------------------------------------------------------
    */

    function updateUI(orderId, currentStep, status) {

        const container =
            document.getElementById(
                'shipping-container-' + orderId
            );


        if (!container) {

            return;

        }


        container.dataset.step =
            String(currentStep);


        const text =
            document.getElementById(
                'shipping-text-' + orderId
            );


        if (text) {

            text.textContent =
                statusText[status]
                ||
                'Status diperbarui';

        }


        const badge =
            document.getElementById(
                'shipping-badge-' + orderId
            );


        if (badge) {

            badge.textContent =
                '🚚 '
                +
                (
                    statusText[status]
                    ||
                    'Status diperbarui'
                );

        }


        const stepText =
            document.getElementById(
                'shipping-step-text-' + orderId
            );


        if (stepText) {

            stepText.textContent =
                currentStep;

        }


        const progress =
            document.getElementById(
                'shipping-progress-' + orderId
            );


        if (progress) {

            progress.style.width =
                getProgressWidth(currentStep);

        }


        const info =
            document.getElementById(
                'shipping-info-' + orderId
            );


        if (info) {

            info.innerHTML =
                infoText[currentStep]
                ||
                'Status pengiriman diperbarui.';

        }


        updateButtons(
            container,
            currentStep
        );


        updateLabels(
            container,
            currentStep
        );

    }


    /*
    |--------------------------------------------------------------------------
    | SHIPPING STATUS CLICK
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        async function (event) {


            const button =
                event.target.closest(
                    '.shipping-step'
                );


            if (!button) {

                return;

            }


            event.preventDefault();


            if (button.disabled) {

                return;

            }


            const orderId =
                button.dataset.order;


            const requestedStep =
                Number(button.dataset.step);


            const requestedStatus =
                button.dataset.status;


            const container =
                document.getElementById(
                    'shipping-container-' + orderId
                );


            if (!container) {

                return;

            }


            const currentStep =
                Number(container.dataset.step);


            if (
                requestedStep
                !==
                currentStep + 1
            ) {

                return;

            }


            if (!csrfToken) {

                showAdminToast(
                    'CSRF token tidak ditemukan. Pastikan layouts.admin memiliki meta csrf-token.',
                    'error'
                );

                return;

            }


            const originalHTML =
                button.innerHTML;


            button.disabled =
                true;


            button.textContent =
                '…';


            button.classList.add(
                'opacity-70',
                'cursor-wait'
            );


            try {


                const response =
                    await fetch(
                        buildShippingUrl(orderId),
                        {

                            method: 'POST',

                            credentials: 'same-origin',

                            headers: {

                                'Content-Type':
                                    'application/json',

                                'Accept':
                                    'application/json',

                                'X-CSRF-TOKEN':
                                    csrfToken,

                                'X-Requested-With':
                                    'XMLHttpRequest',

                            },

                            body:
                                JSON.stringify({

                                    status:
                                        requestedStatus,

                                }),

                        }
                    );


                const result =
                    await readResponse(response);


                console.log(
                    'Shipping Response:',
                    result
                );


                if (!response.ok) {

                    throw new Error(
                        getFriendlyError(
                            response,
                            result
                        )
                    );

                }


                if (!result.json) {

                    throw new Error(
                        'Server tidak mengembalikan JSON yang valid.'
                    );

                }


                if (
                    result.json.success
                    ===
                    false
                ) {

                    throw new Error(
                        result.json.message
                        ||
                        'Update status gagal.'
                    );

                }


                const newStatus =
                    result.json.shipping_status
                    ||
                    requestedStatus;


                let newStep =
                    Number(
                        result.json.current_step
                    );


                if (
                    Number.isNaN(newStep)
                ) {

                    newStep =
                        statusStep[newStatus];

                }


                if (
                    typeof newStep !== 'number'
                    ||
                    Number.isNaN(newStep)
                ) {

                    newStep =
                        requestedStep;

                }


                updateUI(
                    orderId,
                    newStep,
                    newStatus
                );


                showAdminToast(
                    result.json.message
                    ||
                    'Status pengiriman berhasil diperbarui.',
                    'success'
                );


            }

            catch (error) {


                console.error(
                    'Shipping Update Error:',
                    error
                );


                button.disabled =
                    false;


                button.innerHTML =
                    originalHTML;


                button.classList.remove(
                    'opacity-70',
                    'cursor-wait'
                );


                showAdminToast(
                    error.message
                    ||
                    'Terjadi kesalahan saat memperbarui status pengiriman.',
                    'error'
                );

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | TRACKING FORM
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'submit',
        async function (event) {


            const form =
                event.target.closest(
                    '[data-tracking-form]'
                );


            if (!form) {

                return;

            }


            event.preventDefault();


            const orderId =
                form.dataset.trackingForm;


            const input =
                form.querySelector(
                    'input[name="tracking_number"]'
                );


            const button =
                form.querySelector(
                    'button[type="submit"]'
                );


            const message =
                document.getElementById(
                    'tracking-message-' + orderId
                );


            if (!input) {

                return;

            }


            const trackingNumber =
                input.value.trim();


            if (!trackingNumber) {

                input.focus();


                if (message) {

                    message.className =
                        'mt-3 px-4 py-3 rounded-xl text-sm bg-red-50 border border-red-200 text-red-700';

                    message.textContent =
                        'Nomor resi wajib diisi.';

                }

                return;

            }


            if (!csrfToken) {

                showAdminToast(
                    'CSRF token tidak ditemukan.',
                    'error'
                );

                return;

            }


            const originalText =
                button
                    ? button.textContent
                    : 'Simpan Resi';


            if (button) {

                button.disabled =
                    true;


                button.textContent =
                    'Menyimpan…';

            }


            if (message) {

                message.className =
                    'mt-3 px-4 py-3 rounded-xl text-sm bg-maroon-50 border border-maroon-100 text-maroon-700';

                message.textContent =
                    'Menyimpan nomor resi…';

            }


            try {


                const response =
                    await fetch(
                        buildTrackingUrl(orderId),
                        {

                            method: 'POST',

                            credentials: 'same-origin',

                            headers: {

                                'Content-Type':
                                    'application/json',

                                'Accept':
                                    'application/json',

                                'X-CSRF-TOKEN':
                                    csrfToken,

                                'X-Requested-With':
                                    'XMLHttpRequest',

                            },

                            body:
                                JSON.stringify({

                                    tracking_number:
                                        trackingNumber,

                                }),

                        }
                    );


                const result =
                    await readResponse(response);


                console.log(
                    'Tracking Response:',
                    result
                );


                if (!response.ok) {

                    throw new Error(
                        getFriendlyError(
                            response,
                            result
                        )
                    );

                }


                if (!result.json) {

                    throw new Error(
                        'Server tidak mengembalikan JSON yang valid.'
                    );

                }


                if (
                    result.json.success
                    ===
                    false
                ) {

                    throw new Error(
                        result.json.message
                        ||
                        'Nomor resi gagal disimpan.'
                    );

                }


                const savedTracking =
                    result.json.tracking_number
                    ||
                    trackingNumber;


                input.value =
                    savedTracking;


                if (message) {

                    message.className =
                        'mt-3 px-4 py-3 rounded-xl text-sm bg-green-50 border border-green-200 text-green-700';

                    message.textContent =
                        '✓ '
                        +
                        (
                            result.json.message
                            ||
                            ('Nomor resi berhasil disimpan: ' + savedTracking)
                        );

                }


                showAdminToast(
                    'Nomor resi berhasil disimpan: ' + savedTracking,
                    'success'
                );

                window.dispatchEvent(
                    new CustomEvent(
                        'tracking:saved',
                        {
                            detail: {
                                orderId: orderId,
                                trackingNumber: savedTracking,
                                trackingSent: !!result.json.tracking_sent,
                                trackingSentAt: result.json.tracking_sent_at || null,
                            },
                        }
                    )
                );


            }

            catch (error) {


                console.error(
                    'Tracking Update Error:',
                    error
                );


                if (message) {

                    message.className =
                        'mt-3 px-4 py-3 rounded-xl text-sm bg-red-50 border border-red-200 text-red-700';

                    message.textContent =
                        '⚠ '
                        +
                        (
                            error.message
                            ||
                            'Nomor resi gagal disimpan.'
                        );

                }


                showAdminToast(
                    error.message
                    ||
                    'Nomor resi gagal disimpan.',
                    'error'
                );

            }

            finally {


                if (button) {

                    button.disabled =
                        false;


                    button.textContent =
                        originalText;

                }

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | DELETE HISTORY MODAL
    |--------------------------------------------------------------------------
    */

    const deleteHistoryModal =
        document.getElementById(
            'delete-history-modal'
        );


    const deleteHistoryBackdrop =
        document.getElementById(
            'delete-history-backdrop'
        );


    const deleteHistoryDialog =
        document.getElementById(
            'delete-history-dialog'
        );


    const deleteHistoryCancel =
        document.getElementById(
            'delete-history-cancel'
        );


    const deleteHistoryConfirm =
        document.getElementById(
            'delete-history-confirm'
        );


    const deleteHistoryOrderNumber =
        document.getElementById(
            'delete-history-order-number'
        );


    let pendingDeleteForm =
        null;


    /*
    |--------------------------------------------------------------------------
    | OPEN DELETE MODAL
    |--------------------------------------------------------------------------
    */

    function openDeleteHistoryModal(form) {

        if (
            !deleteHistoryModal
            ||
            !form
        ) {

            return;

        }


        pendingDeleteForm =
            form;


        const orderNumber =
            form.dataset.orderNumber
            ||
            '-';


        if (deleteHistoryOrderNumber) {

            deleteHistoryOrderNumber.textContent =
                orderNumber;

        }


        deleteHistoryModal.classList.remove(
            'hidden'
        );


        deleteHistoryModal.setAttribute(
            'aria-hidden',
            'false'
        );


        document.body.classList.add(
            'overflow-hidden'
        );


        requestAnimationFrame(
            function () {

                if (deleteHistoryBackdrop) {

                    deleteHistoryBackdrop.classList.remove(
                        'opacity-0'
                    );

                    deleteHistoryBackdrop.classList.add(
                        'opacity-100'
                    );

                }


                if (deleteHistoryDialog) {

                    deleteHistoryDialog.classList.remove(
                        'opacity-0',
                        'translate-y-5',
                        'scale-95'
                    );

                    deleteHistoryDialog.classList.add(
                        'opacity-100',
                        'translate-y-0',
                        'scale-100'
                    );

                }

            }
        );


        setTimeout(
            function () {

                if (deleteHistoryCancel) {

                    deleteHistoryCancel.focus();

                }

            },
            100
        );

    }


    /*
    |--------------------------------------------------------------------------
    | CLOSE DELETE MODAL
    |--------------------------------------------------------------------------
    */

    function closeDeleteHistoryModal() {

        if (!deleteHistoryModal) {

            return;

        }


        if (deleteHistoryBackdrop) {

            deleteHistoryBackdrop.classList.remove(
                'opacity-100'
            );

            deleteHistoryBackdrop.classList.add(
                'opacity-0'
            );

        }


        if (deleteHistoryDialog) {

            deleteHistoryDialog.classList.remove(
                'opacity-100',
                'translate-y-0',
                'scale-100'
            );

            deleteHistoryDialog.classList.add(
                'opacity-0',
                'translate-y-5',
                'scale-95'
            );

        }


        deleteHistoryModal.setAttribute(
            'aria-hidden',
            'true'
        );


        setTimeout(
            function () {

                deleteHistoryModal.classList.add(
                    'hidden'
                );

                document.body.classList.remove(
                    'overflow-hidden'
                );

                pendingDeleteForm =
                    null;

            },
            300
        );

    }


    /*
    |--------------------------------------------------------------------------
    | DELETE HISTORY FORM SUBMIT
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'submit',
        function (event) {

            const form =
                event.target.closest(
                    '[data-delete-history-form]'
                );


            if (!form) {

                return;

            }


            /*
            |--------------------------------------------------------------------------
            | STOP NATIVE SUBMIT
            |--------------------------------------------------------------------------
            */

            event.preventDefault();


            /*
            |--------------------------------------------------------------------------
            | OPEN MODAL
            |--------------------------------------------------------------------------
            */

            openDeleteHistoryModal(
                form
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | CANCEL DELETE
    |--------------------------------------------------------------------------
    */

    if (deleteHistoryCancel) {

        deleteHistoryCancel.addEventListener(
            'click',
            function () {

                closeDeleteHistoryModal();

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | BACKDROP CLICK
    |--------------------------------------------------------------------------
    */

    if (deleteHistoryBackdrop) {

        deleteHistoryBackdrop.addEventListener(
            'click',
            function () {

                closeDeleteHistoryModal();

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | CONFIRM DELETE
    |--------------------------------------------------------------------------
    */

    if (deleteHistoryConfirm) {

        deleteHistoryConfirm.addEventListener(
            'click',
            function () {

                if (!pendingDeleteForm) {

                    closeDeleteHistoryModal();

                    return;

                }


                const form =
                    pendingDeleteForm;


                const button =
                    form.querySelector(
                        'button[type="submit"]'
                    );


                /*
                |--------------------------------------------------------------------------
                | DISABLE ORIGINAL BUTTON
                |--------------------------------------------------------------------------
                */

                if (button) {

                    button.disabled =
                        true;


                    button.innerHTML =
                        '⏳ Menghapus…';


                    button.classList.add(
                        'opacity-70',
                        'cursor-wait'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | DISABLE MODAL BUTTON
                |--------------------------------------------------------------------------
                */

                deleteHistoryConfirm.disabled =
                    true;


                deleteHistoryConfirm.innerHTML =
                    '⏳ Menghapus…';


                deleteHistoryConfirm.classList.add(
                    'opacity-70',
                    'cursor-wait'
                );


                /*
                |--------------------------------------------------------------------------
                | CLOSE MODAL
                |--------------------------------------------------------------------------
                */

                closeDeleteHistoryModal();


                /*
                |--------------------------------------------------------------------------
                | SUBMIT REAL DELETE FORM
                |--------------------------------------------------------------------------
                |
                | form.submit() digunakan agar event listener submit
                | tidak membuka modal kembali.
                |
                */

                setTimeout(
                    function () {

                        HTMLFormElement.prototype.submit.call(
                            form
                        );

                    },
                    250
                );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | ESCAPE KEY
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        function (event) {

            if (
                event.key === 'Escape'
                &&
                deleteHistoryModal
                &&
                !deleteHistoryModal.classList.contains('hidden')
            ) {

                closeDeleteHistoryModal();

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | INITIAL DELETE MODAL STATE
    |--------------------------------------------------------------------------
    */

    if (deleteHistoryModal) {

        deleteHistoryModal.classList.add(
            'hidden'
        );

        deleteHistoryModal.setAttribute(
            'aria-hidden',
            'true'
        );

    }


});

</script>

{{-- ============================================================= --}}
{{-- TOMBOL SALIN (daftar kemas / data penerima) --}}
{{-- ============================================================= --}}
<script>
document.addEventListener('click', async function (event) {
    const button = event.target.closest('[data-copy]');

    if (!button) {
        return;
    }

    const text = button.getAttribute('data-copy') || '';
    const original = button.dataset.originalLabel || button.textContent.trim();

    button.dataset.originalLabel = original;

    let copied = false;

    try {
        await navigator.clipboard.writeText(text);
        copied = true;
    } catch (error) {
        // Fallback untuk browser / koneksi non-HTTPS
        const area = document.createElement('textarea');
        area.value = text;
        area.style.position = 'fixed';
        area.style.opacity = '0';
        document.body.appendChild(area);
        area.select();

        try {
            copied = document.execCommand('copy');
        } catch (e) {
            copied = false;
        }

        document.body.removeChild(area);
    }

    button.textContent = copied ? '✓ Tersalin' : 'Gagal menyalin';

    setTimeout(function () {
        button.textContent = original;
    }, 1600);
});
</script>


{{-- ============================================================= --}}
{{-- RESI -> CETAK RESI / SHIPPING LABEL --}}
{{-- ============================================================= --}}
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
(function () {
    function printLinks(orderId) {
        return document.querySelectorAll('[data-print-order="' + orderId + '"]');
    }

    // Konfirmasi sebelum mencetak
    document.addEventListener('click', function (event) {
        const link = event.target.closest('[data-print-order]');

        if (!link) {
            return;
        }

        const orderId = link.dataset.printOrder;
        const input = document.getElementById('tracking-number-' + orderId);
        const typed = input ? input.value.trim() : '';
        const saved = (link.dataset.trackingValue || '').trim();
        const isLabel = link.dataset.printKind === 'label';
        const what = isLabel ? 'Shipping label' : 'Nota pesanan';

        if (saved === '' && typed === '') {
            if (!confirm('Nomor resi belum diisi. ' + what + ' akan tercetak TANPA nomor & barcode resi. Lanjutkan mencetak?')) {
                event.preventDefault();
            }

            return;
        }

        if (typed !== '' && typed !== saved) {
            if (!confirm('Nomor resi "' + typed + '" belum disimpan. ' + what + ' akan memakai '
                + (saved ? 'resi tersimpan "' + saved + '"' : 'kondisi TANPA resi')
                + '. Klik Batal, lalu simpan resi dulu. Tetap cetak sekarang?')) {
                event.preventDefault();
            }
        }
    });

    // Tombol "Isi nomor resi" pada pesan terkunci
    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-focus-tracking]');

        if (!button) {
            return;
        }

        const input = document.getElementById('tracking-number-' + button.dataset.focusTracking);

        if (input) {
            input.scrollIntoView({ behavior: 'smooth', block: 'center' });
            input.focus();
        }
    });

    /* ---------------------------------------------------------
       HELPER: request JSON, barcode, panel resi
       --------------------------------------------------------- */

    const trackingSaveUrl = @json(url('/admin/orders/__ORDER_ID__/tracking-number'));
    const trackingSendUrl = @json(url('/admin/orders/__ORDER_ID__/send-tracking'));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function urlFor(template, orderId) {
        return template.replace('__ORDER_ID__', encodeURIComponent(orderId));
    }

    async function postJson(url, payload) {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf || '',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload || {}),
        });

        let json = null;

        try {
            json = await response.json();
        } catch (error) {
            json = null;
        }

        if (!response.ok || !json || json.success === false) {
            throw new Error(
                (json && json.message)
                || (response.status === 419
                    ? 'Sesi halaman telah kedaluwarsa. Silakan refresh halaman.'
                    : 'Permintaan gagal diproses (' + response.status + ').')
            );
        }

        return json;
    }

    function renderBarcode(svg, value) {
        if (!svg || !value || typeof JsBarcode === 'undefined') {
            return;
        }

        try {
            JsBarcode(svg, value, {
                format: 'CODE128',
                displayValue: false,
                height: 55,
                width: 2,
                margin: 0,
                background: '#ffffff',
                lineColor: '#000000'
            });
        } catch (error) {
            svg.innerHTML = '';
        }
    }

    document.querySelectorAll('svg[data-barcode-value]').forEach(function (svg) {
        renderBarcode(svg, svg.getAttribute('data-barcode-value'));
    });

    function setMessage(orderId, type, text) {
        const message = document.getElementById('tracking-message-' + orderId);

        if (!message) {
            return;
        }

        const tone = {
            success: 'bg-green-50 border border-green-200 text-green-700',
            error: 'bg-red-50 border border-red-200 text-red-700',
            info: 'bg-maroon-50 border border-maroon-100 text-maroon-700'
        }[type] || '';

        message.className = 'mt-3 px-4 py-3 rounded-xl text-sm ' + tone;
        message.textContent = text;
    }

    function updatePanel(orderId, tracking, sent, sentAt) {
        const panel = document.getElementById('tracking-panel-' + orderId);

        if (!panel) {
            return;
        }

        panel.classList.remove('hidden');

        const number = document.getElementById('tracking-panel-number-' + orderId);
        if (number) {
            number.textContent = tracking;
        }

        const svg = document.getElementById('tracking-panel-barcode-' + orderId);
        if (svg) {
            svg.setAttribute('data-barcode-value', tracking);
            renderBarcode(svg, tracking);
        }

        const badge = document.getElementById('tracking-panel-sent-' + orderId);
        if (badge) {
            const box = document.createElement('div');

            if (sent) {
                box.className = 'px-4 py-2 rounded-xl bg-green-50 border border-green-200 text-green-700 text-xs font-bold';
                box.textContent = '✓ Sudah dikirim ke pelanggan' + (sentAt ? ' · ' + sentAt : '');
            } else {
                box.className = 'px-4 py-2 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-xs font-bold';
                box.textContent = 'Belum dikirim ke pelanggan';
            }

            badge.innerHTML = '';
            badge.appendChild(box);
        }
    }

    // Tombol "Cetak & Kirim Resi": simpan (bila berubah) -> kirim ke pelanggan -> buka label untuk dicetak
    document.addEventListener('click', async function (event) {
        const button = event.target.closest('[data-send-and-print]');

        if (!button) {
            return;
        }

        const orderId = button.dataset.sendAndPrint;
        const input = document.getElementById('tracking-number-' + orderId);
        const typed = input ? input.value.trim() : '';
        const saved = input ? (input.dataset.savedTracking || '').trim() : '';

        if (typed === '') {
            if (input) {
                input.focus();
            }
            setMessage(orderId, 'error', 'Nomor resi wajib diisi.');
            return;
        }

        if (!confirm('Cetak label dan kirim resi "' + typed + '" ke pelanggan? Pelanggan akan melihat nomor resi dan barcode di halaman pesanannya.')) {
            return;
        }

        // Buka tab sekarang (masih dalam klik) supaya tidak diblokir pop-up blocker
        const printWindow = window.open('', '_blank');
        const originalLabel = button.innerHTML;

        button.disabled = true;
        button.textContent = 'Memproses…';
        setMessage(orderId, 'info', 'Menyimpan dan mengirim resi…');

        try {
            let tracking = saved;

            if (typed !== saved) {
                const savedResult = await postJson(urlFor(trackingSaveUrl, orderId), { tracking_number: typed });

                tracking = savedResult.tracking_number || typed;
                input.value = tracking;

                window.dispatchEvent(new CustomEvent('tracking:saved', {
                    detail: {
                        orderId: orderId,
                        trackingNumber: tracking,
                        trackingSent: !!savedResult.tracking_sent,
                        trackingSentAt: savedResult.tracking_sent_at || null,
                    },
                }));
            }

            const sentResult = await postJson(urlFor(trackingSendUrl, orderId), {});

            updatePanel(orderId, tracking, true, sentResult.tracking_sent_at || null);
            syncWhatsapp(orderId, tracking, true);

            const waOpenButton = document.querySelector('[data-wa-open="' + orderId + '"]');
            if (waOpenButton) {
                waOpenButton.dataset.hasTracking = '1';
                waOpenButton.dataset.trackingSent = '1';
            }

            setMessage(orderId, 'success', '✓ Resi ' + tracking + ' berhasil dikirim ke pelanggan. Label dibuka di tab baru; pesan WhatsApp pembeli sudah berisi nomor resi.');

            const labelUrl = button.dataset.labelUrl || '';

            if (printWindow && labelUrl) {
                printWindow.location.href = labelUrl + (labelUrl.indexOf('?') > -1 ? '&' : '?') + 'print=1';
            }
        } catch (error) {
            if (printWindow) {
                printWindow.close();
            }

            setMessage(orderId, 'error', '⚠ ' + (error.message || 'Resi gagal dikirim.'));
        } finally {
            button.disabled = false;
            button.innerHTML = originalLabel;
        }
    });


    /* ---------------------------------------------------------
       WHATSAPP PEMBELI: Proses Pesanan, isi resi otomatis, buka/salin
       --------------------------------------------------------- */

    function showWhatsappPanel(orderId) {
        const panel = document.getElementById('wa-panel-' + orderId);
        const start = document.querySelector('[data-process-order="' + orderId + '"]');

        if (panel) {
            panel.classList.remove('hidden');
        }

        if (start) {
            start.classList.add('hidden');
        }

        return panel;
    }

    // Ganti / tambahkan baris "No. Resi: ..." tanpa menghapus edit admin lainnya
    function syncWhatsapp(orderId, tracking, focusPanel) {
        const textarea = document.getElementById('wa-message-' + orderId);

        if (!textarea) {
            return;
        }

        const panel = showWhatsappPanel(orderId);
        const line = 'No. Resi: ' + tracking;
        const pattern = /^No\. Resi:.*$/m;

        textarea.value = pattern.test(textarea.value)
            ? textarea.value.replace(pattern, function () { return line; })
            : textarea.value.replace(/\s+$/, '') + '\n' + line;

        const note = document.getElementById('wa-note-' + orderId);
        if (note) {
            note.className = 'mt-2 text-xs text-green-700';
            note.textContent = '✓ Nomor resi ' + tracking + ' sudah masuk ke pesan.';
        }

        if (focusPanel && panel) {
            panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    document.addEventListener('click', function (event) {
        const start = event.target.closest('[data-process-order]');

        if (start) {
            const panel = showWhatsappPanel(start.dataset.processOrder);

            if (panel) {
                panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            return;
        }

        const open = event.target.closest('[data-wa-open]');

        if (open) {
            // KUNCI: resi wajib diisi & disimpan dulu sebelum boleh diteruskan
            // ke pelanggan lewat WhatsApp. Tombol memang sudah "disabled" dari
            // server saat resi kosong, tapi dicek ulang di sini supaya aman
            // walau state tombol belum sinkron (mis. baru diubah lewat JS lain).
            if (open.disabled || open.dataset.hasTracking !== '1') {
                const orderId = open.dataset.waOpen;

                setMessage(orderId, 'error', 'Isi dan simpan nomor resi / AWB terlebih dahulu sebelum meneruskan pesan ke pelanggan.');

                const input = document.getElementById('tracking-number-' + orderId);
                if (input) {
                    input.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    input.focus();
                }

                return;
            }

            const orderId = open.dataset.waOpen;
            const section = document.getElementById('wa-section-' + orderId);
            const textarea = document.getElementById('wa-message-' + orderId);
            const phone = section ? (section.dataset.waPhone || '') : '';

            if (!phone || !textarea) {
                return;
            }

            const originalLabel = open.innerHTML;
            const waUrl = 'https://wa.me/' + phone + '?text=' + encodeURIComponent(textarea.value);

            // Kalau resi sudah pernah ditandai terkirim, tidak perlu memanggil
            // server dulu — buka WhatsApp langsung seperti sebelumnya.
            if (open.dataset.trackingSent === '1') {
                window.open(waUrl, '_blank', 'noopener');
                return;
            }

            // Buka tab sekarang (masih dalam klik) supaya tidak diblokir
            // pop-up blocker, baru arahkan setelah resi ditandai terkirim.
            const waWindow = window.open('', '_blank');

            open.disabled = true;
            open.textContent = 'Menyiapkan…';

            (async function () {
                try {
                    // Tandai resi sudah diteruskan ke pelanggan supaya nomor
                    // resi juga langsung tampil di halaman akun pelanggan —
                    // sebelumnya ini hanya terjadi lewat tombol
                    // "Cetak & Kirim Resi", sehingga kalau admin hanya
                    // membuka WhatsApp, resi tidak pernah muncul di profil
                    // pelanggan.
                    const sentResult = await postJson(urlFor(trackingSendUrl, orderId), {});

                    open.dataset.trackingSent = '1';
                    updatePanel(
                        orderId,
                        sentResult.tracking_number || '',
                        true,
                        sentResult.tracking_sent_at || null
                    );

                    if (waWindow) {
                        waWindow.location.href = waUrl;
                    }
                } catch (error) {
                    if (waWindow) {
                        waWindow.close();
                    }

                    setMessage(orderId, 'error', '⚠ ' + (error.message || 'Resi gagal ditandai terkirim ke pelanggan.'));
                } finally {
                    open.disabled = false;
                    open.innerHTML = originalLabel;
                }
            })();

            return;
        }

        const copy = event.target.closest('[data-wa-copy]');

        if (copy) {
            const textarea = document.getElementById('wa-message-' + copy.dataset.waCopy);

            if (!textarea) {
                return;
            }

            const original = copy.innerHTML;

            const done = function (ok) {
                copy.textContent = ok ? '✓ Tersalin' : 'Gagal menyalin';
                setTimeout(function () { copy.innerHTML = original; }, 1600);
            };

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(textarea.value).then(function () { done(true); }, function () { done(false); });
            } else {
                textarea.select();
                let ok = false;
                try { ok = document.execCommand('copy'); } catch (error) { ok = false; }
                done(ok);
            }
        }
    });

    // Setelah resi tersimpan
    window.addEventListener('tracking:saved', function (event) {
        const orderId = String(event.detail.orderId);
        const tracking = event.detail.trackingNumber;

        const status = document.getElementById('tracking-status-' + orderId);
        if (status) {
            status.innerHTML = '<div class="px-4 py-2 rounded-xl bg-green-50 border border-green-200 text-green-700 text-xs font-bold">✓ Resi sudah tersimpan</div>';
        }

        const hint = document.getElementById('print-hint-' + orderId);
        if (hint) {
            hint.classList.add('hidden');
        }

        const input = document.getElementById('tracking-number-' + orderId);
        if (input) {
            input.dataset.savedTracking = tracking;
        }

        printLinks(orderId).forEach(function (link) {
            link.dataset.hasTracking = '1';
            link.dataset.trackingValue = tracking;
        });

        // Buka kunci tombol "Buka WhatsApp Pembeli" — resi sudah ada,
        // jadi admin sekarang boleh meneruskan pesan ke pelanggan.
        const waOpenButton = document.querySelector('[data-wa-open="' + orderId + '"]');
        if (waOpenButton) {
            waOpenButton.dataset.hasTracking = '1';
            // Kalau resi berubah, backend mereset status "terkirim" —
            // ikuti nilainya di sini supaya tombol WA menandai ulang
            // sendTracking sebelum membuka pesan ke pelanggan.
            waOpenButton.dataset.trackingSent = event.detail.trackingSent ? '1' : '0';

            const section = document.getElementById('wa-section-' + orderId);
            const phoneAvailable = section ? !!(section.dataset.waPhone || '') : false;

            if (phoneAvailable) {
                waOpenButton.disabled = false;
            }
        }

        const waLockNotice = document.getElementById('wa-tracking-lock-' + orderId);
        if (waLockNotice) {
            waLockNotice.remove();
        }

        // Buka kunci progress pengiriman
        const lock = document.getElementById('shipping-lock-' + orderId);
        if (lock) {
            lock.classList.add('hidden');
        }

        const stepper = document.getElementById('shipping-container-' + orderId);
        if (stepper) {
            stepper.classList.remove('pointer-events-none', 'select-none', 'opacity-40');
        }

        // Tampilkan resi (nomor + barcode + detail) setelah tersimpan
        updatePanel(orderId, tracking, !!event.detail.trackingSent, event.detail.trackingSentAt || null);

        // Isi otomatis nomor resi ke pesan WhatsApp pembeli
        syncWhatsapp(orderId, tracking, false);
    });
})();
</script>

@endsection