@extends('layouts.store')

@section('title', $product->name.' — Zalina Fashion')

@section('content')

@php
    $activeVariants = $product->variants
        ->where('is_active', true)
        ->values();

    $hasVariants = $activeVariants->count() > 0;

    /*
    |--------------------------------------------------------------------------
    | BUG FIX: STOK HARUS DIHITUNG PER VARIAN
    |--------------------------------------------------------------------------
    |
    | Sebelumnya $hasStock dan $maxQuantity SELALU memakai $product->stock,
    | padahal saat produk punya varian, stok yang sebenarnya berlaku ada di
    | tiap varian (sama seperti di CheckoutController & cart). Akibatnya:
    |
    | - Varian yang stoknya 0 tetap tampil sebagai pilihan aktif & bisa
    |   ditambahkan ke keranjang, karena tombol "Tambah ke Keranjang" hanya
    |   dinonaktifkan berdasarkan stok produk induk, bukan stok varian yang
    |   sedang dipilih.
    | - Input quantity dibatasi ke stok produk induk, bukan stok varian yang
    |   sedang dipilih, jadi customer bisa memasukkan qty lebih besar dari
    |   stok varian tersebut.
    | - Saat checkout, CheckoutController benar menolak varian yang stoknya
    |   habis (lihat validasi di sana) sehingga order gagal dibuat & stok
    |   memang tidak berkurang — tapi dari sisi customer, halaman produk
    |   sama sekali tidak memberi tahu bahwa varian yang dipilih sudah habis,
    |   jadi terasa seperti "macet" tanpa penjelasan.
    |
    | Sekarang: halaman produk sadar stok per varian dan disinkronkan lewat
    | Alpine.js supaya tombol/quantity mengikuti varian yang sedang dipilih.
    |
    */

    $hasStock = $hasVariants
        ? $activeVariants->contains(fn ($variant) => (int) $variant->stock > 0)
        : (int) $product->stock > 0;

    // Varian yang otomatis terpilih saat halaman dibuka: utamakan varian
    // yang masih ada stoknya, supaya customer tidak langsung "mendarat"
    // di varian yang sudah habis.
    $defaultVariant = $hasVariants
        ? ($activeVariants->first(fn ($variant) => (int) $variant->stock > 0) ?? $activeVariants->first())
        : null;

    // Total stok "general" (jumlah semua varian aktif, atau stok produk kalau
    // tidak punya varian) + peta stok per varian untuk Alpine.
    $totalStock = $hasVariants
        ? (int) $activeVariants->sum(fn ($variant) => max(0, (int) $variant->stock))
        : max(0, (int) $product->stock);

    $availableVariantCount = $activeVariants
        ->filter(fn ($variant) => (int) $variant->stock > 0)
        ->count();

    // Object (bukan list) supaya di JS bisa diakses lewat variantStock[id].
    $variantStockMap = (object) $activeVariants
        ->mapWithKeys(fn ($variant) => [$variant->id => max(0, (int) $variant->stock)])
        ->all();

    $maxQuantity = $hasVariants
        ? max(1, (int) ($defaultVariant->stock ?? 0))
        : max(1, (int) $product->stock);

    $hasSale = $product->sale_price
        && $product->price
        && (float) $product->sale_price < (float) $product->price;

    $discountPercentage = $hasSale
        ? round((1 - ($product->sale_price / $product->price)) * 100)
        : 0;

    $mainImage = $product->image
        ? asset('storage/'.$product->image)
        : null;

    $fallbackDescription = 'Hijab premium Zalina yang dirancang dengan bahan pilihan, lembut, adem, dan nyaman digunakan sepanjang hari. Cocok untuk aktivitas formal maupun santai.';

    $waNumber = '628133117767';

    $waMessage = rawurlencode('Halo Zalina, saya mau tanya soal produk: '.$product->name);

    $waLink = 'https://wa.me/'.$waNumber.'?text='.$waMessage;
@endphp

