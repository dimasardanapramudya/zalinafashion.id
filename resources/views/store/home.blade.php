{{-- ==========================================================================
     ZALINA FASHION
     Editorial Luxury Fashion Homepage
     Laravel Blade View
     File: resources/views/store/home.blade.php
     ========================================================================== --}}

@extends('layouts.store')

@section('title', 'Zalina Fashion — Elegance in Every Drape')

{{-- Menandai body supaya CSS global layout tidak menimpa desain homepage --}}
@section('bodyClass', 'page-scoped page-home')

@section('content')

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    /*
    |--------------------------------------------------------------------------
    | DATA NORMALIZATION
    |--------------------------------------------------------------------------
    | Semua data dibuat aman terhadap:
    | - variable belum dikirim controller
    | - collection kosong
    | - field image berbeda
    | - setting belum tersedia
    |--------------------------------------------------------------------------
    */

    $siteName = $siteName
        ?? data_get($settings ?? null, 'site_name')
        ?? data_get($settings ?? null, 'store_name')
        ?? 'Zalina Fashion';

    $siteTagline = data_get($settings ?? null, 'site_tagline')
        ?? data_get($settings ?? null, 'tagline')
        ?? 'Elegance in Every Drape';

    $siteDescription = data_get($settings ?? null, 'site_description')
        ?? data_get($settings ?? null, 'description')
        ?? 'Koleksi hijab, gamis, dan aksesoris muslimah dengan sentuhan elegan untuk setiap momen.';

    $activeTheme = data_get($settings ?? null, 'theme')
        ?? data_get($settings ?? null, 'store_theme')
        ?? 'maroon_gold';

    $autoplayEnabled = isset($autoplay)
        ? (bool) $autoplay
        : (bool) (
            data_get($settings ?? null, 'slider_autoplay')
            ?? data_get($settings ?? null, 'autoplay')
            ?? true
        );

    $sliders = collect($sliders ?? []);

    $productsCollection = collect(
        $productsCollection
        ?? $products
        ?? []
    );

    $categoriesCollection = collect(
        $categoriesCollection
        ?? $categories
        ?? []
    );

    $discountsCollection = collect(
        $discountsCollection
        ?? $discounts
        ?? []
    );

    /*
    |--------------------------------------------------------------------------
    | THEME CONFIGURATION
    |--------------------------------------------------------------------------
    */

    $themeMap = [
        'maroon_gold' => [
            'name' => 'Maroon Gold',
            'ink' => '#351b25',
            'inkSoft' => '#5d414a',
            'paper' => '#fbf7f3',
            'paperAlt' => '#f2e8e1',
            'accent' => '#a87943',
            'accentSoft' => '#d8b78a',
            'line' => 'rgba(74, 39, 48, .14)',
            'button' => '#4b2633',
            'buttonHover' => '#321923',
            'muted' => '#877278',
        ],

        'rose' => [
            'name' => 'Rose',
            'ink' => '#43262e',
            'inkSoft' => '#76515b',
            'paper' => '#fff8f8',
            'paperAlt' => '#f7e9ec',
            'accent' => '#b77786',
            'accentSoft' => '#e2b8c2',
            'line' => 'rgba(117, 65, 78, .15)',
            'button' => '#7c4052',
            'buttonHover' => '#5c2c3b',
            'muted' => '#977d84',
        ],

        'emerald' => [
            'name' => 'Emerald',
            'ink' => '#173b35',
            'inkSoft' => '#4e6e66',
            'paper' => '#f7fbf8',
            'paperAlt' => '#e4f0eb',
            'accent' => '#9caa77',
            'accentSoft' => '#cbd6b4',
            'line' => 'rgba(23, 59, 53, .14)',
            'button' => '#214f45',
            'buttonHover' => '#12382f',
            'muted' => '#718780',
        ],

        'midnight' => [
            'name' => 'Midnight',
            'ink' => '#202938',
            'inkSoft' => '#5d687a',
            'paper' => '#f7f9fc',
            'paperAlt' => '#e8edf5',
            'accent' => '#b7a17c',
            'accentSoft' => '#d8c8ac',
            'line' => 'rgba(32, 41, 56, .14)',
            'button' => '#283447',
            'buttonHover' => '#172131',
            'muted' => '#7c8798',
        ],

        'cream' => [
            'name' => 'Cream',
            'ink' => '#463b2e',
            'inkSoft' => '#776c5d',
            'paper' => '#fffdf8',
            'paperAlt' => '#f2ecdf',
            'accent' => '#b3925f',
            'accentSoft' => '#dbc9a8',
            'line' => 'rgba(70, 59, 46, .14)',
            'button' => '#66513a',
            'buttonHover' => '#443321',
            'muted' => '#938776',
        ],
    ];

    $theme = $themeMap[$activeTheme] ?? $themeMap['maroon_gold'];

    /*
    |--------------------------------------------------------------------------
    | IMAGE HELPERS
    |--------------------------------------------------------------------------
    */

    $resolveImage = function ($item, $fallback = null) {
        if (!$item) {
            return $fallback;
        }

        $possibleFields = [
            'image',
            'image_path',
            'thumbnail',
            'thumbnail_path',
            'photo',
            'photo_path',
            'cover',
            'cover_image',
            'banner',
            'banner_image',
        ];

        foreach ($possibleFields as $field) {
            $value = data_get($item, $field);

            if (!$value) {
                continue;
            }

            if (Str::startsWith($value, [
                'http://',
                'https://',
                '//',
                'data:image',
            ])) {
                return $value;
            }

            if (Str::startsWith($value, '/')) {
                return $value;
            }

            if (Str::startsWith($value, 'storage/')) {
                return asset($value);
            }

            if (Str::startsWith($value, 'public/')) {
                return asset(Str::after($value, 'public/'));
            }

            try {
                return Storage::url($value);
            } catch (\Throwable $exception) {
                return asset('storage/' . ltrim($value, '/'));
            }
        }

        return $fallback;
    };

    $formatPrice = function ($price) {
        if ($price === null || $price === '') {
            return 'Rp0';
        }

        return 'Rp' . number_format((float) $price, 0, ',', '.');
    };

    $productName = function ($product) {
        return data_get($product, 'name')
            ?? data_get($product, 'title')
            ?? data_get($product, 'product_name')
            ?? 'Koleksi Zalina';
    };

    $productPrice = function ($product) {
        return data_get($product, 'price')
            ?? data_get($product, 'selling_price')
            ?? data_get($product, 'harga')
            ?? 0;
    };

    $productOldPrice = function ($product) {
        return data_get($product, 'old_price')
            ?? data_get($product, 'original_price')
            ?? data_get($product, 'compare_price')
            ?? data_get($product, 'discount_price');
    };

    $productSlug = function ($product) {
        return data_get($product, 'slug')
            ?? data_get($product, 'id');
    };

    $categoryName = function ($category) {
        return data_get($category, 'name')
            ?? data_get($category, 'title')
            ?? data_get($category, 'category_name')
            ?? 'Collection';
    };

    $categorySlug = function ($category) {
        return data_get($category, 'slug')
            ?? data_get($category, 'id');
    };

    $categoryImage = function ($category) use ($resolveImage) {
        // TIDAK ada lagi fallback ke aset pihak ketiga (Unsplash dsb).
        // Jika kategori belum memiliki gambar dari panel admin, kembalikan
        // null agar tampilan memakai placeholder bermerek Zalina Fashion.
        return $resolveImage($category, null);
    };

    $categoryDescription = function ($category) {
        return data_get($category, 'description')
            ?? data_get($category, 'short_description')
            ?? data_get($category, 'summary')
            ?? null;
    };

    $categoryProductCount = function ($category) {
        return data_get($category, 'products_count')
            ?? data_get($category, 'total_products')
            ?? data_get($category, 'product_count')
            ?? null;
    };

    $productImage = function ($product) use ($resolveImage) {
        return $resolveImage(
            $product,
            'https://images.unsplash.com/photo-1584187837178-7d8e3b8e6e9a?auto=format&fit=crop&w=1000&q=85'
        );
    };

    /*
    |--------------------------------------------------------------------------
    | FALLBACK CONTENT
    |--------------------------------------------------------------------------
    */

    $fallbackCategories = collect([
        [
            'id' => 1,
            'name' => 'Hijab',
            'slug' => 'hijab',
            'description' => 'Koleksi hijab lembut dengan jatuhan kain yang elegan.',
        ],
        [
            'id' => 2,
            'name' => 'Gamis',
            'slug' => 'gamis',
            'description' => 'Gamis siluet anggun untuk setiap momen spesialmu.',
        ],
        [
            'id' => 3,
            'name' => 'Aksesoris',
            'slug' => 'aksesoris',
            'description' => 'Detail pelengkap penampilan yang penuh karakter.',
        ],
    ]);

    $fallbackProducts = collect([
        [
            'id' => 1,
            'name' => 'Signature Voile',
            'slug' => 'signature-voile',
            'price' => 129000,
            'old_price' => 159000,
            'category' => 'Hijab',
            'image' => 'https://images.unsplash.com/photo-1584187837178-7d8e3b8e6e9a?auto=format&fit=crop&w=1000&q=85',
        ],
        [
            'id' => 2,
            'name' => 'Soft Elegance',
            'slug' => 'soft-elegance',
            'price' => 149000,
            'old_price' => 179000,
            'category' => 'Hijab',
            'image' => 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?auto=format&fit=crop&w=1000&q=85',
        ],
        [
            'id' => 3,
            'name' => 'Aurelia Dress',
            'slug' => 'aurelia-dress',
            'price' => 289000,
            'old_price' => 349000,
            'category' => 'Gamis',
            'image' => 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=1000&q=85',
        ],
        [
            'id' => 4,
            'name' => 'Luna Inner',
            'slug' => 'luna-inner',
            'price' => 69000,
            'old_price' => null,
            'category' => 'Aksesoris',
            'image' => 'https://images.unsplash.com/photo-1611652022419-a9419f74343d?auto=format&fit=crop&w=1000&q=85',
        ],
        [
            'id' => 5,
            'name' => 'Satin Grace',
            'slug' => 'satin-grace',
            'price' => 169000,
            'old_price' => 199000,
            'category' => 'Hijab',
            'image' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=1000&q=85',
        ],
        [
            'id' => 6,
            'name' => 'Noura Set',
            'slug' => 'noura-set',
            'price' => 319000,
            'old_price' => null,
            'category' => 'Gamis',
            'image' => 'https://images.unsplash.com/photo-1485968579580-b6d095142e6e?auto=format&fit=crop&w=1000&q=85',
        ],
    ]);

    $categoriesView = $categoriesCollection->isNotEmpty()
        ? $categoriesCollection
        : $fallbackCategories;

    $productsView = $productsCollection->isNotEmpty()
        ? $productsCollection
        : $fallbackProducts;

    $featuredProducts = $productsView
        ->filter(function ($product) {
            return (bool) (
                data_get($product, 'is_featured')
                ?? data_get($product, 'featured')
                ?? true
            );
        })
        ->values();

    if ($featuredProducts->isEmpty()) {
        $featuredProducts = $productsView->values();
    }

    $newProducts = $productsView
        ->sortByDesc(function ($product) {
            return data_get($product, 'created_at')
                ?? data_get($product, 'id')
                ?? 0;
        })
        ->values();

    $activeDiscounts = $discountsCollection
        ->filter(function ($discount) {
            $isActive = data_get($discount, 'is_active');

            if ($isActive === null) {
                $isActive = data_get($discount, 'active');
            }

            if ($isActive === null) {
                $isActive = true;
            }

            return (bool) $isActive;
        })
        ->values();

    /*
    |--------------------------------------------------------------------------
    | HERO SLIDES
    |--------------------------------------------------------------------------
    */

    $heroSlides = $sliders->isNotEmpty()
        ? $sliders
        : collect([
            [
                'title' => 'Elegance in Every Drape',
                'subtitle' => 'The Signature Series',
                'description' => 'Siluet lembut, material pilihan, dan detail yang dirancang untuk menemani setiap langkahmu.',
                'image' => 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?auto=format&fit=crop&w=2200&q=90',
                'button_text' => 'Jelajahi Koleksi',
                'button_url' => route('shop'),
            ],
            [
                'title' => 'Softness Meets Confidence',
                'subtitle' => 'Soft Elegance',
                'description' => 'Koleksi bernuansa tenang untuk penampilan yang effortless, refined, dan berkarakter.',
                'image' => 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=2200&q=90',
                'button_text' => 'Lihat Koleksi',
                'button_url' => route('shop'),
            ],
            [
                'title' => 'Made for Your Everyday',
                'subtitle' => 'Daily Essential',
                'description' => 'Potongan versatile dan warna yang mudah dipadukan untuk hari-hari yang penuh aktivitas.',
                'image' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=2200&q=90',
                'button_text' => 'Belanja Sekarang',
                'button_url' => route('shop'),
            ],
        ]);

    /*
    |--------------------------------------------------------------------------
    | VIEW HELPERS
    |--------------------------------------------------------------------------
    */

    $safeRoute = function ($routeName, $parameters = []) {
        try {
            return route($routeName, $parameters);
        } catch (\Throwable $exception) {
            return '#';
        }
    };

    $shopUrl = $safeRoute('shop');

    $productUrl = function ($product) use ($safeRoute, $productSlug) {
        return $safeRoute('product.show', [
            'product' => $productSlug($product),
        ]);
    };

    $categoryUrl = function ($category) use ($shopUrl, $categorySlug) {
        $identifier = $categorySlug($category);

        if (!$identifier) {
            return $shopUrl;
        }

        try {
            return route('shop', [
                'category' => $identifier,
            ]);
        } catch (\Throwable $exception) {
            return $shopUrl . '?category=' . urlencode((string) $identifier);
        }
    };

    $discountValue = function ($discount) {
        $type = data_get($discount, 'type')
            ?? data_get($discount, 'discount_type')
            ?? 'percentage';

        $value = data_get($discount, 'value')
            ?? data_get($discount, 'discount_value')
            ?? 0;

        if (in_array($type, ['percentage', 'percent', 'persentase'])) {
            return rtrim(rtrim(number_format((float) $value, 0, ',', '.'), '0'), ',') . '%';
        }

        return 'Rp' . number_format((float) $value, 0, ',', '.');
    };

    $discountImage = function ($discount) use ($resolveImage) {
        return $resolveImage(
            $discount,
            'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=1200&q=85'
        );
    };

    $discountTitle = function ($discount) {
        return data_get($discount, 'name')
            ?? data_get($discount, 'title')
            ?? data_get($discount, 'description')
            ?? 'Special Offer';
    };

    $discountCode = function ($discount) {
        return data_get($discount, 'code')
            ?? data_get($discount, 'coupon_code')
            ?? 'ZALINA';
    };

    $discountType = function ($discount) {
        return data_get($discount, 'card_style')
            ?? data_get($discount, 'style')
            ?? 'ticket';
    };

    /*
    |--------------------------------------------------------------------------
    | CSS VARIABLES
    |--------------------------------------------------------------------------
    */

    $cssVariables = [
        '--zf-ink' => $theme['ink'],
        '--zf-ink-soft' => $theme['inkSoft'],
        '--zf-paper' => $theme['paper'],
        '--zf-paper-alt' => $theme['paperAlt'],
        '--zf-accent' => $theme['accent'],
        '--zf-accent-soft' => $theme['accentSoft'],
        '--zf-line' => $theme['line'],
        '--zf-button' => $theme['button'],
        '--zf-button-hover' => $theme['buttonHover'],
        '--zf-muted' => $theme['muted'],
    ];

    $cssVariableString = collect($cssVariables)
        ->map(fn ($value, $key) => $key . ':' . $value)
        ->implode(';');
@endphp

