@extends('layouts.admin')

@section('title', 'Pengaturan Toko')

@section('content')

@php
    $settings = $settings ?? [];

    $siteName = old(
        'site_name',
        $settings['site_name'] ?? 'Zalina Fashion'
    ) ?: 'Zalina Fashion';

    $siteTagline = old(
        'site_tagline',
        $settings['site_tagline'] ?? 'Elegance in Every Drape'
    );

    $senderName = old(
        'sender_name',
        $settings['sender_name'] ?? $siteName
    ) ?: $siteName;

    $senderAddress = old(
        'sender_address',
        $settings['sender_address'] ?? ''
    );

    $senderCity = old(
        'sender_city',
        $settings['sender_city'] ?? ''
    );

    $senderProvince = old(
        'sender_province',
        $settings['sender_province'] ?? ''
    );

    $senderPostalCode = old(
        'sender_postal_code',
        $settings['sender_postal_code'] ?? ''
    );

    $senderPhone = old(
        'sender_phone',
        $settings['sender_phone'] ?? ''
    );

    $announcementText = old(
        'announcement_text',
        $settings['announcement_text']
            ?? '✨ Gratis ongkir se-Indonesia untuk pembelian di atas Rp 250.000 ✨'
    );

    $freeShippingEnabled = old(
        'free_shipping_enabled',
        filter_var(
            $settings['free_shipping_enabled'] ?? true,
            FILTER_VALIDATE_BOOLEAN
        )
    );

    $freeShippingMinimum = old(
        'free_shipping_minimum',
        $settings['free_shipping_minimum'] ?? 250000
    );

    $shippingCost = old(
        'shipping_cost',
        $settings['shipping_cost'] ?? 15000
    );

    // Default Rp2.000 sama dengan default di CheckoutController & halaman checkout.
    $adminFee = old(
        'admin_fee',
        $settings['admin_fee'] ?? 2000
    );

    $instagramUrl = old(
        'instagram_url',
        $settings['instagram_url'] ?? 'https://www.instagram.com/zalinascarf.id'
    );

    $instagramLabel = old(
        'instagram_label',
        $settings['instagram_label'] ?? '@zalinascarf.id'
    );

    $shopeeUrl = old(
        'shopee_url',
        $settings['shopee_url'] ?? 'https://id.shp.ee/QdSvtAVp'
    );

    $whatsappUrl = old(
        'whatsapp_url',
        $settings['whatsapp_url'] ?? 'https://wa.me/628133117767'
    );

    $whatsappLabel = old(
        'whatsapp_label',
        $settings['whatsapp_label'] ?? '08133117767'
    );

    $sliderAutoplay = old(
        'slider_autoplay_ms',
        $settings['slider_autoplay_ms'] ?? 5000
    );

    $logoPath = $settings['site_logo'] ?? null;
@endphp

