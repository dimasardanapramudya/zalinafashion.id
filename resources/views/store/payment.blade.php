@extends('layouts.store')

@section('title', 'Pembayaran ' . $order->order_number)

@section('content')

@php
    $p = $order->payment;

    /*
    |--------------------------------------------------------------
    | FLASH STATUS UNTUK POP-UP
    |--------------------------------------------------------------
    | Dipakai untuk menampilkan pop-up ceklist (berhasil) atau
    | toast peringatan pojok kanan atas (gagal) setelah form
    | bukti pembayaran disubmit dan halaman reload.
    |
    | 'success' HANYA dikirim oleh uploadProof() ketika bukti
    | pembayaran benar-benar berhasil diupload — jadi pop-up
    | ceklist ini hanya muncul pada momen itu, bukan begitu
    | pesanan baru dibuat.
    |
    | 'info' dikirim oleh checkout store() saat order baru saja
    | dibuat (klik "Buat Pesanan"). Ini tetap ditampilkan supaya
    | pembeli tahu ordernya tersimpan, tapi sebagai banner biasa —
    | bukan pop-up ceklist sukses.
    */
    $paymentFlashSuccess = session('success');
    $paymentFlashInfo = session('info');
    $paymentFlashError = session('error');
    $paymentValidationErrors = $errors->any() ? $errors->all() : [];

    /*
    |--------------------------------------------------------------
    | STATUS CONFIG
    |--------------------------------------------------------------
    | Palet warna disamakan dengan sistem desain checkout
    | (--zalina-wine #631f2b, --zalina-wine-deep #481f2d,
    | --zalina-gold #b98a3d). Status semantik (verified/rejected/
    | under_review) tetap memakai warna semantik standar
    | (emerald/red/amber) — persis seperti box peringatan di
    | halaman checkout.
    */
    $statusConfig = match($p?->status) {
        'verified' => [
            'label' => 'Pembayaran Terverifikasi',
            'description' => 'Pembayaran telah berhasil dikonfirmasi oleh admin Zalina.',
            'icon' => '✓',
            'wrapper' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
            'iconBg' => 'bg-emerald-600',
            'accent' => 'text-emerald-700',
        ],

        'rejected' => [
            'label' => 'Pembayaran Ditolak',
            'description' => 'Silakan periksa kembali data dan bukti pembayaran Anda.',
            'icon' => '!',
            'wrapper' => 'bg-red-50 border-red-200 text-red-800',
            'iconBg' => 'bg-red-600',
            'accent' => 'text-red-700',
        ],

        'under_review' => [
            'label' => 'Sedang Diverifikasi',
            'description' => 'Bukti pembayaran telah diterima dan sedang diperiksa oleh admin.',
            'icon' => '⏳',
            'wrapper' => 'bg-amber-50 border-amber-200 text-amber-900',
            'iconBg' => 'bg-amber-500',
            'accent' => 'text-amber-700',
        ],

        default => [
            'label' => 'Menunggu Pembayaran',
            'description' => 'Selesaikan pembayaran sesuai nominal yang tertera.',
            'icon' => '₿',
            'wrapper' => 'bg-[#fcf8f9] border-[#eadcdf] text-[#481f2d]',
            'iconBg' => 'bg-[#631f2b]',
            'accent' => 'text-[#631f2b]',
        ],
    };

    /*
    |--------------------------------------------------------------
    | LOGO BANK / E-WALLET
    |--------------------------------------------------------------
    | GANTI: sebelumnya halaman pembayaran ini sama sekali tidak
    | menampilkan logo — cuma nama metode dalam teks. Padahal ini
    | justru halaman paling penting untuk pembeli mengenali rekening
    | tujuan dengan cepat. Logika resolusi logo direplikasi persis
    | dari checkout_blade.php (upload manual > deteksi nama bank/
    | e-wallet > fallback inisial nama) supaya konsisten.
    */
    $paymentMethod = $p?->method;
    $pmName = strtolower(trim($paymentMethod->name ?? ''));

    $pmUploadedImage = null;

    if (!empty($paymentMethod?->image) && file_exists(storage_path('app/public/' . $paymentMethod->image))) {
        $pmUploadedImage = asset('storage/' . $paymentMethod->image);
    }

    $pmLogo = match (true) {
        str_contains($pmName, 'bca') => 'bca.png',
        str_contains($pmName, 'bri') => 'bri.png',
        str_contains($pmName, 'bni') => 'bni.png',
        str_contains($pmName, 'mandiri') => 'mandiri.png',
        str_contains($pmName, 'panin') => 'panin.png',
        str_contains($pmName, 'dana') => 'dana.png',
        str_contains($pmName, 'ovo') => 'ovo.png',
        str_contains($pmName, 'gopay') || str_contains($pmName, 'go pay') || str_contains($pmName, 'go-pay') => 'gopay.png',
        str_contains($pmName, 'shopeepay') || str_contains($pmName, 'shopee pay') || str_contains($pmName, 'shopee-pay') => 'shopeepay.png',
        default => null,
    };

    $pmDetectedImage = ($pmLogo && file_exists(public_path('images/payment-methods/' . $pmLogo)))
        ? asset('images/payment-methods/' . $pmLogo)
        : null;

    $paymentLogoUrl = $pmUploadedImage ?: $pmDetectedImage;
@endphp


{{--
    ============================================================
    STYLE — SAMA PERSIS dengan halaman checkout (font, variabel
    warna, dan seluruh class .zalina-*) supaya desain kedua
    halaman konsisten satu sama lain.
    ============================================================
--}}