<style>
    /* =========================================================================
       ROOT
       ========================================================================= */

    :root {
        {!! $cssVariableString !!}
        --zf-serif: "Cormorant Garamond", "Playfair Display", Georgia, serif;
        --zf-sans: "Inter", "Helvetica Neue", Arial, sans-serif;
        --zf-display: "Bodoni Moda", "Cormorant Garamond", Georgia, serif;
        --zf-radius-sm: 8px;
        --zf-radius-md: 16px;
        --zf-radius-lg: 28px;
        --zf-radius-xl: 40px;
        --zf-shadow-soft: 0 20px 70px rgba(40, 23, 28, .07);
        --zf-shadow-card: 0 12px 45px rgba(40, 23, 28, .06);
        --zf-transition: 420ms cubic-bezier(.22, .61, .36, 1);
    }

    .zf-page {
        background:
            radial-gradient(
                circle at 8% 12%,
                color-mix(in srgb, var(--zf-accent-soft) 14%, transparent),
                transparent 28rem
            ),
            var(--zf-paper);
        color: var(--zf-ink);
        font-family: var(--zf-sans);
        overflow: hidden;
    }

    .zf-page *,
    .zf-page *::before,
    .zf-page *::after {
        box-sizing: border-box;
    }

    .zf-page a {
        color: inherit;
        text-decoration: none;
    }

    .zf-page button,
    .zf-page a {
        -webkit-tap-highlight-color: transparent;
    }

    .zf-page img {
        display: block;
        max-width: 100%;
    }

    .zf-page button {
        font: inherit;
    }

    .zf-container {
        width: min(100% - 48px, 1440px);
        margin-inline: auto;
    }

    .zf-container-wide {
        width: min(100% - 48px, 1680px);
        margin-inline: auto;
    }

    .zf-section {
        position: relative;
        padding-block: 112px;
    }

    .zf-section-tight {
        padding-block: 76px;
    }

    .zf-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        color: var(--zf-accent);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .24em;
        line-height: 1.4;
        text-transform: uppercase;
    }

    .zf-eyebrow::before {
        content: "";
        width: 34px;
        height: 1px;
        background: currentColor;
        opacity: .7;
    }

    .zf-display-title {
        margin: 0;
        font-family: var(--zf-display);
        font-size: clamp(46px, 6vw, 100px);
        font-weight: 400;
        letter-spacing: -.065em;
        line-height: .91;
    }

    .zf-serif-title {
        margin: 0;
        font-family: var(--zf-serif);
        font-size: clamp(42px, 5vw, 76px);
        font-weight: 400;
        letter-spacing: -.055em;
        line-height: .95;
    }

    .zf-section-title {
        margin: 0;
        font-family: var(--zf-serif);
        font-size: clamp(42px, 4.5vw, 72px);
        font-weight: 400;
        letter-spacing: -.055em;
        line-height: .95;
    }

    .zf-section-copy {
        max-width: 520px;
        margin-top: 24px;
        color: var(--zf-muted);
        font-size: 14px;
        font-weight: 400;
        line-height: 1.9;
    }

    .zf-small-copy {
        color: var(--zf-muted);
        font-size: 12px;
        line-height: 1.8;
    }

    .zf-rule {
        width: 100%;
        height: 1px;
        background: var(--zf-line);
    }

    .zf-button {
        position: relative;
        display: inline-flex;
        min-height: 52px;
        align-items: center;
        justify-content: center;
        gap: 20px;
        padding: 0 25px;
        border: 1px solid transparent;
        border-radius: 999px;
        background: var(--zf-button);
        color: #fff;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .17em;
        line-height: 1;
        text-transform: uppercase;
        transition:
            background var(--zf-transition),
            transform var(--zf-transition),
            box-shadow var(--zf-transition);
    }

    .zf-button:hover {
        background: var(--zf-button-hover);
        box-shadow: 0 12px 35px rgba(40, 23, 28, .15);
        transform: translateY(-3px);
    }

    .zf-button:focus-visible {
        outline: 2px solid var(--zf-accent);
        outline-offset: 5px;
    }

    .zf-button * {
        color: inherit;
    }

    .zf-button-outline {
        border-color: var(--zf-ink);
        background: transparent;
        color: var(--zf-ink);
    }

    .zf-button-outline .zf-button-arrow {
        background: color-mix(in srgb, var(--zf-ink) 10%, transparent);
    }

    .zf-button-outline:hover {
        border-color: var(--zf-button);
        background: var(--zf-button);
        color: #fff;
    }

    .zf-button-outline:hover .zf-button-arrow {
        background: rgba(255, 255, 255, .16);
    }

    .zf-button-arrow {
        display: inline-flex;
        width: 24px;
        height: 24px;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(255, 255, 255, .16);
        font-size: 14px;
        transition: transform var(--zf-transition);
    }

    .zf-button:hover .zf-button-arrow {
        transform: translateX(4px);
    }

    .zf-image-wrap {
        position: relative;
        overflow: hidden;
        background: var(--zf-paper-alt);
    }

    .zf-image-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 1.2s cubic-bezier(.22, .61, .36, 1);
    }

    .zf-image-wrap:hover img {
        transform: scale(1.045);
    }

    .zf-label {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 25px;
        padding: 0 10px;
        border: 1px solid var(--zf-line);
        border-radius: 999px;
        color: var(--zf-muted);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .13em;
        text-transform: uppercase;
    }

    .zf-section-heading {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 32px;
        margin-bottom: 48px;
    }

    .zf-section-heading-copy {
        max-width: 600px;
    }

    .zf-section-heading-action {
        flex: 0 0 auto;
    }

    .zf-link-arrow {
        display: inline-flex;
        align-items: center;
        gap: 15px;
        border-bottom: 1px solid var(--zf-ink);
        padding-bottom: 8px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .15em;
        text-transform: uppercase;
        transition:
            color var(--zf-transition),
            border-color var(--zf-transition),
            gap var(--zf-transition);
    }

    .zf-link-arrow:hover {
        gap: 22px;
        color: var(--zf-accent);
        border-color: var(--zf-accent);
    }

    .zf-link-arrow span:last-child {
        font-size: 16px;
        font-weight: 400;
    }

    /* =========================================================================
       ANNOUNCEMENT BAR
       ========================================================================= */

    .zf-announcement {
        position: relative;
        z-index: 30;
        min-height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 9px 18px;
        color: #fff;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .17em;
        line-height: 1.4;
        text-align: center;
        text-transform: uppercase;
    }

    .zf-announcement strong {
        color: var(--zf-accent-soft);
        font-weight: 800;
    }

    /* =========================================================================
       MAIN HEADER
       ========================================================================= */

    .zf-header {
        position: relative;
        z-index: 25;
        background: var(--zf-paper);
        border-bottom: 1px solid var(--zf-line);
    }

    .zf-header-inner {
        min-height: 96px;
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        gap: 32px;
    }

    .zf-header-left,
    .zf-header-right {
        display: flex;
        align-items: center;
        gap: 24px;
    }

    .zf-header-right {
        justify-content: flex-end;
    }

    .zf-header-link {
        position: relative;
        color: var(--zf-ink-soft);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
        transition: color var(--zf-transition);
    }

    .zf-header-link::after {
        content: "";
        position: absolute;
        right: 0;
        bottom: -8px;
        left: 0;
        height: 1px;
        background: var(--zf-accent);
        transform: scaleX(0);
        transform-origin: right;
        transition: transform var(--zf-transition);
    }

    .zf-header-link:hover {
        color: var(--zf-ink);
    }

    .zf-header-link:hover::after {
        transform: scaleX(1);
        transform-origin: left;
    }

    .zf-brand {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 2px;
        text-align: center;
    }

    .zf-brand-main {
        font-family: var(--zf-display);
        font-size: 37px;
        font-weight: 500;
        letter-spacing: -.08em;
        line-height: .85;
    }

    .zf-brand-sub {
        color: var(--zf-accent);
        font-size: 8px;
        font-weight: 800;
        letter-spacing: .42em;
        line-height: 1;
        text-transform: uppercase;
        transform: translateX(2px);
    }

    .zf-header-icon {
        display: inline-flex;
        width: 35px;
        height: 35px;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--zf-line);
        border-radius: 50%;
        color: var(--zf-ink);
        transition:
            background var(--zf-transition),
            color var(--zf-transition),
            transform var(--zf-transition);
    }

    .zf-header-icon:hover {
        background: var(--zf-button);
        color: #fff;
        transform: translateY(-2px);
    }

    .zf-header-icon svg {
        width: 15px;
        height: 15px;
        stroke-width: 1.5;
    }

    .zf-cart-count {
        position: absolute;
        top: -5px;
        right: -5px;
        display: inline-flex;
        min-width: 17px;
        height: 17px;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
        border-radius: 50%;
        background: var(--zf-accent);
        color: #fff;
        font-size: 8px;
        font-weight: 800;
    }

    .zf-icon-button {
        position: relative;
        display: inline-flex;
        border: 0;
        background: transparent;
        padding: 0;
    }

    .zf-mobile-menu-button {
        display: none;
    }

    /* =========================================================================
       HERO — V2 REDESIGN
       ------------------------------------------------------------------------
       Kenapa dirombak:
       - Versi lama menghitung aspect-ratio kontainer gambar secara dinamis
         lewat JS (`syncHeroRatio`) setiap gambar selesai loading. Efeknya:
         di awal render kontainer memakai rasio fallback (4/5), lalu begitu
         gambar asli selesai dimuat rasionya diganti paksa → kontainer
         "melompat" membesar/mengecil di depan mata user. Ini root cause
         dari bug "gambar membesar lalu mengecil saat refresh".
       - Solusi: rasio kontainer SEKARANG TETAP (fixed di CSS per breakpoint),
         ditentukan sekali di awal, tidak pernah dihitung ulang oleh JS.
         `object-fit: cover` menjamin gambar apa pun tetap mengisi penuh
         tanpa distorsi. Sedikit crop pada gambar dengan rasio ekstrem adalah
         trade-off yang normal dan jauh lebih baik daripada layout yang
         meloncat-loncat.
       - Layout mobile dirombak total: alih-alih gambar lalu ada jarak kosong
         besar sebelum judul, sekarang konten disajikan sebagai kartu yang
         "menumpuk" di atas bagian bawah gambar (gaya bottom-sheet editorial),
         jadi tidak ada lagi celah kosong.
       - Tombol CTA didesain ulang total, bentuknya dua-segmen (label +
         kotak ikon terpisah) dengan animasi sapuan warna — tidak lagi pil
         polos seperti tombol default di situs.
       ========================================================================= */

    .zf-hero {
        position: relative;
        width: 100%;
        padding-block: clamp(0px, 2vw, 56px);
        isolation: isolate;
    }

    .zf-hero-glow {
        position: absolute;
        z-index: 0;
        top: -12%;
        right: -8%;
        width: 46%;
        aspect-ratio: 1;
        border-radius: 50%;
        background: radial-gradient(
            circle,
            color-mix(in srgb, var(--zf-accent) 30%, transparent),
            transparent 70%
        );
        filter: blur(70px);
        pointer-events: none;
    }

    .zf-hero-grid {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, .86fr) minmax(0, 1.14fr);
        align-items: center;
        gap: clamp(28px, 4vw, 72px);
        width: min(100% - 48px, 1440px);
        margin-inline: auto;
    }

    /* ---------- COPY (kiri) ---------- */

    .zf-hero-copy {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .zf-hero-copy-inner {
        width: 100%;
        max-width: 520px;
    }

    .zf-hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        margin-bottom: clamp(20px, 3vw, 30px);
        color: var(--zf-accent);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .23em;
        text-transform: uppercase;
    }

    .zf-hero-kicker::before {
        content: "";
        width: 40px;
        height: 1px;
        background: currentColor;
    }

    .zf-hero-kicker-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: var(--zf-accent);
        box-shadow: 0 0 0 4px color-mix(in srgb, var(--zf-accent) 20%, transparent);
        animation: zfHeroPulse 2.4s ease-in-out infinite;
    }

    @keyframes zfHeroPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: .45; transform: scale(1.35); }
    }

    .zf-hero-title {
        max-width: 600px;
        margin: 0;
        font-family: var(--zf-display);
        font-size: clamp(52px, 6.6vw, 104px);
        font-weight: 400;
        letter-spacing: -.07em;
        line-height: .88;
    }

    .zf-hero-title em {
        display: block;
        font-style: italic;
        font-family: var(--zf-serif);
        font-size: .88em;
        letter-spacing: -.055em;
        background: linear-gradient(
            100deg,
            var(--zf-accent) 0%,
            var(--zf-accent-soft) 45%,
            var(--zf-accent) 100%
        );
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .zf-hero-description {
        max-width: 410px;
        margin-top: clamp(20px, 3vw, 32px);
        color: var(--zf-muted);
        font-size: 13.5px;
        line-height: 1.9;
    }

    .zf-hero-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 22px;
        margin-top: clamp(26px, 4vw, 40px);
    }

    /* ---------- CTA utama: bentuk baru, dua segmen (label + kotak ikon) ----------
       Catatan penting: TIDAK memakai isolation/z-index negatif untuk efek
       hover (trik lama itu yang membuat tombol sempat tidak kelihatan di
       sejumlah browser saat pertama kali render). Sekarang hover cuma
       transisi background-color biasa — sederhana dan pasti kelihatan sejak
       render pertama, tanpa bergantung pada JS apa pun. */

    .zf-hero-cta {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 0;
        min-height: 58px;
        padding: 6px 6px 6px 28px;
        border-radius: 999px 16px 16px 999px;
        background: var(--zf-ink);
        color: #fff;
        opacity: 1;
        visibility: visible;
        box-shadow: 0 16px 34px -14px color-mix(in srgb, var(--zf-ink) 65%, transparent);
        transition: background .35s ease, transform .35s ease, box-shadow .35s ease;
    }

    .zf-hero-cta-label {
        padding-right: 22px;
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .16em;
        line-height: 1;
        text-transform: uppercase;
        white-space: nowrap;
        transition: color .3s ease;
    }

    .zf-hero-cta-icon {
        display: inline-flex;
        width: 46px;
        height: 46px;
        flex: none;
        align-items: center;
        justify-content: center;
        border-radius: 13px;
        background: var(--zf-accent);
        color: var(--zf-ink);
        font-size: 16px;
        transition: transform .35s ease, background .3s ease, color .3s ease;
    }

    .zf-hero-cta:hover {
        background: var(--zf-accent);
        transform: translateY(-3px);
        box-shadow: 0 18px 38px -14px color-mix(in srgb, var(--zf-accent) 55%, transparent);
    }

    .zf-hero-cta:hover .zf-hero-cta-label {
        color: var(--zf-ink);
    }

    .zf-hero-cta:hover .zf-hero-cta-icon {
        background: var(--zf-ink);
        color: #fff;
        transform: rotate(45deg);
    }

    .zf-hero-cta:focus-visible {
        outline: 2px solid var(--zf-accent);
        outline-offset: 4px;
    }

    /* ---------- CTA sekunder: teks bergaris bawah ---------- */

    .zf-hero-link-arrow {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 5px;
        color: var(--zf-ink);
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .15em;
        text-transform: uppercase;
    }

    .zf-hero-link-arrow::after {
        content: "";
        position: absolute;
        left: 0;
        right: 100%;
        bottom: 0;
        height: 1px;
        background: var(--zf-accent);
        transition: right .4s cubic-bezier(.22, .61, .36, 1);
    }

    .zf-hero-link-arrow:hover::after {
        right: 0;
    }

    .zf-hero-link-arrow-icon {
        display: inline-flex;
        width: 26px;
        height: 26px;
        align-items: center;
        justify-content: center;
        border: 1px solid color-mix(in srgb, var(--zf-ink) 35%, transparent);
        border-radius: 50%;
        font-size: 12px;
        transition:
            transform .35s cubic-bezier(.22, .61, .36, 1),
            background .3s ease,
            color .3s ease,
            border-color .3s ease;
    }

    .zf-hero-link-arrow:hover {
        color: var(--zf-accent);
    }

    .zf-hero-link-arrow:hover .zf-hero-link-arrow-icon {
        background: var(--zf-accent);
        border-color: var(--zf-accent);
        color: #fff;
        transform: translate(2px, -2px) rotate(45deg);
    }

    .zf-hero-meta {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-top: clamp(36px, 5vw, 55px);
        color: var(--zf-muted);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .15em;
        text-transform: uppercase;
    }

    .zf-hero-meta-line {
        width: 45px;
        height: 1px;
        background: var(--zf-line);
    }

    /* ---------- VISUAL (kanan) — rasio tetap + tinggi dibatasi, tidak lagi
       dihitung oleh JS. `max-height` penting di sini: dulu di layar desktop
       lebar, kolom gambar bisa sangat lebar sehingga aspect-ratio 4:5 membuat
       gambar jadi SANGAT tinggi dan tata letak terlihat "rusak"/berantakan.
       Dengan max-height, tinggi gambar tetap masuk akal di layar lebar
       sementara mobile tetap dapat rasio potretnya sendiri. ---------- */

    .zf-hero-visual {
        position: relative;
        z-index: 1;
        width: 100%;
        aspect-ratio: 4 / 5;
        max-height: 620px;
        overflow: hidden;
        border-radius: clamp(18px, 2.4vw, 30px);
        background:
            linear-gradient(
                160deg,
                var(--zf-paper-alt),
                color-mix(in srgb, var(--zf-accent-soft) 35%, var(--zf-paper-alt))
            );
        box-shadow:
            0 30px 80px -24px color-mix(in srgb, var(--zf-ink) 45%, transparent),
            0 1px 0 rgba(255, 255, 255, .35) inset;
    }

    .zf-hero-image {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
    }

    .zf-hero-image-media {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center 22%;
        transition: transform 1.6s cubic-bezier(.22, .61, .36, 1);
    }

    .zf-hero:hover .zf-hero-image-media {
        transform: scale(1.035);
    }

    .zf-hero-image-fallback {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, var(--zf-accent), var(--zf-ink));
    }

    .zf-hero-overlay {
        position: absolute;
        z-index: 2;
        inset: 0;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0) 58%, rgba(20, 10, 12, .55) 100%);
        pointer-events: none;
    }

    .zf-hero-badge {
        position: absolute;
        z-index: 4;
        top: 24px;
        left: 24px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border: 1px solid rgba(255, 255, 255, .35);
        border-radius: 999px;
        background: rgba(255, 255, 255, .14);
        backdrop-filter: blur(14px);
        color: #fff;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .zf-hero-badge-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(255, 255, 255, .3);
    }

    .zf-hero-navigation {
        position: absolute;
        z-index: 5;
        top: 22px;
        right: 22px;
        display: flex;
        gap: 8px;
    }

    .zf-hero-nav {
        display: inline-flex;
        width: 42px;
        height: 42px;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255, 255, 255, .45);
        border-radius: 50%;
        background: rgba(255, 255, 255, .1);
        color: #fff;
        backdrop-filter: blur(10px);
        transition: background var(--zf-transition), color var(--zf-transition);
    }

    .zf-hero-nav:hover {
        background: #fff;
        color: var(--zf-ink);
    }

    .zf-hero-nav svg {
        width: 15px;
        height: 15px;
        stroke-width: 1.5;
    }

    .zf-hero-controls {
        position: absolute;
        z-index: 5;
        left: 24px;
        right: 24px;
        bottom: 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .zf-hero-counter {
        display: flex;
        align-items: baseline;
        gap: 6px;
        color: #fff;
        font-family: var(--zf-display);
    }

    .zf-hero-counter-current {
        font-size: 22px;
        line-height: 1;
    }

    .zf-hero-counter-sep {
        font-size: 13px;
        opacity: .5;
    }

    .zf-hero-counter-total {
        font-size: 13px;
        opacity: .7;
    }

    .zf-hero-dots {
        display: flex;
        gap: 7px;
    }

    .zf-hero-dot {
        width: 26px;
        height: 2px;
        border: 0;
        background: rgba(255, 255, 255, .4);
        cursor: pointer;
        transition: width var(--zf-transition), background var(--zf-transition);
    }

    .zf-hero-dot.active {
        width: 46px;
        background: #fff;
    }

    /* ---------- RESPONSIVE ---------- */

    @media (max-width: 1180px) {
        .zf-hero-grid {
            grid-template-columns: minmax(0, .92fr) minmax(0, 1.08fr);
            gap: clamp(24px, 3vw, 48px);
        }

        .zf-hero-title {
            font-size: clamp(48px, 6vw, 82px);
        }

        .zf-hero-visual {
            max-height: 540px;
        }
    }

    /* Di bawah 900px, hero berubah jadi tumpukan "bottom-sheet": gambar penuh
       di atas (rasio tetap, tidak ada lompatan), lalu kartu konten yang
       sengaja menumpuk di atas bagian bawah gambar — bukan lagi celah kosong
       antara gambar dan judul seperti versi sebelumnya. */
    @media (max-width: 900px) {
        .zf-hero {
            padding-block: 0 40px;
        }

        .zf-hero-grid {
            grid-template-columns: minmax(0, 1fr);
            gap: 0;
            width: 100%;
        }

        .zf-hero-visual {
            order: -1;
            width: 100%;
            aspect-ratio: 5 / 6;
            max-height: 62vh;
            min-height: 380px;
            border-radius: 0 0 clamp(22px, 6vw, 32px) clamp(22px, 6vw, 32px);
        }

        .zf-hero-copy {
            order: 2;
            position: relative;
            z-index: 2;
            margin-top: -34px;
            padding-inline: 18px;
        }

        .zf-hero-copy-inner {
            max-width: 100%;
            background: var(--zf-paper);
            border-radius: clamp(18px, 5vw, 26px);
            padding: 30px 22px 26px;
            box-shadow: 0 26px 60px -28px color-mix(in srgb, var(--zf-ink) 55%, transparent);
        }

        .zf-hero-title {
            max-width: 100%;
            font-size: clamp(38px, 9.5vw, 60px);
        }

        .zf-hero-description {
            max-width: 100%;
        }

        .zf-hero-glow {
            display: none;
        }

        .zf-hero-controls {
            bottom: 46px;
        }
    }

    @media (max-width: 560px) {
        .zf-hero-visual {
            max-height: 56vh;
            min-height: 340px;
        }

        .zf-hero-copy {
            margin-top: -26px;
            padding-inline: 14px;
        }

        .zf-hero-copy-inner {
            padding: 26px 18px 22px;
        }

        .zf-hero-title {
            font-size: clamp(34px, 12vw, 48px);
        }

        .zf-hero-description {
            font-size: 12.5px;
        }

        .zf-hero-actions {
            flex-direction: column;
            align-items: stretch;
            gap: 16px;
            width: 100%;
        }

        .zf-hero-cta {
            width: 100%;
            justify-content: space-between;
        }

        .zf-hero-link-arrow {
            justify-content: center;
        }

        .zf-hero-badge {
            top: 14px;
            left: 14px;
            padding: 8px 12px;
            font-size: 8px;
        }

        .zf-hero-navigation {
            top: 14px;
            right: 14px;
        }

        .zf-hero-nav {
            width: 36px;
            height: 36px;
        }

        .zf-hero-controls {
            left: 14px;
            right: 14px;
            bottom: 40px;
        }

        .zf-hero-counter-current {
            font-size: 18px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .zf-hero-image-media {
            transition: none;
        }

        .zf-hero-kicker-dot {
            animation: none;
        }

        .zf-hero-cta,
        .zf-hero-cta-icon,
        .zf-hero-link-arrow::after,
        .zf-hero-link-arrow-icon {
            transition: none;
        }
    }

    /* =========================================================================
       INTRO STATEMENT
       ========================================================================= */

    .zf-statement {
        padding-block: 130px;
        text-align: center;
    }

    .zf-statement-inner {
        width: min(100%, 980px);
        margin-inline: auto;
    }

    .zf-statement-title {
        margin: 28px 0 0;
        font-family: var(--zf-serif);
        font-size: clamp(42px, 5.8vw, 90px);
        font-weight: 400;
        letter-spacing: -.06em;
        line-height: .98;
    }

    .zf-statement-title em {
        color: var(--zf-accent);
        font-style: italic;
    }

    .zf-statement-copy {
        width: min(100%, 510px);
        margin: 34px auto 0;
        color: var(--zf-muted);
        font-size: 13px;
        line-height: 2;
    }

    .zf-statement-signature {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
        margin-top: 40px;
        color: var(--zf-accent);
        font-family: var(--zf-serif);
        font-size: 24px;
        font-style: italic;
    }

    .zf-statement-signature::before,
    .zf-statement-signature::after {
        content: "";
        width: 48px;
        height: 1px;
        background: var(--zf-line);
    }

    /* =========================================================================
       CATEGORY EDITORIAL
       ========================================================================= */

    .zf-category-grid {
        display: grid;
        grid-template-columns: 1.2fr .8fr .8fr;
        gap: 18px;
    }

    .zf-category-card {
        position: relative;
        min-height: 570px;
        overflow: hidden;
        background: var(--zf-paper-alt);
    }

    .zf-category-card:nth-child(2),
    .zf-category-card:nth-child(3) {
        min-height: 570px;
    }

    .zf-category-card::after {
        position: absolute;
        inset: 0;
        content: "";
        background:
            linear-gradient(
                180deg,
                transparent 35%,
                rgba(26, 18, 20, .72) 100%
            );
        pointer-events: none;
    }

    .zf-category-card-image {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 1.3s cubic-bezier(.22, .61, .36, 1);
    }

    .zf-category-card:hover .zf-category-card-image {
        transform: scale(1.055);
    }

    .zf-category-card-content {
        position: absolute;
        z-index: 2;
        right: 30px;
        bottom: 30px;
        left: 30px;
        color: #fff;
    }

    .zf-category-card-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 10px;
        background:
            radial-gradient(
                circle at 50% 15%,
                rgba(218, 183, 134, .18),
                transparent 40%
            ),
            linear-gradient(150deg, #5b293b, #28151d);
        color: var(--zf-accent-soft, #d8b78a);
    }

    .zf-category-card-fallback-mark {
        font-family: var(--zf-display);
        font-size: 92px;
        line-height: 1;
    }

    .zf-category-card-fallback small {
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .35em;
    }

    .zf-category-card-index {
        margin-bottom: 12px;
        color: rgba(255, 255, 255, .65);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .2em;
    }

    .zf-category-card-count {
        color: rgba(255, 255, 255, .5);
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: none;
    }

    .zf-category-card-desc {
        max-width: 320px;
        margin: 10px 0 0;
        color: rgba(255, 255, 255, .72);
        font-size: 12px;
        font-weight: 400;
        line-height: 1.7;
        letter-spacing: 0;
        text-transform: none;
    }

    .zf-category-card-title {
        margin: 0;
        font-family: var(--zf-serif);
        font-size: clamp(34px, 3.5vw, 58px);
        font-weight: 400;
        letter-spacing: -.06em;
        line-height: .9;
    }

    .zf-category-card-link {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        margin-top: 22px;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .15em;
        text-transform: uppercase;
    }

    .zf-category-card-link span:last-child {
        font-size: 17px;
        font-weight: 400;
        transition: transform var(--zf-transition);
    }

    .zf-category-card:hover .zf-category-card-link span:last-child {
        transform: translateX(5px);
    }

    /* =========================================================================
       CATEGORY CARD — SCROLL REVEAL
       Muncul dari tengah (scale-in) lalu gambar "melebar" seperti tirai
       loading-slide yang membuka dari kanan ke kiri. Setiap card tampil
       satu-per-satu (staggered) lewat --zf-cat-delay yang di-set inline
       di Blade, supaya tidak bentrok dengan animasi hover yang sudah ada
       (hover hanya menyentuh transform gambar & panah, bukan opacity/scale
       card itu sendiri).
       ========================================================================= */

    .zf-category-card {
        opacity: 0;
        transform: scale(.92);
        transform-origin: 50% 50%;
        transition:
            opacity 1.1s cubic-bezier(.22, .61, .36, 1),
            transform 1.5s cubic-bezier(.22, .61, .36, 1);
        transition-delay: var(--zf-cat-delay, 0ms);
        will-change: opacity, transform;
    }

    .zf-category-card.zf-cat-revealed {
        opacity: 1;
        transform: scale(1);
    }

    /* Tirai loading-slide: melebar dari kanan menuju kiri */
    .zf-category-card-image {
        clip-path: inset(0 0 0 100%);
        transition:
            transform 1.3s cubic-bezier(.22, .61, .36, 1),
            clip-path 1.3s cubic-bezier(.65, 0, .35, 1);
        transition-delay: 0ms, calc(var(--zf-cat-delay, 0ms) + 200ms);
    }

    .zf-category-card.zf-cat-revealed .zf-category-card-image.zf-cat-img-ready {
        clip-path: inset(0 0 0 0%);
    }

    /* Placeholder shimmer selagi menunggu gambar (berat/lambat) selesai
       dimuat, supaya area card tidak terlihat kosong/mati. */
    .zf-category-card.zf-cat-loading {
        background: var(--zf-paper-alt);
    }

    .zf-category-card.zf-cat-loading::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 1;
        background: linear-gradient(
            110deg,
            transparent 30%,
            rgba(255, 255, 255, .35) 50%,
            transparent 70%
        );
        background-size: 220% 100%;
        animation: zfCatShimmer 1.3s ease-in-out infinite;
        pointer-events: none;
    }

    @keyframes zfCatShimmer {
        0% { background-position: 140% 0; }
        100% { background-position: -60% 0; }
    }

    .zf-category-card-content > * {
        opacity: 0;
        transform: translateY(16px);
        transition:
            opacity .9s ease,
            transform .9s cubic-bezier(.22, .61, .36, 1);
        transition-delay: calc(var(--zf-cat-delay, 0ms) + 850ms);
    }

    .zf-category-card.zf-cat-revealed .zf-category-card-content > * {
        opacity: 1;
        transform: translateY(0);
    }

    .zf-category-card-content > *:nth-child(2) { transition-delay: calc(var(--zf-cat-delay, 0ms) + 950ms); }
    .zf-category-card-content > *:nth-child(3) { transition-delay: calc(var(--zf-cat-delay, 0ms) + 1050ms); }
    .zf-category-card-content > *:nth-child(4) { transition-delay: calc(var(--zf-cat-delay, 0ms) + 1150ms); }

    @media (prefers-reduced-motion: reduce) {
        .zf-category-card,
        .zf-category-card-image,
        .zf-category-card-content > * {
            opacity: 1 !important;
            transform: none !important;
            clip-path: none !important;
            transition: none !important;
        }

        .zf-category-card.zf-cat-loading::before {
            animation: none !important;
            display: none !important;
        }
    }

    /* =========================================================================
       RESPONSIVE FOUNDATION
       ========================================================================= */

    @media (max-width: 1180px) {
        .zf-container,
        .zf-container-wide {
            width: min(100% - 36px, 1440px);
        }

        .zf-header-inner {
            gap: 18px;
        }

        .zf-header-left,
        .zf-header-right {
            gap: 16px;
        }

        .zf-category-grid {
            gap: 12px;
        }

        .zf-category-card {
            min-height: 480px;
        }
    }

    @media (max-width: 900px) {
        .zf-section {
            padding-block: 80px;
        }

        .zf-header-inner {
            min-height: 78px;
            grid-template-columns: 1fr auto 1fr;
        }

        .zf-header-left .zf-header-link,
        .zf-header-right .zf-header-link {
            display: none;
        }

        .zf-mobile-menu-button {
            display: inline-flex;
        }

        .zf-brand-main {
            font-size: 32px;
        }

        .zf-category-grid {
            grid-template-columns: 1fr 1fr;
        }

        .zf-category-card:first-child {
            grid-column: 1 / -1;
            min-height: 560px;
        }

        .zf-category-card:nth-child(2),
        .zf-category-card:nth-child(3) {
            min-height: 420px;
        }
    }

    @media (max-width: 620px) {
        .zf-container,
        .zf-container-wide {
            width: min(100% - 28px, 1440px);
        }

        .zf-section {
            padding-block: 66px;
        }

        .zf-section-heading {
            align-items: flex-start;
            flex-direction: column;
            gap: 22px;
            margin-bottom: 30px;
        }

        .zf-announcement {
            min-height: 32px;
            padding-inline: 12px;
            font-size: 8px;
            letter-spacing: .1em;
        }

        .zf-header-inner {
            min-height: 70px;
            gap: 12px;
        }

        .zf-header-left,
        .zf-header-right {
            gap: 8px;
        }

        .zf-header-icon {
            width: 31px;
            height: 31px;
        }

        .zf-brand-main {
            font-size: 29px;
        }

        .zf-brand-sub {
            font-size: 6px;
        }
            letter-spacing: .32em;
        }

        .zf-statement {
            padding-block: 80px;
        }

        .zf-statement-title {
            font-size: clamp(42px, 11vw, 65px);
        }

        .zf-statement-copy {
            font-size: 12px;
        }

        .zf-category-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .zf-category-card:first-child,
        .zf-category-card:nth-child(2),
        .zf-category-card:nth-child(3) {
            grid-column: auto;
            min-height: 460px;
        }

        .zf-category-card-content {
            right: 24px;
            bottom: 24px;
            left: 24px;
        }

        .zf-category-card-title {
            font-size: 49px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .zf-page *,
        .zf-page *::before,
        .zf-page *::after {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
        }
    }

/* Override final warna persentase / nominal potongan */
article.zf-promo-card.zf-promo-theme-maroon_gold .zf-promo-value {
    color: #ffd166 !important;
}

article.zf-promo-card.zf-promo-theme-rose .zf-promo-value {
    color: #ff4f9a !important;
}

article.zf-promo-card.zf-promo-theme-emerald .zf-promo-value {
    color: #45e0ad !important;
}

article.zf-promo-card.zf-promo-theme-midnight .zf-promo-value {
    color: #63a9ff !important;
}

article.zf-promo-card.zf-promo-theme-cream .zf-promo-value {
    color: #b96f2d !important;
}


/* ============================================================
   MORE PIECES — EDITORIAL STACKED GRID
   ============================================================ */

.zf-more-products-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: clamp(18px, 2.4vw, 34px);
    align-items: start;
}

.zf-more-product {
    position: relative;
    min-width: 0;
    opacity: 0;
    transform: translateY(34px);
    animation: zfStackedCardReveal .9s cubic-bezier(.22, .61, .36, 1) forwards;
}

.zf-more-product:nth-child(1) {
    animation-delay: .08s;
}

.zf-more-product:nth-child(2) {
    animation-delay: .28s;
}

.zf-more-product:nth-child(3) {
    animation-delay: .48s;
}

.zf-more-product:nth-child(4) {
    animation-delay: .68s;
}

.zf-more-product:nth-child(5) {
    animation-delay: .88s;
}

.zf-more-product:nth-child(6) {
    animation-delay: 1.08s;
}

.zf-more-product:nth-child(7) {
    animation-delay: 1.28s;
}

.zf-more-product:nth-child(8) {
    animation-delay: 1.48s;
}

/* Membuat tiga card pertama seperti tumpukan editorial */
.zf-more-product:nth-child(1) {
    transform-origin: center bottom;
}

.zf-more-product:nth-child(2) {
    transform-origin: center bottom;
}

.zf-more-product:nth-child(3) {
    transform-origin: center bottom;
}

.zf-more-product:nth-child(1) .zf-featured-product-image {
    transform: rotate(-2.5deg);
}

.zf-more-product:nth-child(2) .zf-featured-product-image {
    transform: translateY(20px) rotate(1.8deg);
}

.zf-more-product:nth-child(3) .zf-featured-product-image {
    transform: rotate(-1deg);
}

.zf-more-product .zf-featured-product-image {
    display: block;
    position: relative;
    overflow: hidden;
    border-radius: 22px;
    background: #eee5df;
    box-shadow: 0 18px 45px rgba(67, 37, 42, .12);
    transition:
        transform .65s cubic-bezier(.22, .61, .36, 1),
        box-shadow .45s ease;
}

.zf-more-product:hover .zf-featured-product-image {
    transform: translateY(-12px) rotate(0deg) scale(1.025);
    box-shadow: 0 28px 60px rgba(67, 37, 42, .2);
}

.zf-more-product .zf-product-image-wrap {
    position: relative;
    aspect-ratio: 4 / 5;
    overflow: hidden;
}

.zf-more-product .zf-product-image-wrap img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
    transition:
        transform 1s cubic-bezier(.22, .61, .36, 1),
        filter .7s ease;
}

