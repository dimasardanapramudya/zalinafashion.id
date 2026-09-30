@extends('layouts.admin')

@section('title','Promo & Diskon')

@section('content')

@php
    // Harus identik dengan $themeMap di resources/views/home.blade.php
    // supaya preview di admin = tampilan asli di homepage.
    $themeMap = [
        'maroon_gold' => ['label' => 'Maroon Gold',    'from' => '#2d0810', 'via' => '#631f2b', 'to' => '#b98a3d'],
        'rose'        => ['label' => 'Rose Blush',     'from' => '#4c1020', 'via' => '#9f3554', 'to' => '#e7a7b7'],
        'emerald'     => ['label' => 'Emerald Luxe',   'from' => '#052d2a', 'via' => '#12665f', 'to' => '#bfa66a'],
        'midnight'    => ['label' => 'Midnight Violet','from' => '#080b18', 'via' => '#1d2340', 'to' => '#705b9a'],
        'cream'       => ['label' => 'Cream Gold',     'from' => '#f4ede3', 'via' => '#e8d5bd', 'to' => '#b98a3d'],
    ];

    $totalPromo    = $discounts->count();
    $activePromo   = $discounts->where('is_active', true)->count();
    $inactivePromo = $totalPromo - $activePromo;

    $endingSoonPromo = $discounts->filter(function ($d) {
        return $d->is_active
            && $d->ends_at
            && now()->diffInDays($d->ends_at, false) <= 3
            && now()->lessThanOrEqualTo($d->ends_at);
    })->count();

    $expiredPromo = $discounts->filter(function ($d) {
        return $d->ends_at && now()->greaterThan($d->ends_at);
    })->count();
@endphp

<style>

    /* ===================== HERO HEADER ===================== */
    .promo-admin-hero {
        position: relative;
        overflow: hidden;
        border-radius: 28px;
        background: linear-gradient(120deg, #2d0810 0%, #631f2b 55%, #8a3145 100%);
        box-shadow: 0 25px 60px rgba(45, 8, 16, 0.35);
    }

    .promo-admin-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 15% 20%, rgba(255,255,255,0.10) 0, transparent 35%),
            radial-gradient(circle at 85% 15%, rgba(185,138,61,0.35) 0, transparent 32%),
            radial-gradient(circle at 60% 90%, rgba(255,255,255,0.06) 0, transparent 40%);
        pointer-events: none;
    }

    /* ===================== STAT CARD ===================== */
    .promo-stat-card {
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.14);
        transition: transform 0.3s ease, background 0.3s ease;
    }

    .promo-stat-card:hover {
        transform: translateY(-4px);
        background: rgba(255,255,255,0.12);
    }

    /* ===================== FORM CARD ===================== */
    .promo-form-card {
        border-radius: 26px;
        box-shadow: 0 20px 45px rgba(45, 8, 16, 0.10);
    }

    .promo-form-card::before {
        content: "";
        display: block;
        height: 6px;
        border-radius: 26px 26px 0 0;
        background: linear-gradient(90deg, #2d0810, #b98a3d, #2d0810);
        margin: -1px -1px 0 -1px;
    }

    /* ===================== SUBMIT BUTTON SHINE ===================== */
    .promo-submit-btn {
        position: relative;
        overflow: hidden;
    }

    .promo-submit-btn::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(100deg, transparent 30%, rgba(255,255,255,0.35) 50%, transparent 70%);
        transform: translateX(-130%);
        transition: transform 0.6s ease;
    }

    .promo-submit-btn:hover::after {
        transform: translateX(130%);
    }

    /* ===================== LIST CARD ===================== */
    .promo-list-card {
        border-radius: 24px;
        transition: transform 0.35s cubic-bezier(.2,.8,.2,1), box-shadow 0.35s ease;
    }

    .promo-list-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 22px 45px rgba(45, 8, 16, 0.14);
    }

    /* ===================== MINI PREVIEW (mirrors storefront card) ===================== */
    .promo-mini-preview {
        position: relative;
        overflow: hidden;
        border-radius: 16px;
        min-height: 92px;
    }

    .promo-mini-preview::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 20% 20%, rgba(255,255,255,0.35) 0, transparent 35%),
            radial-gradient(circle at 80% 80%, rgba(0,0,0,0.15) 0, transparent 40%);
    }

    .promo-mini-preview.style-ticket::after {
        content: "";
        position: absolute;
        top: 0;
        bottom: 0;
        right: 30%;
        border-left: 2px dashed rgba(255,255,255,0.45);
    }

    /* ===================== SWATCH / STYLE PICKER ===================== */
    .theme-swatch {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        border: 3px solid transparent;
        transition: transform 0.25s ease, border-color 0.25s ease;
        cursor: pointer;
    }

    .theme-swatch:hover {
        transform: scale(1.08);
    }

    input:checked + .theme-swatch {
        border-color: #2d0810;
        transform: scale(1.12);
    }

    .style-option {
        border: 2px solid #eadfd4;
        border-radius: 14px;
        padding: 10px;
        cursor: pointer;
        transition: all 0.25s ease;
    }

    input:checked + .style-option {
        border-color: #631f2b;
        background: #fbf3ee;
        box-shadow: 0 6px 16px rgba(99, 31, 43, 0.12);
    }

    /* ===================== TOGGLE SWITCH ===================== */
    .toggle-track {
        width: 46px;
        height: 26px;
        border-radius: 999px;
        background: #e5d9d1;
        transition: background 0.3s ease;
    }

    input:checked + .toggle-track {
        background: #631f2b;
    }

    .toggle-dot {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.25);
        transition: transform 0.3s ease;
        transform: translate(3px, 3px);
    }

    input:checked ~ .toggle-dot {
        transform: translate(23px, 3px);
    }

    /* ===================== CHEVRON ROTATE ===================== */
    .promo-chevron {
        transition: transform 0.3s ease;
    }

    details[open] .promo-chevron {
        transform: rotate(180deg);
    }

