<!doctype html>
<html lang="id">
<head>

    <meta charset="utf-8">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, viewport-fit=cover"
    >

    <meta
        name="theme-color"
        content="#631f2b"
    >

    <title>
        @yield('title', 'Zalina Hijab — Elegance in Every Drape')
    </title>


    {{-- =========================================================
         GOOGLE FONTS
         ========================================================= --}}

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500;1,9..144,600&family=Cormorant+Garamond:ital,wght@0,500;1,500;1,600&family=Cinzel:wght@500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    {{-- =========================================================
         TAILWIND CSS
         ========================================================= --}}

    <script src="https://cdn.tailwindcss.com"></script>

    <script>

        tailwind.config = {

            theme: {

                extend: {

                    fontFamily: {

                        serif: [
                            'Fraunces',
                            '"Playfair Display"',
                            'serif'
                        ],

                        display: [
                            'Fraunces',
                            '"Playfair Display"',
                            'serif'
                        ],

                        accent: [
                            '"Cormorant Garamond"',
                            'Georgia',
                            'serif'
                        ],

                        caps: [
                            'Cinzel',
                            '"Playfair Display"',
                            'serif'
                        ],

                        sans: [
                            'Inter',
                            'ui-sans-serif',
                            'system-ui'
                        ]

                    },


                    colors: {

                        maroon: {

                            50: '#fbf3f4',
                            100: '#f6e4e7',
                            200: '#eec7cd',
                            300: '#e0a0aa',
                            400: '#cc6f80',
                            500: '#ac4257',
                            600: '#7c2d3a',
                            700: '#631f2b',
                            800: '#4a1019',
                            900: '#2d0810'

                        },


                        gold: {

                            100: '#fff4d6',
                            200: '#f6dfaa',
                            300: '#e7c06d',
                            400: '#d9ad57',
                            500: '#b98a3d'

                        },


                        cream: '#fbf6f1'

                    }

                }

            }

        };

    </script>


    {{-- =========================================================
         GLOBAL CSS
         ========================================================= --}}

    <style>

        /* =========================================================
           ZALINA HIJAB
           GLOBAL RESPONSIVE SYSTEM
           ========================================================= */


        [x-cloak] {
            display: none !important;
        }


        /* =========================================================
           GLOBAL
           ========================================================= */

        html {

            scroll-behavior: smooth;

            width: 100%;
            max-width: 100%;

            overflow-x: hidden;

        }


        body {

            margin: 0;
            padding: 0;

            width: 100%;
            max-width: 100%;

            min-width: 0;

            overflow-x: hidden;

            background: #fbf6f1;

            font-family:
                'Inter',
                ui-sans-serif,
                system-ui,
                sans-serif;

            -webkit-text-size-adjust: 100%;
            text-size-adjust: 100%;

        }


        *,
        *::before,
        *::after {

            box-sizing: border-box;

        }


        img,
        video,
        canvas,
        svg {

            max-width: 100%;

        }


        img {

            height: auto;

        }


        button,
        input,
        textarea,
        select {

            font: inherit;

        }


        button,
        a {

            -webkit-tap-highlight-color: transparent;

        }


        a {

            overflow-wrap: anywhere;

        }


        p,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {

            overflow-wrap: break-word;

        }


        .font-serif {

            font-family:
                'Fraunces',
                'Playfair Display',
                serif;

        }


        ::selection {

            background: #d9ad57;

            color: #2d0810;

        }


        ::-webkit-scrollbar {

            width: 10px;

            height: 10px;

        }


        ::-webkit-scrollbar-track {

            background: #f6eee5;

        }


        ::-webkit-scrollbar-thumb {

            background: linear-gradient(180deg, #b98a3d, #631f2b);

            border-radius: 999px;

            border: 2px solid #f6eee5;

        }


        /* =========================================================
           NAV LINK — garis bawah emas yang tumbuh saat hover
           ========================================================= */

        .nav-link-epic {

            position: relative;

            padding-bottom: 4px;

        }


        .nav-link-epic::after {

            content: "";

            position: absolute;

            left: 0;

            bottom: 0;

            width: 0;

            height: 1px;

            background: #b98a3d;

            transition: width .35s ease;

        }


        .nav-link-epic:hover::after {

            width: 100%;

        }


        /* =========================================================
           MOBILE SAFETY
           ========================================================= */

        main,
        section,
        article,
        aside,
        header,
        footer,
        nav,
        div {

            min-width: 0;

        }


        table {

            max-width: 100%;

        }


        pre {

            max-width: 100%;

            overflow-x: auto;

        }


        code {

            overflow-wrap: anywhere;

        }


        /* =========================================================
           PRODUCT CARD
           ========================================================= */

        .product-card {

            transition:
                transform .3s ease,
                box-shadow .3s ease;

        }


        .product-card:hover {

            transform: translateY(-6px);

        }


        /* =========================================================
           SOCIAL CARD
           ========================================================= */

        .social-card {

            transition:
                transform .25s ease,
                box-shadow .25s ease;

        }


        .social-card:hover {

            transform:
                translateY(-6px)
                scale(1.01);

        }


        /* =========================================================
           REVEAL ANIMATION
           ========================================================= */

        .reveal {

            opacity: 0;

            transform:
                translateY(24px);

            transition:
                opacity .7s ease,
                transform .7s ease;

        }


        .reveal.show {

            opacity: 1;

            transform:
                translateY(0);

        }


        /* =========================================================
           SCROLL BUBBLE
           ========================================================= */

        .scroll-bubble {

            transition:
                transform .4s ease,
                opacity .4s ease;

        }


        .scroll-bubble.pop {

            transform:
                translateY(-8px)
                scale(1.12);

        }


        /* =========================================================
           ANNOUNCEMENT
           ========================================================= */

        .announcement-track {

            animation:
                marquee 18s linear infinite;

            will-change:
                transform;

        }


        @keyframes marquee {

            from {

                transform:
                    translateX(100%);

            }

            to {

                transform:
                    translateX(-100%);

            }

        }


        /* =========================================================
           FORM RESPONSIVE SAFETY
           ========================================================= */

        input,
        textarea,
        select {

            max-width: 100%;

        }


        textarea {

            resize: vertical;

        }


        /* =========================================================
           FOOTER LINK
           ========================================================= */

        .footer-link {

            position: relative;
            display: inline-block;

            transition:
                color .2s ease,
                transform .2s ease;

        }


        .footer-link::after {

            content: '';
            position: absolute;
            left: 0;
            bottom: -3px;
            width: 100%;
            height: 1px;
            background: #d9ad57;
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .3s ease;

        }


        .footer-link:hover {

            transform:
                translateX(2px);

        }


        .footer-link:hover::after {

            transform: scaleX(1);

        }


        /* =========================================================
           FOOTER SOCIAL ICON
           ========================================================= */

        .footer-social-icon {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            width: 44px;

            height: 44px;

            color: #ffffff;

            transition:
                transform .25s ease,
                opacity .25s ease,
                color .25s ease,
                box-shadow .25s ease;

        }


        .footer-social-icon:hover {

            transform:
                translateY(-3px);

            color: #f6dfaa;

            box-shadow: 0 10px 26px rgba(217, 173, 87, .35);

        }


        .footer-social-icon svg {

            width: 30px;

            height: 30px;

            flex-shrink: 0;

        }


        /* =========================================================
           MOBILE <= 767px
           ========================================================= */

        @media (max-width: 767px) {


            body {

                width: 100%;

                overflow-x: hidden;

            }


            .fixed.inset-x-0.top-0 {

                width: 100%;

                max-width: 100%;

            }


            .announcement-track {

                animation-duration: 16s;

                white-space: nowrap;

                font-size: 11px;

                line-height: 1.4;

            }


            main.pt-\[104px\] {

                padding-top: 112px !important;

            }


            body:not(.page-scoped) .max-w-7xl {

                width: 100%;

                max-width: 100%;

            }


            body:not(.page-scoped) .px-5 {

                padding-left: 16px !important;

                padding-right: 16px !important;

            }


            body:not(.page-scoped) .sm\:px-8 {

                padding-left: 16px !important;

                padding-right: 16px !important;

            }


            body:not(.page-scoped) h1 {

                line-height: 1.12;

            }


            body:not(.page-scoped) h2 {

                line-height: 1.18;

            }


            body:not(.page-scoped) h3 {

                line-height: 1.25;

            }


            body:not(.page-scoped) .text-7xl {

                font-size: 3rem !important;

                line-height: 1.05 !important;

            }


            body:not(.page-scoped) .text-6xl {

                font-size: 2.75rem !important;

                line-height: 1.08 !important;

            }


            body:not(.page-scoped) .text-5xl {

                font-size: 2.35rem !important;

                line-height: 1.1 !important;

            }


            body:not(.page-scoped) .text-4xl {

                font-size: 2rem !important;

                line-height: 1.12 !important;

            }


            body:not(.page-scoped) .text-3xl {

                font-size: 1.6rem !important;

                line-height: 1.2 !important;

            }


            body:not(.page-scoped) .text-2xl {

                font-size: 1.35rem !important;

            }


            body:not(.page-scoped) .py-20 {

                padding-top: 56px !important;

                padding-bottom: 56px !important;

            }


            body:not(.page-scoped) .py-24 {

                padding-top: 64px !important;

                padding-bottom: 64px !important;

            }


            body:not(.page-scoped) .py-28 {

                padding-top: 72px !important;

                padding-bottom: 72px !important;

            }


            body:not(.page-scoped) .py-32 {

                padding-top: 80px !important;

                padding-bottom: 80px !important;

            }


            body:not(.page-scoped) .mt-20 {

                margin-top: 64px !important;

            }


            body:not(.page-scoped) .mb-20 {

                margin-bottom: 64px !important;

            }


            body:not(.page-scoped) .gap-10 {

                gap: 24px !important;

            }


            body:not(.page-scoped) .gap-12 {

                gap: 28px !important;

            }


            body:not(.page-scoped) .gap-16 {

                gap: 32px !important;

            }


            body:not(.page-scoped) .product-card:hover {

                transform: none;

            }


            body:not(.page-scoped) .social-card:hover {

                transform: none;

            }


            body:not(.page-scoped) .reveal {

                transform:
                    translateY(14px);

            }


            body:not(.page-scoped) .overflow-x-auto {

                max-width: 100%;

                overflow-x: auto;

                -webkit-overflow-scrolling: touch;

            }


            body:not(.page-scoped) table {

                min-width: 600px;

            }


            body:not(.page-scoped) form {

                max-width: 100%;

            }


            body:not(.page-scoped) input,
            body:not(.page-scoped) textarea,
            body:not(.page-scoped) select {

                min-width: 0;

            }


            body:not(.page-scoped) button {

                max-width: 100%;

            }


            aside.fixed.left-0 {

                width:
                    min(88vw, 380px);

                max-width: 380px;

                padding:
                    24px
                    max(20px, env(safe-area-inset-right))
                    24px
                    max(20px, env(safe-area-inset-left));

                overflow-y: auto;

                -webkit-overflow-scrolling: touch;

            }


            footer {

                margin-top:
                    64px !important;

            }


            footer .grid {

                min-width: 0;

            }


            footer h2 {

                font-size:
                    1.75rem !important;

            }


            footer .social-card {

                min-width: 0;

            }


            .scroll-bubble {

                opacity: .55;

            }


            .scroll-bubble:nth-of-type(1) {

                left:
                    8px !important;

            }


            .scroll-bubble:nth-of-type(2) {

                right:
                    8px !important;

            }


            .scroll-bubble:nth-of-type(3) {

                left:
                    20px !important;

            }


            body:not(.page-scoped) /* -----------------------------------------------------
               FOOTER MOBILE
               ----------------------------------------------------- */

            .footer-social-icon {

                width: 48px;

                height: 48px;

            }


            .footer-social-icon svg {

                width: 30px;

                height: 30px;

            }

        }


        /* =========================================================
           VERY SMALL PHONE <= 380px
           ========================================================= */

        @media (max-width: 380px) {


            body:not(.page-scoped) nav {

                padding-left:
                    12px !important;

                padding-right:
                    12px !important;

            }


            body:not(.page-scoped) nav > a {

                gap:
                    8px !important;

            }


            body:not(.page-scoped) nav > a > span:last-child {

                max-width:
                    125px;

                font-size:
                    18px !important;

            }


            body:not(.page-scoped) nav .gap-3 {

                gap:
                    4px !important;

            }


            main.pt-\[104px\] {

                padding-top:
                    112px !important;

            }


            body:not(.page-scoped) .px-5,
            body:not(.page-scoped) .sm\:px-8 {

                padding-left:
                    12px !important;

                padding-right:
                    12px !important;

            }


            body:not(.page-scoped) .text-7xl {

                font-size:
                    2.45rem !important;

            }


            body:not(.page-scoped) .text-6xl {

                font-size:
                    2.3rem !important;

            }


            body:not(.page-scoped) .text-5xl {

                font-size:
                    2rem !important;

            }


            body:not(.page-scoped) .text-4xl {

                font-size:
                    1.75rem !important;

            }


            body:not(.page-scoped) .text-3xl {

                font-size:
                    1.45rem !important;

            }


            body:not(.page-scoped) .text-2xl {

                font-size:
                    1.25rem !important;

            }


            aside.fixed.left-0 {

                width:
                    90vw;

                padding:
                    20px;

            }


            body:not(.page-scoped) .px-8 {

                padding-left:
                    18px !important;

                padding-right:
                    18px !important;

            }


            body:not(.page-scoped) footer .px-5,
            body:not(.page-scoped) footer .sm\:px-8 {

                padding-left:
                    14px !important;

                padding-right:
                    14px !important;

            }


            .footer-social-icon {

                width:
                    44px;

                height:
                    44px;

            }

        }


        /* =========================================================
           TABLET 768px - 1023px
           ========================================================= */

        @media (min-width: 768px)
        and (max-width: 1023px) {


            body {

                overflow-x:
                    hidden;

            }


            body:not(.page-scoped) .max-w-7xl {

                width:
                    100%;

                max-width:
                    100%;

            }


            body:not(.page-scoped) .px-5 {

                padding-left:
                    24px !important;

                padding-right:
                    24px !important;

            }


            body:not(.page-scoped) .sm\:px-8 {

                padding-left:
                    24px !important;

                padding-right:
                    24px !important;

            }


            main.pt-\[104px\] {

                padding-top:
                    108px !important;

            }


            body:not(.page-scoped) .text-7xl {

                font-size:
                    4.5rem !important;

            }


            body:not(.page-scoped) .text-6xl {

                font-size:
                    4rem !important;

            }


            body:not(.page-scoped) .text-5xl {

                font-size:
                    3.25rem !important;

            }

        }


        /* =========================================================
           LANDSCAPE PHONE
           ========================================================= */

        @media (max-width: 900px)
        and (orientation: landscape) {


            main.pt-\[104px\] {

                padding-top:
                    108px !important;

            }


            aside.fixed.left-0 {

                overflow-y:
                    auto;

            }

        }


        /* =========================================================
           TOUCH DEVICE
           ========================================================= */

        @media (hover: none)
        and (pointer: coarse) {


            .product-card:hover,
            .social-card:hover {

                transform:
                    none;

            }

        }


        /* =========================================================
           REDUCED MOTION
           ========================================================= */

        @media (prefers-reduced-motion: reduce) {


            html {

                scroll-behavior:
                    auto;

            }


            *,
            *::before,
            *::after {

                animation-duration:
                    .01ms !important;

                animation-iteration-count:
                    1 !important;

                transition-duration:
                    .01ms !important;

            }

        }


        /* =========================================================
           SAFE AREA
           ========================================================= */

        @supports (padding: env(safe-area-inset-bottom)) {


            body {

                padding-bottom:
                    env(safe-area-inset-bottom);

            }

        }

    </style>


<style id="store-header-icon-style">

/* =========================================================
   ZALINA MASTER HEADER
   Logo center, hamburger kiri, aksi kanan, search panel
   ========================================================= */

:root {
    --zl-cream:      #fbf6f1;
    --zl-cream-deep: #f5ece3;
    --zl-maroon:     #631f2b;
    --zl-maroon-ink: #4a1520;
    --zl-gold:       #b98a3d;
    --zl-line:       rgba(185, 138, 61, .28);
}

.store-master-header {
    background: linear-gradient(180deg, #fdfaf6 0%, var(--zl-cream) 100%);
    border-bottom: 1px solid var(--zl-line);
    box-shadow: 0 1px 24px rgba(99, 31, 43, .05);
}

/* ---------- BARIS UTAMA ---------- */

.store-header-row {
    position: relative;
    display: grid;
    grid-template-columns: auto minmax(0, 1fr) auto;
    align-items: center;
    gap: 12px;
    width: 100%;
    max-width: 1280px;
    height: 76px;
    margin: 0 auto;
    padding: 0 24px;
}

.store-header-side {
    display: flex;
    align-items: center;
    gap: 2px;
    min-width: 0;
}

.store-header-side.is-right {
    justify-content: flex-end;
}

/* ---------- LOGO TENGAH ---------- */
/* Selalu duduk di kolom tengah grid (bukan absolute), jadi TIDAK PERNAH
   menimpa ikon kiri/kanan — lebar logo dibatasi oleh ruang yang benar-benar
   tersisa. Nama toko yang panjang otomatis mengecil (lihat script fitBrandText
   di bawah), dan kalau masih kurang, terpotong rapi dengan "…". */

.store-header-brand {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    min-width: 0;
    max-width: 100%;
    text-decoration: none;
}

.store-header-brand img,
.store-header-brand .store-brand-fallback {
    width: 46px;
    height: 46px;
    border-radius: 999px;
    object-fit: cover;
    border: 1px solid var(--zl-line);
    flex-shrink: 0;
}

.store-header-brand .store-brand-fallback {
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--zl-maroon);
    color: var(--zl-cream);
    font-size: 18px;
    letter-spacing: .08em;
}

.store-brand-text {
    display: block;
    min-width: 0;
    max-width: 100%;
    overflow: hidden;
    font-family: 'Fraunces', 'Playfair Display', serif;
    font-size: 26px;
    font-weight: 500;
    letter-spacing: .04em;
    line-height: 1;
    color: var(--zl-maroon-ink);
    white-space: nowrap;
    text-overflow: ellipsis;
}

/* ---------- IKON (halus / tipis) ---------- */

.store-header-icon {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    padding: 0;
    border: 0;
    border-radius: 999px;
    background: transparent;
    color: var(--zl-maroon);
    cursor: pointer;
    transition: color .25s ease, background .25s ease;
}

.store-header-icon:hover {
    color: var(--zl-gold);
    background: rgba(185, 138, 61, .09);
}

.store-header-icon:active {
    transform: scale(.94);
}

.store-header-icon.is-active {
    color: var(--zl-gold);
    background: rgba(185, 138, 61, .14);
}

.store-header-icon svg {
    width: 23px;
    height: 23px;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.15;
    stroke-linecap: round;
    stroke-linejoin: round;
}

/* Semua ikon di header & drawer dibuat tipis dan seragam */
.store-master-header svg,
.store-drawer svg {
    stroke-width: 1.15 !important;
    stroke-linecap: round;
    stroke-linejoin: round;
}

/* ---------- HAMBURGER HALUS ---------- */

.store-hamburger {
    flex-direction: column;
}

.store-hamburger span {
    display: block;
    width: 20px;
    height: 1.4px;
    margin: 2.6px 0;
    background: currentColor;
    border-radius: 2px;
    transition: transform .3s ease, width .3s ease;
}

.store-hamburger span:nth-child(2) { width: 14px; }

/* Jaga hamburger tetap terlihat di semua ukuran layar */
.store-master-header .store-hamburger {
    display: inline-flex !important;
    flex-direction: column;
}

.store-master-header .store-hamburger span {
    display: block !important;
    flex: 0 0 auto;
}

.store-hamburger:hover span:nth-child(2) { width: 20px; }

/* ---------- BADGE ---------- */

.store-header-badge {
    position: absolute;
    top: -3px;
    right: -3px;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 4px;
    border: 2px solid var(--zl-cream);
    border-radius: 999px;
    background: var(--zl-maroon);
    color: #fff;
    font-family: 'Inter', sans-serif;
    font-size: 9px;
    font-weight: 700;
    line-height: 1;
    box-shadow: 0 2px 6px rgba(99, 31, 43, .35);
}

/* ---------- BARIS MENU ---------- */

.store-header-menu {
    display: none;
    border-top: 1px solid rgba(185, 138, 61, .18);
}

.store-header-menu-inner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 40px;
    max-width: 1280px;
    height: 48px;
    margin: 0 auto;
    padding: 0 24px;
}