.zf-more-product:hover .zf-product-image-wrap img {
    transform: scale(1.09);
    filter: saturate(1.08) contrast(1.03);
}

.zf-more-product .zf-product-image-overlay {
    position: absolute;
    inset: 0;
    background:
        linear-gradient(
            180deg,
            rgba(49, 27, 32, 0) 42%,
            rgba(49, 27, 32, .48) 100%
        );
    opacity: .65;
    transition: opacity .4s ease;
}

.zf-more-product:hover .zf-product-image-overlay {
    opacity: .9;
}

.zf-more-product .zf-product-index {
    position: absolute;
    top: 16px;
    left: 16px;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 38px;
    height: 30px;
    padding: 0 10px;
    border: 1px solid rgba(255, 255, 255, .55);
    border-radius: 999px;
    background: rgba(255, 255, 255, .16);
    color: #fff;
    font-size: 10px;
    letter-spacing: .15em;
    backdrop-filter: blur(10px);
}

.zf-more-product .zf-product-quick {
    position: absolute;
    right: 16px;
    bottom: 16px;
    z-index: 3;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 11px 15px;
    border-radius: 999px;
    background: rgba(255, 255, 255, .94);
    color: #542b35;
    font-size: 10px;
    letter-spacing: .08em;
    opacity: 0;
    transform: translateY(15px);
    transition:
        opacity .35s ease,
        transform .35s ease;
}

.zf-more-product:hover .zf-product-quick {
    opacity: 1;
    transform: translateY(0);
}

.zf-more-product .zf-featured-product-info {
    padding: 22px 5px 0;
}

.zf-more-product .zf-product-category {
    color: #a1857c;
    font-size: 10px;
    letter-spacing: .16em;
    text-transform: uppercase;
}

.zf-more-product .zf-featured-product-name {
    margin: 9px 0 13px;
    font-family: "Cormorant Garamond", serif;
    font-size: clamp(23px, 2.3vw, 32px);
    font-weight: 500;
    line-height: 1;
    letter-spacing: -.025em;
}

.zf-more-product .zf-featured-product-name a {
    color: #542b35;
    text-decoration: none;
    transition: color .3s ease;
}

.zf-more-product:hover .zf-featured-product-name a {
    color: #b38a45;
}

.zf-more-product .zf-featured-product-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}

.zf-more-product .zf-product-price {
    color: #936b43;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: .02em;
}

.zf-more-product .zf-product-old-price {
    margin-left: 6px;
    color: #b9aaa4;
    font-size: 11px;
    text-decoration: line-through;
}

.zf-more-product .zf-product-arrow {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border: 1px solid rgba(84, 43, 53, .25);
    border-radius: 50%;
    color: #542b35;
    text-decoration: none;
    transition:
        background .3s ease,
        color .3s ease,
        transform .3s ease;
}

.zf-more-product:hover .zf-product-arrow {
    background: #542b35;
    color: #fff;
    transform: rotate(45deg);
}

@keyframes zfStackedCardReveal {
    0% {
        opacity: 0;
        transform: translateY(34px) scale(.96);
    }

    70% {
        opacity: 1;
        transform: translateY(-4px) scale(1.01);
    }

    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@media (max-width: 900px) {
    .zf-more-products-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 24px 18px;
    }

    .zf-more-product:nth-child(2) .zf-featured-product-image {
        transform: translateY(0) rotate(1.8deg);
    }
}

@media (max-width: 560px) {
    .zf-more-products-grid {
        grid-template-columns: 1fr;
        gap: 32px;
    }

    .zf-more-product:nth-child(1) .zf-featured-product-image,
    .zf-more-product:nth-child(2) .zf-featured-product-image,
    .zf-more-product:nth-child(3) .zf-featured-product-image {
        transform: rotate(0);
    }

    .zf-more-product .zf-featured-product-name {
        font-size: 27px;
    }

    .zf-more-product .zf-product-quick {
        display: none;
    }
}

@media (prefers-reduced-motion: reduce) {
    .zf-more-product {
        opacity: 1;
        transform: none;
        animation: none;
    }

    .zf-more-product .zf-featured-product-image,
    .zf-more-product .zf-product-image-wrap img,
    .zf-more-product .zf-product-quick,
    .zf-more-product .zf-product-arrow {
        transition: none;
    }
}


/* ============================================================
   MORE PIECES — SAMAKAN DENGAN CARD PRODUK UTAMA
   ============================================================ */

#more-pieces .zf-more-products-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 24px;
}

#more-pieces .zf-more-product {
    opacity: 1;
    transform: none;
    animation: none;
    min-width: 0;
}

#more-pieces .zf-more-product .zf-featured-product-image {
    display: block;
    overflow: hidden;
    border-radius: var(--zf-radius-md, 16px);
    background: var(--zf-surface, #f5eee9);
    box-shadow: none;
    transform: none;
}

#more-pieces .zf-more-product:hover .zf-featured-product-image {
    transform: none;
    box-shadow: none;
}

#more-pieces .zf-more-product .zf-product-image-wrap {
    aspect-ratio: 4 / 5;
    overflow: hidden;
}

#more-pieces .zf-more-product .zf-product-image-wrap img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
    transition: transform .65s cubic-bezier(.22, .61, .36, 1);
}

#more-pieces .zf-more-product:hover .zf-product-image-wrap img {
    transform: scale(1.045);
    filter: none;
}

#more-pieces .zf-more-product .zf-product-image-overlay {
    opacity: 0;
    background: transparent;
}

#more-pieces .zf-more-product:hover .zf-product-image-overlay {
    opacity: 0;
}

#more-pieces .zf-more-product .zf-product-index {
    top: 14px;
    left: 14px;
    min-width: 32px;
    height: 26px;
    padding: 0 8px;
    border: 1px solid rgba(255, 255, 255, .6);
    border-radius: 999px;
    background: rgba(255, 255, 255, .12);
    color: #fff;
    font-size: 10px;
    letter-spacing: .12em;
    backdrop-filter: blur(7px);
}

#more-pieces .zf-more-product .zf-product-quick {
    display: none;
}

#more-pieces .zf-more-product .zf-featured-product-info {
    padding: 16px 0 0;
}

#more-pieces .zf-more-product .zf-product-topline {
    display: flex;
    align-items: center;
    min-height: 16px;
}

#more-pieces .zf-more-product .zf-product-category {
    color: var(--zf-muted, #9a8178);
    font-family: "Inter", sans-serif;
    font-size: 10px;
    font-weight: 500;
    letter-spacing: .13em;
    line-height: 1.4;
    text-transform: uppercase;
}

#more-pieces .zf-more-product .zf-featured-product-name {
    margin: 8px 0 12px;
    font-family: "Playfair Display", Georgia, serif;
    font-size: clamp(18px, 1.8vw, 24px);
    font-weight: 500;
    line-height: 1.2;
    letter-spacing: -.02em;
}

#more-pieces .zf-more-product .zf-featured-product-name a {
    color: var(--zf-ink, #542b35);
    text-decoration: none;
}

#more-pieces .zf-more-product .zf-featured-product-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

#more-pieces .zf-more-product .zf-price-group {
    display: flex;
    align-items: baseline;
    flex-wrap: wrap;
    gap: 8px;
}

#more-pieces .zf-more-product .zf-product-price {
    color: var(--zf-ink, #542b35) !important;
    font-family: "Inter", sans-serif;
    font-size: 13px;
    font-weight: 600;
    line-height: 1.4;
    letter-spacing: .01em;
}

#more-pieces .zf-more-product .zf-product-old-price {
    margin-left: 0;
    color: var(--zf-muted, #9a8178) !important;
    font-family: "Inter", sans-serif;
    font-size: 11px;
    font-weight: 400;
    line-height: 1.4;
    text-decoration: line-through;
}

#more-pieces .zf-more-product .zf-product-arrow {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    border: 1px solid var(--zf-border, rgba(84, 43, 53, .22));
    border-radius: 50%;
    background: transparent;
    color: var(--zf-ink, #542b35);
    font-family: "Inter", sans-serif;
    font-size: 15px;
    text-decoration: none;
    transition:
        background .3s ease,
        color .3s ease,
        transform .3s ease;
}

#more-pieces .zf-more-product:hover .zf-product-arrow {
    background: var(--zf-ink, #542b35);
    color: #fff;
    transform: rotate(45deg);
}

@media (max-width: 900px) {
    #more-pieces .zf-more-products-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 560px) {
    #more-pieces .zf-more-products-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px 14px;
    }

    #more-pieces .zf-more-product .zf-featured-product-name {
        font-size: 18px;
    }

    #more-pieces .zf-more-product .zf-product-price {
        font-size: 12px;
    }

    #more-pieces .zf-more-product .zf-product-arrow {
        width: 30px;
        height: 30px;
        flex-basis: 30px;
    }
}

</style>

<div
    id="home-page"
    class="zf-page"
    x-data="{
        activeSlide: 0,
        slidesCount: {{ $heroSlides->count() }},
        autoplay: {{ $autoplayEnabled ? 'true' : 'false' }},
        timer: null,

        /*
        |--------------------------------------------------------------------------
        | HERO
        |--------------------------------------------------------------------------
        | Catatan: tinggi wadah hero (.zf-hero-visual) SEKARANG FIXED lewat CSS
        | (aspect-ratio per breakpoint), tidak lagi dihitung ulang dari
        | naturalWidth/naturalHeight gambar oleh JS. Sebelumnya kontainer
        | memakai rasio fallback dulu lalu diganti paksa setelah gambar
        | selesai dimuat, yang menyebabkan hero terlihat membesar/mengecil
        | sesaat setelah refresh. Dengan rasio tetap, ukuran hero sudah pasti
        | sejak render pertama — tidak ada lagi lompatan layout.
        |--------------------------------------------------------------------------
        */

        init() {
            if (this.autoplay && this.slidesCount > 1) {
                this.startAutoplay();
            }
        },

        startAutoplay() {
            this.stopAutoplay();

            this.timer = setInterval(() => {
                this.next();
            }, 6500);
        },

        stopAutoplay() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        },

        next() {
            this.activeSlide = (this.activeSlide + 1) % this.slidesCount;
        },

        previous() {
            this.activeSlide = (this.activeSlide - 1 + this.slidesCount) % this.slidesCount;
        },

        goTo(index) {
            this.activeSlide = index;

            if (this.autoplay) {
                this.startAutoplay();
            }
        }
    }"
    @mouseenter="stopAutoplay()"
    @mouseleave="if (autoplay) startAutoplay()"
>
    {{-- ======================================================================
     ANNOUNCEMENT + HEADER DIHAPUS.
     Header atas & running text sekarang 100% milik layouts/store.blade.php
     supaya tidak ada dua header yang saling menumpuk.
     ====================================================================== --}}

    {{-- ======================================================================
         HERO
         ====================================================================== --}}

    <section class="zf-hero" aria-label="Featured collection">
        <div class="zf-hero-glow" aria-hidden="true"></div>

        <div class="zf-hero-grid">

            <div class="zf-hero-copy">
                <div class="zf-hero-copy-inner">

                    <div class="zf-hero-kicker">
                        <span class="zf-hero-kicker-dot" aria-hidden="true"></span>
                        <span x-text="String(activeSlide + 1).padStart(2, '0')"></span>
                        <span>/</span>
                        <span x-text="String(slidesCount).padStart(2, '0')"></span>
                        <span>New Season</span>
                    </div>

                    @foreach($heroSlides as $index => $slide)
                        @php
                            $slideTitle = data_get($slide, 'title')
                                ?? data_get($slide, 'heading')
                                ?? 'Elegance in Every Drape';

                            $slideDescription = data_get($slide, 'description')
                                ?? data_get($slide, 'subtitle')
                                ?? 'Koleksi pilihan untuk melengkapi gaya personalmu.';

                            $slideButtonText = data_get($slide, 'button_text')
                                ?? data_get($slide, 'cta_text')
                                ?? 'Jelajahi Koleksi';

                            $slideButtonUrl = data_get($slide, 'button_url')
                                ?? data_get($slide, 'cta_url')
                                ?? $shopUrl;

                            $titleWords = preg_split('/\s+/', trim($slideTitle));
                            $firstTitlePart = implode(' ', array_slice($titleWords, 0, max(1, ceil(count($titleWords) / 2))));
                            $secondTitlePart = implode(' ', array_slice($titleWords, max(1, ceil(count($titleWords) / 2))));
                        @endphp

                        <div
                            x-show="activeSlide === {{ $index }}"
                            x-transition:enter="transition ease-out duration-700"
                            x-transition:enter-start="opacity-0 translate-y-5"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-300 absolute"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            style="{{ $index === 0 ? '' : 'display:none' }}"
                        >
                            <h1 class="zf-hero-title">
                                {{ $firstTitlePart }}
                                <em>{{ $secondTitlePart }}</em>
                            </h1>

                            <p class="zf-hero-description">
                                {{ $slideDescription }}
                            </p>

                            <div class="zf-hero-actions">
                                {{-- Label tombol SENGAJA di-hardcode "Belanja Sekarang" /
                                     "Lihat Koleksi" supaya selalu tampil langsung tanpa
                                     bergantung pada data slide dari CMS maupun interaksi
                                     apa pun dari user. --}}
                                <a href="{{ $slideButtonUrl }}" class="zf-hero-cta">
                                    <span class="zf-hero-cta-label">Belanja Sekarang</span>
                                    <span class="zf-hero-cta-icon" aria-hidden="true">↗</span>
                                </a>

                                <a href="#collections" class="zf-hero-link-arrow">
                                    <span>Lihat Koleksi</span>
                                    <span class="zf-hero-link-arrow-icon" aria-hidden="true">↗</span>
                                </a>
                            </div>
                        </div>
                    @endforeach

                    <div class="zf-hero-meta">
                        <span>Designed in Indonesia</span>
                        <span class="zf-hero-meta-line"></span>
                        <span>Made for You</span>
                    </div>

                </div>
            </div>

            {{-- Wadah gambar: rasio TETAP lewat CSS (lihat aturan .zf-hero-visual
                 dan breakpoint-nya). Ini yang menghilangkan bug "gambar
                 membesar lalu mengecil" — ukuran sudah pasti sejak render
                 pertama, tidak menunggu JS menghitung ulang setelah gambar
                 selesai dimuat. object-fit: cover menjaga gambar tetap
                 mengisi penuh tanpa distorsi. --}}
            <div class="zf-hero-visual">
                @foreach($heroSlides as $index => $slide)
                    @php
                        $slideImage = data_get($slide, 'image')
                            ?? data_get($slide, 'image_url')
                            ?? data_get($slide, 'desktop_image')
                            ?? data_get($slide, 'mobile_image');

                        if ($slideImage) {
                            if (filter_var($slideImage, FILTER_VALIDATE_URL)) {
                                $slideImage = $slideImage;
                            } elseif (str_starts_with($slideImage, 'public/')) {
                                $slideImage = asset(Str::after($slideImage, 'public/'));
                            } elseif (str_starts_with($slideImage, 'storage/')) {
                                $slideImage = asset($slideImage);
                            } else {
                                $slideImage = Storage::url($slideImage);
                            }
                        }
                    @endphp

                    <div
                        class="zf-hero-image"
                        data-slide="{{ $index }}"
                        x-show="activeSlide === {{ $index }}"
                        x-transition:enter="transition ease-out duration-700"
                        x-transition:enter-start="opacity-0 scale-105"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-500 absolute"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0 scale-105"
                        style="{{ $index === 0 ? '' : 'display:none' }}"
                    >
                        @if($slideImage)
                            <img
                                src="{{ $slideImage }}"
                                alt="{{ data_get($slide, 'title', 'Zalina Fashion') }}"
                                class="zf-hero-image-media"
                            >
                        @else
                            <div class="zf-hero-image-fallback"></div>
                        @endif
                    </div>
                @endforeach

                <div class="zf-hero-overlay" aria-hidden="true"></div>

                <div class="zf-hero-badge">
                    <span class="zf-hero-badge-dot" aria-hidden="true"></span>
                    New Season
                </div>

                @if($heroSlides->count() > 1)
                    <div class="zf-hero-navigation">
                        <button
                            type="button"
                            class="zf-hero-nav"
                            @click="previous()"
                            aria-label="Slide sebelumnya"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M15 18l-6-6 6-6" />
                            </svg>
                        </button>

                        <button
                            type="button"
                            class="zf-hero-nav"
                            @click="next()"
                            aria-label="Slide berikutnya"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M9 18l6-6-6-6" />
                            </svg>
                        </button>
                    </div>

                    <div class="zf-hero-controls">
                        <div class="zf-hero-counter">
                            <span class="zf-hero-counter-current" x-text="String(activeSlide + 1).padStart(2, '0')"></span>
                            <span class="zf-hero-counter-sep">/</span>
                            <span class="zf-hero-counter-total" x-text="String(slidesCount).padStart(2, '0')"></span>
                        </div>

                        <div class="zf-hero-dots">
                            @foreach($heroSlides as $index => $slide)
                                <button
                                    type="button"
                                    class="zf-hero-dot"
                                    :class="{ 'active': activeSlide === {{ $index }} }"
                                    @click="goTo({{ $index }})"
                                    aria-label="Slide {{ $index + 1 }}"
                                ></button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>


    {{-- ======================================================================
         BRAND STATEMENT
         ====================================================================== --}}

    <section class="zf-statement zf-container">
        <div class="zf-statement-inner">
            <span class="zf-eyebrow">The Zalina Philosophy</span>

            <h2 class="zf-statement-title">
                Bukan sekadar apa yang kamu kenakan,
                <em>tetapi bagaimana kamu merasa.</em>
            </h2>

            <p class="zf-statement-copy">
                Kami percaya bahwa keanggunan hadir dari detail yang terasa personal.
                Dari tekstur yang lembut, warna yang tenang, hingga siluet yang memberi
                ruang bagi setiap perempuan untuk tampil sebagai dirinya sendiri.
            </p>

            <div class="zf-statement-signature">
                Zalina Fashion
            </div>
        </div>
    </section>

    {{-- ======================================================================
         COLLECTIONS
         ====================================================================== --}}

    <section
        id="collections"
        class="zf-section zf-container"
    >
        <div class="zf-section-heading">
            <div class="zf-section-heading-copy">
                <span class="zf-eyebrow">Explore the Edit</span>

                <h2 class="zf-section-title" style="margin-top:24px">
                    Curated for<br>
                    <em style="color:var(--zf-accent);font-style:italic">your everyday.</em>
                </h2>

                <p class="zf-section-copy">
                    Temukan koleksi yang dirancang untuk menyatu dengan rutinitas,
                    acara spesial, dan setiap momen yang ingin kamu rayakan.
                </p>
            </div>

            <div class="zf-section-heading-action">
                <a href="{{ $shopUrl }}" class="zf-link-arrow">
                    <span>View All Collections</span>
                    <span>↗</span>
                </a>
            </div>
        </div>

        <div class="zf-category-grid">
            @foreach($categoriesView->take(3) as $index => $category)
                @php
                    $categoryImageSrc = $categoryImage($category);
                    $categoryDesc = $categoryDescription($category);
                    $categoryCount = $categoryProductCount($category);

                    // Urutan animasi: card tengah (index 1) muncul lebih dulu,
                    // lalu melebar 1-per-1 ke kiri (index 0) & ke kanan (index 2).
                    $catRevealOrder = [1 => 0, 0 => 1, 2 => 2];
                    $catDelay = ($catRevealOrder[$index] ?? $index) * 320;
                @endphp

                <a
                    href="{{ $categoryUrl($category) }}"
                    class="zf-category-card"
                    data-category-id="{{ data_get($category, 'id') }}"
                    style="--zf-cat-delay: {{ $catDelay }}ms"
                >
                    @if($categoryImageSrc)
                        <img
                            src="{{ $categoryImageSrc }}"
                            alt="{{ $categoryName($category) }}"
                            class="zf-category-card-image"
                            loading="eager"
                            decoding="async"
                            fetchpriority="high"
                        >
                    @else
                        <div class="zf-category-card-image zf-category-card-fallback">
                            <span class="zf-category-card-fallback-mark">Z</span>
                            <small>ZALINA FASHION</small>
                        </div>
                    @endif

                    <div class="zf-category-card-content">
                        <div class="zf-category-card-index">
                            {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                            / Collection
                            @if($categoryCount !== null)
                                <span class="zf-category-card-count">· {{ $categoryCount }} items</span>
                            @endif
                        </div>

                        <h3 class="zf-category-card-title">
                            {{ $categoryName($category) }}
                        </h3>

                        @if($categoryDesc)
                            <p class="zf-category-card-desc">
                                {{ \Illuminate\Support\Str::limit($categoryDesc, 90) }}
                            </p>
                        @endif

                        <div class="zf-category-card-link">
                            <span>Discover More</span>
                            <span>↗</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
    {{-- ======================================================================
     KODE KE-2 — SECTION LANJUTAN ZALINA FASHION
     Tempel setelah kode ke-1
     ====================================================================== --}}

@php
    /*
    |--------------------------------------------------------------------------
    | DATA AMAN UNTUK SECTION LANJUTAN
    |--------------------------------------------------------------------------
    */

    $safeFeaturedProducts = collect($featuredProducts ?? []);

    $safeActiveDiscounts = collect($activeDiscounts ?? []);

    $safeSlides = collect($slides ?? ($sliders ?? []));

    $safeShopUrl = $shopUrl ?? url('/shop');

    /*
    |--------------------------------------------------------------------------
    | HELPER GAMBAR ZALINA
    |--------------------------------------------------------------------------
    */

    $resolveZalinaImage = function ($item) {
        if (!$item) {
            return null;
        }

        $possibleImages = [
            data_get($item, 'image'),
            data_get($item, 'image_path'),
            data_get($item, 'thumbnail'),
            data_get($item, 'thumbnail_path'),
            data_get($item, 'photo'),
            data_get($item, 'photo_path'),
            data_get($item, 'cover'),
            data_get($item, 'cover_image'),
        ];

        foreach ($possibleImages as $image) {
            if (!$image) {
                continue;
            }

            if (filter_var($image, FILTER_VALIDATE_URL)) {
                return $image;
            }

            if (str_starts_with($image, '/')) {
                return $image;
            }

            if (str_starts_with($image, 'storage/')) {
                return asset($image);
            }

            return \Illuminate\Support\Facades\Storage::url($image);
        }

        return null;
    };

    /*
    |--------------------------------------------------------------------------
    | HELPER NAMA PRODUK
    |--------------------------------------------------------------------------
    */

    $resolveZalinaName = function ($item) {
        return data_get($item, 'name')
            ?? data_get($item, 'product_name')
            ?? data_get($item, 'title')
            ?? 'Zalina Collection';
    };

    /*
    |--------------------------------------------------------------------------
    | HELPER KATEGORI
    |--------------------------------------------------------------------------
    */

    $resolveZalinaCategory = function ($item) {
        return data_get($item, 'category.name')
            ?? data_get($item, 'category_name')
            ?? data_get($item, 'category')
            ?? 'Zalina Fashion';
    };

    /*
    |--------------------------------------------------------------------------
    | HELPER URL PRODUK
    |--------------------------------------------------------------------------
    */

    $resolveZalinaUrl = function ($item) {
        $productId = data_get($item, 'id');
        $productSlug = data_get($item, 'slug');

        if (\Illuminate\Support\Facades\Route::has('product.show')) {
            if ($productSlug) {
                return route('product.show', ['product' => $productSlug]);
            }

            if ($productId) {
                return route('product.show', ['product' => $productId]);
            }
        }

        if ($productSlug) {
            return url('/product/' . $productSlug);
        }

        if ($productId) {
            return url('/product/' . $productId);
        }

        return url('/shop');
    };

    /*
    |--------------------------------------------------------------------------
    | HELPER HARGA PRODUK
    |--------------------------------------------------------------------------
    */

    $resolveZalinaPrice = function ($item) {
        return (float) (
            data_get($item, 'price')
            ?? data_get($item, 'selling_price')
            ?? data_get($item, 'harga')
            ?? 0
        );
    };

    $resolveZalinaOldPrice = function ($item) {
        return (float) (
            data_get($item, 'old_price')
            ?? data_get($item, 'original_price')
            ?? data_get($item, 'compare_price')
            ?? data_get($item, 'harga_lama')
            ?? 0
        );
    };

    $formatZalinaPrice = function ($price) {
        return 'Rp ' . number_format((float) $price, 0, ',', '.');
    };

    /*
    |--------------------------------------------------------------------------
    | HELPER PROMO
    |--------------------------------------------------------------------------
    */

    $resolveDiscountImage = function ($discount) use ($resolveZalinaImage) {
        $discountImage = data_get($discount, 'image')
            ?? data_get($discount, 'image_path')
            ?? data_get($discount, 'banner')
            ?? data_get($discount, 'banner_image');

        if ($discountImage) {
            if (filter_var($discountImage, FILTER_VALIDATE_URL)) {
                return $discountImage;
            }

            if (str_starts_with($discountImage, '/')) {
                return $discountImage;
            }

            if (str_starts_with($discountImage, 'storage/')) {
                return asset($discountImage);
            }

            return \Illuminate\Support\Facades\Storage::url($discountImage);
        }

        return null;
    };

    $resolveDiscountTitle = function ($discount) {
        return data_get($discount, 'name')
            ?? data_get($discount, 'title')
            ?? data_get($discount, 'discount_name')
            ?? 'Zalina Special Offer';
    };

    $resolveDiscountCode = function ($discount) {
        return data_get($discount, 'code')
            ?? data_get($discount, 'coupon_code')
            ?? data_get($discount, 'kode')
            ?? 'ZALINA';
    };

    $resolveDiscountValue = function ($discount) {
        $type = data_get($discount, 'type')
            ?? data_get($discount, 'discount_type')
            ?? 'percentage';

        $value = data_get($discount, 'value')
            ?? data_get($discount, 'discount_value')
            ?? data_get($discount, 'amount')
            ?? 0;

        if (in_array($type, ['fixed', 'nominal', 'amount', 'fixed_amount'], true)) {
            return 'Rp ' . number_format((float) $value, 0, ',', '.');
        }

        return rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',') . '%';
    };
