@extends('layouts.store')

@section('title', 'Profile — Zalina Fashion')

@section('bodyClass', 'page-scoped account-mode')
@section('accountMode', '1')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | DATA USER
    |--------------------------------------------------------------------------
    */

    $user = \App\Models\User::find(
        session('zalina_user_id')
    );


    /*
    |--------------------------------------------------------------------------
    | PROFILE ORDER BADGE
    |--------------------------------------------------------------------------
    |
    | Badge menghitung pesanan customer yang masih aktif.
    |
    | Yang dihitung:
    |
    | - Pesanan belum dibayar
    | - Pesanan menunggu verifikasi pembayaran
    | - Pesanan sudah dibayar
    | - Pesanan sudah dikonfirmasi admin
    | - Pesanan sedang diproses
    | - Pesanan sedang dikemas
    | - Pesanan sudah dikirim
    |
    | Yang tidak dihitung:
    |
    | - Pesanan selesai
    | - Pesanan delivered
    | - Pesanan dibatalkan
    |
    */

    $activeOrderCount = 0;

    $pendingPaymentCount = 0;

    $paidOrderCount = 0;

    $finishedOrderCount = 0;

    $totalOrderCount = 0;

    $profileOrders = collect();


    if ($user && $user->role !== 'admin') {

        /*
        |--------------------------------------------------------------------------
        | QUERY PESANAN (SATU KALI SAJA)
        |--------------------------------------------------------------------------
        |
        | Sebelumnya query pesanan dijalankan DUA KALI: sekali di sini untuk
        | menghitung badge, dan sekali lagi di bagian "Riwayat Pesanan".
        | Keduanya menarik data yang sama persis, jadi sekarang digabung
        | menjadi satu query dengan eager loading lengkap supaya tidak ada
        | query ganda dan tidak ada N+1 saat merender daftar pesanan.
        |
        */

        $profileOrders = \App\Models\Order::query()

            ->with([
                'payment',
                'payment.method',
                'items',
                'items.product',
                'items.variant',
            ])

            ->where('user_id', $user->id)

            ->latest()

            ->get();


        /*
        |--------------------------------------------------------------------------
        | Status Pesanan Yang Sudah Selesai
        |--------------------------------------------------------------------------
        */

        $finishedStatuses = [

            'completed',

            'complete',

            'delivered',

            'finished',

            'success',

        ];


        /*
        |--------------------------------------------------------------------------
        | Status Pesanan Yang Dibatalkan
        |--------------------------------------------------------------------------
        */

        $cancelledStatuses = [

            'cancelled',

            'canceled',

            'rejected',

            'failed',

        ];


        /*
        |--------------------------------------------------------------------------
        | Status Pesanan Aktif
        |--------------------------------------------------------------------------
        */

        $activeStatuses = [

            'pending',

            'processing',

            'confirmed',

            'paid',

            'packed',

            'ready_to_ship',

            'handed_to_courier',

            'shipped',

            'waiting',

        ];


        /*
        |--------------------------------------------------------------------------
        | Status Pembayaran Aktif
        |--------------------------------------------------------------------------
        */

        $activePaymentStatuses = [

            'unpaid',

            'pending_verification',

            'under_review',

            'paid',

            'verified',

            'confirmed',

        ];


        /*
        |--------------------------------------------------------------------------
        | Hitung Badge Profile
        |--------------------------------------------------------------------------
        */

        $activeOrderCount = $profileOrders

            ->filter(function ($order) use (

                $finishedStatuses,

                $cancelledStatuses,

                $activeStatuses,

                $activePaymentStatuses

            ) {

                $orderStatus = strtolower(

                    trim(

                        (string) $order->status

                    )

                );


                $paymentStatus = strtolower(

                    trim(

                        (string) optional(

                            $order->payment

                        )->status

                    )

                );


                /*
                |--------------------------------------------------------------------------
                | Jangan hitung pesanan selesai
                |--------------------------------------------------------------------------
                */

                if (

                    in_array(

                        $orderStatus,

                        $finishedStatuses,

                        true

                    )

                ) {

                    return false;

                }


                /*
                |--------------------------------------------------------------------------
                | Jangan hitung pesanan dibatalkan
                |--------------------------------------------------------------------------
                */

                if (

                    in_array(

                        $orderStatus,

                        $cancelledStatuses,

                        true

                    )

                ) {

                    return false;

                }


                /*
                |--------------------------------------------------------------------------
                | Pesanan aktif berdasarkan status order
                |--------------------------------------------------------------------------
                */

                $isActiveOrder = in_array(

                    $orderStatus,

                    $activeStatuses,

                    true

                );


                /*
                |--------------------------------------------------------------------------
                | Pesanan aktif berdasarkan status payment
                |--------------------------------------------------------------------------
                */

                $isActivePayment = in_array(

                    $paymentStatus,

                    $activePaymentStatuses,

                    true

                );


                return $isActiveOrder || $isActivePayment;

            })

            ->count();


        /*
        |--------------------------------------------------------------------------
        | Hitung Pesanan Belum Dibayar
        |--------------------------------------------------------------------------
        */

        $pendingPaymentCount = $profileOrders

            ->filter(function ($order) use (

                $finishedStatuses,

                $cancelledStatuses

            ) {

                $orderStatus = strtolower(

                    trim(

                        (string) $order->status

                    )

                );


                /*
                |--------------------------------------------------------------------------
                | Jangan hitung pesanan selesai / dibatalkan
                |--------------------------------------------------------------------------
                |
                | Sebelumnya filter ini HANYA melihat status pembayaran, sehingga
                | pesanan yang sudah dibatalkan (yang tidak punya record payment,
                | jadi status pembayarannya '') ikut terhitung sebagai "menunggu
                | pembayaran" selamanya, dan badge merah tidak pernah hilang.
                |
                */

                if (

                    in_array($orderStatus, $finishedStatuses, true)

                    || in_array($orderStatus, $cancelledStatuses, true)

                ) {

                    return false;

                }


                $paymentStatus = strtolower(

                    trim(

                        (string) optional(

                            $order->payment

                        )->status

                    )

                );


                return $paymentStatus === 'unpaid'

                    || $paymentStatus === ''

                    || $paymentStatus === 'pending'

                    || $paymentStatus === 'under_review'

                    || $paymentStatus === 'pending_verification';

            })

            ->count();


        /*
        |--------------------------------------------------------------------------
        | Hitung Pesanan Sudah Dibayar
        |--------------------------------------------------------------------------
        */

        $paidOrderCount = $profileOrders

            ->filter(function ($order) {

                $paymentStatus = strtolower(

                    trim(

                        (string) optional(

                            $order->payment

                        )->status

                    )

                );


                return in_array(

                    $paymentStatus,

                    [

                        'paid',

                        'verified',

                        'confirmed',

                    ],

                    true

                );

            })

            ->count();


        /*
        |--------------------------------------------------------------------------
        | Hitung Pesanan Selesai & Total
        |--------------------------------------------------------------------------
        */

        $finishedOrderCount = $profileOrders

            ->filter(function ($order) use ($finishedStatuses) {

                return in_array(

                    strtolower(trim((string) $order->status)),

                    $finishedStatuses,

                    true

                );

            })

            ->count();


        $totalOrderCount = $profileOrders->count();

        /*
        |--------------------------------------------------------------------------
        | Hitung Pesanan Dengan Retur
        |--------------------------------------------------------------------------
        |
        | Order dianggap "retur" kalau kolom return_status sudah diisi dan
        | bukan 'none'. Dipakai untuk kartu Ringkasan Akun dan tab filter
        | "Retur" di riwayat pesanan (lihat $orderFilterGroup di bawah).
        */

        $returnOrderCount = $profileOrders
            ->filter(function ($order) {
                $returnStatus = strtolower(trim((string) ($order->return_status ?? '')));

                return $returnStatus !== '' && $returnStatus !== 'none';
            })
            ->count();

    }
@endphp

@php
    /*
    |--------------------------------------------------------------------------
    | DATA TAMPILAN (hanya untuk layout — tidak ada data/fitur baru)
    |--------------------------------------------------------------------------
    | Semua nilai di bawah berasal dari variabel yang sudah dihitung di atas.
    */

    $isAdminUser = $user && $user->role === 'admin';

    $userInitial = $user
        ? (mb_strtoupper(mb_substr(trim((string) $user->name), 0, 1)) ?: 'Z')
        : 'Z';

    $joinedShort = ($user && !empty($user->created_at))
        ? $user->created_at->translatedFormat('d M Y')
        : null;

    $heroNotice = null;

    if ($user && !$isAdminUser) {
        if ($pendingPaymentCount > 0) {
            $heroNotice = $pendingPaymentCount . ' pesanan menunggu pembayaran Anda';
        } elseif ($activeOrderCount > 0) {
            $heroNotice = $activeOrderCount . ' pesanan sedang diproses';
        }
    }

    $ico = [
        'user'     => 'M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.1a7.5 7.5 0 0 1 15 0',
        'mail'     => 'M2.25 6.75c0-.83.67-1.5 1.5-1.5h16.5c.83 0 1.5.67 1.5 1.5v10.5a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5V6.75Zm1.5 0 8.25 6 8.25-6',
        'calendar' => 'M6.75 3v2.25M17.25 3v2.25M3.75 8.25h16.5M4.5 6h15a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75.75h-15a.75.75 0 0 1-.75-.75V6.75A.75.75 0 0 1 4.5 6Z',
        'check'    => 'm4.5 12.75 6 6 9-13.5',
        'orders'   => 'M9 5h6M9 9h6M9 13h4m-7 8h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2Z',
        'bag'      => 'M6.2 7.6h11.6l-1 11.1a1.8 1.8 0 0 1-1.8 1.6H9a1.8 1.8 0 0 1-1.8-1.6ZM9.2 7.6V6.3a2.8 2.8 0 0 1 5.6 0v1.3',
        'clock'    => 'M12 6.75v5.25l3.5 2.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        'box'      => 'M21 7.5v9L12 21l-9-4.5v-9L12 3l9 4.5ZM12 12l9-4.5M12 12v9M12 12 3 7.5',
        'done'     => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        'home'     => 'M3 11.5 12 4l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-8.5Z',
        'heart'    => 'M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z',
        'logout'   => 'M10 17l5-5-5-5M15 12H3m12-7h3a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-3',
        'dash'     => 'M3 13h8V3H3v10Zm10 8h8V11h-8v10ZM3 21h8v-6H3v6Zm10-10h8V3h-8v8Z',
        'store'    => 'm3 3 2 1 2.5 10h10.8l2.2-7H7.5',
        'chev'     => 'm9 5 7 7-7 7',
        'back'     => 'M15 19l-7-7 7-7',
        'retur'    => 'M9 15 4 10l5-5M4 10h10a6 6 0 0 1 6 6v1',
    ];

    // Baris "Informasi Akun" / "Informasi Pribadi" — data yang sama seperti sebelumnya
    $accountRows = [
        ['label' => 'Nama Lengkap', 'value' => $user?->name,  'icon' => $ico['user']],
        ['label' => 'Email',        'value' => $user?->email, 'icon' => $ico['mail']],
    ];

    if ($user && !empty($user->created_at)) {
        $accountRows[] = [
            'label' => 'Bergabung Sejak',
            'value' => $user->created_at->translatedFormat('d F Y'),
            'icon'  => $ico['calendar'],
        ];
    }

    $accountRows[] = [
        'label' => 'Status Akun',
        'value' => $isAdminUser ? 'Administrator Aktif' : 'Customer Aktif',
        'icon'  => $ico['check'],
        'badge' => true,
    ];

    // Ringkasan pesanan — angka yang sama seperti kartu statistik sebelumnya
    $summaryRows = [
        ['label' => 'Total Pesanan', 'value' => $totalOrderCount,      'icon' => $ico['orders'], 'suffix' => 'pesanan'],
        ['label' => 'Belum Bayar',   'value' => $pendingPaymentCount,  'icon' => $ico['clock'],  'suffix' => 'pesanan'],
        ['label' => 'Diproses',      'value' => $activeOrderCount,     'icon' => $ico['box'],    'suffix' => 'pesanan'],
        ['label' => 'Selesai',       'value' => $finishedOrderCount,   'icon' => $ico['done'],   'suffix' => 'pesanan'],
    ];

    // Produk retur -- hanya ditampilkan kalau customer pernah mengajukan retur,
    // supaya kartu ini tidak memenuhi ringkasan akun customer yang belum pernah retur.
    if (!$isAdminUser && $returnOrderCount > 0) {
        $summaryRows[] = [
            'label'  => 'Produk Diretur',
            'value'  => $returnOrderCount,
            'icon'   => $ico['retur'],
            'suffix' => 'retur',
            'href'   => '#riwayat-pesanan?filter=retur',
        ];
    }

    // Menu samping (desktop) — hanya halaman yang memang sudah ada
    $sideItems = [
        ['label' => 'Beranda', 'href' => route('home'), 'icon' => $ico['home'], 'active' => false],
    ];

    if ($isAdminUser) {
        $sideItems[] = ['label' => 'Dashboard Admin', 'href' => route('admin.dashboard'), 'icon' => $ico['dash'], 'active' => false];
    } else {
        $sideItems[] = ['label' => 'Pesanan Saya', 'href' => '#riwayat-pesanan', 'icon' => $ico['orders'], 'active' => true];
    }

    $sideItems[] = ['label' => 'Wishlist',  'href' => route('favorites.index'), 'icon' => $ico['heart'], 'active' => false];
    $sideItems[] = ['label' => 'Keranjang', 'href' => route('cart.index'),      'icon' => $ico['bag'],   'active' => false];

    // Aksi cepat (mobile)
    $quickItems = $isAdminUser
        ? [
            ['label' => 'Dashboard', 'href' => route('admin.dashboard'), 'icon' => $ico['dash']],
            ['label' => 'Toko',      'href' => route('shop'),            'icon' => $ico['store']],
            ['label' => 'Wishlist',  'href' => route('favorites.index'), 'icon' => $ico['heart']],
        ]
        : [
            ['label' => 'Pesanan Saya', 'href' => '#riwayat-pesanan',       'icon' => $ico['orders']],
            ['label' => 'Wishlist',     'href' => route('favorites.index'), 'icon' => $ico['heart']],
            ['label' => 'Keranjang',    'href' => route('cart.index'),      'icon' => $ico['bag']],
        ];