.store-menu-link {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 9px;
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: .17em;
    text-transform: uppercase;
    color: var(--zl-maroon);
    text-decoration: none;
    transition: color .25s ease;
}

.store-menu-link svg {
    width: 16px;
    height: 16px;
    opacity: .75;
    transition: opacity .25s ease, transform .25s ease;
}

.store-menu-link::after {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    bottom: -13px;
    height: 1px;
    background: var(--zl-gold);
    transform: scaleX(0);
    transform-origin: center;
    transition: transform .3s ease;
}

.store-menu-link:hover {
    color: var(--zl-gold);
}

.store-menu-link:hover svg {
    opacity: 1;
    transform: translateY(-1px);
}

.store-menu-link:hover::after {
    transform: scaleX(1);
}

/* ---------- PANEL PENCARIAN ---------- */

.store-search-panel {
    border-top: 1px solid rgba(185, 138, 61, .18);
    background: var(--zl-cream-deep);
}

.store-search-form {
    display: flex;
    align-items: center;
    gap: 12px;
    max-width: 760px;
    margin: 0 auto;
    padding: 16px 24px;
}

.store-search-field {
    flex: 1 1 auto;
    display: flex;
    align-items: center;
    gap: 10px;
    height: 48px;
    padding: 0 18px;
    border: 1px solid var(--zl-line);
    border-radius: 999px;
    background: #fff;
    transition: border-color .25s ease, box-shadow .25s ease;
}