@endphp


{{-- ======================================================================
     FEATURED COLLECTION
     ====================================================================== --}}

<section class="zf-section-tight zf-container" id="featured-collection">
    <div class="zf-section-heading">
        <div class="zf-section-heading-copy">
            <span class="zf-eyebrow">
                Zalina Fashion Collection
            </span>

            <h2 class="zf-section-title">
                Pieces with<br>
                <em style="color:var(--zf-accent);font-style:italic">
                    presence.
                </em>
            </h2>

            <p class="zf-section-copy">
                Pilihan koleksi Zalina Fashion yang dirancang untuk
                menemani setiap gaya dengan karakter yang lembut,
                elegan, dan berkesan.
            </p>
        </div>

        <div class="zf-section-heading-action">
            <a href="{{ $safeShopUrl }}" class="zf-link-arrow">
                <span>Shop All Pieces</span>
                <span aria-hidden="true">↗</span>
            </a>
        </div>
    </div>

    @if($safeFeaturedProducts->isNotEmpty())
        @php $shopSlides = $safeFeaturedProducts->take(8)->values(); @endphp

        <div class="zf-shop-slider" data-shop-slider aria-roledescription="carousel">
            <div class="zf-shop-slider-viewport">
                <div
                    class="zf-shop-slider-track"
                    data-shop-track
                    style="--zf-slide-count: {{ $shopSlides->count() }}"
                >
                    @foreach($shopSlides as $index => $product)
                        @php
                            $featuredName = $resolveZalinaName($product);
                            $featuredCategory = $resolveZalinaCategory($product);
                            $featuredImage = $resolveZalinaImage($product);
                            $featuredUrl = $resolveZalinaUrl($product);
                            $featuredPrice = $resolveZalinaPrice($product);
                            $featuredOldPrice = $resolveZalinaOldPrice($product);
                        @endphp

                        <article
                            class="zf-shop-slide{{ $index === 0 ? ' is-active' : '' }}"
                            data-shop-slide
                            aria-hidden="{{ $index === 0 ? 'false' : 'true' }}"
                        >
                            <a
                                href="{{ $featuredUrl }}"
                                class="zf-shop-slide-image"
                                aria-label="Lihat {{ $featuredName }}"
                            >
                                <div class="zf-product-image-wrap">
                                    @if($featuredImage)
                                        <img
                                            src="{{ $featuredImage }}"
                                            alt="{{ $featuredName }}"
                                            loading="lazy"
                                        >
                                    @else
                                        <div class="zf-product-fallback">
                                            <span>Z</span>
                                            <small>ZALINA FASHION</small>
                                        </div>
                                    @endif

                                    <div class="zf-product-image-overlay"></div>

                                    <span class="zf-product-index">
                                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                        / {{ str_pad($shopSlides->count(), 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                </div>
                            </a>

                            <div class="zf-shop-slide-info">
                                <div class="zf-product-topline">
                                    <span class="zf-product-category">
                                        {{ $featuredCategory }}
                                    </span>

                                    @if($index === 0)
                                        <span class="zf-product-status">Signature</span>
                                    @elseif($index === 1)
                                        <span class="zf-product-status">Featured</span>
                                    @endif
                                </div>

                                <h3 class="zf-shop-slide-name">
                                    <a href="{{ $featuredUrl }}">
                                        {{ $featuredName }}
                                    </a>
                                </h3>

                                <div class="zf-price-group">
                                    <span class="zf-product-price">
                                        {{ $formatZalinaPrice($featuredPrice) }}
                                    </span>

                                    @if($featuredOldPrice > $featuredPrice)
                                        <span class="zf-product-old-price">
                                            {{ $formatZalinaPrice($featuredOldPrice) }}
                                        </span>
                                    @endif
                                </div>

                                <a href="{{ $featuredUrl }}" class="zf-button">
                                    <span>View Piece</span>
                                    <span class="zf-button-arrow">↗</span>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            @if($shopSlides->count() > 1)
                <div class="zf-shop-slider-controls">
                    <button
                        type="button"
                        class="zf-slider-nav zf-slider-prev"
                        data-shop-prev
                        aria-label="Produk sebelumnya"
                    >‹</button>

                    <div class="zf-slider-dots" data-shop-dots>
                        @foreach($shopSlides as $index => $product)
                            <button
                                type="button"
                                class="zf-slider-dot{{ $index === 0 ? ' is-active' : '' }}"
                                data-shop-dot="{{ $index }}"
                                aria-label="Ke produk {{ $index + 1 }}"
                            ></button>
                        @endforeach
                    </div>

                    <button
                        type="button"
                        class="zf-slider-nav zf-slider-next"
                        data-shop-next
                        aria-label="Produk berikutnya"
                    >›</button>
                </div>
            @endif
        </div>
    @else
        <div class="zf-empty-collection">
            <span class="zf-empty-collection-mark">Z</span>
            <h3>Koleksi Zalina Fashion</h3>
            <p>
                Koleksi sedang dipersiapkan. Silakan kembali lagi
                untuk melihat pilihan terbaru kami.
            </p>
            <a href="{{ $safeShopUrl }}" class="zf-button">
                Lihat Koleksi
                <span class="zf-button-arrow">↗</span>
            </a>
        </div>
    @endif
</section>


{{-- ======================================================================
     EDITORIAL BANNER — HANYA MENGGUNAKAN ASET ZALINA
     ====================================================================== --}}

@php
    $editorialImage = null;

    if ($safeSlides->isNotEmpty()) {
        $editorialImage = $resolveZalinaImage($safeSlides->first());
    }

    if (!$editorialImage && $safeFeaturedProducts->isNotEmpty()) {
        $editorialImage = $resolveZalinaImage($safeFeaturedProducts->first());
    }
@endphp

<section class="zf-editorial-banner zf-editorial-reveal" id="zalina-editorial">
    <div class="zf-editorial-banner-image">
        @if($editorialImage)
            <img
                src="{{ $editorialImage }}"
                alt="Koleksi Zalina Fashion"
                loading="lazy"
            >
        @else
            <div class="zf-editorial-fallback">
                <span>ZALINA</span>
                <small>FASHION</small>
            </div>
        @endif
    </div>

    <div class="zf-editorial-banner-content">
        <span class="zf-eyebrow zf-editorial-reveal-item">
            The Zalina Perspective
        </span>

        <h2 class="zf-editorial-banner-title zf-editorial-reveal-item">
            Less noise.<br>
            More <em>presence.</em>
        </h2>

        <p class="zf-editorial-banner-copy zf-editorial-reveal-item">
            Gaya yang baik tidak perlu berteriak.
            Ia hadir melalui warna, material, proporsi,
            dan detail yang terasa tepat.
        </p>

        <a href="{{ $safeShopUrl }}" class="zf-button zf-editorial-reveal-item">
            <span>Explore Zalina</span>
            <span class="zf-button-arrow">↗</span>
        </a>
    </div>
</section>


{{-- ======================================================================
     PROMO CAMPAIGN
     ====================================================================== --}}

@if($safeActiveDiscounts->isNotEmpty())
    <section class="zf-section zf-container" id="zalina-promotions">
        <div class="zf-section-heading">
            <div class="zf-section-heading-copy">
                <span class="zf-eyebrow">
                    Zalina Private Offers
                </span>

                <h2 class="zf-section-title">
                    A little more<br>
                    <em style="color:var(--zf-accent);font-style:italic">
                        to love.
                    </em>
                </h2>

                <p class="zf-section-copy">
                    Gunakan penawaran pilihan dari Zalina Fashion
                    untuk melengkapi koleksi favoritmu.
                </p>
            </div>

            <div class="zf-section-heading-action">
                <a href="{{ $safeShopUrl }}" class="zf-link-arrow">
                    <span>Shop with Offer</span>
                    <span aria-hidden="true">↗</span>
                </a>
            </div>
        </div>

        <div class="zf-promo-grid">
            @foreach($safeActiveDiscounts->take(5) as $index => $discount)
                @php
                    $promoImage = $resolveDiscountImage($discount);
                    $promoTitle = $resolveDiscountTitle($discount);
                    $promoCode = $resolveDiscountCode($discount);
                    $promoValue = $resolveDiscountValue($discount);

                    $promoDescription = data_get($discount, 'description')
                        ?? 'Gunakan kode ini saat checkout untuk mendapatkan penawaran khusus dari Zalina Fashion.';
                @endphp

                @php
                    $promoTheme = data_get($discount, 'theme', 'maroon_gold');

                    $allowedThemes = [
                        'maroon_gold',
                        'rose',
                        'emerald',
                        'midnight',
                        'cream',
                    ];

                    if (!in_array($promoTheme, $allowedThemes, true)) {
                        $promoTheme = 'maroon_gold';
                    }
                @endphp

                <article
                    class="zf-promo-card zf-promo-theme-{{ $promoTheme }}"
                    style="transition-delay: {{ min($index, 4) * 120 }}ms"
                    tabindex="0"
                    role="group"
                    aria-label="Kartu promo {{ $promoTitle }}, tekan untuk membalik kartu"
                >
                    <div class="zf-promo-card-inner">
                        {{-- SISI DEPAN --}}
                        <div class="zf-promo-card-face zf-promo-card-front">
                            @if($promoImage)
                                <div class="zf-promo-card-image">
                                    <img
                                        src="{{ $promoImage }}"
                                        alt="{{ $promoTitle }}"
                                        loading="lazy"
                                    >
                                </div>
                            @else
                                <div class="zf-promo-card-image zf-promo-fallback">
                                    <span>Z</span>
                                    <small>ZALINA FASHION</small>
                                </div>
                            @endif

                            <div class="zf-promo-card-shade"></div>

                            <div class="zf-promo-card-content">
                                <div class="zf-promo-card-top">
                                    <span class="zf-promo-card-label">
                                        Zalina Offer
                                    </span>

                                    <span class="zf-promo-card-number">
                                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                </div>

                                <div class="zf-promo-card-main">
                                    <span class="zf-promo-value">
                                        {{ $promoValue }}
                                    </span>

                                    <h3 class="zf-promo-title">
                                        {{ $promoTitle }}
                                    </h3>

                                    <p class="zf-promo-description">
                                        {{ \Illuminate\Support\Str::limit($promoDescription, 105) }}
                                    </p>
                                </div>

                                <div class="zf-promo-card-bottom">
                                    <div class="zf-promo-code">
                                        <span>Promo Code</span>
                                        <strong>{{ $promoCode }}</strong>
                                    </div>

                                    <button
                                        type="button"
                                        class="zf-promo-flip"
                                        data-promo-flip
                                        aria-label="Balik kartu untuk lihat detail"
                                    >
                                        <span class="zf-promo-flip-icon">⟳</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- SISI BELAKANG — gaya kartu ATM --}}
                        <div class="zf-promo-card-face zf-promo-card-back">
                            <div class="zf-promo-card-back-stripe"></div>

                            <div class="zf-promo-card-back-content">
                                <span class="zf-promo-card-label">Zalina Offer · {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>

                                <div class="zf-promo-code zf-promo-code-back">
                                    <span>Promo Code</span>
                                    <strong>{{ $promoCode }}</strong>
                                </div>

                                <p class="zf-promo-description">
                                    {{ \Illuminate\Support\Str::limit($promoDescription, 140) }}
                                </p>

                                <div class="zf-promo-card-back-bottom">
                                    <button
                                        type="button"
                                        class="zf-promo-copy"
                                        data-copy-code="{{ $promoCode }}"
                                        aria-label="Salin kode {{ $promoCode }}"
                                    >
                                        Copy Code
                                    </button>

                                    <button
                                        type="button"
                                        class="zf-promo-flip"
                                        data-promo-flip
                                        aria-label="Balik kembali ke depan kartu"
                                    >
                                        <span class="zf-promo-flip-icon">⟲</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif


{{-- ======================================================================
     SIGNATURE SERIES
     ====================================================================== --}}

@php
    $signatureMainImage = null;
    $signatureSmallImage = null;

    if ($safeFeaturedProducts->isNotEmpty()) {
        $signatureMainImage = $resolveZalinaImage(
            $safeFeaturedProducts->first()
        );
    }

    if ($safeFeaturedProducts->count() > 1) {
        $signatureSmallImage = $resolveZalinaImage(
            $safeFeaturedProducts->skip(1)->first()
        );
    }
@endphp

<section class="zf-section zf-container" id="signature-series">
    <div class="zf-signature-layout">
        <div class="zf-signature-copy">
            <span class="zf-eyebrow">
                Zalina Signature Series
            </span>

            <h2 class="zf-signature-title">
                The art of<br>
                <em>quiet elegance.</em>
            </h2>

            <p class="zf-signature-description">
                Signature Series hadir sebagai representasi karakter
                Zalina Fashion: lembut namun berkarakter, minimal
                namun tetap berkesan.
            </p>

            <div class="zf-signature-details">
                <div>
                    <span class="zf-signature-detail-number">
                        01
                    </span>

                    <span class="zf-signature-detail-title">
                        Soft Touch
                    </span>

                    <p>
                        Material lembut yang nyaman digunakan
                        sepanjang hari.
                    </p>
                </div>

                <div>
                    <span class="zf-signature-detail-number">
                        02
                    </span>

                    <span class="zf-signature-detail-title">
                        Refined Form
                    </span>

                    <p>
                        Warna dan proporsi yang mudah dipadukan
                        dengan gaya personalmu.
                    </p>
                </div>
            </div>

            <a href="{{ $safeShopUrl }}" class="zf-button">
                <span>Discover Signature</span>
                <span class="zf-button-arrow">↗</span>
            </a>
        </div>

        <div class="zf-signature-visual">
            <div class="zf-signature-main-image">
                @if($signatureMainImage)
                    <img
                        src="{{ $signatureMainImage }}"
                        alt="Zalina Signature Series"
                        loading="lazy"
                    >
                @else
                    <div class="zf-signature-fallback">
                        <span>Z</span>
                        <small>ZALINA FASHION</small>
                    </div>
                @endif
            </div>

            <div class="zf-signature-small-image">
                @if($signatureSmallImage)
                    <img
                        src="{{ $signatureSmallImage }}"
                        alt="Detail Zalina Signature Series"
                        loading="lazy"
                    >
                @else
                    <div class="zf-signature-small-fallback">
                        <span>Z</span>
                    </div>
                @endif
            </div>

            <div class="zf-signature-stamp">
                <span>Z</span>
                <small>
                    Signature<br>
                    Series
                </small>
            </div>
        </div>
    </div>
</section>


{{-- ======================================================================
     MORE PIECES — TAMPIL SETELAH "THE ART OF QUIET ELEGANCE"
     ====================================================================== --}}

@php
    $moreProducts = collect($productsView ?? [])
    ->sortByDesc(function ($product) {
        return data_get($product, 'created_at')
            ?? data_get($product, 'id', 0);
    })
    ->values();

    if ($moreProducts->isEmpty()) {
        $moreProducts = $safeFeaturedProducts->skip(4)->values();
    }

    if ($moreProducts->isEmpty()) {
        $moreProducts = $safeFeaturedProducts->values();
    }

    $moreProducts = $moreProducts->take(8);
@endphp

@if($moreProducts->isNotEmpty())
    <section class="zf-section zf-container" id="more-pieces">
        <div class="zf-section-heading">
            <div class="zf-section-heading-copy">
                <span class="zf-eyebrow">
                    Zalina Fashion Collection
                </span>

                <h2 class="zf-section-title">
                    More pieces,<br>
                    <em style="color:var(--zf-accent);font-style:italic">
                        more you.
                    </em>
                </h2>

                <p class="zf-section-copy">
                    Jelajahi lebih banyak pilihan dari koleksi Zalina Fashion,
                    disusun rapi dan siap menemani gaya harianmu.
                </p>
            </div>

            <div class="zf-section-heading-action">
                <a href="{{ $safeShopUrl }}" class="zf-link-arrow">
                    <span>View Full Catalogue</span>
                    <span aria-hidden="true">↗</span>
                </a>
            </div>
        </div>

        <div class="zf-more-products-grid">
            @foreach($moreProducts as $index => $product)
                @php
                    $moreName = $resolveZalinaName($product);
                    $moreCategory = $resolveZalinaCategory($product);
                    $moreImage = $resolveZalinaImage($product);
                    $moreUrl = $resolveZalinaUrl($product);
                    $morePrice = $resolveZalinaPrice($product);
                    $moreOldPrice = $resolveZalinaOldPrice($product);
                @endphp

                <article
                    class="zf-featured-product zf-more-product"
                    style="transition-delay: {{ min($index, 7) * 90 }}ms"
                >
                    <a
                        href="{{ $moreUrl }}"
                        class="zf-featured-product-image"
                        aria-label="Lihat {{ $moreName }}"
                    >
                        <div class="zf-product-image-wrap">
                            @if($moreImage)
                                <img
                                    src="{{ $moreImage }}"
                                    alt="{{ $moreName }}"
                                    loading="lazy"
                                >
                            @else
                                <div class="zf-product-fallback">
                                    <span>Z</span>
                                    <small>ZALINA FASHION</small>
                                </div>
                            @endif

                            <div class="zf-product-image-overlay"></div>

                            <span class="zf-product-index">
                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                            </span>

                            <span class="zf-product-quick">
                                <span>View Piece</span>
                                <span aria-hidden="true">↗</span>
                            </span>
                        </div>
                    </a>

                    <div class="zf-featured-product-info">
                        <div class="zf-product-topline">
                            <span class="zf-product-category">
                                {{ $moreCategory }}
                            </span>
                        </div>

                        <h3 class="zf-featured-product-name">
                            <a href="{{ $moreUrl }}">
                                {{ $moreName }}
                            </a>
                        </h3>

                        <div class="zf-featured-product-bottom">
                            <div class="zf-price-group">
                                <span class="zf-product-price">
                                    {{ $formatZalinaPrice($morePrice) }}
                                </span>

                                @if($moreOldPrice > $morePrice)
                                    <span class="zf-product-old-price">
                                        {{ $formatZalinaPrice($moreOldPrice) }}
                                    </span>
                                @endif
                            </div>

                            <a
                                href="{{ $moreUrl }}"
                                class="zf-product-arrow"
                                aria-label="Lihat {{ $moreName }}"
                            >
                                ↗
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach

        </div>

    </section>

    <div class="zf-category-single-cta">
        <a href="{{ route('category.index') }}" class="zf-category-single-button">
            <span class="zf-category-single-shine"></span>
            <span>Pilih Kategorimu</span>
            <span aria-hidden="true">↗</span>
        </a>
    </div>

    <style>
    .zf-category-single-cta {
        display: flex;
        justify-content: center;
        padding: 34px 24px 64px;
        background: #f8f5f3;
    }

    .zf-category-single-button {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 18px;
        padding: 0 0 9px;
        border: 0;
        border-bottom: 1px solid rgba(101, 31, 53, .45);
        background: transparent;
        color: #651f35;
        font-family: "Inter", Arial, sans-serif;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
        text-decoration: none;
        transform: translateY(0);
        transition:
            color .3s ease,
            border-color .3s ease,
            transform .3s ease;
    }

    .zf-category-single-button:hover {
        color: #a77b3d;
        border-color: #a77b3d;
        transform: translateY(-4px);
    }

    .zf-category-single-button:active {
        transform: translateY(-1px);
    }

    .zf-category-single-button > span:not(.zf-category-single-shine):last-child {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border: 1px solid rgba(101, 31, 53, .35);
        border-radius: 50%;
        color: #651f35;
        font-family: Arial, sans-serif;
        font-size: 16px;
        transition:
            color .3s ease,
            border-color .3s ease,
            transform .3s ease;
    }

    .zf-category-single-button:hover > span:last-child {
        border-color: #a77b3d;
        color: #a77b3d;
        transform: translateX(4px);
    }

    .zf-category-single-shine {
        position: absolute;
        top: 0;
        left: -80%;
        width: 45%;
        height: 100%;
        background: linear-gradient(
            105deg,
            transparent,
            rgba(255, 255, 255, .85),
            transparent
        );
        transform: skewX(-20deg);
        animation: zfCatalogueShine 5s ease-in-out infinite;
        pointer-events: none;
    }

    @keyframes zfCatalogueShine {
        0%,
        65%,
        100% {
            left: -80%;
            opacity: 0;
        }

        15%,
        45% {
            opacity: 1;
        }

        65% {
            left: 150%;
        }
    }

    @media (max-width: 560px) {
        .zf-category-single-cta {
            padding: 28px 16px 52px;
        }

        .zf-category-single-button {
            gap: 14px;
            font-size: 11px;
            letter-spacing: .09em;
        }

        .zf-category-single-button > span:not(.zf-category-single-shine):last-child {
            width: 26px;
            height: 26px;
            font-size: 15px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .zf-category-single-shine,
        .zf-category-single-button,
        .zf-category-single-button > span:last-child {
            animation: none;
            transition: none;
        }
    }
</style>

    
@endif


{{-- ======================================================================
     CSS KODE KE-2
     ====================================================================== --}}

<style>
    .zf-more-products-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 22px;
    }

    /* =========================================================================
       SHOP ALL PIECES — HORIZONTAL SLIDER (1 PRODUK PER TAMPILAN)
       ========================================================================= */

    .zf-shop-slider {
        position: relative;
    }

    .zf-shop-slider-viewport {
        overflow: hidden;
        border-radius: var(--zf-radius-md, 16px);
    }

    .zf-shop-slider-track {
        display: flex;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        will-change: transform;
        transition: transform 750ms cubic-bezier(.22, .61, .36, 1);
    }

    .zf-shop-slider-viewport,
    .zf-shop-slider-track,
    .zf-shop-slide {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }

    .zf-shop-slide {
        flex: 0 0 100%;
        overflow: hidden;
    }

    .zf-shop-slide {
        display: grid;
        grid-template-columns: 1.15fr .85fr;
        align-items: center;
        gap: clamp(28px, 5vw, 70px);
        flex: 0 0 100%;
        max-width: 100%;
        min-width: 0;
        opacity: .35;
        transform: scale(.97);
        transition:
            opacity 750ms cubic-bezier(.22, .61, .36, 1),
            transform 750ms cubic-bezier(.22, .61, .36, 1);
    }

    .zf-shop-slide {
        width: 100%;
        min-width: 100%;
        max-width: 100%;
        flex: 0 0 100%;
        box-sizing: border-box;
        overflow: hidden;
    }

    .zf-shop-slide *,
    .zf-shop-slide *::before,
    .zf-shop-slide *::after {
        box-sizing: border-box;
    }

    .zf-shop-slide-info {
        min-width: 0;
        max-width: 100%;
        overflow: hidden;
    }

    .zf-shop-slide-image {
        width: 100%;
        min-width: 0;
        max-width: 100%;
        overflow: hidden;
    }

    .zf-shop-slide.is-active {
        opacity: 1;
        transform: scale(1);
    }

    .zf-shop-slide-image {
        display: block;
    }

    .zf-shop-slide .zf-product-image-wrap {
        aspect-ratio: 4 / 5;
        max-height: 560px;
    }

    .zf-shop-slide-info {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
        padding-right: clamp(0px, 3vw, 40px);
    }

    .zf-shop-slide-name {
        margin: 14px 0 0;
        font-family: var(--zf-serif);
        font-size: clamp(30px, 3.4vw, 48px);
        font-weight: 500;
        letter-spacing: -.045em;
        line-height: 1.05;
    }

    .zf-shop-slide-name a {
        transition: color var(--zf-transition);
    }

    .zf-shop-slide-name a:hover {
        color: var(--zf-accent);
    }

    .zf-shop-slide-info .zf-price-group {
        margin-top: 18px;
    }

    .zf-shop-slide-info .zf-product-price {
        font-size: 15px;
    }

    .zf-shop-slide-info .zf-button {
        margin-top: 30px;
    }

    .zf-shop-slider-controls {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 22px;
        margin-top: 34px;
    }

    .zf-slider-nav {
        display: inline-flex;
        width: 42px;
        height: 42px;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--zf-line);
        border-radius: 50%;
        background: transparent;
        color: var(--zf-ink);
        font-size: 18px;
        cursor: pointer;
        transition:
            background var(--zf-transition),
            color var(--zf-transition),
            border-color var(--zf-transition),
            transform var(--zf-transition);
    }

    .zf-slider-nav:hover {
        background: var(--zf-button);
        border-color: var(--zf-button);
        color: #fff;
        transform: translateY(-2px);
    }

    .zf-slider-dots {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .zf-slider-dot {
        width: 8px;
        height: 8px;
        padding: 0;
        border: none;
        border-radius: 50%;
        background: var(--zf-line);
        cursor: pointer;
        transition:
            background var(--zf-transition),
            width var(--zf-transition),
            border-radius var(--zf-transition);
    }

    .zf-slider-dot.is-active {
        width: 26px;
        border-radius: 999px;
        background: var(--zf-button);
    }

    @media (max-width: 780px) {
        .zf-shop-slide {
            grid-template-columns: 1fr;
            gap: 24px;
        }

        .zf-shop-slide .zf-product-image-wrap {
            max-height: 420px;
        }
    }

    .zf-featured-product {
        min-width: 0;
    }

    .zf-featured-product-image {
        display: block;
    }

    .zf-product-image-wrap {
        position: relative;
        aspect-ratio: 4 / 5;
        overflow: hidden;
        background: var(--zf-paper-alt);
    }

    .zf-product-image-wrap img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
        filter: saturate(.88);
        transition:
            transform 1s cubic-bezier(.22, .61, .36, 1),
            filter 700ms ease;
    }

    .zf-product-image-wrap:hover img {
        filter: saturate(1);
        transform: scale(1.055);
    }

    .zf-product-image-overlay {
        position: absolute;
        inset: 0;
        background:
            linear-gradient(
                180deg,
                rgba(25, 16, 19, .04),
                rgba(25, 16, 19, .22)
            );
        opacity: 0;
        transition: opacity var(--zf-transition);
    }

    .zf-product-image-wrap:hover .zf-product-image-overlay {
        opacity: 1;
    }

    .zf-product-index {
        position: absolute;
        top: 18px;
        left: 18px;
        color: rgba(255, 255, 255, .9);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .16em;
        text-shadow: 0 1px 8px rgba(0, 0, 0, .2);
    }

    .zf-product-quick {
        position: absolute;
        right: 18px;
        bottom: 18px;
        left: 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 16px;
        background: rgba(255, 255, 255, .94);
        color: var(--zf-ink);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .14em;
        text-transform: uppercase;
        opacity: 0;
        transform: translateY(10px);
        transition:
            opacity var(--zf-transition),
            transform var(--zf-transition);
    }

    .zf-product-quick span:last-child {
        font-size: 17px;
        font-weight: 400;
    }

    .zf-product-image-wrap:hover .zf-product-quick {
        opacity: 1;
        transform: translateY(0);
    }

    .zf-featured-product-info {
        padding-top: 20px;
    }

    .zf-product-topline {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        min-height: 18px;
    }

    .zf-product-category,
    .zf-product-status {
        color: var(--zf-ink-soft, var(--zf-muted));
        font-size: 9.5px;
        font-weight: 800;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .zf-product-status {
        color: var(--zf-accent);
    }

    .zf-featured-product-name {
        margin: 14px 0 0;
        color: var(--zf-ink);
        font-family: var(--zf-serif);
        font-size: 27px;
        font-weight: 500;
        letter-spacing: -.03em;
        line-height: 1.12;
    }

    .zf-featured-product-name a {
        transition: color var(--zf-transition);
    }

    .zf-featured-product-name a:hover {
        color: var(--zf-accent);
    }

    .zf-featured-product-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-top: 19px;
        padding-top: 15px;
        border-top: 1px solid var(--zf-line);
    }

    .zf-price-group {
        display: flex;
        align-items: baseline;
        flex-wrap: wrap;
        gap: 9px;
    }

    .zf-product-price {
        color: var(--zf-ink);
        font-variant-numeric: tabular-nums;
        font-size: 13px;
        font-weight: 800;
        letter-spacing: .015em;
    }

    .zf-product-old-price {
        color: var(--zf-muted);
        font-variant-numeric: tabular-nums;
        font-size: 10.5px;
        font-weight: 600;
        text-decoration: line-through;
        text-decoration-thickness: 1px;
    }

    .zf-product-arrow {
        display: inline-flex;
        width: 34px;
        height: 34px;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--zf-line);
        border-radius: 50%;
        color: var(--zf-ink);
        font-size: 16px;
        transition:
            background var(--zf-transition),
            color var(--zf-transition),
            transform var(--zf-transition);
    }

    .zf-product-arrow:hover {
        background: var(--zf-button);
        color: #fff;
        transform: translateY(-3px);
    }

    .zf-product-fallback,
    .zf-editorial-fallback,
    .zf-signature-fallback,
    .zf-signature-small-fallback {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        background:
            linear-gradient(135deg, #5b293b, #28151d);
        color: #dfbd86;
    }

    .zf-product-fallback span {
        font-family: var(--zf-display);
        font-size: 95px;
        line-height: 1;
    }

    .zf-product-fallback small,
    .zf-editorial-fallback small,
    .zf-signature-fallback small {
        margin-top: 12px;
        font-size: 8px;
        font-weight: 800;
        letter-spacing: .35em;
    }

    .zf-empty-collection {
        min-height: 360px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        padding: 50px 25px;
        border: 1px solid var(--zf-line);
        text-align: center;
    }

    .zf-empty-collection-mark {
        color: var(--zf-accent);
        font-family: var(--zf-display);
        font-size: 80px;
        line-height: 1;
    }

    .zf-empty-collection h3 {
        margin-top: 20px;
        font-family: var(--zf-serif);
        font-size: 30px;
        font-weight: 400;
    }

    .zf-empty-collection p {
        max-width: 420px;
        margin-top: 14px;
        color: var(--zf-muted);
        font-size: 13px;
        line-height: 1.8;
    }

    .zf-empty-collection .zf-button {
        margin-top: 25px;
    }

    .zf-editorial-banner {
        position: relative;
        display: grid;
        min-height: 610px;
        grid-template-columns: 1.12fr .88fr;
        margin-top: 55px;
        color: #fff;
    }

    .zf-editorial-banner-image {
        position: relative;
        min-height: 610px;
        overflow: hidden;
    }

    .zf-editorial-banner-image img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
        filter: saturate(.8);
        transition:
            transform 1.5s cubic-bezier(.22, .61, .36, 1);
    }

    .zf-editorial-banner:hover .zf-editorial-banner-image img {
        transform: scale(1.04);
    }

    .zf-editorial-banner-image::after {
        position: absolute;
        inset: 0;
        content: "";
        background:
            linear-gradient(
                90deg,
                transparent 40%,
                rgba(40, 24, 29, .3)
            );
        pointer-events: none;
    }

    .zf-editorial-fallback {
        min-height: 610px;
        background:
            radial-gradient(
                circle at 30% 20%,
                rgba(218, 183, 134, .2),
                transparent 30%
            ),
            linear-gradient(135deg, #5b293b, #28151d);
    }

    .zf-editorial-fallback span {
        font-family: var(--zf-display);
        font-size: clamp(55px, 8vw, 125px);
        letter-spacing: .04em;
        line-height: 1;
    }

    .zf-editorial-fallback small {
        margin-top: 18px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .55em;
    }

    .zf-editorial-banner-content {
        display: flex;
        align-items: flex-start;
        justify-content: center;
        flex-direction: column;
        padding: 75px clamp(30px, 7vw, 120px);
        background:
            radial-gradient(
                circle at 85% 10%,
                rgba(216, 183, 138, .14),
                transparent 24rem
            ),
            var(--zf-ink);
    }

    .zf-editorial-banner-content .zf-eyebrow {
        color: var(--zf-accent-soft);
    }

    .zf-editorial-banner-title {
        margin: 30px 0 0;
        font-family: var(--zf-serif);
        font-size: clamp(46px, 5vw, 84px);
        font-weight: 400;
        letter-spacing: -.065em;
        line-height: .92;
    }

    .zf-editorial-banner-title em {
        color: var(--zf-accent-soft);
        font-style: italic;
    }

    .zf-editorial-banner-copy {
        max-width: 370px;
        margin: 30px 0 0;
        color: rgba(255, 255, 255, .64);
        font-size: 13px;
        line-height: 2;
    }

    .zf-editorial-banner-content .zf-button {
        margin-top: 38px;
        background: #fff;
        color: var(--zf-ink);
    }

    .zf-editorial-banner-content .zf-button:hover {
        background: var(--zf-accent-soft);
        color: var(--zf-ink);
    }

    .zf-editorial-banner-content .zf-button-arrow {
        color: #fff;
    }

    .zf-promo-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .zf-promo-theme-maroon_gold {
        --promo-bg: linear-gradient(135deg, #5b1728, #a87932);
        --promo-shade: rgba(91, 23, 40, .88);
    }

    .zf-promo-theme-rose {
        --promo-bg: linear-gradient(135deg, #8f4560, #e5a9b9);
        --promo-shade: rgba(143, 69, 96, .88);
    }

    .zf-promo-theme-emerald {
        --promo-bg: linear-gradient(135deg, #064e3b, #34d399);
        --promo-shade: rgba(6, 78, 59, .88);
    }

    .zf-promo-theme-midnight {
        --promo-bg: linear-gradient(135deg, #111827, #374151);
        --promo-shade: rgba(17, 24, 39, .90);
    }

    .zf-promo-theme-cream {
        --promo-bg: linear-gradient(135deg, #d6c2a1, #fff8e7);
        --promo-shade: rgba(126, 93, 52, .82);
    }

    .zf-promo-card {
        background: var(--promo-bg, linear-gradient(135deg, #5b1728, #a87932));

        position: relative;
        min-height: 380px;
        overflow: hidden;
        color: #fff;
    }

    .zf-promo-card-image {
        position: absolute;
        inset: 0;
    }

    .zf-promo-card-image img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
        opacity: .88;
        mix-blend-mode: normal;
        filter: saturate(.7);
        transition: transform 1.3s cubic-bezier(.22, .61, .36, 1);
    }

    .zf-promo-card:hover .zf-promo-card-image img {
        transform: scale(1.06);
    }

    .zf-promo-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        background:
            radial-gradient(
                circle at 50% 20%,
                rgba(218, 183, 134, .18),
                transparent 35%
            ),
            linear-gradient(135deg, #5b293b, #28151d);
        color: var(--zf-accent-soft);
    }

    .zf-promo-fallback span {
        font-family: var(--zf-display);
        font-size: 130px;
        line-height: 1;
    }

    .zf-promo-fallback small {
        margin-top: 16px;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .35em;
    }

    .zf-promo-card-shade {
        position: absolute;
        inset: 0;
        z-index: 1;
        pointer-events: none;
        background:
            linear-gradient(
                180deg,
                rgba(0, 0, 0, .02) 0%,
                rgba(0, 0, 0, .08) 38%,
                rgba(0, 0, 0, .78) 100%
            ),
            linear-gradient(
                0deg,
                var(--promo-shade, rgba(91, 23, 40, .88)) 0%,
                transparent 68%
            );
    }


    .zf-promo-card-content {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 380px;
        padding: 28px;
        font-family: var(--zf-body, 'Inter', sans-serif);
    }

    .zf-promo-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .zf-promo-card-label {
        padding: 7px 11px;
        border: 1px solid rgba(255,255,255,.45);
        border-radius: 999px;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .18em;
        text-transform: uppercase;
        backdrop-filter: blur(8px);
    }

    .zf-promo-card-number {
        font-family: var(--zf-display, 'Playfair Display', serif);
        font-size: 22px;
        font-style: italic;
        font-weight: 400;
        letter-spacing: .04em;
        opacity: .85;
    }

    .zf-promo-card-main {
        max-width: 92%;
    }


    .zf-promo-value {
        position: relative;
        display: inline-block;
        width: fit-content;
        margin-bottom: 18px;
        font-family: 'Playfair Display', Georgia, serif;
        font-size: clamp(54px, 5vw, 86px);
        font-weight: 800;
        font-style: italic;
        letter-spacing: -.075em;
        line-height: .88;
        color: #f7d58a;
        text-shadow:
            0 0 8px rgba(247,213,138,.65),
            0 0 24px rgba(247,213,138,.35),
            0 4px 18px rgba(0,0,0,.3);
        background: linear-gradient(
            115deg,
            #fff8d6 0%,
            #f7d58a 25%,
            #fff 42%,
            #c9953d 58%,
            #fff0b0 78%,
            #f7d58a 100%
        );
        background-size: 220% auto;
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: zfPromoNumberShine 4.5s linear infinite;
    }

    .zf-promo-value::after {
        content: "";
        position: absolute;
        inset: -12px -20px;
        border-radius: 50%;
        background: radial-gradient(
            ellipse,
            rgba(255,255,255,.22),
            transparent 68%
        );
        filter: blur(10px);
        opacity: .7;
        pointer-events: none;
        animation: zfPromoNumberGlow 2.8s ease-in-out infinite;
    }

    .zf-promo-theme-emerald .zf-promo-value {
        color: #8affd0;
        text-shadow:
            0 0 8px rgba(138,255,208,.8),
            0 0 28px rgba(52,211,153,.5),
            0 4px 18px rgba(0,0,0,.3);
        background: linear-gradient(
            115deg,
            #d1fae5 0%,
            #6ee7b7 25%,
            #ffffff 42%,
            #10b981 58%,
            #a7f3d0 78%,
            #34d399 100%
        );
        background-size: 220% auto;
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .zf-promo-theme-cream .zf-promo-value {
        color: #9a682f;
        text-shadow:
            0 0 8px rgba(255,221,153,.7),
            0 0 22px rgba(154,104,47,.25);
        background: linear-gradient(
            115deg,
            #8b5e34 0%,
            #e8c58b 25%,
            #fff8df 42%,
            #a87932 58%,
            #f5deb0 78%,
            #8b5e34 100%
        );
        background-size: 220% auto;
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    @keyframes zfPromoNumberShine {
        0% {
            background-position: 220% center;
        }
        100% {
            background-position: -220% center;
        }
    }

    @keyframes zfPromoNumberGlow {
        0%, 100% {
            transform: scale(.92);
            opacity: .35;
        }
        50% {
            transform: scale(1.08);
            opacity: .8;
        }
    }

    .zf-promo-value {
        display: block;
        margin-bottom: 12px;
        font-family: var(--zf-display, 'Playfair Display', serif);
        font-size: clamp(42px, 4vw, 68px);
        font-weight: 400;
        font-style: italic;
        letter-spacing: -.055em;
        line-height: .9;
    }

    .zf-promo-title {
        margin: 0 0 13px;
        font-family: var(--zf-display, 'Playfair Display', serif);
        font-size: clamp(25px, 2.4vw, 38px);
        font-weight: 500;
        letter-spacing: -.04em;
        line-height: 1.08;
    }

    .zf-promo-description {
        max-width: 290px;
        margin: 0;
        font-family: var(--zf-body, 'Inter', sans-serif);
        font-size: 11px;
        font-weight: 400;
        line-height: 1.8;
        letter-spacing: .015em;
        opacity: .82;
    }

    .zf-promo-card-bottom {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 18px;
        padding-top: 20px;
        border-top: 1px solid rgba(255,255,255,.28);
    }

    .zf-promo-code {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .zf-promo-code span {
        font-size: 8px;
        font-weight: 700;
        letter-spacing: .2em;
        text-transform: uppercase;
        opacity: .7;
    }

    .zf-promo-code strong {
        font-size: 13px;
        font-weight: 800;
        letter-spacing: .14em;
    }

    .zf-promo-copy {
        padding: 10px 13px;
        border: 1px solid rgba(255,255,255,.45);
        border-radius: 999px;
        background: rgba(255,255,255,.08);
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
        transition: .25s ease;
    }

    .zf-promo-copy:hover {
        background: rgba(255,255,255,.22);
        transform: translateY(-2px);
    }

    .zf-promo-theme-emerald .zf-promo-title {
        font-family: var(--zf-body, 'Inter', sans-serif);
        font-size: clamp(23px, 2.1vw, 32px);
        font-weight: 800;
        letter-spacing: -.055em;
        text-transform: uppercase;
    }

    .zf-promo-theme-emerald .zf-promo-value {
        font-family: var(--zf-body, 'Inter', sans-serif);
        font-style: normal;
        font-weight: 800;
        letter-spacing: -.08em;
    }

    .zf-promo-theme-cream {
        color: #4b3028;
    }

    .zf-promo-theme-cream .zf-promo-card-label,
    .zf-promo-theme-cream .zf-promo-card-bottom {
        border-color: rgba(75,48,40,.35);
    }

    .zf-promo-theme-cream .zf-promo-copy {
        border-color: rgba(75,48,40,.4);
        background: rgba(75,48,40,.06);
    }

    .zf-promo-theme-cream .zf-promo-title {
        font-size: clamp(27px, 2.5vw, 40px);
        font-style: italic;
    }

    .zf-promo-theme-cream .zf-promo-description,
    .zf-promo-theme-cream .zf-promo-code span {
        opacity: .7;
    }

    .zf-promo-card-content {
        position: relative;
        z-index: 2;
        display: flex;
        min-height: 380px;
        flex-direction: column;
        justify-content: space-between;
        padding: 27px;
    }

    .zf-promo-card-top,
    .zf-promo-card-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
    }

    .zf-promo-card-label {
        color: rgba(255, 255, 255, .7);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .2em;
        text-transform: uppercase;
    }

    .zf-promo-card-number {
        color: var(--zf-accent-soft);
        font-family: var(--zf-display);
        font-size: 30px;
        line-height: .8;
    }

    .zf-promo-value {
        display: block;
        color: var(--zf-accent-soft);
        font-family: var(--zf-display);
        font-size: 78px;
        font-weight: 400;
        letter-spacing: -.08em;
        line-height: .85;
    }

    .zf-promo-title {
        max-width: 250px;
        margin: 22px 0 0;
        font-family: var(--zf-serif);
        font-size: 31px;
        font-weight: 400;
        letter-spacing: -.045em;
        line-height: .95;
    }

    .zf-promo-description {
        max-width: 270px;
        margin: 15px 0 0;
        color: rgba(255, 255, 255, .64);
        font-size: 11px;
        line-height: 1.8;
    }

    .zf-promo-code {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .zf-promo-code span {
        color: rgba(255, 255, 255, .5);
        font-size: 8px;
        font-weight: 800;
        letter-spacing: .15em;
        text-transform: uppercase;
    }

    .zf-promo-code strong {
        color: #fff;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .13em;
    }

    .zf-promo-copy {
        display: inline-flex;
        min-height: 38px;
        align-items: center;
        justify-content: center;
        padding: 0 15px;
        border: 1px solid rgba(255, 255, 255, .4);
        border-radius: 999px;
        background: transparent;
        color: #fff;
        cursor: pointer;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
        transition:
            background var(--zf-transition),
            color var(--zf-transition),
            border-color var(--zf-transition);
    }

    .zf-promo-copy:hover {
        border-color: #fff;
        background: #fff;
        color: var(--zf-ink);
    }


    /* ============================================================
       FONT & HIGHLIGHT KHUSUS SETIAP PROMO
       ============================================================ */

    .zf-promo-theme-maroon_gold .zf-promo-value {
        font-family: 'Playfair Display', serif;
        color: #f4d58d;
        text-shadow: 0 0 18px rgba(244, 213, 141, .35);
    }

    .zf-promo-theme-maroon_gold .zf-promo-title {
        font-family: 'Cormorant Garamond', serif;
        font-style: italic;
        font-weight: 600;
        color: #fff4d6;
    }

    .zf-promo-theme-rose .zf-promo-value {
        font-family: 'Montserrat', sans-serif;
        font-weight: 800;
        color: #ffd1df;
        letter-spacing: -.06em;
    }

    .zf-promo-theme-rose .zf-promo-title {
        font-family: 'DM Sans', sans-serif;
        font-weight: 700;
        color: #fff0f5;
        letter-spacing: -.04em;
    }

    .zf-promo-theme-emerald .zf-promo-value {
        font-family: 'Space Grotesk', sans-serif;
        font-weight: 800;
        color: #a7f3d0;
        text-shadow: 0 0 16px rgba(167, 243, 208, .3);
    }

    .zf-promo-theme-emerald .zf-promo-title {
        font-family: 'Space Grotesk', sans-serif;
        font-weight: 800;
        color: #ecfdf5;
        text-transform: uppercase;
        letter-spacing: -.055em;
    }

    .zf-promo-theme-midnight .zf-promo-value {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 86px;
        letter-spacing: .01em;
        color: #dbeafe;
        text-shadow: 0 0 20px rgba(147, 197, 253, .35);
    }

    .zf-promo-theme-midnight .zf-promo-title {
        font-family: 'Oswald', sans-serif;
        font-weight: 600;
        color: #f8fafc;
        letter-spacing: -.02em;
        text-transform: uppercase;
    }

    .zf-promo-theme-cream .zf-promo-value {
        font-family: 'Libre Baskerville', serif;
        font-weight: 700;
        color: #8b5e34;
    }

    .zf-promo-theme-cream .zf-promo-title {
        font-family: 'Cormorant Garamond', serif;
        font-weight: 600;
        font-style: italic;
        color: #5b3928;
    }

    .zf-promo-theme-maroon_gold .zf-promo-card-label,
    .zf-promo-theme-maroon_gold .zf-promo-code strong {
        color: #f4d58d;
    }

    .zf-promo-theme-rose .zf-promo-card-label,
    .zf-promo-theme-rose .zf-promo-code strong {
        color: #ffd1df;
    }

    .zf-promo-theme-emerald .zf-promo-card-label,
    .zf-promo-theme-emerald .zf-promo-code strong {
        color: #a7f3d0;
    }

    .zf-promo-theme-midnight .zf-promo-card-label,
    .zf-promo-theme-midnight .zf-promo-code strong {
        color: #bfdbfe;
    }

    .zf-promo-theme-cream .zf-promo-card-label,
    .zf-promo-theme-cream .zf-promo-code strong {
        color: #8b5e34;
    }


/* Warna angka promo berbeda setiap tema */
.zf-promo-theme-maroon_gold .zf-promo-value {
    color: #f6c453 !important;
    text-shadow: 0 0 18px rgba(246, 196, 83, .35);
}

.zf-promo-theme-rose .zf-promo-value {
    color: #e85d9e !important;
    text-shadow: 0 0 18px rgba(232, 93, 158, .35);
}

.zf-promo-theme-emerald .zf-promo-value {
    color: #42d9ad !important;
    text-shadow: 0 0 18px rgba(66, 217, 173, .35);
}

.zf-promo-theme-midnight .zf-promo-value {
    color: #6bb7ff !important;
    text-shadow: 0 0 18px rgba(107, 183, 255, .35);
}

.zf-promo-theme-cream .zf-promo-value {
    color: #b87935 !important;
    text-shadow: 0 0 18px rgba(184, 121, 53, .3);
}

/* Warna kode promo juga dibedakan */
.zf-promo-theme-maroon_gold .zf-promo-code strong {
    color: #f6c453 !important;
}

.zf-promo-theme-rose .zf-promo-code strong {
    color: #e85d9e !important;
}

.zf-promo-theme-emerald .zf-promo-code strong {
    color: #42d9ad !important;
}

.zf-promo-theme-midnight .zf-promo-code strong {
    color: #6bb7ff !important;
}

.zf-promo-theme-cream .zf-promo-code strong {
    color: #b87935 !important;
}


/* Warna persentase / nominal potongan promo */
.zf-promo-theme-maroon_gold .zf-promo-value {
    color: #ffd166 !important;
}

.zf-promo-theme-rose .zf-promo-value {
    color: #ff5caa !important;
}

.zf-promo-theme-emerald .zf-promo-value {
    color: #55f2bd !important;
}

.zf-promo-theme-midnight .zf-promo-value {
    color: #72b9ff !important;
}

.zf-promo-theme-cream .zf-promo-value {
    color: #c47b36 !important;
}

    .zf-signature-layout {
        display: grid;
        grid-template-columns: .88fr 1.12fr;
        align-items: center;
        gap: clamp(50px, 8vw, 140px);
    }

    .zf-signature-title {
        margin: 28px 0 0;
        font-family: var(--zf-serif);
        font-size: clamp(48px, 5.5vw, 86px);
        font-weight: 400;
        letter-spacing: -.065em;
        line-height: .9;
    }

    .zf-signature-title em {
        color: var(--zf-accent);
        font-style: italic;
    }

    .zf-signature-description {
        max-width: 420px;
        margin-top: 30px;
        color: var(--zf-muted);
        font-size: 13px;
        line-height: 2;
    }

    .zf-signature-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-top: 42px;
        padding-top: 24px;
        border-top: 1px solid var(--zf-line);
    }

    .zf-signature-detail-number {
        display: block;
        color: var(--zf-accent);
        font-family: var(--zf-display);
        font-size: 30px;
        line-height: .8;
    }

    .zf-signature-detail-title {
        display: block;
        margin-top: 18px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .15em;
        text-transform: uppercase;
    }

    .zf-signature-details p {
        margin-top: 12px;
        color: var(--zf-muted);
        font-size: 11px;
        line-height: 1.8;
    }

    .zf-signature-copy .zf-button {
        margin-top: 42px;
    }

    .zf-signature-visual {
        position: relative;
        min-height: 680px;
    }

    .zf-signature-main-image {
        position: absolute;
        top: 0;
        right: 0;
        width: 77%;
        height: 610px;
        overflow: hidden;
        background: var(--zf-paper-alt);
    }

    .zf-signature-main-image img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
        filter: saturate(.82);
        transition: transform 1.3s cubic-bezier(.22, .61, .36, 1);
    }

    .zf-signature-visual:hover .zf-signature-main-image img {
        transform: scale(1.04);
    }

    .zf-signature-small-image {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 43%;
        height: 300px;
        overflow: hidden;
        border: 12px solid var(--zf-paper);
        background: var(--zf-paper-alt);
        box-shadow: var(--zf-shadow-soft);
    }

    .zf-signature-small-image img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
        filter: saturate(.82);
    }

    .zf-signature-fallback {
        background:
            linear-gradient(135deg, #5b293b, #28151d);
    }

    .zf-signature-fallback span {
        font-family: var(--zf-display);
        font-size: 170px;
        line-height: 1;
    }

    .zf-signature-small-fallback {
        background:
            linear-gradient(135deg, #ead8c2, #b9956b);
        color: #542331;
    }

    .zf-signature-small-fallback span {
        font-family: var(--zf-display);
        font-size: 100px;
        line-height: 1;
    }

    .zf-signature-stamp {
        position: absolute;
        right: -20px;
        bottom: 90px;
        display: flex;
        width: 132px;
        height: 132px;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 6px;
        border: 1px solid var(--zf-accent);
        border-radius: 50%;
        background: var(--zf-paper);
        color: var(--zf-accent);
        transform: rotate(12deg);
    }

    .zf-signature-stamp span {
        font-family: var(--zf-display);
        font-size: 48px;
        line-height: .8;
    }

    .zf-signature-stamp small {
        font-size: 8px;
        font-weight: 800;
        letter-spacing: .14em;
        line-height: 1.5;
        text-align: center;
        text-transform: uppercase;
    }

    @media (max-width: 1100px) {
        .zf-more-products-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            row-gap: 45px;
        }
    }

    @media (max-width: 900px) {
        .zf-editorial-banner {
            grid-template-columns: 1fr;
        }

        .zf-editorial-banner-image,
        .zf-editorial-fallback {
            min-height: 450px;
        }

        .zf-editorial-banner-content {
            min-height: 500px;
        }

        .zf-promo-grid {
            grid-template-columns: 1fr 1fr;
        }

        .zf-promo-card:last-child {
            grid-column: 1 / -1;
        }

        .zf-signature-layout {
            grid-template-columns: 1fr;
            gap: 55px;
        }

        .zf-signature-copy {
            max-width: 650px;
        }

        .zf-signature-visual {
            min-height: 620px;
        }

        .zf-signature-main-image {
            width: 78%;
            height: 550px;
        }
    }

    @media (max-width: 620px) {
        .zf-more-products-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 24px 12px;
        }

        .zf-featured-product-info {
            padding-top: 14px;
        }

        .zf-featured-product-name {
            font-size: 23px;
        }

        .zf-product-topline {
            align-items: flex-start;
            flex-direction: column;
            gap: 4px;
        }

        .zf-product-quick {
            right: 10px;
            bottom: 10px;
            left: 10px;
            padding: 11px 12px;
            font-size: 8px;
        }

        .zf-product-index {
            top: 12px;
            left: 12px;
        }

        .zf-editorial-banner {
            margin-top: 30px;
        }

        .zf-editorial-banner-image,
        .zf-editorial-fallback {
            min-height: 350px;
        }

        .zf-editorial-banner-content {
            min-height: 430px;
            padding: 55px 26px;
        }

        .zf-editorial-banner-title {
            font-size: 56px;
        }

        .zf-promo-grid {
            grid-template-columns: 1fr;
        }

        .zf-promo-card:last-child {
            grid-column: auto;
        }

        .zf-promo-card,
        .zf-promo-card-content {
            min-height: 360px;
        }

        .zf-promo-card-content {
            padding: 24px;
        }

        .zf-promo-value {
            font-size: 70px;
        }

        .zf-signature-details {
            grid-template-columns: 1fr;
        }

        .zf-signature-visual {
            min-height: 470px;
        }

        .zf-signature-main-image {
            width: 82%;
            height: 400px;
        }

        .zf-signature-small-image {
            width: 48%;
            height: 210px;
            border-width: 8px;
        }

        .zf-signature-stamp {
            right: -5px;
            bottom: 55px;
            width: 95px;
            height: 95px;
        }

        .zf-signature-stamp span {
            font-size: 34px;
        }

        .zf-signature-stamp small {
            font-size: 6px;
        }

        .zf-signature-fallback span {
            font-size: 110px;
        }
    }
</style>


{{-- ======================================================================
     JAVASCRIPT KODE KE-2
     ====================================================================== --}}

<script>
    document.addEventListener('DOMContentLoaded', function () {
        /*
        |--------------------------------------------------------------------------
        | COPY PROMO CODE
        |--------------------------------------------------------------------------
        */

        document.querySelectorAll('[data-copy-code]').forEach(function (button) {
            button.addEventListener('click', async function () {
                const code = button.getAttribute('data-copy-code');

                if (!code) {
                    return;
                }

                try {
                    await navigator.clipboard.writeText(code);

                    const originalText = button.textContent;

                    button.textContent = 'Copied';

                    setTimeout(function () {
                        button.textContent = originalText;
                    }, 1600);
                } catch (error) {
                    const temporaryInput = document.createElement('input');

                    temporaryInput.value = code;
                    document.body.appendChild(temporaryInput);
                    temporaryInput.select();

                    try {
                        document.execCommand('copy');
                    } catch (copyError) {
                        console.warn('Kode promo tidak dapat disalin.');
                    }

                    temporaryInput.remove();

                    const originalText = button.textContent;

                    button.textContent = 'Copied';

                    setTimeout(function () {
                        button.textContent = originalText;
                    }, 1600);
                }
            });
        });

        /*
        |--------------------------------------------------------------------------
        | ANIMASI SECTION SAAT MASUK VIEWPORT
        |--------------------------------------------------------------------------
        | Catatan anti-tabrakan: setiap kelompok elemen punya kelas & observer
        | sendiri (reveal biasa, category wipe, promo flip) supaya transisinya
        | tidak saling menimpa properti CSS yang sama di elemen yang sama.
        */

        const revealElements = document.querySelectorAll(
            '.zf-featured-product, .zf-signature-copy, .zf-signature-visual, ' +
            '.zf-section-heading-copy, .zf-section-heading-action'
        );

        if ('IntersectionObserver' in window) {
            const revealObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('zf-revealed');
                    revealObserver.unobserve(entry.target);
                });
            }, {
                threshold: 0.12
            });

            revealElements.forEach(function (element) {
                element.classList.add('zf-reveal');
                revealObserver.observe(element);
            });
        } else {
            revealElements.forEach(function (element) {
                element.classList.add('zf-reveal', 'zf-revealed');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | CATEGORY CARD — MUNCUL DARI TENGAH, MELEBAR KANAN/KIRI 1 PER 1
        |--------------------------------------------------------------------------
        | Reveal card (opacity/scale + teks) jalan langsung saat kartu masuk
        | viewport, TIDAK menunggu gambar. Wipe gambar (clip-path) baru main
        | begitu file gambar itu sendiri benar-benar selesai dimuat
        | (img.complete / event 'load'), jadi kalau gambarnya berat/lambat,
        | teks & judul tetap muncul tepat waktu dan area gambar cuma
        | menunjukkan shimmer singkat sambil menunggu — bukan blank kosong.
        */

        const categoryCards = document.querySelectorAll('.zf-category-card');

        function markCategoryImageReady(card, img) {
            card.classList.remove('zf-cat-loading');

            if (img) {
                img.classList.add('zf-cat-img-ready');
            }
        }

        function revealCategoryCard(card) {
            card.classList.add('zf-cat-revealed');

            const visual = card.querySelector('.zf-category-card-image');

            if (!visual) {
                return;
            }

            // Fallback (tanpa gambar asli dari admin) langsung siap, tidak
            // perlu menunggu apa pun.
            if (visual.tagName !== 'IMG') {
                markCategoryImageReady(card, visual);
                return;
            }

            const img = visual;

            if (img.complete && img.naturalWidth > 0) {
                markCategoryImageReady(card, img);
                return;
            }

            card.classList.add('zf-cat-loading');

            img.addEventListener('load', function () {
                markCategoryImageReady(card, img);
            }, { once: true });

            img.addEventListener('error', function () {
                markCategoryImageReady(card, img);
            }, { once: true });
        }

        if ('IntersectionObserver' in window) {
            const categoryObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    revealCategoryCard(entry.target);
                    categoryObserver.unobserve(entry.target);
                });
            }, {
                threshold: 0.2
            });

            categoryCards.forEach(function (card) {
                categoryObserver.observe(card);
            });
        } else {
            categoryCards.forEach(revealCategoryCard);
        }

        /*
        |--------------------------------------------------------------------------
        | PROMO CARD — ANIMASI MASUK ALA KARTU ATM + BISA DIBALIK (FLIP)
        |--------------------------------------------------------------------------
        */

        const promoCards = document.querySelectorAll('.zf-promo-card');

        if ('IntersectionObserver' in window) {
            const promoObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('zf-promo-revealed');
                    promoObserver.unobserve(entry.target);
                });
            }, {
                threshold: 0.15
            });

            promoCards.forEach(function (card) {
                promoObserver.observe(card);
            });
        } else {
            promoCards.forEach(function (card) {
                card.classList.add('zf-promo-revealed');
            });
        }

        /* Klik / tap untuk membalik kartu promo, mendukung mobile & desktop.
           Menggunakan querySelectorAll supaya TOMBOL DI DEPAN & DI BELAKANG
           kartu sama-sama berfungsi berkali-kali (sebelumnya hanya tombol
           depan yang punya event listener, jadi setelah dibalik sekali,
           tombol di sisi belakang tidak merespons). */
        promoCards.forEach(function (card) {
            const flipTriggers = card.querySelectorAll('[data-promo-flip]');

            if (!flipTriggers.length) {
                return;
            }

            flipTriggers.forEach(function (flipTrigger) {
                flipTrigger.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    card.classList.toggle('is-flipped');
                });
            });

            card.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    card.classList.toggle('is-flipped');
                }
            });
        });

        /*
        |--------------------------------------------------------------------------
        | "LESS NOISE. MORE PRESENCE." — EDITORIAL BANNER REVEAL
        |--------------------------------------------------------------------------
        */

        const editorialBanner = document.querySelector('.zf-editorial-reveal');

        if (editorialBanner) {
            if ('IntersectionObserver' in window) {
                const editorialObserver = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (!entry.isIntersecting) {
                            return;
                        }

                        entry.target.classList.add('zf-editorial-revealed');
                        editorialObserver.unobserve(entry.target);
                    });
                }, {
                    threshold: 0.2
                });

                editorialObserver.observe(editorialBanner);
            } else {
                editorialBanner.classList.add('zf-editorial-revealed');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SHOP ALL PIECES — HORIZONTAL SLIDER (1 PRODUK PER TAMPILAN)
        |--------------------------------------------------------------------------
        */

        document.querySelectorAll('[data-shop-slider]').forEach(function (slider) {
            const track = slider.querySelector('[data-shop-track]');
            const slides = Array.from(slider.querySelectorAll('[data-shop-slide]'));
            const dots = Array.from(slider.querySelectorAll('[data-shop-dot]'));
            const prevBtn = slider.querySelector('[data-shop-prev]');
            const nextBtn = slider.querySelector('[data-shop-next]');

            if (!track || slides.length === 0) {
                return;
            }

            let current = 0;
            let autoplayTimer = null;
            const AUTOPLAY_DELAY = 2000;

            function renderSlide() {
                const activeSlide = slides[current];

                if (!activeSlide) {
                    return;
                }

                const viewport = slider.querySelector('.zf-shop-slider-viewport');
                const slideWidth = activeSlide.getBoundingClientRect().width;
                const trackStyle = window.getComputedStyle(track);
                const gap = parseFloat(trackStyle.gap || trackStyle.columnGap || 0);
                const viewportWidth = viewport.getBoundingClientRect().width;

                const slideCenter = current * (slideWidth + gap) + (slideWidth / 2);
                const viewportCenter = viewportWidth / 2;
                const offset = slideCenter - viewportCenter;

                track.style.transform =
                    'translate3d(-' + Math.max(0, offset) + 'px, 0, 0)';

                slides.forEach(function (slide, index) {
                    const distance = Math.abs(index - current);
                    const isActive = index === current;

                    slide.classList.toggle('is-active', isActive);
                    slide.classList.toggle('is-neighbor', distance === 1);

                    slide.setAttribute(
                        'aria-hidden',
                        distance <= 1 ? 'false' : 'true'
                    );
                });

                dots.forEach(function (dot, index) {
                    dot.classList.toggle('is-active', index === current);
                });
            }

            window.addEventListener('resize', function () {
                renderSlide();
            });

            function goTo(index) {
                current = (index + slides.length) % slides.length;
                renderSlide();
            }

            function next() {
                goTo(current + 1);
            }

            function prev() {
                goTo(current - 1);
            }

            function startAutoplay() {
                stopAutoplay();
                autoplayTimer = window.setInterval(next, AUTOPLAY_DELAY);
            }

            function stopAutoplay() {
                if (autoplayTimer) {
                    window.clearInterval(autoplayTimer);
                    autoplayTimer = null;
                }
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function () {
                    next();
                    startAutoplay();
                });
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', function () {
                    prev();
                    startAutoplay();
                });
            }

            dots.forEach(function (dot) {
                dot.addEventListener('click', function () {
                    goTo(parseInt(dot.getAttribute('data-shop-dot'), 10) || 0);
                    startAutoplay();
                });
            });

            slider.addEventListener('mouseenter', stopAutoplay);
            slider.addEventListener('mouseleave', startAutoplay);
            slider.addEventListener('focusin', stopAutoplay);
            slider.addEventListener('focusout', startAutoplay);

            /* Swipe geser di layar sentuh */
            let touchStartX = null;

            track.addEventListener('touchstart', function (event) {
                touchStartX = event.touches[0].clientX;
                stopAutoplay();
            }, { passive: true });

            track.addEventListener('touchend', function (event) {
                if (touchStartX === null) {
                    return;
                }

                const deltaX = event.changedTouches[0].clientX - touchStartX;

                if (deltaX > 40) {
                    prev();
                } else if (deltaX < -40) {
                    next();
                }

                touchStartX = null;
                startAutoplay();
            }, { passive: true });

            /* Geser pakai cursor (mouse drag) di desktop — responsif, satu
               mekanisme slider yang sama dipakai untuk touch & mouse supaya
               animasinya tidak bertabrakan dengan slider lain. */
            let isPointerDown = false;
            let pointerStartX = 0;

            track.addEventListener('pointerdown', function (event) {
                if (event.pointerType === 'touch') {
                    return; // sudah ditangani lewat touchstart/touchend
                }

                isPointerDown = true;
                pointerStartX = event.clientX;
                slider.classList.add('is-dragging');
                stopAutoplay();
            });

            window.addEventListener('pointermove', function (event) {
                if (!isPointerDown) {
                    return;
                }

                event.preventDefault();
            });

            window.addEventListener('pointerup', function (event) {
                if (!isPointerDown) {
                    return;
                }

                isPointerDown = false;
                slider.classList.remove('is-dragging');

                const deltaX = event.clientX - pointerStartX;

                if (deltaX > 40) {
                    prev();
                } else if (deltaX < -40) {
                    next();
                }

                startAutoplay();
            });

            track.addEventListener('dragstart', function (event) {
                event.preventDefault();
            });

            if (slides.length > 1 && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                startAutoplay();
            }

            renderSlide();
        });
    });