<style>
    [x-cloak] {
        display: none !important;
    }

    /* =========================================================
       ZALINA PRODUCT PAGE
    ========================================================= */

    .zalina-product-page {
        color: #351820;
        background:
            radial-gradient(
                circle at top right,
                rgba(243, 226, 216, .38),
                transparent 34rem
            ),
            #fffaf8;
        overflow-x: hidden;
    }

    .zalina-product-page a,
    .zalina-product-page button {
        -webkit-tap-highlight-color: transparent;
    }

    .zalina-product-page button:focus-visible,
    .zalina-product-page a:focus-visible,
    .zalina-product-page input:focus-visible {
        outline: 3px solid rgba(114, 47, 63, .22);
        outline-offset: 3px;
    }

    /* =========================================================
       BREADCRUMB
    ========================================================= */

    .zalina-breadcrumb {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 7px;
        color: #8b6570;
        font-size: 12px;
        line-height: 1.6;
    }

    .zalina-breadcrumb a {
        transition: color .2s ease;
    }

    .zalina-breadcrumb a:hover {
        color: #722f3f;
    }

    .zalina-breadcrumb-current {
        min-width: 0;
        max-width: 210px;
        overflow: hidden;
        color: #5b303b;
        font-weight: 600;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* =========================================================
       PRODUCT IMAGE
    ========================================================= */

    .zalina-product-image {
        position: relative;
        aspect-ratio: 4 / 5;
        width: 100%;
        overflow: hidden;
        border: 1px solid #ead7db;
        border-radius: 28px;
        background:
            linear-gradient(
                135deg,
                #fff0f3 0%,
                #fffaf8 52%,
                #f8eddf 100%
            );
        box-shadow: 0 18px 55px rgba(91, 48, 59, .08);
    }

    .zalina-product-image img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center center;
        margin: 0 auto;
    }

    .zalina-image-fallback {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 30px;
        text-align: center;
    }

    .zalina-image-fallback span {
        color: #dcbec5;
        font-family: Georgia, "Times New Roman", serif;
        font-size: clamp(6rem, 20vw, 11rem);
        line-height: 1;
        user-select: none;
    }

    .zalina-sale-badge {
        position: absolute;
        top: 18px;
        left: 18px;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        min-height: 30px;
        padding: 6px 12px;
        border-radius: 999px;
        background: #722f3f;
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .04em;
        box-shadow: 0 8px 20px rgba(114, 47, 63, .2);
    }

    .zalina-image-count {
        position: absolute;
        right: 16px;
        bottom: 16px;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 32px;
        padding: 7px 11px;
        border: 1px solid rgba(255, 255, 255, .75);
        border-radius: 999px;
        background: rgba(255, 255, 255, .84);
        color: #5b303b;
        font-size: 11px;
        font-weight: 700;
        backdrop-filter: blur(10px);
    }

    .zalina-thumbnail-grid {
        display: flex;
        gap: 9px;
        margin-top: 12px;
        overflow-x: auto;
        overflow-y: hidden;
        padding-bottom: 6px;
        scroll-snap-type: x proximity;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #e3b8c2 transparent;
    }

    .zalina-thumbnail-grid::-webkit-scrollbar {
        height: 6px;
    }

    .zalina-thumbnail-grid::-webkit-scrollbar-track {
        background: transparent;
    }

    .zalina-thumbnail-grid::-webkit-scrollbar-thumb {
        background: #e3b8c2;
        border-radius: 999px;
    }

    .zalina-thumbnail {
        position: relative;
        flex: 0 0 auto;
        width: 72px;
        aspect-ratio: 1 / 1;
        overflow: hidden;
        border: 1px solid #ead7db;
        border-radius: 15px;
        background: #fff4f5;
        scroll-snap-align: start;
    }

    .zalina-thumbnail img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .zalina-thumbnail-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        color: #c9aeb5;
    }


    /* =========================================================
       STOCK STATUS / SOLD OUT
       Namespace terisolasi agar tidak menimpa style existing
    ========================================================= */

    .zalina-stock-status-overlay {
        position: absolute;
        inset: 0;
        z-index: 8;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: inherit;
        background: rgba(91, 48, 59, .74);
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
        animation: zalina-stock-overlay-in .3s ease both;
        pointer-events: none;
    }

    .zalina-stock-status-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        padding: 18px;
        text-align: center;
    }

    .zalina-stock-status-icon {
        width: 48px;
        height: 48px;
        color: #fff;
        opacity: .92;
        animation: zalina-stock-icon-in .6s cubic-bezier(.68, -.55, .265, 1.55) both;
    }

    .zalina-stock-status-text {
        margin: 0;
        color: #fff;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 1.7rem;
        font-weight: 500;
        line-height: 1.2;
        letter-spacing: -.025em;
        text-shadow: 0 2px 8px rgba(0,0,0,.2);
    }

    .zalina-stock-alert {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-top: 20px;
        padding: 16px;
        border: 1px solid rgba(198, 103, 108, .25);
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(198, 103, 108, .08), rgba(114, 47, 63, .06));
        animation: zalina-stock-alert-in .4s ease both;
    }

    .zalina-stock-alert-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        flex: 0 0 auto;
        border-radius: 50%;
        background: rgba(114, 47, 63, .15);
        color: #722f3f;
    }

    .zalina-stock-alert-icon svg {
        width: 18px;
        height: 18px;
    }

    .zalina-stock-alert-text {
        flex: 1;
        min-width: 0;
    }

    .zalina-stock-alert-title {
        margin: 0;
        color: #722f3f;
        font-size: 14px;
        font-weight: 700;
    }

    .zalina-stock-alert-subtitle {
        margin: 4px 0 0;
        color: #927780;
        font-size: 13px;
        line-height: 1.5;
    }

    .zalina-add-button.is-sold-out,
    .zalina-mobile-cart-button.is-sold-out {
        cursor: not-allowed;
        background: #e5d5d9;
        color: #a0868f;
        box-shadow: none;
        opacity: .72;
        transform: none;
    }

    .zalina-add-button.is-sold-out:hover,
    .zalina-add-button.is-sold-out:active,
    .zalina-mobile-cart-button.is-sold-out:hover,
    .zalina-mobile-cart-button.is-sold-out:active {
        background: #e5d5d9;
        box-shadow: none;
        transform: none;
    }

    @keyframes zalina-stock-overlay-in {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes zalina-stock-icon-in {
        0% { opacity: 0; transform: scale(.3) rotate(-45deg); }
        100% { opacity: .92; transform: scale(1) rotate(0); }
    }

    @keyframes zalina-stock-alert-in {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* =========================================================
       PRODUCT INFORMATION
    ========================================================= */

    .zalina-product-info {
        min-width: 0;
    }

    .zalina-category-label {
        color: #a47a43;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .22em;
        line-height: 1.5;
        text-transform: uppercase;
    }

    .zalina-product-title {
        margin-top: 10px;
        color: #351820;
        font-family: Georgia, "Times New Roman", serif;
        font-size: clamp(2rem, 4vw, 2.75rem);
        font-weight: 500;
        letter-spacing: -.025em;
        line-height: 1.13;
        overflow-wrap: anywhere;
    }

    .zalina-price-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px 12px;
        margin-top: 20px;
    }

    .zalina-current-price {
        color: #722f3f;
        font-family: Georgia, "Times New Roman", serif;
        font-size: clamp(1.7rem, 4vw, 2.15rem);
        font-weight: 700;
        letter-spacing: -.02em;
        line-height: 1.2;
    }

    .zalina-old-price {
        color: #b8959e;
        font-size: 15px;
        text-decoration: line-through;
    }

    .zalina-special-price {
        display: inline-flex;
        align-items: center;
        min-height: 25px;
        padding: 4px 9px;
        border-radius: 999px;
        background: #f3e3c9;
        color: #79572c;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .03em;
    }

    .zalina-stock-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 7px;
        margin-top: 15px;
        color: #8b6570;
        font-size: 13px;
        line-height: 1.6;
    }

    .zalina-stock-dot {
        width: 8px;
        height: 8px;
        flex: 0 0 auto;
        border-radius: 999px;
    }

    .zalina-stock-dot.available {
        background: #22a06b;
    }

    .zalina-stock-dot.empty {
        background: #dc5b68;
    }

    .zalina-stock-available {
        color: #198754;
        font-weight: 700;
    }

    .zalina-stock-empty {
        color: #c2414d;
        font-weight: 700;
    }

    .zalina-stock-separator {
        color: #d0b8be;
    }

    /* =========================================================
       DESCRIPTION
    ========================================================= */

    .zalina-description {
        width: 100%;
        min-width: 0;
        margin-top: 24px;
        padding: 18px;
        border: 1px solid #eadcdf;
        border-radius: 19px;
        background: #fff;
        box-shadow: 0 8px 30px rgba(91, 48, 59, .035);
    }

    .zalina-description-heading {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 11px;
    }

    .zalina-description-heading-line {
        width: 28px;
        height: 1px;
        flex: 0 0 auto;
        background: #c8a06a;
    }

    .zalina-description-heading-text {
        color: #a47a43;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .22em;
        line-height: 1.5;
        text-transform: uppercase;
    }

    .zalina-description-content {
        max-width: 100%;
        color: #5b3a43;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.9;
        overflow-wrap: anywhere;
        white-space: pre-line;
        word-break: break-word;
    }

    /* =========================================================
       VARIANTS
    ========================================================= */

    .zalina-variant-label {
        position: relative;
        display: inline-flex;
        cursor: pointer;
    }

    .zalina-variant-label input {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }

    .zalina-variant-option {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        max-width: 100%;
        padding: 10px 17px;
        border: 1px solid #dfc9cf;
        border-radius: 999px;
        background: #fff;
        color: #5b303b;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.35;
        overflow-wrap: anywhere;
        text-align: center;
        transition:
            background .2s ease,
            border-color .2s ease,
            color .2s ease,
            transform .2s ease,
            box-shadow .2s ease;
        user-select: none;
    }

    .zalina-variant-label:hover .zalina-variant-option {
        border-color: #8f4b5c;
        background: #fff8f9;
        transform: translateY(-1px);
    }

    .zalina-variant-option {
        flex-direction: column;
        gap: 2px;
    }

    .zalina-variant-name {
        display: block;
    }

    .zalina-variant-stock {
        display: block;
        font-size: 10.5px;
        font-weight: 600;
        letter-spacing: .02em;
        color: #198754;
    }

    .zalina-variant-stock.is-low {
        color: #b7791f;
    }

    /* Varian habis: abu-abu, dicoret, tidak bisa dipilih */
    .zalina-variant-label.is-variant-out {
        cursor: not-allowed;
    }

    .zalina-variant-label.is-variant-out .zalina-variant-option {
        border-color: #e2c8cd;
        border-style: dashed;
        background: #f7f0f1;
        color: #b79aa1;
    }

    .zalina-variant-label.is-variant-out .zalina-variant-name {
        text-decoration: line-through;
    }

    .zalina-variant-label.is-variant-out .zalina-variant-stock {
        color: #c14b4b;
    }

    .zalina-variant-label.is-variant-out:hover .zalina-variant-option {
        border-color: #e2c8cd;
        background: #f7f0f1;
        transform: none;
    }

    .zalina-variant-summary {
        margin-top: 4px;
        font-size: 12px;
        font-weight: 600;
        color: #7b5560;
    }

    .zalina-qty-remaining {
        align-self: center;
        font-size: 12px;
        font-weight: 600;
        color: #7b5560;
    }

    .zalina-variant-out-label {
        margin-left: 4px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #c14b4b;
    }

    .zalina-variant-label input:checked + .zalina-variant-option {
        border-color: #722f3f;
        background: #722f3f;
        color: #fff;
        box-shadow: 0 7px 18px rgba(114, 47, 63, .18);
    }

    /* =========================================================
       QUANTITY
    ========================================================= */

    .zalina-qty {
        display: inline-flex;
        align-items: center;
        width: fit-content;
        height: 50px;
        overflow: hidden;
        border: 1px solid #dfc9cf;
        border-radius: 999px;
        background: #fff;
    }

    .zalina-qty button {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 50px;
        color: #722f3f;
        font-size: 20px;
        font-weight: 700;
        transition: background .2s ease;
    }

    .zalina-qty button:hover {
        background: #fff4f6;
    }

    .zalina-qty input {
        width: 48px;
        height: 50px;
        border: 0;
        outline: 0;
        background: transparent;
        color: #351820;
        font-size: 14px;
        font-weight: 800;
        text-align: center;
    }

    .zalina-qty input::-webkit-outer-spin-button,
    .zalina-qty input::-webkit-inner-spin-button {
        margin: 0;
        -webkit-appearance: none;
    }

    .zalina-qty input[type="number"] {
        -moz-appearance: textfield;
    }

    /* =========================================================
       BUTTONS
    ========================================================= */

    .zalina-add-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-height: 50px;
        border-radius: 999px;
        background: #722f3f;
        color: #fff;
        font-size: 14px;
        font-weight: 800;
        box-shadow: 0 8px 25px rgba(114, 47, 63, .18);
        transition:
            background .2s ease,
            box-shadow .2s ease,
            transform .2s ease;
    }

    .zalina-add-button:hover {
        background: #5f2634;
        box-shadow: 0 10px 30px rgba(114, 47, 63, .24);
        transform: translateY(-1px);
    }

    .zalina-add-button:active {
        background: #4f202c;
        transform: translateY(0);
    }

    .zalina-add-button:disabled {
        cursor: not-allowed;
        background: #d8c3c8;
        box-shadow: none;
        transform: none;
    }

    .zalina-support-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        min-height: 48px;
        padding: 0 18px;
        border: 1px solid #ead7db;
        border-radius: 999px;
        background: #fff;
        color: #722f3f;
        font-size: 13px;
        font-weight: 700;
        transition:
            background .2s ease,
            border-color .2s ease;
    }

    .zalina-support-link:hover {
        border-color: #d8b9c1;
        background: #fff5f7;
    }

    .zalina-icon {
        width: 18px;
        height: 18px;
        flex: 0 0 auto;
    }

    /* =========================================================
       SIGNATURE SECTION
    ========================================================= */

    .zalina-signature {
        position: relative;
        margin-top: 48px;
    }

    .zalina-signature-line {
        width: 100%;
        height: 1px;
        background: linear-gradient(
            90deg,
            transparent,
            #e8d4d9 15%,
            #e8d4d9 85%,
            transparent
        );
    }

    .zalina-signature-number {
        color: #c8a06a;
        font-family: Georgia, "Times New Roman", serif;
        line-height: 1;
    }

    .zalina-signature-item {
        position: relative;
        min-width: 0;
    }

    .zalina-signature-item::after {
        position: absolute;
        top: 10%;
        right: 0;
        width: 1px;
        height: 80%;
        background: #eadcdf;
        content: "";
    }

    .zalina-signature-item:last-child::after {
        display: none;
    }

    .zalina-signature-title {
        color: #351820;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 19px;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .zalina-signature-description {
        margin-top: 9px;
        color: #795d65;
        font-size: 13px;
        line-height: 1.7;
        overflow-wrap: anywhere;
    }

    /* =========================================================
       FLOATING SUPPORT BUTTON
    ========================================================= */

    .zalina-floating-support {
        position: fixed;
        right: 20px;
        bottom: 22px;
        z-index: 35;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        border: 1px solid #ead7db;
        border-radius: 999px;
        background: #fff;
        color: #722f3f;
        box-shadow: 0 10px 30px rgba(91, 48, 59, .14);
        transition:
            background .2s ease,
            transform .2s ease;
    }

    .zalina-floating-support:hover {
        background: #fff5f7;
        transform: translateY(-2px);
    }

    /* =========================================================
       MOBILE STICKY CART
    ========================================================= */

    .zalina-mobile-cart {
        position: fixed;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 40;
        padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
        border-top: 1px solid #eadcdf;
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 -8px 30px rgba(91, 48, 59, .08);
        backdrop-filter: blur(16px);
    }

    .zalina-mobile-cart-inner {
        display: flex;
        align-items: center;
        gap: 12px;
        max-width: 1280px;
        margin: 0 auto;
    }

    .zalina-mobile-cart-price {
        min-width: 0;
        flex: 1;
    }

    .zalina-mobile-cart-label {
        color: #a47a43;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .12em;
        line-height: 1.5;
        text-transform: uppercase;
    }

    .zalina-mobile-cart-value {
        margin-top: 2px;
        overflow: hidden;
        color: #722f3f;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 18px;
        font-weight: 700;
        line-height: 1.3;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .zalina-mobile-cart-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 46px;
        padding: 0 18px;
        border-radius: 999px;
        background: #722f3f;
        color: #fff;
        font-size: 13px;
        font-weight: 800;
        white-space: nowrap;
    }

    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (min-width: 1024px) {
        .zalina-product-image {
            aspect-ratio: 4 / 5;
            border-radius: 32px;
        }

        .zalina-description {
            padding: 20px;
        }

        .zalina-description-content {
            font-size: 15px;
            line-height: 1.9;
        }

        .zalina-signature {
            margin-top: 58px;
        }
    }

    @media (max-width: 767px) {
        .zalina-breadcrumb {
            font-size: 11px;
        }

        .zalina-breadcrumb-current {
            max-width: 155px;
        }

        .zalina-product-image {
            border-radius: 22px;
        }

        .zalina-sale-badge {
            top: 13px;
            left: 13px;
        }

        .zalina-image-count {
            right: 12px;
            bottom: 12px;
        }

        .zalina-thumbnail-grid {
            gap: 7px;
            margin-top: 9px;
        }

        .zalina-thumbnail {
            width: 60px;
            border-radius: 11px;
        }

        .zalina-product-title {
            margin-top: 8px;
            font-size: 31px;
        }

        .zalina-price-row {
            margin-top: 16px;
        }

        .zalina-current-price {
            font-size: 28px;
        }

        .zalina-description {
            margin-top: 20px;
            padding: 15px;
            border-radius: 16px;
        }

        .zalina-description-content {
            font-size: 13px;
            line-height: 1.85;
        }

        .zalina-variant-option {
            min-height: 42px;
            padding: 9px 14px;
            font-size: 12px;
        }

        .zalina-signature {
            margin-top: 42px;
        }

        .zalina-signature-item::after {
            display: none;
        }

        .zalina-signature-item + .zalina-signature-item {
            border-top: 1px solid #eadcdf;
        }

        .zalina-floating-support {
            right: 16px;
            bottom: 88px;
            width: 44px;
            height: 44px;
        }
    }

    @media (max-width: 420px) {
        .zalina-mobile-cart {
            padding-right: 12px;
            padding-left: 12px;
        }

        .zalina-mobile-cart-inner {
            gap: 9px;
        }

        .zalina-mobile-cart-button {
            padding: 0 14px;
            font-size: 12px;
        }

        .zalina-mobile-cart-value {
            font-size: 16px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .zalina-product-page *,
        .zalina-product-page *::before,
        .zalina-product-page *::after {
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
            animation-duration: .01ms !important;
        }
    }

    /* =========================================================
       PRODUCT LOVE
    ========================================================= */

    .zalina-product-love-row {
        display: flex;
        align-items: center;
        margin-top: 14px;
        margin-bottom: 8px;
    }

    .zalina-product-love-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        padding: 8px 16px;
        border: 1px solid #f9a8d4;
        border-radius: 999px;
        background: #fff1f8;
        color: #ec4899;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        transition: all .2s ease;
    }

    .zalina-product-love-button:hover {
        background: #fce7f3;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(236, 72, 153, .18);
    }

    .zalina-product-love-button.is-loved {
        background: #ec4899;
        border-color: #ec4899;
        color: #fff;
        box-shadow: 0 8px 20px rgba(236, 72, 153, .24);
    }

    .zalina-product-love-icon {
        font-size: 24px;
        line-height: 1;
    }

    .zalina-product-love-button.is-loved
    .zalina-product-love-icon {
        animation: zalina-love-button-pop .35s ease;
    }

    .zalina-double-love {
    position: absolute;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    opacity: 0;
}

