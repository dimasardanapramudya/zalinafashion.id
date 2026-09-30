@extends('layouts.store')

@section('title', 'Koleksi — Zalina Fashion')

@section('content')

<style>
    /* =========================================================================
       WISHLIST / LOVE BUTTON
       ========================================================================= */
    .shop-product-love-row {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        min-height: 38px;
        margin-top: 10px;
        margin-bottom: 3px;
    }

    .product-love-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 34px;
        padding: 6px 11px;
        border: 1px solid rgba(89, 28, 45, .18);
        border-radius: 999px;
        background: rgba(255, 250, 248, .9);
        color: #8b3b52;
        cursor: pointer;
        transition:
            transform .2s ease,
            background .2s ease,
            color .2s ease,
            box-shadow .2s ease;
    }

    .product-love-button:hover {
        transform: translateY(-2px);
        background: #fbeef1;
        box-shadow: 0 6px 16px rgba(89, 28, 45, .14);
    }

    .product-love-button.is-loved {
        background: #591c2d;
        color: #fff2ec;
        border-color: #591c2d;
        box-shadow: 0 5px 16px rgba(89, 28, 45, .22);
    }

    .love-icon {
        font-size: 20px;
        line-height: 1;
    }

    .love-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .04em;
    }

    .product-love-button.is-loved .love-icon {
        animation: love-button-pop .35s ease;
    }

    .double-tap-love {
        position: absolute;
        inset: 0;
        z-index: 15;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        opacity: 0;
    }

    .double-love-heart {
        position: absolute;
        color: #f2b6c3;
        font-size: 82px;
        line-height: 1;
        text-shadow:
            0 3px 0 #fff,
            0 8px 22px rgba(89, 28, 45, .3);
        opacity: 0;
    }

    .double-tap-love.show .heart-one {
        animation: double-love-first .42s ease-out forwards;
    }

    .double-tap-love.show .heart-two {
        animation: double-love-second .42s ease-out .18s forwards;
    }

    @keyframes double-love-first {
        0% {
            opacity: 0;
            transform: scale(.25) rotate(-12deg);
        }

        25% {
            opacity: 1;
            transform: scale(1.15) rotate(-8deg);
        }

        65% {
            opacity: 1;
            transform: scale(1) rotate(-8deg);
        }

        100% {
            opacity: 0;
            transform: scale(1.35) rotate(-8deg);
        }
    }

    @keyframes double-love-second {
        0% {
            opacity: 0;
            transform: scale(.25) rotate(12deg);
        }

        25% {
            opacity: 1;
            transform: scale(1.15) rotate(8deg);
        }

        65% {
            opacity: 1;
            transform: scale(1) rotate(8deg);
        }

        100% {
            opacity: 0;
            transform: scale(1.35) rotate(8deg);
        }
    }

    @keyframes love-button-pop {
        0%, 100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.25);
        }
    }
</style>