</script>


<style>
    .zf-reveal {
        opacity: 0;
        transform: translateY(24px);
        transition:
            opacity 800ms ease,
            transform 800ms cubic-bezier(.22, .61, .36, 1);
    }

    .zf-revealed {
        opacity: 1;
        transform: translateY(0);
    }

    @media (prefers-reduced-motion: reduce) {
        .zf-reveal {
            opacity: 1;
            transform: none;
            transition: none;
        }

        .zf-product-image-wrap img,
        .zf-editorial-banner-image img,
        .zf-signature-main-image img {
            transition: none;
        }
    }
</style>
<style>
/* =========================================================
   SHOP ALL PIECES — RESPONSIVE DESKTOP & MOBILE FIX
   ========================================================= */

.zf-shop-slider {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    overflow: hidden;
}

.zf-shop-slider-viewport {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    overflow: hidden;
}

.zf-shop-slider-track {
    display: flex;
    width: 100%;
    max-width: 100%;
    min-width: 0;
}

.zf-shop-slide {
    position: relative;
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr);
    width: 100%;
    min-width: 100%;
    max-width: 100%;
    flex: 0 0 100%;
    gap: clamp(18px, 4vw, 70px);
    overflow: hidden;
    box-sizing: border-box;
}

.zf-shop-slide-image,
.zf-shop-slide-info {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    box-sizing: border-box;
}

