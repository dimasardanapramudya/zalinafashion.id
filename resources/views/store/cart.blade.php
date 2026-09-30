blade
@extends('layouts.store')

@section('title', 'Keranjang — Zalina Fashion')

@section('content')

@php
    use Illuminate\Support\Facades\Storage;

    /*
    |--------------------------------------------------------------------------
    | DATA KERANJANG
    |--------------------------------------------------------------------------
    */

    $items = $cart?->items ?? collect();

    /*
    |--------------------------------------------------------------------------
    | TOTAL KERANJANG
    |--------------------------------------------------------------------------
    |
    | PRIORITAS HARGA:
    |
    | 1. Harga variant
    | 2. sale_price produk
    | 3. current_price produk
    | 4. price produk
    | 5. 0
    |
    | Dengan urutan ini, harga varian akan selalu digunakan jika tersedia.
    | Ini membuat total cart dan progress gratis ongkir lebih akurat.
    |
    */

    $totalItems = 0;
    $totalPrice = 0;
    $hasStockIssue = false;

    foreach ($items as $cartItem) {
        $product = $cartItem->product;
        $variant = $cartItem->variant;

        /*
        |--------------------------------------------------------------------------
        | HARGA ITEM
        |--------------------------------------------------------------------------
        |
        | Variant price WAJIB menjadi prioritas utama.
        |
        */

        $price = (float) (
            $variant?->price
            ?? $product?->sale_price
            ?? $product?->current_price
            ?? $product?->price
            ?? 0
        );

        /*
        |--------------------------------------------------------------------------
        | QUANTITY
        |--------------------------------------------------------------------------
        */

        $quantity = max(
            (int) $cartItem->quantity,
            1
        );

        /*
        |--------------------------------------------------------------------------
        | STOK
        |--------------------------------------------------------------------------
        |
        | Jika menggunakan variant:
        |     gunakan stok variant.
        |
        | Jika tidak menggunakan variant:
        |     gunakan stok produk.
        |
        */

        $itemStock = $variant
            ? (int) ($variant->stock ?? 0)
            : (int) ($product?->stock ?? 0);

        if (
            $itemStock <= 0 ||
            $quantity > $itemStock
        ) {
            $hasStockIssue = true;
        }

        /*
        |--------------------------------------------------------------------------
        | TOTAL
        |--------------------------------------------------------------------------
        */

        $totalItems += $quantity;

        $totalPrice += (
            $price * $quantity
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GRATIS ONGKIR
    |--------------------------------------------------------------------------
    |
    | Semua pengaturan berasal dari Setting.
    |
    | free_shipping_enabled
    |     1 = aktif
    |     0 = nonaktif
    |
    | free_shipping_minimum
    |     target minimal belanja.
    |
    | Default target = Rp250.000
    |
    */

    $freeShippingEnabled = \App\Models\Setting::value(
        'free_shipping_enabled',
        '0'
    ) === '1';

    $freeShippingMinimum = \App\Models\Setting::value(
        'free_shipping_minimum',
        250000
    );

    $freeShippingMinimum = (float) $freeShippingMinimum;

    if ($freeShippingMinimum <= 0) {
        $freeShippingMinimum = 250000;
    }

    /*
    |--------------------------------------------------------------------------
    | PROGRESS GRATIS ONGKIR
    |--------------------------------------------------------------------------
    |
    | Menggunakan totalPrice yang sudah memasukkan harga variant.
    |
    */

    $freeShippingProgress = min(
        ($totalPrice / $freeShippingMinimum) * 100,
        100
    );

    $freeShippingRemaining = max(
        $freeShippingMinimum - $totalPrice,
        0
    );

    $freeShippingAchieved = $freeShippingEnabled
        && $totalPrice >= $freeShippingMinimum;
@endphp

<div
    x-data="{
        activeDeleteModal: null,
        activeImage: null
    }"
    @keydown.escape.window="
        activeDeleteModal = null;
        activeImage = null;
    "
    class="relative min-h-screen overflow-hidden bg-[#fbf8f6] text-[#351b24]"
>

    {{-- ============================================================
         GLOBAL STYLE
    ============================================================ --}}

    <style>
        [x-cloak] {
            display: none !important;
        }

        .zalina-scrollbar::-webkit-scrollbar {
            width: 5px;
        }

        .zalina-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .zalina-scrollbar::-webkit-scrollbar-thumb {
            background: #d8c1c7;
            border-radius: 999px;
        }

        .cart-card {
            animation: cartCardEnter .45s ease both;
        }

        @keyframes cartCardEnter {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .cart-product-image {
            aspect-ratio: 4 / 5;
        }

        .cart-product-image img {
            object-position: center;
        }

        .cart-quantity-input::-webkit-outer-spin-button,
        .cart-quantity-input::-webkit-inner-spin-button {
            margin: 0;
            appearance: none;
        }

        .cart-quantity-input {
            appearance: textfield;
        }

        .shipping-progress-track {
            position: relative;
            overflow: hidden;
            height: 10px;
            border-radius: 999px;
            background: #eadde0;
        }

        .shipping-progress-fill {
            position: relative;
            height: 100%;
            min-width: 0;
            border-radius: 999px;
            background: linear-gradient(
                90deg,
                #631f2b 0%,
                #7c2d3a 55%,
                #b98a3d 100%
            );
            transition: width .7s cubic-bezier(.22, 1, .36, 1);
        }

        .shipping-progress-fill::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(
                110deg,
                transparent 20%,
                rgba(255,255,255,.38) 45%,
                transparent 70%
            );
            animation: shippingShine 2.5s infinite;
        }

        @keyframes shippingShine {
            from {
                transform: translateX(-100%);
            }

            to {
                transform: translateX(100%);
            }
        }

        .shipping-success {
            animation: shippingSuccess .45s ease both;
        }

        @keyframes shippingSuccess {
            from {
                opacity: 0;
                transform: scale(.98) translateY(4px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @media (min-width: 640px) {
            .cart-product-image {
                aspect-ratio: auto;
                height: 160px;
                width: 128px;
            }
        }

        @media (max-width: 639px) {
            .cart-card {
                border-radius: 1.5rem;
            }

            .cart-summary {
                position: static !important;
            }
        }
    </style>

    {{-- ============================================================
         DECORATIVE BACKGROUND
    ============================================================ --}}

    <div class="pointer-events-none absolute inset-0 overflow-hidden">

        <div
            class="absolute -left-40 top-20 h-96 w-96 rounded-full bg-[#631f2b]/10 blur-3xl"
        ></div>

        <div
            class="absolute -right-40 top-[34rem] h-[32rem] w-[32rem] rounded-full bg-[#b98a3d]/10 blur-3xl"
        ></div>

        <div
            class="absolute left-1/2 top-[70rem] h-96 w-96 -translate-x-1/2 rounded-full bg-[#631f2b]/5 blur-3xl"
        ></div>

    </div>

    <main
        class="relative mx-auto w-full max-w-7xl px-4 py-7 sm:px-6 sm:py-10 lg:px-8 lg:py-14"
    >

        {{-- ========================================================
             BREADCRUMB
        ======================================================== --}}

        <nav
            aria-label="Breadcrumb"
            class="mb-7 flex flex-wrap items-center gap-2 text-[10px] font-bold uppercase tracking-[0.2em] text-[#927780]"
        >

            <a
                href="{{ route('home') }}"
                class="transition hover:text-[#631f2b]"
            >
                Beranda
            </a>

            <span class="text-[#c8a6ad]">
                /
            </span>

            <span class="text-[#631f2b]">
                Keranjang
            </span>

        </nav>

        {{-- ========================================================
             HEADER
        ======================================================== --}}

        <section
            class="mb-9 flex flex-col gap-6 md:flex-row md:items-end md:justify-between"
        >

            <div class="min-w-0">

                <div
                    class="mb-4 inline-flex items-center gap-2 rounded-full border border-[#d8b66f]/40 bg-white/80 px-4 py-2 text-[9px] font-bold uppercase tracking-[0.24em] text-[#9b7540] shadow-sm backdrop-blur"
                >
                    <span
                        class="h-1.5 w-1.5 rounded-full bg-[#b98a3d]"
                    ></span>

                    Your Personal Collection
                </div>

                <h1
                    class="font-serif text-4xl leading-[1.05] text-[#481f2d] sm:text-5xl lg:text-6xl"
                >
                    Keranjang

                    <span class="block italic text-[#a66b7c]">
                        Pilihanmu
                    </span>
                </h1>

                <p
                    class="mt-4 max-w-xl text-sm leading-7 text-[#806b72] sm:text-base"
                >
                    Periksa kembali koleksi pilihanmu sebelum melanjutkan
                    ke proses pembayaran.
                </p>

            </div>

            {{-- TOTAL ITEM --}}

            <div
                class="flex w-full items-center gap-3 rounded-2xl border border-[#eadde0] bg-white/85 px-4 py-4 shadow-[0_12px_40px_rgba(72,31,45,0.06)] backdrop-blur sm:w-auto sm:px-5"
            >

                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#631f2b]/10 text-[#631f2b]"
                >

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 4h13m-9 4a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"
                        />
                    </svg>

                </div>

                <div>

                    <p
                        class="text-[9px] font-bold uppercase tracking-[0.2em] text-[#a58b92]"
                    >
                        Total Item
                    </p>

                    <p
                        class="mt-1 font-serif text-2xl text-[#481f2d]"
                    >
                        {{ $totalItems }}

                        <span class="text-sm text-[#a58b92]">
                            item
                        </span>
                    </p>

                </div>

            </div>

        </section>

        {{-- ========================================================
             EMPTY CART
        ======================================================== --}}

        @if ($items->isEmpty())

            <section
                class="relative overflow-hidden rounded-[2rem] border border-[#eadde0] bg-white px-6 py-16 text-center shadow-[0_20px_70px_rgba(72,31,45,0.07)] sm:px-12 sm:py-24"
            >

                <div
                    class="absolute -right-24 -top-24 h-64 w-64 rounded-full bg-[#b98a3d]/10 blur-3xl"
                ></div>

                <div
                    class="absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-[#631f2b]/10 blur-3xl"
                ></div>

                <div class="relative mx-auto max-w-xl">

                    <div
                        class="mx-auto mb-8 flex h-28 w-28 items-center justify-center rounded-full border border-[#e6cfcf] bg-[#fbf2f4] text-[#631f2b] shadow-inner"
                    >

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-12 w-12"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 4h13m-9 4a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"
                            />
                        </svg>

                    </div>

                    <p
                        class="mb-3 text-[10px] font-bold uppercase tracking-[0.3em] text-[#b28c4f]"
                    >
                        Your Collection Awaits
                    </p>

                    <h2
                        class="font-serif text-3xl text-[#481f2d] sm:text-4xl"
                    >
                        Keranjangmu masih kosong
                    </h2>

                    <p
                        class="mx-auto mt-5 max-w-md text-sm leading-7 text-[#806b72]"
                    >
                        Belum ada produk yang dipilih. Temukan koleksi hijab
                        dan fashion muslimah pilihan Zalina untuk melengkapi
                        penampilanmu.
                    </p>

                    <div
                        class="mt-9 flex flex-col justify-center gap-3 sm:flex-row"
                    >

                        <a
                            href="{{ route('shop') }}"
                            class="group inline-flex items-center justify-center gap-3 rounded-full bg-[#631f2b] px-7 py-4 text-xs font-bold uppercase tracking-[0.18em] text-white shadow-[0_12px_30px_rgba(99,31,43,0.25)] transition duration-300 hover:-translate-y-1 hover:bg-[#7c2d3a]"
                        >

                            Mulai Belanja

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 transition group-hover:translate-x-1"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5 12h14m-6-6l6 6-6 6"
                                />
                            </svg>

                        </a>

                        <a
                            href="{{ route('home') }}"
                            class="inline-flex items-center justify-center rounded-full border border-[#d8c1c7] bg-white px-7 py-4 text-xs font-bold uppercase tracking-[0.18em] text-[#631f2b] transition duration-300 hover:-translate-y-1 hover:border-[#631f2b] hover:bg-[#fbf2f4]"
                        >
                            Kembali ke Beranda
                        </a>

                    </div>

                </div>

            </section>

        @else

            {{-- ====================================================
                 FREE SHIPPING PROGRESS
            ==================================================== --}}

            @if ($freeShippingEnabled)

                <section
                    class="relative mb-8 overflow-hidden rounded-[2rem] border border-[#e7d8c2] bg-gradient-to-br from-white via-[#fffdfb] to-[#fbf3ee] p-5 shadow-[0_18px_55px_rgba(72,31,45,0.07)] sm:p-7"
                >

                    <div
                        class="pointer-events-none absolute -right-16 -top-16 h-44 w-44 rounded-full bg-[#b98a3d]/10 blur-3xl"
                    ></div>

                    <div
                        class="pointer-events-none absolute -bottom-20 -left-20 h-44 w-44 rounded-full bg-[#631f2b]/5 blur-3xl"
                    ></div>

                    <div class="relative">

                        <div
                            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                        >

                            <div class="flex items-start gap-4">

                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#631f2b] text-[#f6dfaa] shadow-[0_10px_25px_rgba(99,31,43,0.18)]"
                                >

                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M3 7h13v10H3zM16 10h3l2 3v4h-5zM5 17h.01M17 17h.01M5 7l1-3h7l1 3"
                                        />
                                    </svg>

                                </div>

                                <div class="min-w-0">

                                    @if ($freeShippingAchieved)

                                        <p
                                            class="text-[9px] font-bold uppercase tracking-[0.22em] text-[#b28c4f]"
                                        >
                                            Free Shipping Unlocked
                                        </p>

                                        <h2
                                            class="shipping-success mt-1 font-serif text-xl text-[#631f2b] sm:text-2xl"
                                        >
                                            🎉 Selamat! Kamu mendapatkan GRATIS ONGKIR
                                        </h2>

                                        <p
                                            class="mt-1 text-xs leading-5 text-[#806b72]"
                                        >
                                            Total belanjamu sudah mencapai batas
                                            gratis ongkir.
                                        </p>

                                    @else

                                        <p
                                            class="text-[9px] font-bold uppercase tracking-[0.22em] text-[#b28c4f]"
                                        >
                                            Keuntungan Belanja
                                        </p>

                                        <h2
                                            class="mt-1 font-serif text-xl text-[#481f2d] sm:text-2xl"
                                        >
                                            Sedikit lagi menuju GRATIS ONGKIR
                                        </h2>

                                        <p
                                            class="mt-1 text-xs leading-5 text-[#806b72]"
                                        >
                                            Belanja lagi
                                            <strong class="font-semibold text-[#631f2b]">
                                                Rp {{ number_format($freeShippingRemaining, 0, ',', '.') }}
                                            </strong>
                                            untuk mendapatkan gratis ongkir.
                                        </p>

                                    @endif

                                </div>

                            </div>

                            <div
                                class="shrink-0 rounded-2xl border border-[#eadde0] bg-white/80 px-4 py-3 sm:min-w-[155px] sm:text-right"
                            >

                                <p
                                    class="text-[9px] font-bold uppercase tracking-[0.18em] text-[#a58b92]"
                                >
                                    Target
                                </p>

                                <p
                                    class="mt-1 font-serif text-lg text-[#631f2b]"
                                >
                                    Rp {{ number_format($freeShippingMinimum, 0, ',', '.') }}
                                </p>

                            </div>

                        </div>

                        <div class="mt-6">

                            <div
                                class="mb-2 flex items-center justify-between gap-4"
                            >

                                <span
                                    class="text-[9px] font-bold uppercase tracking-[0.16em] text-[#927780]"
                                >
                                    Progress gratis ongkir
                                </span>

                                <span
                                    class="text-[10px] font-bold text-[#631f2b]"
                                >
                                    {{ number_format($freeShippingProgress, 0) }}%
                                </span>

                            </div>

                            <div
                                class="shipping-progress-track"
                                role="progressbar"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                aria-valuenow="{{ number_format($freeShippingProgress, 0) }}"
                                aria-label="Progress menuju gratis ongkir"
                            >

                                <div
                                    class="shipping-progress-fill"
                                    style="width: {{ $freeShippingProgress }}%;"
                                ></div>

                            </div>

                            <div
                                class="mt-3 flex flex-col gap-1 text-[10px] sm:flex-row sm:items-center sm:justify-between"
                            >

                                <span class="text-[#a58b92]">
                                    Belanja saat ini:
                                    <strong class="text-[#631f2b]">
                                        Rp {{ number_format($totalPrice, 0, ',', '.') }}
                                    </strong>
                                </span>

                                @if ($freeShippingAchieved)

                                    <span
                                        class="font-bold text-[#9b7540]"
                                    >
                                        ✓ Gratis ongkir aktif
                                    </span>

                                @else

                                    <span class="text-[#a58b92]">
                                        Target:
                                        <strong class="text-[#631f2b]">
                                            Rp {{ number_format($freeShippingMinimum, 0, ',', '.') }}
                                        </strong>
                                    </span>

                                @endif

                            </div>

                        </div>

                    </div>

                </section>

            @endif

            {{-- ====================================================
                 CART CONTENT
            ==================================================== --}}

            <div
                class="grid items-start gap-7 lg:grid-cols-[minmax(0,1fr)_380px] lg:gap-9"
            >

                {{-- PRODUCT LIST --}}

                <section class="min-w-0">

                    <div
                        class="mb-5 flex items-end justify-between gap-4"
                    >

                        <div>

                            <p
                                class="text-[9px] font-bold uppercase tracking-[0.25em] text-[#b28c4f]"
                            >
                                Selected Items
                            </p>

                            <h2
                                class="mt-1 font-serif text-2xl text-[#481f2d] sm:text-3xl"
                            >
                                Produk Pilihanmu
                            </h2>

                        </div>

                        <span
                            class="shrink-0 rounded-full border border-[#e6d5da] bg-white px-3 py-2 text-[10px] font-semibold text-[#631f2b] sm:px-4"
                        >
                            {{ $items->count() }} produk
                        </span>

                    </div>

                    <div class="space-y-5">

                        @foreach ($items as $item)

                            @php

                                $product = $item->product;
                                $variant = $item->variant;

                                /*
                                |--------------------------------------------------------------------------
                                | HARGA ITEM
                                |--------------------------------------------------------------------------
                                |
                                | Sama persis dengan kalkulasi total di atas.
                                | Variant price selalu menjadi prioritas utama.
                                |
                                */

                                $price = (float) (
                                    $variant?->price
                                    ?? $product?->sale_price
                                    ?? $product?->current_price
                                    ?? $product?->price
                                    ?? 0
                                );

                                $quantity = max(
                                    (int) $item->quantity,
                                    1
                                );

                                $lineTotal = (
                                    $price * $quantity
                                );

                                /*
                                |--------------------------------------------------------------------------
                                | STOK ITEM
                                |--------------------------------------------------------------------------
                                */

                                $availableStock = $variant
                                    ? (int) ($variant->stock ?? 0)
                                    : (int) ($product?->stock ?? 0);

                                $isOutOfStock = $availableStock <= 0;

                                $exceedsStock = !$isOutOfStock
                                    && $quantity > $availableStock;

                                /*
                                |--------------------------------------------------------------------------
                                | GAMBAR PRODUK
                                |--------------------------------------------------------------------------
                                */

                                $imagePath = null;
                                $imageUrl = null;

                                if (
                                    $variant &&
                                    filled($variant->image)
                                ) {
                                    $imagePath = trim(
                                        (string) $variant->image
                                    );
                                } elseif (
                                    $product &&
                                    filled($product->image)
                                ) {
                                    $imagePath = trim(
                                        (string) $product->image
                                    );
                                }

                                if ($imagePath) {

                                    if (
                                        str_starts_with(
                                            $imagePath,
                                            'http://'
                                        ) ||
                                        str_starts_with(
                                            $imagePath,
                                            'https://'
                                        )
                                    ) {

                                        $imageUrl = $imagePath;

                                    } else {

                                        $imagePath = ltrim(
                                            trim($imagePath),
                                            '/'
                                        );

                                        if (
                                            Storage::disk('public')->exists(
                                                $imagePath
                                            )
                                        ) {

                                            $imageUrl = asset(
                                                'storage/' . $imagePath
                                            );

                                        }

                                    }

                                }

                            @endphp

                            {{-- PRODUCT CARD --}}

                            <article
                                class="cart-card group relative overflow-hidden rounded-[1.75rem] border border-[#eadde0] bg-white p-4 shadow-[0_14px_45px_rgba(72,31,45,0.05)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_20px_60px_rgba(72,31,45,0.1)] sm:p-5"
                            >

                                <div
                                    class="flex min-w-0 flex-col gap-5 sm:flex-row"
                                >

                                    {{-- PRODUCT IMAGE --}}

                                    <a
                                        href="{{ $product ? route('product.show', $product) : '#' }}"
                                        class="cart-product-image group/image relative block w-full shrink-0 overflow-hidden rounded-2xl bg-[#f7eef0] sm:w-32"
                                    >

                                        @if ($imageUrl)

                                            <img
                                                src="{{ $imageUrl }}"
                                                alt="{{ $product?->name ?? 'Produk Zalina' }}"
                                                loading="lazy"
                                                class="h-full w-full object-cover transition duration-700 group-hover/image:scale-110"
                                                onerror="
                                                    this.style.display='none';
                                                    this.nextElementSibling.classList.remove('hidden');
                                                "
                                            >

                                            <div
                                                class="hidden h-full w-full items-center justify-center text-[#b78d99]"
                                            >

                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-12 w-12"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                    stroke-width="1"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M4 16l4-4a3 3 0 014 0l4 4m-8-8h.01M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                    />
                                                </svg>

                                            </div>

                                        @else

                                            <div
                                                class="flex h-full w-full items-center justify-center text-[#b78d99]"
                                            >

                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-12 w-12"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                    stroke-width="1"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M4 16l4-4a3 3 0 014 0l4 4m-8-8h.01M5 20h14a2 2 0 002-2V6a2 2 0 002 2v12a2 2 0 002 2z"
                                                    />
                                                </svg>

                                            </div>

                                        @endif

                                        <div
                                            class="absolute left-3 top-3 rounded-full bg-white/90 px-2.5 py-1 text-[9px] font-bold uppercase tracking-[0.15em] text-[#631f2b] shadow-sm backdrop-blur"
                                        >
                                            Zalina
                                        </div>

                                        <div
                                            class="pointer-events-none absolute inset-0 bg-gradient-to-t from-[#351b24]/20 via-transparent to-transparent opacity-0 transition duration-500 group-hover/image:opacity-100"
                                        ></div>

                                        @if ($isOutOfStock)

                                            <div
                                                class="absolute inset-0 flex items-center justify-center bg-[#351b24]/55"
                                            >

                                                <span
                                                    class="rounded-full bg-white px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.18em] text-[#631f2b] shadow-sm"
                                                >
                                                    Stok Habis
                                                </span>

                                            </div>

                                        @endif

                                    </a>

                                    {{-- PRODUCT INFORMATION --}}

                                    <div
                                        class="flex min-w-0 flex-1 flex-col justify-between"
                                    >

                                        <div>

                                            <div
                                                class="flex items-start justify-between gap-3"
                                            >

                                                <div class="min-w-0">

                                                    <p
                                                        class="mb-2 text-[9px] font-bold uppercase tracking-[0.2em] text-[#b28c4f]"
                                                    >
                                                        Zalina Collection
                                                    </p>

                                                    <a
                                                        href="{{ $product ? route('product.show', $product) : '#' }}"
                                                        class="line-clamp-2 font-serif text-xl leading-snug text-[#481f2d] transition hover:text-[#631f2b]"
                                                    >
                                                        {{ $product?->name ?? 'Produk tidak tersedia' }}
                                                    </a>

                                                </div>

                                                <button
                                                    type="button"
                                                    @click="activeDeleteModal = {{ $item->id }}"
                                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-[#eadde0] text-[#a58b92] transition hover:border-red-200 hover:bg-red-50 hover:text-red-500"
                                                    aria-label="Hapus produk"
                                                >

                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        class="h-4 w-4"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        stroke-width="1.5"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M6 7h12M10 11v6m4-6v6M9 7V4h6v3m-9 0l1 13h8l1-13"
                                                        />
                                                    </svg>

                                                </button>

                                            </div>

                                            @if ($variant)

                                                <div
                                                    class="mt-3 flex flex-wrap items-center gap-2"
                                                >

                                                    @if ($variant->name)

                                                        <span
                                                            class="rounded-full bg-[#fbf2f4] px-3 py-1 text-[10px] font-medium text-[#631f2b]"
                                                        >
                                                            {{ $variant->name }}
                                                        </span>

                                                    @endif

                                                    @if ($variant->color)

                                                        <span
                                                            class="rounded-full border border-[#eadde0] px-3 py-1 text-[10px] font-medium text-[#927780]"
                                                        >
                                                            {{ $variant->color }}
                                                        </span>

                                                    @endif

                                                </div>

                                            @endif

                                            @if ($isOutOfStock)

                                                <p
                                                    class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.1em] text-red-600"
                                                >
                                                    Stok Habis — hapus atau ganti varian untuk lanjut checkout
                                                </p>

                                            @elseif ($exceedsStock)

                                                <p
                                                    class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.1em] text-amber-700"
                                                >
                                                    Sisa stok {{ $availableStock }} — kurangi jumlah untuk lanjut checkout
                                                </p>

                                            @endif

                                        </div>

                                        {{-- PRICE AND QUANTITY --}}

                                        <div
                                            class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
                                        >

                                            <div>

                                                <p
                                                    class="text-[9px] font-bold uppercase tracking-[0.16em] text-[#a58b92]"
                                                >
                                                    Harga Satuan
                                                </p>

                                                <p
                                                    class="mt-1 font-serif text-lg text-[#631f2b]"
                                                >
                                                    Rp {{ number_format($price, 0, ',', '.') }}
                                                </p>

                                            </div>

                                            <form
                                                action="{{ route('cart.update', $item) }}"
                                                method="POST"
                                                class="flex w-full items-center justify-between gap-3 sm:w-auto sm:justify-end"
                                            >

                                                @csrf

                                                @method('PATCH')

                                                <div
                                                    class="flex items-center rounded-full border border-[#e6d5da] bg-[#fcf8f9] p-1"
                                                >

                                                    <button
                                                        type="button"
                                                        @disabled($isOutOfStock)
                                                        onclick="
                                                            const input = this.parentElement.querySelector('input');
                                                            input.value = Math.max(
                                                                1,
                                                                parseInt(input.value || 1) - 1
                                                            );
                                                        "
                                                        class="flex h-8 w-8 items-center justify-center rounded-full text-lg text-[#631f2b] transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent"
                                                        aria-label="Kurangi jumlah"
                                                    >
                                                        −
                                                    </button>

                                                    <input
                                                        type="number"
                                                        name="quantity"
                                                        value="{{ min($quantity, max($availableStock, 1)) }}"
                                                        min="1"
                                                        max="{{ max($availableStock, 1) }}"
                                                        @disabled($isOutOfStock)
                                                        class="cart-quantity-input h-8 w-10 border-0 bg-transparent p-0 text-center text-sm font-bold text-[#481f2d] outline-none focus:ring-0 disabled:opacity-40"
                                                        aria-label="Jumlah produk"
                                                    >

                                                    <button
                                                        type="button"
                                                        @disabled($isOutOfStock)
                                                        onclick="
                                                            const input = this.parentElement.querySelector('input');
                                                            input.value = Math.min(
                                                                {{ max($availableStock, 1) }},
                                                                parseInt(input.value || 1) + 1
                                                            );
                                                        "
                                                        class="flex h-8 w-8 items-center justify-center rounded-full text-lg text-[#631f2b] transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent"
                                                        aria-label="Tambah jumlah"
                                                    >
                                                        +
                                                    </button>

                                                </div>

                                                <button
                                                    type="submit"
                                                    @disabled($isOutOfStock)
                                                    class="rounded-full border border-[#d8c1c7] px-4 py-2 text-[10px] font-bold uppercase tracking-[0.12em] text-[#631f2b] transition hover:border-[#631f2b] hover:bg-[#fbf2f4] disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-[#d8c1c7] disabled:hover:bg-transparent"
                                                >
                                                    Update
                                                </button>

                                            </form>

                                        </div>

                                    </div>

                                </div>

                                {{-- LINE TOTAL --}}

                                <div
                                    class="mt-5 flex items-center justify-between gap-4 border-t border-[#f0e5e8] pt-4"
                                >

                                    <span
                                        class="text-[9px] font-bold uppercase tracking-[0.2em] text-[#a58b92]"
                                    >
                                        Total Produk
                                    </span>

                                    <span
                                        class="font-serif text-xl text-[#481f2d]"
                                    >
                                        Rp {{ number_format($lineTotal, 0, ',', '.') }}
                                    </span>

                                </div>

                            </article>

                            {{-- DELETE MODAL --}}

                            <template x-teleport="body">

                                <div
                                    x-show="activeDeleteModal === {{ $item->id }}"
                                    x-cloak
                                    x-transition.opacity.duration.300ms
                                    class="fixed inset-0 z-[9999] flex items-center justify-center bg-[#24131a]/65 px-4 py-6 backdrop-blur-md"
                                    role="dialog"
                                    aria-modal="true"
                                >

                                    <div
                                        x-show="activeDeleteModal === {{ $item->id }}"
                                        x-transition:enter="transition duration-300 ease-out"
                                        x-transition:enter-start="translate-y-8 scale-95 opacity-0"
                                        x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                                        x-transition:leave="transition duration-200 ease-in"
                                        x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                                        x-transition:leave-end="translate-y-8 scale-95 opacity-0"
                                        @click.outside="activeDeleteModal = null"
                                        class="zalina-scrollbar max-h-[90vh] w-full max-w-md overflow-y-auto rounded-[2rem] border border-[#eadde0] bg-[#fffdfb] shadow-[0_30px_100px_rgba(36,19,26,0.35)]"
                                    >

                                        <div
                                            class="relative overflow-hidden px-6 pb-7 pt-9 text-center sm:px-8"
                                        >

                                            <div
                                                class="absolute -right-16 -top-16 h-44 w-44 rounded-full bg-[#631f2b]/10 blur-3xl"
                                            ></div>

                                            <div
                                                class="absolute -bottom-20 -left-16 h-44 w-44 rounded-full bg-[#b98a3d]/10 blur-3xl"
                                            ></div>

                                            <button
                                                type="button"
                                                @click="activeDeleteModal = null"
                                                class="absolute right-5 top-5 flex h-9 w-9 items-center justify-center rounded-full border border-[#eadde0] text-[#a58b92] transition hover:bg-[#fbf2f4] hover:text-[#631f2b]"
                                                aria-label="Tutup modal"
                                            >

                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-4 w-4"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M6 6l12 12M18 6L6 18"
                                                    />
                                                </svg>

                                            </button>

                                            <div
                                                class="relative mx-auto flex h-20 w-20 items-center justify-center rounded-full border border-[#e6cfcf] bg-[#fbf2f4] text-[#631f2b] shadow-inner"
                                            >

                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-9 w-9"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                    stroke-width="1.3"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4h6v3m-9 0h12"
                                                    />
                                                </svg>

                                            </div>

                                            <p
                                                class="relative mt-5 text-[10px] font-bold uppercase tracking-[0.28em] text-[#b28c4f]"
                                            >
                                                Konfirmasi Pilihan
                                            </p>

                                            <h3
                                                class="relative mt-2 font-serif text-3xl text-[#481f2d]"
                                            >
                                                Hapus produk?
                                            </h3>

                                            <p
                                                class="relative mx-auto mt-3 max-w-sm text-sm leading-6 text-[#806b72]"
                                            >
                                                Apakah kamu yakin ingin menghapus
                                                produk ini dari keranjang?

                                                <span
                                                    class="mt-2 block font-semibold text-[#631f2b]"
                                                >
                                                    {{ $product?->name ?? 'Produk Zalina' }}
                                                </span>
                                            </p>

                                        </div>

                                        <div
                                            class="flex flex-col gap-3 border-t border-[#eadde0] bg-white px-6 py-5 sm:flex-row sm:px-8"
                                        >

                                            <button
                                                type="button"
                                                @click="activeDeleteModal = null"
                                                class="flex-1 rounded-full border border-[#d8c1c7] px-5 py-3.5 text-xs font-bold uppercase tracking-[0.16em] text-[#631f2b] transition hover:border-[#631f2b] hover:bg-[#fbf2f4]"
                                            >
                                                Batal
                                            </button>

                                            <form
                                                action="{{ route('cart.destroy', $item) }}"
                                                method="POST"
                                                class="flex-1"
                                            >

                                                @csrf

                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="flex w-full items-center justify-center gap-2 rounded-full bg-[#631f2b] px-5 py-3.5 text-xs font-bold uppercase tracking-[0.16em] text-white shadow-[0_10px_25px_rgba(99,31,43,0.22)] transition hover:bg-[#7c2d3a]"
                                                >
                                                    Ya, Hapus
                                                </button>

                                            </form>

                                        </div>

                                    </div>

                                </div>

                            </template>

                        @endforeach

                    </div>

                    {{-- CONTINUE SHOPPING --}}

                    <div class="pt-6">

                        <a
                            href="{{ route('shop') }}"
                            class="group inline-flex items-center gap-3 text-xs font-bold uppercase tracking-[0.18em] text-[#631f2b]"
                        >

                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-full border border-[#d8c1c7] transition group-hover:border-[#631f2b] group-hover:bg-[#fbf2f4]"
                            >

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 transition group-hover:-translate-x-1"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M19 12H5m7 6l-6-6 6-6"
                                    />
                                </svg>

                            </span>

                            Lanjutkan Belanja

                        </a>

                    </div>

                </section>

                {{-- ========================================================
                     ORDER SUMMARY
                ======================================================== --}}

                <aside
                    class="cart-summary lg:sticky lg:top-8"
                >

                    <div
                        class="relative overflow-hidden rounded-[2rem] border border-[#eadde0] bg-white p-6 shadow-[0_18px_60px_rgba(72,31,45,0.08)] sm:p-8"
                    >

                        <div
                            class="absolute -right-20 -top-20 h-52 w-52 rounded-full bg-[#b98a3d]/10 blur-3xl"
                        ></div>

                        <div class="relative">

                            <div class="mb-7">

                                <p
                                    class="text-[10px] font-bold uppercase tracking-[0.25em] text-[#b28c4f]"
                                >
                                    Order Overview
                                </p>

                                <h2
                                    class="mt-2 font-serif text-3xl text-[#481f2d]"
                                >
                                    Ringkasan Pesanan
                                </h2>

                            </div>

                            <div
                                class="space-y-4 border-b border-[#eadde0] pb-6"
                            >

                                <div
                                    class="flex items-center justify-between gap-4 text-sm"
                                >

                                    <span class="text-[#806b72]">
                                        Total Item
                                    </span>

                                    <span class="font-semibold text-[#481f2d]">
                                        {{ $totalItems }} pcs
                                    </span>

                                </div>

                                <div
                                    class="flex items-center justify-between gap-4 text-sm"
                                >

                                    <span class="text-[#806b72]">
                                        Total Harga Item
                                    </span>

                                    <span class="font-semibold text-[#481f2d]">
                                        Rp {{ number_format($totalPrice, 0, ',', '.') }}
                                    </span>

                                </div>

                                @if ($freeShippingEnabled)

                                    <div
                                        class="rounded-2xl bg-[#fbf2f4] p-4"
                                    >

                                        <div
                                            class="flex items-start gap-3"
                                        >

                                            <div
                                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white text-[#631f2b] shadow-sm"
                                            >

                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-4 w-4"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M3 7h13v10H3zM16 10h3l2 3v4h-5zM5 17h.01M17 17h.01"
                                                    />
                                                </svg>

                                            </div>

                                            <div class="min-w-0">

                                                @if ($freeShippingAchieved)

                                                    <p
                                                        class="text-[9px] font-bold uppercase tracking-[0.14em] text-[#9b7540]"
                                                    >
                                                        Gratis Ongkir
                                                    </p>

                                                    <p
                                                        class="mt-1 text-xs font-semibold leading-5 text-[#631f2b]"
                                                    >
                                                        ✓ Gratis ongkir sudah kamu dapatkan.
                                                    </p>

                                                @else

                                                    <p
                                                        class="text-[9px] font-bold uppercase tracking-[0.14em] text-[#9b7540]"
                                                    >
                                                        Menuju Gratis Ongkir
                                                    </p>

                                                    <p
                                                        class="mt-1 text-xs leading-5 text-[#806b72]"
                                                    >
                                                        Kurang
                                                        <strong class="font-semibold text-[#631f2b]">
                                                            Rp {{ number_format($freeShippingRemaining, 0, ',', '.') }}
                                                        </strong>
                                                        lagi.
                                                    </p>

                                                @endif

                                            </div>

                                        </div>

                                    </div>

                                @endif

                            </div>

                            {{-- TOTAL PAYMENT --}}

                            <div
                                class="flex items-end justify-between gap-4 py-6"
                            >

                                <div>

                                    <p
                                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#a58b92]"
                                    >
                                        Total Pembayaran
                                    </p>

                                    <p
                                        class="mt-2 font-serif text-3xl text-[#631f2b]"
                                    >
                                        Rp {{ number_format($totalPrice, 0, ',', '.') }}
                                    </p>

                                </div>

                            </div>

                            {{-- CHECKOUT --}}

                            @if ($hasStockIssue)

                                <div
                                    class="flex w-full cursor-not-allowed items-center justify-between rounded-full bg-[#d8c1c7] px-6 py-4 text-xs font-bold uppercase tracking-[0.18em] text-white/80"
                                >

                                    <span>
                                        Lanjut Checkout
                                    </span>

                                    <span
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-white/15"
                                    >

                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M5 12h14m-6-6l6 6-6 6"
                                            />
                                        </svg>

                                    </span>

                                </div>

                                <p
                                    class="mt-3 text-center text-[11px] font-medium text-red-600"
                                >
                                    Ada produk stok habis / melebihi stok tersedia.
                                    Sesuaikan jumlahnya sebelum lanjut checkout.
                                </p>

                            @else

                                <a
                                    href="{{ route('checkout.show') }}"
                                    class="group flex w-full items-center justify-between rounded-full bg-[#631f2b] px-6 py-4 text-xs font-bold uppercase tracking-[0.18em] text-white shadow-[0_12px_30px_rgba(99,31,43,0.25)] transition duration-300 hover:-translate-y-1 hover:bg-[#7c2d3a]"
                                >

                                    <span>
                                        Lanjut Checkout
                                    </span>

                                    <span
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-white/15 transition group-hover:translate-x-1"
                                    >

                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M5 12h14m-6-6l6 6-6 6"
                                            />
                                        </svg>

                                    </span>

                                </a>

                            @endif

                            {{-- SECURITY NOTICE --}}

                            <div
                                class="mt-6 flex items-start gap-3 rounded-2xl bg-[#fcf8f9] p-4"
                            >

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="mt-0.5 h-5 w-5 shrink-0 text-[#b28c4f]"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2V9a8 8 0 10-16 0v10a2 2 0 002 2z"
                                    />
                                </svg>

                                <p
                                    class="text-[11px] leading-5 text-[#927780]"
                                >
                                    Data pesananmu diproses dengan aman.
                                    Pembayaran akan diverifikasi oleh admin
                                    sebelum pesanan dikirim.
                                </p>

                            </div>

                            {{-- TRUST FEATURES --}}

                            <div
                                class="mt-7 grid grid-cols-3 gap-3 border-t border-[#eadde0] pt-6"
                            >

                                <div class="text-center">

                                    <div
                                        class="mx-auto flex h-9 w-9 items-center justify-center rounded-full bg-[#fbf2f4] text-[#631f2b]"
                                    >

                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M12 3l7 4v5c0 4.5-3 7-7 9-4-2-7-4.5-7-9V7l7-4z"
                                            />
                                        </svg>

                                    </div>

                                    <p
                                        class="mt-2 text-[9px] font-bold uppercase tracking-[0.08em] text-[#927780]"
                                    >
                                        Aman
                                    </p>

                                </div>

                                <div class="text-center">

                                    <div
                                        class="mx-auto flex h-9 w-9 items-center justify-center rounded-full bg-[#fbf2f4] text-[#631f2b]"
                                    >

                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M3 7h13v10H3zM16 10h3l2 3v4h-5z"
                                            />
                                        </svg>

                                    </div>

                                    <p
                                        class="mt-2 text-[9px] font-bold uppercase tracking-[0.08em] text-[#927780]"
                                    >
                                        Terpercaya
                                    </p>

                                </div>

                                <div class="text-center">

                                    <div
                                        class="mx-auto flex h-9 w-9 items-center justify-center rounded-full bg-[#fbf2f4] text-[#631f2b]"
                                    >

                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M5 13l4 4L19 7"
                                            />
                                        </svg>

                                    </div>

                                    <p
                                        class="mt-2 text-[9px] font-bold uppercase tracking-[0.08em] text-[#927780]"
                                    >
                                        Berkualitas
                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>

                </aside>

            </div>

        @endif

    </main>

</div>

@endsection