.store-search-field:focus-within {
    border-color: var(--zl-gold);
    box-shadow: 0 0 0 3px rgba(185, 138, 61, .12);
}

.store-search-field svg {
    width: 19px;
    height: 19px;
    fill: none;
    stroke: var(--zl-maroon);
    stroke-width: 1.15;
    flex-shrink: 0;
    opacity: .7;
}

.store-search-field input {
    width: 100%;
    min-width: 0;
    border: 0;
    outline: 0;
    background: transparent;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    color: var(--zl-maroon-ink);
}

.store-search-field input::placeholder {
    color: rgba(99, 31, 43, .42);
}

.store-search-submit {
    height: 48px;
    padding: 0 26px;
    border: 0;
    border-radius: 999px;
    background: var(--zl-maroon);
    color: var(--zl-cream);
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .14em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background .25s ease;
    flex-shrink: 0;
}

.store-search-submit:hover {
    background: var(--zl-gold);
}

/* ---------- SARAN PRODUK (LIVE) ---------- */

.store-search-suggestions {
    max-width: 760px;
    margin: 0 auto;
    padding: 0 24px 20px;
}

.store-search-status {
    padding: 16px 4px;
    color: rgba(99, 31, 43, .55);
    font-family: 'Inter', sans-serif;
    font-size: 12.5px;
    text-align: center;
}

.store-search-results {
    display: flex;
    flex-direction: column;
    gap: 2px;
    margin: 0;
    padding: 6px 0;
    list-style: none;
    background: #fff;
    border: 1px solid var(--zl-line);
    border-radius: 16px;
    overflow: hidden;
}

.store-search-result {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 10px 14px;
    text-decoration: none;
    color: var(--zl-maroon-ink);
    transition: background .18s ease;
}

.store-search-result:hover,
.store-search-result.is-active {
    background: rgba(185, 138, 61, .1);
}

.store-search-result-thumb {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    flex-shrink: 0;
    border-radius: 10px;
    overflow: hidden;
    background: var(--zl-cream-deep);
}

.store-search-result-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.store-search-result-fallback {
    font-family: 'Fraunces', serif;
    font-size: 16px;
    color: var(--zl-gold);
}

.store-search-result-info {
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 0;
}

.store-search-result-name {
    overflow: hidden;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 500;
    color: var(--zl-maroon-ink);
    text-overflow: ellipsis;
    white-space: nowrap;
}

.store-search-result-price {
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    font-weight: 600;
    color: var(--zl-maroon);
}

.store-search-result-price s {
    font-weight: 400;
    color: rgba(99, 31, 43, .4);
}

.store-search-viewall {
    display: block;
    margin-top: 10px;
    padding: 12px 4px;
    text-align: center;
    color: var(--zl-maroon);
    font-family: 'Inter', sans-serif;
    font-size: 11.5px;
    font-weight: 600;
    letter-spacing: .04em;
    text-decoration: none;
    border-top: 1px solid var(--zl-line);
}

.store-search-viewall:hover {
    color: var(--zl-gold);
}

@media (max-width: 640px) {

    .store-search-suggestions {
        padding: 0 14px 16px;
    }

    .store-search-result-thumb {
        width: 42px;
        height: 42px;
    }

}

/* ---------- OFFSET KONTEN ---------- */

body main.pt-\[104px\] {
    padding-top: 110px !important;
}

@media (min-width: 1024px) {

    .store-header-menu {
        display: block;
    }

    body main.pt-\[104px\] {
        padding-top: 164px !important;
    }

    /* Hamburger hanya untuk mobile/tablet — menu utama sudah ada di baris bawah */
    .store-hamburger {
        display: none !important;
    }

    /* Tanpa hamburger, ikon cari otomatis jadi item paling kiri */
    .store-header-side.is-left {
        justify-content: flex-start;
    }

}

/* ---------- RESPONSIVE ---------- */

@media (max-width: 1023px) {

    .store-header-row {
        height: 68px;
        padding: 0 16px;
    }

    .store-header-brand img,
    .store-header-brand .store-brand-fallback {
        width: 40px;
        height: 40px;
    }

    .store-brand-text {
        font-size: 22px;
    }

}

@media (max-width: 640px) {

    .store-header-row {
        height: 62px;
        padding: 0 10px;
    }

    .store-header-icon {
        width: 40px;
        height: 40px;
    }

    .store-header-icon svg {
        width: 21px;
        height: 21px;
    }

    .store-header-brand {
        gap: 9px;
    }

    .store-header-brand img,
    .store-header-brand .store-brand-fallback {
        width: 34px;
        height: 34px;
    }

    .store-brand-text {
        font-size: 19px;
        letter-spacing: .03em;
    }

    .store-search-form {
        gap: 8px;
        padding: 12px 14px;
    }

    .store-search-field {
        height: 44px;
        padding: 0 14px;
    }

    .store-search-submit {
        height: 44px;
        padding: 0 18px;
        font-size: 10px;
    }

    body main.pt-\[104px\] {
        padding-top: 100px !important;
    }

}

/* Layar sangat sempit: sembunyikan wordmark, sisakan lambang */
@media (max-width: 380px) {

    .store-brand-text {
        display: none;
    }

    .store-header-icon {
        width: 36px;
        height: 36px;
    }

}

/* ---------- DRAWER ---------- */

.store-drawer .mobile-menu-item {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: .13em;
    text-transform: uppercase;
    color: var(--zl-maroon);
}

.store-drawer .mobile-menu-item svg {
    width: 19px;
    height: 19px;
    opacity: .7;
}

</style>

<style id="store-scoped-page-guard">

    /* =========================================================
       SCOPED PAGE GUARD
       Halaman yang punya desain sendiri (mis. #home-page)
       memakai <body class="page-scoped">.
       Dekorasi global layout dimatikan supaya tidak menumpuk.
       ========================================================= */

    body.page-scoped .scroll-bubble {
        display: none !important;
    }

    /* Halaman scoped mengatur spasi & lebarnya sendiri */
    body.page-scoped main > #home-page {
        width: 100%;
        max-width: 100%;
    }

    /* Cegah dua header/announcement bertumpuk:
       hanya header milik layout yang boleh tampil. */
    body.page-scoped main .zf-header,
    body.page-scoped main .zf-announcement {
        display: none !important;
    }