.zf-shop-slide-image {
    overflow: hidden;
}

.zf-shop-slide .zf-product-image-wrap {
    width: 100%;
    max-width: 100%;
    min-width: 0;
}

.zf-shop-slide-info {
    overflow-wrap: anywhere;
}

@media (max-width: 780px) {
    .zf-shop-slider {
        width: 100%;
        max-width: 100%;
        margin: 0;
        padding: 0;
        overflow: hidden;
    }

    .zf-shop-slider-viewport {
        width: 100%;
        max-width: 100%;
        margin: 0;
        padding: 0;
        border-radius: 14px;
        overflow: hidden;
    }

    .zf-shop-slider-track {
        width: 100%;
        max-width: 100%;
    }

    .zf-shop-slide {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        justify-content: flex-start;
        width: 100%;
        min-width: 100%;
        max-width: 100%;
        flex: 0 0 100%;
        gap: 18px;
        padding: 0;
        transform: none;
    }

    .zf-shop-slide.is-active {
        transform: none;
    }

    .zf-shop-slide-image {
        width: 100%;
        max-width: 100%;
        flex: 0 0 auto;
    }

    .zf-shop-slide .zf-product-image-wrap {
        width: 100%;
        max-width: 100%;
        height: auto;
        max-height: none;
        aspect-ratio: 4 / 5;
    }

    .zf-shop-slide-info {
        width: 100%;
        max-width: 100%;
        padding: 0 4px 18px;
        gap: 5px;
    }

    .zf-shop-slide-name {
        max-width: 100%;
        font-size: clamp(26px, 8vw, 36px);
        line-height: 1.08;
        overflow-wrap: anywhere;
    }

    .zf-shop-slide-info .zf-product-price,
    .zf-shop-slide-info .zf-product-old-price {
        max-width: 100%;
        overflow-wrap: anywhere;
    }
}

