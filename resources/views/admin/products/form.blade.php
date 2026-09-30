@extends('layouts.admin')

@section('title', $product->exists ? 'Edit Produk' : 'Tambah Produk')

@section('content')

<style>
    #stock.is-auto-stock {
        cursor: not-allowed;
        background: #f1f5f9;
        color: #64748b;
    }
</style>

@php
    $isEdit = $product->exists;
@endphp

<div class="mx-auto max-w-6xl space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-[0.22em] text-[#a56b7e]">
                <span class="h-2 w-2 rounded-full bg-[#c9a45c]"></span>
                Zalina Fashion
            </div>

            <h1 class="font-serif text-3xl font-bold tracking-tight text-[#42101f] sm:text-4xl">
                {{ $isEdit ? 'Edit Produk' : 'Tambah Produk' }}
            </h1>

            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                {{ $isEdit
                    ? 'Perbarui informasi, harga, stok, dan tampilan produk Zalina Fashion.'
                    : 'Tambahkan produk baru ke katalog Zalina Fashion dengan informasi yang lengkap.'
                }}
            </p>
        </div>

        <a
            href="{{ route('admin.products.index') }}"
            class="inline-flex w-fit items-center gap-2 rounded-xl border border-[#eadcdf] bg-white px-4 py-2.5 text-sm font-bold text-[#8b1e3f] shadow-sm transition hover:border-[#c9a45c] hover:bg-[#fffafc]"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.8"
                    d="M10 19l-7-7m0 0l7-7m-7 7h18"
                />
            </svg>

            Kembali ke Produk
        </a>
    </div>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- ERROR VALIDATION --}}
    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-5">
            <div class="flex items-start gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M12 9v4m0 4h.01M10.3 3.8L2.8 17a2 2 0 001.7 3h15a2 2 0 001.7-3L13.7 3.8a2 2 0 00-3.4 0z"
                        />
                    </svg>
                </div>

                <div>
                    <h3 class="font-bold text-red-800">
                        Terjadi kesalahan
                    </h3>

                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- FORM UTAMA --}}
    <form
        method="POST"
        action="{{ $isEdit ? route('admin.products.update', $product) : route('admin.products.store') }}"
        enctype="multipart/form-data"
        class="space-y-6"
    >
        @csrf

        @if($isEdit)
            @method('PUT')
        @endif

        <div class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">

            {{-- INFORMASI PRODUK --}}
            <section class="overflow-hidden rounded-3xl border border-[#eadcdf] bg-white shadow-sm">

                <div class="border-b border-[#f0e5e8] bg-gradient-to-r from-[#fffafc] to-white px-6 py-5 sm:px-7">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#f8e9ee] text-[#8b1e3f]">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M20 7l-8-4-8 4m16 0v10l-8 4-8-4V7m16 0l-8 4m-8-4l8 4m0 0v10"
                                />
                            </svg>
                        </div>

                        <div>
                            <h2 class="font-serif text-xl font-bold text-[#42101f]">
                                Informasi Produk
                            </h2>

                            <p class="text-xs text-slate-500">
                                Detail utama yang akan ditampilkan di katalog.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-5 p-6 sm:p-7">

                    {{-- NAMA PRODUK --}}
                    <div>
                        <label for="name" class="mb-2 block text-sm font-bold text-[#42101f]">
                            Nama Produk <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name', $product->name) }}"
                            required
                            maxlength="255"
                            placeholder="Contoh: Zalina Signature Series"
                            class="w-full rounded-2xl border border-[#eadcdf] bg-[#fffdfd] px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-[#8b1e3f] focus:ring-4 focus:ring-[#8b1e3f]/10"
                        >
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">

                        {{-- KATEGORI --}}
                        <div>
                            <label for="category_id" class="mb-2 block text-sm font-bold text-[#42101f]">
                                Kategori
                            </label>

                            <select
                                id="category_id"
                                name="category_id"
                                class="w-full rounded-2xl border border-[#eadcdf] bg-[#fffdfd] px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#8b1e3f] focus:ring-4 focus:ring-[#8b1e3f]/10"
                            >
                                <option value="">— Pilih kategori —</option>

                                @foreach($categories as $category)
                                    <option
                                        value="{{ $category->id }}"
                                        @selected((string) old('category_id', $product->category_id) === (string) $category->id)
                                    >
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- SKU --}}
                        <div>
                            <label for="sku" class="mb-2 block text-sm font-bold text-[#42101f]">
                                SKU
                            </label>

                            <input
                                id="sku"
                                type="text"
                                name="sku"
                                value="{{ old('sku', $product->sku) }}"
                                maxlength="100"
                                placeholder="Contoh: ZLN-001"
                                class="w-full rounded-2xl border border-[#eadcdf] bg-[#fffdfd] px-4 py-3 text-sm uppercase text-slate-700 outline-none transition placeholder:normal-case placeholder:text-slate-400 focus:border-[#8b1e3f] focus:ring-4 focus:ring-[#8b1e3f]/10"
                            >
                        </div>
                    </div>

                    {{-- HARGA --}}
                    <div class="rounded-2xl border border-[#eadcdf] bg-[#fffafc] p-4">
                        <div class="mb-4 flex items-center gap-2">
                            <svg class="h-4 w-4 text-[#c9a45c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M12 1v22m5-18H9a4 4 0 000 8h6a4 4 0 010 8H7"
                                />
                            </svg>

                            <h3 class="text-sm font-bold text-[#42101f]">
                                Harga Produk
                            </h3>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">

                            {{-- HARGA NORMAL --}}
                            <div>
                                <label for="price" class="mb-2 block text-sm font-bold text-[#42101f]">
                                    Harga Normal <span class="text-red-500">*</span>
                                </label>

                                <div class="relative">
                                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">
                                        Rp
                                    </span>

                                    <input
                                        id="price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        name="price"
                                        value="{{ old('price', $product->price) }}"
                                        required
                                        placeholder="0"
                                        class="w-full rounded-2xl border border-[#eadcdf] bg-white py-3 pl-11 pr-4 text-sm text-slate-700 outline-none transition focus:border-[#8b1e3f] focus:ring-4 focus:ring-[#8b1e3f]/10"
                                    >
                                </div>
                            </div>

                            {{-- HARGA DISKON --}}
                            <div>
                                <label for="sale_price" class="mb-2 block text-sm font-bold text-[#42101f]">
                                    Harga Diskon
                                </label>

                                <div class="relative">
                                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">
                                        Rp
                                    </span>

                                    <input
                                        id="sale_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        name="sale_price"
                                        value="{{ old('sale_price', $product->sale_price) }}"
                                        placeholder="Opsional"
                                        class="w-full rounded-2xl border border-[#eadcdf] bg-white py-3 pl-11 pr-4 text-sm text-slate-700 outline-none transition focus:border-[#8b1e3f] focus:ring-4 focus:ring-[#8b1e3f]/10"
                                    >
                                </div>
                            </div>
                        </div>

                        <p class="mt-3 text-xs leading-5 text-slate-500">
                            Kosongkan harga diskon jika produk tidak sedang memiliki promo.
                        </p>
                    </div>

                    {{-- STOK --}}
                    <div>
                        <label for="stock" class="mb-2 block text-sm font-bold text-[#42101f]">
                            Stok Produk <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <input
                                id="stock"
                                type="number"
                                min="0"
                                name="stock"
                                value="{{ old('stock', $product->stock ?? 0) }}"
                                required
                                class="w-full rounded-2xl border border-[#eadcdf] bg-[#fffdfd] px-4 py-3 pr-20 text-sm text-slate-700 outline-none transition focus:border-[#8b1e3f] focus:ring-4 focus:ring-[#8b1e3f]/10"
                            >

                            <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">
                                item
                            </span>
                        </div>

                        <p id="stockAutoHint" class="mt-2 hidden text-xs leading-5 text-slate-500">
                            Dihitung otomatis dari total stok semua varian aktif di bawah — tidak bisa diubah manual selama produk ini punya varian.
                        </p>
                    </div>

                    {{-- BERAT --}}
                    <div>
                        <label for="weight" class="mb-2 block text-sm font-bold text-[#42101f]">
                            Berat Produk <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <input
                                id="weight"
                                type="number"
                                min="1"
                                name="weight"
                                value="{{ old('weight', $product->weight ?? '') }}"
                                required
                                placeholder="Contoh: 250"
                                class="w-full rounded-2xl border border-[#eadcdf] bg-[#fffdfd] px-4 py-3 pr-20 text-sm text-slate-700 outline-none transition focus:border-[#8b1e3f] focus:ring-4 focus:ring-[#8b1e3f]/10"
                            >

                            <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">
                                gram
                            </span>
                        </div>

                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            Wajib diisi — dipakai untuk menghitung ongkos kirim saat checkout. Produk dengan berat 0/kosong akan membuat pelanggan gagal checkout.
                        </p>
                    </div>

                    {{-- DESKRIPSI --}}
                    <div>
                        <label for="description" class="mb-2 block text-sm font-bold text-[#42101f]">
                            Deskripsi Produk
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            rows="6"
                            placeholder="Tulis deskripsi bahan, ukuran, warna, dan keunggulan produk..."
                            class="w-full resize-y rounded-2xl border border-[#eadcdf] bg-[#fffdfd] px-4 py-3 text-sm leading-6 text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-[#8b1e3f] focus:ring-4 focus:ring-[#8b1e3f]/10"
                        >{{ old('description', $product->description) }}</textarea>
                    </div>

                </div>
            </section>

            {{-- GAMBAR DAN STATUS --}}
            <div class="space-y-6">

                {{-- FOTO PRODUK --}}
                <section class="overflow-hidden rounded-3xl border border-[#eadcdf] bg-white shadow-sm">

                    <div class="border-b border-[#f0e5e8] bg-gradient-to-r from-[#fffafc] to-white px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#f8e9ee] text-[#8b1e3f]">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.6"
                                        d="M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 0h10M7 16l3-3 3 3 2-2 3 3"
                                    />
                                </svg>
                            </div>

                            <div>
                                <h2 class="font-serif text-xl font-bold text-[#42101f]">
                                    Foto Produk
                                </h2>

                                <p class="text-xs text-slate-500">
                                    Gunakan foto yang jelas dan berkualitas.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">

                        {{-- GAMBAR SAAT INI --}}
                        @if($isEdit && !empty($product->image))
                            <div class="mb-5">
                                <p class="mb-3 text-xs font-bold uppercase tracking-[0.16em] text-[#a56b7e]">
                                    Gambar Saat Ini
                                </p>

                                <div class="overflow-hidden rounded-2xl border border-[#eadcdf] bg-[#fffafc]">
                                    <img
                                        src="{{ asset('storage/' . ltrim($product->image, '/')) }}"
                                        alt="{{ $product->name }}"
                                        class="h-72 w-full object-contain p-3"
                                        onerror="this.style.display='none'; document.getElementById('currentImageError').classList.remove('hidden');"
                                    >
                                </div>

                                <p id="currentImageError" class="mt-2 hidden text-xs text-red-500">
                                    Gambar lama tidak ditemukan pada storage.
                                </p>
                            </div>
                        @endif

                        {{-- UPLOAD AREA --}}
                        <label
                            for="image"
                            class="group block cursor-pointer rounded-2xl border-2 border-dashed border-[#dec4ce] bg-[#fffafc] p-5 text-center transition hover:border-[#8b1e3f] hover:bg-[#fff5f8]"
                        >
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-[#8b1e3f] shadow-sm transition group-hover:scale-105">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.6"
                                        d="M12 16V4m0 0L7 9m5-5l5 5M5 14v5a2 2 0 002 2h10a2 2 0 002-2v-5"
                                    />
                                </svg>
                            </div>

                            <p class="mt-4 text-sm font-bold text-[#42101f]">
                                Klik untuk memilih gambar
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                JPG, JPEG, PNG, atau WEBP
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                Maksimal 50 MB
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                {{ $isEdit
                                    ? 'Kosongkan jika tidak ingin mengganti gambar.'
                                    : 'Pilih gambar utama produk.'
                                }}
                            </p>
                        </label>

                        <input
                            id="image"
                            type="file"
                            name="image"
                            accept="image/jpeg,image/png,image/jpg,image/webp"
                            class="sr-only"
                        >

                        <p id="imageError" class="mt-3 hidden rounded-xl bg-red-50 px-3 py-2 text-xs font-semibold text-red-600"></p>

                        {{-- PREVIEW GAMBAR BARU --}}
                        <div id="imagePreviewContainer" class="mt-5 hidden">
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#a56b7e]">
                                    Preview Gambar Baru
                                </p>

                                <button
                                    type="button"
                                    id="removeImagePreview"
                                    class="text-xs font-bold text-red-600 transition hover:text-red-800"
                                >
                                    Hapus
                                </button>
                            </div>

                            <div class="overflow-hidden rounded-2xl border border-[#eadcdf] bg-[#fffafc]">
                                <img
                                    id="imagePreview"
                                    src=""
                                    alt="Preview gambar produk"
                                    class="h-72 w-full object-contain p-3"
                                >
                            </div>

                            <p id="imageFileName" class="mt-2 truncate text-xs text-slate-500"></p>
                        </div>
                    </div>
                </section>

                {{-- STATUS PRODUK --}}
                <section class="overflow-hidden rounded-3xl border border-[#eadcdf] bg-white shadow-sm">

                    <div class="border-b border-[#f0e5e8] bg-gradient-to-r from-[#fffafc] to-white px-6 py-5">
                        <h2 class="font-serif text-xl font-bold text-[#42101f]">
                            Pengaturan Produk
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            Atur visibilitas dan prioritas produk.
                        </p>
                    </div>

                    <div class="space-y-4 p-6">

                        {{-- PRODUK AKTIF --}}
                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-[#eadcdf] bg-[#fffafc] p-4 transition hover:border-[#c9a45c]">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                @checked((bool) old('is_active', $product->exists ? $product->is_active : true))
                                class="mt-1 h-4 w-4 rounded border-[#d8b9c5] text-[#8b1e3f] focus:ring-[#8b1e3f]"
                            >

                            <span>
                                <span class="block text-sm font-bold text-[#42101f]">
                                    Produk Aktif
                                </span>

                                <span class="mt-1 block text-xs leading-5 text-slate-500">
                                    Produk dapat ditampilkan dan dibeli oleh customer.
                                </span>
                            </span>
                        </label>

                        {{-- PRODUK UNGGULAN --}}
                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-[#eadcdf] bg-[#fffafc] p-4 transition hover:border-[#c9a45c]">
                            <input
                                type="checkbox"
                                name="is_featured"
                                value="1"
                                @checked((bool) old('is_featured', $product->is_featured ?? false))
                                class="mt-1 h-4 w-4 rounded border-[#d8b9c5] text-[#8b1e3f] focus:ring-[#8b1e3f]"
                            >

                            <span>
                                <span class="block text-sm font-bold text-[#42101f]">
                                    Produk Unggulan
                                </span>

                                <span class="mt-1 block text-xs leading-5 text-slate-500">
                                    Produk diprioritaskan pada bagian produk unggulan.
                                </span>
                            </span>
                        </label>

                    </div>
                </section>
            </div>
        </div>


        {{-- VARIAN PRODUK --}}
        @if($isEdit)
            <section class="overflow-hidden rounded-3xl border border-[#eadcdf] bg-white shadow-sm">
                <div class="border-b border-[#f0e5e8] bg-gradient-to-r from-[#fffafc] to-white px-6 py-5 sm:px-7">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="font-serif text-xl font-bold text-[#42101f]">
                                Varian Produk
                            </h2>
                            <p class="mt-1 text-xs text-slate-500">
                                Tambahkan atau ubah warna, gambar, SKU, harga, dan stok setiap varian.
                            </p>
                        </div>

                        <button
                            type="button"
                            id="addVariant"
                            class="rounded-xl bg-[#8b1e3f] px-4 py-2.5 text-xs font-bold text-white transition hover:bg-[#6f1632]"
                        >
                            + Tambah Varian
                        </button>
                    </div>
                </div>

                <div class="space-y-4 p-6" id="variantsContainer">
                    @forelse($product->variants as $index => $variant)
                        <div class="variant-row rounded-2xl border border-[#eadcdf] bg-[#fffafc] p-4">
                            <input
                                type="hidden"
                                name="variants[{{ $index }}][id]"
                                value="{{ $variant->id }}"
                            >

                            <div class="mb-4 flex items-center justify-between">
                                <p class="text-sm font-bold text-[#42101f]">
                                    Varian {{ $index + 1 }}
                                </p>

                                <button
                                    type="button"
                                    class="removeVariant text-xs font-bold text-red-600 hover:text-red-800"
                                >
                                    Hapus
                                </button>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <label class="mb-1 block text-xs font-bold text-[#42101f]">
                                        Nama Varian
                                    </label>
                                    <input
                                        type="text"
                                        name="variants[{{ $index }}][name]"
                                        value="{{ old('variants.'.$index.'.name', $variant->name) }}"
                                        required
                                        class="w-full rounded-xl border border-[#eadcdf] bg-white px-3 py-2.5 text-sm outline-none focus:border-[#8b1e3f]"
                                        placeholder="Contoh: Hitam"
                                    >
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-bold text-[#42101f]">
                                        SKU
                                    </label>
                                    <input
                                        type="text"
                                        name="variants[{{ $index }}][sku]"
                                        value="{{ old('variants.'.$index.'.sku', $variant->sku) }}"
                                        class="w-full rounded-xl border border-[#eadcdf] bg-white px-3 py-2.5 text-sm outline-none focus:border-[#8b1e3f]"
                                        placeholder="Contoh: ZLN-HITAM"
                                    >
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-bold text-[#42101f]">
                                        Warna
                                    </label>
                                    <input
                                        type="text"
                                        name="variants[{{ $index }}][color]"
                                        value="{{ old('variants.'.$index.'.color', $variant->color) }}"
                                        class="w-full rounded-xl border border-[#eadcdf] bg-white px-3 py-2.5 text-sm outline-none focus:border-[#8b1e3f]"
                                        placeholder="Contoh: Hitam"
                                    >
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-bold text-[#42101f]">
                                        Harga Varian
                                    </label>
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        name="variants[{{ $index }}][price]"
                                        value="{{ old('variants.'.$index.'.price', $variant->price) }}"
                                        class="w-full rounded-xl border border-[#eadcdf] bg-white px-3 py-2.5 text-sm outline-none focus:border-[#8b1e3f]"
                                        placeholder="Kosongkan untuk harga produk"
                                    >
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-bold text-[#42101f]">
                                        Stok
                                    </label>
                                    <input
                                        type="number"
                                        min="0"
                                        name="variants[{{ $index }}][stock]"
                                        value="{{ old('variants.'.$index.'.stock', $variant->stock) }}"
                                        class="w-full rounded-xl border border-[#eadcdf] bg-white px-3 py-2.5 text-sm outline-none focus:border-[#8b1e3f]"
                                    >
                                </div>

                                <div class="flex items-center gap-2 pt-6">
                                    <input
                                        type="checkbox"
                                        name="variants[{{ $index }}][is_active]"
                                        value="1"
                                        @checked((bool) $variant->is_active)
                                        class="h-4 w-4 rounded border-[#d8b9c5] text-[#8b1e3f]"
                                    >
                                    <label class="text-xs font-bold text-[#42101f]">
                                        Varian Aktif
                                    </label>
                                </div>

                                {{-- GAMBAR VARIAN --}}
                                @php
                                    $variantImageSrc = !empty($variant->image)
                                        ? asset('storage/' . ltrim($variant->image, '/'))
                                        : null;
                                @endphp

                                <div class="sm:col-span-2 lg:col-span-3">
                                    <label class="mb-1 block text-xs font-bold text-[#42101f]">
                                        Gambar Varian
                                    </label>

                                    <div class="flex items-start gap-4">
                                        <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-[#eadcdf] bg-white text-[#d8b9c5]">
                                            <img
                                                src="{{ $variantImageSrc }}"
                                                alt="Gambar {{ $variant->name }}"
                                                class="variantImagePreview h-full w-full object-cover {{ $variantImageSrc ? '' : 'hidden' }}"
                                            >
                                            <svg class="variantImagePlaceholder h-8 w-8 {{ $variantImageSrc ? 'hidden' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <rect x="3" y="4" width="18" height="16" rx="2"/>
                                                <circle cx="9" cy="10" r="1.6"/>
                                                <path d="M21 16l-5-5-8 8"/>
                                            </svg>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <input
                                                type="file"
                                                name="variants[{{ $index }}][image]"
                                                accept="image/jpeg,image/png,image/jpg,image/webp"
                                                class="variantImageInput block w-full rounded-xl border border-[#eadcdf] bg-white px-3 py-2 text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-[#f7e9ed] file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-[#8b1e3f]"
                                            >

                                            <p class="variantImageError mt-2 hidden rounded-xl bg-red-50 px-3 py-2 text-xs font-semibold text-red-600"></p>

                                            <p class="mt-2 text-[11px] leading-5 text-slate-500">
                                                Ditampilkan di halaman produk saat customer memilih varian ini.
                                                JPG, PNG, atau WEBP, maksimal 5 MB.
                                            </p>

                                            @if($variantImageSrc)
                                                <label class="mt-2 flex cursor-pointer items-center gap-2 text-xs font-semibold text-red-600">
                                                    <input
                                                        type="checkbox"
                                                        name="variants[{{ $index }}][remove_image]"
                                                        value="1"
                                                        class="removeVariantImage h-4 w-4 rounded border-[#d8b9c5] text-red-600"
                                                    >
                                                    Hapus gambar saat ini
                                                </label>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p id="emptyVariantMessage" class="rounded-xl bg-[#fffafc] p-4 text-sm text-slate-500">
                            Belum ada varian. Klik tombol “Tambah Varian”.
                        </p>
                    @endforelse
                </div>
            </section>
        @endif

        {{-- ACTION FOOTER --}}
        <div class="flex flex-col-reverse gap-3 rounded-3xl border border-[#eadcdf] bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-6">

            <a
                href="{{ route('admin.products.index') }}"
                class="inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-bold text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
            >
                Batal
            </a>

            <button
                type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#8b1e3f] to-[#b34d70] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-[#8b1e3f]/20 transition hover:-translate-y-0.5 hover:shadow-xl"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M5 12l4 4L19 6"
                    />
                </svg>

                {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Produk' }}
            </button>
        </div>
    
                        <div class="mt-5">
                            <label
                                for="images"
                                class="block text-sm font-semibold text-gray-700"
                            >
                                Gambar Slider Produk
                            </label>

                            <p class="mt-1 text-xs text-gray-500">
                                Upload maksimal 10 gambar tambahan. Gambar kosong tidak akan ditampilkan di slider.
                            </p>

                            <input
                                name="images[]"
                                id="images"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                multiple
                                class="mt-3 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm"
                            >

                            <p id="galleryFileStatus" class="mt-2 text-xs text-gray-500"></p>
                        </div>

</form>
</div>

{{-- JAVASCRIPT PREVIEW DAN VALIDASI --}}

<script>
document.addEventListener('DOMContentLoaded', function () {
    const variantsContainer = document.getElementById('variantsContainer');
    const addVariantButton = document.getElementById('addVariant');
    let variantIndex = variantsContainer
        ? variantsContainer.querySelectorAll('.variant-row').length
        : 0;

    /*
    |--------------------------------------------------------------------------
    | STOK PRODUK MENGIKUTI TOTAL STOK VARIAN AKTIF
    |--------------------------------------------------------------------------
    |
    | Sebelumnya field "Stok Produk" diisi manual, benar-benar terpisah dari
    | field stok tiap varian di bawahnya — sehingga dua-duanya gampang tidak
    | sinkron (server sudah dipaksa sinkron saat disimpan, tapi di form
    | sendiri admin masih bisa ketik angka sembarang yang membingungkan).
    |
    | Sekarang: begitu produk ini punya minimal satu baris varian, field
    | "Stok Produk" dikunci (readonly, BUKAN disabled — supaya tetap ikut
    | terkirim saat submit) dan nilainya otomatis dihitung ulang dari
    | penjumlahan stok semua varian yang statusnya "Varian Aktif", setiap
    | kali stok/centang varian diubah, atau varian ditambah/dihapus. Kalau
    | semua baris varian dihapus, field ini kembali bisa diisi manual
    | seperti produk tanpa varian.
    |
    */

    const stockInput = document.getElementById('stock');
    const stockAutoHint = document.getElementById('stockAutoHint');

    function recalculateStockFromVariants() {
        if (!stockInput || !variantsContainer) {
            return;
        }

        const rows = variantsContainer.querySelectorAll('.variant-row');

        if (rows.length === 0) {
            stockInput.readOnly = false;
            stockInput.classList.remove('is-auto-stock');
            stockAutoHint?.classList.add('hidden');
            return;
        }

        let total = 0;

        rows.forEach(function (row) {
            const activeCheckbox = row.querySelector('input[name*="[is_active]"]');
            const stockField = row.querySelector('input[name*="[stock]"]');
            const isActive = activeCheckbox ? activeCheckbox.checked : true;

            if (isActive && stockField) {
                total += parseInt(stockField.value, 10) || 0;
            }
        });

        stockInput.value = total;
        stockInput.readOnly = true;
        stockInput.classList.add('is-auto-stock');
        stockAutoHint?.classList.remove('hidden');
    }

    if (variantsContainer) {
        variantsContainer.addEventListener('input', function (event) {
            if (event.target.matches('input[name*="[stock]"]')) {
                recalculateStockFromVariants();
            }
        });

        variantsContainer.addEventListener('change', function (event) {
            if (event.target.matches('input[name*="[is_active]"]')) {
                recalculateStockFromVariants();
            }
        });
    }

    // Hitung status awal saat halaman pertama dimuat (mode edit dengan varian sudah ada).
    recalculateStockFromVariants();

    if (addVariantButton && variantsContainer) {
        addVariantButton.addEventListener('click', function () {
            const emptyMessage = document.getElementById('emptyVariantMessage');

            if (emptyMessage) {
                emptyMessage.remove();
            }

            const row = document.createElement('div');

            row.className = 'variant-row rounded-2xl border border-[#eadcdf] bg-[#fffafc] p-4';

            row.innerHTML = `
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-bold text-[#42101f]">
                        Varian Baru
                    </p>

                    <button
                        type="button"
                        class="removeVariant text-xs font-bold text-red-600 hover:text-red-800"
                    >
                        Hapus
                    </button>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

                    <div>
                        <label class="mb-1 block text-xs font-bold text-[#42101f]">SKU</label>
                        <input
                            type="text"
                            name="variants[${variantIndex}][sku]"
                            class="w-full rounded-xl border border-[#eadcdf] bg-white px-3 py-2.5 text-sm outline-none focus:border-[#8b1e3f]"
                        >
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold text-[#42101f]">Warna</label>
                        <input
                            type="text"
                            name="variants[${variantIndex}][color]"
                            class="w-full rounded-xl border border-[#eadcdf] bg-white px-3 py-2.5 text-sm outline-none focus:border-[#8b1e3f]"
                        >
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold text-[#42101f]">Harga Varian</label>
                        <input
                            type="number"
                            min="0"
                            step="0.01"
                            name="variants[${variantIndex}][price]"
                            class="w-full rounded-xl border border-[#eadcdf] bg-white px-3 py-2.5 text-sm outline-none focus:border-[#8b1e3f]"
                        >
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold text-[#42101f]">Stok</label>
                        <input
                            type="number"
                            min="0"
                            value="0"
                            name="variants[${variantIndex}][stock]"
                            class="w-full rounded-xl border border-[#eadcdf] bg-white px-3 py-2.5 text-sm outline-none focus:border-[#8b1e3f]"
                        >
                    </div>

                    <div class="flex items-center gap-2 pt-6">
                        <input
                            type="checkbox"
                            name="variants[${variantIndex}][is_active]"
                            value="1"
                            checked
                            class="h-4 w-4 rounded border-[#d8b9c5] text-[#8b1e3f]"
                        >
                        <label class="text-xs font-bold text-[#42101f]">
                            Varian Aktif
                        </label>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="mb-1 block text-xs font-bold text-[#42101f]">Gambar Varian</label>

                        <div class="flex items-start gap-4">
                            <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-[#eadcdf] bg-white text-[#d8b9c5]">
                                <img alt="" class="variantImagePreview hidden h-full w-full object-cover">
                                <svg class="variantImagePlaceholder h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <rect x="3" y="4" width="18" height="16" rx="2"/>
                                    <circle cx="9" cy="10" r="1.6"/>
                                    <path d="M21 16l-5-5-8 8"/>
                                </svg>
                            </div>

                            <div class="min-w-0 flex-1">
                                <input
                                    type="file"
                                    name="variants[${variantIndex}][image]"
                                    accept="image/jpeg,image/png,image/jpg,image/webp"
                                    class="variantImageInput block w-full rounded-xl border border-[#eadcdf] bg-white px-3 py-2 text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-[#f7e9ed] file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-[#8b1e3f]"
                                >
                                <p class="variantImageError mt-2 hidden rounded-xl bg-red-50 px-3 py-2 text-xs font-semibold text-red-600"></p>
                                <p class="mt-2 text-[11px] leading-5 text-slate-500">
                                    JPG, PNG, atau WEBP, maksimal 5 MB.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            variantsContainer.appendChild(row);
            variantIndex++;

            recalculateStockFromVariants();
        });
    }

    if (variantsContainer) {
        variantsContainer.addEventListener('click', function (event) {
            const removeButton = event.target.closest('.removeVariant');

            if (!removeButton) {
                return;
            }

            const row = removeButton.closest('.variant-row');

            if (row) {
                row.remove();
            }

            if (!variantsContainer.querySelector('.variant-row')) {
                variantsContainer.innerHTML = `
                    <p id="emptyVariantMessage" class="rounded-xl bg-[#fffafc] p-4 text-sm text-slate-500">
                        Belum ada varian. Klik tombol “Tambah Varian”.
                    </p>
                `;
            }

            recalculateStockFromVariants();
        });
    }


    // ---------------------------------------------------------------
    // GAMBAR VARIAN: validasi + preview langsung (berlaku juga untuk
    // baris varian yang baru ditambahkan lewat tombol "Tambah Varian").
    // ---------------------------------------------------------------
    if (variantsContainer) {
        const variantMaxSize = 5 * 1024 * 1024; // 5 MB
        const variantTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];

        variantsContainer.addEventListener('change', function (event) {
            const target = event.target;

            // Centang "Hapus gambar saat ini": redupkan preview.
            if (target.classList && target.classList.contains('removeVariantImage')) {
                const row = target.closest('.variant-row');
                const preview = row ? row.querySelector('.variantImagePreview') : null;

                if (preview) {
                    preview.style.opacity = target.checked ? '.3' : '';
                }

                return;
            }

            if (!target.classList || !target.classList.contains('variantImageInput')) {
                return;
            }

            const row = target.closest('.variant-row');
            const preview = row.querySelector('.variantImagePreview');
            const placeholder = row.querySelector('.variantImagePlaceholder');
            const error = row.querySelector('.variantImageError');
            const file = target.files && target.files[0];

            const fail = function (message) {
                target.value = '';
                error.textContent = message;
                error.classList.remove('hidden');
            };

            error.classList.add('hidden');

            if (!file) {
                return;
            }

            if (!variantTypes.includes(file.type)) {
                fail('Format gambar harus JPG, PNG, atau WEBP.');
                return;
            }

            if (file.size > variantMaxSize) {
                fail('Ukuran gambar varian maksimal 5 MB.');
                return;
            }

            if (preview.dataset.objectUrl) {
                URL.revokeObjectURL(preview.dataset.objectUrl);
            }

            const url = URL.createObjectURL(file);

            preview.dataset.objectUrl = url;
            preview.src = url;
            preview.style.opacity = '';
            preview.classList.remove('hidden');

            if (placeholder) {
                placeholder.classList.add('hidden');
            }

            // Pilih gambar baru = batalkan centang "hapus gambar".
            const removeBox = row.querySelector('.removeVariantImage');

            if (removeBox) {
                removeBox.checked = false;
            }
        });
    }


    const galleryInput = document.getElementById('images');
    const galleryFileStatus = document.getElementById('galleryFileStatus');

    if (galleryInput) {
        galleryInput.addEventListener('change', function () {
            const count = this.files ? this.files.length : 0;

            if (galleryFileStatus) {
                galleryFileStatus.textContent =
                    count > 0
                        ? count + ' gambar slider dipilih.'
                        : 'Belum ada gambar slider dipilih.';
            }

            console.log('GALLERY FILE COUNT:', count);
        });
    }

    const imageInput = document.getElementById('image');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewImage = document.getElementById('imagePreview');
    const imageFileName = document.getElementById('imageFileName');
    const removeButton = document.getElementById('removeImagePreview');
    const imageError = document.getElementById('imageError');

    const maxFileSize = 50 * 1024 * 1024;

    if (!imageInput) {
        return;
    }

    imageInput.addEventListener('change', function () {
        const file = this.files && this.files[0];

        hideError();

        if (!file) {
            resetPreview();
            return;
        }

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/jpg',
            'image/webp'
        ];

        if (!allowedTypes.includes(file.type)) {
            showError('Format gambar harus JPG, JPEG, PNG, atau WEBP.');
            resetPreview();
            return;
        }

        if (file.size > maxFileSize) {
            showError('Ukuran gambar maksimal 50 MB.');
            resetPreview();
            return;
        }

        const reader = new FileReader();

        reader.onload = function (event) {
            previewImage.src = event.target.result;
            imageFileName.textContent =
                file.name + ' — ' + formatFileSize(file.size);

            previewContainer.classList.remove('hidden');
        };

        reader.onerror = function () {
            showError('Gambar tidak dapat dibaca. Silakan pilih gambar lain.');
            resetPreview();
        };

        reader.readAsDataURL(file);
    });

    if (removeButton) {
        removeButton.addEventListener('click', function () {
            resetPreview();
        });
    }

    function resetPreview() {
        imageInput.value = '';

        if (previewImage) {
            previewImage.src = '';
        }

        if (imageFileName) {
            imageFileName.textContent = '';
        }

        if (previewContainer) {
            previewContainer.classList.add('hidden');
        }
    }

    function showError(message) {
        if (!imageError) {
            alert(message);
            return;
        }

        imageError.textContent = message;
        imageError.classList.remove('hidden');
    }

    function hideError() {
        if (!imageError) {
            return;
        }

        imageError.textContent = '';
        imageError.classList.add('hidden');
    }

    function formatFileSize(bytes) {
        const megabytes = bytes / (1024 * 1024);

        return megabytes.toFixed(2) + ' MB';
    }
});
</script>

@endsection