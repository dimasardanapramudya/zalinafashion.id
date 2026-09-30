@extends('layouts.admin')

@section('title', $slider->exists ? 'Edit Slider' : 'Tambah Slider')

@section('content')

@php
    $isEdit = $slider->exists;

    $themes = [
        'maroon_gold' => 'Maroon & Gold',
        'rose'        => 'Rose',
        'emerald'     => 'Emerald',
        'midnight'    => 'Midnight',
        'cream'       => 'Cream',
    ];

    $currentTheme = old('theme', $slider->theme ?? 'maroon_gold');
@endphp

<div class="mx-auto max-w-6xl space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <a
                href="{{ route('admin.sliders.index') }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-[#9b6674] transition hover:text-[#7c2638]"
            >
                <span>←</span>
                Kembali ke Slider
            </a>

            <div class="mt-4">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#b58a50]">
                    {{ $isEdit ? 'Slider Management' : 'New Marketing Content' }}
                </p>

                <h1 class="mt-1 font-serif text-3xl font-semibold text-[#451522] sm:text-4xl">
                    {{ $isEdit ? 'Edit Slider' : 'Tambah Slider' }}
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-[#927780]">
                    {{ $isEdit
                        ? 'Perbarui informasi, tampilan, dan pengaturan banner promosi Zalina.'
                        : 'Buat banner promosi baru untuk ditampilkan pada halaman utama Zalina.'
                    }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="rounded-full border border-[#eadde0] bg-white px-4 py-2 text-xs font-semibold text-[#7c2638]">
                {{ $isEdit ? 'Mode Edit' : 'Mode Tambah' }}
            </span>
        </div>

    </div>


    {{-- VALIDATION ERROR --}}
    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-5 text-sm text-red-700">
            <div class="flex items-start gap-3">
                <span class="text-lg">!</span>

                <div>
                    <p class="font-bold">
                        Terdapat kesalahan pada form.
                    </p>

                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif


    {{-- FORM --}}
    <form
        method="POST"
        enctype="multipart/form-data"
        action="{{ $isEdit
            ? route('admin.sliders.update', $slider)
            : route('admin.sliders.store')
        }}"
        class="space-y-6"
    >

        @csrf

        @if($isEdit)
            @method('PUT')
        @endif


        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">


            {{-- LEFT CONTENT --}}
            <div class="space-y-6">


                {{-- MAIN CONTENT --}}
                <section class="overflow-hidden rounded-[2rem] border border-[#eadde0] bg-white shadow-sm">

                    <div class="border-b border-[#f1e7e9] px-6 py-5 sm:px-8">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#f8e9ed] text-xl text-[#7c2638]">
                                ✦
                            </div>

                            <div>
                                <h2 class="font-serif text-xl font-semibold text-[#451522]">
                                    Konten Utama
                                </h2>

                                <p class="mt-1 text-xs text-[#927780]">
                                    Atur teks yang muncul pada banner slider.
                                </p>
                            </div>
                        </div>
                    </div>


                    <div class="grid gap-5 p-6 sm:grid-cols-2 sm:p-8">

                        {{-- TITLE --}}
                        <div class="sm:col-span-2">
                            <label for="title" class="form-label">
                                Judul Utama
                                <span class="text-[#b58a50]">*</span>
                            </label>

                            <input
                                id="title"
                                type="text"
                                name="title"
                                value="{{ old('title', $slider->title) }}"
                                required
                                placeholder="Contoh: Elegance in Every Drape"
                                class="form-input"
                            >

                            <p class="form-help">
                                Gunakan judul singkat yang kuat dan mudah dibaca.
                            </p>
                        </div>


                        {{-- EYEBROW --}}
                        <div>
                            <label for="eyebrow" class="form-label">
                                Label Kecil
                            </label>

                            <input
                                id="eyebrow"
                                type="text"
                                name="eyebrow"
                                value="{{ old('eyebrow', $slider->eyebrow) }}"
                                placeholder="Contoh: Zalina Signature Series"
                                class="form-input"
                            >
                        </div>


                        {{-- ACCENT TEXT --}}
                        <div>
                            <label for="accent_text" class="form-label">
                                Teks Aksen
                            </label>

                            <input
                                id="accent_text"
                                type="text"
                                name="accent_text"
                                value="{{ old('accent_text', $slider->accent_text) }}"
                                placeholder="Contoh: Soft Touch"
                                class="form-input"
                            >
                        </div>


                        {{-- DESCRIPTION --}}
                        <div class="sm:col-span-2">
                            <label for="description" class="form-label">
                                Deskripsi
                            </label>

                            <textarea
                                id="description"
                                name="description"
                                rows="5"
                                placeholder="Tulis deskripsi singkat mengenai koleksi atau promo..."
                                class="form-input resize-y"
                            >{{ old('description', $slider->description) }}</textarea>

                            <p class="form-help">
                                Disarankan menggunakan 1–3 kalimat agar tetap nyaman dibaca.
                            </p>
                        </div>

                    </div>

                </section>



                {{-- BUTTON SETTINGS --}}
                <section class="overflow-hidden rounded-[2rem] border border-[#eadde0] bg-white shadow-sm">

                    <div class="border-b border-[#f1e7e9] px-6 py-5 sm:px-8">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#f8e9ed] text-xl text-[#7c2638]">
                                ↗
                            </div>

                            <div>
                                <h2 class="font-serif text-xl font-semibold text-[#451522]">
                                    Tombol & Navigasi
                                </h2>

                                <p class="mt-1 text-xs text-[#927780]">
                                    Tentukan teks dan tujuan tombol pada slider.
                                </p>
                            </div>
                        </div>
                    </div>


                    <div class="grid gap-5 p-6 sm:grid-cols-2 sm:p-8">

                        <div>
                            <label for="button_text" class="form-label">
                                Teks Tombol
                            </label>

                            <input
                                id="button_text"
                                type="text"
                                name="button_text"
                                value="{{ old('button_text', $slider->button_text) }}"
                                placeholder="Contoh: Belanja Sekarang"
                                class="form-input"
                            >
                        </div>


                        <div>
                            <label for="button_url" class="form-label">
                                Link Tombol
                            </label>

                            <input
                                id="button_url"
                                type="text"
                                name="button_url"
                                value="{{ old('button_url', $slider->button_url) }}"
                                placeholder="/shop atau https://..."
                                class="form-input"
                            >

                            <p class="form-help">
                                Contoh: `/shop`, `/collections`, atau URL eksternal.
                            </p>
                        </div>

                    </div>

                </section>



                {{-- IMAGE SETTINGS --}}
                <section class="overflow-hidden rounded-[2rem] border border-[#eadde0] bg-white shadow-sm">

                    <div class="border-b border-[#f1e7e9] px-6 py-5 sm:px-8">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#f8e9ed] text-xl text-[#7c2638]">
                                ▧
                            </div>

                            <div>
                                <h2 class="font-serif text-xl font-semibold text-[#451522]">
                                    Gambar Slider
                                </h2>

                                <p class="mt-1 text-xs text-[#927780]">
                                    Upload gambar utama yang digunakan pada banner.
                                </p>
                            </div>
                        </div>
                    </div>


                    <div class="p-6 sm:p-8">

                        <label for="image" class="form-label">
                            Gambar Banner
                        </label>

                        <label
                            for="image"
                            class="group mt-2 flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#eadde0] bg-[#fffafb] px-6 py-10 text-center transition hover:border-[#cda6b0] hover:bg-[#fff7f9]"
                        >

                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#f8e9ed] text-2xl text-[#7c2638] transition group-hover:scale-105">
                                ↑
                            </div>

                            <p class="mt-4 text-sm font-bold text-[#451522]">
                                Klik untuk upload gambar
                            </p>

                            <p class="mt-1 text-xs text-[#927780]">
                                PNG, JPG, JPEG, atau WEBP
                            </p>

                            <p class="mt-1 text-xs text-[#927780]">
                                Gunakan gambar landscape untuk hasil terbaik.
                            </p>

                            <input
                                id="image"
                                type="file"
                                name="image"
                                accept="image/png,image/jpeg,image/jpg,image/webp"
                                class="hidden"
                                onchange="previewSliderImage(event)"
                            >

                        </label>


                        {{-- IMAGE PREVIEW --}}
                        <div id="imagePreviewWrapper" class="{{ $slider->image ? '' : 'hidden' }} mt-5">

                            <div class="mb-2 flex items-center justify-between">
                                <p class="text-xs font-bold uppercase tracking-[0.15em] text-[#b58a50]">
                                    Preview Gambar
                                </p>

                                <button
                                    type="button"
                                    onclick="removeSliderPreview()"
                                    class="text-xs font-semibold text-[#9b6674] hover:text-[#7c2638]"
                                >
                                    Hapus Preview
                                </button>
                            </div>

                            <div class="overflow-hidden rounded-2xl border border-[#eadde0] bg-[#fffafb]">
                                <img
                                    id="imagePreview"
                                    src="{{ $slider->image ? asset('storage/'.$slider->image) : '' }}"
                                    alt="Preview slider"
                                    class="h-64 w-full object-cover sm:h-80"
                                >
                            </div>

                        </div>

                    </div>

                </section>

            </div>



            {{-- RIGHT SIDEBAR --}}
            <div class="space-y-6">


                {{-- PUBLISH SETTINGS --}}
                <section class="overflow-hidden rounded-[2rem] border border-[#eadde0] bg-white shadow-sm">

                    <div class="border-b border-[#f1e7e9] px-6 py-5">
                        <h2 class="font-serif text-xl font-semibold text-[#451522]">
                            Pengaturan
                        </h2>

                        <p class="mt-1 text-xs text-[#927780]">
                            Atur status dan posisi slider.
                        </p>
                    </div>


                    <div class="space-y-5 p-6">

                        {{-- STATUS --}}
                        <div>
                            <label for="is_active" class="form-label">
                                Status Slider
                            </label>

                            <label class="flex cursor-pointer items-center justify-between rounded-2xl border border-[#eadde0] bg-[#fffafb] p-4">

                                <div>
                                    <p class="text-sm font-bold text-[#451522]">
                                        Slider Aktif
                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-[#927780]">
                                        Tampilkan banner pada halaman utama.
                                    </p>
                                </div>

                                <input
                                    id="is_active"
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    @checked(old('is_active', $slider->is_active ?? true))
                                    class="h-5 w-5 rounded border-[#d8b8c1] text-[#7c2638] focus:ring-[#7c2638]"
                                >

                            </label>
                        </div>


                        {{-- SORT ORDER --}}
                        <div>
                            <label for="sort_order" class="form-label">
                                Urutan Tampilan
                            </label>

                            <input
                                id="sort_order"
                                type="number"
                                name="sort_order"
                                min="0"
                                value="{{ old('sort_order', $slider->sort_order ?? 0) }}"
                                class="form-input"
                            >

                            <p class="form-help">
                                Angka lebih kecil akan tampil lebih awal.
                            </p>
                        </div>


                        {{-- THEME --}}
                        <div>
                            <label for="theme" class="form-label">
                                Tema Warna
                            </label>

                            <select
                                id="theme"
                                name="theme"
                                class="form-input"
                                onchange="updateThemePreview(this.value)"
                            >

                                @foreach($themes as $key => $label)
                                    <option
                                        value="{{ $key }}"
                                        @selected($currentTheme === $key)
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach

                            </select>
                        </div>


                        {{-- THEME PREVIEW --}}
                        <div>
                            <p class="form-label">
                                Preview Tema
                            </p>

                            <div
                                id="themePreview"
                                class="theme-preview theme-{{ $currentTheme }}"
                            >
                                <span id="themePreviewEyebrow">
                                    {{ old('eyebrow', $slider->eyebrow ?: 'ZALINA SIGNATURE') }}
                                </span>

                                <strong id="themePreviewTitle">
                                    {{ old('title', $slider->title ?: 'Elegance in Every Drape') }}
                                </strong>

                                <small>
                                    Preview tampilan warna slider
                                </small>
                            </div>
                        </div>

                    </div>

                </section>



                {{-- ACTIONS --}}
                <section class="rounded-[2rem] border border-[#eadde0] bg-white p-6 shadow-sm">

                    <button
                        type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-full bg-[#7c2638] px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-[#7c2638]/20 transition hover:-translate-y-0.5 hover:bg-[#5f1d2d]"
                    >
                        <span>✓</span>
                        {{ $isEdit ? 'Perbarui Slider' : 'Simpan Slider' }}
                    </button>


                    <a
                        href="{{ route('admin.sliders.index') }}"
                        class="mt-3 flex w-full items-center justify-center rounded-full border border-[#eadde0] bg-white px-6 py-3.5 text-sm font-bold text-[#7c2638] transition hover:bg-[#fff7f9]"
                    >
                        Batal
                    </a>


                    <p class="mt-4 text-center text-xs leading-5 text-[#927780]">
                        Pastikan seluruh informasi slider sudah sesuai sebelum disimpan.
                    </p>

                </section>

            </div>

        </div>

    </form>

</div>


<style>
    .form-label {
        display: block;
        margin-bottom: .5rem;
        font-size: .875rem;
        font-weight: 700;
        color: #451522;
    }

    .form-input {
        width: 100%;
        border-radius: .9rem;
        border: 1px solid #eadde0;
        background: #fff;
        padding: .8rem 1rem;
        font-size: .875rem;
        color: #451522;
        outline: none;
        transition: .2s ease;
    }

    .form-input::placeholder {
        color: #c2a7af;
    }

    .form-input:focus {
        border-color: #b58a50;
        box-shadow: 0 0 0 4px rgba(181,138,80,.12);
    }

    .form-help {
        margin-top: .45rem;
        font-size: .75rem;
        line-height: 1.4;
        color: #927780;
    }

    .theme-preview {
        display: flex;
        min-height: 190px;
        flex-direction: column;
        justify-content: center;
        overflow: hidden;
        border-radius: 1.25rem;
        padding: 1.5rem;
        color: white;
        transition: .3s ease;
    }

    .theme-preview span {
        font-size: .65rem;
        font-weight: 800;
        letter-spacing: .18em;
        text-transform: uppercase;
        opacity: .8;
    }

    .theme-preview strong {
        display: block;
        margin-top: .65rem;
        font-family: Georgia, serif;
        font-size: 1.65rem;
        line-height: 1.1;
    }

    .theme-preview small {
        display: block;
        margin-top: 1rem;
        font-size: .7rem;
        opacity: .75;
    }

    .theme-maroon_gold {
        background: linear-gradient(135deg, #451522, #7c2638 60%, #b58a50);
    }

    .theme-rose {
        background: linear-gradient(135deg, #8f5366, #d9a6b5);
    }

    .theme-emerald {
        background: linear-gradient(135deg, #123d35, #267653);
    }

    .theme-midnight {
        background: linear-gradient(135deg, #111827, #374151);
    }

    .theme-cream {
        background: linear-gradient(135deg, #d9c5a5, #f4e8d3);
        color: #451522;
    }
</style>


<script>
    function previewSliderImage(event) {
        const file = event.target.files[0];

        if (!file) {
            return;
        }

        const preview = document.getElementById('imagePreview');
        const wrapper = document.getElementById('imagePreviewWrapper');

        preview.src = URL.createObjectURL(file);
        wrapper.classList.remove('hidden');
    }


    function removeSliderPreview() {
        const input = document.getElementById('image');
        const preview = document.getElementById('imagePreview');
        const wrapper = document.getElementById('imagePreviewWrapper');

        input.value = '';
        preview.src = '';
        wrapper.classList.add('hidden');
    }


    function updateThemePreview(theme) {
        const preview = document.getElementById('themePreview');

        preview.className = 'theme-preview theme-' + theme;
    }


    document.addEventListener('DOMContentLoaded', function () {

        const titleInput = document.getElementById('title');
        const eyebrowInput = document.getElementById('eyebrow');

        const previewTitle = document.getElementById('themePreviewTitle');
        const previewEyebrow = document.getElementById('themePreviewEyebrow');

        if (titleInput) {
            titleInput.addEventListener('input', function () {
                previewTitle.textContent =
                    this.value || 'Elegance in Every Drape';
            });
        }

        if (eyebrowInput) {
            eyebrowInput.addEventListener('input', function () {
                previewEyebrow.textContent =
                    this.value || 'ZALINA SIGNATURE';
            });
        }

    });
</script>

@endsection