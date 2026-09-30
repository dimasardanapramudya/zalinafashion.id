@extends('layouts.admin')

@section('title', 'Kelola Produk')

@section('content')
@php
    use Illuminate\Support\Facades\Storage;

    $totalProducts = $products->total();

    $activeProducts = $products->getCollection()
        ->where('is_active', true)
        ->count();

    $lowStockProducts = $products->getCollection()
        ->where('stock', '<=', 5)
        ->count();

    $featuredProducts = $products->getCollection()
        ->where('is_featured', true)
        ->count();
@endphp

<div class="min-h-screen space-y-6 bg-[#faf8f6] px-4 py-5 sm:px-6 lg:px-8">

    {{-- HEADER --}}
    <section class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#4b1428] via-[#711f3c] to-[#a65c75] px-5 py-7 text-white shadow-xl sm:px-8 sm:py-9">

        <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute -bottom-32 left-1/3 h-80 w-80 rounded-full bg-[#e8c27d]/20 blur-3xl"></div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

            <div class="max-w-2xl">
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.18em] text-white/90 backdrop-blur">
                    <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                    Product Management
                </div>

                <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">
                    Kelola Produk
                </h1>

                <p class="mt-2 max-w-xl text-sm leading-6 text-white/75">
                    Atur katalog produk Zalina Fashion, mulai dari gambar,
                    harga, stok, hingga status produk yang tampil di toko.
                </p>
            </div>

            <a
                href="{{ route('admin.products.create') }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3.5 text-sm font-bold text-[#681d38] shadow-lg transition hover:-translate-y-0.5 hover:bg-[#fff8ed] sm:w-fit"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 4v16m8-8H4"
                    />
                </svg>

                Tambah Produk
            </a>
        </div>
    </section>

    {{-- FLASH MESSAGE --}}
    @if(session('success'))
        <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-800 shadow-sm">
            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 13l4 4L19 7"
                />
            </svg>

            <span class="font-medium">
                {{ session('success') }}
            </span>
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-800 shadow-sm">
            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8v4m0 4h.01M10.3 4.5L2.8 17a2 2 0 001.7 3h15a2 2 0 001.7-3L13.7 4.5a2 2 0 00-3.4 0z"
                />
            </svg>

            <span class="font-medium">
                {{ session('error') }}
            </span>
        </div>
    @endif

    {{-- STATISTICS --}}
    <section class="grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-4 xl:grid-cols-4">

        {{-- TOTAL --}}
        <div class="rounded-2xl border border-[#eadde2] bg-white p-4 shadow-sm sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-[#9a6a7b] sm:text-xs">
                        Total Produk
                    </p>

                    <p class="mt-2 text-2xl font-bold text-[#421727] sm:text-3xl">
                        {{ $totalProducts }}
                    </p>
                </div>

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#f7eaf0] text-[#8b3d5b] sm:h-12 sm:w-12">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M20 7l-8-4-8 4m16 0v10l-8 4-8-4V7m16 0l-8 4-8-4m8 4v10"
                        />
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-xs text-[#a17b89]">
                Seluruh katalog
            </p>
        </div>

        {{-- ACTIVE --}}
        <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-emerald-600 sm:text-xs">
                        Produk Aktif
                    </p>

                    <p class="mt-2 text-2xl font-bold text-emerald-900 sm:text-3xl">
                        {{ $activeProducts }}
                    </p>
                </div>

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 sm:h-12 sm:w-12">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-xs text-emerald-600/80">
                Tampil di toko
            </p>
        </div>

        {{-- LOW STOCK --}}
        <div class="rounded-2xl border border-amber-100 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-amber-600 sm:text-xs">
                        Stok Menipis
                    </p>

                    <p class="mt-2 text-2xl font-bold text-amber-900 sm:text-3xl">
                        {{ $lowStockProducts }}
                    </p>
                </div>

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 sm:h-12 sm:w-12">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 9v4m0 4h.01M10.3 4.5L2.8 17a2 2 0 001.7 3h15a2 2 0 001.7-3L13.7 4.5a2 2 0 00-3.4 0z"
                        />
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-xs text-amber-600/80">
                Perlu diperiksa
            </p>
        </div>

        {{-- FEATURED --}}
        <div class="rounded-2xl border border-purple-100 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-purple-600 sm:text-xs">
                        Featured
                    </p>

                    <p class="mt-2 text-2xl font-bold text-purple-900 sm:text-3xl">
                        {{ $featuredProducts }}
                    </p>
                </div>

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-100 text-purple-700 sm:h-12 sm:w-12">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 3l2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3 1.1-6.2L3 9.6l6.2-.9L12 3z"
                        />
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-xs text-purple-600/80">
                Produk unggulan
            </p>
        </div>
    </section>

    {{-- PRODUCT SECTION --}}
    <section class="overflow-hidden rounded-[2rem] border border-[#eadde2] bg-white shadow-sm">

        {{-- SECTION HEADER --}}
        <div class="border-b border-[#f0e5e9] px-5 py-5 sm:px-7">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h2 class="text-xl font-bold text-[#421727]">
                        Daftar Produk
                    </h2>

                    <p class="mt-1 text-sm leading-6 text-[#987582]">
                        Kelola gambar, harga, stok, dan status produk.
                    </p>
                </div>

                <div class="w-fit rounded-xl bg-[#f9eef3] px-3 py-2 text-xs font-bold text-[#8b3d5b]">
                    {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }}
                    dari {{ $products->total() }} produk
                </div>
            </div>
        </div>

        {{-- DESKTOP TABLE --}}
        <div class="hidden overflow-x-auto md:block">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#fcf7f9] text-xs uppercase tracking-wide text-[#916576]">
                    <tr>
                        <th class="px-6 py-4 font-bold">Produk</th>
                        <th class="px-4 py-4 font-bold">Kategori</th>
                        <th class="px-4 py-4 font-bold">Harga</th>
                        <th class="px-4 py-4 font-bold">Stok</th>
                        <th class="px-4 py-4 font-bold">Status</th>
                        <th class="px-6 py-4 text-right font-bold">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#f4e9ed]">
                    @forelse($products as $product)
                        @php
                            $productName = $product->name ?? 'Produk';
                            $initial = mb_strtoupper(mb_substr($productName, 0, 1));

                            $imagePath = $product->image
                                ? ltrim($product->image, '/')
                                : null;

                            $imageExists = $imagePath
                                && Storage::disk('public')->exists($imagePath);

                            $stockClass = match (true) {
                                $product->stock <= 0 => 'border-rose-200 bg-rose-50 text-rose-700',
                                $product->stock <= 5 => 'border-amber-200 bg-amber-50 text-amber-700',
                                default => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                            };

                            $stockLabel = match (true) {
                                $product->stock <= 0 => 'Habis',
                                $product->stock <= 5 => 'Menipis',
                                default => 'Tersedia',
                            };
                        @endphp

                        <tr class="transition hover:bg-[#fffafc]">

                            {{-- PRODUCT --}}
                            <td class="px-6 py-5">
                                <div class="flex min-w-[250px] items-center gap-4">

                                    <div class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl border border-[#eadde2] bg-gradient-to-br from-[#f9eef3] to-[#fff7e9]">
                                        @if($imageExists)
                                            <img
                                                src="{{ asset('storage/' . $imagePath) }}"
                                                alt="{{ $productName }}"
                                                class="h-full w-full object-cover"
                                                loading="lazy"
                                            >
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-xl font-bold text-[#a56b7e]">
                                                {{ $initial }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="min-w-0">
                                        <p class="font-bold text-[#421727]">
                                            {{ $productName }}
                                        </p>

                                        <p class="mt-1 text-xs text-[#a17b89]">
                                            SKU: {{ $product->sku ?: 'Belum ada SKU' }}
                                        </p>

                                        @if($imageExists)
                                            <p class="mt-1 text-[10px] font-semibold text-emerald-600">
                                                Gambar tersedia
                                            </p>
                                        @else
                                            <p class="mt-1 text-[10px] font-semibold text-amber-600">
                                                Belum ada gambar
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- CATEGORY --}}
                            <td class="px-4 py-5 align-middle">
                                <span class="inline-flex rounded-xl border border-[#eadde2] bg-[#fcf7f9] px-3 py-2 text-xs font-semibold text-[#8b3d5b]">
                                    {{ $product->category?->name ?? 'Tanpa Kategori' }}
                                </span>
                            </td>

                            {{-- PRICE --}}
                            <td class="px-4 py-5 align-middle">
                                <p class="font-bold text-[#421727]">
                                    Rp {{ number_format($product->sale_price ?? $product->price, 0, ',', '.') }}
                                </p>

                                @if($product->sale_price)
                                    <p class="mt-1 text-xs text-[#b28e9c] line-through">
                                        Rp {{ number_format($product->price, 0, ',', '.') }}
                                    </p>

                                    <span class="mt-2 inline-flex rounded-full bg-rose-100 px-2 py-1 text-[10px] font-bold text-rose-700">
                                        SALE
                                    </span>
                                @endif
                            </td>

                            {{-- STOCK --}}
                            <td class="px-4 py-5 align-middle">
                                <p class="font-bold text-[#421727]">
                                    {{ $product->stock }} pcs
                                </p>

                                <span class="mt-2 inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $stockClass }}">
                                    {{ $stockLabel }}
                                </span>
                            </td>

                            {{-- STATUS --}}
                            <td class="px-4 py-5 align-middle">
                                <div class="flex flex-wrap gap-2">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $product->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>

                                    @if($product->is_featured)
                                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                            Featured
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- ACTION --}}
                            <td class="px-6 py-5 align-middle">
                                <div class="flex justify-end gap-2">
                                    <a
                                        href="{{ route('admin.products.edit', $product) }}"
                                        class="inline-flex items-center gap-2 rounded-xl border border-[#e6ccd6] bg-white px-3 py-2 text-xs font-bold text-[#8b3d5b] transition hover:bg-[#fcf0f4]"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-width="1.8"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.1 2.1 0 013 3L12 15l-4 1 1-4 9.5-9.5z"
                                            />
                                        </svg>
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.products.destroy', $product) }}"
                                        onsubmit="return confirm('Hapus produk ini?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-white px-3 py-2 text-xs font-bold text-rose-600 transition hover:bg-rose-50"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-width="1.8"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M6 7h12M9 7V4h6v3m-8 0l1 13h6l1-13M10 11v5m4-5v5"
                                                />
                                            </svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#f9eef3] text-[#a56b7e]">
                                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M20 7l-8-4-8 4m16 0v10l-8 4-8-4V7m16 0l-8 4-8-4m8 4v10"
                                        />
                                    </svg>
                                </div>

                                <h3 class="mt-4 text-lg font-bold text-[#421727]">
                                    Belum Ada Produk
                                </h3>

                                <p class="mt-2 text-sm text-[#987582]">
                                    Tambahkan produk pertama untuk mulai membangun katalog.
                                </p>

                                <a
                                    href="{{ route('admin.products.create') }}"
                                    class="mt-5 inline-flex items-center gap-2 rounded-xl bg-[#711f3c] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#5b172f]"
                                >
                                    Tambah Produk
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- MOBILE CARDS --}}
        <div class="divide-y divide-[#f4e9ed] md:hidden">
            @forelse($products as $product)
                @php
                    $productName = $product->name ?? 'Produk';
                    $initial = mb_strtoupper(mb_substr($productName, 0, 1));

                    $imagePath = $product->image
                        ? ltrim($product->image, '/')
                        : null;

                    $imageExists = $imagePath
                        && Storage::disk('public')->exists($imagePath);

                    $stockClass = match (true) {
                        $product->stock <= 0 => 'border-rose-200 bg-rose-50 text-rose-700',
                        $product->stock <= 5 => 'border-amber-200 bg-amber-50 text-amber-700',
                        default => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                    };

                    $stockLabel = match (true) {
                        $product->stock <= 0 => 'Habis',
                        $product->stock <= 5 => 'Menipis',
                        default => 'Tersedia',
                    };
                @endphp

                <article class="p-4">
                    <div class="flex gap-3">

                        <div class="h-24 w-24 shrink-0 overflow-hidden rounded-2xl border border-[#eadde2] bg-gradient-to-br from-[#f9eef3] to-[#fff7e9]">
                            @if($imageExists)
                                <img
                                    src="{{ asset('storage/' . $imagePath) }}"
                                    alt="{{ $productName }}"
                                    class="h-full w-full object-cover"
                                    loading="lazy"
                                >
                            @else
                                <div class="flex h-full w-full items-center justify-center text-2xl font-bold text-[#a56b7e]">
                                    {{ $initial }}
                                </div>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="line-clamp-2 text-sm font-bold leading-5 text-[#421727]">
                                    {{ $productName }}
                                </h3>

                                @if($product->is_featured)
                                    <span class="shrink-0 rounded-full bg-amber-100 px-2 py-1 text-[9px] font-bold text-amber-700">
                                        Featured
                                    </span>
                                @endif
                            </div>

                            <p class="mt-1 text-xs text-[#a17b89]">
                                SKU: {{ $product->sku ?: 'Belum ada SKU' }}
                            </p>

                            <p class="mt-2 text-base font-bold text-[#711f3c]">
                                Rp {{ number_format($product->sale_price ?? $product->price, 0, ',', '.') }}
                            </p>

                            @if($product->sale_price)
                                <p class="text-xs text-[#b28e9c] line-through">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <span class="rounded-xl border border-[#eadde2] bg-[#fcf7f9] px-3 py-1.5 text-xs font-semibold text-[#8b3d5b]">
                            {{ $product->category?->name ?? 'Tanpa Kategori' }}
                        </span>

                        <span class="rounded-full border px-3 py-1.5 text-xs font-semibold {{ $stockClass }}">
                            {{ $product->stock }} pcs · {{ $stockLabel }}
                        </span>

                        <span class="rounded-full px-3 py-1.5 text-xs font-semibold {{ $product->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    @if($imageExists)
                        <p class="mt-3 text-xs font-semibold text-emerald-600">
                            ✓ Gambar produk tersedia
                        </p>
                    @else
                        <p class="mt-3 text-xs font-semibold text-amber-600">
                            ! Gambar produk belum tersedia
                        </p>
                    @endif

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <a
                            href="{{ route('admin.products.edit', $product) }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-[#e6ccd6] bg-white px-3 py-3 text-xs font-bold text-[#8b3d5b] transition hover:bg-[#fcf0f4]"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.1 2.1 0 013 3L12 15l-4 1 1-4 9.5-9.5z"
                                />
                            </svg>
                            Edit Produk
                        </a>

                        <form
                            method="POST"
                            action="{{ route('admin.products.destroy', $product) }}"
                            onsubmit="return confirm('Hapus produk ini?')"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-rose-200 bg-white px-3 py-3 text-xs font-bold text-rose-600 transition hover:bg-rose-50"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M6 7h12M9 7V4h6v3m-8 0l1 13h6l1-13M10 11v5m4-5v5"
                                    />
                                </svg>
                                Hapus
                            </button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="px-5 py-16 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#f9eef3] text-[#a56b7e]">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M20 7l-8-4-8 4m16 0v10l-8 4-8-4V7m16 0l-8 4-8-4m8 4v10"
                            />
                        </svg>
                    </div>

                    <h3 class="mt-4 text-lg font-bold text-[#421727]">
                        Belum Ada Produk
                    </h3>

                    <p class="mt-2 text-sm text-[#987582]">
                        Tambahkan produk pertama ke katalog Zalina Fashion.
                    </p>

                    <a
                        href="{{ route('admin.products.create') }}"
                        class="mt-5 inline-flex items-center justify-center rounded-xl bg-[#711f3c] px-4 py-3 text-sm font-bold text-white"
                    >
                        Tambah Produk
                    </a>
                </div>
            @endforelse
        </div>

        {{-- PAGINATION --}}
        @if($products->hasPages())
            <div class="border-t border-[#f0e5e9] bg-[#fffdfd] px-4 py-4 sm:px-6">
                {{ $products->links() }}
            </div>
        @endif
    </section>
</div>
@endsection