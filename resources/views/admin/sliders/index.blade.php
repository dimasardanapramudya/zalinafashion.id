@extends('layouts.admin')

@section('title', 'Slider & Iklan')

@section('content')

@php
    $sliderCount = $sliders?->count() ?? 0;
    $activeCount = $sliders?->where('is_active', true)->count() ?? 0;
    $inactiveCount = $sliderCount - $activeCount;
@endphp

<div class="mx-auto max-w-7xl space-y-8">

    {{-- HEADER --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-maroon-900 via-maroon-800 to-maroon-700 px-6 py-8 text-white shadow-lg sm:px-8">
        <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-maroon-100">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="4" width="18" height="16" rx="2"/>
                        <path d="M3 9h18M8 4v5M16 4v5"/>
                    </svg>
                    Store Marketing
                </div>

                <h1 class="font-serif text-3xl font-semibold sm:text-4xl">
                    Slider & Iklan
                </h1>

                <p class="mt-3 text-sm leading-relaxed text-maroon-100 sm:text-base">
                    Kelola banner promosi, gambar, urutan tampil,
                    tema, dan tombol CTA tanpa mengubah source code.
                </p>
            </div>

            <a
                href="{{ route('admin.sliders.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-full bg-white px-5 py-3 text-sm font-bold text-maroon-800 shadow-sm transition hover:bg-maroon-50 focus:outline-none focus:ring-2 focus:ring-white/60"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14"/>
                </svg>

                Tambah Slider
            </a>
        </div>

        <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full border-[30px] border-white/10"></div>
        <div class="pointer-events-none absolute -bottom-32 right-24 h-72 w-72 rounded-full border-[24px] border-white/5"></div>
    </div>

    {{-- SUMMARY --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-maroon-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-slate-500">
                    Total Slider
                </p>

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-maroon-50 text-maroon-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="4" width="18" height="16" rx="2"/>
                        <path d="M3 9h18"/>
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-3xl font-bold text-maroon-900">
                {{ $sliderCount }}
            </p>

            <p class="mt-1 text-xs text-slate-500">
                Semua banner tersimpan
            </p>
        </div>

        <div class="rounded-2xl border border-green-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-slate-500">
                    Slider Aktif
                </p>

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-50 text-green-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M5 12l4 4L19 6"/>
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-3xl font-bold text-green-700">
                {{ $activeCount }}
            </p>

            <p class="mt-1 text-xs text-slate-500">
                Ditampilkan di website
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-slate-500">
                    Slider Nonaktif
                </p>

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-3xl font-bold text-slate-700">
                {{ $inactiveCount }}
            </p>

            <p class="mt-1 text-xs text-slate-500">
                Tidak ditampilkan sementara
            </p>
        </div>
    </div>

    {{-- SLIDER GRID --}}
    <div class="space-y-4">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="font-serif text-2xl font-semibold text-maroon-900">
                    Daftar Slider
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Atur urutan dan isi banner promosi toko.
                </p>
            </div>

            <p class="text-xs font-semibold uppercase tracking-wider text-maroon-400">
                {{ $sliderCount }} Banner
            </p>
        </div>

        @if($sliderCount > 0)
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach($sliders as $slider)
                    @php
                        $imagePath = $slider->image ?? null;
                        $theme = $slider->theme ?: 'default';
                        $title = $slider->title ?: 'Tanpa Judul';
                        $description = $slider->description ?: 'Belum ada deskripsi slider.';
                        $sortOrder = $slider->sort_order ?? 0;
                        $isActive = (bool) $slider->is_active;
                    @endphp

                    <article class="group overflow-hidden rounded-3xl border border-maroon-100 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-xl">

                        {{-- IMAGE --}}
                        <div class="relative h-52 overflow-hidden bg-gradient-to-br from-maroon-100 via-rose-100 to-amber-100">
                            @if($imagePath)
                                <img
                                    src="{{ asset('storage/' . ltrim($imagePath, '/')) }}"
                                    alt="{{ $title }}"
                                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                    loading="lazy"
                                >
                            @else
                                <div class="flex h-full items-center justify-center">
                                    <div class="text-center">
                                        <div class="font-serif text-7xl font-bold text-maroon-300">
                                            Z
                                        </div>

                                        <p class="mt-1 text-xs font-semibold uppercase tracking-[0.2em] text-maroon-400">
                                            Zalina Fashion
                                        </p>
                                    </div>
                                </div>
                            @endif

                            {{-- IMAGE OVERLAY --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>

                            {{-- THEME --}}
                            <div class="absolute left-4 top-4 rounded-full border border-white/50 bg-white/90 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-maroon-800 shadow-sm">
                                {{ $theme }}
                            </div>

                            {{-- STATUS --}}
                            <div class="absolute right-4 top-4 rounded-full px-3 py-1 text-[11px] font-bold shadow-sm {{ $isActive ? 'bg-green-100 text-green-700' : 'bg-white/90 text-slate-500' }}">
                                <span class="mr-1 inline-block h-1.5 w-1.5 rounded-full {{ $isActive ? 'bg-green-500' : 'bg-slate-400' }}"></span>
                                {{ $isActive ? 'Aktif' : 'Nonaktif' }}
                            </div>

                            {{-- SORT ORDER --}}
                            <div class="absolute bottom-4 left-4 rounded-full bg-black/50 px-3 py-1 text-xs font-semibold text-white backdrop-blur-sm">
                                Urutan #{{ $sortOrder }}
                            </div>
                        </div>

                        {{-- CONTENT --}}
                        <div class="p-5">
                            <div class="min-h-[116px]">
                                <h3 class="line-clamp-2 font-serif text-xl font-semibold leading-snug text-maroon-900">
                                    {{ $title }}
                                </h3>

                                <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-slate-500">
                                    {{ $description }}
                                </p>
                            </div>

                            {{-- CTA INFO --}}
                            @if(!empty($slider->button_text) || !empty($slider->button_url))
                                <div class="mt-4 rounded-xl bg-maroon-50 px-3 py-2.5">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-maroon-400">
                                        CTA Button
                                    </p>

                                    <p class="mt-1 truncate text-xs font-semibold text-maroon-700">
                                        {{ $slider->button_text ?: 'Tanpa teks tombol' }}
                                    </p>

                                    @if(!empty($slider->button_url))
                                        <p class="mt-0.5 truncate text-[11px] text-maroon-500">
                                            {{ $slider->button_url }}
                                        </p>
                                    @endif
                                </div>
                            @endif

                            {{-- ACTIONS --}}
                            <div class="mt-5 flex items-center justify-between gap-3 border-t border-maroon-100 pt-4">
                                <a
                                    href="{{ route('admin.sliders.edit', $slider) }}"
                                    class="inline-flex items-center gap-2 rounded-full bg-maroon-50 px-4 py-2 text-sm font-semibold text-maroon-700 transition hover:bg-maroon-100"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M12 20h9"/>
                                        <path d="M16.5 3.5a2.1 2.1 0 013 3L8 18l-4 1 1-4L16.5 3.5z"/>
                                    </svg>

                                    Edit
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('admin.sliders.destroy', $slider) }}"
                                    onsubmit="return confirm('Hapus slider ini? Data yang dihapus tidak dapat dikembalikan.')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M3 6h18M9 6V4h6v2M8 10v7M12 10v7M16 10v7M5 6l1 15h12l1-15"/>
                                        </svg>

                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            {{-- EMPTY STATE --}}
            <div class="rounded-3xl border border-dashed border-maroon-200 bg-white px-6 py-16 text-center shadow-sm">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-maroon-50 text-maroon-400">
                    <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="4" width="18" height="16" rx="2"/>
                        <path d="M3 9h18M8 4v5M16 4v5"/>
                    </svg>
                </div>

                <h3 class="mt-5 font-serif text-2xl font-semibold text-maroon-900">
                    Belum Ada Slider
                </h3>

                <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-slate-500">
                    Tambahkan banner pertama untuk menampilkan promosi,
                    koleksi terbaru, atau informasi penting di halaman utama.
                </p>

                <a
                    href="{{ route('admin.sliders.create') }}"
                    class="mt-6 inline-flex items-center gap-2 rounded-full bg-maroon-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-maroon-800"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>

                    Tambah Slider Pertama
                </a>
            </div>
        @endif
    </div>

</div>

@endsection