</style>

<style id="account-mode-style">

    /* =========================================================
       MODE AKUN
       Aktif hanya untuk halaman yang memakai
       @section('accountMode') — mis. Akun & Riwayat Pesanan.
       Halaman lain tidak terpengaruh.
       ========================================================= */

    body.account-mode {
        background: #f8f1ee;
    }

    /* Header mode akun ikut alur dokumen (sticky), jadi
       padding-top untuk header fixed bawaan tidak dipakai. */
    body.account-mode main.pt-\[104px\] {
        padding-top: 0 !important;
    }

    body.account-mode footer {
        margin-top: 3rem;
    }

    /* ---------- BOTTOM NAV (mobile) ---------- */

    .zl-bottom-nav {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 55;
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        background: #fff;
        border-top: 1px solid #f0dfe2;
        padding: 8px 6px calc(8px + env(safe-area-inset-bottom, 0px));
        box-shadow: 0 -6px 24px rgba(99, 31, 43, .06);
    }

    .zl-bn-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 3px;
        color: #a98a91;
        font-size: 10.5px;
        font-weight: 500;
        line-height: 1.2;
        text-decoration: none;
    }

    .zl-bn-item svg {
        width: 22px;
        height: 22px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.7;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .zl-bn-item.is-active {
        color: #631f2b;
        font-weight: 600;
    }

    .zl-bn-item.is-active svg {
        fill: currentColor;
    }

    .zl-bn-icon {
        position: relative;
        display: inline-flex;
    }

    .zl-bn-badge {
        position: absolute;
        top: -6px;
        right: -10px;
        min-width: 16px;
        height: 16px;
        padding: 0 4px;
        border-radius: 999px;
        background: #e11d48;
        color: #fff;
        font-size: 9px;
        font-style: normal;
        font-weight: 700;
        line-height: 16px;
        text-align: center;
    }

    @media (max-width: 1023.98px) {
        body.account-mode {
            padding-bottom: calc(68px + env(safe-area-inset-bottom, 0px));
        }
    }

    @media (min-width: 1024px) {
        .zl-bottom-nav {
            display: none;
        }
    }

</style>

@stack('styles')
@yield('head')

</head>


{{-- =============================================================
     SITE DATA
     ============================================================= --}}

@php

    $navUser = \App\Models\User::find(
        session('zalina_user_id')
    );


    $cart = \App\Models\Cart::where(
        'session_id',
        session()->getId()
    )
    ->withCount('items')
    ->first();


    $cartCount =
        $cart
        ? $cart->items_count
        : 0;


    $siteSettings =
        \App\Models\Setting::pluck(
            'value',
            'key'
        );


    $siteName =
        $siteSettings['site_name']
        ?? 'Zalina Hijab';


    $siteLogo =
        $siteSettings['site_logo']
        ?? null;


    $announcement =
        $siteSettings['announcement_text']
        ?? '✨ Gratis ongkir se-Indonesia untuk pembelian di atas Rp 250.000 ✨';

@endphp


<body
    class="antialiased text-maroon-900 @yield('bodyClass')"
    x-data="{
        mobileNav: false,
        searchOpen: false,
        searchQuery: @js(request('search', request('q', ''))),
        searchResults: [],
        searchLoading: false,
        searchActiveIndex: -1,
        searchTimer: null,
        searchSuggestUrl: @js(route('search.suggest')),

        runSearch() {
            clearTimeout(this.searchTimer);

            const term = this.searchQuery.trim();
            this.searchActiveIndex = -1;

            if (term.length < 2) {
                this.searchResults = [];
                this.searchLoading = false;
                return;
            }

            this.searchLoading = true;

            this.searchTimer = setTimeout(() => {
                fetch(this.searchSuggestUrl + '?search=' + encodeURIComponent(term))
                    .then(response => response.json())
                    .then(data => {
                        this.searchResults = data.suggestions || [];
                        this.searchLoading = false;
                    })
                    .catch(() => {
                        this.searchResults = [];
                        this.searchLoading = false;
                    });
            }, 300);
        },

        moveSearchActive(step) {
            if (!this.searchResults.length) {
                return;
            }

            const max = this.searchResults.length - 1;
            this.searchActiveIndex = this.searchActiveIndex + step;

            if (this.searchActiveIndex < 0) {
                this.searchActiveIndex = max;
            } else if (this.searchActiveIndex > max) {
                this.searchActiveIndex = 0;
            }
        },

        goToActiveSearchResult() {
            const item = this.searchResults[this.searchActiveIndex];

            if (item && item.url) {
                window.location.href = item.url;
            }
        },

        closeSearch() {
            this.searchOpen = false;
            this.searchResults = [];
            this.searchActiveIndex = -1;
        },

        /*
        |--------------------------------------------------------------------------
        | AUTO-FIT NAMA TOKO
        |--------------------------------------------------------------------------
        | Supaya ganti nama toko (mis. 'Zalina' -> 'Zalina Fashion') tidak
        | pernah menimpa ikon kiri/kanan: ukuran font nama toko otomatis
        | diperkecil sampai muat di ruang yang tersisa, di desktop maupun
        | mobile. Kalau masih kurang pada ukuran minimum, sisanya dipotong
        | rapi dengan '...'.
        |--------------------------------------------------------------------------
        */

        fitBrandText() {
            const el = this.$refs.brandText;

            if (!el) {
                return;
            }

            el.style.fontSize = '';

            const minSize = 11;
            const computed = window.getComputedStyle(el);
            let size = parseFloat(computed.fontSize);
            let guard = 0;

            while (el.scrollWidth > el.clientWidth + 1 && size > minSize && guard < 40) {
                size -= 1;
                el.style.fontSize = size + 'px';
                guard++;
            }
        },

        init() {
            this.$nextTick(() => this.fitBrandText());

            window.addEventListener('resize', () => this.fitBrandText());

            if (window.document.fonts && window.document.fonts.ready) {
                window.document.fonts.ready.then(() => this.fitBrandText());
            }
        }
    }"
    @keydown.escape.window="mobileNav = false; closeSearch()"