<style>

    @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=Manrope:wght@400;500;600;700;800&display=swap');

    :root{
        --zalina-wine: #631f2b;
        --zalina-wine-dark: #7c2d3a;
        --zalina-wine-deep: #481f2d;
        --zalina-gold: #b98a3d;
        --zalina-gold-soft: #f6dfaa;
        --zalina-cream: #fbf6f1;
        --zalina-ink: #481f2d;
    }

    .zalina-checkout{
        font-family: 'Manrope', ui-sans-serif, system-ui, sans-serif;
        background:
            radial-gradient(ellipse at top left, rgba(143,48,75,0.06), transparent 55%),
            radial-gradient(ellipse at bottom right, rgba(212,175,104,0.08), transparent 50%),
            var(--zalina-cream);
    }

    .zalina-display{
        font-family: 'Cormorant Garamond', Georgia, serif;
        letter-spacing: 0.01em;
    }

    .zalina-card{
        background:#fff;
        border:1px solid #eadcdf;
        border-radius: 1.75rem;
        box-shadow: 0 14px 45px rgba(72,31,45,0.05);
        overflow: hidden;
    }

    .zalina-card-head{
        border-bottom:1px solid #f0e5e8;
    }

    .zalina-badge{
        border-radius: 9999px;
        background: linear-gradient(155deg, var(--zalina-wine) 0%, var(--zalina-wine-dark) 100%);
        box-shadow: 0 6px 16px -6px rgba(143,48,75,0.55);
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-style: italic;
    }

    .zalina-input{
        border:1px solid #eadcdf;
        background:#fff;
        border-radius: 1rem;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .zalina-input:focus{
        outline:none;
        border-color: var(--zalina-wine);
        box-shadow: 0 0 0 3px rgba(143,48,75,0.10);
    }

    .zalina-input:disabled{
        background:#f5eef0;
        color:#a58b92;
        cursor:not-allowed;
    }

    .zalina-gold-rule{
        background: linear-gradient(90deg, var(--zalina-wine) 0%, var(--zalina-gold) 100%);
    }

    .zalina-btn-primary{
        border-radius: 9999px;
        background: linear-gradient(155deg, var(--zalina-wine) 0%, var(--zalina-wine-dark) 100%);
        letter-spacing: 0.03em;
        box-shadow: 0 14px 30px -12px rgba(143,48,75,0.5);
        transition: transform .2s ease, box-shadow .2s ease, filter .15s ease;
    }

    .zalina-btn-primary:hover:not(:disabled){
        filter: brightness(1.06);
        transform: translateY(-2px);
        box-shadow: 0 18px 34px -12px rgba(143,48,75,0.55);
    }

    .zalina-soft-box{
        border-radius: 1.25rem;
    }

    .zalina-pill-box{
        border-radius: 1.5rem;
    }

    /* ------------------------------------------------------------
       Kartu gelap "detail transfer" (nomor rekening / akun),
       memakai variabel warna yang sama dengan .zalina-badge.
    ------------------------------------------------------------ */

    .zalina-payment-hero-card{
        background: linear-gradient(155deg, var(--zalina-wine-deep) 0%, var(--zalina-wine) 55%, var(--zalina-wine-dark) 100%);
    }

    /* ------------------------------------------------------------
       Pop-up sukses / toast error — animasi ceklist & progress bar
    ------------------------------------------------------------ */

    #payment-success-circle {
        stroke-dasharray: 283;
        stroke-dashoffset: 283;
    }

    #payment-success-check {
        stroke-dasharray: 60;
        stroke-dashoffset: 60;
    }

    .payment-success-box-in #payment-success-circle {
        animation: paymentCircleDraw .6s cubic-bezier(.65,0,.45,1) forwards;
    }

    .payment-success-box-in #payment-success-check {
        animation: paymentCheckDraw .35s cubic-bezier(.65,0,.45,1) .55s forwards;
    }

    .payment-success-box-in #payment-success-ping {
        animation: paymentPing 1s cubic-bezier(0,0,.2,1) .6s 1;
    }

    @keyframes paymentCircleDraw {
        to { stroke-dashoffset: 0; }
    }

    @keyframes paymentCheckDraw {
        to { stroke-dashoffset: 0; }
    }

    @keyframes paymentPing {
        0%   { transform: scale(0.8); opacity: .6; }
        75%, 100% { transform: scale(1.6); opacity: 0; }
    }

    #payment-error-toast-bar {
        transform-origin: left;
        animation: paymentToastShrink 6s linear forwards;
    }

    @keyframes paymentToastShrink {
        from { transform: scaleX(1); }
        to   { transform: scaleX(0); }
    }

</style>