@endphp


<style>
    /* Kolom tabel riwayat pesanan (desktop) — dipakai header & baris agar selalu sejajar */
    @media (min-width: 1024px) {
        .zl-cols {
            display: grid;
            grid-template-columns:
                minmax(172px, 1.35fr)
                minmax(138px, 1fr)
                minmax(0, 2.2fr)
                minmax(88px, .85fr)
                minmax(130px, 1.15fr)
                128px;
            align-items: center;
            column-gap: 16px;
        }
    }

    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    .zalina-order summary::-webkit-details-marker { display: none; }
    .zalina-order summary::marker { content: ''; }
    .zalina-order summary {
        display: block;
        cursor: pointer;
        list-style: none;
        -webkit-tap-highlight-color: transparent;
    }
    .zalina-order[open] > summary {
        border-bottom: 1px solid #f3e3e7;
    }
    .zalina-order[open] .zalina-order-chevron {
        transform: rotate(180deg);
    }
    .zalina-order-chevron {
        transition: transform .25s ease;
    }
    .zalina-order-body {
        background: #fdf9f8;
        animation: zalinaOrderReveal .28s ease both;
    }
    @keyframes zalinaOrderReveal {
        from { opacity: 0; transform: translateY(-6px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @media (prefers-reduced-motion: reduce) {
        .zalina-order-body { animation: none; }
        .zalina-order-chevron { transition: none; }
    }

    .zalina-order-tab {
        background: #fdf3f3;
        color: #7c2d3a;
        border-color: #f3dde0;
    }
    .zalina-order-tab:hover {
        background: #fbe9ea;
    }
    .zalina-order-tab.is-active {
        background: #6d1f2b;
        color: #fff;
        border-color: #6d1f2b;
    }

    /*
    | GRUP TAB RIWAYAT PESANAN — mobile-first.
    |
    | < 480px (default)  : grid 3 kolom, tab jadi 2 baris otomatis.
    |                       Semua tab SELALU terlihat, tidak ada yang
    |                       tersembunyi di luar layar/scroll.
    | >= 480px (xs) & up  : kembali ke satu baris + scroll horizontal,
    |                       karena ruang sudah cukup.
    */
    .zl-order-tabs {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    @media (min-width: 480px) {
        .zl-order-tabs {
            display: flex;
            overflow-x: auto;
        }
    }

    .zalina-order-tab {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        text-align: center;
        white-space: nowrap;
    }

    /* Tombol "Retur" dibuat beda warna (amber) supaya langsung
       kelihatan meski letaknya di ujung baris/scroll. */
    .zalina-order-tab-retur {
        background: #fff7e6;
        color: #92650f;
        border-color: #f3dfab;
    }
    .zalina-order-tab-retur:hover {
        background: #fdedc7;
    }
    .zalina-order-tab-retur.is-active {
        background: #b7791f;
        color: #fff;
        border-color: #b7791f;
    }
    .zl-tab-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 16px;
        height: 16px;
        padding: 0 4px;
        border-radius: 999px;
        background: #b7791f;
        color: #fff;
        font-size: 10px;
        font-weight: 800;
        line-height: 1;
    }
    .zalina-order-tab-retur.is-active .zl-tab-badge {
        background: rgba(255, 255, 255, .28);
    }
</style>

<div class="zl-acc-page">

    {{-- ============================================================= --}}
    {{-- USER LOGIN --}}
    {{-- ============================================================= --}}

    @if($user)

        {{-- ========================================================= --}}
        {{-- SHELL: SIDEBAR (desktop) + KONTEN --}}
        {{-- ========================================================= --}}

        <div class="mx-auto w-full max-w-[1440px] xl:flex">

            {{-- ===================================================== --}}
            {{-- SIDEBAR --}}
            {{-- ===================================================== --}}

            <aside class="hidden w-[264px] shrink-0 border-r border-maroon-100/70 bg-white xl:block">

                <div class="sticky top-[76px] flex min-h-[calc(100vh-76px)] flex-col p-4">

                    <nav class="space-y-1" aria-label="Menu akun">

                        @foreach($sideItems as $item)

                            <a
                                href="{{ $item['href'] }}"
                                class="flex items-center gap-3.5 rounded-xl px-4 py-3 text-[14.5px] transition {{ $item['active'] ? 'bg-maroon-50 font-semibold text-maroon-700' : 'text-maroon-800/80 hover:bg-maroon-50/70 hover:text-maroon-700' }}"
                                @if($item['active']) aria-current="page" @endif
                            >
                                <svg class="h-5 w-5 shrink-0 {{ $item['active'] ? 'text-maroon-600' : 'text-maroon-400' }}" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="{{ $item['icon'] }}" />
                                </svg>
                                {{ $item['label'] }}
                            </a>

                        @endforeach

                    </nav>

                    <form method="POST" action="{{ route('logout') }}" class="mt-auto border-t border-maroon-100/70 pt-3">
                        @csrf
                        <button
                            type="submit"
                            class="flex w-full items-center gap-3.5 rounded-xl px-4 py-3 text-left text-[14.5px] text-maroon-800/80 transition hover:bg-maroon-50/70 hover:text-maroon-700"
                        >
                            <svg class="h-5 w-5 shrink-0 text-maroon-400" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="{{ $ico['logout'] }}" />
                            </svg>
                            Keluar
                        </button>
                    </form>

                </div>

            </aside>


            <div class="min-w-0 flex-1 lg:p-6 xl:p-8">


                {{-- ================================================= --}}
                {{-- HERO — MOBILE --}}
                {{-- ================================================= --}}

                <section class="relative overflow-hidden bg-gradient-to-b from-maroon-700 via-maroon-700 to-maroon-600 pb-16 text-white lg:hidden">

                    <div class="pointer-events-none absolute -right-12 top-8 h-52 w-52 rounded-full border border-white/10"></div>
                    <div class="pointer-events-none absolute -right-2 top-20 h-36 w-36 rounded-full border border-white/10"></div>

                    <div class="relative flex items-center gap-2 px-4 pt-[calc(env(safe-area-inset-top,0px)+14px)]">
                        <a
                            href="{{ route('home') }}"
                            class="-ml-1 flex h-9 w-9 items-center justify-center rounded-full text-white transition hover:bg-white/10"
                            aria-label="Kembali ke beranda"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="{{ $ico['back'] }}" />
                            </svg>
                        </a>

                        <p class="text-[17px] font-semibold">Akun Saya</p>
                    </div>

                    <div class="relative flex items-center gap-4 px-4 pt-5">

                        <div class="flex h-[84px] w-[84px] shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-maroon-100 via-maroon-200 to-maroon-300 font-serif text-[34px] text-maroon-700 shadow-lg ring-4 ring-white/90">
                            {{ $userInitial }}
                        </div>

                        <div class="min-w-0">

                            <h2 class="break-words text-[19px] font-bold leading-tight">
                                {{ $user->name }}
                            </h2>

                            <p class="mt-0.5 text-[13px] text-white/80">
                                {{ $isAdminUser ? 'Administrator' : 'Customer' }} Zalina Fashion
                            </p>

                            <span class="mt-2 inline-flex items-center rounded-full bg-maroon-300/60 px-3 py-0.5 text-[11px] font-semibold text-white">
                                {{ $isAdminUser ? 'Admin' : 'Member' }}
                            </span>

                            @if($heroNotice)
                                <p class="mt-2.5 flex items-center gap-2 text-[11.5px] font-medium text-white/90">
                                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-amber-300"></span>
                                    {{ $heroNotice }}
                                </p>
                            @endif

                        </div>

                    </div>

                </section>


                {{-- Aksi cepat (mobile) --}}
                <div class="relative -mt-9 px-4 lg:hidden">

                    <div class="grid grid-cols-4 rounded-3xl bg-white px-2 py-4 shadow-[0_8px_30px_rgba(99,31,43,.10)]">

                        @foreach($quickItems as $item)

                            <a
                                href="{{ $item['href'] }}"
                                class="flex flex-col items-center gap-2 px-1 text-center text-[11.5px] font-medium leading-tight text-maroon-800"
                            >
                                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-maroon-50 text-maroon-600">
                                    <svg class="h-[22px] w-[22px]" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="{{ $item['icon'] }}" />
                                    </svg>
                                </span>
                                {{ $item['label'] }}
                            </a>

                        @endforeach

                        <form method="POST" action="{{ route('logout') }}" class="contents">
                            @csrf
                            <button
                                type="submit"
                                class="flex flex-col items-center gap-2 px-1 text-center text-[11.5px] font-medium leading-tight text-maroon-800"
                            >
                                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-maroon-50 text-maroon-600">
                                    <svg class="h-[22px] w-[22px]" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="{{ $ico['logout'] }}" />
                                    </svg>
                                </span>
                                Keluar
                            </button>
                        </form>

                    </div>

                </div>


                {{-- ================================================= --}}
                {{-- HERO — DESKTOP --}}
                {{-- ================================================= --}}

                <section class="relative hidden overflow-hidden rounded-[22px] bg-gradient-to-br from-maroon-700 via-maroon-600 to-maroon-700 px-10 py-9 text-white shadow-[0_10px_30px_rgba(99,31,43,.18)] lg:block">

                    <div class="pointer-events-none absolute -right-16 -top-24 h-80 w-80 rounded-full bg-white/[0.06]"></div>
                    <div class="pointer-events-none absolute right-40 top-10 h-48 w-48 rounded-full border border-white/10"></div>
                    <div class="pointer-events-none absolute -bottom-24 left-1/3 h-64 w-64 rounded-full bg-black/10"></div>

                    <div class="relative flex items-center gap-8">

                        <div class="flex h-28 w-28 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-maroon-100 via-maroon-200 to-maroon-300 font-serif text-[44px] text-maroon-700 shadow-xl ring-[5px] ring-white/90">
                            {{ $userInitial }}
                        </div>

                        <div class="min-w-0 flex-1">

                            <h1 class="break-words font-serif text-[32px] leading-tight">
                                {{ $user->name }}
                            </h1>

                            <p class="mt-1 text-[15px] text-white/80">
                                {{ $isAdminUser ? 'Administrator' : 'Customer' }} Zalina Fashion
                            </p>

                            <span class="mt-3 inline-flex items-center rounded-full bg-maroon-300/60 px-3.5 py-1 text-[12px] font-semibold text-white">
                                {{ $isAdminUser ? 'Admin' : 'Member' }}
                            </span>

                            @if($joinedShort)
                                <p class="mt-3 text-[13px] text-white/85">
                                    Bergabung sejak {{ $joinedShort }}
                                </p>
                            @endif

                            @if($heroNotice)
                                <p class="mt-3 inline-flex items-center gap-2 rounded-full bg-white/15 px-3.5 py-1.5 text-[12px] font-medium text-white">
                                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-amber-300"></span>
                                    {{ $heroNotice }}
                                </p>
                            @endif

                        </div>

                        @if($isAdminUser)
                            <a
                                href="{{ route('admin.dashboard') }}"
                                class="shrink-0 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-maroon-700 shadow-sm transition hover:bg-maroon-50"
                            >
                                Dashboard Admin
                            </a>
                        @else
                            <a
                                href="{{ route('shop') }}"
                                class="shrink-0 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-maroon-700 shadow-sm transition hover:bg-maroon-50"
                            >
                                Lanjut Belanja
                            </a>
                        @endif

                    </div>

                </section>


        {{-- ========================================================= --}}
        {{-- ADMIN PROFILE --}}
        {{-- ========================================================= --}}

        @if($user->role === 'admin')

            <div class="space-y-8 px-4 pb-6 pt-6 lg:px-0 lg:pb-0">

                {{-- Admin Card --}}
                <div class="bg-white rounded-3xl border border-maroon-100 shadow-sm overflow-hidden">

                    <div class="bg-gradient-to-r from-maroon-800 via-maroon-700 to-maroon-600 px-6 sm:px-8 py-8 sm:py-10 text-white">

                        <div class="flex flex-col sm:flex-row sm:items-center gap-5">

                            <div class="w-20 h-20 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center shrink-0">

                                <svg
                                    class="w-10 h-10"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.7"
                                        d="M12 3l7 4v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V7l7-4Z"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.7"
                                        d="m9.5 12 1.7 1.7 3.5-3.5"
                                    />
                                </svg>

                            </div>

                            <div>

                                <p class="text-xs uppercase tracking-widest text-maroon-200">
                                    Zalina Fashion Administration
                                </p>

                                <h2 class="font-serif text-3xl sm:text-4xl mt-1">
                                    Akun Administrator
                                </h2>

                                <p class="text-sm sm:text-base text-maroon-100 mt-2 max-w-2xl">
                                    Anda sedang login sebagai administrator Zalina Fashion.
                                    Kelola toko, produk, pesanan, pembayaran, promosi,
                                    dan pengaturan sistem melalui Dashboard Admin.
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="p-6 sm:p-8">

                        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">

                            <div class="rounded-2xl bg-maroon-50/60 border border-maroon-100 p-5">

                                <p class="text-xs uppercase tracking-wider text-maroon-400">
                                    Role
                                </p>

                                <p class="font-bold text-maroon-900 mt-2">
                                    Administrator
                                </p>

                            </div>


                            <div class="rounded-2xl bg-maroon-50/60 border border-maroon-100 p-5">

                                <p class="text-xs uppercase tracking-wider text-maroon-400">
                                    Nama
                                </p>

                                <p class="font-bold text-maroon-900 mt-2 break-words">
                                    {{ $user->name }}
                                </p>

                            </div>


                            <div class="rounded-2xl bg-maroon-50/60 border border-maroon-100 p-5">

                                <p class="text-xs uppercase tracking-wider text-maroon-400">
                                    Email
                                </p>

                                <p class="font-bold text-maroon-900 mt-2 break-all">
                                    {{ $user->email }}
                                </p>

                            </div>


                            <div class="rounded-2xl bg-neutral-50 border border-neutral-200 p-5">

                                <p class="text-xs uppercase tracking-wider text-neutral-500">
                                    Status
                                </p>

                                <p class="font-bold text-neutral-700 mt-2">
                                    Aktif
                                </p>

                            </div>

                        </div>


                        <div class="mt-7 flex flex-wrap gap-3">

                            <a
                                href="{{ route('admin.dashboard') }}"
                                class="inline-flex items-center justify-center bg-maroon-700 hover:bg-maroon-800 text-white px-6 py-3 rounded-full font-semibold transition"
                            >
                                Buka Dashboard Admin
                            </a>

                            <a
                                href="{{ route('shop') }}"
                                class="inline-flex items-center justify-center border border-maroon-200 text-maroon-700 hover:bg-maroon-50 px-6 py-3 rounded-full font-semibold transition"
                            >
                                Lihat Toko
                            </a>

                        </div>

                    </div>

                </div>


                {{-- Admin Notice --}}
                <div class="rounded-3xl border border-amber-200 bg-amber-50 p-6">

                    <div class="flex items-start gap-4">

                        <div class="w-11 h-11 rounded-full bg-white flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.108 1.205.166.396.506.71.93.78l.894.15c.542.09.94.559.94 1.109v1.094c0 .55-.398 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.93.78-.164.398-.142.854.108 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.108-.397.166-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.893-.15c-.543-.09-.94-.56-.94-1.11v-1.094c0-.55.398-1.02.94-1.11l.893-.149c.425-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.108.397-.165.71-.505.78-.93l.15-.894Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>
                        </div>

                        <div>

                            <h3 class="font-semibold text-amber-800">
                                Akses Administrator
                            </h3>

                            <p class="text-sm text-amber-700 mt-1 leading-6">
                                Gunakan Dashboard Admin untuk melakukan perubahan pada
                                produk, stok, pesanan, pembayaran, promo, dan data toko.
                            </p>

                        </div>

                    </div>

                </div>

            </div>


        {{-- ========================================================= --}}
        {{-- CUSTOMER PROFILE --}}
        {{-- ========================================================= --}}

        @else

            {{-- ===================================================== --}}
            {{-- KONTEN CUSTOMER --}}
            {{-- ===================================================== --}}

            <div class="space-y-4 px-4 pb-6 pt-4 lg:space-y-6 lg:px-0 lg:pb-0 lg:pt-6">


                {{-- ================================================= --}}
                {{-- RINGKASAN — MOBILE (list) --}}
                {{-- ================================================= --}}

                <section class="rounded-3xl border border-maroon-100/70 bg-white p-4 shadow-[0_2px_12px_rgba(99,31,43,.05)] lg:hidden">

                    <h2 class="mb-1 text-[16px] font-bold text-maroon-800">Ringkasan Akun</h2>

                    <div class="divide-y divide-maroon-50">

                        @foreach($summaryRows as $row)

                            <a href="{{ $row['href'] ?? '#riwayat-pesanan' }}" class="zl-summary-row flex items-center gap-3.5 py-2.5" data-order-filter-link="{{ !empty($row['href']) ? 'retur' : '' }}">

                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-maroon-50 text-maroon-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="{{ $row['icon'] }}" />
                                    </svg>
                                </span>

                                <span class="min-w-0 flex-1">
                                    <span class="block text-[12px] text-maroon-400">{{ $row['label'] }}</span>
                                    <span class="block text-[14.5px] font-semibold text-maroon-900">{{ $row['value'] }} {{ $row['suffix'] ?? 'pesanan' }}</span>
                                </span>

                                <svg class="h-4 w-4 shrink-0 text-maroon-300" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="{{ $ico['chev'] }}" />
                                </svg>

                            </a>

                        @endforeach

                    </div>

                </section>


                {{-- ================================================= --}}
                {{-- INFORMASI PRIBADI — MOBILE --}}
                {{-- ================================================= --}}

                <section class="rounded-3xl border border-maroon-100/70 bg-white p-4 shadow-[0_2px_12px_rgba(99,31,43,.05)] lg:hidden">

                    <h2 class="mb-1 text-[16px] font-bold text-maroon-800">Informasi Pribadi</h2>

                    <div class="divide-y divide-maroon-50">

                        @foreach($accountRows as $row)

                            <div class="flex items-center gap-3.5 py-2.5">

                                <span class="flex h-10 w-10 shrink-0 items-center justify-center text-maroon-500">
                                    <svg class="h-[22px] w-[22px]" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="{{ $row['icon'] }}" />
                                    </svg>
                                </span>

                                <span class="min-w-0 flex-1">
                                    <span class="block text-[12px] text-maroon-400">{{ $row['label'] }}</span>

                                    @if(!empty($row['badge']))
                                        <span class="mt-1 inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[11.5px] font-semibold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            {{ $row['value'] }}
                                        </span>
                                    @else
                                        <span class="block break-all text-[14.5px] font-medium text-maroon-900">{{ $row['value'] }}</span>
                                    @endif
                                </span>

                            </div>

                        @endforeach

                    </div>

                </section>


                {{-- ================================================= --}}
                {{-- STATISTIK — DESKTOP --}}
                {{-- ================================================= --}}

                <section class="hidden grid-cols-2 gap-4 lg:grid {{ count($summaryRows) >= 5 ? 'xl:grid-cols-5' : 'xl:grid-cols-4' }}">

                    @foreach($summaryRows as $row)

                        <a href="{{ $row['href'] ?? '#riwayat-pesanan' }}" class="zl-summary-row flex items-center gap-4 rounded-2xl border border-maroon-100/70 bg-white px-5 py-5 shadow-[0_2px_12px_rgba(99,31,43,.05)] transition hover:border-maroon-200" data-order-filter-link="{{ !empty($row['href']) ? 'retur' : '' }}">

                            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-maroon-50 text-maroon-600">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="{{ $row['icon'] }}" />
                                </svg>
                            </span>

                            <div class="min-w-0">
                                <p class="truncate text-[12.5px] text-maroon-400">{{ $row['label'] }}</p>
                                <p class="mt-0.5 truncate text-[19px] font-bold leading-tight text-maroon-900">{{ $row['value'] }} {{ $row['suffix'] ?? 'pesanan' }}</p>
                            </div>

                        </a>

                    @endforeach

                </section>


                {{-- ================================================= --}}
                {{-- INFORMASI AKUN — DESKTOP --}}
                {{-- ================================================= --}}

                <section class="hidden rounded-[22px] border border-maroon-100/70 bg-white p-8 shadow-[0_2px_12px_rgba(99,31,43,.05)] lg:block">

                    <h2 class="mb-2 text-[17px] font-bold text-maroon-800">Informasi Akun</h2>

                    <div class="grid grid-cols-2 gap-x-12">

                        @foreach($accountRows as $row)

                            <div class="flex items-center gap-4 border-b border-maroon-50 py-4">

                                <svg class="h-5 w-5 shrink-0 text-maroon-400" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="{{ $row['icon'] }}" />
                                </svg>

                                <span class="w-36 shrink-0 text-[14px] text-maroon-500">{{ $row['label'] }}</span>

                                @if(!empty($row['badge']))
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[12px] font-semibold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        {{ $row['value'] }}
                                    </span>
                                @else
                                    <span class="min-w-0 break-all text-[14.5px] font-medium text-maroon-900">{{ $row['value'] }}</span>
                                @endif

                            </div>

                        @endforeach

                    </div>

                </section>


                {{-- ================================================= --}}
                {{-- RIWAYAT PESANAN --}}
                {{-- ================================================= --}}

                <section id="riwayat-pesanan" class="scroll-mt-24">

                    <div class="lg:rounded-[22px] lg:border lg:border-maroon-100/70 lg:bg-white lg:p-8 lg:shadow-[0_2px_12px_rgba(99,31,43,.05)]">

                        <div class="mb-4 flex items-center justify-between gap-3 lg:mb-5">

                            <h2 class="text-[18px] font-bold text-maroon-900 lg:text-[20px]">
                                Riwayat Pesanan
                            </h2>

                            <a
                                href="{{ route('shop') }}"
                                class="inline-flex shrink-0 items-center gap-2 rounded-full border border-maroon-200 bg-white px-4 py-2 text-[12.5px] font-semibold text-maroon-700 transition hover:bg-maroon-50"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="{{ $ico['store'] }}" />
                                </svg>
                                Belanja Lagi
                            </a>

                        </div>


                        {{-- TAB FILTER STATUS (tampilan saja, data tetap sama) --}}
                        @if($totalOrderCount > 0)

                            <div
                                id="zalinaOrderTabs"
                                class="zl-order-tabs no-scrollbar -mx-1 mb-4 gap-1.5 px-1 sm:gap-2 lg:mb-5"
                            >
                                <button type="button" data-order-filter="all" class="zalina-order-tab shrink-0 rounded-full border px-3 py-1.5 text-[11.5px] font-semibold transition sm:px-4 sm:py-2 sm:text-[12.5px] lg:px-6 lg:py-2.5 lg:text-[13px]">Semua</button>
                                <button type="button" data-order-filter="processing" class="zalina-order-tab shrink-0 rounded-full border px-3 py-1.5 text-[11.5px] font-semibold transition sm:px-4 sm:py-2 sm:text-[12.5px] lg:px-6 lg:py-2.5 lg:text-[13px]">Diproses</button>
                                <button type="button" data-order-filter="shipped" class="zalina-order-tab shrink-0 rounded-full border px-3 py-1.5 text-[11.5px] font-semibold transition sm:px-4 sm:py-2 sm:text-[12.5px] lg:px-6 lg:py-2.5 lg:text-[13px]">Dikirim</button>
                                <button type="button" data-order-filter="finished" class="zalina-order-tab shrink-0 rounded-full border px-3 py-1.5 text-[11.5px] font-semibold transition sm:px-4 sm:py-2 sm:text-[12.5px] lg:px-6 lg:py-2.5 lg:text-[13px]">Selesai</button>
                                <button type="button" data-order-filter="cancelled" class="zalina-order-tab shrink-0 rounded-full border px-3 py-1.5 text-[11.5px] font-semibold transition sm:px-4 sm:py-2 sm:text-[12.5px] lg:px-6 lg:py-2.5 lg:text-[13px]">Dibatalkan</button>
                                @if($returnOrderCount > 0)
                                    <button type="button" data-order-filter="retur" class="zalina-order-tab zalina-order-tab-retur shrink-0 rounded-full border px-3 py-1.5 text-[11.5px] font-semibold transition sm:px-4 sm:py-2 sm:text-[12.5px] lg:px-6 lg:py-2.5 lg:text-[13px]">
                                        Retur
                                        <span class="zl-tab-badge">{{ $returnOrderCount }}</span>
                                    </button>
                                @endif
                            </div>

                        @endif


                        @php
                            /*
                            | Data pesanan sudah diambil sekali di atas ($profileOrders)
                            | lengkap dengan eager loading — tidak ada query kedua.
                            */
                            $orders = $profileOrders;
                        @endphp


                        {{-- Tabel (desktop) / daftar kartu (mobile) --}}
                        <div class="{{ $totalOrderCount > 0 ? 'lg:overflow-hidden lg:rounded-2xl lg:border lg:border-maroon-100/80' : '' }}">

                            @if($totalOrderCount > 0)

                                <div class="zl-cols hidden bg-[#fbf3f2] px-6 py-3.5 text-[12.5px] font-semibold text-maroon-700/80 lg:grid">
                                    <span>No. Pesanan</span>
                                    <span>Tanggal</span>
                                    <span>Produk</span>
                                    <span>Total</span>
                                    <span>Status</span>
                                    <span class="text-center">Aksi</span>
                                </div>

                            @endif


                            {{-- ================================================= --}}
                            {{-- ORDER LIST --}}
                            {{-- ================================================= --}}
                @forelse($orders as $order)

                    @php

                        $payment = $order->payment;

                        /*
                        |--------------------------------------------------------------------------
                        | SHIPPING STATUS
                        |--------------------------------------------------------------------------
                        */

                        /*
                        | Pakai `?:` bukan `??` — kolom shipping_status bisa berisi
                        | string kosong, bukan hanya NULL, dan string kosong tidak
                        | tertangkap oleh `??` sehingga label status jadi tidak akurat.
                        */

                        $shippingStatus = $order->shipping_status ?: 'waiting';


                        $shippingMap = [

                            'waiting' => [
                                'step' => 0,
                                'label' => 'Menunggu diproses',
                                'description' => 'Pesanan Anda menunggu proses dari Zalina Fashion.',
                            ],

                            'packing' => [
                                'step' => 1,
                                'label' => 'Sedang dikemas',
                                'description' => 'Pesanan sedang disiapkan dan dikemas.',
                            ],

                            'packed' => [
                                'step' => 2,
                                'label' => 'Pesanan selesai dikemas',
                                'description' => 'Pesanan telah selesai dikemas dan siap diserahkan.',
                            ],

                            'handed_to_courier' => [
                                'step' => 3,
                                'label' => 'Diserahkan ke kurir',
                                'description' => 'Pesanan telah diserahkan kepada pihak kurir.',
                            ],

                            'shipped' => [
                                'step' => 4,
                                'label' => 'Sedang dikirim',
                                'description' => 'Paket sedang dalam perjalanan menuju alamat Anda.',
                            ],

                            'delivered' => [
                                'step' => 5,
                                'label' => 'Barang sudah diterima',
                                'description' => 'Pesanan telah diterima oleh customer.',
                            ],

                        ];


                        $currentStep =
                            $shippingMap[$shippingStatus]['step']
                            ?? 0;


                        $shippingLabel =
                            $shippingMap[$shippingStatus]['label']
                            ?? 'Menunggu diproses';


                        $shippingDescription =
                            $shippingMap[$shippingStatus]['description']
                            ?? 'Pesanan Anda sedang diproses.';


                        /*
                        |--------------------------------------------------------------------------
                        | STATUS PEMBAYARAN
                        |--------------------------------------------------------------------------
                        |
                        | Sebelumnya kartu pembayaran hanya mengenali status 'verified',
                        | padahal perhitungan badge di atas menganggap 'paid' dan
                        | 'confirmed' juga sudah lunas. Akibatnya pesanan berstatus
                        | 'paid' tetap ditulis "Menunggu Pembayaran" di kartunya
                        | walaupun sudah terhitung lunas di badge.
                        |
                        */

                        $paymentStatusValue = strtolower(
                            trim((string) optional($payment)->status)
                        );

                        $paymentIsPaid = in_array(
                            $paymentStatusValue,
                            ['verified', 'paid', 'confirmed'],
                            true
                        );

                        $orderStatusValue = strtolower(
                            trim((string) $order->status)
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | STATUS RINGKAS UNTUK HEADER KARTU
                        |--------------------------------------------------------------------------
                        */

                        if (in_array($orderStatusValue, ['cancelled', 'canceled', 'rejected', 'failed'], true)) {
                            $chipLabel = 'Dibatalkan';
                            $chipClass = 'bg-maroon-50 text-maroon-700 border-maroon-200';
                        } elseif (in_array($orderStatusValue, ['completed', 'complete', 'delivered', 'finished', 'success'], true)) {
                            $chipLabel = 'Selesai';
                            $chipClass = 'bg-neutral-50 text-neutral-700 border-neutral-200';
                        } elseif ($paymentStatusValue === 'rejected') {
                            $chipLabel = 'Pembayaran Ditolak';
                            $chipClass = 'bg-maroon-50 text-maroon-700 border-maroon-200';
                        } elseif (in_array($paymentStatusValue, ['under_review', 'pending_verification'], true)) {
                            $chipLabel = 'Menunggu Verifikasi';
                            $chipClass = 'bg-amber-50 text-amber-700 border-amber-200';
                        } elseif (!$paymentIsPaid) {
                            $chipLabel = 'Belum Dibayar';
                            $chipClass = 'bg-maroon-50 text-maroon-700 border-maroon-200';
                        } else {
                            $chipLabel = $shippingLabel;
                            $chipClass = 'bg-amber-50 text-amber-700 border-amber-200';
                        }


                        /*
                        | Butuh perhatian user -> kartu dibuka otomatis.
                        */

                        $needsAttention =
                            !$paymentIsPaid
                            && !in_array($orderStatusValue, ['cancelled', 'canceled', 'rejected', 'failed'], true)
                            && !in_array($orderStatusValue, ['completed', 'complete', 'delivered', 'finished', 'success'], true);


                        $previewItems = $order->items->take(3);

                        $extraItemCount = max(0, $order->items->count() - $previewItems->count());


                        /*
                        |--------------------------------------------------------------------------
                        | GRUP UNTUK TAB FILTER (tampilan saja)
                        |--------------------------------------------------------------------------
                        */

                        if (in_array($orderStatusValue, ['cancelled', 'canceled', 'rejected', 'failed'], true)) {
                            $orderFilterGroup = 'cancelled';
                        } elseif (in_array($orderStatusValue, ['completed', 'complete', 'delivered', 'finished', 'success'], true)) {
                            $orderFilterGroup = 'finished';
                        } elseif ($shippingStatus === 'shipped' || $orderStatusValue === 'shipped') {
                            $orderFilterGroup = 'shipped';
                        } else {
                            $orderFilterGroup = 'processing';
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | RETUR — order tetap tampil di tab aslinya (mis. "Selesai"), DAN juga
                        | ikut muncul saat tab "Retur" dipilih. Makanya grup ditulis dua kata,
                        | dipisah spasi, lalu dicocokkan lewat .split(' ') di JS.
                        |--------------------------------------------------------------------------
                        */

                        $returnStatusValue = strtolower(trim((string) ($order->return_status ?? '')));
                        $hasActiveReturn   = $returnStatusValue !== '' && $returnStatusValue !== 'none';

                        $returnBadgeLabel = match ($returnStatusValue) {
                            'requested' => 'Retur Diajukan',
                            'approved'  => 'Retur Disetujui',
                            'rejected'  => 'Retur Ditolak',
                            'completed' => 'Retur Selesai',
                            default     => 'Retur',
                        };

                        $orderFilterGroupAttr = $hasActiveReturn
                            ? $orderFilterGroup . ' retur'
                            : $orderFilterGroup;

                    @endphp

                    {{-- ================================================= --}}
                    {{-- ORDER CARD --}}
                    {{-- ================================================= --}}

                    @php
                        /*
                        | Warna status mengikuti mockup: hijau = selesai, biru = dikirim,
                        | oranye = diproses / menunggu, merah = dibatalkan / ditolak.
                        | Label & kelompok filter tetap memakai logika di atas.
                        */
                        if ($orderFilterGroup === 'cancelled' || $paymentStatusValue === 'rejected') {
                            $chipTone = 'bg-rose-50 text-rose-600';
                            $chipDot  = 'bg-rose-500';
                        } elseif ($orderFilterGroup === 'finished') {
                            $chipTone = 'bg-emerald-50 text-emerald-600';
                            $chipDot  = 'bg-emerald-500';
                        } elseif ($orderFilterGroup === 'shipped') {
                            $chipTone = 'bg-sky-50 text-sky-600';
                            $chipDot  = 'bg-sky-500';
                        } else {
                            $chipTone = 'bg-orange-50 text-orange-600';
                            $chipDot  = 'bg-orange-500';
                        }

                        $orderNumberLabel = $order->order_number ?? ('Invoice #' . $order->id);
                        $firstItem = $order->items->first();
                        $firstItemName = $firstItem?->product_name ?? $firstItem?->product?->name ?? 'Produk';
                        $thumbImage = $firstItem?->product?->image ?? $firstItem?->product?->image_path ?? null;
                        $orderTotalLabel = 'Rp ' . number_format($order->grand_total ?? 0, 0, ',', '.');
                    @endphp

                    <details
                        class="zalina-order group relative mb-3 overflow-hidden rounded-2xl border border-maroon-100/80 bg-white shadow-[0_2px_10px_rgba(99,31,43,.05)] lg:mb-0 lg:rounded-none lg:border-0 lg:border-b lg:border-maroon-100/70 lg:shadow-none lg:last:border-b-0"
                        data-order-group="{{ $orderFilterGroupAttr }}"
                        @if($needsAttention) open @endif
                    >

                        {{-- ================================================= --}}
                        {{-- SUMMARY (SELALU TERLIHAT) --}}
                        {{-- ================================================= --}}

                        <summary class="px-4 py-4 transition hover:bg-maroon-50/30 lg:px-6">

                            {{-- MOBILE --}}
                            <div class="lg:hidden">

                                <div class="flex items-start justify-between gap-3">

                                    <div class="min-w-0">
                                        <p class="break-all text-[13px] font-bold leading-tight text-maroon-900">{{ $orderNumberLabel }}</p>
                                        <p class="mt-0.5 text-[11px] text-maroon-400">{{ $order->created_at?->translatedFormat('d M Y, H:i') }}</p>
                                    </div>

                                    <span class="inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-md px-2 py-1 text-[10.5px] font-semibold {{ $chipTone }}">
                                        <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $chipDot }}"></span>
                                        {{ $chipLabel }}
                                    </span>

                                    @if($hasActiveReturn)
                                        <span class="inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-md border border-amber-200 bg-amber-50 px-2 py-1 text-[10.5px] font-semibold text-amber-700">
                                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-amber-500"></span>
                                            {{ $returnBadgeLabel }}
                                        </span>
                                    @endif

                                </div>

                                <div class="mt-3 flex items-center gap-3">

                                    <div class="relative h-16 w-16 shrink-0 overflow-hidden rounded-xl border border-maroon-100 bg-maroon-50">
                                        @if($thumbImage)
                                            <img
                                                src="{{ asset('storage/' . ltrim($thumbImage, '/')) }}"
                                                alt="{{ $firstItemName }}"
                                                class="h-full w-full object-cover"
                                                loading="lazy"
                                            >
                                        @else
                                            <div class="flex h-full w-full items-center justify-center">
                                                <svg class="h-6 w-6 text-maroon-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3.75 6.75h16.5M3.75 6.75a1.5 1.5 0 0 1 1.5-1.5h13.5a1.5 1.5 0 0 1 1.5 1.5M3.75 6.75v10.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5V6.75M8.25 10.5a3.75 3.75 0 0 0 7.5 0"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-[13.5px] font-medium text-maroon-900">{{ $firstItemName }}</p>
                                        <p class="mt-0.5 text-[11.5px] text-maroon-400">{{ $order->items->count() }} Produk</p>
                                        <p class="mt-1 text-[14.5px] font-bold text-maroon-900">{{ $orderTotalLabel }}</p>
                                    </div>

                                    <svg class="zalina-order-chevron h-5 w-5 shrink-0 text-maroon-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="m6 9 6 6 6-6" />
                                    </svg>

                                </div>

                            </div>


                            {{-- DESKTOP (baris tabel) --}}
                            <div class="zl-cols hidden lg:grid">

                                <p class="whitespace-nowrap text-[13px] font-medium text-maroon-900">{{ $orderNumberLabel }}</p>

                                <p class="whitespace-nowrap text-[13px] text-maroon-700/80">{{ $order->created_at?->translatedFormat('d M Y, H:i') }}</p>

                                <div class="flex min-w-0 items-center gap-3">

                                    <div class="relative h-12 w-12 shrink-0 overflow-hidden rounded-lg border border-maroon-100 bg-maroon-50">
                                        @if($thumbImage)
                                            <img
                                                src="{{ asset('storage/' . ltrim($thumbImage, '/')) }}"
                                                alt="{{ $firstItemName }}"
                                                class="h-full w-full object-cover"
                                                loading="lazy"
                                            >
                                        @else
                                            <div class="flex h-full w-full items-center justify-center">
                                                <svg class="h-5 w-5 text-maroon-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3.75 6.75h16.5M3.75 6.75a1.5 1.5 0 0 1 1.5-1.5h13.5a1.5 1.5 0 0 1 1.5 1.5M3.75 6.75v10.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5V6.75M8.25 10.5a3.75 3.75 0 0 0 7.5 0"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="min-w-0">
                                        <p class="truncate text-[13px] font-medium text-maroon-900">{{ $firstItemName }}</p>
                                        <p class="text-[12px] text-maroon-400">{{ $order->items->count() }} Produk</p>
                                    </div>

                                </div>

                                <p class="whitespace-nowrap text-[13px] font-semibold text-maroon-900">{{ $orderTotalLabel }}</p>

                                <div>
                                    <span class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-[11.5px] font-semibold leading-tight {{ $chipTone }}">
                                        <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $chipDot }}"></span>
                                        {{ $chipLabel }}
                                    </span>

                                    @if($hasActiveReturn)
                                        <span class="inline-flex items-center gap-1.5 rounded-md border border-amber-200 bg-amber-50 px-2.5 py-1 text-[11.5px] font-semibold leading-tight text-amber-700">
                                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-amber-500"></span>
                                            {{ $returnBadgeLabel }}
                                        </span>
                                    @endif
                                </div>

                                <div>
                                    <span class="inline-flex w-full items-center justify-center rounded-lg bg-maroon-700 px-4 py-2.5 text-[12.5px] font-semibold text-white transition group-hover:bg-maroon-800">
                                        <span class="group-open:hidden">Lihat Detail</span>
                                        <span class="hidden group-open:inline">Tutup Detail</span>
                                    </span>
                                </div>

                            </div>

                        </summary>


                        <div class="zalina-order-body">



                        {{-- ================================================= --}}
                        {{-- PRODUCT + PAYMENT --}}
                        {{-- ================================================= --}}

                        <div class="grid grid-cols-1 xl:grid-cols-3">


                            {{-- ================================================= --}}
                            {{-- PRODUCT --}}
                            {{-- ================================================= --}}

                            <div class="xl:col-span-2 p-3 sm:p-4 border-b xl:border-b-0 xl:border-r border-maroon-100">

                                <div class="flex items-center justify-between gap-4 mb-5">

                                    <div>

                                        <h3 class="text-base sm:text-lg font-bold text-maroon-800">
                                            Produk Pesanan
                                        </h3>

                                    </div>


                                    <span class="text-xs bg-maroon-50 text-maroon-700 px-3 py-1.5 rounded-full whitespace-nowrap">

                                        {{ $order->items->count() }}
                                        Produk

                                    </span>

                                </div>


                                <div class="space-y-3">

                                    @forelse($order->items as $item)

                                        @php

                                            $product = $item->product;

                                            $variant = $item->variant;

                                            $image =
                                                $product?->image
                                                ?? $product?->image_path
                                                ?? null;

                                        @endphp


                                        <div class="flex gap-4 p-4 rounded-2xl bg-maroon-50/30 border border-maroon-100">


                                            {{-- Product Image --}}
                                            <div class="w-20 h-20 sm:w-24 sm:h-24 shrink-0 rounded-xl overflow-hidden bg-white border border-maroon-100">

                                                @if($image)

                                                    <img
                                                        src="{{ asset('storage/' . ltrim($image, '/')) }}"
                                                        alt="{{ $item->product_name ?? $product?->name ?? 'Produk Zalina Fashion' }}"
                                                        class="w-full h-full object-cover"
                                                        loading="lazy"
                                                    >

                                                @else

                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <svg class="w-7 h-7 text-maroon-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3.75 6.75h16.5M3.75 6.75a1.5 1.5 0 0 1 1.5-1.5h13.5a1.5 1.5 0 0 1 1.5 1.5M3.75 6.75v10.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5V6.75M8.25 10.5a3.75 3.75 0 0 0 7.5 0"/>
                                                        </svg>
                                                    </div>

                                                @endif

                                            </div>


                                            {{-- Product Information --}}
                                            <div class="flex-1 min-w-0">

                                                <div class="font-bold text-maroon-900 break-words">

                                                    {{ $item->product_name ?? $product?->name ?? 'Produk' }}

                                                </div>


                                                @if($variant)

                                                    <div class="text-xs text-maroon-500 mt-1">

                                                        Varian:
                                                        <span class="font-medium">
                                                            {{ $variant->name ?? '-' }}
                                                        </span>

                                                        @if(!empty($variant->color))

                                                            <span class="mx-1">
                                                                •
                                                            </span>

                                                            Warna:
                                                            <span class="font-medium">
                                                                {{ $variant->color }}
                                                            </span>

                                                        @endif

                                                    </div>

                                                @endif


                                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end gap-2 mt-4">

                                                    <div class="text-xs sm:text-sm text-maroon-500">

                                                        {{ $item->quantity }}
                                                        ×
                                                        Rp
                                                        {{ number_format($item->price, 0, ',', '.') }}

                                                    </div>


                                                    <div class="font-bold text-maroon-800">

                                                        Rp
                                                        {{ number_format($item->subtotal, 0, ',', '.') }}

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    @empty

                                        <div class="text-center py-8 text-sm text-maroon-400">
                                            Produk tidak ditemukan.
                                        </div>

                                    @endforelse

                                </div>

                            </div>



                            {{-- ================================================= --}}
                            {{-- PAYMENT --}}
                            {{-- ================================================= --}}

                            <div class="p-3 sm:p-4 bg-maroon-50/30">

                                <h3 class="text-base sm:text-lg font-bold text-maroon-800 mb-5">
                                    Pembayaran
                                </h3>


                                {{-- Verified / Paid / Confirmed --}}
                                @if($paymentIsPaid)

                                    <div class="bg-neutral-50 border border-neutral-200 rounded-2xl p-4">

                                        <div class="text-sm font-bold text-neutral-700">
                                            ✓ Pembayaran Dikonfirmasi
                                        </div>

                                        @if($payment->method)

                                            <div class="text-xs text-neutral-600 mt-2">
                                                {{ $payment->method->name }}
                                            </div>

                                        @endif

                                    </div>


                                {{-- Under Review --}}
                                @elseif(in_array($paymentStatusValue, ['under_review', 'pending_verification'], true))

                                    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">

                                        <div class="text-sm font-bold text-amber-700">
                                            ⏳ Menunggu Approval Admin
                                        </div>

                                        <div class="text-xs text-amber-600 mt-2">
                                            Bukti pembayaran sedang diperiksa oleh admin.
                                        </div>

                                    </div>


                                {{-- Rejected --}}
                                @elseif($paymentStatusValue === 'rejected')

                                    <div class="bg-maroon-50 border border-maroon-200 rounded-2xl p-4">

                                        <div class="text-sm font-bold text-maroon-800">
                                            ✕ Pembayaran Ditolak
                                        </div>

                                        @if($payment->rejection_reason)

                                            <div class="text-xs text-maroon-700 mt-2 leading-5">
                                                {{ $payment->rejection_reason }}
                                            </div>

                                        @endif

                                    </div>


                                {{-- Waiting --}}
                                @else

                                    <div class="bg-maroon-50 border border-maroon-100 rounded-2xl p-4">

                                        <div class="text-sm font-bold text-maroon-700">
                                            Menunggu Pembayaran
                                        </div>

                                        <div class="text-xs text-maroon-500 mt-2 leading-5">
                                            Silakan lakukan pembayaran untuk melanjutkan proses pesanan.
                                        </div>

                                    </div>

                                @endif



                                {{-- ================================================= --}}
                                {{-- PAYMENT METHOD --}}
                                {{-- ================================================= --}}

                                @if($payment && $payment->method)

                                    <div class="mt-5 rounded-2xl bg-white border border-maroon-100 p-4">

                                        <div class="text-[11px] uppercase tracking-wider text-maroon-400">
                                            Metode Pembayaran
                                        </div>

                                        <div class="font-bold text-maroon-900 mt-2">
                                            {{ $payment->method->name }}
                                        </div>

                                        @if($payment->method->account_name)

                                            <div class="text-sm text-maroon-600 mt-1">
                                                {{ $payment->method->account_name }}
                                            </div>

                                        @endif

                                        @if($payment->method->account_number)

                                            <div class="font-semibold text-maroon-800 mt-2 break-all">
                                                {{ $payment->method->account_number }}
                                            </div>

                                        @endif

                                    </div>

                                @endif



                                {{-- ================================================= --}}
                                {{-- ORDER ACTIONS --}}
                                {{-- ================================================= --}}

                                <div class="mt-5 space-y-3">

                                    <a
                                        href="{{ route('payment.show', $order) }}"
                                        class="w-full flex items-center justify-center gap-2 border border-maroon-200 text-maroon-800 px-4 py-3 rounded-xl text-sm font-semibold hover:bg-maroon-50 transition"
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
                                                stroke-width="1.8"
                                                d="M9 5h6M9 9h6M9 13h4m-7 8h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2Z"
                                            />
                                        </svg>

                                        Lihat Detail Pesanan

                                    </a>


                                    @if($paymentIsPaid)

                                        <a
                                            href="{{ route('receipt.show', $order) }}"
                                            class="w-full flex items-center justify-center gap-2 bg-maroon-700 hover:bg-maroon-800 text-white px-4 py-3 rounded-xl text-sm font-semibold transition"
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
                                                    stroke-width="1.8"
                                                    d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"
                                                />
                                            </svg>

                                            Lihat Receipt

                                        </a>

                                    @endif

                                </div>

                            </div>

                        </div>



                        {{-- ================================================= --}}
                        {{-- SHIPPING TRACKING --}}
                        {{-- ================================================= --}}

                        <div class="border-t border-maroon-100 p-3 sm:p-4 lg:p-6 bg-white">

                            <div class="flex items-center justify-between gap-3 mb-5">

                                <h3 class="text-base sm:text-lg font-bold text-maroon-800">
                                    Status Pengiriman
                                </h3>

                                <div class="px-3 py-1.5 bg-maroon-50 rounded-full text-[11px] font-semibold text-maroon-700 whitespace-nowrap shrink-0">

                                    @if($currentStep >= 5)
                                        Selesai
                                    @else
                                        Langkah {{ $currentStep }}/5
                                    @endif

                                </div>

                            </div>



                            {{-- ================================================= --}}
                            {{-- SHIPPING STEPPER (Responsive) --}}
                            {{-- ================================================= --}}

                            @php

                                $steps = [

                                    [
                                        'step' => 1,
                                        'title' => 'Dikemas',
                                        'icon' => '📦',
                                    ],

                                    [
                                        'step' => 2,
                                        'title' => 'Selesai',
                                        'icon' => '✓',
                                    ],

                                    [
                                        'step' => 3,
                                        'title' => 'Ke Kurir',
                                        'icon' => '🚚',
                                    ],

                                    [
                                        'step' => 4,
                                        'title' => 'Dikirim',
                                        'icon' => '🛣',
                                    ],

                                    [
                                        'step' => 5,
                                        'title' => 'Diterima',
                                        'icon' => '🏠',
                                    ],

                                ];

                            @endphp


                            <div class="mb-6">

                                {{-- ============================================= --}}
                                {{-- STEPPER HORIZONTAL (SEMUA UKURAN LAYAR) --}}
                                {{-- ============================================= --}}
                                {{--
                                | Sebelumnya di mobile dipakai stepper VERTIKAL yang
                                | memakan ruang sangat tinggi (5 langkah bertumpuk ke
                                | bawah untuk SETIAP pesanan). Sekarang memakai satu
                                | stepper horizontal ringkas yang tetap terbaca di
                                | layar kecil, sehingga riwayat pesanan jauh lebih pendek.
                                --}}

                                <div class="flex items-start">

                                    @foreach($steps as $index => $step)

                                        @php

                                            $stepNumber = $step['step'];

                                            $completed =
                                                $stepNumber <= $currentStep;

                                            $isCurrent =
                                                $stepNumber === $currentStep;

                                            $isLast =
                                                $index === count($steps) - 1;

                                            $connectorFilled =
                                                $currentStep > $stepNumber;

                                        @endphp


                                        <div class="flex flex-col items-center {{ $isLast ? 'shrink-0' : 'flex-1' }}">

                                            <div class="flex items-center w-full">

                                                <div
                                                    class="
                                                    shrink-0
                                                    w-8
                                                    h-8
                                                    sm:w-10
                                                    sm:h-10
                                                    rounded-full
                                                    flex
                                                    items-center
                                                    justify-center
                                                    font-bold
                                                    text-xs
                                                    sm:text-sm
                                                    transition-all
                                                    duration-300

                                                    {{ $completed
                                                        ? ($stepNumber === 5 && $currentStep >= 5
                                                            ? 'bg-maroon-900 text-white shadow-lg shadow-maroon-100'
                                                            : 'bg-maroon-700 text-white shadow-lg shadow-maroon-100')
                                                        : 'bg-white border-2 border-maroon-100 text-maroon-300'
                                                    }}

                                                    {{ $isCurrent
                                                        ? 'ring-4 ring-maroon-100 scale-110'
                                                        : ''
                                                    }}
                                                    "
                                                >

                                                    @if($stepNumber === 5 && $currentStep >= 5)

                                                        ✓

                                                    @else

                                                        {{ $step['icon'] }}

                                                    @endif

                                                </div>


                                                @unless($isLast)

                                                    <div
                                                        class="
                                                        flex-1
                                                        h-1
                                                        mx-1
                                                        rounded-full
                                                        transition-all
                                                        duration-700

                                                        {{ $connectorFilled
                                                            ? 'bg-gradient-to-r from-maroon-700 to-maroon-500'
                                                            : 'bg-maroon-100'
                                                        }}
                                                        "
                                                    >
                                                    </div>

                                                @endunless

                                            </div>


                                            <div
                                                class="
                                                mt-2
                                                text-center
                                                text-[9px]
                                                sm:text-[11px]
                                                lg:text-xs
                                                font-semibold
                                                leading-tight
                                                px-0.5

                                                {{ $completed
                                                    ? 'text-maroon-800'
                                                    : 'text-maroon-300'
                                                }}
                                                "
                                            >

                                                {{ $step['title'] }}

                                            </div>

                                        </div>

                                    @endforeach

                                </div>


                            </div>



                            {{-- ================================================= --}}
                            {{-- SHIPPING INFO --}}
                            {{-- ================================================= --}}

                            <div class="rounded-2xl bg-maroon-50/50 border border-maroon-100 p-4 sm:p-5">

                                <div class="flex items-start gap-3">

                                    <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shrink-0">
                                        🚚
                                    </div>

                                    <div>

                                        <div class="font-semibold text-maroon-900">
                                            {{ $shippingLabel }}
                                        </div>

                                        <div class="text-sm text-maroon-500 mt-1 leading-5">
                                            {{ $shippingDescription }}
                                        </div>

                                    </div>

                                </div>

                            </div>



                            {{-- ================================================= --}}
                            {{-- DETAIL RESI (tampil setelah admin menekan "Cetak & Kirim Resi") --}}
                            {{-- ================================================= --}}

                            @php
                                $resiTracking = trim((string) $order->tracking_number);

                                $resiVisible = $resiTracking !== ''
                                    && filled($order->tracking_sent_at ?? null)
                                    && !in_array($orderStatusValue, ['cancelled', 'canceled', 'rejected', 'failed'], true);

                                if ($resiVisible) {
                                    $resiWeight = (int) ($order->shipping_weight ?? 0);

                                    if ($resiWeight <= 0 && method_exists($order, 'getCalculatedShippingWeight')) {
                                        $resiWeight = (int) $order->getCalculatedShippingWeight();
                                    }

                                    $resiSentAt = \Illuminate\Support\Carbon::parse($order->tracking_sent_at)->translatedFormat('d M Y, H:i');
                                    $resiQty = (int) $order->items->sum('quantity');

                                    $resiRows = [
                                        'Kurir'       => $order->shipping_courier ? strtoupper($order->shipping_courier) : '-',
                                        'Layanan'     => $order->shipping_service ?: '-',
                                        'Estimasi'    => $order->shipping_etd ?: '-',
                                        'Berat'       => $resiWeight > 0 ? number_format($resiWeight, 0, ',', '.') . ' gram' : '-',
                                        'Jumlah'      => $resiQty . ' item',
                                        'Resi dikirim' => $resiSentAt,
                                    ];
                                }
                            @endphp

                            @if($resiVisible)

                                <div class="mt-5 overflow-hidden rounded-2xl border border-maroon-100 bg-white">

                                    <div class="flex items-center justify-between gap-3 border-b border-maroon-100 bg-maroon-50/60 px-4 py-3 sm:px-5">

                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <svg class="h-5 w-5 shrink-0 text-maroon-600" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M21 7.5v9L12 21l-9-4.5v-9L12 3l9 4.5ZM12 12l9-4.5M12 12v9M12 12 3 7.5" />
                                            </svg>
                                            <h4 class="truncate text-sm font-bold text-maroon-800 sm:text-base">Detail Resi Pengiriman</h4>
                                        </div>

                                        <span class="shrink-0 rounded-md bg-white px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-maroon-700 border border-maroon-100">
                                            {{ $order->shipping_courier ?: 'Kurir' }}
                                        </span>

                                    </div>

                                    <div class="grid gap-5 p-4 sm:p-5 md:grid-cols-2">

                                        <div class="min-w-0">

                                            <p class="text-[11px] font-semibold uppercase tracking-wider text-maroon-400">
                                                Nomor Resi / AWB
                                            </p>

                                            <p class="mt-1 break-all font-mono text-xl font-extrabold tracking-wide text-maroon-900">
                                                {{ $resiTracking }}
                                            </p>

                                            <div class="mt-3 rounded-xl border border-maroon-100 bg-white p-3">
                                                <svg
                                                    class="block h-16 w-full"
                                                    role="img"
                                                    aria-label="Barcode resi {{ $resiTracking }}"
                                                    data-zl-barcode="{{ $resiTracking }}"
                                                ></svg>
                                            </div>

                                            <p class="mt-2 text-[11.5px] text-maroon-400">
                                                No. Pesanan {{ $order->order_number ?? ('#' . $order->id) }}
                                            </p>

                                        </div>

                                        <div class="min-w-0">

                                            <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                                                @foreach($resiRows as $resiLabel => $resiValue)
                                                    <div class="min-w-0">
                                                        <dt class="text-[11px] uppercase tracking-wider text-maroon-400">{{ $resiLabel }}</dt>
                                                        <dd class="mt-0.5 break-words font-semibold text-maroon-900">{{ $resiValue }}</dd>
                                                    </div>
                                                @endforeach
                                            </dl>

                                            <div class="mt-4 rounded-xl bg-maroon-50/50 p-3 text-sm">
                                                <p class="text-[11px] uppercase tracking-wider text-maroon-400">Penerima</p>
                                                <p class="mt-0.5 font-semibold text-maroon-900">
                                                    {{ $order->customer_name ?: '-' }}
                                                    @if($order->customer_phone)
                                                        <span class="font-normal text-maroon-500">· {{ $order->customer_phone }}</span>
                                                    @endif
                                                </p>
                                                @if($order->shipping_address)
                                                    <p class="mt-1 whitespace-pre-line break-words text-[13px] leading-5 text-maroon-600">{{ $order->shipping_address }}</p>
                                                @endif
                                                @if($order->destination_name)
                                                    <p class="mt-1 text-[12px] font-semibold text-maroon-500">Tujuan: {{ $order->destination_name }}</p>
                                                @endif
                                            </div>

                                        </div>

                                    </div>

                                    @if($order->items->count() > 0)

                                        <div class="border-t border-maroon-100 px-4 py-4 sm:px-5">

                                            <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-maroon-400">Isi Paket</p>

                                            <ul class="divide-y divide-dashed divide-maroon-100">
                                                @foreach($order->items as $resiItem)
                                                    <li class="flex items-start justify-between gap-3 py-2 text-sm">
                                                        <span class="min-w-0 break-words text-maroon-800">
                                                            {{ $resiItem->product_name ?? $resiItem->product?->name ?? 'Produk' }}
                                                            @if($resiItem->variant)
                                                                <span class="block text-xs text-maroon-400">Varian: {{ $resiItem->variant->name ?? '-' }}@if(!empty($resiItem->variant->color)) · {{ $resiItem->variant->color }}@endif</span>
                                                            @endif
                                                        </span>
                                                        <span class="shrink-0 font-bold text-maroon-700">× {{ $resiItem->quantity }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>

                                        </div>

                                    @endif

                                </div>

                            @endif

                            {{-- ================================================= --}}
                            {{-- CONFIRM DELIVERED --}}
                            {{-- ================================================= --}}

                            @if($order->status === 'shipped')

                                <div class="mt-6 bg-neutral-50 border border-neutral-200 rounded-2xl p-4 sm:p-5">

                                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                                        <div>

                                            <div class="font-semibold text-neutral-800">
                                                🚚 Paket Sedang Dikirim
                                            </div>

                                            <div class="text-sm text-neutral-600 mt-1 leading-5">
                                                Jika paket sudah sampai di tangan Anda,
                                                silakan konfirmasi penerimaan pesanan.
                                            </div>

                                        </div>


                                        <form
                                            method="POST"
                                            action="{{ route('order.delivered', $order) }}"
                                        >

                                            @csrf

                                            <button
                                                type="submit"
                                                class="w-full md:w-auto bg-maroon-800 hover:bg-maroon-900 text-white px-5 py-3 rounded-xl text-sm font-semibold transition"
                                            >
                                                ✓ Barang Sudah Sampai
                                            </button>

                                        </form>

                                    </div>

                                </div>


                            @elseif($order->status === 'delivered')

                                <div class="mt-5 bg-neutral-50 border border-neutral-200 rounded-2xl p-4 sm:p-5">

                                    <div class="flex items-start gap-3">

                                        <div class="text-xl">
                                            ✓
                                        </div>

                                        <div>

                                            <div class="font-semibold text-neutral-700">
                                                Pesanan Telah Diterima
                                            </div>

                                            <div class="text-sm text-neutral-600 mt-1">
                                                Terima kasih telah berbelanja di Zalina Fashion.
                                            </div>

                                        </div>

                                    </div>

                                </div>


                                {{-- ================================================= --}}
                                {{-- RETURN SECTION --}}
                                {{-- ================================================= --}}

                                @if ($order->return_status === 'requested')

                                    <div class="mt-5 bg-white border border-amber-200 rounded-2xl overflow-hidden shadow-sm">

                                        <div class="flex items-center gap-3 bg-amber-50 px-4 sm:px-5 py-3.5 border-b border-amber-100">

                                            <div class="w-9 h-9 rounded-full bg-white border border-amber-200 flex items-center justify-center shrink-0">
                                                <svg class="w-4.5 h-4.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.75v5.25l3.5 2.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                                </svg>
                                            </div>

                                            <div class="min-w-0">
                                                <div class="font-bold text-amber-800 text-sm sm:text-base">
                                                    Retur Sedang Ditinjau
                                                </div>
                                                <p class="text-xs text-amber-700/80 mt-0.5 leading-4">
                                                    Menunggu persetujuan admin Zalina Fashion.
                                                </p>
                                            </div>

                                        </div>

                                        <div class="p-4 sm:p-5">

                                            <div class="flex flex-col sm:flex-row gap-4">

                                                @if ($order->return_image)
                                                    <a
                                                        href="{{ asset('storage/' . ltrim($order->return_image, '/')) }}"
                                                        target="_blank"
                                                        class="group/img relative w-full sm:w-28 h-40 sm:h-28 rounded-xl overflow-hidden border border-maroon-100 shrink-0 bg-maroon-50"
                                                    >
                                                        <img
                                                            src="{{ asset('storage/' . ltrim($order->return_image, '/')) }}"
                                                            alt="Bukti foto retur"
                                                            class="w-full h-full object-cover"
                                                            loading="lazy"
                                                        >
                                                        <span class="absolute inset-0 bg-black/0 group-hover/img:bg-black/30 transition flex items-center justify-center">
                                                            <span class="opacity-0 group-hover/img:opacity-100 text-white text-[11px] font-semibold transition">Lihat foto</span>
                                                        </span>
                                                    </a>
                                                @endif

                                                <div class="flex-1 min-w-0 space-y-2">

                                                    <div class="flex justify-between gap-3 text-sm">
                                                        <span class="text-maroon-400 shrink-0">Alasan</span>
                                                        <span class="font-semibold text-maroon-900 text-right">{{ $order->return_reason }}</span>
                                                    </div>

                                                    @if ($order->return_customer_note)
                                                        <div class="flex justify-between gap-3 text-sm">
                                                            <span class="text-maroon-400 shrink-0">Catatan</span>
                                                            <span class="font-semibold text-maroon-900 text-right">{{ $order->return_customer_note }}</span>
                                                        </div>
                                                    @endif

                                                    @unless ($order->return_image)
                                                        <p class="text-xs text-maroon-400 italic">Tidak ada foto bukti yang dilampirkan.</p>
                                                    @endunless

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                @elseif ($order->return_status === 'approved')

                                    <div class="mt-5 bg-white border border-emerald-200 rounded-2xl overflow-hidden shadow-sm">

                                        <div class="flex items-center gap-3 bg-emerald-50 px-4 sm:px-5 py-3.5 border-b border-emerald-100">

                                            <div class="w-9 h-9 rounded-full bg-white border border-emerald-200 flex items-center justify-center shrink-0">
                                                <svg class="w-4.5 h-4.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4.5 12.75 6 6 9-13.5"/>
                                                </svg>
                                            </div>

                                            <div class="min-w-0">
                                                <div class="font-bold text-emerald-800 text-sm sm:text-base">
                                                    Retur Disetujui
                                                </div>
                                                <p class="text-xs text-emerald-700/80 mt-0.5 leading-4">
                                                    Silakan ikuti langkah pengembalian barang di bawah ini.
                                                </p>
                                            </div>

                                        </div>

                                        <div class="p-4 sm:p-5">

                                            @if ($order->return_admin_note)
                                                <div class="mb-4 bg-maroon-50/60 border border-maroon-100 rounded-xl p-3.5 text-sm text-maroon-800">
                                                    <span class="font-semibold">Catatan admin:</span>
                                                    {{ $order->return_admin_note }}
                                                </div>
                                            @endif

                                            <div class="rounded-xl border border-maroon-100 p-4">

                                                <div class="flex items-center gap-2 font-semibold text-maroon-900 text-sm mb-3">
                                                    <svg class="w-4 h-4 text-maroon-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 9h16.5M3.75 9a2.25 2.25 0 0 1 2.25-2.25h12A2.25 2.25 0 0 1 20.25 9M3.75 9v8.25A2.25 2.25 0 0 0 6 19.5h12a2.25 2.25 0 0 0 2.25-2.25V9"/>
                                                    </svg>
                                                    Syarat & Cara Pengembalian Barang
                                                </div>

                                                <ol class="text-sm text-maroon-600 space-y-1.5 list-decimal list-inside leading-5">
                                                    <li>Kemas produk dalam kondisi asli beserta label dan kemasan.</li>
                                                    <li>Sertakan salinan struk/nomor pesanan <strong>{{ $order->order_number ?? ('#' . $order->id) }}</strong> di dalam paket.</li>
                                                    <li>Kirim ke alamat: <strong>Zalina Fashion, Gresik, Jawa Timur</strong> (detail lengkap dikirim via WhatsApp/email).</li>
                                                    <li>Gunakan jasa kurir yang bisa dilacak, lalu simpan nomor resinya.</li>
                                                    <li>Setelah barang kami terima dan diperiksa, retur akan diselesaikan dan proses refund/penggantian akan diproses.</li>
                                                </ol>

                                            </div>

                                        </div>

                                    </div>

                                @elseif ($order->return_status === 'rejected')

                                    <div class="mt-5 bg-white border border-maroon-200 rounded-2xl overflow-hidden shadow-sm">

                                        <div class="flex items-center gap-3 bg-maroon-50 px-4 sm:px-5 py-3.5 border-b border-maroon-100">

                                            <div class="w-9 h-9 rounded-full bg-white border border-maroon-200 flex items-center justify-center shrink-0">
                                                <svg class="w-4.5 h-4.5 text-maroon-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
                                                </svg>
                                            </div>

                                            <div class="min-w-0">
                                                <div class="font-bold text-maroon-800 text-sm sm:text-base">
                                                    Retur Tidak Disetujui
                                                </div>
                                                <p class="text-xs text-maroon-600/80 mt-0.5 leading-4">
                                                    Mohon maaf, pengajuan retur Anda belum bisa kami proses.
                                                </p>
                                            </div>

                                        </div>

                                        @if ($order->return_admin_note)
                                            <div class="p-4 sm:p-5">
                                                <div class="bg-maroon-50 border border-maroon-100 rounded-xl p-3.5 text-sm text-maroon-700">
                                                    <span class="font-semibold">Alasan:</span>
                                                    {{ $order->return_admin_note }}
                                                </div>
                                            </div>
                                        @endif

                                    </div>

                                @elseif ($order->return_status === 'completed')

                                    <div class="mt-5 bg-white border border-maroon-100 rounded-2xl overflow-hidden shadow-sm">

                                        <div class="flex items-center gap-3 bg-maroon-50/60 px-4 sm:px-5 py-3.5">

                                            <div class="w-9 h-9 rounded-full bg-white border border-maroon-200 flex items-center justify-center shrink-0">
                                                <svg class="w-4.5 h-4.5 text-maroon-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                                </svg>
                                            </div>

                                            <div class="min-w-0">
                                                <div class="font-bold text-maroon-900 text-sm sm:text-base">
                                                    Retur Selesai
                                                </div>
                                                <p class="text-xs text-maroon-500 mt-0.5 leading-4">
                                                    Terima kasih atas kesabaran Anda.
                                                </p>
                                            </div>

                                        </div>

                                    </div>

                                @elseif ($order->canRequestReturn())

                                    <div class="mt-5 bg-white border border-maroon-100 rounded-2xl overflow-hidden shadow-sm">

                                        <div class="flex items-center gap-3 bg-maroon-50/60 px-4 sm:px-5 py-3.5 border-b border-maroon-100">

                                            <div class="w-9 h-9 rounded-full bg-white border border-maroon-200 flex items-center justify-center shrink-0">
                                                <svg class="w-4.5 h-4.5 text-maroon-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.5 3.75 19.5 6.75m0 0-3 3m3-3H8.25A4.5 4.5 0 0 0 3.75 11.25M7.5 20.25 4.5 17.25m0 0 3-3m-3 3H15.75a4.5 4.5 0 0 0 4.5-4.5"/>
                                                </svg>
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <div class="font-bold text-maroon-900 text-sm sm:text-base">
                                                    Ajukan Retur Produk
                                                </div>
                                                <p class="text-xs text-maroon-500 mt-0.5 leading-4">
                                                    Produk bermasalah? Ajukan retur dan jelaskan alasan Anda.
                                                </p>
                                            </div>

                                        </div>

                                        <div class="p-4 sm:p-5">

                                            <div class="min-w-0 flex-1">


                                                @if ($errors->any() && old('return_order_id') == $order->id)
                                                    <div class="mt-4 bg-maroon-50 border border-maroon-200 rounded-xl p-3 text-sm text-maroon-700 space-y-1">
                                                        @foreach ($errors->all() as $error)
                                                            <div>{{ $error }}</div>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                <form
                                                    method="POST"
                                                    action="{{ route('order.return.request', $order) }}"
                                                    enctype="multipart/form-data"
                                                    class="mt-4 space-y-4"
                                                    id="returnForm{{ $order->id }}"
                                                >

                                                    @csrf

                                                    <input type="hidden" name="return_order_id" value="{{ $order->id }}">

                                                    <div>
                                                        <label class="block text-sm font-semibold text-maroon-900 mb-1.5">
                                                            Alasan Retur
                                                        </label>
                                                        <select
                                                            name="reason"
                                                            required
                                                            class="w-full rounded-xl border border-maroon-200 px-4 py-3 text-sm text-maroon-900 bg-white focus:outline-none focus:border-maroon-500 focus:ring-1 focus:ring-maroon-500"
                                                        >
                                                            <option value="">Pilih alasan retur</option>
                                                            <option value="Produk rusak">Produk rusak</option>
                                                            <option value="Salah ukuran">Salah ukuran</option>
                                                            <option value="Tidak sesuai deskripsi">Tidak sesuai deskripsi</option>
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <label class="block text-sm font-semibold text-maroon-900 mb-1.5">
                                                            Catatan Tambahan
                                                        </label>
                                                        <textarea
                                                            name="note"
                                                            rows="3"
                                                            placeholder="Jelaskan detail masalah pada produk Anda..."
                                                            class="w-full rounded-xl border border-maroon-200 px-4 py-3 text-sm text-maroon-900 placeholder:text-maroon-300 bg-white focus:outline-none focus:border-maroon-500 focus:ring-1 focus:ring-maroon-500 resize-none"
                                                        ></textarea>
                                                    </div>

                                                    <div>
                                                        <label class="block text-sm font-semibold text-maroon-900 mb-1.5">
                                                            Foto Bukti Produk
                                                        </label>

                                                        {{--
                                                        |--------------------------------------------------------------------------
                                                        | INPUT FOTO RETUR — 2 TOMBOL, 1 INPUT ASLI
                                                        |--------------------------------------------------------------------------
                                                        |
                                                        | Sengaja dibuat 2 tombol terpisah (Kamera & Galeri) karena
                                                        | perilaku <input type="file"> tanpa "capture" TIDAK konsisten
                                                        | di semua browser/HP — sebagian menampilkan dialog pilihan,
                                                        | sebagian langsung ke kamera, sebagian langsung ke galeri.
                                                        |
                                                        | PERBAIKAN: kedua tombol memicu SATU input file yang sama
                                                        | (name="image", yang beneran dikirim ke server). Atribut
                                                        | "capture" di-toggle lewat JS sebelum input diklik. Sebelumnya
                                                        | ada 2 input tersembunyi terpisah yang filenya "disalin" ke
                                                        | input asli pakai trik DataTransfer — trik itu kadang gagal
                                                        | diam-diam di sejumlah browser (termasuk Safari), sehingga
                                                        | foto yang dipilih user tidak pernah benar-benar terkirim ke
                                                        | server meskipun nama filenya sempat muncul di UI.
                                                        |--------------------------------------------------------------------------
                                                        --}}

                                                        <div class="grid grid-cols-2 gap-3">

                                                            <button
                                                                type="button"
                                                                id="returnBtnCamera{{ $order->id }}"
                                                                class="flex items-center justify-center gap-2 border border-dashed border-maroon-200 rounded-xl px-3 py-3 text-xs sm:text-sm font-semibold text-maroon-700 hover:border-maroon-400 hover:bg-maroon-50 transition"
                                                            >
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M6.827 6.75A2.25 2.25 0 0 1 8.905 5.25h6.19a2.25 2.25 0 0 1 2.078 1.5l.415 1.05h1.412a2.25 2.25 0 0 1 2.25 2.25v8.25a2.25 2.25 0 0 1-2.25 2.25H4.5a2.25 2.25 0 0 1-2.25-2.25V10.05a2.25 2.25 0 0 1 2.25-2.25h1.412l.415-1.05Z"/>
                                                                    <circle cx="12" cy="13.5" r="3.25" stroke-width="1.7"/>
                                                                </svg>
                                                                Ambil Foto
                                                            </button>

                                                            <button
                                                                type="button"
                                                                id="returnBtnGallery{{ $order->id }}"
                                                                class="flex items-center justify-center gap-2 border border-dashed border-maroon-200 rounded-xl px-3 py-3 text-xs sm:text-sm font-semibold text-maroon-700 hover:border-maroon-400 hover:bg-maroon-50 transition"
                                                            >
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3.75 6.75h16.5M3.75 6.75a1.5 1.5 0 0 1 1.5-1.5h13.5a1.5 1.5 0 0 1 1.5 1.5M3.75 6.75v10.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5V6.75M8.25 10.5a3.75 3.75 0 0 0 7.5 0"/>
                                                                </svg>
                                                                Pilih dari Galeri
                                                            </button>

                                                        </div>

                                                        {{-- Preview foto yang dipilih (thumbnail muncul begitu ada file) --}}
                                                        <div
                                                            id="returnPreviewWrap{{ $order->id }}"
                                                            class="flex items-center gap-3 bg-maroon-50/50 border border-maroon-100 rounded-xl p-2.5 mt-3"
                                                        >
                                                            <img
                                                                id="returnPreviewImg{{ $order->id }}"
                                                                src=""
                                                                alt="Preview foto retur"
                                                                class="hidden w-14 h-14 rounded-lg object-cover border border-maroon-100 bg-white shrink-0"
                                                            >
                                                            <p
                                                                id="returnFileName{{ $order->id }}"
                                                                class="text-sm text-maroon-400 truncate"
                                                            >
                                                                Belum ada foto dipilih
                                                            </p>
                                                        </div>

                                                        {{-- Input file ASLI — satu-satunya input yang dikirim ke
                                                             server (name="image"). Tombol Kamera & Galeri sama-sama
                                                             memicu input ini langsung, tanpa proses penyalinan file
                                                             antar-input yang rawan gagal. --}}
                                                        <input
                                                            type="file"
                                                            name="image"
                                                            id="returnImage{{ $order->id }}"
                                                            accept="image/*"
                                                            class="hidden"
                                                        >

                                                        <p class="text-xs text-maroon-300 mt-1.5">
                                                            Format JPG, JPEG, PNG, atau WEBP. Maksimal 20 MB.
                                                        </p>

                                                        <p
                                                            id="returnFileError{{ $order->id }}"
                                                            class="hidden text-xs font-semibold text-maroon-700 mt-1.5"
                                                        >
                                                            Ukuran foto terlalu besar (maks. 20 MB). Pilih foto yang lebih kecil.
                                                        </p>
                                                    </div>

                                                    <button
                                                        type="submit"
                                                        id="returnSubmit{{ $order->id }}"
                                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-maroon-800 hover:bg-maroon-900 text-white px-6 py-3 rounded-full font-bold text-sm transition"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 12 3.269 3.126A59.77 59.77 0 0 1 21.485 12 59.77 59.77 0 0 1 3.27 20.876L5.999 12Zm0 0h7.5"/>
                                                        </svg>
                                                        Kirim Pengajuan Retur
                                                    </button>

                                                </form>

                                                <script>
                                                (function () {
                                                    var mainInput = document.getElementById('returnImage{{ $order->id }}');
                                                    var btnCamera = document.getElementById('returnBtnCamera{{ $order->id }}');
                                                    var btnGallery = document.getElementById('returnBtnGallery{{ $order->id }}');
                                                    var nameLabel = document.getElementById('returnFileName{{ $order->id }}');
                                                    var previewImg = document.getElementById('returnPreviewImg{{ $order->id }}');
                                                    var errorText = document.getElementById('returnFileError{{ $order->id }}');
                                                    var submitBtn = document.getElementById('returnSubmit{{ $order->id }}');
                                                    var maxBytes = 20 * 1024 * 1024;

                                                    if (!mainInput) { return; }

                                                    function handleFile(file) {

                                                        if (!file) {
                                                            nameLabel.textContent = 'Belum ada foto dipilih';
                                                            nameLabel.classList.remove('text-maroon-700', 'font-medium');
                                                            nameLabel.classList.add('text-maroon-400');
                                                            previewImg.classList.add('hidden');
                                                            previewImg.src = '';
                                                            errorText.classList.add('hidden');
                                                            submitBtn.disabled = false;
                                                            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                                                            return;
                                                        }

                                                        nameLabel.textContent = file.name;
                                                        nameLabel.classList.remove('text-maroon-400');
                                                        nameLabel.classList.add('text-maroon-700', 'font-medium');

                                                        if (window.URL && window.URL.createObjectURL) {
                                                            previewImg.src = window.URL.createObjectURL(file);
                                                            previewImg.classList.remove('hidden');
                                                        }

                                                        if (file.size > maxBytes) {
                                                            errorText.classList.remove('hidden');
                                                            submitBtn.disabled = true;
                                                            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                                                        } else {
                                                            errorText.classList.add('hidden');
                                                            submitBtn.disabled = false;
                                                            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                                                        }
                                                    }

                                                    // Input asli yang berubah -> langsung update UI.
                                                    // Tidak ada lagi proses penyalinan file antar-input.
                                                    mainInput.addEventListener('change', function () {
                                                        handleFile(mainInput.files && mainInput.files[0]);
                                                    });

                                                    if (btnCamera) {
                                                        btnCamera.addEventListener('click', function () {
                                                            mainInput.value = '';
                                                            mainInput.setAttribute('capture', 'environment');
                                                            mainInput.click();
                                                        });
                                                    }

                                                    if (btnGallery) {
                                                        btnGallery.addEventListener('click', function () {
                                                            mainInput.value = '';
                                                            mainInput.removeAttribute('capture');
                                                            mainInput.click();
                                                        });
                                                    }
                                                })();
                                                </script>

                                            </div>

                                        </div>

                                    </div>

                                @endif

                            @endif

                        </div>

                        </div>

                    </details>


                @empty

                    {{-- ================================================= --}}
                    {{-- EMPTY ORDER --}}
                    {{-- ================================================= --}}

                    <div class="border border-dashed border-maroon-200 rounded-3xl py-14 sm:py-16 text-center">

                        <div class="w-20 h-20 mx-auto rounded-full bg-maroon-50 flex items-center justify-center mb-5">
                            <svg class="w-9 h-9 text-maroon-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 6.75h16.5M3.75 6.75a1.5 1.5 0 0 1 1.5-1.5h13.5a1.5 1.5 0 0 1 1.5 1.5M3.75 6.75v10.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5V6.75M8.25 10.5a3.75 3.75 0 0 0 7.5 0"/>
                            </svg>
                        </div>

                        <p class="font-serif text-xl sm:text-2xl text-maroon-900">
                            Belum ada pesanan
                        </p>

                        <p class="text-sm text-maroon-400 mt-2 max-w-md mx-auto px-5">
                            Yuk mulai belanja koleksi Zalina Fashion dan pesanan Anda akan muncul di halaman ini.
                        </p>

                        <a
                            href="{{ route('shop') }}"
                            class="inline-flex items-center justify-center gap-2 mt-6 bg-maroon-700 hover:bg-maroon-800 text-white px-6 py-3 rounded-full text-sm font-semibold transition"
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
                                    stroke-width="1.8"
                                    d="m3 3 2 1 2.5 10h10.8l2.2-7H7.5"
                                />
                            </svg>

                            Belanja Sekarang

                        </a>

                    </div>

                @endforelse
                        </div>

                        @if($totalOrderCount > 0)
                            <p id="zlOrderCount" class="mt-4 hidden text-[12px] text-maroon-400 lg:block">
                                Menampilkan 1 - {{ $totalOrderCount }} dari {{ $totalOrderCount }} pesanan
                            </p>
                        @endif

                    </div>

                </section>

            </div>
        @endif

            </div>

        </div>

        @if($user->role !== 'admin' && $profileOrders->contains(fn ($o) => filled($o->tracking_number) && filled($o->tracking_sent_at ?? null)))
            <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
            <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof JsBarcode === 'undefined') { return; }

                document.querySelectorAll('svg[data-zl-barcode]').forEach(function (svg) {
                    try {
                        JsBarcode(svg, svg.getAttribute('data-zl-barcode'), {
                            format: 'CODE128',
                            displayValue: false,
                            height: 60,
                            width: 2,
                            margin: 0,
                            background: '#ffffff',
                            lineColor: '#000000'
                        });
                    } catch (error) {
                        svg.innerHTML = '';
                    }
                });
            });
            </script>
        @endif
        @if($user->role !== 'admin' && $totalOrderCount > 0)
            <script>
            (function () {
                var tabs = document.querySelectorAll('#zalinaOrderTabs .zalina-order-tab');
                var cards = document.querySelectorAll('.zalina-order');
                var counter = document.getElementById('zlOrderCount');
                // Kartu "Produk Diretur" di Ringkasan Akun -> klik langsung
                // membuka tab "Retur" di riwayat pesanan.
                var summaryReturnLinks = document.querySelectorAll('[data-order-filter-link="retur"]');

                if (!tabs.length || !cards.length) { return; }

                function applyFilter(filter) {

                    var visibleCount = 0;

                    cards.forEach(function (card) {
                        // Satu order bisa punya lebih dari satu grup, contoh:
                        // "finished retur" (order sudah selesai TAPI juga diretur).
                        // Dengan begitu order itu tetap tampil di tab "Selesai"
                        // maupun tab "Retur".
                        var groups = (card.getAttribute('data-order-group') || '').split(' ');
                        var visible = (filter === 'all') || groups.indexOf(filter) !== -1;
                        card.style.display = visible ? '' : 'none';
                        if (visible) { visibleCount++; }
                    });

                    tabs.forEach(function (btn) {
                        btn.classList.toggle('is-active', btn.getAttribute('data-order-filter') === filter);
                    });

                    if (counter) {
                        counter.textContent = 'Menampilkan '
                            + (visibleCount ? '1 - ' + visibleCount : '0')
                            + ' dari ' + cards.length + ' pesanan';
                    }
                }

                tabs.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        applyFilter(btn.getAttribute('data-order-filter'));
                    });
                });

                summaryReturnLinks.forEach(function (link) {
                    link.addEventListener('click', function () {
                        // Biarkan browser scroll ke #riwayat-pesanan seperti biasa,
                        // filter diterapkan setelahnya lewat tab "Retur".
                        window.setTimeout(function () { applyFilter('retur'); }, 50);
                    });
                });

                if (window.location.hash.indexOf('filter=retur') !== -1) {
                    applyFilter('retur');
                } else {
                    applyFilter('all');
                }
            })();
            </script>
        @endif



    {{-- ============================================================= --}}
    {{-- BELUM LOGIN --}}
    {{-- ============================================================= --}}

    @else

        <div class="max-w-md mx-auto text-center px-5 py-16 sm:py-20">

            <div class="w-16 h-16 mx-auto rounded-full bg-maroon-900 flex items-center justify-center mb-6">
                <svg class="w-8 h-8 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.1a7.5 7.5 0 0 1 15 0"/>
                </svg>
            </div>

            <p class="text-xs uppercase tracking-[0.25em] text-maroon-400 mb-2">
                Zalina Fashion Account
            </p>

            <h1 class="font-serif text-3xl sm:text-4xl text-neutral-900 mb-3">
                Anda belum masuk
            </h1>

            <p class="text-sm text-neutral-500 mb-8">
                Masuk atau buat akun untuk melihat riwayat transaksi, status pembayaran,
                receipt, dan progress pengiriman pesanan Anda.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">

                <a
                    href="{{ route('login.form') }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-maroon-800 hover:bg-maroon-900 text-white px-6 py-3.5 rounded-full text-sm font-semibold transition"
                >
                    Masuk
                </a>

                <a
                    href="{{ route('register.form') }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 border border-neutral-200 text-neutral-800 hover:bg-neutral-50 px-6 py-3.5 rounded-full text-sm font-semibold transition"
                >
                    Daftar Akun
                </a>

            </div>

        </div>

    @endif

</div>

@endsection