@media (max-width: 480px) {
    .zf-shop-slide {
        gap: 14px;
    }

    .zf-shop-slide .zf-product-image-wrap {
        aspect-ratio: 4 / 5;
    }

    .zf-shop-slide-info {
        padding: 0 2px 14px;
    }

    .zf-shop-slide-name {
        font-size: 28px;
    }
}
</style>

<style>
/* =========================================================
   SHOP ALL PIECES — MULTI CARD SLIDER
   ========================================================= */

.zf-shop-slider {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    overflow: hidden;
}

.zf-shop-slider-viewport {
    width: 100%;
    max-width: 100%;
    overflow: hidden;
}

.zf-shop-slider-track {
    display: flex;
    gap: 20px;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    transition: transform 700ms cubic-bezier(.22, .61, .36, 1);
    will-change: transform;
}

.zf-shop-slide {
    flex: 0 0 calc((100% - 40px) / 3);
    width: calc((100% - 40px) / 3);
    min-width: 0;
    max-width: calc((100% - 40px) / 3);
    display: flex;
    flex-direction: column;
    gap: 16px;
    opacity: 1;
    transform: none;
    overflow: hidden;
    box-sizing: border-box;
}

.zf-shop-slide .zf-product-image-wrap {
    width: 100%;
    aspect-ratio: 4 / 5;
    max-height: none;
}

.zf-shop-slide-info {
    width: 100%;
    min-width: 0;
    padding: 0;
    overflow: hidden;
}

.zf-shop-slide-name {
    font-size: clamp(22px, 2vw, 32px);
    line-height: 1.1;
    overflow-wrap: anywhere;
}

@media (max-width: 900px) {
    .zf-shop-slider-track {
        gap: 16px;
    }

    .zf-shop-slide {
        flex-basis: calc((100% - 16px) / 2);
        width: calc((100% - 16px) / 2);
        max-width: calc((100% - 16px) / 2);
    }
}

@media (max-width: 560px) {
    .zf-shop-slider-track {
        gap: 12px;
    }

    .zf-shop-slide {
        flex: 0 0 calc(86% - 6px);
        width: calc(86% - 6px);
        max-width: calc(86% - 6px);
        gap: 12px;
    }

    .zf-shop-slide .zf-product-image-wrap {
        aspect-ratio: 4 / 5;
    }

    .zf-shop-slide-name {
        font-size: 25px;
    }
}
</style>

<style>
/* FINAL SHOP SLIDER SIZE */
.zf-shop-slider-viewport {
    overflow: hidden;
    width: 100%;
    padding: 10px 0 22px;
}

.zf-shop-slider-track {
    gap: 16px !important;
    width: max-content !important;
}

.zf-shop-slide {
    flex: 0 0 280px !important;
    width: 280px !important;
    max-width: 280px !important;
    min-width: 0 !important;
    transform: scale(.88) !important;
    opacity: .65 !important;
}

.zf-shop-slide.is-active {
    transform: scale(.96) !important;
    opacity: 1 !important;
    z-index: 2;
}

.zf-shop-slide.is-neighbor {
    transform: scale(.90) !important;
    opacity: .78 !important;
}

@media (max-width: 1100px) {
    .zf-shop-slide {
        flex-basis: 240px !important;
        width: 240px !important;
        max-width: 240px !important;
    }
}

@media (max-width: 700px) {
    .zf-shop-slider-track {
        gap: 12px !important;
    }

    .zf-shop-slide {
        flex-basis: 76vw !important;
        width: 76vw !important;
        max-width: 76vw !important;
        transform: scale(.92) !important;
    }

    .zf-shop-slide.is-active {
        transform: scale(.98) !important;
    }
}
</style>

<style>
/* PRESISI: 3 CARD DESKTOP, 1 CARD MOBILE */
.zf-shop-slider-viewport {
    overflow: hidden !important;
    width: 100% !important;
    padding: 12px 0 24px !important;
}

.zf-shop-slider-track {
    display: flex !important;
    gap: 16px !important;
    width: max-content !important;
    align-items: stretch !important;
    transform-origin: left center !important;
}

.zf-shop-slide,
.zf-shop-slide.is-active,
.zf-shop-slide.is-neighbor {
    box-sizing: border-box !important;
    flex: 0 0 calc((100vw - 160px) / 3) !important;
    width: calc((100vw - 160px) / 3) !important;
    max-width: 340px !important;
    min-width: 0 !important;
    opacity: 1 !important;
    transform: none !important;
    filter: none !important;
    transition: opacity .35s ease !important;
}

@media (min-width: 1200px) {
    .zf-shop-slide,
    .zf-shop-slide.is-active,
    .zf-shop-slide.is-neighbor {
        flex-basis: 300px !important;
        width: 300px !important;
    }
}

@media (max-width: 900px) {
    .zf-shop-slide,
    .zf-shop-slide.is-active,
    .zf-shop-slide.is-neighbor {
        flex-basis: 260px !important;
        width: 260px !important;
    }
}

@media (max-width: 600px) {
    .zf-shop-slider-track {
        gap: 12px !important;
    }

    .zf-shop-slide,
    .zf-shop-slide.is-active,
    .zf-shop-slide.is-neighbor {
        flex-basis: calc(100vw - 110px) !important;
        width: calc(100vw - 110px) !important;
        max-width: none !important;
    }
}
</style>

<style>
@media (max-width: 600px) {
    .zf-shop-slider-viewport {
        padding-left: 8px !important;
        padding-right: 8px !important;
    }

    .zf-shop-slider-track {
        gap: 10px !important;
    }

    .zf-shop-slide,
    .zf-shop-slide.is-active,
    .zf-shop-slide.is-neighbor {
        flex: 0 0 64vw !important;
        width: 64vw !important;
        max-width: 64vw !important;
        transform: none !important;
        opacity: 1 !important;
    }

    .zf-shop-slide img {
        max-height: 260px !important;
        object-fit: contain !important;
    }
}
</style>

<style>
/* SHOP SLIDER — ACTIVE CARD MENONJOL */
.zf-shop-slide {
    position: relative;
    opacity: .38 !important;
    filter: brightness(.58) saturate(.72) !important;
    transform: translateY(10px) scale(.94) !important;
    transition:
        transform .7s cubic-bezier(.22, .61, .36, 1),
        opacity .7s ease,
        filter .7s ease,
        box-shadow .7s ease !important;
    will-change: transform, opacity, filter;
}

.zf-shop-slide.is-neighbor {
    opacity: .62 !important;
    filter: brightness(.78) saturate(.88) !important;
    transform: translateY(5px) scale(.97) !important;
}

.zf-shop-slide.is-active {
    opacity: 1 !important;
    filter: brightness(1) saturate(1) !important;
    transform: translateY(-4px) scale(1) !important;
    z-index: 5;
    box-shadow:
        0 18px 35px rgba(75, 20, 35, .16),
        0 4px 12px rgba(75, 20, 35, .08) !important;
}

.zf-shop-slide.is-active::after {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: inherit;
    pointer-events: none;
    box-shadow:
        inset 0 0 0 1px rgba(181, 145, 82, .24),
        0 0 0 1px rgba(181, 145, 82, .08);
}

@media (prefers-reduced-motion: reduce) {
    .zf-shop-slide,
    .zf-shop-slide.is-active,
    .zf-shop-slide.is-neighbor {
        transition: none !important;
        transform: none !important;
    }
}

@media (max-width: 600px) {
    .zf-shop-slide {
        transform: translateY(6px) scale(.97) !important;
        opacity: .42 !important;
    }

    .zf-shop-slide.is-neighbor {
        transform: translateY(3px) scale(.985) !important;
        opacity: .65 !important;
    }

    .zf-shop-slide.is-active {
        transform: translateY(-3px) scale(1) !important;
        opacity: 1 !important;
    }
}
</style>

<style id="zf-card-final-adjustment">
.zf-shop-slider-viewport {
    overflow: hidden !important;
    padding-left: 24px !important;
    padding-right: 24px !important;
}

.zf-shop-slider-track {
    align-items: stretch !important;
    padding-top: 18px !important;
    padding-bottom: 24px !important;
}

.zf-shop-slide {
    box-sizing: border-box !important;
    overflow: visible !important;
}

.zf-shop-slide.is-active {
    transform: translateY(-8px) scale(1.025) !important;
    transform-origin: center center !important;
    z-index: 20 !important;
}

.zf-shop-slide .zf-button {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 18px !important;
    min-width: 150px !important;
    min-height: 46px !important;
    padding: 12px 14px 12px 18px !important;
    background: #ffffff !important;
    color: #171717 !important;
    border: 1px solid rgba(23, 23, 23, .16) !important;
    border-radius: 0 !important;
    box-shadow: 0 8px 18px rgba(0, 0, 0, .08) !important;
    text-decoration: none !important;
    transition: background .25s ease, color .25s ease, transform .25s ease !important;
}

.zf-shop-slide .zf-button span:first-child {
    color: #171717 !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    letter-spacing: .08em !important;
    text-transform: uppercase !important;
    white-space: nowrap !important;
}

.zf-shop-slide .zf-button .zf-button-arrow {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 24px !important;
    height: 24px !important;
    background: #171717 !important;
    color: #ffffff !important;
    font-size: 16px !important;
    line-height: 1 !important;
    flex-shrink: 0 !important;
}

.zf-shop-slide .zf-button:hover {
    background: #171717 !important;
    color: #ffffff !important;
    transform: translateY(-2px) !important;
}

.zf-shop-slide .zf-button:hover span:first-child {
    color: #ffffff !important;
}

.zf-shop-slide .zf-button:hover .zf-button-arrow {
    background: #ffffff !important;
    color: #171717 !important;
}

@media (max-width: 600px) {
    .zf-shop-slider-viewport {
        padding-left: 16px !important;
        padding-right: 16px !important;
    }

    .zf-shop-slide.is-active {
        transform: translateY(-5px) scale(1.015) !important;
    }

    .zf-shop-slide .zf-button {
        min-width: 136px !important;
        gap: 12px !important;
        padding: 11px 12px 11px 14px !important;
    }
}
</style>


<style id="zf-highlight-card-square">
.zf-shop-slide {
    min-height: 520px !important;
    border-radius: 0 !important;
}

.zf-shop-slide.is-active {
    flex-basis: 380px !important;
    width: 380px !important;
    max-width: 380px !important;
    min-height: 560px !important;
    transform: translateY(-10px) scale(1.03) !important;
}

.zf-shop-slide .zf-shop-slide-content,
.zf-shop-slide .zf-product-card-content,
.zf-shop-slide > div {
    min-width: 0 !important;
}

.zf-shop-slide img {
    width: 100% !important;
    height: 330px !important;
    object-fit: cover !important;
}

@media (max-width: 900px) {
    .zf-shop-slide.is-active {
        flex-basis: 340px !important;
        width: 340px !important;
        max-width: 340px !important;
    }
}

@media (max-width: 600px) {
    .zf-shop-slide {
        min-height: 460px !important;
    }

    .zf-shop-slide.is-active {
        flex-basis: 82vw !important;
        width: 82vw !important;
        max-width: 82vw !important;
        min-height: 500px !important;
        transform: translateY(-6px) scale(1.02) !important;
    }

    .zf-shop-slide img {
        height: 270px !important;
    }
}
</style>


<style id="zf-premium-product-info">
.zf-shop-slide-info {
    box-sizing: border-box !important;
    width: 100% !important;
    max-width: 100% !important;
    padding: 24px 28px 28px !important;
    overflow: hidden !important;
}

.zf-product-topline {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 12px !important;
    margin-bottom: 14px !important;
    padding: 0 !important;
}

.zf-product-category,
.zf-product-status {
    font-family: var(--zf-sans) !important;
    font-size: 9px !important;
    letter-spacing: .12em !important;
    line-height: 1.4 !important;
    text-transform: uppercase !important;
}

.zf-shop-slide-name {
    max-width: 100% !important;
    margin: 0 0 14px !important;
    padding: 0 !important;
    font-family: var(--zf-display) !important;
    font-size: clamp(22px, 2.2vw, 31px) !important;
    font-weight: 500 !important;
    letter-spacing: -.025em !important;
    line-height: 1.08 !important;
    overflow-wrap: anywhere !important;
}

.zf-shop-slide-name a {
    color: var(--zf-ink) !important;
    text-decoration: none !important;
}

.zf-price-group {
    display: flex !important;
    align-items: baseline !important;
    flex-wrap: wrap !important;
    gap: 10px !important;
    margin: 0 0 22px !important;
    padding: 0 !important;
}

.zf-product-price {
    color: var(--zf-accent) !important;
    font-family: var(--zf-display) !important;
    font-size: clamp(25px, 2.5vw, 36px) !important;
    font-weight: 700 !important;
    letter-spacing: -.035em !important;
    line-height: 1 !important;
    white-space: nowrap !important;
}

.zf-product-old-price {
    color: #8d8587 !important;
    font-family: var(--zf-sans) !important;
    font-size: clamp(11px, 1vw, 13px) !important;
    font-weight: 500 !important;
    letter-spacing: .01em !important;
    line-height: 1.2 !important;
    text-decoration: line-through !important;
    text-decoration-thickness: 1px !important;
    white-space: nowrap !important;
}

.zf-shop-slide .zf-button {
    align-self: flex-start !important;
    width: fit-content !important;
    max-width: 100% !important;
    margin-top: 4px !important;
    padding: 12px 14px 12px 18px !important;
    font-family: var(--zf-sans) !important;
    font-size: 10px !important;
    letter-spacing: .11em !important;
    white-space: nowrap !important;
}

@media (max-width: 900px) {
    .zf-shop-slide-info {
        padding: 22px 22px 24px !important;
    }

    .zf-shop-slide-name {
        font-size: 25px !important;
    }

    .zf-product-price {
        font-size: 30px !important;
    }
}

@media (max-width: 600px) {
    .zf-shop-slide-info {
        padding: 20px 18px 22px !important;
    }

    .zf-product-topline {
        margin-bottom: 11px !important;
    }

    .zf-shop-slide-name {
        font-size: 23px !important;
        line-height: 1.12 !important;
    }

    .zf-price-group {
        gap: 8px !important;
        margin-bottom: 18px !important;
    }

    .zf-product-price {
        font-size: 28px !important;
    }

    .zf-product-old-price {
        font-size: 11px !important;
    }
}
</style>


<style id="zf-product-image-no-crop">
.zf-shop-slide-image,
.zf-product-image-wrap {
    position: relative !important;
    overflow: hidden !important;
    display: block !important;
    height: 330px !important;
    min-height: 330px !important;
    background: #f7f3f1 !important;
}

.zf-shop-slide img {
    display: block !important;
    width: 100% !important;
    height: 100% !important;
    object-fit: contain !important;
    object-position: center center !important;
    transform: translateY(10px) !important;
}

.zf-shop-slide.is-active img {
    transform: translateY(10px) !important;
}

@media (max-width: 900px) {
    .zf-shop-slide-image,
    .zf-product-image-wrap {
        height: 300px !important;
        min-height: 300px !important;
    }
}

@media (max-width: 600px) {
    .zf-shop-slide-image,
    .zf-product-image-wrap {
        height: 270px !important;
        min-height: 270px !important;
    }

    .zf-shop-slide img,
    .zf-shop-slide.is-active img {
        transform: translateY(6px) !important;
    }
}
</style>


<style id="zf-discover-signature-premium">
.zf-signature-content .zf-button,
.zf-signature-copy .zf-button {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 22px !important;
    min-width: 210px !important;
    min-height: 52px !important;
    padding: 0 12px 0 20px !important;
    border: 1px solid #ded8d3 !important;
    border-radius: 0 !important;
    background: #ffffff !important;
    color: #171717 !important;
    font-family: var(--zf-sans) !important;
    font-size: 10px !important;
    font-weight: 700 !important;
    letter-spacing: .13em !important;
    text-transform: uppercase !important;
    box-shadow: 0 8px 24px rgba(48, 27, 33, .07) !important;
}

.zf-signature-content .zf-button > span:first-child,
.zf-signature-copy .zf-button > span:first-child {
    color: #171717 !important;
    white-space: nowrap !important;
}

.zf-signature-content .zf-button .zf-button-arrow,
.zf-signature-copy .zf-button .zf-button-arrow {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 30px !important;
    height: 30px !important;
    background: #171717 !important;
    color: #ffffff !important;
    font-size: 17px !important;
    line-height: 1 !important;
}

.zf-signature-content .zf-button:hover,
.zf-signature-copy .zf-button:hover {
    background: #171717 !important;
    color: #ffffff !important;
    transform: translateY(-2px) !important;
}

.zf-signature-content .zf-button:hover > span:first-child,
.zf-signature-copy .zf-button:hover > span:first-child {
    color: #ffffff !important;
}

.zf-signature-content .zf-button:hover .zf-button-arrow,
.zf-signature-copy .zf-button:hover .zf-button-arrow {
    background: #ffffff !important;
    color: #171717 !important;
}

@media (max-width: 600px) {
    .zf-signature-content .zf-button,
    .zf-signature-copy .zf-button {
        min-width: 190px !important;
        min-height: 48px !important;
        gap: 14px !important;
        padding-left: 16px !important;
    }
}
</style>


<style id="zf-full-catalog-premium">
.zf-section-heading-action {
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
}

.zf-link-arrow {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 24px !important;
    min-width: 214px !important;
    min-height: 54px !important;
    padding: 0 12px 0 20px !important;
    border: 1px solid #d9d1cc !important;
    border-radius: 0 !important;
    background: #ffffff !important;
    color: #171717 !important;
    font-family: var(--zf-sans) !important;
    font-size: 10px !important;
    font-weight: 700 !important;
    letter-spacing: .13em !important;
    line-height: 1 !important;
    text-transform: uppercase !important;
    text-decoration: none !important;
    box-shadow: 0 8px 22px rgba(48, 27, 33, .06) !important;
    transition:
        background .3s ease,
        color .3s ease,
        transform .3s ease,
        box-shadow .3s ease !important;
}

.zf-link-arrow > span:first-child {
    color: #171717 !important;
    white-space: nowrap !important;
}

.zf-link-arrow > span:last-child {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 30px !important;
    height: 30px !important;
    background: #171717 !important;
    color: #ffffff !important;
    font-size: 17px !important;
    line-height: 1 !important;
    flex-shrink: 0 !important;
}

.zf-link-arrow:hover {
    background: #171717 !important;
    color: #ffffff !important;
    transform: translateY(-3px) !important;
    box-shadow: 0 12px 28px rgba(23, 23, 23, .16) !important;
}

.zf-link-arrow:hover > span:first-child {
    color: #ffffff !important;
}

.zf-link-arrow:hover > span:last-child {
    background: #ffffff !important;
    color: #171717 !important;
}

@media (max-width: 600px) {
    .zf-section-heading-action {
        width: 100% !important;
        justify-content: flex-start !important;
        margin-top: 22px !important;
        padding-top: 18px !important;
        border-top: 1px solid rgba(100, 76, 67, .18) !important;
    }

    .zf-link-arrow {
        width: 100% !important;
        min-width: 0 !important;
        min-height: 52px !important;
        gap: 16px !important;
        padding-left: 18px !important;
        font-size: 10px !important;
        letter-spacing: .11em !important;
    }
}
</style>

{{-- ======================================================================
     SHOP ALL PIECES — SLIDER FINAL (menang atas semua definisi lama)

     - Transisi antar-slide dibuat mulus (sebelumnya track berpindah
       posisi secara instan/patah, tanpa transition sama sekali).
     - Slide non-aktif dibuat agak gelap & mengecil, slide aktif
       'timbul' (naik + membesar + shadow dalam).
     - Kartu apa pun (aktif atau tidak) ikut 'timbul' saat disentuh
       kursor/jari, agar terasa interaktif.
     - Mobile: lebar slide aktif dikecilkan supaya 2 tetangga kiri-kanan
       ikut terlihat sebagian — pola 3 kartu (gelap – terang – gelap).
     ====================================================================== --}}

<style id="zf-shop-slider-final-premium">

    #home-page .zf-shop-slider-viewport {
        overflow: hidden !important;
    }

    /* Perpindahan antar-slide dibuat mulus */
    #home-page .zf-shop-slider-track {
        transition: transform .65s cubic-bezier(.22, .61, .36, 1) !important;
    }

    /* Kondisi dasar: semua kartu sedikit gelap & mengecil */
    #home-page .zf-shop-slide {
        opacity: .5 !important;
        filter: saturate(.72) brightness(.9) !important;
        transform: scale(.86) !important;
        transition:
            transform .55s cubic-bezier(.22, .61, .36, 1),
            opacity .55s ease,
            filter .55s ease,
            box-shadow .4s ease !important;
        will-change: transform, opacity;
    }

    /* Tetangga kanan-kiri kartu aktif: sedikit lebih terang */
    #home-page .zf-shop-slide.is-neighbor {
        opacity: .68 !important;
        filter: saturate(.85) brightness(.95) !important;
        transform: scale(.93) !important;
    }

    /* Kartu aktif: timbul penuh — terang, naik, membesar, shadow dalam */
    #home-page .zf-shop-slide.is-active {
        opacity: 1 !important;
        filter: none !important;
        transform: translateY(-12px) scale(1.05) !important;
        box-shadow:
            0 34px 64px rgba(43, 15, 20, .28),
            0 10px 22px rgba(43, 15, 20, .14) !important;
        z-index: 20 !important;
    }

    /* Kartu mana pun ikut 'timbul' saat disentuh kursor / jari,
       terlepas dari status aktif atau tidak. */
    #home-page .zf-shop-slide:hover,
    #home-page .zf-shop-slide:focus-within,
    #home-page .zf-shop-slide:active {
        opacity: 1 !important;
        filter: none !important;
        transform: translateY(-12px) scale(1.06) !important;
        box-shadow:
            0 34px 64px rgba(43, 15, 20, .3),
            0 10px 22px rgba(43, 15, 20, .16) !important;
        z-index: 25 !important;
    }

    /* ---------- MOBILE: 3 kartu — gelap / terang-timbul / gelap ---------- */

    @media (max-width: 700px) {

        #home-page .zf-shop-slider-track {
            gap: 14px !important;
        }

        #home-page .zf-shop-slide {
            flex: 0 0 60vw !important;
            width: 60vw !important;
            max-width: 60vw !important;
            min-height: 420px !important;
        }

        #home-page .zf-shop-slide.is-active {
            flex: 0 0 68vw !important;
            width: 68vw !important;
            max-width: 68vw !important;
            min-height: 460px !important;
            transform: translateY(-8px) scale(1.04) !important;
        }

        #home-page .zf-shop-slide:hover,
        #home-page .zf-shop-slide:focus-within,
        #home-page .zf-shop-slide:active {
            transform: translateY(-8px) scale(1.05) !important;
        }

        #home-page .zf-shop-slide img {
            height: 240px !important;
        }

    }

    @media (prefers-reduced-motion: reduce) {

        #home-page .zf-shop-slider-track,
        #home-page .zf-shop-slide {
            transition: none !important;
        }

    }