.zalina-double-love-heart {
    position: absolute;
    color: #ff4f9a;
    font-size: 118px;
    line-height: 1;
    opacity: 0;
    transform: scale(.2);
    text-shadow:
        0 0 6px rgba(255, 79, 154, .9),
        0 0 14px rgba(255, 79, 154, .65),
        0 0 26px rgba(255, 105, 180, .45);
    filter: none;
    will-change: transform, opacity;
}

.zalina-double-love.show {
    animation: zalinaLoveOverlaySmooth .85s ease-out forwards;
}

.zalina-double-love.show .heart-one {
    animation: zalinaHeartSmooth .85s cubic-bezier(.16, 1, .3, 1) forwards;
}

.zalina-double-love.show .heart-two {
    animation: zalinaHeartFloat .85s cubic-bezier(.16, 1, .3, 1) forwards;
}

@keyframes zalinaLoveOverlaySmooth {
    0% {
        opacity: 0;
    }
    12% {
        opacity: 1;
    }
    72% {
        opacity: 1;
    }
    100% {
        opacity: 0;
    }
}

@keyframes zalinaHeartSmooth {
    0% {
        opacity: 0;
        transform: scale(.2) rotate(-12deg);
        filter: none;
    }
    18% {
        opacity: 1;
        transform: scale(1.18) rotate(0deg);
        filter: none;
    }
    42% {
        opacity: 1;
        transform: scale(1) rotate(0deg);
    }
    72% {
        opacity: .9;
        transform: scale(1.08) rotate(3deg);
    }
    100% {
        opacity: 0;
        transform: scale(1.28) rotate(8deg);
        filter: none;
    }
}