<div class="mx-auto max-w-6xl space-y-8">

    {{-- PAGE HEADER --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-maroon-900 via-maroon-800 to-maroon-700 px-6 py-8 text-white shadow-lg sm:px-8">
        <div class="relative z-10 max-w-3xl">
            <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-maroon-100">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M12 3l2.8 5.7L21 9.6l-4.5 4.4 1.1 6.2L12 17.3 6.4 20.2l1.1-6.2L3 9.6l6.2-.9L12 3z"/>
                </svg>
                Store Configuration
            </div>

            <h1 class="font-serif text-3xl font-semibold sm:text-4xl">
                Pengaturan Toko
            </h1>

            <p class="mt-3 max-w-2xl text-sm leading-relaxed text-maroon-100 sm:text-base">
                Kelola identitas Zalina Fashion, informasi pengiriman,
                promosi, media sosial, dan perilaku slider dari satu halaman.
            </p>
        </div>

        <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full border-[30px] border-white/10"></div>
        <div class="pointer-events-none absolute -bottom-28 right-20 h-64 w-64 rounded-full border-[24px] border-white/5"></div>
    </div>

    {{-- SUCCESS ALERT --}}
    @if(session('success'))
        <div class="flex items-start gap-3 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-700 shadow-sm">
            <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M5 13l4 4L19 7"/>
            </svg>

            <div>
                <p class="font-bold">Pengaturan berhasil disimpan</p>
                <p class="mt-1 text-green-600">
                    {{ session('success') }}
                </p>
            </div>
        </div>
    @endif

    {{-- VALIDATION ALERT --}}
    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700 shadow-sm">
            <div class="flex items-start gap-3">
                <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 9v4m0 4h.01M10.3 3.8L2.8 17a2 2 0 001.7 3h15a2 2 0 001.7 0L13.7 3.8a2 2 0 00-3.4 0z"/>
                </svg>

                <div>
                    <p class="font-bold">Ada data yang perlu diperbaiki</p>

                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('admin.settings.update') }}"
        enctype="multipart/form-data"
        class="space-y-6"
    >
        @csrf
        @method('PUT')

        {{-- BRANDING --}}
        <section class="overflow-hidden rounded-3xl border border-maroon-100 bg-white shadow-sm">
            <div class="border-b border-maroon-100 bg-maroon-50/60 px-6 py-5 sm:px-8">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-maroon-700 text-white shadow-sm">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 3l2.8 5.7L21 9.6l-4.5 4.4 1.1 6.2L12 17.3 6.4 20.2l1.1-6.2L3 9.6l6.2-.9L12 3z"/>
                        </svg>
                    </div>

                    <div>
                        <h2 class="font-serif text-2xl text-maroon-900">
                            Branding
                        </h2>

                        <p class="mt-1 text-sm text-maroon-500">
                            Identitas utama yang tampil di website.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 px-6 py-6 sm:px-8 lg:grid-cols-3">
                <div class="grid gap-5 sm:grid-cols-2 lg:col-span-2">
                    <div>
                        <label for="site_name" class="mb-2 block text-sm font-semibold text-slate-700">
                            Nama Toko
                        </label>

                        <input
                            id="site_name"
                            type="text"
                            name="site_name"
                            required
                            value="{{ $siteName }}"
                            placeholder="Contoh: Zalina Fashion"
                            class="admin-setting-input"
                        >
                    </div>

                    <div>
                        <label for="site_tagline" class="mb-2 block text-sm font-semibold text-slate-700">
                            Tagline
                        </label>

                        <input
                            id="site_tagline"
                            type="text"
                            name="site_tagline"
                            value="{{ $siteTagline }}"
                            placeholder="Elegance in Every Drape"
                            class="admin-setting-input"
                        >
                    </div>

                    <div class="sm:col-span-2">
                        <label for="logo" class="mb-2 block text-sm font-semibold text-slate-700">
                            Logo Toko
                        </label>

                        <div class="flex flex-col gap-4 rounded-2xl border border-dashed border-maroon-200 bg-maroon-50/40 p-4 sm:flex-row sm:items-center">
                            <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-maroon-100 bg-white shadow-sm">
                                @if($logoPath)
                                    <img
                                        src="{{ asset('storage/' . ltrim($logoPath, '/')) }}"
                                        alt="Logo {{ $siteName }}"
                                        class="h-full w-full object-contain p-2"
                                    >
                                @else
                                    <div class="text-center text-maroon-300">
                                        <svg class="mx-auto h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path d="M4 5h16v14H4z"/>
                                            <path d="M8 13l2.5-3 3 4 2-2 3.5 4"/>
                                        </svg>

                                        <span class="mt-1 block text-[10px] font-semibold uppercase">
                                            No Logo
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <input
                                    id="logo"
                                    type="file"
                                    name="logo"
                                    accept="image/png,image/jpeg,image/webp"
                                    class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-maroon-700 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-maroon-800"
                                >

                                <p class="mt-2 text-xs leading-relaxed text-slate-500">
                                    Format PNG, JPG, atau WEBP. Maksimal 4 MB.
                                    Gunakan logo dengan latar transparan untuk hasil terbaik.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-maroon-100 bg-maroon-50/50 p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-maroon-500">
                        Live Preview
                    </p>

                    <div class="mt-5 flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white shadow-sm">
                            @if($logoPath)
                                <img
                                    src="{{ asset('storage/' . ltrim($logoPath, '/')) }}"
                                    alt="Preview logo"
                                    class="h-10 w-10 object-contain"
                                >
                            @else
                                <span class="font-serif text-xl font-bold text-maroon-700">
                                    Z
                                </span>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <p class="truncate font-serif text-lg font-semibold text-maroon-900">
                                {{ $siteName }}
                            </p>

                            <p class="truncate text-xs text-maroon-500">
                                {{ $siteTagline }}
                            </p>
                        </div>
                    </div>

                    <p class="mt-5 text-xs leading-relaxed text-maroon-600">
                        Preview ini menggambarkan tampilan identitas toko pada navbar.
                    </p>
                </div>
            </div>
        </section>

        {{-- SENDER --}}
        <section class="overflow-hidden rounded-3xl border border-maroon-100 bg-white shadow-sm">
            <div class="border-b border-maroon-100 bg-maroon-50/60 px-6 py-5 sm:px-8">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-maroon-700 text-white shadow-sm">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M3 7l9-4 9 4v10l-9 4-9-4V7z"/>
                            <path d="M3 7l9 5 9-5M12 12v9"/>
                        </svg>
                    </div>

                    <div>
                        <h2 class="font-serif text-2xl text-maroon-900">
                            Informasi Pengirim
                        </h2>

                        <p class="mt-1 text-sm text-maroon-500">
                            Digunakan pada shipping label dan label paket.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 px-6 py-6 sm:px-8 lg:grid-cols-3">
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                    <div class="flex items-center gap-2 text-amber-800">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 9v4m0 4h.01M10.3 3.8L2.8 17a2 2 0 001.7 3h15a2 2 0 001.7 3L13.7 3.8a2 2 0 00-3.4 0z"/>
                        </svg>

                        <p class="text-sm font-bold">
                            Pastikan data benar
                        </p>
                    </div>

                    <p class="mt-2 text-sm leading-relaxed text-amber-700">
                        Gunakan alamat dan nomor telepon yang benar-benar
                        digunakan saat menyerahkan paket kepada kurir.
                    </p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2 lg:col-span-2">
                    <div class="sm:col-span-2">
                        <label for="sender_name" class="mb-2 block text-sm font-semibold text-slate-700">
                            Nama Pengirim / Nama Toko
                        </label>

                        <input
                            id="sender_name"
                            type="text"
                            name="sender_name"
                            required
                            value="{{ $senderName }}"
                            placeholder="Contoh: Zalina Fashion"
                            class="admin-setting-input"
                        >
                    </div>

                    <div class="sm:col-span-2">
                        <label for="sender_address" class="mb-2 block text-sm font-semibold text-slate-700">
                            Alamat Pengirim
                        </label>

                        <textarea
                            id="sender_address"
                            name="sender_address"
                            required
                            rows="3"
                            placeholder="Alamat lengkap toko atau gudang"
                            class="admin-setting-input resize-y"
                        >{{ $senderAddress }}</textarea>
                    </div>

                    <div>
                        <label for="sender_city" class="mb-2 block text-sm font-semibold text-slate-700">
                            Kota / Kabupaten
                        </label>

                        <input
                            id="sender_city"
                            type="text"
                            name="sender_city"
                            required
                            value="{{ $senderCity }}"
                            placeholder="Contoh: Gresik"
                            class="admin-setting-input"
                        >
                    </div>

                    <div>
                        <label for="sender_province" class="mb-2 block text-sm font-semibold text-slate-700">
                            Provinsi
                        </label>

                        <input
                            id="sender_province"
                            type="text"
                            name="sender_province"
                            required
                            value="{{ $senderProvince }}"
                            placeholder="Contoh: Jawa Timur"
                            class="admin-setting-input"
                        >
                    </div>

                    <div>
                        <label for="sender_postal_code" class="mb-2 block text-sm font-semibold text-slate-700">
                            Kode Pos
                        </label>

                        <input
                            id="sender_postal_code"
                            type="text"
                            name="sender_postal_code"
                            required
                            value="{{ $senderPostalCode }}"
                            placeholder="Contoh: 611xx"
                            class="admin-setting-input"
                        >
                    </div>

                    <div>
                        <label for="sender_phone" class="mb-2 block text-sm font-semibold text-slate-700">
                            Nomor Telepon Pengirim
                        </label>

                        <input
                            id="sender_phone"
                            type="text"
                            name="sender_phone"
                            required
                            value="{{ $senderPhone }}"
                            placeholder="Contoh: 08xxxxxxxxxx"
                            class="admin-setting-input"
                        >
                    </div>
                </div>
            </div>
        </section>

        {{-- PROMO AND SHIPPING --}}
        <section class="overflow-hidden rounded-3xl border border-maroon-100 bg-white shadow-sm">
            <div class="border-b border-maroon-100 bg-maroon-50/60 px-6 py-5 sm:px-8">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-maroon-700 text-white shadow-sm">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M20 12V8a2 2 0 00-2-2h-5L9 2 2 9l13 13 7-7-2-3z"/>
                            <path d="M7 7h.01"/>
                        </svg>
                    </div>

                    <div>
                        <h2 class="font-serif text-2xl text-maroon-900">
                            Promo dan Pengiriman
                        </h2>

                        <p class="mt-1 text-sm text-maroon-500">
                            Atur pengumuman, gratis ongkir, dan biaya pengiriman normal.
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-6 px-6 py-6 sm:px-8">

                <div>
                    <label for="announcement_text" class="mb-2 block text-sm font-semibold text-slate-700">
                        Teks Pengumuman
                    </label>

                    <input
                        id="announcement_text"
                        type="text"
                        name="announcement_text"
                        value="{{ $announcementText }}"
                        placeholder="Teks promo yang tampil di bagian atas website"
                        class="admin-setting-input"
                    >

                    <p class="mt-2 text-xs text-slate-500">
                        Contoh: Gratis ongkir untuk pembelian minimal Rp250.000.
                    </p>
                </div>

                <div class="rounded-2xl border border-maroon-100 bg-maroon-50/50 p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="font-semibold text-maroon-900">
                                Aktifkan Gratis Ongkir
                            </h3>

                            <p class="mt-1 text-sm leading-relaxed text-maroon-600">
                                Jika aktif, biaya pengiriman menjadi Rp0 ketika subtotal
                                memenuhi minimal pembelian.
                            </p>
                        </div>

                        <label class="relative inline-flex cursor-pointer items-center">
                            <input
                                type="checkbox"
                                name="free_shipping_enabled"
                                value="1"
                                class="peer sr-only"
                                @checked((bool) $freeShippingEnabled)
                            >

                            <div class="h-7 w-12 rounded-full bg-slate-300 transition peer-checked:bg-maroon-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-maroon-200 after:absolute after:left-[3px] after:top-[3px] after:h-[22px] after:w-[22px] after:rounded-full after:bg-white after:shadow-sm after:transition-all peer-checked:after:translate-x-5"></div>

                            <span class="ml-3 text-sm font-semibold text-slate-700">
                                Aktif
                            </span>
                        </label>
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="free_shipping_minimum" class="mb-2 block text-sm font-semibold text-slate-700">
                            Minimal Gratis Ongkir
                        </label>

                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-sm font-semibold text-maroon-400">
                                Rp
                            </span>

                            <input
                                id="free_shipping_minimum"
                                type="number"
                                name="free_shipping_minimum"
                                min="0"
                                step="1"
                                value="{{ $freeShippingMinimum }}"
                                class="admin-setting-input pl-12"
                            >
                        </div>

                        <p class="mt-2 text-xs text-slate-500">
                            Contoh: 250000 berarti gratis ongkir mulai Rp250.000.
                        </p>
                    </div>

                    <div>
                        <label for="shipping_cost" class="mb-2 block text-sm font-semibold text-slate-700">
                            Biaya Ongkir Normal
                        </label>

                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-sm font-semibold text-maroon-400">
                                Rp
                            </span>

                            <input
                                id="shipping_cost"
                                type="number"
                                name="shipping_cost"
                                min="0"
                                step="1"
                                value="{{ $shippingCost }}"
                                class="admin-setting-input pl-12"
                            >
                        </div>

                        <p class="mt-2 text-xs text-slate-500">
                            Biaya yang digunakan ketika gratis ongkir tidak terpenuhi.
                        </p>
                    </div>

                    <div>
                        <label for="admin_fee" class="mb-2 block text-sm font-semibold text-slate-700">
                            Biaya Admin
                        </label>

                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-sm font-semibold text-maroon-400">
                                Rp
                            </span>

                            <input
                                id="admin_fee"
                                type="number"
                                name="admin_fee"
                                min="0"
                                step="1"
                                value="{{ $adminFee }}"
                                class="admin-setting-input pl-12"
                            >
                        </div>

                        <p class="mt-2 text-xs text-slate-500">
                            Ditambahkan ke setiap pesanan dan tampil sebagai "Biaya Admin"
                            di checkout. Isi 0 jika tidak ingin menarik biaya admin.
                        </p>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-maroon-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 8v4m0 4h.01"/>
                        </svg>

                        <div>
                            <p class="text-sm font-semibold text-slate-800">
                                Ringkasan aturan pengiriman
                            </p>

                            <p class="mt-1 text-sm leading-relaxed text-slate-600">
                                @if($freeShippingEnabled)
                                    Gratis ongkir aktif untuk subtotal minimal
                                    <strong>
                                        Rp{{ number_format((float) $freeShippingMinimum, 0, ',', '.') }}
                                    </strong>.
                                    Jika belum memenuhi minimum, biaya ongkir normal adalah
                                    <strong>
                                        Rp{{ number_format((float) $shippingCost, 0, ',', '.') }}
                                    </strong>.
                                @else
                                    Gratis ongkir sedang nonaktif.
                                    Semua pesanan akan menggunakan biaya ongkir normal sebesar
                                    <strong>
                                        Rp{{ number_format((float) $shippingCost, 0, ',', '.') }}
                                    </strong>.
                                @endif

                                Setiap pesanan juga dikenai biaya admin
                                <strong>
                                    Rp{{ number_format((float) $adminFee, 0, ',', '.') }}
                                </strong>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- SOCIAL MEDIA --}}
        <section class="overflow-hidden rounded-3xl border border-maroon-100 bg-white shadow-sm">
            <div class="border-b border-maroon-100 bg-maroon-50/60 px-6 py-5 sm:px-8">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-maroon-700 text-white shadow-sm">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="3" width="18" height="18" rx="5"/>
                            <circle cx="12" cy="12" r="4"/>
                            <path d="M17.5 6.5h.01"/>
                        </svg>
                    </div>

                    <div>
                        <h2 class="font-serif text-2xl text-maroon-900">
                            Social Media
                        </h2>

                        <p class="mt-1 text-sm text-maroon-500">
                            Link yang tampil pada footer website.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid gap-5 px-6 py-6 sm:grid-cols-2 sm:px-8">
                <div>
                    <label for="instagram_url" class="mb-2 block text-sm font-semibold text-slate-700">
                        Instagram URL
                    </label>

                    <input
                        id="instagram_url"
                        type="url"
                        name="instagram_url"
                        value="{{ $instagramUrl }}"
                        placeholder="https://www.instagram.com/..."
                        class="admin-setting-input"
                    >
                </div>

                <div>
                    <label for="instagram_label" class="mb-2 block text-sm font-semibold text-slate-700">
                        Instagram Label
                    </label>

                    <input
                        id="instagram_label"
                        type="text"
                        name="instagram_label"
                        value="{{ $instagramLabel }}"
                        placeholder="@username"
                        class="admin-setting-input"
                    >
                </div>

                <div>
                    <label for="shopee_url" class="mb-2 block text-sm font-semibold text-slate-700">
                        Shopee URL
                    </label>

                    <input
                        id="shopee_url"
                        type="url"
                        name="shopee_url"
                        value="{{ $shopeeUrl }}"
                        placeholder="https://id.shp.ee/..."
                        class="admin-setting-input"
                    >
                </div>

                <div>
                    <label for="whatsapp_url" class="mb-2 block text-sm font-semibold text-slate-700">
                        WhatsApp URL
                    </label>

                    <input
                        id="whatsapp_url"
                        type="url"
                        name="whatsapp_url"
                        value="{{ $whatsappUrl }}"
                        placeholder="https://wa.me/..."
                        class="admin-setting-input"
                    >
                </div>

                <div class="sm:col-span-2">
                    <label for="whatsapp_label" class="mb-2 block text-sm font-semibold text-slate-700">
                        WhatsApp Label
                    </label>

                    <input
                        id="whatsapp_label"
                        type="text"
                        name="whatsapp_label"
                        value="{{ $whatsappLabel }}"
                        placeholder="Nomor WhatsApp yang ditampilkan"
                        class="admin-setting-input"
                    >
                </div>
            </div>
        </section>

        {{-- SLIDER --}}
        <section class="overflow-hidden rounded-3xl border border-maroon-100 bg-white shadow-sm">
            <div class="border-b border-maroon-100 bg-maroon-50/60 px-6 py-5 sm:px-8">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-maroon-700 text-white shadow-sm">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M4 6h16M4 12h16M4 18h16"/>
                            <circle cx="8" cy="6" r="2"/>
                            <circle cx="15" cy="12" r="2"/>
                            <circle cx="10" cy="18" r="2"/>
                        </svg>
                    </div>

                    <div>
                        <h2 class="font-serif text-2xl text-maroon-900">
                            Slider
                        </h2>

                        <p class="mt-1 text-sm text-maroon-500">
                            Atur waktu perpindahan banner otomatis.
                        </p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-6 sm:px-8">
                <div class="max-w-md">
                    <label for="slider_autoplay_ms" class="mb-2 block text-sm font-semibold text-slate-700">
                        Autoplay Slider
                    </label>

                    <div class="relative">
                        <input
                            id="slider_autoplay_ms"
                            type="number"
                            name="slider_autoplay_ms"
                            min="1000"
                            max="20000"
                            step="500"
                            value="{{ $sliderAutoplay }}"
                            class="admin-setting-input pr-20"
                        >

                        <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-xs font-semibold text-maroon-400">
                            ms
                        </span>
                    </div>

                    <p class="mt-2 text-xs leading-relaxed text-slate-500">
                        Nilai 5000 berarti slider berpindah setiap 5 detik.
                        Nilai minimum 1000 dan maksimum 20000.
                    </p>
                </div>
            </div>
        </section>

        {{-- ACTION --}}
        <div class="sticky bottom-4 z-20 flex flex-col gap-3 rounded-3xl border border-maroon-100 bg-white/95 p-4 shadow-xl backdrop-blur sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-maroon-900">
                    Simpan perubahan toko
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Semua pengaturan akan diterapkan setelah disimpan.
                </p>
            </div>

            <button
                type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-full bg-maroon-700 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-maroon-800 focus:outline-none focus:ring-2 focus:ring-maroon-300 focus:ring-offset-2"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                    <path d="M17 21v-8H7v8M7 3v5h8"/>
                </svg>

                Simpan Semua Pengaturan
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .admin-setting-input {
        width: 100%;
        border-radius: 0.9rem;
        border: 1px solid rgb(231 213 220);
        background: rgb(255 255 255);
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        color: rgb(51 28 38);
        outline: none;
        transition:
            border-color 150ms ease,
            box-shadow 150ms ease,
            background-color 150ms ease;
    }

    .admin-setting-input::placeholder {
        color: rgb(171 145 157);
    }

    .admin-setting-input:hover {
        border-color: rgb(196 157 174);
    }

    .admin-setting-input:focus {
        border-color: rgb(128 45 76);
        box-shadow: 0 0 0 3px rgb(128 45 76 / 0.12);
    }

    .admin-setting-input:invalid:not(:placeholder-shown) {
        border-color: rgb(220 38 38);
    }
</style>
@endpush