>


    {{-- =========================================================
         FLOATING BUBBLES
         ========================================================= --}}

    <div
        class="scroll-bubble fixed top-32 left-5 w-4 h-4 rounded-full bg-maroon-300/30 blur-[1px] pointer-events-none z-10"
    ></div>


    <div
        class="scroll-bubble fixed top-2/3 right-7 w-6 h-6 rounded-full bg-gold-400/20 blur-[1px] pointer-events-none z-10"
    ></div>


    <div
        class="scroll-bubble fixed bottom-20 left-10 w-3 h-3 rounded-full bg-maroon-400/20 pointer-events-none z-10"
    ></div>


    {{-- =========================================================
         HEADER
         ========================================================= --}}

    @hasSection('accountMode')

    {{-- =========================================================
         HEADER MODE AKUN (desktop)
         Logo kiri — pencarian — favorit, keranjang, akun.
         Mobile memakai top bar milik halaman + bottom navigation.
         ========================================================= --}}

    <header class="zl-acc-header sticky top-0 z-50 hidden border-b border-maroon-100/70 bg-white lg:block">

        <div class="mx-auto flex h-[76px] max-w-[1440px] items-center gap-6 px-6 xl:px-8">

            {{-- LOGO --}}
            <a
                href="{{ route('home') }}"
                class="flex w-[236px] shrink-0 items-center gap-3"
                aria-label="{{ $siteName }}"
            >
                @if($siteLogo)
                    <img
                        src="{{ asset('storage/' . $siteLogo) }}"
                        alt="{{ $siteName }}"
                        class="h-12 w-12 shrink-0 rounded-full object-cover"
                    >
                @else
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-gold-300 bg-gold-100 font-serif text-2xl text-maroon-700">Z</span>
                @endif

                <span class="min-w-0 leading-tight">
                    <span class="block truncate font-caps text-[17px] font-semibold uppercase tracking-[0.06em] text-maroon-700">{{ $siteName }}</span>
                    <span class="block truncate font-accent text-[13px] italic text-maroon-400">{{ $siteSettings['site_tagline'] ?? 'Elegance in Every Drape' }}</span>
                </span>
            </a>


            {{-- PENCARIAN --}}
            <div
                class="relative mx-auto w-full max-w-[640px] flex-1"
                @click.outside="closeSearch()"
            >
                <form
                    action="{{ route('shop') }}"
                    method="GET"
                    class="relative"
                    @submit="if (!searchQuery.trim()) { $event.preventDefault(); }"
                >
                    <svg class="pointer-events-none absolute left-4 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-maroon-400" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="11" cy="11" r="6.4" />
                        <path d="M16 16l4.2 4.2" />
                    </svg>

                    <input
                        type="search"
                        name="search"
                        x-model="searchQuery"
                        @input="searchOpen = true; runSearch()"
                        @focus="searchOpen = true"
                        @keydown.down.prevent="moveSearchActive(1)"
                        @keydown.up.prevent="moveSearchActive(-1)"
                        @keydown.enter="if (searchActiveIndex > -1) { $event.preventDefault(); goToActiveSearchResult(); }"
                        placeholder="Cari produk, kategori, atau brand..."
                        autocomplete="off"
                        class="h-11 w-full rounded-full border border-maroon-100 bg-white pl-11 pr-12 text-sm text-maroon-900 placeholder:text-maroon-300 focus:border-maroon-300 focus:outline-none focus:ring-2 focus:ring-maroon-100"
                    >

                    <button
                        type="submit"
                        class="absolute right-2 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full text-maroon-500 transition hover:bg-maroon-50"
                        aria-label="Cari"
                    >
                        <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="11" cy="11" r="6.4" />
                            <path d="M16 16l4.2 4.2" />
                        </svg>
                    </button>
                </form>

                {{-- SARAN PRODUK (LIVE, SAAT MENGETIK) --}}
                <div
                    x-cloak
                    x-show="searchOpen && searchQuery.trim().length >= 2"
                    x-transition
                    class="absolute left-0 right-0 top-[calc(100%+8px)] z-50 overflow-hidden rounded-2xl border border-maroon-100 bg-white shadow-xl"
                >
                    <template x-if="searchLoading">
                        <div class="px-4 py-3 text-sm text-maroon-400">Mencari produk…</div>
                    </template>

                    <template x-if="!searchLoading && searchResults.length === 0">
                        <div class="px-4 py-3 text-sm text-maroon-400">Produk tidak ditemukan.</div>
                    </template>

                    <template x-if="!searchLoading && searchResults.length > 0">
                        <ul class="max-h-[360px] divide-y divide-maroon-50 overflow-y-auto">
                            <template x-for="(item, index) in searchResults" :key="item.id">
                                <li>
                                    <a
                                        :href="item.url"
                                        class="flex items-center gap-3 px-4 py-3 transition hover:bg-maroon-50"
                                        :class="{ 'bg-maroon-50': index === searchActiveIndex }"
                                        @mouseenter="searchActiveIndex = index"
                                    >
                                        <span class="h-11 w-11 shrink-0 overflow-hidden rounded-lg bg-maroon-50">
                                            <img
                                                :src="item.image"
                                                :alt="item.name"
                                                x-show="item.image"
                                                class="h-full w-full object-cover"
                                                loading="lazy"
                                            >
                                            <span
                                                x-show="!item.image"
                                                class="flex h-full w-full items-center justify-center font-serif text-maroon-400"
                                            >Z</span>
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block truncate text-sm font-semibold text-maroon-900" x-text="item.name"></span>
                                            <span class="block text-xs text-maroon-500">
                                                <span x-text="item.price"></span>
                                                <s class="ml-1 text-maroon-300" x-show="item.has_discount" x-text="item.original_price"></s>
                                            </span>
                                        </span>
                                    </a>
                                </li>
                            </template>
                        </ul>
                    </template>

                    <a
                        :href="'{{ route('shop') }}' + '?search=' + encodeURIComponent(searchQuery)"
                        class="block border-t border-maroon-100 px-4 py-3 text-center text-xs font-semibold text-maroon-700 transition hover:bg-maroon-50"
                        x-show="!searchLoading && searchResults.length > 0"
                    >
                        Lihat semua hasil
                    </a>
                </div>
            </div>


            {{-- AKSI KANAN --}}
            <div class="flex shrink-0 items-center gap-1.5">

                <a
                    href="{{ route('favorites.index') }}"
                    class="relative flex h-11 w-11 items-center justify-center rounded-full text-maroon-700 transition hover:bg-maroon-50"
                    aria-label="Favorit"
                    title="Favorit"
                >
                    <svg class="h-[22px] w-[22px]" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 20s-7.2-4.4-9.1-8.3A4.9 4.9 0 0 1 12 6.6a4.9 4.9 0 0 1 9.1 5.1C19.2 15.6 12 20 12 20Z" />
                    </svg>
                </a>

                <a
                    href="{{ route('cart.index') }}"
                    class="relative flex h-11 w-11 items-center justify-center rounded-full text-maroon-700 transition hover:bg-maroon-50"
                    aria-label="Keranjang"
                    title="Keranjang"
                >
                    <svg class="h-[22px] w-[22px]" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6.2 7.6h11.6l-1 11.1a1.8 1.8 0 0 1-1.8 1.6H9a1.8 1.8 0 0 1-1.8-1.6Z" />
                        <path d="M9.2 7.6V6.3a2.8 2.8 0 0 1 5.6 0v1.3" />
                    </svg>

                    @if($cartCount > 0)
                        <span class="absolute right-0.5 top-0.5 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-bold leading-none text-white">
                            {{ $cartCount > 99 ? '99+' : $cartCount }}
                        </span>
                    @endif
                </a>


                @if($navUser)

                    <div
                        class="relative ml-3"
                        x-data="{ open: false }"
                        @click.outside="open = false"
                        @keydown.escape.window="open = false"
                    >
                        <button
                            type="button"
                            class="flex items-center gap-3 rounded-full py-1 pl-1 pr-2 transition hover:bg-maroon-50"
                            @click="open = !open"
                            :aria-expanded="open.toString()"
                            aria-haspopup="true"
                        >
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-maroon-200 to-maroon-400 font-serif text-base font-semibold text-white">
                                {{ mb_strtoupper(mb_substr(trim((string) $navUser->name), 0, 1)) ?: 'Z' }}
                            </span>

                            <span class="text-left leading-tight">
                                <span class="block max-w-[110px] truncate text-[13px] font-semibold text-maroon-900">{{ $navUser->name }}</span>
                                <span class="block text-[11px] text-maroon-400">{{ $navUser->role === 'admin' ? 'Admin' : 'Member' }}</span>
                            </span>

                            <svg
                                class="h-4 w-4 text-maroon-500 transition-transform"
                                :class="{ 'rotate-180': open }"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                viewBox="0 0 24 24" aria-hidden="true"
                            >
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                        <div
                            x-cloak
                            x-show="open"
                            x-transition
                            class="absolute right-0 top-[calc(100%+10px)] z-50 w-52 rounded-2xl border border-maroon-100 bg-white p-2 shadow-xl"
                        >
                            <a
                                href="{{ route('profile') }}"
                                class="flex items-center rounded-xl px-3 py-2.5 text-sm text-maroon-800 transition hover:bg-maroon-50"
                            >
                                Akun Saya
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="flex w-full items-center rounded-xl px-3 py-2.5 text-left text-sm text-maroon-800 transition hover:bg-maroon-50"
                                >
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>

                @else

                    <a
                        href="{{ route('login.form') }}"
                        class="ml-3 rounded-full bg-maroon-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-maroon-800"
                    >
                        Masuk
                    </a>

                @endif

            </div>

        </div>

    </header>

    @else

    <div
        class="fixed inset-x-0 top-0 z-50 w-full max-w-full"
    >


        {{-- =====================================================
             RUNNING TEXT
             ===================================================== --}}

        <div
            class="bg-maroon-700 text-cream overflow-hidden whitespace-nowrap w-full"
        >

            <div class="py-2">

                <div
                    class="announcement-track inline-block text-xs sm:text-sm font-medium tracking-[0.02em]"
                >

                    {{ $announcement }}

                    &nbsp;&nbsp;&nbsp;&nbsp;

                    ✦

                    &nbsp;&nbsp;&nbsp;&nbsp;

                    {{ $announcement }}

                </div>

            </div>

        </div>


        {{-- =====================================================
             NAVIGATION
             ===================================================== --}}

        <header class="store-master-header">

            {{-- =====================================================
                 BARIS UTAMA: hamburger kiri — logo tengah — aksi kanan
                 ===================================================== --}}

            <div class="store-header-row">

                {{-- KIRI --}}
                <div class="store-header-side is-left">

                    <button
                        type="button"
                        class="store-header-icon store-hamburger"
                        aria-label="Buka menu"
                        @click="mobileNav = true"
                    >
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>

                    <button
                        type="button"
                        class="store-header-icon"
                        :class="{ 'is-active': searchOpen }"
                        aria-label="Cari produk"
                        :aria-expanded="searchOpen.toString()"
                        @click="searchOpen = !searchOpen; if (searchOpen) { $nextTick(() => $refs.searchInput.focus()) }"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="11" cy="11" r="6.4" />
                            <path d="M16 16l4.2 4.2" />
                        </svg>
                    </button>

                </div>


                {{-- LOGO TENGAH --}}
                <a
                    href="{{ route('home') }}"
                    class="store-header-brand"
                    aria-label="{{ $siteName }}"
                >

                    @if($siteLogo)

                        <img
                            src="{{ asset('storage/' . $siteLogo) }}"
                            alt="{{ $siteName }}"
                        >

                    @else

                        <span class="store-brand-fallback">Z</span>

                    @endif

                    <span class="store-brand-text" x-ref="brandText">{{ $siteName }}</span>

                </a>


                {{-- KANAN --}}
                <div class="store-header-side is-right">

                    {{-- FAVORIT --}}
                    <a
                        href="{{ route('favorites.index') }}"
                        class="store-header-icon"
                        aria-label="Favorit"
                        title="Favorit"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 20s-7.2-4.4-9.1-8.3A4.9 4.9 0 0 1 12 6.6a4.9 4.9 0 0 1 9.1 5.1C19.2 15.6 12 20 12 20Z" />
                        </svg>
                    </a>


                    {{-- PROFIL --}}
                    @php
                        /*
                        |------------------------------------------------------------
                        | Jumlah pesanan aktif customer
                        |------------------------------------------------------------
                        */

                        $profileOrderCount = 0;

                        $profileUserId = session('zalina_user_id');

                        if ($profileUserId) {
                            $profileOrderCount = \App\Models\Order::where(
                                'user_id',
                                $profileUserId
                            )
                            ->whereNotIn('status', [
                                'completed',
                                'complete',
                                'delivered',
                                'finished',
                                'success',
                                'cancelled',
                                'canceled',
                                'rejected',
                                'failed',
                            ])
                            ->count();
                        }
                    @endphp

                    <a
                        href="{{ route('profile') }}"
                        class="store-header-icon"
                        aria-label="Profil"
                        title="Profil"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="8.4" r="3.9" />
                            <path d="M4.6 20.2a7.6 7.6 0 0 1 14.8 0" />
                        </svg>

                        @if($profileOrderCount > 0)
                            <span
                                class="store-header-badge"
                                aria-label="{{ $profileOrderCount }} pesanan aktif"
                            >
                                {{ $profileOrderCount > 99 ? '99+' : $profileOrderCount }}
                            </span>
                        @endif
                    </a>


                    {{-- KERANJANG --}}
                    <a
                        href="{{ route('cart.index') }}"
                        class="store-header-icon"
                        aria-label="Keranjang"
                        title="Keranjang"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M6.2 7.6h11.6l-1 11.1a1.8 1.8 0 0 1-1.8 1.6H9a1.8 1.8 0 0 1-1.8-1.6Z" />
                            <path d="M9.2 7.6V6.3a2.8 2.8 0 0 1 5.6 0v1.3" />
                        </svg>

                        <span class="store-header-badge">{{ $cartCount }}</span>
                    </a>

                </div>

            </div>


            {{-- =====================================================
                 BARIS MENU (desktop)
                 ===================================================== --}}

            <div class="store-header-menu">

                <nav class="store-header-menu-inner" aria-label="Menu utama">

                    <a href="{{ route('home') }}" class="store-menu-link">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 10.4 12 4l8 6.4V19a1.3 1.3 0 0 1-1.3 1.3H5.3A1.3 1.3 0 0 1 4 19Z" />
                            <path d="M9.6 20.3v-6h4.8v6" />
                        </svg>
                        Beranda
                    </a>

                    <a href="{{ route('shop') }}" class="store-menu-link">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M20 7.4 12 3.6 4 7.4l8 3.8Z" />
                            <path d="M20 7.4v9.2L12 20.4l-8-3.8V7.4" />
                            <path d="M12 11.2v9.2" />
                        </svg>
                        Koleksi
                    </a>

                    <a href="{{ route('category.index') }}" class="store-menu-link">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="4" y="4" width="6.6" height="6.6" rx="1.4" />
                            <rect x="13.4" y="4" width="6.6" height="6.6" rx="1.4" />
                            <rect x="4" y="13.4" width="6.6" height="6.6" rx="1.4" />
                            <rect x="13.4" y="13.4" width="6.6" height="6.6" rx="1.4" />
                        </svg>
                        Kategori
                    </a>

                    <a href="/about" class="store-menu-link">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="8.4" />
                            <path d="M12 11.2v5" />
                            <path d="M12 7.9v.6" />
                        </svg>
                        Tentang Kami
                    </a>

                </nav>

            </div>


            {{-- =====================================================
                 PANEL PENCARIAN
                 ===================================================== --}}

            <div
                x-cloak
                x-show="searchOpen"
                x-transition
                class="store-search-panel"
                @click.outside="closeSearch()"
            >

                <form
                    action="{{ route('shop') }}"
                    method="GET"
                    class="store-search-form"
                    @submit="if (!searchQuery.trim()) { $event.preventDefault(); $refs.searchInput.focus(); }"
                >

                    <label class="store-search-field">

                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="11" cy="11" r="6.4" />
                            <path d="M16 16l4.2 4.2" />
                        </svg>

                        <input
                            type="search"
                            name="search"
                            x-model="searchQuery"
                            @input="runSearch()"
                            @keydown.down.prevent="moveSearchActive(1)"
                            @keydown.up.prevent="moveSearchActive(-1)"
                            @keydown.enter="if (searchActiveIndex > -1) { $event.preventDefault(); goToActiveSearchResult(); }"
                            placeholder="Cari hijab, pashmina, gamis..."
                            x-ref="searchInput"
                            autocomplete="off"
                            required
                        >

                    </label>

                    <button type="submit" class="store-search-submit">
                        Cari
                    </button>

                </form>


                {{-- =================================================
                     SARAN PRODUK (LIVE, SAAT MENGETIK)
                     ================================================= --}}

                <div
                    x-cloak
                    x-show="searchQuery.trim().length >= 2"
                    x-transition
                    class="store-search-suggestions"
                >

                    <template x-if="searchLoading">
                        <div class="store-search-status">Mencari produk…</div>
                    </template>

                    <template x-if="!searchLoading && searchResults.length === 0">
                        <div class="store-search-status">Produk tidak ditemukan.</div>
                    </template>

                    <template x-if="!searchLoading && searchResults.length > 0">
                        <ul class="store-search-results">
                            <template x-for="(item, index) in searchResults" :key="item.id">
                                <li>
                                    <a
                                        :href="item.url"
                                        class="store-search-result"
                                        :class="{ 'is-active': index === searchActiveIndex }"
                                        @mouseenter="searchActiveIndex = index"
                                    >
                                        <span class="store-search-result-thumb">
                                            <img
                                                :src="item.image"
                                                :alt="item.name"
                                                x-show="item.image"
                                                loading="lazy"
                                            >
                                            <span
                                                class="store-search-result-fallback"
                                                x-show="!item.image"
                                            >
                                                Z
                                            </span>
                                        </span>

                                        <span class="store-search-result-info">
                                            <span class="store-search-result-name" x-text="item.name"></span>
                                            <span class="store-search-result-price">
                                                <span x-text="item.price"></span>
                                                <s x-show="item.has_discount" x-text="item.original_price"></s>
                                            </span>
                                        </span>
                                    </a>
                                </li>
                            </template>
                        </ul>
                    </template>

                    <a
                        :href="'{{ route('shop') }}' + '?search=' + encodeURIComponent(searchQuery)"
                        class="store-search-viewall"
                        x-show="!searchLoading && searchResults.length > 0"
                    >
                        Lihat semua hasil untuk “<span x-text="searchQuery"></span>”
                    </a>

                </div>

            </div>

        </header>

    </div>

    @endif


    {{-- =========================================================
         MOBILE OVERLAY
         ========================================================= --}}

    <div
        x-cloak
        x-show="mobileNav"
        x-transition.opacity
        class="fixed inset-0 z-[60] bg-black/40"
        @click="mobileNav = false"
    ></div>


    {{-- =========================================================
         MOBILE SIDEBAR
         ========================================================= --}}

    <aside
        x-cloak
        x-show="mobileNav"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="store-drawer fixed left-0 top-0 z-[70] h-full w-[85vw] max-w-sm bg-cream border-r border-gold-200 shadow-2xl p-6 overflow-y-auto"
    >


        <div
            class="flex items-center justify-between mb-8 gap-4 pb-5 border-b border-gold-200"
        >

            <span
                class="font-serif text-2xl text-maroon-900 truncate"
            >
                {{ $siteName }}
            </span>


            <button
                @click="mobileNav = false"
                class="p-2 rounded-full hover:bg-maroon-50 text-maroon-700 flex-shrink-0"
                type="button"
                aria-label="Tutup menu"
            >
                ✕
            </button>

        </div>


        <div
            class="space-y-2 text-maroon-800"
        >


            {{-- =================================================
                 BERANDA
                 ================================================= --}}

            <a
                @click="mobileNav = false"
                href="{{ route('home') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-maroon-50"
            >

                <svg
                    class="w-5 h-5 text-maroon-500 flex-shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M3 11.5L12 4l9 7.5V20a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1v-8.5z"
                    />

                </svg>

                Beranda

            </a>


            {{-- =================================================
                 KOLEKSI
                 ================================================= --}}

            <a
                @click="mobileNav = false"
                href="{{ route('shop') }}"
                class="mobile-menu-item flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-maroon-50 transition-all duration-200"
            >

                <svg
                    class="w-5 h-5 text-maroon-500 flex-shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-14L4 7m8 4v10"
                    />

                </svg>

                Koleksi

            </a>


            {{-- =================================================
                 KATEGORI
                 PERBAIKAN:
                 route('categories') → route('category.index')
                 ================================================= --}}

            <a
                @click="mobileNav = false"
                href="{{ route('category.index') }}"
                class="mobile-menu-item flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-maroon-50 transition-all duration-200"
            >

                <svg
                    class="w-5 h-5 text-maroon-500 flex-shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M4 6h16M4 12h16M4 18h16"
                    />

                </svg>

                Kategori

            </a>


            {{-- =================================================
                 TENTANG KAMI
                 ================================================= --}}

            <a
                @click="mobileNav = false"
                href="/about"
                class="mobile-menu-item flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-maroon-50 transition-all duration-200"
            >

                <svg
                    class="w-5 h-5 text-maroon-500 flex-shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M12 21a9 9 0 100-18 9 9 0 000 18zM12 11v6m0-9.5h.01"
                    />

                </svg>

                Tentang Kami

            </a>


            {{-- =================================================
                 CART
                 ================================================= --}}

            <a
                @click="mobileNav = false"
                href="{{ route('cart.index') }}"
                class="flex items-center justify-between px-4 py-3 rounded-xl hover:bg-maroon-50 gap-3"
            >

                <span
                    class="flex items-center gap-3 min-w-0"
                >

                    <svg
                        class="w-5 h-5 text-maroon-600 flex-shrink-0"
                        fill="currentColor"
                        viewBox="0 0 24 24"
                    >

                        <path
                            d="M7 4H4a1 1 0 010-2h3a1 1 0 01.95.68L8.3 4H20a1 1 0 01.95 1.32l-2.1 6.3A3 3 0 0116 14H9.2l.55 2H18a1 1 0 110 2H9a1 1 0 01-.95-.68L5.1 8H4a1 1 0 010-2h1.77L5.2 4.3A1 1 0 017 4z"
                        />

                    </svg>


                    <span class="truncate">
                        Keranjang
                    </span>

                </span>


                <span
                    class="bg-gold-100 text-maroon-800 text-xs font-bold px-2 py-1 rounded-full flex-shrink-0"
                >
                    {{ $cartCount }}
                </span>

            </a>

            {{-- =================================================
                 FAVORIT
                 ================================================= --}}

            <a
                @click="mobileNav = false"
                href="{{ route('favorites.index') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-maroon-50 transition-all duration-200"
            >

                <svg
                    class="w-5 h-5 text-maroon-600 flex-shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 000-7.78z"
                    />
                </svg>

                <span class="truncate">
                    Favorit
                </span>

            </a>

        </div>

    </aside>


    {{-- =========================================================
         MAIN
         ========================================================= --}}

    <main
        class="pt-[104px] w-full min-w-0"
    >


        {{-- =====================================================
             SUCCESS MESSAGE
             ===================================================== --}}

        @if(session('success'))

            <div
                class="max-w-7xl mx-auto px-5 sm:px-8 mt-6"
            >

                <div
                    class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm break-words"
                >

                    {{ session('success') }}

                </div>

            </div>

        @endif


        {{-- =====================================================
             PAGE CONTENT
             ===================================================== --}}

        @yield('content')

    </main>

    @hasSection('accountMode')

    {{-- =========================================================
         BOTTOM NAVIGATION (mobile, mode akun)
         ========================================================= --}}

    <nav class="zl-bottom-nav" aria-label="Navigasi bawah">

        <a href="{{ route('home') }}" class="zl-bn-item">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 10.4 12 4l8 6.4V19a1.3 1.3 0 0 1-1.3 1.3H5.3A1.3 1.3 0 0 1 4 19Z" />
                <path d="M9.6 20.3v-6h4.8v6" />
            </svg>
            <span>Beranda</span>
        </a>

        <a href="{{ route('category.index') }}" class="zl-bn-item">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <rect x="4" y="4" width="6.6" height="6.6" rx="1.4" />
                <rect x="13.4" y="4" width="6.6" height="6.6" rx="1.4" />
                <rect x="4" y="13.4" width="6.6" height="6.6" rx="1.4" />
                <rect x="13.4" y="13.4" width="6.6" height="6.6" rx="1.4" />
            </svg>
            <span>Kategori</span>
        </a>

        <a href="{{ route('favorites.index') }}" class="zl-bn-item">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 20s-7.2-4.4-9.1-8.3A4.9 4.9 0 0 1 12 6.6a4.9 4.9 0 0 1 9.1 5.1C19.2 15.6 12 20 12 20Z" />
            </svg>
            <span>Wishlist</span>
        </a>

        <a href="{{ route('cart.index') }}" class="zl-bn-item">
            <span class="zl-bn-icon">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6.2 7.6h11.6l-1 11.1a1.8 1.8 0 0 1-1.8 1.6H9a1.8 1.8 0 0 1-1.8-1.6Z" />
                    <path d="M9.2 7.6V6.3a2.8 2.8 0 0 1 5.6 0v1.3" />
                </svg>
                @if($cartCount > 0)
                    <em class="zl-bn-badge">{{ $cartCount > 99 ? '99+' : $cartCount }}</em>
                @endif
            </span>
            <span>Keranjang</span>
        </a>

        <a href="{{ route('profile') }}" class="zl-bn-item is-active" aria-current="page">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="8.4" r="3.9" />
                <path d="M4.6 20.2a7.6 7.6 0 0 1 14.8 0" />
            </svg>
            <span>Akun</span>
        </a>

    </nav>

    @endif
    {{-- =========================================================
     EPIC PREMIUM FOOTER
     ========================================================= --}}