@php
    // Dipakai untuk badge "X filter aktif" di kartu filter.
    $activeFilters = collect([
        request('search'),
        request('min_price'),
        request('max_price'),
        request('sort') && request('sort') !== 'latest' ? request('sort') : null,
    ])->filter()->count();

    /*
    |--------------------------------------------------------------------------
    | STOK GENERAL DI KARTU PRODUK (GAYA SHOPEE)
    |--------------------------------------------------------------------------
    |
    | Kartu di halaman shop hanya menampilkan STOK GENERAL: total stok semua
    | varian aktif (atau stok produk kalau tidak punya varian). Rincian per
    | varian ada di halaman detail produk.
    |
    | Perhitungannya satu sumber: accessor Product::available_stock,
    | is_sold_out, dan stock_label (lihat Product.php), sehingga angkanya
    | PASTI sama dengan halaman produk. $stockLookup dipertahankan supaya
    | partial store.partials.shop-results yang sudah ada tetap jalan.
    |
    | Accessor tidak bergantung pada closure di file ini, jadi aman dipakai
    | langsung di partial (termasuk saat partial dirender sendirian lewat
    | AJAX). Tambahkan ->with('variants') pada query produk shop di
    | controller supaya tidak terjadi N+1 query.
    |
    */

    $stockLookup = fn ($product) => (int) $product->available_stock;
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Inter:wght@400;500;600;700&display=swap');

    .zalina-shop {
        --zs-ink: #2c1119;
        --zs-maroon: #591c2d;
        --zs-maroon-deep: #34101c;
        --zs-gold: #a9803f;
        --zs-gold-soft: #cfae76;
        --zs-paper: #faf6f1;
        --zs-surface: #ffffff;
        --zs-line: #e8dcd5;
        --zs-muted: #8a7169;
        --zs-muted-soft: #a99691;
        --zs-radius: 16px;
        --zs-radius-sm: 10px;
        --zs-serif: 'Cormorant Garamond', 'Playfair Display', Georgia, serif;
        --zs-sans: 'Inter', 'Helvetica Neue', Arial, sans-serif;

        font-family: var(--zs-sans);
        background: var(--zs-paper);
        color: var(--zs-ink);
    }

    .zalina-shop *,
    .zalina-shop *::before,
    .zalina-shop *::after {
        box-sizing: border-box;
    }

    .zalina-shop [x-cloak] {
        display: none !important;
    }

    .shop-container {
        width: min(1240px, calc(100% - 40px));
        margin: 0 auto;
    }

    /* =========================================================================
       MAIN LAYOUT
       ========================================================================= */

    .shop-main {
        padding: 28px 0 70px;
    }

    .shop-layout {
        display: grid;
        grid-template-columns: 280px minmax(0, 1fr);
        gap: 40px;
        align-items: start;
    }

    .shop-sidebar {
        position: sticky;
        top: 95px;
    }

    .shop-filter-card {
        border: 1px solid var(--zs-line);
        border-radius: var(--zs-radius);
        background: var(--zs-surface);
    }

    .shop-filter-head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
        padding: 24px 24px 18px;
        border-bottom: 1px solid var(--zs-line);
    }

    .shop-filter-head h2 {
        margin: 0;
        color: var(--zs-maroon);
        font-family: var(--zs-serif);
        font-size: 24px;
        font-weight: 500;
    }

    .shop-filter-count {
        color: var(--zs-gold);
        font-size: 11.5px;
        font-weight: 600;
        white-space: nowrap;
    }

    .shop-filter-section {
        padding: 22px 24px;
        border-bottom: 1px solid var(--zs-line);
    }

    .shop-filter-section-flush {
        padding-top: 4px;
        border-bottom: 0;
    }

    .shop-filter-label {
        display: block;
        margin-bottom: 11px;
        color: var(--zs-ink);
        font-size: 13px;
        font-weight: 600;
    }

    .shop-search-row {
        position: relative;
    }

    .shop-search-input {
        width: 100%;
        padding: 12px 42px 12px 14px;
        border: 1px solid var(--zs-line);
        border-radius: var(--zs-radius-sm);
        outline: none;
        background: var(--zs-paper);
        color: var(--zs-ink);
        font-family: var(--zs-sans);
        font-size: 13px;
        transition: border-color .2s ease, background .2s ease;
    }

    .shop-search-input:focus {
        border-color: var(--zs-maroon);
        background: var(--zs-surface);
    }

    .shop-search-submit {
        position: absolute;
        right: 5px;
        top: 5px;
        bottom: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        border: 0;
        border-radius: 8px;
        background: transparent;
        color: var(--zs-muted);
        cursor: pointer;
        transition: color .2s ease, background .2s ease;
    }

    .shop-search-submit:hover {
        background: var(--zs-paper);
        color: var(--zs-maroon);
    }

    .shop-search-submit svg {
        width: 16px;
        height: 16px;
    }

    .shop-price-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 11px;
    }

    .shop-price-heading .shop-filter-label {
        margin-bottom: 0;
    }

    .shop-price-reset {
        border: 0;
        background: none;
        padding: 0;
        color: var(--zs-maroon);
        font-size: 11.5px;
        font-weight: 700;
        text-decoration: underline;
        text-underline-offset: 2px;
        cursor: pointer;
    }

    .shop-price-reset:hover {
        color: var(--zs-maroon-deep);
    }

    .shop-price-readout {
        margin: 0 0 22px;
        color: var(--zs-maroon);
        font-family: var(--zs-serif);
        font-size: 20px;
        font-weight: 600;
    }

    .shop-price-sep {
        margin: 0 4px;
        color: var(--zs-muted-soft);
        font-family: var(--zs-sans);
        font-size: 14px;
    }

    .shop-range-area {
        position: relative;
        height: 34px;
    }

    .shop-range-track,
    .shop-range-active {
        position: absolute;
        top: 14px;
        height: 4px;
        border-radius: 999px;
    }

    .shop-range-track {
        right: 0;
        left: 0;
        background: var(--zs-line);
    }

    .shop-range-active {
        background: linear-gradient(
            90deg,
            var(--zs-maroon),
            var(--zs-gold)
        );
    }

    .shop-range-input {
        position: absolute;
        top: 4px;
        left: 0;
        z-index: 5;
        width: 100%;
        height: 24px;
        appearance: none;
        -webkit-appearance: none;
        background: transparent;
        pointer-events: none;
        outline: none;
    }

    .shop-range-input::-webkit-slider-thumb {
        width: 18px;
        height: 18px;
        appearance: none;
        -webkit-appearance: none;
        border: 3px solid var(--zs-maroon);
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 3px 10px rgba(89, 28, 45, .25);
        pointer-events: auto;
        cursor: grab;
    }

    .shop-range-input::-moz-range-thumb {
        width: 12px;
        height: 12px;
        border: 3px solid var(--zs-maroon);
        border-radius: 50%;
        background: #fff;
        pointer-events: auto;
        cursor: grab;
    }

    .shop-range-limits {
        display: flex;
        justify-content: space-between;
        margin-top: 4px;
        color: var(--zs-muted-soft);
        font-size: 10.5px;
    }

    .shop-clear-link {
        display: inline-block;
        color: var(--zs-muted);
        font-size: 12px;
        font-weight: 600;
        text-decoration: underline;
        text-underline-offset: 2px;
    }

    .shop-clear-link:hover {
        color: var(--zs-maroon);
    }

    .shop-sidebar-note {
        margin-top: 18px;
        padding: 22px;
        border-radius: var(--zs-radius);
        background: linear-gradient(
            155deg,
            var(--zs-maroon-deep),
            var(--zs-maroon)
        );
        color: rgba(253, 246, 238, .9);
    }

    .shop-sidebar-note-kicker {
        margin: 0;
        color: var(--zs-gold-soft);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .06em;
    }

    .shop-sidebar-note-text {
        margin: 10px 0 0;
        color: rgba(253, 246, 238, .7);
        font-size: 12.5px;
        line-height: 1.8;
    }

    /* =========================================================================
       TOOLBAR
       ========================================================================= */

    .shop-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 20px;
    }

    .shop-toolbar-count {
        margin: 0;
        color: var(--zs-muted);
        font-size: 13.5px;
    }

    .shop-toolbar-count strong {
        color: var(--zs-maroon);
        font-weight: 600;
    }

    .shop-sort-form {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .shop-sort-label {
        color: var(--zs-muted);
        font-size: 12.5px;
    }

    .shop-sort-select {
        padding: 10px 32px 10px 13px;
        border: 1px solid var(--zs-line);
        border-radius: var(--zs-radius-sm);
        outline: none;
        background: var(--zs-surface);
        color: var(--zs-maroon);
        font-size: 12.5px;
        cursor: pointer;
    }

    .shop-chips {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin-bottom: 24px;
    }

    .shop-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border: 1px solid var(--zs-line);
        border-radius: var(--zs-radius-sm);
        background: var(--zs-surface);
        color: var(--zs-maroon);
        font-size: 12px;
        text-decoration: none;
    }

    .shop-chip:hover {
        border-color: var(--zs-maroon);
    }

    .shop-chip span {
        color: var(--zs-muted-soft);
        font-size: 13px;
    }

    /* =========================================================================
       LOADING STATE
       ========================================================================= */

    #shop-results.is-loading {
        position: relative;
        opacity: .55;
        pointer-events: none;
        transition: opacity .15s ease;
    }

    /* =========================================================================
       PRODUCT GRID
       ========================================================================= */

    .shop-products-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 26px 22px;
    }

    .shop-product-card {
        position: relative;
        display: block;
        color: inherit;
        text-decoration: none;
    }

    .shop-product-image {
        position: relative;
        overflow: hidden;
        aspect-ratio: 3 / 4;
        border-radius: var(--zs-radius);
        background: linear-gradient(150deg, #f4e9ec, #fbf6ee);
    }

    .shop-product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition:
            transform .6s cubic-bezier(.2, .8, .2, 1),
            filter .3s ease;
    }

    .shop-product-card:hover .shop-product-image img {
        transform: scale(1.05);
    }

    .shop-product-card.is-soldout .shop-product-image img {
        filter: grayscale(55%) brightness(.92);
    }

    .shop-product-card.is-soldout:hover .shop-product-image img {
        transform: none;
    }

    .shop-image-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        text-align: center;
    }

    .shop-fallback-circle {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 84px;
        height: 84px;
        margin: 0 auto;
        border: 1px solid #e5cfd5;
        border-radius: 50%;
        color: #cf9fac;
        font-family: var(--zs-serif);
        font-size: 48px;
    }

    .shop-fallback-caption {
        margin-top: 13px;
        color: #b88e9c;
        font-size: 10px;
        letter-spacing: .1em;
    }

    .shop-badge {
        position: absolute;
        top: 12px;
        padding: 6px 11px;
        border-radius: var(--zs-radius-sm);
        font-size: 10.5px;
        font-weight: 700;
    }

    .shop-badge-sale {
        left: 12px;
        background: var(--zs-maroon);
        color: #fdf1f4;
    }

    .shop-badge-featured {
        right: 12px;
        background: var(--zs-gold-soft);
        color: #3c1823;
    }

    .shop-soldout-plate {
        position: absolute;
        right: 0;
        bottom: 0;
        left: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 12px 10px;
        background: rgba(44, 17, 25, .78);
        backdrop-filter: blur(2px);
    }

    .shop-soldout-plate span {
        color: #fdf6ee;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: .08em;
    }

    .shop-product-info {
        padding: 16px 2px 0;
    }

    .shop-product-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 8px;
    }

    .shop-product-category {
        overflow: hidden;
        color: var(--zs-muted-soft);
        font-size: 10.5px;
        font-weight: 600;
        letter-spacing: .04em;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .shop-low-stock {
        color: var(--zs-gold);
        font-size: 10.5px;
        font-weight: 700;
        white-space: nowrap;
    }

    .shop-stock-badge {
        color: #198754;
        font-size: 10.5px;
        font-weight: 700;
        white-space: nowrap;
    }

    .shop-stock-badge.is-empty {
        color: #c2414d;
    }

    .shop-product-name {
        display: -webkit-box;
        overflow: hidden;
        min-height: 50px;
        color: var(--zs-ink);
        font-family: var(--zs-serif);
        font-size: 19px;
        font-weight: 600;
        line-height: 1.28;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .shop-product-bottom {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 10px;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid var(--zs-line);
    }

    .shop-product-price {
        color: var(--zs-maroon);
        font-family: var(--zs-serif);
        font-size: 19px;
        font-weight: 600;
    }

    .shop-product-old-price {
        margin-top: 3px;
        color: var(--zs-muted-soft);
        font-size: 11px;
        text-decoration: line-through;
    }

    .shop-product-cta {
        color: var(--zs-muted);
        font-size: 11.5px;
        font-weight: 600;
        text-decoration: underline;
        text-underline-offset: 3px;
        text-decoration-color: transparent;
        transition:
            color .2s ease,
            text-decoration-color .2s ease;
    }

    .shop-product-card:hover .shop-product-cta {
        color: var(--zs-maroon);
        text-decoration-color: var(--zs-maroon);
    }

    .shop-product-card.is-soldout .shop-product-cta {
        color: var(--zs-muted-soft);
    }

    /* =========================================================================
       EMPTY STATE
       ========================================================================= */

    .shop-empty {
        padding: 70px 25px;
        border: 1px solid var(--zs-line);
        border-radius: var(--zs-radius);
        background: var(--zs-surface);
        text-align: center;
    }

    .shop-empty h3 {
        margin: 0;
        color: var(--zs-maroon);
        font-family: var(--zs-serif);
        font-size: 27px;
        font-weight: 500;
    }

    .shop-empty p {
        max-width: 420px;
        margin: 12px auto 0;
        color: var(--zs-muted);
        font-size: 13px;
        line-height: 1.8;
    }

    .shop-empty a {
        display: inline-block;
        margin-top: 22px;
        padding: 12px 22px;
        border-radius: var(--zs-radius-sm);
        background: var(--zs-maroon);
        color: #fdf1f4;
        font-size: 12.5px;
        font-weight: 700;
        text-decoration: none;
    }

    /* =========================================================================
       RESPONSIVE
       ========================================================================= */

    @media (max-width: 1100px) {
        .shop-products-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .shop-layout {
            grid-template-columns: 260px minmax(0, 1fr);
        }
    }

    @media (max-width: 820px) {
        .shop-layout {
            grid-template-columns: 1fr;
        }

        .shop-sidebar {
            position: static;
        }

        .shop-sidebar-note {
            display: none;
        }
    }

    @media (max-width: 620px) {
        .shop-container {
            width: min(100% - 28px, 1240px);
        }

        .shop-main {
            padding: 20px 0 55px;
        }

        .shop-toolbar {
            flex-direction: column;
            align-items: flex-start;
            gap: 14px;
        }

        .shop-sort-form {
            width: 100%;
            justify-content: space-between;
        }

        .shop-products-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px 12px;
        }

        .shop-product-name {
            min-height: 40px;
            font-size: 15.5px;
        }

        .shop-product-price {
            font-size: 16px;
        }

        .shop-product-info {
            padding-top: 12px;
        }

        .shop-product-bottom {
            gap: 6px;
            margin-top: 10px;
            padding-top: 10px;
        }

        .shop-product-cta {
            font-size: 10px;
        }

        .shop-filter-head {
            padding: 20px 18px 16px;
        }

        .shop-filter-section {
            padding: 18px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .zalina-shop *,
        .zalina-shop *::before,
        .zalina-shop *::after {
            animation: none !important;
            transition: none !important;
        }
    }
</style>

<div
    class="zalina-shop"
    x-data="{
        minPrice: {{ (int) $selectedMinPrice }},
        maxPrice: {{ (int) $selectedMaxPrice }},
        minLimit: {{ (int) $globalMinPrice }},
        maxLimit: {{ (int) $globalMaxPrice }},
        submitTimer: null,

        formatPrice(value) {
            return new Intl.NumberFormat('id-ID').format(
                Math.round(Number(value) || 0)
            );
        },

        minPercent() {
            if (this.maxLimit <= this.minLimit) return 0;

            return (
                (this.minPrice - this.minLimit) /
                (this.maxLimit - this.minLimit)
            ) * 100;
        },

        maxPercent() {
            if (this.maxLimit <= this.minLimit) return 100;

            return (
                (this.maxPrice - this.minLimit) /
                (this.maxLimit - this.minLimit)
            ) * 100;
        },

        isPriceDefault() {
            return (
                this.minPrice === this.minLimit &&
                this.maxPrice === this.maxLimit
            );
        },

        // Slider digeser -> filter otomatis diterapkan setelah jeda singkat,
        // supaya request tidak dikirim di setiap piksel geseran.
        autoSubmit(delay = 550) {
            clearTimeout(this.submitTimer);

            this.submitTimer = setTimeout(() => {
                this.$refs.filterForm.requestSubmit();
            }, delay);
        },

        resetPrice() {
            this.minPrice = this.minLimit;
            this.maxPrice = this.maxLimit;
            this.autoSubmit(0);
        },

        // Kirim form filter (pencarian, harga, urutan) lewat AJAX,
        // supaya hasil produk berubah tanpa reload halaman.
        submitFilterForm(event) {
            const form = event.target;
            const params = new URLSearchParams(new FormData(form));

            [...params.keys()].forEach((key) => {
                if (params.get(key) === '') {
                    params.delete(key);
                }
            });

            const query = params.toString();
            const url = form.action + (query ? ('?' + query) : '');

            this.loadResults(url);
        },

        // Ambil ulang fragment hasil produk (#shop-results) via fetch,
        // lalu tukar isinya tanpa reload seluruh halaman.
        async loadResults(url, pushState = true) {
            const resultsEl = document.getElementById('shop-results');

            if (!resultsEl) return;

            resultsEl.classList.add('is-loading');

            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error('Gagal memuat produk.');
                }

                resultsEl.innerHTML = await response.text();

                if (pushState) {
                    history.pushState(
                        { shopResultsUrl: url },
                        '',
                        url
                    );
                }

                window.zalinaInitShopResults?.(resultsEl);
            } catch (error) {
                console.error('Shop filter error:', error);

                // Fallback: kalau AJAX gagal, tetap navigasi normal
                // supaya user tidak terjebak tanpa hasil.
                window.location.href = url;
            } finally {
                resultsEl.classList.remove('is-loading');
            }
        }
    }"