<div class="zalina-checkout min-h-screen relative overflow-hidden">

    {{-- DECORATIVE BACKGROUND (tambahan di atas radial gradient .zalina-checkout) --}}
    <div class="pointer-events-none absolute inset-0 -z-10">
        <div class="absolute -top-32 -right-32 h-96 w-96 rounded-full bg-[#631f2b]/10 blur-3xl"></div>
        <div class="absolute top-[35rem] -left-40 h-96 w-96 rounded-full bg-[#b98a3d]/10 blur-3xl"></div>
    </div>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}

        <div class="mb-8 sm:mb-10">

            <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-[#d8b66f]/40 bg-white/70 px-4 py-2 text-[10px] font-bold uppercase tracking-[0.28em] text-[#9b7540] shadow-sm backdrop-blur">
                <span class="h-1.5 w-1.5 rounded-full bg-[#b98a3d]"></span>
                Pesanan {{ $order->order_number }}
            </div>

            <div class="flex flex-col justify-between gap-5 md:flex-row md:items-end">

                <div>
                    <h1 class="zalina-display text-4xl md:text-5xl font-semibold tracking-tight text-[#481f2d]">
                        Pembayaran
                        <span class="block italic text-[#631f2b]">
                            Pesanan Anda
                        </span>
                    </h1>

                    <p class="text-sm md:text-base text-[#806b72] mt-4 max-w-2xl leading-relaxed">
                        Lengkapi pembayaran dengan tenang. Pesanan Anda akan diproses
                        setelah pembayaran berhasil dikonfirmasi oleh admin.
                    </p>
                </div>

                <div class="inline-flex w-fit items-center gap-2 rounded-full border border-[#eadcdf] bg-white/80 px-4 py-2 text-xs font-semibold text-[#631f2b] shadow-sm backdrop-blur">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-[#b98a3d]"></span>
                    Secure checkout
                </div>

            </div>


            {{-- STEPPER: checkout -> pembayaran (konsisten dengan stepper di halaman checkout) --}}

            <ol class="mt-6 flex flex-wrap items-center gap-x-3 gap-y-2 text-xs font-semibold">

                <li class="inline-flex items-center gap-2 rounded-full border border-[#eadcdf] bg-white/80 px-4 py-2 text-[#9b7540]">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#f6dfaa] text-[10px] text-[#7c5a20]">1</span>
                    Isi Data &amp; Buat Pesanan
                </li>

                <li class="text-[#c9b2b8]" aria-hidden="true">→</li>

                <li class="inline-flex items-center gap-2 rounded-full bg-[#631f2b] px-4 py-2 text-white shadow-sm">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white/20 text-[10px]">2</span>
                    Pembayaran &amp; Upload Bukti
                </li>

            </ol>

        </div>

        @if(!$p)

            {{-- PAYMENT NOT FOUND --}}
            <div class="zalina-card p-8 text-center sm:p-12">
                <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-red-50 text-2xl text-red-600">
                    !
                </div>

                <h2 class="zalina-display text-3xl font-semibold text-[#481f2d]">
                    Data Pembayaran Tidak Ditemukan
                </h2>

                <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-gray-500">
                    Data pembayaran untuk pesanan ini belum tersedia.
                    Silakan hubungi admin apabila masalah terus terjadi.
                </p>
            </div>

        @else

            {{-- PAYMENT STATUS HERO --}}
            <div class="zalina-card relative mb-8">

                <div class="absolute right-0 top-0 h-40 w-40 translate-x-1/3 -translate-y-1/3 rounded-full bg-[#b98a3d]/15 blur-2xl"></div>

                <div class="relative flex flex-col gap-5 p-6 sm:p-8 md:flex-row md:items-center md:justify-between">

                    <div class="flex items-start gap-4">

                        <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-2xl text-xl font-bold text-white shadow-lg {{ $statusConfig['iconBg'] }}">
                            {{ $statusConfig['icon'] }}
                        </div>

                        <div>
                            <p class="mb-1 text-xs font-bold uppercase tracking-[0.16em] text-[#9b7540]">
                                Payment status
                            </p>

                            <h2 class="zalina-display text-2xl font-semibold text-[#481f2d] sm:text-3xl">
                                {{ $statusConfig['label'] }}
                            </h2>

                            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-gray-500">
                                {{ $statusConfig['description'] }}
                            </p>
                        </div>

                    </div>

                    <div class="zalina-soft-box border border-[#eadcdf] bg-[#fcf8f9] px-5 py-4 md:min-w-[210px]">
                        <p class="text-xs font-semibold uppercase tracking-wider text-[#9b7540]">
                            Total transfer
                        </p>

                        <p class="mt-1 text-xl font-bold text-[#481f2d] sm:text-2xl">
                            Rp {{ number_format($p->amount_expected, 0, ',', '.') }}
                        </p>
                    </div>

                </div>

                <div class="zalina-gold-rule h-1"></div>

            </div>

            <div class="grid gap-8 lg:grid-cols-[0.92fr_1.08fr]">

                {{-- LEFT COLUMN --}}
                <div class="space-y-8">

                    {{-- ========================================================= --}}
                    {{-- 01. RINGKASAN PESANAN --}}
                    {{-- ========================================================= --}}

                    <section class="zalina-card">

                        <div class="zalina-card-head px-5 py-6 md:px-8 md:py-7">

                            <div class="flex items-center justify-between gap-4">

                                <div class="flex items-center gap-4">

                                    <div class="w-12 h-12 shrink-0 zalina-badge rounded-full text-white flex items-center justify-center text-lg">
                                        01
                                    </div>

                                    <div>
                                        <h2 class="zalina-display text-xl md:text-2xl font-semibold text-[#481f2d]">
                                            Ringkasan Pesanan
                                        </h2>

                                        <p class="text-sm text-gray-500 mt-1">
                                            Produk yang ada di dalam pesanan ini.
                                        </p>
                                    </div>

                                </div>

                                <span class="hidden sm:inline-flex rounded-full bg-[#fbf2f4] px-3 py-1 text-xs font-semibold text-[#631f2b]">
                                    #{{ $order->order_number }}
                                </span>

                            </div>

                        </div>

                        <div class="p-5 md:p-7">

                            <div class="space-y-4">

                                @foreach($order->items as $item)

                                    <div class="flex items-center gap-4">

                                        <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-2xl border border-[#eadcdf] bg-[#f7eef0]">
                                            @if($item->product?->image)
                                                <img
                                                    src="{{ asset('storage/' . $item->product->image) }}"
                                                    alt="{{ $item->product->name }}"
                                                    class="h-full w-full object-cover"
                                                >
                                            @else
                                                <div class="flex h-full w-full items-center justify-center text-xl text-[#c9b2b8]">
                                                    Z
                                                </div>
                                            @endif
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <h3 class="truncate text-sm font-semibold text-[#481f2d]">
                                                {{ $item->product_name ?: ($item->product?->name ?? 'Produk Zalina') }}
                                            </h3>

                                            @if(filled($item->variant_name))
                                                <p class="mt-0.5 text-xs font-semibold text-[#9b7540]">
                                                    Varian/Warna: {{ $item->variant_name }}
                                                </p>
                                            @endif

                                            <p class="mt-1 text-xs text-gray-500">
                                                {{ $item->quantity }} ×
                                                Rp {{ number_format($item->price, 0, ',', '.') }}
                                            </p>
                                        </div>

                                        <p class="text-right text-sm font-bold text-[#631f2b]">
                                            Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                                        </p>

                                    </div>

                                @endforeach

                                <div class="border-t border-dashed border-[#eadcdf] pt-4">

                                    <div class="flex justify-between gap-4 text-sm text-gray-500">
                                        <span>Subtotal</span>
                                        <span>
                                            Rp {{ number_format($order->subtotal ?? 0, 0, ',', '.') }}
                                        </span>
                                    </div>

                                    @if(isset($order->shipping_total))

                                        <div class="mt-2 flex justify-between gap-4 text-sm text-gray-500">
                                            <span>Pengiriman</span>

                                            @if((float) ($order->shipping_total ?? 0) <= 0)
                                                <span class="font-semibold text-emerald-600">
                                                    GRATIS ONGKIR
                                                </span>
                                            @else
                                                <span>
                                                    Rp {{ number_format((float) $order->shipping_total, 0, ',', '.') }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="mt-2 flex justify-between gap-4 text-sm text-gray-500">
                                            <span>Biaya Admin</span>
                                            <span>
                                                {{ (float) ($order->admin_fee ?? 0) > 0
                                                    ? 'Rp '.number_format((float) $order->admin_fee, 0, ',', '.')
                                                    : 'Gratis' }}
                                            </span>
                                        </div>

                                    @endif

                                    @if((float) ($order->discount_total ?? 0) > 0)
                                        <div class="mt-2 flex justify-between gap-4 text-sm text-emerald-700">
                                            <span>Diskon</span>
                                            <span>
                                                - Rp {{ number_format((float) $order->discount_total, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @endif

                                    <div class="mt-4 flex items-end justify-between gap-4">
                                        <span class="font-semibold text-[#481f2d]">
                                            Total pesanan
                                        </span>

                                        <span class="zalina-display text-2xl font-bold text-[#631f2b]">
                                            Rp {{ number_format($p->amount_expected, 0, ',', '.') }}
                                        </span>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </section>

                    {{-- ========================================================= --}}
                    {{-- 02. DETAIL PEMBAYARAN --}}
                    {{-- ========================================================= --}}

                    <section class="zalina-card">

                        <div class="zalina-card-head px-5 py-6 md:px-8 md:py-7">

                            <div class="flex items-center gap-4">

                                <div class="w-12 h-12 shrink-0 zalina-badge rounded-full text-white flex items-center justify-center text-lg">
                                    02
                                </div>

                                <div>
                                    <h2 class="zalina-display text-xl md:text-2xl font-semibold text-[#481f2d]">
                                        Detail Pembayaran
                                    </h2>

                                    <p class="text-sm text-gray-500 mt-1">
                                        Transfer sesuai nominal ke rekening / akun berikut.
                                    </p>
                                </div>

                            </div>

                        </div>

                        <div class="p-5 md:p-7">

                            <div class="zalina-payment-hero-card relative overflow-hidden rounded-3xl p-6 text-white shadow-lg">

                                <div class="absolute -right-12 -top-12 h-40 w-40 rounded-full border border-white/10"></div>
                                <div class="absolute -bottom-20 -left-12 h-48 w-48 rounded-full border border-white/10"></div>

                                <div class="relative">

                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.2em] text-white/70">
                                                Payment account
                                            </p>

                                            <h3 class="zalina-display mt-2 text-2xl">
                                                {{ $p->method?->name ?? 'Metode Pembayaran' }}
                                            </h3>
                                        </div>

                                        {{--
                                            GANTI: sebelumnya cuma tanda bintang dekoratif
                                            "✦". Sekarang logo bank/e-wallet ditampilkan
                                            besar (h-14–16, mobile tetap muat karena pakai
                                            badge putih ukuran tetap, bukan lebar penuh)
                                            supaya pembeli langsung yakin transfer ke
                                            rekening yang benar. Fallback ke "✦" kalau
                                            logo tidak tersedia.
                                        --}}
                                        @if($paymentLogoUrl)
                                            <div class="flex h-14 w-20 shrink-0 items-center justify-center rounded-2xl bg-white p-2 shadow-lg sm:h-16 sm:w-24">
                                                <img
                                                    src="{{ $paymentLogoUrl }}"
                                                    alt="{{ $p->method?->name }}"
                                                    class="max-h-full max-w-full object-contain"
                                                    loading="lazy"
                                                    onerror="this.closest('div').classList.add('hidden')"
                                                >
                                            </div>
                                        @else
                                            <div class="text-2xl text-[#f6dfaa]">
                                                ✦
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mt-8">
                                        <p class="text-xs uppercase tracking-wider text-white/70">
                                            Nomor rekening / akun
                                        </p>

                                        <div class="mt-2 flex flex-wrap items-center gap-3">
                                            <p class="break-all text-xl font-bold tracking-wider sm:text-2xl">
                                                {{ $p->method?->account_number ?? '-' }}
                                            </p>

                                            @if($p->method?->account_number)
                                                <button
                                                    type="button"
                                                    onclick="copyAccount(@js($p->method?->account_number))"
                                                    class="rounded-full border border-white/20 px-3 py-1 text-xs font-semibold text-white transition hover:bg-white/10"
                                                >
                                                    Salin
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-7 flex flex-wrap justify-between gap-5">
                                        <div>
                                            <p class="text-xs uppercase tracking-wider text-white/70">
                                                Atas nama
                                            </p>

                                            <p class="mt-1 font-semibold">
                                                {{ $p->method?->account_name ?? '-' }}
                                            </p>
                                        </div>

                                        <div class="text-right">
                                            <p class="text-xs uppercase tracking-wider text-white/70">
                                                Nominal
                                            </p>

                                            <p class="mt-1 font-semibold text-[#f6dfaa]">
                                                Rp {{ number_format($p->amount_expected, 0, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>

                                </div>

                            </div>

                            @if($p->method?->instructions)
                                <div class="zalina-soft-box mt-5 border border-[#f0d9a6] bg-[#fff8e6] p-4">
                                    <p class="text-xs font-bold uppercase tracking-wider text-[#7c5a20]">
                                        Instruksi pembayaran
                                    </p>

                                    <p class="mt-2 text-sm leading-relaxed text-[#7c5a20]">
                                        {{ $p->method->instructions }}
                                    </p>
                                </div>
                            @endif

                        </div>

                    </section>

                </div>

                {{-- RIGHT COLUMN --}}
                <div class="space-y-8">

                    @if(in_array($p->status, ['pending', 'rejected']))

                        {{-- ========================================================= --}}
                        {{-- 03. KIRIM BUKTI PEMBAYARAN --}}
                        {{-- ========================================================= --}}

                        <section class="zalina-card">

                            <div class="zalina-card-head px-5 py-6 md:px-8 md:py-7">

                                <div class="flex items-center gap-4">

                                    <div class="w-12 h-12 shrink-0 zalina-badge rounded-full text-white flex items-center justify-center text-lg">
                                        03
                                    </div>

                                    <div>
                                        <h2 class="zalina-display text-xl md:text-2xl font-semibold text-[#481f2d]">
                                            Kirim Bukti Pembayaran
                                        </h2>

                                        <p class="text-sm text-gray-500 mt-1">
                                            Pastikan informasi pengirim dan bukti transfer sudah sesuai.
                                        </p>
                                    </div>

                                </div>

                            </div>

                            <div class="p-5 md:p-7">

                                @if($p->status === 'rejected')
                                    <div class="zalina-soft-box mb-6 border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                                        <div class="flex items-start gap-3">
                                            <span class="text-lg">!</span>

                                            <div>
                                                <p class="font-bold">
                                                    Pembayaran sebelumnya ditolak
                                                </p>

                                                <p class="mt-1 leading-relaxed">
                                                    {{ $p->rejection_reason ?? 'Silakan periksa kembali bukti pembayaran Anda.' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($paymentFlashInfo)
                                    <div class="zalina-soft-box mb-6 border border-[#f0d9a6] bg-[#fff8e6] p-4 text-sm text-[#7c5a20]">
                                        <div class="flex items-start gap-3">
                                            <span class="text-lg">✦</span>

                                            <div>
                                                <p class="font-bold">
                                                    Pesanan tersimpan
                                                </p>

                                                <p class="mt-1 leading-relaxed">
                                                    {{ $paymentFlashInfo }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($paymentFlashError && !$paymentValidationErrors)
                                    <div class="zalina-soft-box mb-6 border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                                        <div class="flex items-start gap-3">
                                            <span class="text-lg">!</span>

                                            <div>
                                                <p class="font-bold">
                                                    Gagal mengirim bukti pembayaran
                                                </p>

                                                <p class="mt-1 leading-relaxed">
                                                    {{ $paymentFlashError }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($errors->any())
                                    <div class="zalina-soft-box mb-6 border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                                        <p class="font-bold">Periksa kembali data berikut:</p>

                                        <ul class="mt-2 list-disc space-y-1 pl-5">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form
                                    method="POST"
                                    enctype="multipart/form-data"
                                    action="{{ route('payment.upload', $order) }}"
                                    class="space-y-5"
                                >

                                    @csrf

                                    <div>
                                        <label class="mb-2 block text-sm font-semibold text-[#481f2d]">
                                            Nama pengirim
                                        </label>

                                        <input
                                            name="sender_name"
                                            required
                                            value="{{ old('sender_name', $p->sender_name) }}"
                                            placeholder="Nama sesuai rekening pengirim"
                                            class="w-full zalina-input px-4 py-3.5 text-sm text-gray-800 placeholder:text-gray-400"
                                        >
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-semibold text-[#481f2d]">
                                            Nomor rekening / akun
                                            <span class="font-normal text-gray-400">(opsional)</span>
                                        </label>

                                        <input
                                            name="sender_account"
                                            value="{{ old('sender_account', $p->sender_account) }}"
                                            placeholder="Nomor rekening atau akun pengirim"
                                            class="w-full zalina-input px-4 py-3.5 text-sm text-gray-800 placeholder:text-gray-400"
                                        >
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-semibold text-[#481f2d]">
                                            Nominal pembayaran
                                        </label>

                                        <div class="relative">
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-[#9b7540]">
                                                Rp
                                            </span>

                                            <input
                                                name="amount_paid"
                                                required
                                                type="number"
                                                min="1"
                                                value="{{ old('amount_paid', $p->amount_expected) }}"
                                                class="w-full zalina-input py-3.5 pl-12 pr-4 text-sm font-semibold text-gray-800"
                                            >
                                        </div>
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-semibold text-[#481f2d]">
                                            Waktu pembayaran
                                        </label>

                                        <input
                                            name="paid_at"
                                            required
                                            type="datetime-local"
                                            value="{{ old('paid_at') }}"
                                            class="w-full zalina-input px-4 py-3.5 text-sm text-gray-800"
                                        >
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-semibold text-[#481f2d]">
                                            Bukti transfer
                                        </label>

                                        <label
                                            for="proof"
                                            id="proof-upload-label"
                                            class="zalina-soft-box group flex cursor-pointer flex-col items-center justify-center border-2 border-dashed border-[#eadcdf] bg-[#fcf8f9] px-5 py-8 text-center transition hover:border-[#631f2b] hover:bg-[#fbf2f4]"
                                        >
                                            <span id="proof-upload-icon" class="mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-2xl text-[#631f2b] shadow-sm transition group-hover:scale-105">
                                                ↑
                                            </span>

                                            <span id="proof-upload-title" class="text-sm font-bold text-[#481f2d]">
                                                Pilih bukti pembayaran
                                            </span>

                                            <span id="proof-upload-hint" class="mt-1 text-xs leading-relaxed text-gray-500">
                                                JPG, PNG, atau WEBP · Maksimal sesuai ketentuan sistem
                                            </span>

                                            <span
                                                id="proof-name"
                                                class="mt-3 hidden max-w-full break-all rounded-full bg-white px-4 py-2 text-xs font-semibold text-[#631f2b] shadow-sm"
                                            ></span>

                                            <input
                                                id="proof"
                                                name="proof"
                                                required
                                                type="file"
                                                accept="image/jpeg,image/png,image/webp"
                                                class="hidden"
                                                onchange="showProofName(this)"
                                            >
                                        </label>
                                    </div>

                                    <button
                                        type="submit"
                                        class="zalina-btn-primary group flex w-full items-center justify-center gap-3 py-4 px-5 text-sm font-bold text-white transition duration-200"
                                    >
                                        Kirim Bukti Pembayaran
                                        <span class="transition group-hover:translate-x-1">
                                            →
                                        </span>
                                    </button>

                                    <p class="text-center text-xs leading-relaxed text-gray-400">
                                        Dengan mengirim bukti, Anda menyatakan bahwa data pembayaran yang diberikan benar.
                                    </p>

                                </form>

                            </div>

                        </section>

                    @else

                        {{-- ========================================================= --}}
                        {{-- 03. BUKTI PEMBAYARAN (receipt view) --}}
                        {{-- ========================================================= --}}

                        <section class="zalina-card">

                            <div class="zalina-card-head px-5 py-6 md:px-8 md:py-7">

                                <div class="flex items-center gap-4">

                                    <div class="w-12 h-12 shrink-0 zalina-badge rounded-full text-white flex items-center justify-center text-lg">
                                        03
                                    </div>

                                    <div>
                                        <h2 class="zalina-display text-xl md:text-2xl font-semibold text-[#481f2d]">
                                            Bukti Pembayaran
                                        </h2>

                                        <p class="text-sm text-gray-500 mt-1">
                                            Status verifikasi bukti transfer yang sudah dikirim.
                                        </p>
                                    </div>

                                </div>

                            </div>

                            <div class="p-5 md:p-7">

                                @if($p->status === 'under_review')
                                    <div class="zalina-soft-box border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
                                        <div class="flex items-start gap-3">
                                            <span class="text-xl">⏳</span>

                                            <div>
                                                <p class="font-bold">
                                                    Menunggu approval admin
                                                </p>

                                                <p class="mt-1 leading-relaxed">
                                                    Bukti pembayaran sudah kami terima dan sedang diperiksa.
                                                </p>

                                                <p class="mt-2 text-xs text-amber-700">
                                                    Receipt resmi tersedia setelah pembayaran disetujui.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($p->status === 'verified')
                                    <div class="zalina-soft-box border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-800">
                                        <div class="flex items-start gap-3">
                                            <span class="text-xl">✓</span>

                                            <div>
                                                <p class="font-bold">
                                                    Pembayaran disetujui admin
                                                </p>

                                                <p class="mt-1 leading-relaxed">
                                                    Pembayaran berhasil dikonfirmasi. Receipt resmi telah tersedia.
                                                </p>

                                                <a
                                                    href="{{ route('receipt.show', $order) }}"
                                                    class="zalina-btn-primary mt-4 inline-flex items-center justify-center px-5 py-3 text-sm font-bold text-white"
                                                >
                                                    Lihat Receipt
                                                    <span class="ml-2">→</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($p->proof_path)
                                    <div class="zalina-soft-box mt-6 overflow-hidden border border-[#eadcdf] bg-[#fcf8f9]">
                                        <div class="border-b border-[#eadcdf] bg-white px-4 py-3">
                                            <p class="text-xs font-bold uppercase tracking-wider text-[#9b7540]">
                                                Uploaded proof
                                            </p>
                                        </div>

                                        <img
                                            src="{{ asset('storage/' . $p->proof_path) }}"
                                            alt="Bukti Pembayaran"
                                            class="max-h-[520px] w-full object-contain"
                                        >
                                    </div>
                                @endif

                                <div class="mt-6 space-y-3 border-t border-dashed border-[#eadcdf] pt-5 text-sm">

                                    <div class="flex justify-between gap-4">
                                        <span class="text-gray-500">
                                            Nama pengirim
                                        </span>

                                        <span class="text-right font-semibold text-[#481f2d]">
                                            {{ $p->sender_name ?? '-' }}
                                        </span>
                                    </div>

                                    <div class="flex justify-between gap-4">
                                        <span class="text-gray-500">
                                            Nominal dibayar
                                        </span>

                                        <span class="text-right font-semibold text-[#481f2d]">
                                            Rp {{ number_format($p->amount_paid ?? 0, 0, ',', '.') }}
                                        </span>
                                    </div>

                                    @if($p->paid_at)
                                        <div class="flex justify-between gap-4">
                                            <span class="text-gray-500">
                                                Waktu pembayaran
                                            </span>

                                            <span class="text-right font-semibold text-[#481f2d]">
                                                {{ \Carbon\Carbon::parse($p->paid_at)->format('d M Y H:i') }}
                                            </span>
                                        </div>
                                    @endif

                                </div>

                            </div>

                        </section>

                    @endif

                    {{-- SECURITY NOTE --}}
                    <div class="zalina-soft-box flex items-start gap-3 border border-[#eadcdf] bg-white/70 p-5 text-sm text-gray-500 shadow-sm">
                        <span class="text-lg text-[#b98a3d]">✦</span>

                        <p class="leading-relaxed">
                            Jangan membagikan bukti pembayaran atau informasi rekening
                            kepada pihak lain. Pastikan transfer hanya dilakukan ke
                            rekening resmi Zalina.
                        </p>
                    </div>

                </div>

            </div>

        @endif

    </div>

</div>

{{-- ================================================================
     POP-UP CEKLIST SUKSES (muncul saat bukti pembayaran berhasil
     terkirim, dipicu dari session('success'))
================================================================ --}}
<div id="payment-success-modal"
     class="fixed inset-0 z-[9998] hidden items-center justify-center px-4"
     role="dialog"
     aria-modal="true">

    <div id="payment-success-overlay"
         class="absolute inset-0 bg-[#481f2d]/60 opacity-0 backdrop-blur-sm transition-opacity duration-300"
         onclick="closePaymentSuccessModal()"></div>

    <div id="payment-success-box"
         class="zalina-card relative z-10 w-full max-w-sm translate-y-4 scale-95 p-8 text-center opacity-0 shadow-2xl transition-all duration-300 ease-out">

        <div class="relative mx-auto mb-5 flex h-24 w-24 items-center justify-center">
            <span id="payment-success-ping" class="absolute inset-0 rounded-full bg-emerald-200 opacity-0"></span>

            <svg viewBox="0 0 100 100" class="relative h-24 w-24">
                <circle id="payment-success-circle"
                        cx="50" cy="50" r="45"
                        fill="none"
                        stroke="#059669"
                        stroke-width="6"
                        stroke-linecap="round"
                        transform="rotate(-90 50 50)"></circle>

                <path id="payment-success-check"
                      d="M29 51 L44 66 L73 34"
                      fill="none"
                      stroke="#059669"
                      stroke-width="7"
                      stroke-linecap="round"
                      stroke-linejoin="round"></path>
            </svg>
        </div>

        <h3 class="zalina-display text-2xl font-semibold text-[#481f2d]">
            Berhasil Terkirim!
        </h3>

        <p id="payment-success-message" class="mx-auto mt-2 max-w-xs text-sm leading-relaxed text-gray-500">
            Bukti pembayaran Anda sudah kami terima dan akan segera diperiksa oleh admin.
        </p>

        <button type="button"
                onclick="closePaymentSuccessModal()"
                class="zalina-btn-primary mt-6 w-full px-6 py-3.5 text-sm font-bold text-white">
            Oke, Mengerti
        </button>
    </div>
</div>

{{-- ================================================================
     TOAST PERINGATAN (pojok kanan atas, muncul saat pengiriman
     bukti pembayaran gagal / ada error validasi)
================================================================ --}}
<div id="payment-error-toast"
     class="fixed right-4 top-4 z-[9999] hidden w-[calc(100%-2rem)] max-w-sm translate-x-[120%] opacity-0 transition-all duration-300 ease-out sm:right-6 sm:top-6"
     role="alert">

    <div class="zalina-card overflow-hidden">
        <div class="flex items-start gap-3 p-4">
            <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-red-100 text-lg font-bold text-red-600">
                !
            </span>

            <div class="min-w-0 flex-1 pt-0.5">
                <p id="payment-error-toast-title" class="text-sm font-bold text-[#481f2d]">
                    Gagal Mengirim
                </p>

                <div id="payment-error-toast-body" class="mt-1 space-y-0.5 text-xs leading-relaxed text-gray-500"></div>
            </div>

            <button type="button"
                    onclick="closePaymentErrorToast()"
                    aria-label="Tutup"
                    class="flex-shrink-0 rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                </svg>
            </button>
        </div>

        <div class="h-1 w-full bg-red-100">
            <div id="payment-error-toast-bar" class="h-full w-full bg-red-500"></div>
        </div>
    </div>
</div>

<script>
    const paymentFlashSuccessMessage = @js($paymentFlashSuccess);
    const paymentFlashErrorMessage = @js($paymentFlashError);
    const paymentValidationErrorMessages = @js($paymentValidationErrors);

    function openPaymentSuccessModal(message) {
        const modal = document.getElementById('payment-success-modal');
        const overlay = document.getElementById('payment-success-overlay');
        const box = document.getElementById('payment-success-box');
        const messageEl = document.getElementById('payment-success-message');

        if (!modal || !overlay || !box) {
            return;
        }

        if (message) {
            messageEl.textContent = message;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        requestAnimationFrame(() => {
            overlay.classList.remove('opacity-0');
            overlay.classList.add('opacity-100');

            box.classList.remove('scale-95', 'opacity-0', 'translate-y-4');
            box.classList.add('scale-100', 'opacity-100', 'translate-y-0', 'payment-success-box-in');
        });

        document.body.classList.add('overflow-hidden');
    }

    function closePaymentSuccessModal() {
        const modal = document.getElementById('payment-success-modal');
        const overlay = document.getElementById('payment-success-overlay');
        const box = document.getElementById('payment-success-box');

        if (!modal || !overlay || !box) {
            return;
        }

        overlay.classList.remove('opacity-100');
        overlay.classList.add('opacity-0');

        box.classList.remove('scale-100', 'opacity-100', 'translate-y-0');
        box.classList.add('scale-95', 'opacity-0', 'translate-y-4');
        box.classList.remove('payment-success-box-in');

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 250);

        document.body.classList.remove('overflow-hidden');
    }

    let paymentErrorToastTimer = null;

    function showPaymentErrorToast(messages, title) {
        const toast = document.getElementById('payment-error-toast');
        const titleEl = document.getElementById('payment-error-toast-title');
        const bodyEl = document.getElementById('payment-error-toast-body');
        const bar = document.getElementById('payment-error-toast-bar');

        if (!toast || !bodyEl) {
            return;
        }

        const list = Array.isArray(messages) ? messages.filter(Boolean) : [messages].filter(Boolean);

        if (list.length === 0) {
            return;
        }

        titleEl.textContent = title || 'Gagal Mengirim';
        bodyEl.innerHTML = '';

        list.slice(0, 4).forEach((msg) => {
            const p = document.createElement('p');
            p.textContent = msg;
            bodyEl.appendChild(p);
        });

        toast.classList.remove('hidden');

        // Restart progress bar animation
        bar.style.animation = 'none';
        void bar.offsetWidth;
        bar.style.animation = '';

        requestAnimationFrame(() => {
            toast.classList.remove('translate-x-[120%]', 'opacity-0');
            toast.classList.add('translate-x-0', 'opacity-100');
        });

        clearTimeout(paymentErrorToastTimer);
        paymentErrorToastTimer = setTimeout(closePaymentErrorToast, 6000);
    }

    function closePaymentErrorToast() {
        const toast = document.getElementById('payment-error-toast');

        if (!toast) {
            return;
        }

        toast.classList.remove('translate-x-0', 'opacity-100');
        toast.classList.add('translate-x-[120%]', 'opacity-0');

        clearTimeout(paymentErrorToastTimer);

        setTimeout(() => {
            toast.classList.add('hidden');
        }, 300);
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (paymentValidationErrorMessages && paymentValidationErrorMessages.length > 0) {
            showPaymentErrorToast(paymentValidationErrorMessages, 'Periksa kembali data Anda');
        } else if (paymentFlashErrorMessage) {
            showPaymentErrorToast(paymentFlashErrorMessage, 'Gagal Mengirim');
        } else if (paymentFlashSuccessMessage) {
            openPaymentSuccessModal(paymentFlashSuccessMessage);
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') {
            return;
        }

        const modal = document.getElementById('payment-success-modal');

        if (modal && !modal.classList.contains('hidden')) {
            closePaymentSuccessModal();
        }
    });
</script>

<script>
    /*
    |--------------------------------------------------------------
    | PRATINJAU FILE BUKTI PEMBAYARAN
    |--------------------------------------------------------------
    | Sebelumnya fungsi ini dipanggil dari onchange="showProofName(this)"
    | tapi belum pernah didefinisikan, jadi nama file yang dipilih
    | customer tidak pernah tampil. Sekarang: nama file + ukurannya
    | ditampilkan, kotak upload berubah gaya jadi "terisi", dan kalau
    | filenya gambar, thumbnail pratinjaunya langsung dirender.
    */
    function showProofName(input) {
        const label = document.getElementById('proof-upload-label');
        const nameEl = document.getElementById('proof-name');
        const iconEl = document.getElementById('proof-upload-icon');
        const titleEl = document.getElementById('proof-upload-title');
        const hintEl = document.getElementById('proof-upload-hint');

        const file = input.files && input.files[0];

        if (!file) {
            resetProofUpload();
            return;
        }

        if (nameEl) {
            nameEl.textContent = file.name + ' · ' + formatFileSize(file.size);
            nameEl.classList.remove('hidden');
        }

        if (label) {
            label.classList.remove('border-dashed', 'border-[#eadcdf]', 'bg-[#fcf8f9]');
            label.classList.add('border-solid', 'border-emerald-400', 'bg-emerald-50/50');
        }

        if (iconEl) {
            iconEl.textContent = '✓';
            iconEl.classList.remove('text-[#631f2b]');
            iconEl.classList.add('text-emerald-600');
        }

        if (titleEl) {
            titleEl.textContent = 'File siap diunggah';
        }

        if (hintEl) {
            hintEl.textContent = 'Klik lagi untuk mengganti file.';
        }

        renderProofPreview(file);
    }

    function resetProofUpload() {
        const label = document.getElementById('proof-upload-label');
        const nameEl = document.getElementById('proof-name');
        const iconEl = document.getElementById('proof-upload-icon');
        const titleEl = document.getElementById('proof-upload-title');
        const hintEl = document.getElementById('proof-upload-hint');

        if (nameEl) {
            nameEl.textContent = '';
            nameEl.classList.add('hidden');
        }

        if (label) {
            label.classList.add('border-dashed', 'border-[#eadcdf]', 'bg-[#fcf8f9]');
            label.classList.remove('border-solid', 'border-emerald-400', 'bg-emerald-50/50');
        }

        if (iconEl) {
            iconEl.textContent = '↑';
            iconEl.classList.add('text-[#631f2b]');
            iconEl.classList.remove('text-emerald-600');
        }

        if (titleEl) {
            titleEl.textContent = 'Pilih bukti pembayaran';
        }

        if (hintEl) {
            hintEl.textContent = 'JPG, PNG, atau WEBP · Maksimal sesuai ketentuan sistem';
        }

        removeProofPreview();
    }

    function formatFileSize(bytes) {
        if (typeof bytes !== 'number' || isNaN(bytes)) {
            return '';
        }

        const units = ['B', 'KB', 'MB', 'GB'];
        let size = bytes;
        let unitIndex = 0;

        while (size >= 1024 && unitIndex < units.length - 1) {
            size /= 1024;
            unitIndex++;
        }

        const precision = unitIndex === 0 || size >= 10 ? 0 : 1;

        return size.toFixed(precision) + ' ' + units[unitIndex];
    }

    function renderProofPreview(file) {
        removeProofPreview();

        if (!file.type || !file.type.startsWith('image/')) {
            return;
        }

        const label = document.getElementById('proof-upload-label');

        if (!label || typeof FileReader === 'undefined') {
            return;
        }

        const reader = new FileReader();

        reader.onload = function (event) {
            const img = document.createElement('img');

            img.id = 'proof-preview-image';
            img.src = event.target.result;
            img.alt = 'Pratinjau bukti pembayaran';
            img.className = 'mt-4 max-h-48 w-full rounded-2xl border border-emerald-200 bg-white object-contain p-2';

            label.appendChild(img);
        };

        reader.readAsDataURL(file);
    }

    function removeProofPreview() {
        const existing = document.getElementById('proof-preview-image');

        if (existing) {
            existing.remove();
        }
    }
</script>

<script>
    function copyAccount(account) {
        const cleanAccount = String(account || '').trim();

        if (!cleanAccount) {
            
            return;
        }

        // Metode modern
        if (
            navigator.clipboard &&
            window.isSecureContext
        ) {
            navigator.clipboard.writeText(cleanAccount)
                .then(function () {
                    
                })
                .catch(function () {
                    copyAccountFallback(cleanAccount);
                });

            return;
        }

        // Metode kompatibel untuk HTTP/IP lokal
        copyAccountFallback(cleanAccount);
    }

    function copyAccountFallback(account) {
        const textArea = document.createElement('textarea');

        textArea.value = account;

        textArea.setAttribute('readonly', '');
        textArea.style.position = 'fixed';
        textArea.style.top = '0';
        textArea.style.left = '-9999px';
        textArea.style.width = '1px';
        textArea.style.height = '1px';
        textArea.style.opacity = '0';
        textArea.style.pointerEvents = 'none';

        document.body.appendChild(textArea);

        textArea.focus();
        textArea.select();
        textArea.setSelectionRange(
            0,
            textArea.value.length
        );

        let copied = false;

        try {
            copied = document.execCommand('copy');
        } catch (error) {
            copied = false;
        }

        document.body.removeChild(textArea);

        if (copied) {
            
        } else {
            showAccountForManualCopy(account);
        }
    }

    function showAccountForManualCopy(account) {
        const modal = document.createElement('div');

        modal.style.position = 'fixed';
        modal.style.inset = '0';
        modal.style.zIndex = '99999';
        modal.style.display = 'flex';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
        modal.style.padding = '20px';
        modal.style.background = 'rgba(0, 0, 0, 0.55)';

        modal.innerHTML = `
            <div style="
                width: 100%;
                max-width: 380px;
                background: white;
                border-radius: 16px;
                padding: 24px;
                box-shadow: 0 20px 50px rgba(0,0,0,.25);
                text-align: center;
            ">
                <h3 style="
                    margin: 0 0 10px;
                    color: #481f2d;
                    font-size: 18px;
                    font-weight: 700;
                ">
                    Salin Nomor Rekening
                </h3>

                <p style="
                    margin: 0 0 14px;
                    color: #6b7280;
                    font-size: 13px;
                ">
                    Tekan lama nomor rekening, lalu pilih Salin.
                </p>

                <input
                    type="text"
                    value="${escapeHtml(account)}"
                    readonly
                    onclick="this.select()"
                    style="
                        width: 100%;
                        box-sizing: border-box;
                        border: 1px solid #d1d5db;
                        border-radius: 10px;
                        padding: 12px;
                        text-align: center;
                        font-size: 16px;
                        font-weight: 700;
                        color: #374151;
                        background: #f9fafb;
                    "
                >

                <button
                    type="button"
                    onclick="this.closest('[data-copy-modal]').remove()"
                    style="
                        margin-top: 16px;
                        width: 100%;
                        border: 0;
                        border-radius: 10px;
                        padding: 12px;
                        background: #631f2b;
                        color: white;
                        font-weight: 700;
                        cursor: pointer;
                    "
                >
                    Tutup
                </button>
            </div>
        `;

        modal.setAttribute('data-copy-modal', 'true');

        document.body.appendChild(modal);
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>

@endsection