<footer class="relative mt-24 overflow-hidden bg-[#2d0810] text-white">

    {{-- Garis pemisah emas — pembatas premium antara halaman dan footer --}}
    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-[#d9ad57] to-transparent"></div>

    {{-- Decorative glow --}}
    <div class="pointer-events-none absolute -top-32 -right-32 h-96 w-96 rounded-full bg-[#b98a3d]/10 blur-3xl"></div>

    <div class="pointer-events-none absolute -bottom-40 -left-40 h-96 w-96 rounded-full bg-[#631f2b]/40 blur-3xl"></div>

    <div class="relative mx-auto max-w-7xl px-5 py-16 sm:px-8 lg:py-20">

        {{-- TOP BRAND SECTION --}}
        <div class="grid grid-cols-1 gap-12 lg:grid-cols-[1.4fr_1fr_1fr_1.2fr]">

            {{-- BRAND --}}
            <div class="min-w-0">

                <a
                    href="{{ route('home') }}"
                    class="group inline-flex items-center gap-4"
                >

                    @if($siteLogo)

                        <img
                            src="{{ asset('storage/' . $siteLogo) }}"
                            alt="{{ $siteName }}"
                            class="h-16 w-16 rounded-full border border-[#d9ad57]/50 object-cover shadow-xl transition duration-500 group-hover:scale-105"
                        >

                    @else

                        <span
                            class="flex h-16 w-16 items-center justify-center rounded-full border border-[#d9ad57]/50 bg-[#d9ad57] font-serif text-3xl text-[#2d0810] shadow-xl transition duration-500 group-hover:scale-105"
                        >
                            Z
                        </span>

                    @endif

                    <span class="min-w-0">

                        <span class="block truncate font-serif text-3xl tracking-wide text-[#fff4d6]">
                            {{ $siteName }}
                        </span>

                        <span class="mt-1 block text-[10px] font-semibold uppercase tracking-[0.32em] text-[#d9ad57]">
                            Modest Fashion
                        </span>

                    </span>

                </a>

                <p class="mt-7 max-w-sm text-sm leading-8 text-[#d8b9c0]">
                    Temukan keindahan dalam setiap detail bersama Zalina.
                    Koleksi modest fashion yang dirancang dengan sentuhan
                    elegan, nyaman, dan penuh karakter.
                </p>

                <div class="mt-7 flex items-center gap-3">

                    <span class="h-px w-12 bg-[#d9ad57]"></span>

                    <span class="font-serif text-sm italic text-[#f6dfaa]">
                        Elegance in Every Drape
                    </span>

                </div>

            </div>


            {{-- NAVIGATION --}}
            <div>

                <h3 class="mb-6 text-xs font-bold uppercase tracking-[0.25em] text-[#f6dfaa]">
                    Explore
                </h3>

                <nav class="space-y-4 text-sm" aria-label="Explore">

                    <a
                        href="{{ route('home') }}"
                        class="footer-link block text-[#d8b9c0] hover:text-white"
                    >
                        Beranda
                    </a>

                    <a
                        href="/collections"
                        class="footer-link block text-[#d8b9c0] hover:text-white"
                    >
                        Koleksi
                    </a>

                    <a
                        href="/categories"
                        class="footer-link block text-[#d8b9c0] hover:text-white"
                    >
                        Kategori
                    </a>

                    <a
                        href="/about"
                        class="footer-link block text-[#d8b9c0] hover:text-white"
                    >
                        Tentang {{ $siteName }}
                    </a>

                    <a
                        href="/contact"
                        class="footer-link block text-[#d8b9c0] hover:text-white"
                    >
                        Hubungi Kami
                    </a>

                </nav>

            </div>


            {{-- CUSTOMER CARE --}}
            <div>

                <h3 class="mb-6 text-xs font-bold uppercase tracking-[0.25em] text-[#f6dfaa]">
                    Customer Care
                </h3>

                <nav class="space-y-4 text-sm" aria-label="Customer Care">

                    <a
                        href="/faq"
                        class="footer-link block text-[#d8b9c0] hover:text-white"
                    >
                        FAQ
                    </a>

                    <a
                        href="/shipping-policy"
                        class="footer-link block text-[#d8b9c0] hover:text-white"
                    >
                        Kebijakan Pengiriman
                    </a>

                    <a
                        href="/returns-and-exchanges"
                        class="footer-link block text-[#d8b9c0] hover:text-white"
                    >
                        Retur & Penukaran
                    </a>

                    <a
                        href="/terms"
                        class="footer-link block text-[#d8b9c0] hover:text-white"
                    >
                        Syarat & Ketentuan
                    </a>

                    <a
                        href="/privacy-policy"
                        class="footer-link block text-[#d8b9c0] hover:text-white"
                    >
                        Kebijakan Privasi
                    </a>

                </nav>

            </div>


            {{-- CONTACT --}}
            <div>

                <h3 class="mb-6 text-xs font-bold uppercase tracking-[0.25em] text-[#f6dfaa]">
                    Stay Connected
                </h3>

                <p class="text-sm leading-7 text-[#d8b9c0]">
                    Ikuti perjalanan {{ $siteName }} dan dapatkan informasi koleksi,
                    promo, serta kabar terbaru dari kami.
                </p>

                <div class="mt-6 space-y-4">

                    <a
                        href="{{ $siteSettings['whatsapp_url'] ?? 'https://wa.me/628133117767' }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group flex items-center gap-3 text-sm text-[#d8b9c0] hover:text-white"
                    >

                        <span class="flex h-10 w-10 items-center justify-center rounded-full border border-[#8e5360] transition group-hover:border-[#d9ad57] group-hover:bg-[#d9ad57] group-hover:text-[#2d0810]">

                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 2a10 10 0 00-8.66 15l-1.18 4.3 4.4-1.15A10 10 0 1012 2zm5.2 14.1c-.22.62-1.27 1.13-1.75 1.2-.45.07-1.03.1-1.66-.1-.38-.12-.87-.28-1.5-.55-2.65-1.14-4.38-3.8-4.51-3.98-.13-.18-1.08-1.44-1.08-2.74 0-1.3.68-1.94.92-2.2.24-.26.52-.33.69-.33h.5c.16 0 .37-.06.58.45.22.52.74 1.8.81 1.93.07.13.12.29.02.47-.1.18-.15.29-.3.44-.15.15-.31.33-.44.44-.15.15-.31.31-.13.6.18.29.8 1.31 1.72 2.12 1.18 1.05 2.18 1.38 2.48 1.53.3.15.48.13.66-.08.18-.21.76-.88.96-1.18.2-.3.4-.25.67-.15.28.1 1.76.83 2.06.98.3.15.5.22.57.34.07.12.07.7-.15 1.32z"/>
                            </svg>

                        </span>

                        <span>WhatsApp {{ $siteName }}</span>

                    </a>


                    <a
                        href="mailto:zalinafashion.id@gmail.com"
                        class="group flex items-center gap-3 text-sm text-[#d8b9c0] hover:text-white"
                    >

                        <span class="flex h-10 w-10 items-center justify-center rounded-full border border-[#8e5360] transition group-hover:border-[#d9ad57] group-hover:bg-[#d9ad57] group-hover:text-[#2d0810]">

                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                <path d="M4 6h16v12H4z"/>
                                <path d="m4 7 8 6 8-6"/>
                            </svg>

                        </span>

                        <span class="break-all">
                            zalinafashion.id@gmail.com
                        </span>

                    </a>

                </div>

            </div>

        </div>


        {{-- SOCIAL AREA --}}
        <div class="mt-16 border-t border-[#5b2634] pt-8">

            <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#f6dfaa]">
                        Follow {{ $siteName }}
                    </p>

                    <p class="mt-2 text-sm text-[#b98b96]">
                        Elegance, inspiration, and everyday beauty.
                    </p>

                </div>


                <div class="flex items-center gap-3">

                    {{-- INSTAGRAM --}}
                    <a
                        href="{{ $siteSettings['instagram_url'] ?? 'https://www.instagram.com/zalinascarf.id?igsh=YTNkZGVzdTA1ZGtk' }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Instagram {{ $siteName }}"
                        class="footer-social-icon flex h-12 w-12 items-center justify-center rounded-full border border-[#6d3544] text-[#f6dfaa] transition duration-300 hover:-translate-y-1 hover:border-[#d9ad57] hover:bg-[#d9ad57] hover:text-[#2d0810]"
                    >
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M7 2C4.24 2 2 4.24 2 7v10c0 2.76 2.24 5 5 5h10c2.76 0 5-2.24 5-5V7c0-2.76-2.24-5-5-5H7zm10 2c1.65 0 3 1.35 3 3v10c0 1.65-1.35 3-3 3H7c-1.65 0-3-1.35-3-3V7c0-1.65 1.35-3 3-3h10zm-5 3.5A4.5 4.5 0 1012 16.5 4.5 4.5 0 0012 7.5zm0 2A2.5 2.5 0 1112 14.5 2.5 2.5 0 0112 9.5zM17.5 6a1 1 0 100 2 1 1 0 000-2z"/>
                        </svg>
                    </a>


                    {{-- SHOPEE --}}
                    <a
                        href="{{ $siteSettings['shopee_url'] ?? 'https://id.shp.ee/QdSvtAVp' }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Shopee {{ $siteName }}"
                        class="footer-social-icon flex h-12 w-12 items-center justify-center rounded-full border border-[#6d3544] text-[#f6dfaa] transition duration-300 hover:-translate-y-1 hover:border-[#d9ad57] hover:bg-[#d9ad57] hover:text-[#2d0810]"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="9" cy="20" r="1.4" fill="currentColor" stroke="none"/>
                            <circle cx="17" cy="20" r="1.4" fill="currentColor" stroke="none"/>
                            <path d="M3 4h2l2.2 11.6a2 2 0 0 0 2 1.6h7.4a2 2 0 0 0 1.96-1.6L20 8H6.2"/>
                        </svg>
                    </a>


                    {{-- WHATSAPP --}}
                    <a
                        href="{{ $siteSettings['whatsapp_url'] ?? 'https://wa.me/628133117767' }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="WhatsApp {{ $siteName }}"
                        class="footer-social-icon flex h-12 w-12 items-center justify-center rounded-full border border-[#6d3544] text-[#f6dfaa] transition duration-300 hover:-translate-y-1 hover:border-[#d9ad57] hover:bg-[#d9ad57] hover:text-[#2d0810]"
                    >
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12 2a10 10 0 00-8.66 15l-1.18 4.3 4.4-1.15A10 10 0 1012 2zm5.2 14.1c-.22.62-1.27 1.13-1.75 1.2-.45.07-1.03.1-1.66-.1-.38-.12-.87-.28-1.5-.55-2.65-1.14-4.38-3.8-4.51-3.98-.13-.18-1.08-1.44-1.08-2.74 0-1.3.68-1.94.92-2.2.24-.26.52-.33.69-.33h.5c.16 0 .37-.06.58.45.22.52.74 1.8.81 1.93.07.13.12.29.02.47-.1.18-.15.29-.3.44-.15.15-.31.33-.44.44-.15.15-.31.31-.13.6.18.29.8 1.31 1.72 2.12 1.18 1.05 2.18 1.38 2.48 1.53.3.15.48.13.66-.08.18-.21.76-.88.96-1.18.2-.3.4-.25.67-.15.28.1 1.76.83 2.06.98.3.15.5.22.57.34.07.12.07.7-.15 1.32z"/>
                        </svg>
                    </a>

                </div>

            </div>

        </div>


        {{-- COPYRIGHT --}}
        <div class="mt-10 flex flex-col gap-4 border-t border-[#5b2634] pt-7 text-xs text-[#b98b96] sm:flex-row sm:items-center sm:justify-between">

            <p>
                © {{ date('Y') }} {{ $siteName }}.
                Semua hak cipta dilindungi.
            </p>

            <p class="font-serif text-sm italic text-[#d9ad57]">
                {{ $siteSettings['site_tagline'] ?? 'Elegance in Every Drape' }}
            </p>

        </div>

    </div>

</footer>

    {{-- =========================================================
         ALPINE JS
         ========================================================= --}}

    <script
        defer
        src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"
    ></script>


    {{-- =========================================================
         GLOBAL JAVASCRIPT
         ========================================================= --}}

    <script>

        document.addEventListener(
            'DOMContentLoaded',
            function () {


                /* =================================================
                   REVEAL ANIMATION
                   ================================================= */

                const observer =
                    new IntersectionObserver(
                        function (entries) {

                            entries.forEach(
                                function (entry) {

                                    if (
                                        entry.isIntersecting
                                    ) {

                                        entry.target.classList.add(
                                            'show'
                                        );


                                        observer.unobserve(
                                            entry.target
                                        );

                                    }

                                }
                            );

                        },
                        {
                            threshold: 0.12
                        }
                    );


                document
                    .querySelectorAll(
                        '.reveal, .product-card'
                    )
                    .forEach(
                        function (element) {

                            observer.observe(
                                element
                            );

                        }
                    );


                /* =================================================
                   SCROLL BUBBLES
                   ================================================= */

                let scrollTimer;


                window.addEventListener(
                    'scroll',
                    function () {


                        clearTimeout(
                            scrollTimer
                        );


                        document
                            .querySelectorAll(
                                '.scroll-bubble'
                            )
                            .forEach(
                                function (
                                    bubble,
                                    index
                                ) {


                                    setTimeout(
                                        function () {

                                            bubble.classList.add(
                                                'pop'
                                            );

                                        },
                                        index * 90
                                    );


                                }
                            );


                        scrollTimer =
                            setTimeout(
                                function () {


                                    document
                                        .querySelectorAll(
                                            '.scroll-bubble'
                                        )
                                        .forEach(
                                            function (
                                                bubble
                                            ) {

                                                bubble.classList.remove(
                                                    'pop'
                                                );

                                            }
                                        );


                                },
                                700
                            );


                    },
                    {
                        passive: true
                    }
                );


                /* =================================================
                   PREVENT ACCIDENTAL HORIZONTAL OVERFLOW
                   ================================================= */

                function checkHorizontalOverflow() {


                    document.documentElement.style.overflowX =
                        'hidden';


                    document.body.style.overflowX =
                        'hidden';


                }


                checkHorizontalOverflow();


                /* =================================================
                   CLOSE MOBILE NAV AFTER RESIZE
                   ================================================= */

                window.addEventListener(
                    'resize',
                    function () {


                        if (
                            false
                        ) {


                            const body =
                                document.body;


                            if (
                                body.__x
                                &&
                                body.__x.$data
                            ) {


                                body.__x.$data.mobileNav =
                                    false;


                            }

                        }

                    },
                    {
                        passive: true
                    }
                );


            }
        );

    </script>



    @stack('scripts')

</body>
</html>