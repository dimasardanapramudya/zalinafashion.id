@extends('layouts.store')

@section('title', ($category->name ?? 'Kategori') . ' — Zalina Fashion')

@section('content')

@php
$categoryName = (string) ($category->name ?? 'Kategori');
$categorySlug = $category->slug ?? null;
$categoryDescription = trim((string) ($category->description ?? ''));


$productItems = $products ?? collect();

$productCount = method_exists($products, 'total')
    ? $products->total()
    : $productItems->count();

$categoryImage = !empty($category->image)
    ? (
        str_starts_with($category->image, 'http')
            ? $category->image
            : asset('storage/' . ltrim($category->image, '/'))
    )
    : null;


@endphp

<style>
    .zf-category-detail {
        --zf-maroon: #631f2b;
        --zf-maroon-dark: #4a1520;
        --zf-deep: #2d0810;
        --zf-gold: #b98a3d;
        --zf-gold-light: #d9ad57;
        --zf-cream: #fbf6f1;
        --zf-line: #eadde0;
    }

    .zf-product-card {
        transition:
            transform .3s cubic-bezier(.22,1,.36,1),
            box-shadow .3s ease,
            border-color .3s ease;
    }

    .zf-product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 18px 42px rgba(45,8,16,.10);
        border-color: rgba(185,138,61,.55);
    }

    .zf-product-card img {
        transition: transform .6s cubic-bezier(.22,1,.36,1);
    }

    .zf-product-card:hover img {
        transform: scale(1.05);
    }

    .zf-product-arrow {
        transition:
            transform .3s ease,
            background-color .3s ease,
            color .3s ease;
    }

    .zf-product-card:hover .zf-product-arrow {
        transform: translateX(3px);
    }

    .zf-category-fade {
        animation: zfCategoryFade .55s cubic-bezier(.22,1,.36,1) both;
    }

    @keyframes zfCategoryFade {
        from {
            opacity: 0;
            transform: translateY(12px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .zf-category-fade {
            animation: none;
        }

        .zf-product-card,
        .zf-product-card img,
        .zf-product-arrow {
            transition: none;
        }
    }
</style>

<div class="zf-category-detail min-h-screen bg-[#fbf6f1] text-[#2d0810]">


{{-- ============================================================
     CATEGORY HEADER
     Tanpa hero besar dan tanpa breadcrumb.
============================================================ --}}

<section class="bg-[#fbf6f1]">

    <div class="mx-auto max-w-7xl px-5 pb-8 pt-8 sm:px-8 sm:pb-10 sm:pt-10">

        <div class="zf-category-fade flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

            <div class="max-w-3xl">

                <div class="mb-3 flex items-center gap-3">

                    <span class="h-px w-8 bg-[#b98a3d]"></span>

                    <span class="text-[9px] font-bold uppercase tracking-[.30em] text-[#b98a3d]">
                        Zalina Collection
                    </span>

                </div>

                <h1 class="font-serif text-4xl leading-tight text-[#2d0810] sm:text-5xl lg:text-6xl">
                    {{ $categoryName }}
                </h1>

                @if($categoryDescription !== '')

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-[#806b72] sm:text-base">
                        {{ $categoryDescription }}
                    </p>

                @else

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-[#806b72] sm:text-base">
                        Temukan koleksi {{ strtolower($categoryName) }} pilihan Zalina Fashion.
                    </p>

                @endif

            </div>


            <div class="flex items-center gap-3">

                <div class="h-9 w-px bg-[#eadde0]"></div>

                <div>

                    <p class="font-serif text-2xl text-[#631f2b]">
                        {{ $productCount }}
                    </p>

                    <p class="text-[9px] font-bold uppercase tracking-[.16em] text-[#806b72]">
                        Produk
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>


{{-- ============================================================
     PRODUCTS
============================================================ --}}

<section id="produk" class="pb-14 sm:pb-20">

    <div class="mx-auto max-w-7xl px-5 sm:px-8">


        {{-- ====================================================
             SEARCH & SORT
        ==================================================== --}}

        <form
            method="GET"
            action="{{ url()->current() }}"
            class="zf-category-fade mb-8 rounded-[1.25rem] border border-[#eadde0] bg-white p-3 shadow-[0_8px_30px_rgba(45,8,16,.035)]"
        >

            <div class="grid gap-3 lg:grid-cols-[1fr_auto]">

                <div class="relative">

                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Cari produk di kategori ini..."
                        class="h-12 w-full rounded-xl border border-[#eadde0] bg-[#fbf6f1] px-4 pr-12 text-sm text-[#2d0810] outline-none transition placeholder:text-[#9b898e] focus:border-[#b98a3d] focus:ring-2 focus:ring-[#b98a3d]/10"
                    >

                    <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-[#806b72]">
                        ⌕
                    </span>

                </div>


                <div class="flex gap-3">

                    <select
                        name="sort"
                        onchange="this.form.submit()"
                        class="h-12 min-w-[165px] rounded-xl border border-[#eadde0] bg-[#fbf6f1] px-4 text-sm text-[#2d0810] outline-none transition focus:border-[#b98a3d] focus:ring-2 focus:ring-[#b98a3d]/10"
                    >

                        <option value="latest" @selected(!request('sort') || request('sort') === 'latest')>
                            Terbaru
                        </option>

                        <option value="price_low" @selected(request('sort') === 'price_low')>
                            Harga Terendah
                        </option>

                        <option value="price_high" @selected(request('sort') === 'price_high')>
                            Harga Tertinggi
                        </option>

                        <option value="name" @selected(request('sort') === 'name')>
                            Nama A-Z
                        </option>

                    </select>


                    <button
                        type="submit"
                        class="h-12 rounded-xl bg-[#631f2b] px-6 text-sm font-semibold text-white transition hover:bg-[#4a1520]"
                    >
                        Cari
                    </button>

                </div>

            </div>

        </form>


        {{-- ====================================================
             SECTION TITLE
        ==================================================== --}}

        @if($productItems->count())

            <div class="zf-category-fade mb-6 flex items-end justify-between gap-5">

                <div>

                    <p class="text-[9px] font-bold uppercase tracking-[.30em] text-[#b98a3d]">
                        Collection
                    </p>

                    <h2 class="mt-1 font-serif text-2xl text-[#2d0810] sm:text-3xl">
                        {{ $categoryName }}
                    </h2>

                </div>

                <a
                    href="{{ route('category.index') }}"
                    class="hidden text-xs font-semibold text-[#631f2b] transition hover:text-[#b98a3d] sm:block"
                >
                    ← Semua Kategori
                </a>

            </div>


            {{-- ====================================================
                 PRODUCT GRID
            ==================================================== --}}

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">

                @foreach($productItems as $product)

                    @php
                        $productImage = !empty($product->image)
                            ? asset('storage/' . ltrim($product->image, '/'))
                            : null;
                    @endphp

                    <a
                        href="{{ route('product.show', $product) }}"
                        class="zf-product-card group overflow-hidden rounded-[1.25rem] border border-[#eadde0] bg-white"
                    >

                        {{-- IMAGE --}}

                        <div class="relative aspect-[4/5] overflow-hidden bg-[#f3ece7]">

                            @if($productImage)

                                <img
                                    src="{{ $productImage }}"
                                    alt="{{ $product->name }}"
                                    class="h-full w-full object-cover"
                                    loading="lazy"
                                >

                            @else

                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#f5ece3] to-[#eadde0]">

                                    <span class="font-serif text-7xl text-[#631f2b]/15">
                                        Z
                                    </span>

                                </div>

                            @endif


                            <span class="absolute left-3 top-3 rounded-full border border-white/40 bg-[#fbf6f1]/90 px-2.5 py-1.5 text-[8px] font-bold uppercase tracking-[.12em] text-[#631f2b] shadow-sm backdrop-blur">
                                Zalina
                            </span>

                        </div>


                        {{-- PRODUCT INFO --}}

                        <div class="p-4 sm:p-5">

                            <h3 class="line-clamp-2 min-h-[2.8rem] font-serif text-lg leading-tight text-[#2d0810] transition group-hover:text-[#631f2b]">
                                {{ $product->name }}
                            </h3>


                            <div class="mt-4 flex items-center justify-between gap-2">

                                <span class="text-sm font-bold text-[#631f2b] sm:text-base">
                                    Rp {{ number_format((float) $product->price, 0, ',', '.') }}
                                </span>

                                <span class="zf-product-arrow flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[#eadde0] text-xs text-[#631f2b] group-hover:border-[#b98a3d] group-hover:bg-[#b98a3d] group-hover:text-[#2d0810]">
                                    →
                                </span>

                            </div>

                        </div>

                    </a>

                @endforeach

            </div>


            {{-- ====================================================
                 PAGINATION
            ==================================================== --}}

            @if(method_exists($productItems, 'hasPages') && $productItems->hasPages())

                <div class="mt-10 flex justify-center border-t border-[#eadde0] pt-7">

                    {{ $productItems->links() }}

                </div>

            @endif


        @else

            {{-- ====================================================
                 EMPTY STATE
            ==================================================== --}}

            <div class="zf-category-fade rounded-[1.75rem] border border-[#eadde0] bg-white px-6 py-20 text-center shadow-sm">

                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-[#f5ece3] text-3xl text-[#631f2b]">
                    ⌕
                </div>

                <h2 class="mt-6 font-serif text-2xl text-[#2d0810] sm:text-3xl">
                    Produk Tidak Ditemukan
                </h2>

                <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-[#806b72]">
                    Belum ada produk yang sesuai dengan pencarianmu di kategori {{ $categoryName }}.
                </p>

                <a
                    href="{{ route('category.show', ['category' => $categorySlug]) }}"
                    class="mt-7 inline-flex rounded-full bg-[#631f2b] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#4a1520]"
                >
                    Lihat Semua Produk
                </a>

            </div>

        @endif

    </div>

</section>


{{-- ============================================================
     CTA
============================================================ --}}

<section class="bg-[#631f2b]">

    <div class="mx-auto flex max-w-7xl flex-col gap-5 px-5 py-10 sm:px-8 lg:flex-row lg:items-center lg:justify-between">

        <div>

            <p class="text-[9px] font-bold uppercase tracking-[.32em] text-[#d9ad57]">
                Zalina Fashion
            </p>

            <h2 class="mt-1 font-serif text-2xl text-white sm:text-3xl">
                Temukan gaya yang terasa seperti kamu.
            </h2>

        </div>

        <a
            href="{{ route('shop') }}"
            class="inline-flex w-fit items-center gap-3 rounded-full border border-[#d9ad57]/60 px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#b98a3d] hover:text-[#2d0810]"
        >
            Lihat Semua Produk →
        </a>

    </div>

</section>


</div>

@endsection
