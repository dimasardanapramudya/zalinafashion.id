<!doctype html>

<html lang="id">

<head>


<meta charset="utf-8">

{{-- WAJIB UNTUK AJAX / FETCH LARAVEL --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>
    @yield('title', 'Admin') — Zalina Hijab
</title>


{{-- ============================================================
    GOOGLE FONT
============================================================ --}}

<link
    rel="preconnect"
    href="https://fonts.googleapis.com"
>

<link
    rel="preconnect"
    href="https://fonts.gstatic.com"
>

<link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600;700;800&display=swap"
    rel="stylesheet"
>


{{-- ============================================================
    TAILWIND
============================================================ --}}

<script src="https://cdn.tailwindcss.com"></script>


{{-- ============================================================
    TAILWIND CONFIG
============================================================ --}}

<script>

    tailwind.config = {

        theme: {

            extend: {

                fontFamily: {

                    serif: [
                        '"Playfair Display"',
                        'serif'
                    ],

                    sans: [
                        'Inter',
                        'ui-sans-serif',
                        'system-ui',
                        'sans-serif'
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
                        800: '#4c1622',
                        900: '#3a0f19'

                    },


                    gold: {

                        50: '#fdf9ee',
                        100: '#f8edcb',
                        200: '#efd693',
                        300: '#e4b957',
                        400: '#d9a233',
                        500: '#c78924'

                    },


                    cream: '#fbf6f1'

                },


                boxShadow: {

                    soft:
                        '0 10px 40px -12px rgba(76,22,34,0.18)'

                }

            }

        }

    };

</script>


{{-- ============================================================
    ALPINE.JS
============================================================ --}}

<script
    defer
    src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"
></script>


{{-- ============================================================
    CUSTOM STYLE
============================================================ --}}

<style>

    [x-cloak] {
        display: none !important;
    }


    html {
        scroll-behavior: smooth;
    }


    body {
        background: #f7f4f2;
    }


    .font-serif {
        font-family: 'Playfair Display', serif;
    }


    table th {

        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #9c7580;

    }


    .sidebar-scroll::-webkit-scrollbar {
        width: 4px;
    }


    .sidebar-scroll::-webkit-scrollbar-thumb {

        background: rgba(255, 255, 255, 0.18);
        border-radius: 999px;

    }


    /* =========================================================
        ZALINA ADMIN TOAST
    ========================================================= */

    .zalina-toast {

        animation:
            zalinaToastIn
            0.45s
            cubic-bezier(.22,1,.36,1)
            forwards;

    }


    .zalina-toast-leaving {

        animation:
            zalinaToastOut
            0.35s
            cubic-bezier(.4,0,1,1)
            forwards;

    }


    .zalina-toast-progress {

        animation:
            zalinaToastProgress
            5s
            linear
            forwards;

        transform-origin: left center;

    }


    @keyframes zalinaToastIn {

        0% {

            opacity: 0;
            transform:
                translate3d(30px, -8px, 0)
                scale(.96);

        }

        100% {

            opacity: 1;
            transform:
                translate3d(0, 0, 0)
                scale(1);

        }

    }


    @keyframes zalinaToastOut {

        0% {

            opacity: 1;
            transform:
                translate3d(0, 0, 0)
                scale(1);

        }

        100% {

            opacity: 0;
            transform:
                translate3d(25px, -6px, 0)
                scale(.96);

        }

    }


    @keyframes zalinaToastProgress {

        0% {
            transform: scaleX(1);
        }

        100% {
            transform: scaleX(0);
        }

    }


    @media (max-width: 640px) {

        .zalina-toast-container {

            left: 1rem;
            right: 1rem;
            width: auto;

        }

    }

</style>


</head>

@php


/*
|--------------------------------------------------------------------------
| SETTINGS ADMIN
|--------------------------------------------------------------------------
*/

$adminSettings = \App\Models\Setting::pluck(
    'value',
    'key'
);


$adminSiteName =
    $adminSettings['site_name']
    ?? 'Zalina Hijab';


$adminLogo =
    $adminSettings['site_logo']
    ?? null;
    try {
    $pendingReturnsCount = \App\Models\Order::where(
        'return_status',
        'requested'
    )->count();
} catch (\Throwable $e) {
    $pendingReturnsCount = 0;
}


/*
|--------------------------------------------------------------------------
| NAVIGATION ADMIN
|--------------------------------------------------------------------------
*/
try {
    $pendingReturnsCount = \App\Models\Order::where(
        'return_status',
        \App\Models\Order::RETURN_REQUESTED
    )->count();
} catch (\Throwable $e) {
    $pendingReturnsCount = 0;
}
$nav = [

    [
        'route' => 'admin.dashboard',
        'label' => 'Dashboard',
        'icon' => 'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z'
    ],

    [
        'route' => 'admin.products.index',
        'label' => 'Produk',
        'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-14L4 7m8 4v10M4 7v10l8 4'
    ],

    [
        'route' => 'admin.categories.index',
        'label' => 'Kategori',
        'icon' => 'M4 6h16M4 12h16M4 18h7'
    ],

    [
        'route' => 'admin.orders.index',
        'label' => 'Pesanan',
        'icon' => 'M3 3h2l.4 2M7 13h10l3.6-8H5.4M7 13L5.4 5M7 13l-1.5 6h11M9 21a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z'
    ],

    [
        'route' => 'admin.returns.index',
        'label' => 'Retur',
        'icon' => 'M3 10h10a4 4 0 010 8H7m-4-8l4-4m-4 4l4 4'
    ],

    [
        'route' => 'admin.payments.index',
        'label' => 'Verifikasi Pembayaran',
        'icon' => 'M9 14l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
    ],

    [
        'route' => 'admin.payment-methods.index',
        'label' => 'Metode Pembayaran',
        'icon' => 'M3 10h18M7 15h1m4 0h1M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z'
    ],

    [
        'route' => 'admin.discounts.index',
        'label' => 'Promo & Diskon',
        'icon' => 'M7 7h.01M3 3h7l11 11-7 7L3 10V3zM11 11h.01'
    ],

    [
        'route' => 'admin.sliders.index',
        'label' => 'Slider & Iklan',
        'icon' => 'M4 5h16v14H4zM8 9l3 3 2-2 3 4'
    ],

    [
        'route' => 'admin.settings.index',
        'label' => 'Pengaturan Toko',
        'icon' => 'M12 15.5a3.5 3.5 0 100-7 3.5 3.5 0 000 7zm7.43-2.5a7.95 7.95 0 000-2l2-1.55-2-3.46-2.35.95a7.7 7.7 0 00-1.73-1L15 3h-4l-.35 2.94a7.7 7.7 0 00-1.73 1L6.57 6l-2 3.46L6.57 11a7.95 7.95 0 000 2l-2 1.55 2 3.46 2.35-.95a7.7 7.7 0 001.73 1L11 21h4l.35-2.94a7.7 7.7 0 001.73-1l2.35.95 2-3.46L19.43 13z'
    ]

];


@endphp

<body
    class="text-maroon-900"
    x-data="{ sidebar: false }"
>

<div class="min-h-screen flex">


{{-- ============================================================
    MOBILE OVERLAY
============================================================ --}}

<div
    x-cloak
    x-show="sidebar"
    x-transition.opacity
    class="fixed inset-0 bg-black/40 z-30 lg:hidden"
    @click="sidebar = false"
></div>


{{-- ============================================================
    SIDEBAR
============================================================ --}}

<aside
    :class="
        sidebar
            ? 'translate-x-0'
            : '-translate-x-full lg:translate-x-0'
    "
    class="
        fixed
        lg:sticky
        top-0
        z-40
        h-screen
        w-64
        shrink-0
        bg-maroon-900
        text-maroon-100
        flex
        flex-col
        transition-transform
        duration-300
    "
>


    {{-- ========================================================
        LOGO
    ======================================================== --}}

    <div
        class="
            h-20
            flex
            items-center
            gap-3
            px-6
            border-b
            border-maroon-800
        "
    >

        @if($adminLogo)

            <img
                src="{{ asset('storage/' . $adminLogo) }}"
                class="
                    w-10
                    h-10
                    rounded-full
                    object-cover
                    border
                    border-maroon-700
                "
                alt="{{ $adminSiteName }}"
            >

        @else

            <span
                class="
                    w-10
                    h-10
                    shrink-0
                    rounded-full
                    bg-gradient-to-br
                    from-gold-400
                    to-gold-500
                    flex
                    items-center
                    justify-center
                "
            >

                <span
                    class="
                        font-serif
                        text-maroon-900
                        text-lg
                    "
                >
                    Z
                </span>

            </span>

        @endif


        <span
            class="
                font-serif
                text-lg
                text-cream
                truncate
            "
        >
            {{ $adminSiteName }}
        </span>

    </div>


    {{-- ========================================================
        NAVIGATION
    ======================================================== --}}

    <nav
        class="
            sidebar-scroll
            flex-1
            px-4
            py-6
            space-y-1
            overflow-y-auto
        "
    >

        @foreach($nav as $item)

  @php
    $notificationCount = $item['badge'] ?? 0;

    if ($item['route'] === 'admin.payments.index') {
        $notificationCount = $pendingPaymentCount ?? 0;
    }

    if ($item['route'] === 'admin.orders.index') {
        $notificationCount = $pendingOrderCount ?? 0;
    }

    if ($item['route'] === 'admin.returns.index') {
        $notificationCount = $pendingReturnsCount ?? 0;
    }
@endphp

            <a
                href="{{ route($item['route']) }}"
                class="
                    flex
                    items-center
                    gap-3
                    px-3
                    py-2.5
                    rounded-xl
                    text-sm
                    font-medium
                    transition
                    duration-200

                    {{
                        request()->routeIs($item['route'])

                        ? 'bg-maroon-700 text-cream shadow-soft'

                        : 'text-maroon-300 hover:bg-maroon-800 hover:text-cream'
                    }}
                "
            >

                <span class="relative shrink-0">

                    <svg
                        class="
                            w-5
                            h-5
                        "
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="{{ $item['icon'] }}"
                        />

                    </svg>

                    @if($notificationCount > 0)
                        <span
                            class="
                                absolute
                                -top-3
                                -right-3
                                min-w-5
                                h-5
                                px-1
                                flex
                                items-center
                                justify-center
                                rounded-full
                                bg-red-500
                                text-white
                                text-[10px]
                                font-bold
                                leading-none
                                ring-2
                                ring-maroon-900
                            "
                        >
                            {{ $notificationCount > 99 ? '99+' : $notificationCount }}
                        </span>
                    @endif

                </span>
                <span class="flex-1">
    {{ $item['label'] }}
</span>

            </a>

        @endforeach

    </nav>


    {{-- ========================================================
        BOTTOM NAVIGATION
    ======================================================== --}}

    <div
        class="
            p-4
            border-t
            border-maroon-800
        "
    >

        <a
            href="{{ route('home') }}"
            class="
                flex
                items-center
                gap-3
                px-3
                py-2.5
                rounded-xl
                text-sm
                font-medium
                text-maroon-300
                hover:bg-maroon-800
                hover:text-cream
                transition
            "
        >

            <svg
                class="
                    w-5
                    h-5
                "
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M10 19l-7-7m0 0l7-7m-7 7h18"
                />

            </svg>


            <span>
                Kembali ke Toko
            </span>

        </a>

    </div>


</aside>


{{-- ============================================================
    MAIN CONTENT
============================================================ --}}

<div
    class="
        flex-1
        min-w-0
        flex
        flex-col
    "
>


    {{-- ========================================================
        HEADER
    ======================================================== --}}

    <header
        class="
            h-20
            bg-white
            border-b
            border-maroon-100
            flex
            items-center
            justify-between
            px-4
            sm:px-6
            sticky
            top-0
            z-20
        "
    >


        {{-- LEFT HEADER --}}

        <div
            class="
                flex
                items-center
                gap-4
            "
        >


            {{-- MOBILE MENU --}}

            <button
                @click="sidebar = true"
                class="
                    lg:hidden
                    p-2
                    rounded-lg
                    hover:bg-maroon-50
                    transition
                "
                type="button"
                aria-label="Buka menu"
            >

                <svg
                    class="
                        w-5
                        h-5
                        text-maroon-800
                    "
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16"
                    />

                </svg>

            </button>


            {{-- PAGE TITLE --}}

            <h1
                class="
                    font-serif
                    text-xl
                    sm:text-2xl
                    text-maroon-900
                "
            >

                @yield('title', 'Dashboard')

            </h1>

        </div>


        {{-- RIGHT HEADER --}}

        <div
            class="
                flex
                items-center
                gap-3
            "
        >

            <span
                class="
                    hidden
                    sm:block
                    text-sm
                    text-maroon-500
                "
            >

                {{ now()->translatedFormat('l, d F Y') }}

            </span>


            <span
                class="
                    w-9
                    h-9
                    rounded-full
                    bg-maroon-100
                    flex
                    items-center
                    justify-center
                    font-semibold
                    text-maroon-700
                    text-sm
                "
            >
                A
            </span>

        </div>


    </header>


    {{-- ========================================================
        ZALINA TOAST NOTIFICATION
    ======================================================== --}}

    <div
        class="
            zalina-toast-container
            fixed
            top-5
            right-5
            z-[100]
            w-[380px]
            max-w-[calc(100vw-2rem)]
            space-y-3
            pointer-events-none
        "
    >


        {{-- ====================================================
            SUCCESS TOAST
        ==================================================== --}}

        @if(session('success'))

            <div
                x-data="{ show: true }"
                x-init="
                    setTimeout(() => {
                        show = false;
                    }, 5000);
                "
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-5 scale-95"
                x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                x-transition:leave-end="opacity-0 translate-x-5 scale-95"
                class="
                    zalina-toast
                    relative
                    overflow-hidden
                    pointer-events-auto
                    bg-white
                    border
                    border-maroon-100
                    rounded-2xl
                    shadow-[0_18px_50px_-15px_rgba(58,15,25,0.28)]
                "
                role="alert"
                aria-live="polite"
            >

                <div class="flex items-start gap-4 p-4">

                    {{-- ICON --}}

                    <div
                        class="
                            w-10
                            h-10
                            shrink-0
                            rounded-xl
                            bg-green-50
                            border
                            border-green-100
                            flex
                            items-center
                            justify-center
                        "
                    >

                        <svg
                            class="
                                w-5
                                h-5
                                text-green-600
                            "
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 13l4 4L19 7"
                            />

                        </svg>

                    </div>


                    {{-- CONTENT --}}

                    <div class="min-w-0 flex-1 pt-0.5">

                        <p
                            class="
                                text-[11px]
                                font-bold
                                uppercase
                                tracking-[0.12em]
                                text-green-700
                                mb-1
                            "
                        >
                            Berhasil
                        </p>

                        <p
                            class="
                                text-sm
                                leading-5
                                font-medium
                                text-maroon-900
                            "
                        >
                            {{ session('success') }}
                        </p>

                    </div>


                    {{-- CLOSE --}}

                    <button
                        type="button"
                        @click="show = false"
                        class="
                            shrink-0
                            p-1.5
                            rounded-lg
                            text-maroon-300
                            hover:text-maroon-700
                            hover:bg-maroon-50
                            transition
                        "
                        aria-label="Tutup notifikasi"
                    >

                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />

                        </svg>

                    </button>

                </div>


                {{-- PROGRESS BAR --}}

                <div
                    class="
                        absolute
                        bottom-0
                        left-0
                        h-0.5
                        w-full
                        bg-green-100
                    "
                >

                    <div
                        class="
                            zalina-toast-progress
                            h-full
                            bg-green-500
                        "
                    ></div>

                </div>

            </div>

        @endif


        {{-- ====================================================
            ERROR TOAST
        ==================================================== --}}

        @if(session('error'))

            <div
                x-data="{ show: true }"
                x-init="
                    setTimeout(() => {
                        show = false;
                    }, 5000);
                "
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-5 scale-95"
                x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                x-transition:leave-end="opacity-0 translate-x-5 scale-95"
                class="
                    zalina-toast
                    relative
                    overflow-hidden
                    pointer-events-auto
                    bg-white
                    border
                    border-maroon-100
                    rounded-2xl
                    shadow-[0_18px_50px_-15px_rgba(58,15,25,0.28)]
                "
                role="alert"
                aria-live="assertive"
            >

                <div class="flex items-start gap-4 p-4">

                    {{-- ICON --}}

                    <div
                        class="
                            w-10
                            h-10
                            shrink-0
                            rounded-xl
                            bg-maroon-50
                            border
                            border-maroon-100
                            flex
                            items-center
                            justify-center
                        "
                    >

                        <svg
                            class="
                                w-5
                                h-5
                                text-maroon-600
                            "
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 9v4m0 4h.01M10.29 3.86l-7.82 13.5A2 2 0 004.2 20.5h15.6a2 2 0 001.73-3.14l-7.82-13.5a2 2 0 00-3.42 0z"
                            />

                        </svg>

                    </div>


                    {{-- CONTENT --}}

                    <div class="min-w-0 flex-1 pt-0.5">

                        <p
                            class="
                                text-[11px]
                                font-bold
                                uppercase
                                tracking-[0.12em]
                                text-maroon-600
                                mb-1
                            "
                        >
                            Perhatian
                        </p>

                        <p
                            class="
                                text-sm
                                leading-5
                                font-medium
                                text-maroon-900
                            "
                        >
                            {{ session('error') }}
                        </p>

                    </div>


                    {{-- CLOSE --}}

                    <button
                        type="button"
                        @click="show = false"
                        class="
                            shrink-0
                            p-1.5
                            rounded-lg
                            text-maroon-300
                            hover:text-maroon-700
                            hover:bg-maroon-50
                            transition
                        "
                        aria-label="Tutup notifikasi"
                    >

                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />

                        </svg>

                    </button>

                </div>


                {{-- PROGRESS BAR --}}

                <div
                    class="
                        absolute
                        bottom-0
                        left-0
                        h-0.5
                        w-full
                        bg-maroon-100
                    "
                >

                    <div
                        class="
                            zalina-toast-progress
                            h-full
                            bg-maroon-600
                        "
                    ></div>

                </div>

            </div>

        @endif


        {{-- ====================================================
            VALIDATION TOAST
        ==================================================== --}}

        @if($errors->any())

            <div
                x-data="{ show: true }"
                x-init="
                    setTimeout(() => {
                        show = false;
                    }, 6500);
                "
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-5 scale-95"
                x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                x-transition:leave-end="opacity-0 translate-x-5 scale-95"
                class="
                    zalina-toast
                    relative
                    overflow-hidden
                    pointer-events-auto
                    bg-white
                    border
                    border-maroon-100
                    rounded-2xl
                    shadow-[0_18px_50px_-15px_rgba(58,15,25,0.28)]
                "
                role="alert"
                aria-live="assertive"
            >

                <div class="flex items-start gap-4 p-4">

                    {{-- ICON --}}

                    <div
                        class="
                            w-10
                            h-10
                            shrink-0
                            rounded-xl
                            bg-amber-50
                            border
                            border-amber-100
                            flex
                            items-center
                            justify-center
                        "
                    >

                        <svg
                            class="
                                w-5
                                h-5
                                text-amber-600
                            "
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 9v4m0 4h.01M10.29 3.86l-7.82 13.5A2 2 0 004.2 20.5h15.6a2 2 0 001.73-3.14l-7.82-13.5a2 2 0 00-3.42 0z"
                            />

                        </svg>

                    </div>


                    {{-- CONTENT --}}

                    <div class="min-w-0 flex-1 pt-0.5">

                        <p
                            class="
                                text-[11px]
                                font-bold
                                uppercase
                                tracking-[0.12em]
                                text-amber-700
                                mb-1
                            "
                        >
                            Periksa Kembali
                        </p>


                        <div
                            class="
                                text-sm
                                leading-5
                                font-medium
                                text-maroon-900
                            "
                        >

                            @foreach($errors->all() as $error)

                                <p class="mb-1 last:mb-0">
                                    {{ $error }}
                                </p>

                            @endforeach

                        </div>

                    </div>


                    {{-- CLOSE --}}

                    <button
                        type="button"
                        @click="show = false"
                        class="
                            shrink-0
                            p-1.5
                            rounded-lg
                            text-maroon-300
                            hover:text-maroon-700
                            hover:bg-maroon-50
                            transition
                        "
                        aria-label="Tutup notifikasi"
                    >

                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />

                        </svg>

                    </button>

                </div>


                {{-- PROGRESS BAR --}}

                <div
                    class="
                        absolute
                        bottom-0
                        left-0
                        h-0.5
                        w-full
                        bg-amber-100
                    "
                >

                    <div
                        class="
                            zalina-toast-progress
                            h-full
                            bg-amber-500
                        "
                        style="animation-duration: 6.5s;"
                    ></div>

                </div>

            </div>

        @endif

    </div>


    {{-- ========================================================
        PAGE CONTENT
    ======================================================== --}}

    <main
        class="
            flex-1
            p-4
            sm:p-6
        "
    >

        {{-- ====================================================
            PAGE CONTENT
        ==================================================== --}}

        @yield('content')

    </main>


</div>


</div>

</body>

</html>