@keyframes zalinaHeartFloat {
    0% {
        opacity: 0;
        transform: scale(.15) translate(0, 0) rotate(15deg);
        filter: none;
    }
    20% {
        opacity: .9;
        transform: scale(.72) translate(45px, -35px) rotate(12deg);
        filter: none;
    }
    65% {
        opacity: .65;
        transform: scale(.62) translate(100px, -105px) rotate(25deg);
    }
    100% {
        opacity: 0;
        transform: scale(.42) translate(130px, -145px) rotate(38deg);
        filter: none;
    }
}

    /* =========================================================
       PANEL KERANJANG (BOTTOM SHEET)
    ========================================================= */

    .zalina-sheet-root {
        position: fixed;
        inset: 0;
        z-index: 70;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        color: #351820;
    }

    .zalina-sheet-root *,
    .zalina-sheet-root *::before,
    .zalina-sheet-root *::after {
        box-sizing: border-box;
    }

    .zalina-sheet-root button:focus-visible,
    .zalina-sheet-root input:focus-visible {
        outline: 3px solid rgba(114, 47, 63, .22);
        outline-offset: 3px;
    }

    .zalina-sheet-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(53, 24, 32, .55);
        backdrop-filter: blur(2px);
    }

    .zalina-sheet {
        position: relative;
        width: 100%;
        max-width: 540px;
        max-height: 90vh;
        overflow-y: auto;
        border-radius: 26px 26px 0 0;
        background: #fffaf8;
        box-shadow: 0 -18px 50px rgba(53, 24, 32, .22);
        padding-bottom: env(safe-area-inset-bottom);
    }

    .zalina-sheet-head {
        position: relative;
        display: flex;
        align-items: flex-end;
        gap: 14px;
        padding: 20px 52px 18px 20px;
    }

    .zalina-sheet-thumb {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        width: 96px;
        height: 96px;
        overflow: hidden;
        border: 1px solid #ead7db;
        border-radius: 16px;
        background: linear-gradient(135deg, #fff0f3, #f8eddf);
        color: #dcbec5;
        font-family: Georgia, serif;
        font-size: 44px;
    }

    .zalina-sheet-thumb img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .zalina-sheet-summary {
        min-width: 0;
        flex: 1;
    }

    .zalina-sheet-price {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        gap: 2px 10px;
    }

    .zalina-sheet-price-now {
        color: #722f3f;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 24px;
        font-weight: 700;
        letter-spacing: -.02em;
        line-height: 1.2;
    }

    .zalina-sheet-stock {
        margin: 6px 0 0;
        color: #8b6570;
        font-size: 13px;
    }

    .zalina-sheet-stock strong { color: #198754; font-weight: 700; }
    .zalina-sheet-stock.is-low strong { color: #b7791f; }
    .zalina-sheet-stock.is-out { color: #c2414d; font-weight: 700; }

    .zalina-sheet-picked {
        margin: 3px 0 0;
        color: #7b5560;
        font-size: 12px;
        font-weight: 600;
    }

    .zalina-sheet-close {
        position: absolute;
        top: 14px;
        right: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 999px;
        color: #8b6570;
        transition: background .2s ease;
    }

    .zalina-sheet-close:hover { background: #f6e8eb; }

    .zalina-sheet-section {
        padding: 18px 20px;
        border-top: 1px solid #eadcdf;
    }

    .zalina-sheet-label {
        color: #4d2832;
        font-size: 14px;
        font-weight: 700;
    }

    .zalina-sheet-footer {
        padding: 16px 20px 20px;
        border-top: 1px solid #eadcdf;
    }

    .zalina-sheet-footer .zalina-add-button {
        width: 100%;
        min-height: 52px;
    }

    /* gambar varian di dalam chip */
    .zalina-variant-img {
        display: block;
        width: 44px;
        height: 44px;
        margin-bottom: 4px;
        border-radius: 10px;
        object-fit: cover;
        background: #f8eddf;
    }

    .zalina-variant-label.is-variant-out .zalina-variant-img {
        opacity: .45;
        filter: grayscale(1);
    }

    .zalina-sheet-thumb img {
        animation: zalina-thumb-swap .28s ease both;
    }

    @keyframes zalina-thumb-swap {
        from { opacity: 0; transform: scale(.94); }
        to   { opacity: 1; transform: scale(1); }
    }

    /* transisi */
    .zs-fade-enter { transition: opacity .25s ease; }
    .zs-fade-leave { transition: opacity .2s ease; }
    .zs-fade-start { opacity: 0; }
    .zs-fade-end   { opacity: 1; }

    .zs-enter { transition: transform .32s cubic-bezier(.22, .61, .36, 1), opacity .32s ease; }
    .zs-leave { transition: transform .22s ease, opacity .22s ease; }
    .zs-enter-start { transform: translateY(100%); opacity: .6; }
    .zs-enter-end   { transform: translateY(0); opacity: 1; }
    .zs-leave-end   { transform: translateY(100%); opacity: .6; }

    @media (min-width: 768px) {
        .zalina-sheet-root { align-items: center; }

        .zalina-sheet {
            border-radius: 26px;
            max-height: 86vh;
        }

        .zs-enter-start,
        .zs-leave-end { transform: translateY(24px); opacity: 0; }
    }

    @media (prefers-reduced-motion: reduce) {
        .zs-enter, .zs-leave, .zs-fade-enter, .zs-fade-leave {
            transition-duration: .01ms !important;
        }
    }

</style>

<div class="zalina-product-page min-h-screen pb-32 lg:pb-0">

    {{-- =========================================================
         BREADCRUMB
    ========================================================== --}}
    <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 sm:pt-6 lg:px-8">

        <nav class="zalina-breadcrumb" aria-label="Breadcrumb">

            <a href="{{ route('home') }}">
                Beranda
            </a>

            <span aria-hidden="true" class="text-[#c9aeb5]">/</span>

            <a href="{{ route('shop') }}">
                Koleksi
            </a>

            <span aria-hidden="true" class="text-[#c9aeb5]">/</span>

            <span class="zalina-breadcrumb-current" title="{{ $product->name }}">
                {{ $product->name }}
            </span>

        </nav>

    </div>

    {{-- =========================================================
         MAIN PRODUCT AREA
    ========================================================== --}}
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-10 lg:px-8 lg:py-14">

        <div class="grid items-start gap-8 lg:grid-cols-2 lg:gap-14 xl:gap-20">

            {{-- =================================================
                 LEFT — PRODUCT IMAGE SLIDER
            ================================================== --}}
            @php
                $sliderImages = collect();

                if ($product->image) {
                    $sliderImages->push(asset('storage/' . ltrim($product->image, '/')));
                }

                foreach ($product->images->sortBy('sort_order') as $galleryImage) {
                    if (!empty($galleryImage->path)) {
                        $sliderImages->push(asset('storage/' . ltrim($galleryImage->path, '/')));
                    }
                }

                $sliderImages = $sliderImages->filter()->values();
            @endphp

            <div class="w-full min-w-0 lg:sticky lg:top-24">
                <div class="mx-auto w-full max-w-[560px]">

                    <div
                        x-data="{
                            images: @js($sliderImages->all()),

                            /*
                            | pos = indeks pada TRACK (yang berisi slide kloning),
                            | bukan indeks gambar asli. Track disusun sebagai:
                            |   [gambar terakhir, ...semua gambar, gambar pertama]
                            | sehingga swipe dari gambar terakhir bisa MAJU ke
                            | kloning gambar pertama, lalu posisinya di-teleport
                            | diam-diam tanpa transisi. Efeknya: loop mulus ke
                            | depan, tidak pernah mundur melintasi semua gambar.
                            */
                            pos: 0,
                            animate: true,

                            offset: 0,
                            dragging: false,
                            startX: 0,
                            startY: 0,
                            axis: null,
                            width: 1,

                            get looped() {
                                return this.images.length > 1;
                            },

                            get slides() {
                                if (!this.looped) {
                                    return this.images.slice();
                                }

                                return [
                                    this.images[this.images.length - 1],
                                    ...this.images,
                                    this.images[0]
                                ];
                            },

                            get active() {
                                if (!this.looped) {
                                    return 0;
                                }

                                const n = this.images.length;

                                return ((this.pos - 1) % n + n) % n;
                            },

                            init() {
                                this.pos = this.looped ? 1 : 0;
                            },

                            go(direction) {
                                if (!this.looped) return;

                                this.animate = true;
                                this.pos += direction;
                                this.offset = 0;
                            },

                            next() { this.go(1); },
                            previous() { this.go(-1); },

                            goTo(index) {
                                this.animate = true;
                                this.pos = this.looped ? index + 1 : index;
                                this.offset = 0;
                                this.scrollThumbnailIntoView();
                            },

                            /*
                            | Teleport senyap setelah animasi sampai di slide kloning.
                            */
                            onTransitionEnd() {
                                if (!this.looped) return;

                                const n = this.images.length;

                                if (this.pos > n) {
                                    this.jumpTo(1);
                                } else if (this.pos < 1) {
                                    this.jumpTo(n);
                                }
                            },

                            jumpTo(position) {
                                this.animate = false;
                                this.pos = position;

                                requestAnimationFrame(() => {
                                    requestAnimationFrame(() => {
                                        this.animate = true;
                                    });
                                });
                            },

                            startDrag(event) {
                                if (!this.looped) return;

                                const point = event.touches
                                    ? event.touches[0]
                                    : event;

                                this.dragging = true;
                                this.axis = null;
                                this.startX = point.clientX;
                                this.startY = point.clientY;
                                this.width = this.$refs.viewport.offsetWidth || 1;
                                this.offset = 0;
                            },

                            /*
                            | AXIS LOCK
                            |
                            | Arah gerakan dikunci setelah melewati ambang 8px.
                            | Kalau ternyata vertikal, drag langsung dibatalkan
                            | supaya halaman tetap bisa di-scroll ke bawah dan
                            | gambar TIDAK ikut bergeser ke samping.
                            */
                            moveDrag(event) {
                                if (!this.dragging) return;

                                const point = event.touches
                                    ? event.touches[0]
                                    : event;

                                const dx = point.clientX - this.startX;
                                const dy = point.clientY - this.startY;

                                if (this.axis === null) {
                                    if (Math.abs(dx) < 8 && Math.abs(dy) < 8) {
                                        return;
                                    }

                                    this.axis = Math.abs(dx) > Math.abs(dy) ? 'x' : 'y';
                                }

                                if (this.axis === 'y') {
                                    this.dragging = false;
                                    this.offset = 0;
                                    this.animate = true;
                                    return;
                                }

                                this.animate = false;
                                this.offset = dx;
                            },

                            endDrag() {
                                if (!this.dragging) return;

                                this.dragging = false;
                                this.animate = true;

                                const threshold = Math.max(40, this.width * 0.15);

                                if (this.axis === 'x' && Math.abs(this.offset) >= threshold) {
                                    this.pos += this.offset < 0 ? 1 : -1;
                                    this.scrollThumbnailIntoView();
                                }

                                this.axis = null;
                                this.offset = 0;
                            },

                            scrollThumbnailIntoView() {
                                this.$nextTick(() => {
                                    const grid = this.$refs.thumbnailGrid;

                                    if (!grid) return;

                                    const thumb = grid.querySelector(
                                        '[data-thumb-index=\'' + this.active + '\']'
                                    );

                                    if (thumb) {
                                        thumb.scrollIntoView({
                                            behavior: 'smooth',
                                            inline: 'center',
                                            block: 'nearest'
                                        });
                                    }
                                });
                            }
                        }"
                        class="relative"
                    >
                        <div
                            x-ref="viewport"
                            class="zalina-product-image relative overflow-hidden select-none"
                            style="touch-action: pan-y;"
                            @touchstart="startDrag($event)"
                            @touchmove="moveDrag($event)"
                            @touchend="endDrag()"
                            @touchcancel="endDrag()"
                            @mousedown="startDrag($event)"
                            @mousemove="moveDrag($event)"
                            @mouseup="endDrag()"
                            @mouseleave="dragging && endDrag()"
                        >
                            <template x-if="images.length > 0">
                                <div
                                    class="absolute inset-0 flex w-full h-full"
                                    style="will-change: transform;"
                                    @transitionend="onTransitionEnd()"
                                    :style="{
                                        transform: `translate3d(calc(-${pos * 100}% + ${offset}px), 0, 0)`,
                                        transition: (dragging || !animate) ? 'none' : 'transform 420ms cubic-bezier(.22,.61,.36,1)'
                                    }"
                                >
                                    <template x-for="(image, index) in slides" :key="index">
                                        <div class="relative h-full w-full min-w-full shrink-0 overflow-hidden flex items-center justify-center">
                                            <img
                                                :src="image"
                                                alt="{{ $product->name }}"
                                                loading="eager"
                                                draggable="false"
                                                class="h-full w-full object-contain object-center pointer-events-none"
                                            >
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <div
                                class="zalina-double-love"
                                id="zalinaDoubleLove"
                            >
                                <span class="zalina-double-love-heart heart-one">♥</span>
                                <span class="zalina-double-love-heart heart-two">♥</span>
                            </div>

                            <template x-if="images.length === 0">
                                <div class="zalina-image-fallback">
                                    <span>{{ mb_substr($product->name, 0, 1) }}</span>
                                </div>
                            </template>

                            @if(!$hasStock)
                                <div class="zalina-stock-status-overlay" aria-label="Produk terjual habis">
                                    <div class="zalina-stock-status-content">
                                        <svg class="zalina-stock-status-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                                        </svg>
                                        <p class="zalina-stock-status-text">Terjual Habis</p>
                                    </div>
                                </div>
                            @endif

                            @if($hasSale)
                                <span class="zalina-sale-badge">
                                    -{{ $discountPercentage }}%
                                </span>
                            @endif

                            <template x-if="images.length > 1">
                                <div>
                                    <button
                                        type="button"
                                        @click="previous()"
                                        class="absolute left-3 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white/90 px-3 py-2 text-lg text-[#8b1e3f] shadow-md"
                                        aria-label="Gambar sebelumnya"
                                    >
                                        ‹
                                    </button>

                                    <button
                                        type="button"
                                        @click="next()"
                                        class="absolute right-3 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white/90 px-3 py-2 text-lg text-[#8b1e3f] shadow-md"
                                        aria-label="Gambar berikutnya"
                                    >
                                        ›
                                    </button>
                                </div>
                            </template>

                            <template x-if="images.length > 0">
                                <span class="zalina-image-count" x-text="(active + 1) + '/ ' + images.length + ' Foto'"></span>
                            </template>
                        </div>

                        <template x-if="images.length > 1">
                            <div class="zalina-thumbnail-grid mt-4" x-ref="thumbnailGrid">
                                <template x-for="(image, index) in images" :key="'thumb-' + index">
                                    <button
                                        type="button"
                                        @click="goTo(index)"
                                        :data-thumb-index="index"
                                        class="zalina-thumbnail overflow-hidden rounded-xl border-2 transition"
                                        :class="active === index ? 'border-[#8b1e3f]' : 'border-transparent'"
                                    >
                                        <img
                                            :src="image"
                                            :alt="'{{ $product->name }} thumbnail ' + (index + 1)"
                                            class="h-full w-full object-cover pointer-events-none"
                                            loading="lazy"
                                        >
                                    </button>
                                </template>
                            </div>
                        </template>

                    </div>
                </div>
            </div>

            {{-- =================================================
                 RIGHT — PRODUCT INFORMATION
            ================================================== --}}
            <div class="zalina-product-info w-full">

                {{-- CATEGORY --}}
                <div class="zalina-category-label">
                    {{ $product->category?->name ?? 'Zalina Fashion' }}
                </div>

                {{-- PRODUCT NAME --}}
                <h1 class="zalina-product-title">
                    {{ $product->name }}
                </h1>

                {{-- PRODUCT LOVE --}}
                <div class="zalina-product-love-row">
                    <button
                        type="button"
                        id="zalinaProductLoveButton"
                        class="zalina-product-love-button"
                        aria-label="Sukai produk"
                    >
                        <span
                            id="zalinaProductLoveIcon"
                            class="zalina-product-love-icon"
                        >♡</span>

                        <span id="zalinaProductLoveLabel">
                            Love
                        </span>
                    </button>
                </div>

                {{-- PRICE --}}
                <div class="zalina-price-row">

                    <span class="zalina-current-price">
                        Rp {{ number_format($product->current_price, 0, ',', '.') }}
                    </span>

                    @if($hasSale)

                        <span class="zalina-old-price">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </span>

                        <span class="zalina-special-price">
                            Harga Spesial
                        </span>

                    @endif

                </div>

                {{-- SKU (info stok, varian & jumlah sekarang ada di panel keranjang) --}}
                @if($product->sku)
                    <div class="zalina-stock-row">
                        <span>SKU: {{ $product->sku }}</span>
                    </div>
                @endif

                {{-- DESCRIPTION --}}
                <section class="zalina-description" aria-labelledby="description-title">

                    <div class="zalina-description-heading">

                        <span class="zalina-description-heading-line"></span>

                        <h2
                            id="description-title"
                            class="zalina-description-heading-text"
                        >
                            About the piece
                        </h2>

                    </div>

                    <p class="zalina-description-content">
                        {{ $product->description ?: $fallbackDescription }}
                    </p>

                </section>

                {{-- TOMBOL KERANJANG (membuka panel pilih varian & jumlah) --}}
                <div class="mt-7 sm:mt-8">
                    <button
                        type="button"
                        class="zalina-add-button w-full px-6 sm:px-8 {{ $hasStock ? '' : 'is-sold-out' }}"
                        @if($hasStock)
                            @click="$store.zalinaAddToCart.open = true"
                        @else
                            disabled
                            aria-disabled="true"
                        @endif
                    >
                        <svg
                            class="zalina-icon"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            aria-hidden="true"
                        >
                            <path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 1.9-1.4L21 8H6"/>
                            <circle cx="10" cy="20" r="1"/>
                            <circle cx="18" cy="20" r="1"/>
                            <path d="M12 5v6m-3-3h6"/>
                        </svg>

                        <span>{{ $hasStock ? 'Tambah ke Keranjang' : 'Stok Habis' }}</span>
                    </button>
                </div>

                {{-- SUPPORT LINK --}}
                <div class="mt-4 flex flex-wrap gap-3">

                    <a
                        href="{{ $waLink }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="zalina-support-link"
                    >

                        <svg
                            class="zalina-icon"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path d="M12 2a10 10 0 00-8.66 15l-1.18 4.3 4.4-1.15A10 10 0 1012 2zm5.2 14.1c-.22.62-1.27 1.13-1.75 1.2-.45.07-1.03.1-1.66-.1-.38-.12-.87-.28-1.5-.55-2.65-1.14-4.38-3.8-4.51-3.98-.13-.18-1.08-1.44-1.08-2.74 0-1.3.68-1.94.92-2.2.24-.26.52-.33.69-.33h.5c.16 0 .37-.06.58.45.22.52.74 1.8.81 1.93.07.13.12.29.02.47-.1.18-.15.29-.3.44-.15.15-.31.33-.44.44-.15.15-.31.31-.13.6.18.29.8 1.31 1.72 2.12 1.18 1.05 2.18 1.38 2.48 1.53.3.15.48.13.66-.08.18-.21.76-.88.96-1.18.2-.3.4-.25.67-.15.28.1 1.76.83 2.06.98.3.15.5.22.57.34.07.12.07.7-.15 1.32z"/>
                        </svg>

                        Tanya Produk

                    </a>

                </div>

                {{-- =================================================
                     ZALINA SIGNATURE
                ================================================== --}}
                <section class="zalina-signature">

                    <div class="zalina-signature-line"></div>

                    <div class="flex flex-col gap-4 pb-6 pt-7 sm:flex-row sm:items-end sm:justify-between sm:gap-6 sm:pb-7 sm:pt-9">

                        <div class="min-w-0">

                            <div class="mb-3 flex items-center gap-3">

                                <span class="text-[10px] font-bold uppercase tracking-[.3em] text-[#a47a43]">
                                    Zalina Signature
                                </span>

                                <span class="h-px w-10 bg-[#c8a06a]"></span>

                            </div>

                            <h2 class="font-serif text-2xl font-medium leading-tight text-[#351820] sm:text-3xl">
                                Made for your moments.
                            </h2>

                            <p class="mt-2 max-w-xl text-xs leading-6 text-[#856b73] sm:text-sm">
                                Detail sederhana, karakter yang terasa.
                                Temukan sentuhan Zalina dalam setiap pilihan.
                            </p>

                        </div>

                        <div class="hidden text-right sm:block">

                            <div class="text-[9px] uppercase tracking-[.25em] text-[#b99da5]">
                                Zalina Fashion
                            </div>

                            <div class="mt-1 font-serif text-sm italic text-[#8a6871]">
                                Elegance in Every Drape
                            </div>

                        </div>

                    </div>

                    <div class="grid grid-cols-1 border-y border-[#eadcdf] sm:grid-cols-3">

                        {{-- SIGNATURE 01 --}}
                        <div class="zalina-signature-item py-6 sm:py-8 sm:pr-7">

                            <div class="flex items-start gap-4">

                                <span class="zalina-signature-number text-2xl sm:text-3xl">
                                    01
                                </span>

                                <div class="min-w-0">

                                    <div class="mb-2 text-[10px] font-bold uppercase tracking-[.2em] text-[#a47a43]">
                                        The Feel
                                    </div>

                                    <div class="zalina-signature-title">
                                        Lembut & Nyaman
                                    </div>

                                    <p class="zalina-signature-description">
                                        Dibuat untuk terasa ringan,
                                        nyaman, dan effortless sepanjang hari.
                                    </p>

                                </div>

                            </div>

                        </div>

                        {{-- SIGNATURE 02 --}}
                        <div class="zalina-signature-item py-6 sm:px-7 sm:py-8">

                            <div class="flex items-start gap-4">

                                <span class="zalina-signature-number text-2xl sm:text-3xl">
                                    02
                                </span>

                                <div class="min-w-0">

                                    <div class="mb-2 text-[10px] font-bold uppercase tracking-[.2em] text-[#a47a43]">
                                        The Collection
                                    </div>

                                    <div class="zalina-signature-title">
                                        Zalina Signature
                                    </div>

                                    <p class="zalina-signature-description">
                                        Sebuah pilihan yang dirancang
                                        untuk melengkapi gaya personalmu.
                                    </p>

                                </div>

                            </div>

                        </div>

                        {{-- SIGNATURE 03 --}}
                        <div class="zalina-signature-item py-6 sm:py-8 sm:pl-7">

                            <div class="flex items-start gap-4">

                                <span class="zalina-signature-number text-2xl sm:text-3xl">
                                    03
                                </span>

                                <div class="min-w-0">

                                    <div class="mb-2 text-[10px] font-bold uppercase tracking-[.2em] text-[#a47a43]">
                                        Availability
                                    </div>

                                    <div class="zalina-signature-title">

                                        @if($hasStock)
                                            Ready to Wear
                                        @else
                                            Currently Unavailable
                                        @endif

                                    </div>

                                    <p class="zalina-signature-description">

                                        @if($hasStock)
                                            Siap menjadi bagian
                                            dari koleksi harianmu.
                                        @else
                                            Produk sedang belum tersedia
                                            untuk dipesan.
                                        @endif

                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="flex flex-col gap-5 py-7 sm:flex-row sm:items-center sm:justify-between sm:gap-6 sm:py-9">

                        <div>

                            <div class="mb-2 text-[9px] font-bold uppercase tracking-[.25em] text-[#a47a43]">
                                Zalina Philosophy
                            </div>

                            <p class="font-serif text-base italic text-[#6d4b54] sm:text-lg">
                                “Elegance in every drape.”
                            </p>

                        </div>

                        <div class="flex items-center gap-3">

                            <span class="h-px w-12 bg-[#c8a06a]"></span>

                            <span class="text-[9px] font-semibold uppercase tracking-[.25em] text-[#a47a43]">
                                ZALINA FASHION
                            </span>

                        </div>

                    </div>

                    <div class="zalina-signature-line"></div>

                </section>

            </div>

        </div>

    </div>

</div>

{{-- =============================================================
     FLOATING SUPPORT ICON
============================================================= --}}
<a
    href="{{ $waLink }}"
    target="_blank"
    rel="noopener noreferrer"
    class="zalina-floating-support"
    aria-label="Hubungi Zalina via WhatsApp"
    title="Tanya via WhatsApp"
>

    <svg
        class="h-5 w-5"
        viewBox="0 0 24 24"
        fill="currentColor"
        aria-hidden="true"
    >
        <path d="M12 2a10 10 0 00-8.66 15l-1.18 4.3 4.4-1.15A10 10 0 1012 2zm5.2 14.1c-.22.62-1.27 1.13-1.75 1.2-.45.07-1.03.1-1.66-.1-.38-.12-.87-.28-1.5-.55-2.65-1.14-4.38-3.8-4.51-3.98-.13-.18-1.08-1.44-1.08-2.74 0-1.3.68-1.94.92-2.2.24-.26.52-.33.69-.33h.5c.16 0 .37-.06.58.45.22.52.74 1.8.81 1.93.07.13.12.29.02.47-.1.18-.15.29-.3.44-.15.15-.31.33-.44.44-.15.15-.31.31-.13.6.18.29.8 1.31 1.72 2.12 1.18 1.05 2.18 1.38 2.48 1.53.3.15.48.13.66-.08.18-.21.76-.88.96-1.18.2-.3.4-.25.67-.15.28.1 1.76.83 2.06.98.3.15.5.22.57.34.07.12.07.7-.15 1.32z"/>
    </svg>

</a>

{{-- =============================================================
     MOBILE STICKY CART
     Sold out -> tombol nonaktif total (tidak bisa disentuh).
============================================================= --}}
<div class="zalina-mobile-cart lg:hidden">

    <div class="zalina-mobile-cart-inner">

        <div class="zalina-mobile-cart-price">

            <div class="zalina-mobile-cart-label">
                Harga
            </div>

            <div class="zalina-mobile-cart-value">
                Rp {{ number_format($product->current_price, 0, ',', '.') }}
            </div>

        </div>

        <button
            type="button"
            class="zalina-mobile-cart-button {{ $hasStock ? '' : 'is-sold-out' }}"
            @if($hasStock)
                @click="$store.zalinaAddToCart.open = true"
            @else
                disabled
                aria-disabled="true"
            @endif
        >

            <svg
                class="zalina-icon"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
                aria-hidden="true"
            >
                <path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 1.9-1.4L21 8H6"/>
                <circle cx="10" cy="20" r="1"/>
                <circle cx="18" cy="20" r="1"/>
                <path d="M12 5v6m-3-3h6"/>
            </svg>

            <span>{{ $hasStock ? 'Keranjang' : 'Stok Habis' }}</span>

        </button>

    </div>

</div>

{{-- =============================================================
     PANEL KERANJANG (bottom sheet)
     Berisi: stok, pilihan varian, jumlah, dan tombol konfirmasi.
     Form tetap POST ke cart.add dengan field quantity + variant_id,
     jadi notifikasi WhatsApp admin di CartController tidak berubah.
============================================================= --}}
@if($hasStock)
@php
    $variantNameMap = (object) $activeVariants
        ->mapWithKeys(fn ($variant) => [$variant->id => (string) $variant->name])
        ->all();

    /*
    | Gambar per varian. Nama kolom belum pasti, jadi beberapa kemungkinan
    | dicoba berurutan. Kalau kolom gambar varian Anda bernama lain,
    | tambahkan di daftar $variantImageColumns.
    */
    $variantImageColumns = ['image', 'image_path', 'photo', 'picture', 'thumbnail', 'img'];

    /*
    | Ambil kolom image LANGSUNG dari database. Ini menjaga gambar tetap
    | muncul walau controller halaman produk memuat varian dengan
    | ->select([...]) terbatas atau memakai data cache lama.
    */
    $variantImagesFromDb = [];

    try {
        if (\Illuminate\Support\Facades\Schema::hasColumn('product_variants', 'image')) {
            $variantImagesFromDb = \App\Models\ProductVariant::query()
                ->whereIn('id', $activeVariants->pluck('id')->all())
                ->pluck('image', 'id')
                ->all();
        }
    } catch (\Throwable $e) {
        $variantImagesFromDb = [];
    }

    $variantImageUrl = function ($variant) use ($variantImageColumns, $variantImagesFromDb) {
        $candidates = [];

        foreach ($variantImageColumns as $column) {
            $candidates[] = data_get($variant, $column);
        }

        $candidates[] = $variantImagesFromDb[$variant->id] ?? null;

        foreach ($candidates as $path) {
            if (is_string($path) && trim($path) !== '') {
                return preg_match('#^https?://#i', $path)
                    ? $path
                    : asset('storage/'.ltrim($path, '/'));
            }
        }

        return null;
    };

    $variantImageMap = (object) $activeVariants
        ->mapWithKeys(fn ($variant) => [$variant->id => $variantImageUrl($variant)])
        ->all();
@endphp

<div
    class="zalina-sheet-root"
    x-data="{
        qty: 1,
        submitting: false,
        hasVariants: {{ $hasVariants ? 'true' : 'false' }},
        variantId: {{ $defaultVariant->id ?? 'null' }},
        variantStock: @js($variantStockMap),
        variantNames: @js($variantNameMap),
        variantImages: @js($variantImageMap),
        mainImage: @js($mainImage),
        productStock: {{ (int) $product->stock }},
        get currentStock() {
            return this.hasVariants
                ? Number(this.variantStock[this.variantId] ?? 0)
                : Number(this.productStock);
        },
        get maxQty() { return Math.max(1, this.currentStock); },
        get outOfStock() { return this.currentStock <= 0; },
        get sheetImage() {
            return (this.hasVariants && this.variantImages[this.variantId]) || this.mainImage;
        },
        get variantName() { return this.hasVariants ? (this.variantNames[this.variantId] ?? '') : ''; },
        selectVariant(id) {
            this.variantId = id;
            this.qty = Math.min(this.qty, this.maxQty) || 1;
        },
        clampQty() {
            this.qty = Math.max(1, Math.min(this.maxQty, Math.floor(Number(this.qty)) || 1));
        },
        close() { $store.zalinaAddToCart.open = false; }
    }"
    x-show="$store.zalinaAddToCart.open"
    x-cloak
    x-effect="document.body.style.overflow = $store.zalinaAddToCart.open ? 'hidden' : ''"
    @keydown.escape.window="close()"
    @pageshow.window="submitting = false; close()"
    role="dialog"
    aria-modal="true"
    aria-label="Pilih varian dan jumlah"
>

    <div
        class="zalina-sheet-backdrop"
        x-show="$store.zalinaAddToCart.open"
        x-transition:enter="zs-fade-enter"
        x-transition:enter-start="zs-fade-start"
        x-transition:enter-end="zs-fade-end"
        x-transition:leave="zs-fade-leave"
        x-transition:leave-start="zs-fade-end"
        x-transition:leave-end="zs-fade-start"
        @click="close()"
    ></div>

    <div
        class="zalina-sheet"
        x-show="$store.zalinaAddToCart.open"
        x-transition:enter="zs-enter"
        x-transition:enter-start="zs-enter-start"
        x-transition:enter-end="zs-enter-end"
        x-transition:leave="zs-leave"
        x-transition:leave-start="zs-enter-end"
        x-transition:leave-end="zs-leave-end"
    >

        <form
            id="add-to-cart-form"
            method="POST"
            action="{{ route('cart.add', $product) }}"
            @submit="
                clampQty();
                if (outOfStock || submitting || (hasVariants && !variantId)) {
                    $event.preventDefault();
                    return;
                }
                submitting = true;
            "
        >

            @csrf

            {{-- HEADER: foto, harga, stok --}}
            <div class="zalina-sheet-head">

                <div class="zalina-sheet-thumb">
                    <template x-if="sheetImage">
                        <img
                            :src="sheetImage"
                            :alt="variantName ? '{{ addslashes($product->name) }} - ' + variantName : '{{ addslashes($product->name) }}'"
                            :key="sheetImage"
                        >
                    </template>
                    <template x-if="!sheetImage">
                        <span>{{ mb_substr($product->name, 0, 1) }}</span>
                    </template>
                </div>

                <div class="zalina-sheet-summary">

                    <div class="zalina-sheet-price">
                        <span class="zalina-sheet-price-now">
                            Rp {{ number_format($product->current_price, 0, ',', '.') }}
                        </span>

                        @if($hasSale)
                            <span class="zalina-old-price">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </span>
                        @endif
                    </div>

                    <p class="zalina-sheet-stock" :class="{ 'is-low': currentStock > 0 && currentStock <= 5, 'is-out': outOfStock }">
                        <span x-show="!outOfStock">Stok: <strong x-text="currentStock"></strong></span>
                        <span x-show="outOfStock" x-cloak>Stok habis</span>
                    </p>

                    <p class="zalina-sheet-picked" x-show="variantName" x-cloak x-text="'Warna: ' + variantName"></p>

                </div>

                <button
                    type="button"
                    class="zalina-sheet-close"
                    @click="close()"
                    aria-label="Tutup"
                >
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </button>

            </div>

            {{-- VARIAN --}}
            @if($activeVariants->count() > 0)

                <section class="zalina-sheet-section">

                    <div class="mb-3 flex items-start justify-between gap-4">

                        <div class="min-w-0">

                            <h2 class="text-sm font-bold uppercase tracking-[.12em] text-[#4d2832]">
                                Pilih Varian
                            </h2>

                            <p class="zalina-variant-summary">
                                Total stok {{ $totalStock }} &middot;
                                {{ $availableVariantCount }} dari {{ $activeVariants->count() }} varian tersedia
                            </p>

                        </div>

                        <span class="shrink-0 text-[10px] font-semibold uppercase tracking-[.15em] text-[#b18a51]">
                            Color
                        </span>

                    </div>

                    <div class="flex flex-wrap gap-2.5">

                        @foreach($activeVariants as $variant)

                            @php
                                $variantStockNow = max(0, (int) $variant->stock);
                                $variantHasStock = $variantStockNow > 0;
                            @endphp

                            <label
                                class="zalina-variant-label {{ $variantHasStock ? '' : 'is-variant-out' }}"
                                title="{{ $variantHasStock ? 'Stok tersedia: '.$variantStockNow : 'Stok habis' }}"
                            >

                                <input
                                    type="radio"
                                    name="variant_id"
                                    value="{{ $variant->id }}"
                                    x-model.number="variantId"
                                    @change="selectVariant({{ $variant->id }})"
                                    @checked($defaultVariant && $defaultVariant->id === $variant->id)
                                    @disabled(!$variantHasStock)
                                >

                                <span class="zalina-variant-option">
                                    @if($variantImageUrl($variant))
                                        <img
                                            class="zalina-variant-img"
                                            src="{{ $variantImageUrl($variant) }}"
                                            alt="{{ $variant->name }}"
                                            loading="lazy"
                                        >
                                    @endif
                                    <span class="zalina-variant-name">{{ $variant->name }}</span>
                                    <span class="zalina-variant-stock {{ $variantHasStock && $variantStockNow <= 5 ? 'is-low' : '' }}">
                                        @if(!$variantHasStock)
                                            Habis
                                        @elseif($variantStockNow <= 5)
                                            Sisa {{ $variantStockNow }}
                                        @else
                                            Stok {{ $variantStockNow }}
                                        @endif
                                    </span>
                                </span>

                            </label>

                        @endforeach

                    </div>

                </section>

            @endif

            {{-- JUMLAH --}}
            <section class="zalina-sheet-section">

                <div class="flex items-center justify-between gap-4">

                    <span class="zalina-sheet-label">Jumlah</span>

                    <div class="zalina-qty">

                        <button
                            type="button"
                            :disabled="outOfStock"
                            @click="qty = Math.max(1, qty - 1)"
                            aria-label="Kurangi jumlah"
                        >
                            −
                        </button>

                        <input
                            type="number"
                            name="quantity"
                            value="1"
                            min="1"
                            :max="maxQty"
                            :disabled="outOfStock"
                            x-model.number="qty"
                            @change="clampQty()"
                            aria-label="Jumlah produk"
                        >

                        <button
                            type="button"
                            :disabled="outOfStock"
                            @click="qty = Math.min(maxQty, qty + 1)"
                            aria-label="Tambah jumlah"
                        >
                            +
                        </button>

                    </div>

                </div>

                <div
                    x-show="currentStock > 0 && currentStock <= 5"
                    x-cloak
                    class="zalina-stock-alert"
                    role="status"
                >
                    <div class="zalina-stock-alert-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                        </svg>
                    </div>
                    <div class="zalina-stock-alert-text">
                        <p class="zalina-stock-alert-title">Stok Terbatas</p>
                        <p
                            class="zalina-stock-alert-subtitle"
                            x-text="'Hanya ' + currentStock + (hasVariants ? ' pcs tersisa untuk varian ini. Pesan sekarang!' : ' produk tersisa. Pesan sekarang!')"
                        ></p>
                    </div>
                </div>

                <div
                    x-show="outOfStock"
                    x-cloak
                    class="zalina-stock-alert"
                    role="status"
                >
                    <div class="zalina-stock-alert-text">
                        <p class="zalina-stock-alert-title">Varian ini terjual habis</p>
                        <p class="zalina-stock-alert-subtitle">Silakan pilih varian lain yang masih tersedia.</p>
                    </div>
                </div>

            </section>

            {{-- KONFIRMASI --}}
            <div class="zalina-sheet-footer">

                <button
                    type="submit"
                    class="zalina-add-button w-full px-6"
                    :class="{ 'is-sold-out': outOfStock }"
                    :disabled="outOfStock || submitting"
                    :aria-disabled="(outOfStock || submitting) ? 'true' : 'false'"
                >

                    <svg
                        class="zalina-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                        aria-hidden="true"
                    >
                        <path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 1.9-1.4L21 8H6"/>
                        <circle cx="10" cy="20" r="1"/>
                        <circle cx="18" cy="20" r="1"/>
                        <path d="M12 5v6m-3-3h6"/>
                    </svg>

                    <span x-text="outOfStock ? 'Stok Habis' : (submitting ? 'Memproses…' : 'Masukkan Keranjang')"></span>

                </button>

            </div>

        </form>

    </div>

</div>
@endif


{{--
|--------------------------------------------------------------------------
| SCRIPT LOVE — DITARUH DI DALAM @section
|--------------------------------------------------------------------------
|
| Sebelumnya blok <script> ini berada SETELAH @endsection. Karena view ini
| memakai @extends, konten di luar section tidak ikut ke dalam struktur
| layout — HTML-nya bocor keluar sebelum <!DOCTYPE>, yang membuat markup
| tidak valid dan eksekusinya bergantung pada perilaku browser. Sekarang
| skrip berada di dalam section sehingga posisinya pasti benar.
|
--}}

@php
    /*
    | Status favorit dihitung dengan pengaman.
    |
    | Kalau controller sudah mengirim $isFavorited, nilai itu yang dipakai.
    | Kalau belum, dihitung di sini TAPI dibungkus pengecekan tabel dan
    | try/catch — sebelumnya query Eloquent dipanggil mentah di dalam
    | @json(), sehingga bila tabel favorites belum ada (migrasi belum jalan)
    | SELURUH halaman produk ikut crash, bukan hanya fitur love-nya.
    */

    if (!isset($isFavorited)) {
        $isFavorited = false;

        try {
            $zalinaUserId = session('zalina_user_id');

            if ($zalinaUserId && \Illuminate\Support\Facades\Schema::hasTable('favorites')) {
                $isFavorited = \App\Models\Favorite::query()
                    ->where('user_id', $zalinaUserId)
                    ->where('product_id', $product->id)
                    ->exists();
            }
        } catch (\Throwable $e) {
            $isFavorited = false;
        }
    }
@endphp

{{--
|--------------------------------------------------------------------------
| ALPINE STORE — BUKA/TUTUP PANEL KERANJANG
|--------------------------------------------------------------------------
|
| Didaftarkan lewat alpine:init. Dipakai bersama oleh tombol keranjang di
| halaman, tombol di sticky bar mobile, dan panel keranjang (bottom sheet)
| yang letaknya terpisah di DOM.
|
--}}

<script>
document.addEventListener('alpine:init', function () {
    Alpine.store('zalinaAddToCart', {
        // true = panel keranjang (varian, stok, jumlah) sedang terbuka
        open: false,
        // true = SEMUA stok habis -> tombol keranjang tidak bisa disentuh
        soldOut: {{ $hasStock ? 'false' : 'true' }},
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const loveButton    = document.getElementById('zalinaProductLoveButton');
    const loveIcon      = document.getElementById('zalinaProductLoveIcon');
    const loveLabel     = document.getElementById('zalinaProductLoveLabel');
    const loveAnimation = document.getElementById('zalinaDoubleLove');
    const imageArea     = document.querySelector('.zalina-product-image');

    if (!loveButton) return;

    const toggleUrl = @json(route('favorites.toggle', $product->id));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    let isLoved  = Boolean(@json((bool) $isFavorited));
    let inFlight = false;

    function updateLoveButton() {
        loveButton.classList.toggle('is-loved', isLoved);
        loveButton.setAttribute('aria-pressed', isLoved ? 'true' : 'false');

        if (loveIcon)  { loveIcon.textContent  = isLoved ? '♥' : '♡'; }
        if (loveLabel) { loveLabel.textContent = isLoved ? 'Loved' : 'Love'; }
    }

    updateLoveButton();

    /*
    |--------------------------------------------------------------------------
    | REQUEST FAVORIT
    |--------------------------------------------------------------------------
    |
    | inFlight mencegah request bertumpuk saat tombol diketuk cepat berkali-kali.
    | Tanpa ini, dua request toggle bisa saling menimpa dan status akhirnya
    | jadi kebalikan dari yang user lihat.
    |
    */

    async function sendToggle() {
        if (inFlight) return;

        inFlight = true;

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

            if (response.redirected || response.status === 401 || response.status === 419) {
                console.warn('Request favorit dialihkan ke profile:', response.status);
                return;
            }

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Gagal mengubah favorit');
            }

            isLoved = Boolean(data.favorited);
            updateLoveButton();

        } catch (error) {
            console.error('Favorite error:', error);
        } finally {
            inFlight = false;
        }
    }

    function animateLove() {
        if (!loveAnimation) return;

        loveAnimation.style.display        = 'flex';
        loveAnimation.style.position       = 'absolute';
        loveAnimation.style.inset          = '0';
        loveAnimation.style.zIndex         = '9999';
        loveAnimation.style.alignItems     = 'center';
        loveAnimation.style.justifyContent = 'center';
        loveAnimation.style.pointerEvents  = 'none';
        loveAnimation.style.opacity        = '1';

        loveAnimation.classList.remove('show');
        void loveAnimation.offsetWidth;
        loveAnimation.classList.add('show');

        setTimeout(function () {
            loveAnimation.classList.remove('show');
            loveAnimation.style.opacity = '0';
        }, 900);
    }

    /*
    |--------------------------------------------------------------------------
    | TOMBOL LOVE — SATU-SATUNYA CARA UNTUK UN-LOVE
    |--------------------------------------------------------------------------
    */

    loveButton.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        sendToggle();
    });

    /*
    |--------------------------------------------------------------------------
    | DOUBLE TAP DI GAMBAR — HANYA MENAMBAH LOVE, TIDAK PERNAH MENGHAPUS
    |--------------------------------------------------------------------------
    |
    | Sebelumnya double-tap memanggil toggle mentah, jadi double-tap pada
    | produk yang SUDAH di-love malah menghapusnya dari favorit padahal
    | animasi hati tetap muncul. Sekarang animasi selalu tampil, tetapi
    | request hanya dikirim kalau produk belum di-love.
    |
    */

    function likeOnDoubleTap() {
        animateLove();

        if (!isLoved) {
            sendToggle();
        }
    }

    if (imageArea) {

        const TAP_SLOP    = 10;   // px — di atas ini dianggap geser, bukan ketukan
        const TAP_MAX_MS  = 400;  // di atas ini dianggap tahan lama, bukan ketukan
        const DOUBLE_MS   = 400;  // jeda maksimum antar dua ketukan

        let downX = 0;
        let downY = 0;
        let downTime = 0;
        let moved = false;
        let lastTapTime = 0;

        /*
        | Memakai Pointer Events, BUKAN kombinasi click + touchend.
        |
        | Versi lama memasang DUA handler sekaligus (click dan touchend) untuk
        | area yang sama. Di perangkat sentuh keduanya ikut terpicu dari satu
        | ketukan (touchend lalu click sintetis), sehingga hitungan ketukan
        | jadi kacau dan satu ketukan bisa terbaca sebagai dua. Pointer Events
        | menyatukan mouse dan sentuh dalam satu jalur, jadi tidak ada lagi
        | event ganda.
        */

        imageArea.addEventListener('pointerdown', function (event) {
            downX = event.clientX;
            downY = event.clientY;
            downTime = Date.now();
            moved = false;
        });

        imageArea.addEventListener('pointermove', function (event) {
            if (moved) return;

            if (
                Math.abs(event.clientX - downX) > TAP_SLOP ||
                Math.abs(event.clientY - downY) > TAP_SLOP
            ) {
                moved = true;
            }
        });

        imageArea.addEventListener('pointercancel', function () {
            moved = true;
            lastTapTime = 0;
        });

        imageArea.addEventListener('pointerup', function (event) {

            if (event.target.closest('button, a, input, select, textarea')) {
                return;
            }

            /*
            | Jari sempat bergeser (swipe kiri/kanan ATAU scroll ke bawah)
            | -> ini bukan ketukan. Rantai double-tap direset supaya swipe
            | beruntun tidak pernah terbaca sebagai double-tap.
            */

            if (moved || (Date.now() - downTime) > TAP_MAX_MS) {
                lastTapTime = 0;
                return;
            }

            const now = Date.now();

            if (lastTapTime !== 0 && (now - lastTapTime) < DOUBLE_MS) {
                event.preventDefault();
                likeOnDoubleTap();
                lastTapTime = 0;
            } else {
                lastTapTime = now;
            }
        });
    }
});
</script>

@endsection