</style>


{{-- ========================================================= --}}
{{-- HERO HEADER + STATS --}}
{{-- ========================================================= --}}

<div class="promo-admin-hero px-6 sm:px-10 py-8 sm:py-10 mb-8">

    <div class="relative z-10 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">

        <div>
            <span class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.25em] text-[#e7c06d]">
                <span style="width:24px;height:1px;background:currentColor;display:inline-block;"></span>
                Admin Panel
            </span>

            <h1 class="mt-3 text-white text-3xl sm:text-4xl" style="font-family: Georgia, serif;">
                Promo &amp; Diskon
            </h1>

            <p class="mt-2 text-white/60 text-sm max-w-md">
                Kelola voucher, diskon persen, dan potongan harga. Atur tema warna dan bentuk kartu agar sesuai gaya kampanye promomu di homepage.
            </p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 w-full lg:w-auto">

            <div class="promo-stat-card rounded-2xl px-5 py-4 text-center">
                <div class="text-2xl font-bold text-white">{{ $totalPromo }}</div>
                <div class="text-[11px] uppercase tracking-widest text-white/50 mt-1">Total</div>
            </div>

            <div class="promo-stat-card rounded-2xl px-5 py-4 text-center">
                <div class="text-2xl font-bold text-emerald-300">{{ $activePromo }}</div>
                <div class="text-[11px] uppercase tracking-widest text-white/50 mt-1">Aktif</div>
            </div>

            <div class="promo-stat-card rounded-2xl px-5 py-4 text-center">
                <div class="text-2xl font-bold text-amber-300">{{ $endingSoonPromo }}</div>
                <div class="text-[11px] uppercase tracking-widest text-white/50 mt-1">Segera Habis</div>
            </div>

            <div class="promo-stat-card rounded-2xl px-5 py-4 text-center">
                <div class="text-2xl font-bold text-white/40">{{ $inactivePromo }}</div>
                <div class="text-[11px] uppercase tracking-widest text-white/50 mt-1">Nonaktif</div>
            </div>

        </div>

    </div>

</div>


<div class="grid xl:grid-cols-[400px_1fr] gap-6 items-start">


    {{-- ========================================================= --}}
    {{-- TAMBAH PROMO --}}
    {{-- ========================================================= --}}

    <form
        method="POST"
        enctype="multipart/form-data"
        action="{{ route('admin.discounts.store') }}"
        x-data="{ type: 'percent', preview: null }"
        class="promo-form-card bg-white border border-maroon-100 overflow-hidden xl:sticky xl:top-6"
    >

        <div class="p-6 space-y-5">

            @csrf

            <div>
                <h2 class="font-serif text-2xl text-maroon-800" style="font-family: Georgia, serif;">
                    Tambah Promo
                </h2>

                <p class="text-sm text-maroon-500 mt-1">
                    Promo dapat berupa kode voucher atau diskon otomatis yang tampil di homepage.
                </p>
            </div>


            <div>
                <label class="block text-sm font-semibold mb-1.5 text-maroon-800">
                    Nama Promo
                </label>

                <input
                    required
                    name="name"
                    placeholder="Diskon Akhir Tahun"
                    class="w-full rounded-xl border border-maroon-200 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-maroon-300 transition"
                >
            </div>


            {{-- UPLOAD GAMBAR DENGAN PREVIEW --}}
            <div>
                <label class="block text-sm font-semibold mb-1.5 text-maroon-800">
                    Gambar Promo
                </label>

                <label class="relative flex flex-col items-center justify-center w-full h-28 rounded-xl border-2 border-dashed border-maroon-200 hover:border-maroon-400 cursor-pointer overflow-hidden bg-maroon-50/50 transition-colors">

                    <template x-if="!preview">
                        <span class="text-xs text-maroon-400 text-center px-6 leading-relaxed">
                            Klik untuk upload gambar<br>(opsional, maks 2MB)
                        </span>
                    </template>

                    <img x-show="preview" :src="preview" class="absolute inset-0 w-full h-full object-cover">

                    <input
                        type="file"
                        name="image"
                        accept="image/*"
                        class="sr-only"
                        @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : null"
                    >
                </label>
            </div>


            <div>
                <label class="block text-sm font-semibold mb-1.5 text-maroon-800">
                    Kode Promo <span class="font-normal text-maroon-400">(opsional)</span>
                </label>

                <input
                    name="code"
                    placeholder="HEMAT10"
                    class="w-full rounded-xl border border-maroon-200 px-4 py-2.5 uppercase focus:outline-none focus:ring-2 focus:ring-maroon-300 transition"
                >
            </div>


            <div class="grid grid-cols-2 gap-3">

                <div>
                    <label class="block text-sm font-semibold mb-1.5 text-maroon-800">
                        Jenis
                    </label>

                    <select
                        name="type"
                        x-model="type"
                        class="w-full rounded-xl border border-maroon-200 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-maroon-300 transition"
                    >
                        <option value="percent">Persentase (%)</option>
                        <option value="fixed">Potongan Rp</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1.5 text-maroon-800">
                        Nilai
                    </label>

                    <div class="relative">
                        <input
                            required
                            type="number"
                            step="0.01"
                            min="0"
                            name="value"
                            class="w-full rounded-xl border border-maroon-200 pl-4 pr-10 py-2.5 focus:outline-none focus:ring-2 focus:ring-maroon-300 transition"
                        >
                        <span
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-sm font-semibold text-maroon-400"
                            x-text="type === 'percent' ? '%' : 'Rp'"
                        ></span>
                    </div>
                </div>

            </div>


            <div>
                <label class="block text-sm font-semibold mb-1.5 text-maroon-800">
                    Minimum Belanja
                </label>

                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-maroon-400">Rp</span>
                    <input
                        type="number"
                        min="0"
                        name="minimum_order"
                        value="0"
                        class="w-full rounded-xl border border-maroon-200 pl-10 pr-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-maroon-300 transition"
                    >
                </div>
            </div>


            <div class="grid grid-cols-2 gap-3">

                <div>
                    <label class="block text-sm font-semibold mb-1.5 text-maroon-800">
                        Mulai
                    </label>

                    <input
                        type="datetime-local"
                        name="starts_at"
                        class="w-full rounded-xl border border-maroon-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-maroon-300 transition"
                    >
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1.5 text-maroon-800">
                        Berakhir
                    </label>

                    <input
                        type="datetime-local"
                        name="ends_at"
                        class="w-full rounded-xl border border-maroon-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-maroon-300 transition"
                    >
                </div>

            </div>


            {{-- THEME PICKER --}}
            <div>
                <label class="block text-sm font-semibold mb-2 text-maroon-800">
                    Tema Warna Kartu
                </label>

                <div class="flex flex-wrap gap-3">
                    @foreach($themeMap as $key => $t)
                        <label>
                            <input
                                type="radio"
                                name="theme"
                                value="{{ $key }}"
                                class="sr-only peer"
                                {{ $loop->first ? 'checked' : '' }}
                            >
                            <span
                                class="theme-swatch block"
                                style="background: linear-gradient(135deg, {{ $t['from'] }}, {{ $t['via'] }}, {{ $t['to'] }});"
                                title="{{ $t['label'] }}"
                            ></span>
                        </label>
                    @endforeach
                </div>
            </div>


            {{-- CARD STYLE PICKER --}}
            <div>
                <label class="block text-sm font-semibold mb-2 text-maroon-800">
                    Bentuk Kartu di Homepage
                </label>

                <div class="grid grid-cols-2 gap-3">

                    <label>
                        <input type="radio" name="card_style" value="ticket" class="sr-only peer" checked>
                        <span class="style-option block text-center">
                            <span class="block h-9 rounded-lg mb-2 relative overflow-hidden bg-gradient-to-br from-maroon-700 to-[#b98a3d]">
                                <span class="absolute top-0 bottom-0 right-[28%] border-l-2 border-dashed border-white/40"></span>
                            </span>
                            <span class="text-xs font-semibold text-maroon-700">Tiket Kupon</span>
                        </span>
                    </label>

                    <label>
                        <input type="radio" name="card_style" value="banner" class="sr-only peer">
                        <span class="style-option block text-center">
                            <span class="block h-9 rounded-lg mb-2 bg-gradient-to-r from-maroon-700 to-[#b98a3d]"></span>
                            <span class="text-xs font-semibold text-maroon-700">Banner Lebar</span>
                        </span>
                    </label>

                </div>
            </div>


            {{-- TOGGLE ACTIVE --}}
            <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                <span class="relative inline-block">
                    <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                    <span class="toggle-track block"></span>
                    <span class="toggle-dot absolute top-0 left-0"></span>
                </span>
                <span class="text-sm font-semibold text-maroon-800">Promo aktif</span>
            </label>


            <button class="promo-submit-btn w-full bg-gradient-to-r from-maroon-800 via-maroon-700 to-maroon-800 text-white py-3.5 rounded-full font-semibold shadow-lg shadow-maroon-900/20 hover:shadow-xl transition-shadow">
                Tambah Promo
            </button>

        </div>

    </form>



    {{-- ========================================================= --}}
    {{-- LIST PROMO --}}
    {{-- ========================================================= --}}

    <div class="space-y-5">

        @forelse($discounts as $d)

            @php
                $dTheme = $themeMap[$d->theme ?? 'maroon_gold'] ?? $themeMap['maroon_gold'];
                $dStyle = $d->card_style ?? 'ticket';

                $dValueLabel = $d->type === 'percent'
                    ? rtrim(rtrim(number_format((float) $d->value, 2, ',', '.'), '0'), ',') . '%'
                    : 'Rp ' . number_format((float) $d->value, 0, ',', '.');

                $dIsExpired = $d->ends_at && now()->greaterThan($d->ends_at);

                $dIsEndingSoon = $d->is_active
                    && !$dIsExpired
                    && $d->ends_at
                    && now()->diffInDays($d->ends_at, false) <= 3;
            @endphp

            <details class="promo-list-card bg-white border border-maroon-100 overflow-hidden group">

                <summary class="cursor-pointer list-none p-5 flex flex-wrap items-center gap-4">

                    {{-- MINI PREVIEW MIRRORING STOREFRONT CARD --}}
                    <div
                        class="promo-mini-preview style-{{ $dStyle }} w-full sm:w-44 shrink-0"
                        style="background: linear-gradient(135deg, {{ $dTheme['from'] }}, {{ $dTheme['via'] }}, {{ $dTheme['to'] }});"
                    >
                        @if($d->image)
                            <img
                                src="{{ asset('storage/' . $d->image) }}"
                                class="absolute inset-0 w-full h-full object-cover opacity-30"
                            >
                        @endif

                        <div class="relative z-10 h-full flex flex-col items-center justify-center text-center px-2 py-3">
                            <div class="text-white font-bold text-xl leading-none" style="font-family: Georgia, serif;">
                                {{ $dValueLabel }}
                            </div>
                            <div class="text-white/70 text-[10px] uppercase tracking-widest mt-1">OFF</div>
                        </div>
                    </div>


                    <div class="flex-1 min-w-[160px]">

                        <div class="font-semibold text-maroon-900">
                            {{ $d->name }}
                        </div>

                        <div class="text-xs text-maroon-400 mt-1 flex flex-wrap items-center gap-x-2 gap-y-1">
                            <span>{{ $d->code ?: 'Tanpa kode' }}</span>

                            @if($d->minimum_order)
                                <span>· Min Rp {{ number_format((float) $d->minimum_order, 0, ',', '.') }}</span>
                            @endif

                            @if($d->ends_at)
                                <span>· s/d {{ $d->ends_at->translatedFormat('d M Y') }}</span>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-1.5 mt-2">

                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide {{ $d->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $d->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>

                            @if($dIsExpired)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide bg-gray-200 text-gray-500">
                                    Kadaluarsa
                                </span>
                            @elseif($dIsEndingSoon)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide bg-amber-100 text-amber-700">
                                    Segera Berakhir
                                </span>
                            @endif

                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide bg-maroon-50 text-maroon-500">
                                {{ $dStyle === 'banner' ? 'Banner' : 'Tiket' }} · {{ $dTheme['label'] }}
                            </span>

                        </div>

                    </div>


                    <svg class="promo-chevron w-5 h-5 text-maroon-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>

                </summary>



                <div class="border-t border-maroon-100 p-5 sm:p-6 bg-maroon-50/30">

                    <form
                        method="POST"
                        enctype="multipart/form-data"
                        action="{{ route('admin.discounts.update', $d) }}"
                        x-data="{ type: '{{ $d->type }}' }"
                        class="grid md:grid-cols-2 gap-4"
                    >

                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-semibold mb-1 text-maroon-600">Nama Promo</label>
                            <input name="name" value="{{ $d->name }}" class="w-full rounded-xl border border-maroon-200 px-4 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-maroon-300 transition">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold mb-1 text-maroon-600">Kode Promo</label>
                            <input name="code" value="{{ $d->code }}" class="w-full rounded-xl border border-maroon-200 px-4 py-2.5 bg-white uppercase focus:outline-none focus:ring-2 focus:ring-maroon-300 transition">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold mb-1 text-maroon-600">Jenis</label>
                            <select name="type" x-model="type" class="w-full rounded-xl border border-maroon-200 px-4 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-maroon-300 transition">
                                <option value="percent" @selected($d->type === 'percent')>Persentase</option>
                                <option value="fixed" @selected($d->type === 'fixed')>Potongan Rp</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold mb-1 text-maroon-600">Nilai</label>
                            <div class="relative">
                                <input type="number" step="0.01" min="0" name="value" value="{{ $d->value }}" class="w-full rounded-xl border border-maroon-200 pl-4 pr-10 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-maroon-300 transition">
                                <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-sm font-semibold text-maroon-400" x-text="type === 'percent' ? '%' : 'Rp'"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold mb-1 text-maroon-600">Minimum Belanja</label>
                            <input type="number" min="0" name="minimum_order" value="{{ $d->minimum_order }}" class="w-full rounded-xl border border-maroon-200 px-4 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-maroon-300 transition">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-maroon-600">Mulai</label>
                                <input type="datetime-local" name="starts_at" value="{{ optional($d->starts_at)->format('Y-m-d\TH:i') }}" class="w-full rounded-xl border border-maroon-200 px-3 py-2.5 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-maroon-300 transition">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold mb-1 text-maroon-600">Berakhir</label>
                                <input type="datetime-local" name="ends_at" value="{{ optional($d->ends_at)->format('Y-m-d\TH:i') }}" class="w-full rounded-xl border border-maroon-200 px-3 py-2.5 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-maroon-300 transition">
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold mb-1 text-maroon-600">Ganti Gambar</label>
                            <input type="file" name="image" accept="image/*" class="w-full rounded-xl border border-maroon-200 px-4 py-2.5 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-maroon-300 transition">
                        </div>

                        {{-- THEME PICKER --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold mb-2 text-maroon-600">Tema Warna Kartu</label>
                            <div class="flex flex-wrap gap-3">
                                @foreach($themeMap as $key => $t)
                                    <label>
                                        <input
                                            type="radio"
                                            name="theme"
                                            value="{{ $key }}"
                                            class="sr-only peer"
                                            @checked(($d->theme ?? 'maroon_gold') === $key)
                                        >
                                        <span
                                            class="theme-swatch block"
                                            style="background: linear-gradient(135deg, {{ $t['from'] }}, {{ $t['via'] }}, {{ $t['to'] }});"
                                            title="{{ $t['label'] }}"
                                        ></span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- CARD STYLE PICKER --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold mb-2 text-maroon-600">Bentuk Kartu</label>
                            <div class="grid grid-cols-2 gap-3 max-w-xs">
                                <label>
                                    <input type="radio" name="card_style" value="ticket" class="sr-only peer" @checked(($d->card_style ?? 'ticket') === 'ticket')>
                                    <span class="style-option block text-center">
                                        <span class="block h-8 rounded-lg mb-1.5 relative overflow-hidden bg-gradient-to-br from-maroon-700 to-[#b98a3d]">
                                            <span class="absolute top-0 bottom-0 right-[28%] border-l-2 border-dashed border-white/40"></span>
                                        </span>
                                        <span class="text-[11px] font-semibold text-maroon-700">Tiket</span>
                                    </span>
                                </label>
                                <label>
                                    <input type="radio" name="card_style" value="banner" class="sr-only peer" @checked(($d->card_style ?? 'ticket') === 'banner')>
                                    <span class="style-option block text-center">
                                        <span class="block h-8 rounded-lg mb-1.5 bg-gradient-to-r from-maroon-700 to-[#b98a3d]"></span>
                                        <span class="text-[11px] font-semibold text-maroon-700">Banner</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <label class="inline-flex items-center gap-3 cursor-pointer select-none md:col-span-2">
                            <span class="relative inline-block">
                                <input type="checkbox" name="is_active" value="1" @checked($d->is_active) class="sr-only peer">
                                <span class="toggle-track block"></span>
                                <span class="toggle-dot absolute top-0 left-0"></span>
                            </span>
                            <span class="text-sm font-semibold text-maroon-800">Promo aktif</span>
                        </label>

                        <button class="promo-submit-btn bg-gradient-to-r from-maroon-800 via-maroon-700 to-maroon-800 text-white px-6 py-2.5 rounded-full font-semibold w-fit shadow-md hover:shadow-lg transition-shadow">
                            Simpan Perubahan
                        </button>

                    </form>


                    <form
                        method="POST"
                        action="{{ route('admin.discounts.destroy', $d) }}"
                        class="mt-4 pt-4 border-t border-maroon-100/70"
                        onsubmit="return confirm('Hapus promo ini secara permanen?')"
                    >
                        @csrf
                        @method('DELETE')

                        <button class="inline-flex items-center gap-1.5 text-sm text-red-600 font-semibold hover:text-red-700 transition">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 6h18M8 6V4a1 1 0 011-1h6a1 1 0 011 1v2m2 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6h14z" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Hapus Promo
                        </button>
                    </form>

                </div>

            </details>

        @empty

            <div class="bg-white rounded-3xl border-2 border-dashed border-maroon-200 p-14 text-center">
                <div class="text-4xl mb-3">🏷️</div>
                <div class="text-maroon-500 font-semibold">Belum ada promo</div>
                <p class="text-sm text-maroon-400 mt-1">Tambahkan promo pertamamu lewat form di sebelah kiri.</p>
            </div>

        @endforelse

    </div>

</div>

@endsection