</style>


</div>
{{-- /#home-page --}}

{{-- ============================================================
     ZALINA PREMIUM CUSTOMER SERVICE FLOATING CHAT
     ============================================================ --}}
<style>
    #zalina-customer-service-float {
        position: fixed;
        right: 24px;
        bottom: 128px;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transform: translateY(24px) scale(.9);
        transition:
            opacity .35s ease,
            visibility .35s ease,
            transform .35s cubic-bezier(.22, 1, .36, 1);
    }

    #zalina-customer-service-float.is-visible {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        transform: translateY(0) scale(1);
    }

    #zalina-customer-service-float.is-scrolling {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transform: translateY(18px) scale(.9);
    }

    .zalina-cs-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        gap: 13px;
    }

    .zalina-cs-message {
        position: relative;
        min-width: 190px;
        max-width: 225px;
        padding: 13px 17px;
        border: 1px solid rgba(255,255,255,.95);
        border-radius: 17px;
        background:
            linear-gradient(
                145deg,
                rgba(255,255,255,.98),
                rgba(255,255,255,.78)
            );
        box-shadow:
            0 15px 36px rgba(50,30,35,.16),
            0 4px 12px rgba(50,30,35,.08),
            inset 0 1px 0 rgba(255,255,255,1);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        color: #292326;
        font-family: inherit;
    }

    .zalina-cs-message::after {
        content: "";
        position: absolute;
        top: 50%;
        right: -8px;
        width: 15px;
        height: 15px;
        background: rgba(255,255,255,.9);
        border-top: 1px solid rgba(255,255,255,.95);
        border-right: 1px solid rgba(255,255,255,.95);
        transform: translateY(-50%) rotate(45deg);
    }

    .zalina-cs-message strong {
        position: relative;
        z-index: 1;
        display: block;
        margin-bottom: 4px;
        color: #171315;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .02em;
    }

    .zalina-cs-message span {
        position: relative;
        z-index: 1;
        display: block;
        color: #625b5e;
        font-size: 11px;
        font-weight: 500;
        line-height: 1.5;
    }

    .zalina-cs-button {
        position: relative;
        display: grid;
        width: 76px;
        height: 76px;
        place-items: center;
        border: 1px solid rgba(255,255,255,.98);
        border-radius: 27px 27px 27px 9px;
        background:
            linear-gradient(
                145deg,
                #ffffff 0%,
                #fdfdfd 55%,
                #eeeeee 100%
            );
        box-shadow:
            0 17px 38px rgba(45,30,35,.22),
            0 5px 13px rgba(45,30,35,.1),
            inset 0 1px 0 rgba(255,255,255,1),
            inset 0 -4px 10px rgba(0,0,0,.035);
        text-decoration: none;
        transition:
            transform .3s ease,
            box-shadow .3s ease;
    }

    .zalina-cs-button:hover {
        transform: translateY(-5px) scale(1.05);
        box-shadow:
            0 23px 48px rgba(45,30,35,.28),
            0 7px 16px rgba(45,30,35,.13),
            inset 0 1px 0 rgba(255,255,255,1);
    }

    .zalina-cs-button:focus-visible {
        outline: 3px solid rgba(35,35,35,.3);
        outline-offset: 5px;
    }

    .zalina-cs-button::before {
        content: "";
        position: absolute;
        inset: -8px;
        border: 1px solid rgba(255,255,255,.9);
        border-radius: 33px 33px 33px 13px;
        animation: zalinaCsPulse 2.8s ease-out infinite;
    }

    .zalina-cs-tail {
        position: absolute;
        right: -1px;
        bottom: -1px;
        width: 24px;
        height: 24px;
        background: #ffffff;
        clip-path: polygon(0 0, 100% 0, 100% 100%);
        border-bottom-right-radius: 8px;
        z-index: 1;
    }

    .zalina-cs-icon {
        position: relative;
        z-index: 3;
        display: block;
        width: 55px;
        height: 55px;
        filter:
            drop-shadow(0 2px 2px rgba(0,0,0,.13))
            drop-shadow(0 4px 7px rgba(0,0,0,.08));
    }

    .zalina-cs-icon .black-main {
        fill: #151515;
        stroke: #151515;
        stroke-width: 1.2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .zalina-cs-icon .black-detail {
        fill: none;
        stroke: #151515;
        stroke-width: 2.7;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .zalina-cs-status {
        position: absolute;
        top: 4px;
        right: 5px;
        z-index: 6;
        width: 12px;
        height: 12px;
        border: 2px solid #ffffff;
        border-radius: 50%;
        background: #75b98b;
        box-shadow: 0 2px 7px rgba(40,70,45,.2);
    }

    .zalina-cs-caption {
        position: absolute;
        right: 0;
        bottom: -22px;
        width: 76px;
        color: #ffffff;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .1em;
        line-height: 1;
        text-align: center;
        text-shadow: 0 2px 5px rgba(40,25,30,.3);
        text-transform: uppercase;
    }

    @keyframes zalinaCsPulse {
        0% {
            opacity: .7;
            transform: scale(.92);
        }

        70% {
            opacity: 0;
            transform: scale(1.28);
        }

        100% {
            opacity: 0;
            transform: scale(1.28);
        }
    }

    @media (max-width: 640px) {
        #zalina-customer-service-float {
            right: 15px;
            bottom: 108px;
        }

        .zalina-cs-wrapper {
            gap: 9px;
        }

        .zalina-cs-message {
            min-width: 0;
            max-width: 165px;
            padding: 10px 12px;
            border-radius: 14px;
        }

        .zalina-cs-message strong {
            font-size: 10px;
        }

        .zalina-cs-message span {
            font-size: 10px;
        }

        .zalina-cs-button {
            width: 64px;
            height: 64px;
            border-radius: 23px 23px 23px 8px;
        }

        .zalina-cs-icon {
            width: 47px;
            height: 47px;
        }

        .zalina-cs-caption {
            width: 64px;
            bottom: -19px;
            font-size: 8px;
        }

        .zalina-cs-tail {
            width: 20px;
            height: 20px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #zalina-customer-service-float,
        .zalina-cs-button,
        .zalina-cs-button::before {
            animation: none !important;
            transition: none !important;
        }
    }
</style>

<div
    id="zalina-customer-service-float"
    aria-label="Customer service Zalina Fashion"
>
    <div class="zalina-cs-wrapper">

        <div class="zalina-cs-message">
            <strong>Butuh bantuan?</strong>
            <span>Klik ikon ini untuk chat admin.</span>
        </div>

        <a
            class="zalina-cs-button"
            href="https://wa.me/628133117767"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Chat admin Zalina Fashion melalui WhatsApp"
        >
            <span class="zalina-cs-status"></span>

            <svg
                class="zalina-cs-icon"
                viewBox="0 0 100 100"
                role="img"
                aria-hidden="true"
            >
                <!-- Headset bagian atas -->
                <path
                    class="black-main"
                    d="M18 48V38C18 20 32 8 50 8s32 12 32 30v10h-8V38C74 25 64 17 50 17S26 25 26 38v10z"
                />

                <!-- Headset kiri -->
                <path
                    class="black-main"
                    d="M15 44h12v30H15c-7 0-12-5-12-12v-6c0-7 5-12 12-12z"
                />

                <!-- Headset kanan -->
                <path
                    class="black-main"
                    d="M73 44h12c7 0 12 5 12 12v6c0 7-5 12-12 12H73z"
                />

                <!-- Kepala -->
                <path
                    class="black-main"
                    d="M29 42c0-14 9-24 21-24s21 10 21 24v17c0 15-9 26-21 26S29 74 29 59z"
                />

                <!-- Rambut -->
                <path
                    class="black-main"
                    d="M29 44c0-16 8-26 21-26 12 0 21 9 21 24-8 1-15-3-21-10-5 7-12 11-21 12z"
                />

                <!-- Mata -->
                <circle cx="43" cy="54" r="2.5" fill="#ffffff"/>
                <circle cx="57" cy="54" r="2.5" fill="#ffffff"/>

                <!-- Senyum -->
                <path
                    class="black-detail"
                    d="M43 66c4 4 10 4 14 0"
                />

                <!-- Mikrofon customer service -->
                <path
                    class="black-main"
                    d="M29 72c0 9 8 16 18 16h7v-7h-7c-6 0-11-4-11-9z"
                />

                <path
                    class="black-detail"
                    d="M54 88h8c8 0 14-6 14-14"
                />

                <circle
                    cx="76"
                    cy="74"
                    r="4"
                    fill="#151515"
                />
            </svg>

            <span class="zalina-cs-tail"></span>
            <span class="zalina-cs-caption">Chat Admin</span>
        </a>
    </div>
</div>

<script>
    (() => {
        const widget = document.getElementById(
            'zalina-customer-service-float'
        );

        if (!widget) return;

        let scrollTimer = null;

        const showWidget = () => {
            widget.classList.add('is-visible');
            widget.classList.remove('is-scrolling');
        };

        const hideWidget = () => {
            widget.classList.remove('is-visible');
            widget.classList.add('is-scrolling');
        };

        const handleScroll = () => {
            hideWidget();

            clearTimeout(scrollTimer);

            scrollTimer = setTimeout(() => {
                showWidget();
            }, 650);
        };

        window.addEventListener('scroll', handleScroll, {
            passive: true
        });

        window.addEventListener('load', () => {
            setTimeout(showWidget, 900);
        });

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                hideWidget();
            }
        });
    })();
</script>
{{-- ============================================================
     END ZALINA PREMIUM CUSTOMER SERVICE FLOATING CHAT
     ============================================================ --}}

{{-- ============================================================
     PEMBARUAN ANIMASI — CARD KATEGORI, PRODUK, & PROMO
     Blok ini sengaja diletakkan paling akhir supaya aturan di
     dalamnya menjadi "kata terakhir" dan tidak ditimpa oleh
     definisi duplikat/lama di atas — inilah yang membuat animasi
     lama saling bertabrakan di berbagai breakpoint.
     ============================================================ --}}
<style>
    /* -------------------------------------------------------------
       1) PROMO CARD — ANIMASI MASUK ALA KARTU ATM + BISA DIBALIK
       ------------------------------------------------------------- */

    .zf-promo-card {
        perspective: 1600px;
        min-height: 380px;
        background: transparent !important;
        opacity: 0;
        transform: translateY(28px) rotateY(-12deg) scale(.94);
        transition:
            opacity .8s cubic-bezier(.22, .61, .36, 1),
            transform .9s cubic-bezier(.22, .61, .36, 1);
        transition-delay: var(--zf-cat-delay, 0ms);
        outline: none;
    }

    .zf-promo-card.zf-promo-revealed {
        opacity: 1;
        transform: translateY(0) rotateY(0deg) scale(1);
    }

    .zf-promo-card-inner {
        position: relative;
        width: 100%;
        min-height: 380px;
        transform-style: preserve-3d;
        transition: transform .8s cubic-bezier(.65, 0, .35, 1);
    }

    .zf-promo-card.is-flipped .zf-promo-card-inner {
        transform: rotateY(180deg);
    }

    .zf-promo-card-face {
        position: absolute;
        inset: 0;
        overflow: hidden;
        border-radius: 14px;
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
        min-height: 380px;
    }

    .zf-promo-card-front {
        position: relative;
    }

    .zf-promo-card-back {
        transform: rotateY(180deg);
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: var(--promo-bg, linear-gradient(135deg, #5b1728, #a87932));
        color: #fff;
    }

    .zf-promo-card-back-stripe {
        width: 100%;
        height: 46px;
        margin-top: 26px;
        background: repeating-linear-gradient(
            135deg,
            rgba(0, 0, 0, .85),
            rgba(0, 0, 0, .85) 6px,
            rgba(0, 0, 0, .7) 6px,
            rgba(0, 0, 0, .7) 12px
        );
    }

    .zf-promo-card-back-content {
        display: flex;
        flex-direction: column;
        gap: 16px;
        padding: 24px 26px 28px;
    }

    .zf-promo-code-back strong {
        font-size: 20px;
        letter-spacing: .18em;
    }

    .zf-promo-card-back-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 8px;
    }

    .zf-promo-flip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        border: 1px solid rgba(255, 255, 255, .45);
        border-radius: 999px;
        background: rgba(255, 255, 255, .08);
        color: #fff;
        font-size: 16px;
        line-height: 1;
        cursor: pointer;
        transition: transform .3s ease, background .25s ease;
    }

    .zf-promo-flip:hover {
        background: rgba(255, 255, 255, .22);
        transform: rotate(180deg);
    }

    .zf-promo-theme-cream .zf-promo-flip {
        border-color: rgba(75, 48, 40, .4);
        background: rgba(75, 48, 40, .06);
        color: #4b3028;
    }

    @media (hover: none) {
        /* Mobile / touch: tanpa hover, cukup tap tombol flip */
        .zf-promo-flip:hover {
            transform: none;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .zf-promo-card {
            opacity: 1 !important;
            transform: none !important;
            transition: none !important;
        }

        .zf-promo-card-inner {
            transition: none !important;
        }
    }

    /* -------------------------------------------------------------
       2) SHOP SLIDER — SATU-SATUNYA SLIDER RESPONSIF (CURSOR + TOUCH)
       Menjadi penentu akhir supaya tidak bentrok dengan definisi
       duplikat sebelumnya di file ini.
       ------------------------------------------------------------- */

    .zf-shop-slider-track {
        cursor: grab;
        touch-action: pan-y;
        user-select: none;
    }

    .zf-shop-slider.is-dragging .zf-shop-slider-track {
        cursor: grabbing;
    }

    .zf-shop-slide img {
        -webkit-user-drag: none;
        user-drag: none;
    }

    @media (max-width: 900px) {
        .zf-shop-slider-track {
            touch-action: pan-y;
        }
    }

    /* -------------------------------------------------------------
       3) GRID PRODUK — RESPONSIF DI SEMUA UKURAN LAYAR
       Auto-fit supaya jumlah kolom menyesuaikan lebar layar tanpa
       overflow, tanpa perlu daftar breakpoint manual yang saling
       tumpang tindih.
       ------------------------------------------------------------- */

    #more-pieces .zf-more-products-grid,
    .zf-more-products-grid {
        display: grid !important;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) !important;
        gap: clamp(14px, 2vw, 24px) !important;
    }

    @media (max-width: 560px) {
        #more-pieces .zf-more-products-grid,
        .zf-more-products-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 12px !important;
        }
    }

    @media (max-width: 380px) {
        #more-pieces .zf-more-products-grid,
        .zf-more-products-grid {
            grid-template-columns: 1fr !important;
        }
    }

    .zf-promo-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    @media (max-width: 860px) {
        .zf-promo-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 560px) {
        .zf-promo-grid {
            grid-template-columns: 1fr;
        }

        .zf-promo-card,
        .zf-promo-card-inner,
        .zf-promo-card-face {
            min-height: 340px;
        }
    }

    /* -------------------------------------------------------------
       4) ANTI-TABRAKAN ANIMASI HOVER vs REVEAL
       Pastikan transform hover pada gambar & tombol tidak pernah
       ikut ter-delay oleh --zf-cat-delay milik reveal scroll.
       ------------------------------------------------------------- */

    .zf-featured-product-image,
    .zf-product-image-wrap img,
    .zf-product-arrow,
    .zf-promo-card-image img {
        transition-delay: 0ms !important;
    }

    /* -------------------------------------------------------------
       5) "MORE PIECES" — RESET LAYOUT (versi final, menang cascade)
       File ini punya beberapa definisi grid/card lama yang saling
       tumpang-tindih (grid 3 kolom vs 4 kolom, kartu diputar miring
       ala editorial-stack, animasi keyframe otomatis) sehingga
       tampilannya berantakan di desktop maupun mobile. Blok ini
       menjadi definisi tunggal & final untuk section #more-pieces.
       ------------------------------------------------------------- */

    #more-pieces .zf-more-products-grid {
        display: grid !important;
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        gap: clamp(16px, 2vw, 28px) !important;
        align-items: start !important;
    }

    @media (max-width: 1180px) {
        #more-pieces .zf-more-products-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        }
    }

    @media (max-width: 760px) {
        #more-pieces .zf-more-products-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 20px 14px !important;
        }
    }

    @media (max-width: 420px) {
        #more-pieces .zf-more-products-grid {
            grid-template-columns: minmax(0, 1fr) !important;
        }
    }

    /* Matikan animasi keyframe lama & kemiringan kartu editorial-stack
       yang menyebabkan card tabrakan/overlap saat grid menyempit. */
    #more-pieces .zf-more-product {
        position: relative !important;
        min-width: 0 !important;
        max-width: none !important;
        animation: none !important;
        opacity: 0;
        transform: translateY(28px) !important;
        transition:
            opacity .8s ease,
            transform .8s cubic-bezier(.22, .61, .36, 1) !important;
    }

    #more-pieces .zf-more-product.zf-revealed {
        opacity: 1;
        transform: translateY(0) !important;
    }

    #more-pieces .zf-more-product .zf-featured-product-image,
    #more-pieces .zf-more-product:nth-child(1) .zf-featured-product-image,
    #more-pieces .zf-more-product:nth-child(2) .zf-featured-product-image,
    #more-pieces .zf-more-product:nth-child(3) .zf-featured-product-image {
        display: block !important;
        position: relative !important;
        width: 100% !important;
        overflow: hidden !important;
        border-radius: 18px !important;
        background: var(--zf-paper-alt, #eee5df) !important;
        box-shadow: 0 14px 34px rgba(67, 37, 42, .1) !important;
        transform: none !important;
        transition: box-shadow .45s ease !important;
    }

    #more-pieces .zf-more-product:hover .zf-featured-product-image {
        box-shadow: 0 22px 48px rgba(67, 37, 42, .16) !important;
    }

    #more-pieces .zf-more-product .zf-product-image-wrap {
        position: relative !important;
        width: 100% !important;
        aspect-ratio: 4 / 5 !important;
        overflow: hidden !important;
    }

    #more-pieces .zf-more-product .zf-product-image-wrap img {
        width: 100% !important;
        height: 100% !important;
        display: block !important;
        object-fit: cover !important;
        transform: none !important;
        transition: transform 1s cubic-bezier(.22, .61, .36, 1), filter .7s ease !important;
    }

    #more-pieces .zf-more-product:hover .zf-product-image-wrap img {
        transform: scale(1.06) !important;
    }

    #more-pieces .zf-more-product .zf-featured-product-info {
        padding: 18px 2px 0 !important;
    }

    #more-pieces .zf-more-product .zf-featured-product-name {
        margin: 8px 0 12px !important;
        font-size: clamp(19px, 1.8vw, 26px) !important;
        line-height: 1.15 !important;
    }

    #more-pieces .zf-more-product .zf-featured-product-bottom {
        flex-wrap: wrap !important;
        row-gap: 8px !important;
    }

    @media (prefers-reduced-motion: reduce) {
        #more-pieces .zf-more-product {
            opacity: 1;
            transform: none !important;
            transition: none !important;
        }
    }

    /* -------------------------------------------------------------
       6) "LESS NOISE. MORE PRESENCE." — EDITORIAL BANNER REVEAL
       Gambar melebar seperti tirai (clip-path), teks & tombol fade-up
       menyusul. Didukung penuh di mobile & desktop.
       ------------------------------------------------------------- */

    .zf-editorial-reveal .zf-editorial-banner-image {
        clip-path: inset(0 0 0 100%);
        transition: clip-path 1.4s cubic-bezier(.65, 0, .35, 1);
    }

    .zf-editorial-reveal.zf-editorial-revealed .zf-editorial-banner-image {
        clip-path: inset(0 0 0 0%);
    }

    .zf-editorial-reveal-item {
        display: inline-block;
        opacity: 0;
        transform: translateY(22px);
        transition: opacity .8s ease, transform .8s cubic-bezier(.22, .61, .36, 1);
    }

    h2.zf-editorial-reveal-item,
    p.zf-editorial-reveal-item {
        display: block;
    }

    .zf-editorial-reveal-item:nth-of-type(1) { transition-delay: .15s; }
    .zf-editorial-reveal-item:nth-of-type(2) { transition-delay: .35s; }
    .zf-editorial-reveal-item:nth-of-type(3) { transition-delay: .55s; }
    .zf-editorial-reveal-item:nth-of-type(4) { transition-delay: .75s; }

    .zf-editorial-reveal.zf-editorial-revealed .zf-editorial-reveal-item {
        opacity: 1;
        transform: translateY(0);
    }

    @media (prefers-reduced-motion: reduce) {
        .zf-editorial-reveal .zf-editorial-banner-image,
        .zf-editorial-reveal-item {
            opacity: 1 !important;
            clip-path: none !important;
            transform: none !important;
            transition: none !important;
        }
    }
</style>


<style>
/* ZALINA FINAL PRODUCT FONT OVERRIDE */

#more-pieces .zf-more-product .zf-featured-product-name,
#more-pieces .zf-more-product .zf-featured-product-name a {
    font-family: 'Inter', Arial, sans-serif !important;
    font-size: 15px !important;
    font-weight: 600 !important;
    line-height: 1.55 !important;
    letter-spacing: -0.01em !important;
    color: #681d35 !important;
}

#more-pieces .zf-more-product .zf-product-price {
    font-family: 'Inter', Arial, sans-serif !important;
    font-size: 18px !important;
    font-weight: 700 !important;
    line-height: 1.4 !important;
    letter-spacing: -0.02em !important;
    color: #8f1d3f !important;
}

#more-pieces .zf-more-product .zf-product-old-price {
    font-family: 'Inter', Arial, sans-serif !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    color: #9b858b !important;
}

@media (min-width: 768px) {
    #more-pieces .zf-more-product .zf-featured-product-name,
    #more-pieces .zf-more-product .zf-featured-product-name a {
        font-size: 16px !important;
    }

    #more-pieces .zf-more-product .zf-product-price {
        font-size: 20px !important;
    }
}

</style>



<style>
/* ZALINA PREMIUM PRODUCT FONT OVERRIDE */

#more-pieces .zf-more-product .zf-featured-product-name,
#more-pieces .zf-more-product .zf-featured-product-name a {
    font-family: 'Playfair Display', Georgia, serif !important;
    font-size: 16px !important;
    font-weight: 500 !important;
    line-height: 1.45 !important;
    letter-spacing: -0.015em !important;
    color: #542536 !important;
    text-rendering: optimizeLegibility;
    -webkit-font-smoothing: antialiased;
}

#more-pieces .zf-more-product .zf-featured-product-name a {
    transition:
        color .25s ease,
        letter-spacing .25s ease;
}

#more-pieces .zf-more-product:hover .zf-featured-product-name a {
    color: #a47745 !important;
    letter-spacing: 0 !important;
}

#more-pieces .zf-more-product .zf-product-price {
    font-family: 'Inter', Arial, sans-serif !important;
    font-size: 18px !important;
    font-weight: 700 !important;
    line-height: 1.35 !important;
    letter-spacing: -0.025em !important;
    color: #9a7040 !important;
    font-variant-numeric: tabular-nums;
    text-rendering: optimizeLegibility;
    -webkit-font-smoothing: antialiased;
}

#more-pieces .zf-more-product .zf-product-old-price {
    font-family: 'Inter', Arial, sans-serif !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    line-height: 1.4 !important;
    letter-spacing: 0 !important;
    color: #aa969b !important;
    text-decoration-thickness: 1px !important;
}

#more-pieces .zf-more-product .zf-price-group {
    margin-top: 8px !important;
    display: flex !important;
    align-items: baseline !important;
    gap: 8px !important;
    flex-wrap: wrap !important;
}

@media (min-width: 768px) {
    #more-pieces .zf-more-product .zf-featured-product-name,
    #more-pieces .zf-more-product .zf-featured-product-name a {
        font-size: 17px !important;
        line-height: 1.5 !important;
    }

    #more-pieces .zf-more-product .zf-product-price {
        font-size: 20px !important;
    }
}

@media (prefers-reduced-motion: reduce) {
    #more-pieces .zf-more-product .zf-featured-product-name a {
        transition: none !important;
    }
}
</style>

@endsection