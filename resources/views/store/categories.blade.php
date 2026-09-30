@extends('layouts.store')

@section('title', 'Kategori — Zalina Fashion')

@section('content')

@php
$displayCategories = collect($categories ?? [])
->filter(fn ($category) => (bool) ($category->is_active ?? false))
->sortBy(fn ($category) => $category->sort_order ?? 999999)
->values();


$categoryCount = $displayCategories->count();

$resolveImage = function ($category) {
    if (empty($category->image)) {
        return null;
    }

    return str_starts_with($category->image, 'http')
        ? $category->image
        : asset('storage/' . ltrim($category->image, '/'));
};


@endphp

<style>
    .zf-category-page {
        --zf-maroon: #631f2b;
        --zf-maroon-dark: #4a1520;
        --zf-maroon-deep: #2d0810;
        --zf-gold: #b98a3d;
        --zf-gold-light: #d9ad57;
        --zf-cream: #fbf6f1;
        --zf-cream-deep: #f5ece3;
        --zf-line: #eadde0;
    }

    .zf-category-card {
        transition:
            transform .35s cubic-bezier(.22,1,.36,1),
            box-shadow .35s ease,
            border-color .35s ease;
    }

    .zf-category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 45px rgba(45, 8, 16, .10);
        border-color: rgba(185, 138, 61, .50);
    }

    .zf-category-card img {
        transition: transform .7s cubic-bezier(.22,1,.36,1);
    }

    .zf-category-card:hover img {
        transform: scale(1.05);
    }

    .zf-arrow {
        transition:
            transform .3s ease,
            background-color .3s ease,
            color .3s ease;
    }

    .zf-category-card:hover .zf-arrow {
        transform: translateX(3px);
    }

    @keyframes zfCategoryReveal {
        from {
            opacity: 0;
            transform: translateY(14px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .zf-category-reveal {
        animation: zfCategoryReveal .6s cubic-bezier(.22,1,.36,1) both;
    }

    .zf-category-delay-1 {
        animation-delay: .06s;
    }

    .zf-category-delay-2 {
        animation-delay: .12s;
    }

    .zf-category-delay-3 {
        animation-delay: .18s;
    }

    .zf-category-delay-4 {
        animation-delay: .24s;
    }

    @media (prefers-reduced-motion: reduce) {
        .zf-category-reveal {
            animation: none;
        }

        .zf-category-card,
        .zf-category-card img,
        .zf-arrow {
            transition: none;
        }
    }
</style>

<div class="zf-category-page min-h-screen bg-[#fbf6f1] text-[#2d0810]">


{{-- ============================================================
     CATEGORY CONTENT
     Langsung tampil setelah navbar/layout.
============================================================ --}}

<section class="bg-[#fbf6f1] py-8 sm:py-10 lg:py-12">

    <div class="mx-auto max-w-7xl px-5 sm:px-8">

        @if($displayCategories->isNotEmpty())

            {{-- ====================================================
                 HEADER RINGKAS
                 Tidak ada hero / breadcrumb / judul halaman besar.
            ==================================================== --}}

            <div class="zf-category-reveal mb-8 flex items-end justify-between gap-5 sm:mb-10">

                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[.32em] text-[#b98a3d]">
                        Zalina Fashion
                    </p>

                    <h1 class="mt-2 font-serif text-3xl leading-tight text-[#2d0810] sm:text-4xl">
                        Pilih Kategori
                    </h1>
                </div>

                <div class="hidden text-right sm:block">
                    <span class="text-xs text-[#806b72]">
                        {{ $categoryCount }}
                        {{ $categoryCount === 1 ? 'kategori' : 'kategori' }}
                    </span>
                </div>

            </div>


            {{-- ====================================================
                 GRID KATEGORI
            ==================================================== --}}

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 sm:gap-5 lg:grid-cols-3 lg:gap-6">

                @foreach($displayCategories as $index => $category)

                    @php
                        $categoryImage = $resolveImage($category);

                        $categoryDescription = trim(
                            (string) ($category->description ?? '')
                        );
                    @endphp

                    <a
                        href="{{ route('category.show', ['category' => $category->slug]) }}"
                        class="zf-category-card zf-category-reveal zf-category-delay-{{ min(($index % 4) + 1, 4) }} group overflow-hidden rounded-[1.35rem] border border-[#eadde0] bg-white"
                    >

                        {{-- IMAGE --}}

                        <div class="relative aspect-[4/3] overflow-hidden bg-[#f3ece7]">

                            @if($categoryImage)

                                <img
                                    src="{{ $categoryImage }}"
                                    alt="{{ $category->name }}"
                                    class="h-full w-full object-cover"
                                    loading="{{ $index < 3 ? 'eager' : 'lazy' }}"
                                >

                            @else

                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#f5ece3] to-[#eadde0]">

                                    <span class="font-serif text-7xl text-[#631f2b]/15">
                                        Z
                                    </span>

                                </div>

                            @endif

                            {{-- NUMBER --}}

                            <div class="absolute left-3 top-3 flex h-8 min-w-8 items-center justify-center rounded-full border border-white/50 bg-[#fbf6f1]/90 px-2.5 text-[9px] font-bold tracking-[.12em] text-[#631f2b] shadow-sm backdrop-blur">

                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}

                            </div>

                        </div>


                        {{-- CONTENT --}}

                        <div class="p-4 sm:p-5">

                            <div class="flex items-center justify-between gap-3">

                                <div class="min-w-0">

                                    <h2 class="truncate font-serif text-xl leading-tight text-[#2d0810] transition group-hover:text-[#631f2b] sm:text-2xl">

                                        {{ $category->name }}

                                    </h2>

                                    @if($categoryDescription !== '')

                                        <p class="mt-1.5 line-clamp-1 text-xs leading-5 text-[#806b72] sm:text-sm">

                                            {{ $categoryDescription }}

                                        </p>

                                    @else

                                        <p class="mt-1.5 line-clamp-1 text-xs leading-5 text-[#806b72] sm:text-sm">

                                            Jelajahi koleksi {{ strtolower($category->name) }}

                                        </p>

                                    @endif

                                </div>


                                {{-- ARROW --}}

                                <span class="zf-arrow flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-[#eadde0] text-sm text-[#631f2b] group-hover:border-[#b98a3d] group-hover:bg-[#b98a3d] group-hover:text-[#2d0810]">

                                    →

                                </span>

                            </div>

                        </div>

                    </a>

                @endforeach

            </div>


        @else

            {{-- ====================================================
                 EMPTY STATE
            ==================================================== --}}

            <div class="zf-category-reveal rounded-[1.75rem] border border-[#eadde0] bg-white px-6 py-20 text-center shadow-sm">

                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-[#f5ece3] text-3xl text-[#631f2b]">
                    ⌕
                </div>

                <h2 class="mt-6 font-serif text-2xl text-[#2d0810] sm:text-3xl">
                    Belum Ada Kategori
                </h2>

                <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-[#806b72]">
                    Saat ini belum ada kategori aktif yang tersedia.
                </p>

                <a
                    href="{{ route('home') }}"
                    class="mt-7 inline-flex rounded-full bg-[#631f2b] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#4a1520]"
                >
                    Kembali ke Beranda
                </a>

            </div>

        @endif

    </div>

</section>


</div>

@endsection
