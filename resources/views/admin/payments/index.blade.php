@extends('layouts.admin')

@section('title', 'Verifikasi Pembayaran')

@section('content')
{{--
| Sebelumnya wrapper ini punya x-data="{ rejecting: null }", padahal
| setiap <tr> di bawah sudah punya x-data="{ rejecting: false }" sendiri
| yang sepenuhnya menutupi (shadow) state di level ini — jadi x-data di
| sini tidak pernah benar-benar dipakai. Dihapus supaya tidak membingungkan.
--}}
<div class="space-y-6">
    {{-- HEADER --}}
    <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#5b1025] via-[#761d38] to-[#a64b67] px-6 py-8 text-white shadow-xl md:px-8">
        <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-[#e8c27d]/15 blur-3xl"></div>

        <div class="relative flex flex-col justify-between gap-6 md:flex-row md:items-end">
            <div>
                {{--
                | Wordmark serif "Zalina" — elemen brand yang sama dipakai di
                | aksen sukses halaman cart (font-serif, warna maroon tua),
                | supaya admin langsung kenal ini bagian dari Zalina, bukan
                | template dashboard generik.
                --}}
                <p class="font-serif text-sm italic tracking-wide text-[#f3d9a8]">
                    Zalina Admin
                </p>

                <h1 class="mt-1 text-2xl font-bold tracking-tight md:text-3xl">
                    Verifikasi Pembayaran
                </h1>

                <p class="mt-2 max-w-xl text-sm leading-6 text-white/70">
                    Periksa bukti pembayaran pelanggan dan pastikan setiap pesanan
                    terverifikasi dengan tepat sebelum diproses.
                </p>
            </div>

            <div class="flex items-center gap-3 rounded-2xl border border-white/15 bg-white/10 px-5 py-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#e8c27d]/20 text-[#f3d9a8]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M9 7h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z"/>
                    </svg>
                </div>

                <div>
                    <p class="text-3xl font-bold leading-none">
                        {{ $payments->total() }}
                    </p>
                    <p class="mt-1 text-xs text-white/60">
                        Data pembayaran terdaftar
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- STATISTICS --}}
    @php
        /*
        |--------------------------------------------------------------
        | HITUNG STATISTIK
        |--------------------------------------------------------------
        | PERBAIKAN: sebelumnya $payments->whereIn(...)->count() dan
        | $payments->where(...)->count() dipanggil langsung pada
        | paginator. Laravel meneruskan pemanggilan itu ke koleksi
        | item HALAMAN SAAT INI saja, sementara "Total pembayaran" di
        | header memakai total() (seluruh data). Akibatnya statistik
        | saling bertentangan — misalnya "Total: 47" tapi
        | "Menunggu: 3" karena hanya menghitung halaman 1.
        |
        | Kalau controller mengirim $paymentStatusCounts (hitungan
        | lintas seluruh data), itu yang dipakai. Kalau tidak, angka
        | tetap dihitung dari halaman ini TAPI diberi keterangan yang
        | jelas supaya admin tidak salah baca.
        */

        $paymentIsPaginated = method_exists($payments, 'total') && $payments->total() !== $payments->count();

        $paymentCounts = collect($paymentStatusCounts ?? []);

        $countPaymentsByStatuses = function (array $statuses) use ($paymentCounts, $payments) {
            if ($paymentCounts->isNotEmpty()) {
                return (int) $paymentCounts->only($statuses)->sum();
            }

            return $payments->filter(
                fn ($payment) => in_array(strtolower((string) $payment->status), $statuses, true)
            )->count();
        };

        $waitingCount  = $countPaymentsByStatuses(['pending', 'waiting', 'waiting_verification', 'under_review']);
        $verifiedCount = $countPaymentsByStatuses(['verified']);
        $rejectedCount = $countPaymentsByStatuses(['rejected', 'declined']);

        $paymentCountsArePartial = $paymentIsPaginated && $paymentCounts->isEmpty();
    @endphp

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="flex items-center gap-4 rounded-2xl border-l-4 border-amber-400 bg-amber-50/70 p-5">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-700">
                    Menunggu
                    @if ($paymentCountsArePartial)
                        <span class="font-normal normal-case text-amber-600/60">(halaman ini)</span>
                    @endif
                </p>
                <p class="mt-0.5 text-2xl font-bold text-amber-900">
                    {{ $waitingCount }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl border-l-4 border-emerald-400 bg-emerald-50/70 p-5">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">
                    Terverifikasi
                    @if ($paymentCountsArePartial)
                        <span class="font-normal normal-case text-emerald-600/60">(halaman ini)</span>
                    @endif
                </p>
                <p class="mt-0.5 text-2xl font-bold text-emerald-900">
                    {{ $verifiedCount }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl border-l-4 border-rose-400 bg-rose-50/70 p-5">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-rose-700">
                    Ditolak
                    @if ($paymentCountsArePartial)
                        <span class="font-normal normal-case text-rose-600/60">(halaman ini)</span>
                    @endif
                </p>
                <p class="mt-0.5 text-2xl font-bold text-rose-900">
                    {{ $rejectedCount }}
                </p>
            </div>
        </div>
    </div>

    {{--
    | GANTI: sebelumnya ini tabel horizontal min-w-[1600px] dengan 7
    | kolom sempit — admin harus scroll ke samping, dan kolom "Bukti
    | Transfer" cuma kebagian ~112px karena ruang habis dipakai kolom
    | lain. Sekarang jadi daftar KARTU (satu pembayaran = satu kartu),
    | tidak ada scroll horizontal, dan bukti transfer punya kolom
    | sendiri yang jauh lebih besar & jelas untuk diperiksa.
    | Semua logika PHP (breakdown biaya, pencocokan nominal, resolusi
    | gambar) TIDAK diubah — hanya markup tampilannya.
    --}}
    <div class="overflow-hidden rounded-[2rem] border border-maroon-100 bg-white shadow-sm">
        <div class="flex flex-col justify-between gap-3 border-b border-maroon-100 px-6 py-5 md:flex-row md:items-center">
            <div>
                <p class="font-serif text-sm italic text-[#8a4b5c]">Daftar Pembayaran</p>
                <p class="mt-1 text-sm text-maroon-500">
                    Periksa nominal, metode, dan bukti transfer pelanggan.
                </p>
            </div>

            <div class="rounded-xl bg-maroon-50 px-3 py-2 text-xs font-semibold text-maroon-700">
                {{ $payments->firstItem() ?? 0 }}–{{ $payments->lastItem() ?? 0 }}
                dari {{ $payments->total() }} pembayaran
            </div>
        </div>

        @php
            /*
            |--------------------------------------------------------------
            | NORMALISASI URL GAMBAR PRODUK
            |--------------------------------------------------------------
            | Sebelumnya halaman ini SAMA SEKALI tidak menampilkan gambar
            | produk yang dipesan — kolom "Breakdown" hanya berisi angka
            | (subtotal/diskon/ongkir), tidak ada info produknya sendiri.
            | Helper ini dipakai untuk menampilkan thumbnail produk pada
            | kolom Pesanan, dengan pola yang sama seperti halaman Retur:
            | cek beberapa kemungkinan nama kolom, dukung path relatif
            | maupun URL absolut, dan bersihkan prefix "public/"/slash
            | di depan supaya tidak menghasilkan URL rusak (double slash).
            */

            $resolveProductImageUrl = function ($raw) {

                if (blank($raw)) {
                    return null;
                }

                $raw = trim((string) $raw);

                if (
                    str_starts_with($raw, 'http://') ||
                    str_starts_with($raw, 'https://') ||
                    str_starts_with($raw, '//')
                ) {
                    return $raw;
                }

                $path = ltrim($raw, '/');

                if (str_starts_with($path, 'public/')) {
                    $path = substr($path, 7);
                }

                return str_starts_with($path, 'storage/')
                    ? asset($path)
                    : asset('storage/' . $path);
            };

            /*
            | relationLoaded() dicek sebelum menyentuh relasi images
            | supaya tidak memicu lazy loading — kalau aplikasi
            | mengaktifkan Model::preventLazyLoading(), menyentuh
            | relasi yang belum di-eager-load akan melempar
            | exception dan membuat seluruh halaman ini crash.
            */

            $resolvePaymentItemImage = function ($item) use ($resolveProductImageUrl) {

                $product = $item->product ?? null;

                $candidates = [
                    $item->product_image ?? null,
                    $item->image ?? null,
                    $product->image ?? null,
                    $product->image_path ?? null,
                    $product->thumbnail ?? null,
                ];

                if ($product && method_exists($product, 'relationLoaded') && $product->relationLoaded('images')) {
                    $firstImage = $product->images->first();

                    if ($firstImage) {
                        $candidates[] = $firstImage->path ?? $firstImage->image ?? null;
                    }
                }

                foreach ($candidates as $candidate) {
                    $url = $resolveProductImageUrl($candidate);

                    if ($url) {
                        return $url;
                    }
                }

                return null;
            };

            // Versi aman untuk daftar item: gagal memuat satu gambar tidak
            // boleh menjatuhkan seluruh halaman.
            $safeItemImage = function ($item) use ($resolvePaymentItemImage) {
                try {
                    return $resolvePaymentItemImage($item);
                } catch (\Throwable $e) {
                    return null;
                }
            };
        @endphp

        <div class="divide-y divide-maroon-100">
            @forelse($payments as $p)
                @php
                    $status = strtolower((string) $p->status);

                    $statusClass = match ($status) {
                        'verified' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        'rejected', 'declined' => 'bg-rose-100 text-rose-700 border-rose-200',
                        default => 'bg-amber-100 text-amber-700 border-amber-200',
                    };

                    $statusLabel = match ($status) {
                        'verified' => 'Terverifikasi',
                        'rejected', 'declined' => 'Ditolak',
                        'waiting_verification', 'under_review' => 'Menunggu Verifikasi',
                        'waiting' => 'Menunggu',
                        'pending' => 'Pending',
                        default => ucwords(str_replace('_', ' ', $status)),
                    };
                @endphp

                @php
                    /*
                    | try/catch berjaga-jaga: kalau controller belum
                    | eager-load relasi 'order.items.product' dan
                    | aplikasi mengaktifkan Model::preventLazyLoading(),
                    | menyentuh $p->order->items di sini bisa melempar
                    | LazyLoadingViolationException. Dibungkus supaya
                    | satu baris pembayaran yang bermasalah tidak
                    | menjatuhkan seluruh halaman.
                    */
                    try {
                        $paymentItems = $p->order?->items ?? collect();
                    } catch (\Throwable $e) {
                        $paymentItems = collect();
                    }

                    $paymentFirstItem = $paymentItems instanceof \Illuminate\Support\Collection
                        ? $paymentItems->first()
                        : null;

                    $paymentFirstItemImageUrl = $paymentFirstItem
                        ? $resolvePaymentItemImage($paymentFirstItem)
                        : null;

                    $paymentItemCount = $paymentItems->count();
                @endphp

                <div
                    x-data="{ rejecting: false, confirming: false }"
                    class="p-6 transition hover:bg-maroon-50/20 md:p-8"
                >
                    {{-- BARIS ATAS: identitas pesanan + status --}}
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="relative h-12 w-12 shrink-0 overflow-hidden rounded-xl bg-maroon-100 text-maroon-700">
                                @if ($paymentFirstItemImageUrl)
                                    <img src="{{ $paymentFirstItemImageUrl }}"
                                         alt="Produk pesanan {{ $p->order?->order_number ?? '' }}"
                                         class="h-full w-full object-cover"
                                         loading="lazy"
                                         onerror="this.parentElement.querySelector('.zalina-order-fallback-icon').classList.remove('hidden'); this.remove();">
                                @endif

                                <svg class="zalina-order-fallback-icon h-5 w-5 absolute inset-0 m-auto {{ $paymentFirstItemImageUrl ? 'hidden' : '' }}"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                          d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 11H4L5 9z"/>
                                </svg>

                                @if ($paymentItemCount > 1)
                                    <span class="absolute -bottom-1 -right-1 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-maroon-700 px-1 text-[9px] font-black text-white ring-2 ring-white">
                                        {{ $paymentItemCount }}
                                    </span>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <p class="font-bold text-maroon-950">
                                    {{ $p->order?->order_number ?? '—' }}
                                </p>

                                <p class="mt-1 text-xs text-maroon-500">
                                    {{ $p->sender_name ?: 'Nama pengirim belum tersedia' }}
                                </p>
                            </div>
                        </div>

                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold {{ $statusClass }}">
                            <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                            {{ $statusLabel }}
                        </span>
                    </div>

                    {{-- BADAN KARTU: produk | keuangan | bukti --}}
                    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-[1.3fr_1fr_1fr]">

                        {{-- PRODUK DIPESAN --}}
                        <div>
                            <p class="mb-3 text-[11px] font-semibold uppercase tracking-wide text-maroon-400">
                                Produk Dipesan
                            </p>

                            @if($p->order && $paymentItems->count() > 0)

                                <ul class="space-y-3">
                                    @foreach($paymentItems as $item)
                                        @php
                                            $itemQty = (int) ($item->quantity ?? 0);
                                            $itemPrice = (float) ($item->price ?? 0);
                                            $itemSubtotal = (float) ($item->subtotal ?? ($itemPrice * $itemQty));
                                            $itemImage = $safeItemImage($item);
                                            $itemName = $item->product_name
                                                ?: ($item->product->name ?? 'Produk tidak diketahui');
                                        @endphp

                                        <li class="flex items-start gap-3">
                                            <div class="h-12 w-12 shrink-0 overflow-hidden rounded-lg bg-maroon-100">
                                                @if($itemImage)
                                                    <img src="{{ $itemImage }}"
                                                         alt="{{ $itemName }}"
                                                         class="h-full w-full object-cover"
                                                         loading="lazy"
                                                         onerror="this.remove()">
                                                @else
                                                    <div class="flex h-full w-full items-center justify-center text-sm font-bold text-maroon-400">
                                                        {{ mb_substr($itemName, 0, 1) }}
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-semibold leading-snug text-maroon-950">
                                                    {{ $itemName }}
                                                </p>

                                                <div class="mt-1 flex flex-wrap items-center gap-1.5 text-[11px]">
                                                    @if(filled($item->variant_name))
                                                        <span class="rounded-full border border-maroon-200 bg-maroon-50 px-2 py-0.5 font-semibold text-maroon-700">
                                                            Varian/Warna: {{ $item->variant_name }}
                                                        </span>
                                                    @else
                                                        <span class="rounded-full border border-maroon-100 px-2 py-0.5 text-maroon-400">
                                                            Tanpa varian
                                                        </span>
                                                    @endif

                                                    <span class="rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 font-bold text-amber-700">
                                                        Jumlah: {{ $itemQty }}
                                                    </span>

                                                    @if(filled($item->sku))
                                                        <span class="text-maroon-400">SKU: {{ $item->sku }}</span>
                                                    @endif
                                                </div>

                                                <p class="mt-1 text-xs text-maroon-500">
                                                    {{ $itemQty }} × Rp {{ number_format($itemPrice, 0, ',', '.') }}
                                                    =
                                                    <span class="font-semibold text-maroon-800">
                                                        Rp {{ number_format($itemSubtotal, 0, ',', '.') }}
                                                    </span>
                                                </p>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>

                                <p class="mt-3 border-t border-maroon-100 pt-2 text-[11px] font-semibold text-maroon-500">
                                    {{ $paymentItems->count() }} jenis produk · total {{ (int) $paymentItems->sum('quantity') }} pcs
                                </p>

                                {{-- Penerima & pengiriman --}}
                                @php $paymentOrder = $p->order; @endphp

                                <div class="mt-3 space-y-0.5 rounded-lg bg-maroon-50/60 px-3 py-2 text-[11px] leading-relaxed text-maroon-600">
                                    @if(filled($paymentOrder->customer_name) || filled($paymentOrder->customer_phone))
                                        <div>
                                            <span class="text-maroon-400">Penerima:</span>
                                            <span class="font-semibold">{{ $paymentOrder->customer_name }}</span>
                                            @if(filled($paymentOrder->customer_phone))
                                                · {{ $paymentOrder->customer_phone }}
                                            @endif
                                        </div>
                                    @endif

                                    @if(filled($paymentOrder->shipping_address) || filled($paymentOrder->destination_name))
                                        <div class="break-words">
                                            <span class="text-maroon-400">Alamat:</span>
                                            {{ $paymentOrder->shipping_address }}
                                            @if(filled($paymentOrder->destination_name))
                                                ({{ $paymentOrder->destination_name }})
                                            @endif
                                        </div>
                                    @endif

                                    @if(filled($paymentOrder->shipping_courier))
                                        <div>
                                            <span class="text-maroon-400">Kurir:</span>
                                            <span class="font-semibold">
                                                {{ strtoupper($paymentOrder->shipping_courier) }}
                                                {{ $paymentOrder->shipping_service }}
                                            </span>
                                            @if(filled($paymentOrder->shipping_etd))
                                                · estimasi {{ $paymentOrder->shipping_etd }}
                                            @endif
                                        </div>
                                    @endif
                                </div>

                            @elseif($p->order)
                                <span class="text-xs italic text-maroon-400">
                                    Item pesanan tidak dapat dimuat
                                </span>
                            @else
                                <span class="text-xs italic text-maroon-400">—</span>
                            @endif
                        </div>

                        {{-- ===================================================== --}}
                        {{-- KEUANGAN: hitung rincian biaya & pencocokan nominal   --}}
                        {{-- ===================================================== --}}
                        @php
                            $ord = $p->order;
                            $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

                            // Rincian biaya pesanan (snapshot yang tersimpan saat checkout)
                            $rbSubtotal   = (float) ($ord->subtotal ?? 0);
                            $rbDiscount   = (float) ($ord->discount_total ?? 0);
                            $rbShipping   = (float) ($ord->shipping_total ?? 0);
                            $rbAdminFee   = (float) ($ord->admin_fee ?? 0);
                            $rbGrandTotal = (float) ($ord->grand_total ?? 0);

                            // Cek konsistensi: subtotal - diskon + ongkir + admin harus = total
                            $rbComputed = $rbSubtotal - $rbDiscount + $rbShipping + $rbAdminFee;
                            $rbGap = (int) round($rbGrandTotal - $rbComputed);

                            // Pencocokan tagihan vs nominal yang diisi pelanggan
                            $trExpected = (float) $p->amount_expected;
                            $trPaid = $p->amount_paid !== null ? (float) $p->amount_paid : null;
                            $trDiff = $trPaid !== null ? (int) round($trPaid - $trExpected) : null;

                            $trState = match (true) {
                                $trDiff === null => 'none',
                                $trDiff === 0 => 'match',
                                $trDiff < 0 => 'short',
                                default => 'over',
                            };

                            // Tagihan di tabel payments harus sama dengan total pesanan
                            $trExpectedGap = $ord ? (int) round($trExpected - $rbGrandTotal) : 0;

                            $confirmMessage = 'Konfirmasi pembayaran untuk pesanan '
                                . ($ord?->order_number ?? '#' . $p->id)
                                . '? Tindakan ini akan menandai pembayaran sebagai terverifikasi.';

                            if ($trState === 'short' || $trState === 'over') {
                                $confirmMessage = 'PERHATIAN: nominal yang diisi pelanggan di form TIDAK SESUAI tagihan. '
                                    . 'Tagihan ' . $rp($trExpected)
                                    . ', diisi pelanggan ' . $rp($trPaid)
                                    . ' (' . ($trState === 'short' ? 'kurang ' : 'lebih ')
                                    . $rp(abs($trDiff)) . '). Ini BUKAN hasil baca otomatis dari bukti '
                                    . 'transfer — cek dulu foto buktinya sebelum konfirmasi. '
                                    . 'Tetap konfirmasi pesanan ' . ($ord?->order_number ?? '#' . $p->id) . '?';
                            } elseif ($trState === 'none') {
                                $confirmMessage = 'Pelanggan belum mengisi nominal transfer di form. '
                                    . 'Tetap konfirmasi pesanan ' . ($ord?->order_number ?? '#' . $p->id) . '?';
                            }
                        @endphp

                        {{-- RINCIAN BIAYA (breakdown keuangan pesanan) --}}
                        <div>
                            <p class="mb-3 text-[11px] font-semibold uppercase tracking-wide text-maroon-400">
                                Rincian Biaya
                            </p>

                            @if($ord)
                                <div class="w-full rounded-2xl border border-maroon-100 bg-white p-4 text-xs shadow-sm">

                                    <dl class="space-y-2">
                                        <div class="flex items-start justify-between gap-3">
                                            <dt class="text-maroon-500">Subtotal produk</dt>
                                            <dd class="font-semibold text-maroon-900">{{ $rp($rbSubtotal) }}</dd>
                                        </div>

                                        @if($rbDiscount > 0)
                                            <div class="flex items-start justify-between gap-3">
                                                <dt class="text-maroon-500">Diskon</dt>
                                                <dd class="font-semibold text-emerald-600">- {{ $rp($rbDiscount) }}</dd>
                                            </div>
                                        @endif

                                        <div class="flex items-start justify-between gap-3">
                                            <dt class="text-maroon-500">
                                                Ongkos kirim
                                                @if(filled($ord->shipping_courier))
                                                    <span class="block text-[10px] font-semibold uppercase tracking-wide text-maroon-400">
                                                        {{ $ord->shipping_courier }} {{ $ord->shipping_service }}
                                                    </span>
                                                @endif
                                            </dt>

                                            <dd class="text-right">
                                                @if($rbShipping <= 0)
                                                    <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                                                        GRATIS ONGKIR
                                                    </span>
                                                @else
                                                    <span class="font-semibold text-maroon-900">{{ $rp($rbShipping) }}</span>
                                                @endif
                                            </dd>
                                        </div>

                                        <div class="flex items-start justify-between gap-3">
                                            <dt class="text-maroon-500">Biaya admin</dt>
                                            <dd class="font-semibold text-maroon-900">
                                                {{ $rbAdminFee > 0 ? $rp($rbAdminFee) : 'Gratis' }}
                                            </dd>
                                        </div>
                                    </dl>

                                    <div class="mt-3 flex items-center justify-between gap-3 border-t border-dashed border-maroon-200 pt-3">
                                        <span class="font-bold text-maroon-900">Total tagihan</span>
                                        <span class="text-sm font-extrabold text-maroon-950">{{ $rp($rbGrandTotal) }}</span>
                                    </div>

                                    @if($rbGap !== 0 || $trExpectedGap !== 0)
                                        <div class="mt-3 space-y-1 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-[11px] leading-relaxed text-amber-800">
                                            @if($rbGap !== 0)
                                                <p>
                                                    <span class="font-bold">Rincian tidak cocok:</span>
                                                    total pesanan berbeda {{ $rp(abs($rbGap)) }} dari jumlah komponen di atas.
                                                </p>
                                            @endif

                                            @if($trExpectedGap !== 0)
                                                <p>
                                                    <span class="font-bold">Tagihan pembayaran</span>
                                                    ({{ $rp($trExpected) }}) berbeda dari total pesanan ({{ $rp($rbGrandTotal) }}).
                                                </p>
                                            @endif
                                        </div>
                                    @endif

                                </div>
                            @else
                                <span class="text-xs italic text-maroon-400">—</span>
                            @endif
                        </div>

                        {{--
                        | BUKTI TRANSFER
                        |
                        | GANTI: sebelumnya thumbnail 112x112px terjepit di
                        | kolom tabel sempit. Sekarang punya kolom sendiri
                        | dengan ukuran gambar jauh lebih besar (aspect 3:4,
                        | lebar penuh kolom sampai maksimal 280px) supaya
                        | admin bisa langsung baca nominal & rekening di
                        | struk tanpa harus klik dulu.
                        --}}
                        <div>
                            <p class="mb-3 text-[11px] font-semibold uppercase tracking-wide text-maroon-400">
                                Bukti Transfer
                            </p>

                            @php
                                /*
                                | Sebelumnya path dipakai mentah: asset('storage/'.$p->proof_path).
                                | Kalau proof_path tersimpan dengan slash di depan
                                | (mis. "/payments/xxx.jpg"), hasilnya jadi URL
                                | "storage//payments/xxx.jpg" (double slash) dan
                                | gambarnya gagal dimuat. ltrim() membersihkan itu.
                                */
                                $proofUrl = $p->proof_path
                                    ? asset('storage/' . ltrim($p->proof_path, '/'))
                                    : null;
                            @endphp

                            @if($proofUrl)
                                <a
                                    href="{{ $proofUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="group/proof block w-full max-w-[280px]"
                                >
                                    <div class="relative aspect-[3/4] w-full overflow-hidden rounded-2xl border border-maroon-100 bg-maroon-50">
                                        <img
                                            src="{{ $proofUrl }}"
                                            alt="Bukti pembayaran {{ $p->order?->order_number }}"
                                            class="h-full w-full object-cover transition duration-300 group-hover/proof:scale-105"
                                            loading="lazy"
                                            onerror="this.closest('a').classList.add('pointer-events-none'); this.replaceWith(Object.assign(document.createElement('div'), { className: 'flex h-full w-full items-center justify-center text-xs font-semibold text-rose-500 text-center px-4', textContent: 'Gagal memuat bukti' }));"
                                        >

                                        <div class="absolute inset-0 flex items-end justify-center bg-gradient-to-t from-maroon-950/70 via-maroon-950/0 to-transparent p-3 opacity-0 transition group-hover/proof:opacity-100">
                                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-white">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                          d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z"/>
                                                </svg>
                                                Klik untuk memperbesar
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            @else
                                <div class="flex aspect-[3/4] w-full max-w-[280px] items-center justify-center rounded-2xl border border-dashed border-maroon-200 bg-maroon-50/40 text-xs italic text-maroon-400">
                                    Belum upload
                                </div>
                            @endif

                            {{--
                            | Info transfer TANPA klaim pencocokan nominal —
                            | sebelumnya ada kotak "Tagihan vs Dibayar vs
                            | Selisih" yang menampilkan status "Sesuai",
                            | padahal sistem tidak pernah membaca nominal
                            | asli dari gambar bukti upload, hanya dari
                            | input form pelanggan. Itu menyesatkan admin
                            | seolah sudah terverifikasi otomatis, jadi
                            | dihapus. Info identitas transfer (bukan
                            | klaim nominal) tetap ditampilkan karena
                            | masih berguna.
                            --}}
                            <dl class="mt-3 w-full max-w-[280px] space-y-1.5 text-xs text-maroon-600">
                                <div class="flex justify-between gap-3">
                                    <dt class="text-maroon-400">Metode</dt>
                                    <dd class="text-right font-semibold text-maroon-900">
                                        {{ $p->method?->name ?? 'Tidak diketahui' }}
                                    </dd>
                                </div>

                                <div class="flex justify-between gap-3">
                                    <dt class="text-maroon-400">Pengirim</dt>
                                    <dd class="text-right font-semibold text-maroon-900">
                                        {{ filled($p->sender_name) ? $p->sender_name : '—' }}
                                    </dd>
                                </div>

                                @if(filled($p->sender_account))
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-maroon-400">Rek/e-wallet</dt>
                                        <dd class="text-right font-semibold text-maroon-900">{{ $p->sender_account }}</dd>
                                    </div>
                                @endif

                                <div class="flex justify-between gap-3">
                                    <dt class="text-maroon-400">Tgl transfer</dt>
                                    <dd class="text-right font-semibold text-maroon-900">
                                        {{ $p->paid_at ? $p->paid_at->format('d M Y, H:i') : '—' }}
                                    </dd>
                                </div>

                                @if($p->verified_at)
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-maroon-400">Dikonfirmasi</dt>
                                        <dd class="text-right font-semibold text-emerald-700">
                                            {{ $p->verified_at->format('d M Y, H:i') }}
                                        </dd>
                                    </div>
                                @endif
                            </dl>

                            @if($status === 'rejected' && filled($p->rejection_reason))
                                <div class="mt-3 w-full max-w-[280px] rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-[11px] leading-relaxed text-rose-700">
                                    <span class="font-bold">Alasan ditolak:</span>
                                    {{ $p->rejection_reason }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- AKSI --}}
                    <div class="mt-6 flex justify-end border-t border-maroon-100 pt-5">
                        @if($status !== 'verified')
                            <div class="flex justify-end gap-2">
                                <button
                                    type="button"
                                    @click="confirming = true"
                                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 hover:shadow-md"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Konfirmasi
                                </button>

                                <button
                                    type="button"
                                    @click="rejecting = !rejecting"
                                    class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-50"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                              d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Tolak
                                </button>
                            </div>

                            {{--
                            | GANTI: window.confirm() bawaan browser diganti modal
                            | milik website sendiri, konsisten dengan tampilan
                            | Zalina Admin (bukan popup abu-abu khas browser).
                            | Isi pesannya tetap memakai $confirmMessage yang
                            | sudah menyesuaikan kondisi pencocokan nominal
                            | (sesuai / kurang / lebih / belum diisi).
                            --}}
                            <div
                                x-show="confirming"
                                x-cloak
                                x-transition.opacity
                                @keydown.escape.window="confirming = false"
                                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                                style="display: none;"
                            >
                                <div
                                    class="absolute inset-0 bg-maroon-950/60 backdrop-blur-sm"
                                    @click="confirming = false"
                                ></div>

                                <div
                                    x-show="confirming"
                                    x-transition.scale.origin.top
                                    class="relative w-full max-w-sm rounded-3xl bg-white p-6 text-left shadow-2xl"
                                >
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ in_array($trState, ['short', 'over']) ? 'bg-amber-100 text-amber-700' : ($trState === 'none' ? 'bg-maroon-100 text-maroon-600' : 'bg-emerald-100 text-emerald-700') }}">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if($trState === 'short' || $trState === 'over' || $trState === 'none')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                      d="M12 9v4m0 4h.01M10.29 3.86l-8.18 14.18A1 1 0 003 19.5h18a1 1 0 00.89-1.46L13.71 3.86a1 1 0 00-1.72 0z"/>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                      d="M5 13l4 4L19 7"/>
                                            @endif
                                        </svg>
                                    </div>

                                    <h3 class="mt-4 text-base font-bold text-maroon-950">
                                        @if($trState === 'short' || $trState === 'over')
                                            Nominal Transfer Tidak Sesuai
                                        @elseif($trState === 'none')
                                            Nominal Belum Diisi Pelanggan
                                        @else
                                            Konfirmasi Pembayaran
                                        @endif
                                    </h3>

                                    <p class="mt-2 text-sm leading-relaxed text-maroon-600">
                                        {{ $confirmMessage }}
                                    </p>

                                    <div class="mt-6 flex justify-end gap-2">
                                        <button
                                            type="button"
                                            @click="confirming = false"
                                            class="rounded-xl px-4 py-2 text-xs font-semibold text-maroon-600 transition hover:bg-maroon-50"
                                        >
                                            Batal
                                        </button>

                                        <form
                                            method="POST"
                                            action="{{ route('admin.payments.verify', $p) }}"
                                        >
                                            @csrf

                                            <button
                                                type="submit"
                                                class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700"
                                            >
                                                Ya, Konfirmasi
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <form
                                x-show="rejecting"
                                x-transition
                                method="POST"
                                action="{{ route('admin.payments.reject', $p) }}"
                                class="mt-3 w-full max-w-md rounded-2xl border border-rose-100 bg-rose-50 p-3 text-left"
                            >
                                @csrf

                                <label class="mb-2 block text-xs font-semibold text-rose-800">
                                    Alasan Penolakan
                                </label>

                                <textarea
                                    required
                                    name="reason"
                                    rows="3"
                                    placeholder="Contoh: Bukti pembayaran tidak sesuai dengan nominal tagihan."
                                    class="w-full rounded-xl border border-rose-200 bg-white p-3 text-xs text-maroon-900 outline-none transition placeholder:text-rose-300 focus:border-rose-400 focus:ring-2 focus:ring-rose-100"
                                ></textarea>

                                <div class="mt-2 flex justify-end gap-2">
                                    <button
                                        type="button"
                                        @click="rejecting = false"
                                        class="rounded-lg px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-white"
                                    >
                                        Batal
                                    </button>

                                    <button
                                        type="submit"
                                        class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-rose-700"
                                    >
                                        Kirim Penolakan
                                    </button>
                                </div>
                            </form>
                        @else
                            <span class="inline-flex items-center gap-2 text-xs font-semibold text-emerald-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7"/>
                                </svg>
                                Sudah dikonfirmasi
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-6 py-16 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-maroon-50 text-maroon-400">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z"/>
                        </svg>
                    </div>

                    <h3 class="mt-4 text-base font-bold text-maroon-900">
                        Belum ada pembayaran
                    </h3>

                    <p class="mt-1 text-sm text-maroon-500">
                        Pembayaran pelanggan akan muncul di halaman ini.
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- PAGINATION --}}
    <div class="rounded-2xl border border-maroon-100 bg-white px-5 py-4 shadow-sm">
        {{ $payments->withQueryString()->links() }}
    </div>
</div>
@endsection