>

    {{-- MAIN SHOP --}}
    <section class="shop-main">
        <div class="shop-container">
            <div class="shop-layout">

                {{-- SIDEBAR --}}
                <aside class="shop-sidebar">

                    <form
                        action="{{ route('shop') }}"
                        method="GET"
                        class="shop-filter-card"
                        x-ref="filterForm"
                        @submit.prevent="submitFilterForm($event)"
                    >

                        <input
                            type="hidden"
                            name="sort"
                            value="{{ request('sort') }}"
                        >

                        <div class="shop-filter-head">
                            <h2>Filter Koleksi</h2>

                            @if($activeFilters > 0)
                                <span class="shop-filter-count">
                                    {{ $activeFilters }} filter aktif
                                </span>
                            @endif
                        </div>

                        {{-- SEARCH --}}
                        <div class="shop-filter-section">
                            <label
                                for="shop-search"
                                class="shop-filter-label"
                            >
                                Cari produk
                            </label>

                            <div class="shop-search-row">
                                <input
                                    id="shop-search"
                                    type="text"
                                    name="search"
                                    value="{{ request('search') }}"
                                    placeholder="Nama hijab, warna, bahan…"
                                    class="shop-search-input"
                                >

                                <button
                                    type="submit"
                                    class="shop-search-submit"
                                    aria-label="Cari produk"
                                >
                                    <svg
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="m21 21-4.35-4.35M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- PRICE — otomatis memfilter saat slider digeser --}}
                        <div class="shop-filter-section">
                            <div class="shop-price-heading">
                                <label class="shop-filter-label">
                                    Rentang harga
                                </label>

                                <button
                                    type="button"
                                    x-cloak
                                    x-show="!isPriceDefault()"
                                    x-transition.opacity
                                    @click="resetPrice()"
                                    class="shop-price-reset"
                                >
                                    Atur ulang
                                </button>
                            </div>

                            <p class="shop-price-readout">
                                Rp
                                <span x-text="formatPrice(minPrice)"></span>

                                <span class="shop-price-sep">–</span>

                                Rp
                                <span x-text="formatPrice(maxPrice)"></span>
                            </p>

                            <div class="shop-range-area">
                                <div class="shop-range-track"></div>

                                <div
                                    class="shop-range-active"
                                    :style="
                                        'left:' +
                                        minPercent() +
                                        '%; width:' +
                                        (maxPercent() - minPercent()) +
                                        '%'
                                    "
                                ></div>

                                <input
                                    type="range"
                                    x-model.number="minPrice"
                                    @input="autoSubmit()"
                                    :min="minLimit"
                                    :max="Math.max(minLimit, maxPrice - 1000)"
                                    step="1000"
                                    class="shop-range-input"
                                    aria-label="Harga minimum"
                                >

                                <input
                                    type="range"
                                    x-model.number="maxPrice"
                                    @input="autoSubmit()"
                                    :min="Math.min(maxLimit, minPrice + 1000)"
                                    :max="maxLimit"
                                    step="1000"
                                    class="shop-range-input"
                                    aria-label="Harga maksimum"
                                >
                            </div>

                            <div class="shop-range-limits">
                                <span>
                                    Rp
                                    <span x-text="formatPrice(minLimit)"></span>
                                </span>

                                <span>
                                    Rp
                                    <span x-text="formatPrice(maxLimit)"></span>
                                </span>
                            </div>

                            <input
                                type="hidden"
                                name="min_price"
                                :value="minPrice"
                            >

                            <input
                                type="hidden"
                                name="max_price"
                                :value="maxPrice"
                            >
                        </div>

                        @if($activeFilters > 0)
                            <div class="shop-filter-section shop-filter-section-flush">
                                <a
                                    href="{{ route('shop') }}"
                                    class="shop-clear-link"
                                    @click.prevent="loadResults('{{ route('shop') }}')"
                                >
                                    Hapus semua filter
                                </a>
                            </div>
                        @endif

                    </form>

                    <div class="shop-sidebar-note">
                        <p class="shop-sidebar-note-kicker">
                            Zalina Fashion
                        </p>

                        <p class="shop-sidebar-note-text">
                            Pilihan sederhana untuk tampil anggun, nyaman,
                            dan percaya diri setiap hari.
                        </p>
                    </div>

                </aside>

                {{-- PRODUCT AREA --}}
                <div class="min-w-0" id="shop-results">

                    @include('store.partials.shop-results')

                </div>

            </div>
        </div>
    </section>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    /*
     * =========================================================================
     * LOVE BUTTON + DOUBLE-TAP
     *
     * Dipisah menjadi fungsi supaya bisa dipanggil ulang setelah
     * #shop-results ditukar isinya lewat AJAX.
     * =========================================================================
     */

    function updateLoveButton(button, loved) {

        const icon = button.querySelector('.love-icon');

        button.classList.toggle('is-loved', loved);

        if (icon) {
            icon.textContent = loved ? '♥' : '♡';
        }

        button.setAttribute(
            'aria-label',
            loved ? 'Hapus dari favorit' : 'Sukai produk'
        );

        button.setAttribute(
            'aria-pressed',
            loved ? 'true' : 'false'
        );
    }

    async function toggleLove(productId, button) {

        if (!button) return;

        const toggleUrl = '/favorit/' + productId + '/toggle';

        button.disabled = true;

        try {

            const response = await fetch(toggleUrl, {
                method: 'POST',

                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },

                credentials: 'same-origin'
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message || 'Gagal menyimpan favorit.'
                );
            }

            updateLoveButton(
                button,
                Boolean(data.favorited)
            );

        } catch (error) {

            console.error(
                'Favorite error:',
                error
            );

        } finally {

            button.disabled = false;
        }
    }

    function initLoveButtons(container) {

        container
            .querySelectorAll('[data-love-button]')
            .forEach(function (button) {

                if (button.dataset.initialized === 'true') {
                    return;
                }

                const productId =
                    button.dataset.loveButton;

                /*
                 * Status awal diambil dari database melalui
                 * atribut data-loved yang dikirim dari Blade.
                 */
                const initialLoved =
                    button.dataset.loved === '1' ||
                    button.dataset.loved === 'true';

                updateLoveButton(
                    button,
                    initialLoved
                );

                button.dataset.initialized = 'true';

                button.addEventListener(
                    'click',
                    function (event) {

                        event.preventDefault();
                        event.stopPropagation();

                        toggleLove(
                            productId,
                            button
                        );
                    }
                );
            });
    }

    function initDoubleTapLove(container) {

        container
            .querySelectorAll('.love-product-area')
            .forEach(function (area) {

                if (
                    area.dataset.doubleTapInitialized === 'true'
                ) {
                    return;
                }

                area.dataset.doubleTapInitialized = 'true';

                let lastTap = 0;

                area.addEventListener(
                    'click',
                    function (event) {

                        if (
                            event.target.closest(
                                '[data-love-button]'
                            )
                        ) {
                            return;
                        }

                        const now = Date.now();

                        if (now - lastTap < 350) {

                            event.preventDefault();
                            event.stopPropagation();

                            const productId =
                                area.dataset.productId;

                            const button =
                                area.querySelector(
                                    '[data-love-button="' +
                                    productId +
                                    '"]'
                                );

                            const animation =
                                area.querySelector(
                                    '[data-double-love="' +
                                    productId +
                                    '"]'
                                );

                            toggleLove(
                                productId,
                                button
                            );

                            if (animation) {

                                animation.classList.remove(
                                    'show'
                                );

                                void animation.offsetWidth;

                                animation.classList.add(
                                    'show'
                                );
                            }
                        }

                        lastTap = now;
                    }
                );
            });
    }

    /*
     * Inisialisasi pertama saat halaman dibuka.
     */
    initLoveButtons(document);
    initDoubleTapLove(document);

    /*
     * Dipanggil dari Alpine setiap kali #shop-results
     * ditukar isinya.
     */
    window.zalinaInitShopResults = function (container) {

        initLoveButtons(container);
        initDoubleTapLove(container);
    };

    /*
     * =========================================================================
     * NAVIGASI AJAX UNTUK LINK DI DALAM #shop-results
     *
     * Pagination, chip filter aktif, dan "lihat semua koleksi"
     * ikut memfilter tanpa reload.
     *
     * Link kartu produk (.shop-product-card) sengaja dikecualikan
     * karena harus membuka halaman detail produk.
     * =========================================================================
     */

    document.addEventListener(
        'click',
        function (event) {

            const resultsEl =
                document.getElementById('shop-results');

            if (
                !resultsEl ||
                !resultsEl.contains(event.target)
            ) {
                return;
            }

            const link =
                event.target.closest('a[href]');

            if (
                !link ||
                !resultsEl.contains(link)
            ) {
                return;
            }

            if (
                link.closest('.shop-product-card')
            ) {
                return;
            }

            const zalinaRoot =
                document.querySelector('.zalina-shop');

            const data =
                zalinaRoot &&
                window.Alpine
                    ? window.Alpine.$data(zalinaRoot)
                    : null;

            if (
                !data ||
                typeof data.loadResults !== 'function'
            ) {
                return;
            }

            event.preventDefault();

            data.loadResults(link.href);
        }
    );

    /*
     * Tombol back/forward browser tetap sinkron
     * dengan hasil filter.
     */
    window.addEventListener(
        'popstate',
        function () {

            const zalinaRoot =
                document.querySelector('.zalina-shop');

            const data =
                zalinaRoot &&
                window.Alpine
                    ? window.Alpine.$data(zalinaRoot)
                    : null;

            if (
                !data ||
                typeof data.loadResults !== 'function'
            ) {
                return;
            }

            data.loadResults(
                window.location.href,
                false
            );
        }
    );

});
</script>

@endsection