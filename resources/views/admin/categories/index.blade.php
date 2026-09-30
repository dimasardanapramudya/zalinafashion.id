@extends('layouts.admin')

@section('title', 'Kategori Produk')

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.25em] text-gold-600">
                Product Management
            </p>

            <h1 class="font-serif text-3xl font-semibold text-maroon-950">
                Kategori Produk
            </h1>

            <p class="mt-1 text-sm text-maroon-500">
                Kelola nama, deskripsi, gambar, dan status kategori produk.
            </p>
        </div>

        <div class="inline-flex w-fit items-center gap-2 rounded-full border border-maroon-100
                    bg-white px-4 py-2 text-sm font-semibold text-maroon-700 shadow-sm">
            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
            {{ $categories->count() }} Kategori
        </div>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-200
                    bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M5 13l4 4L19 7"/>
            </svg>

            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Error Alert --}}
    @if(session('error'))
        <div class="flex items-center gap-3 rounded-2xl border border-red-200
                    bg-red-50 px-4 py-3 text-sm text-red-700">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M12 8v4m0 4h.01M5.07 19h13.86a2 2 0 001.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16a2 2 0 001.73 3z"/>
            </svg>

            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Validation Error --}}
    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
            <p class="mb-2 text-sm font-semibold text-red-700">
                Periksa kembali data berikut:
            </p>

            <ul class="space-y-1 text-xs text-red-600">
                @foreach($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Main Content --}}
    <div class="grid gap-6 xl:grid-cols-[380px_1fr]">

        {{-- Add Category --}}
        <div class="relative overflow-hidden rounded-3xl border border-maroon-100
                    bg-white p-6 shadow-sm">

            <div class="absolute -right-12 -top-12 h-36 w-36 rounded-full
                        bg-gold-100/50 blur-2xl"></div>

            <div class="relative">

                <div class="mb-6 flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl
                                bg-maroon-900 text-gold-300 shadow-lg">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>

                    <div>
                        <h2 class="font-serif text-xl font-semibold text-maroon-950">
                            Tambah Kategori
                        </h2>

                        <p class="text-xs text-maroon-400">
                            Buat kategori baru untuk katalog.
                        </p>
                    </div>
                </div>

                <form
                    method="POST"
                    action="{{ route('admin.categories.store') }}"
                    enctype="multipart/form-data"
                    class="space-y-5"
                >
                    @csrf

                    <div>
                        <label for="name"
                               class="mb-2 block text-sm font-semibold text-maroon-800">
                            Nama Kategori
                        </label>

                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            placeholder="Contoh: Pashmina Silk"
                            class="w-full rounded-2xl border border-maroon-200 bg-maroon-50/20
                                   px-4 py-3 text-sm text-maroon-900 placeholder-maroon-300
                                   outline-none transition focus:border-gold-500 focus:bg-white
                                   focus:ring-4 focus:ring-gold-500/10"
                        >
                    </div>

                    <div>
                        <label for="description"
                               class="mb-2 block text-sm font-semibold text-maroon-800">
                            Deskripsi
                            <span class="font-normal text-maroon-400">(Opsional)</span>
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            placeholder="Tuliskan deskripsi singkat kategori..."
                            class="w-full resize-none rounded-2xl border border-maroon-200
                                   bg-maroon-50/20 px-4 py-3 text-sm text-maroon-900
                                   placeholder-maroon-300 outline-none transition
                                   focus:border-gold-500 focus:bg-white
                                   focus:ring-4 focus:ring-gold-500/10"
                        >{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label for="image"
                               class="mb-2 block text-sm font-semibold text-maroon-800">
                            Gambar Kategori
                            <span class="font-normal text-maroon-400">(Opsional)</span>
                        </label>

                        <label
                            for="image"
                            class="flex cursor-pointer flex-col items-center justify-center
                                   rounded-2xl border-2 border-dashed border-maroon-200
                                   bg-maroon-50/20 px-4 py-6 text-center transition
                                   hover:border-gold-500 hover:bg-gold-50/30"
                        >
                            <svg class="mb-2 h-8 w-8 text-maroon-300"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="1.6"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>

                            <span class="text-sm font-semibold text-maroon-700">
                                Pilih gambar
                            </span>

                            <span class="mt-1 text-xs text-maroon-400">
                                JPG, PNG, WEBP maksimal 2 MB
                            </span>

                            <span id="image-name"
                                  class="mt-2 hidden text-xs font-semibold text-gold-700">
                            </span>
                        </label>

                        <input
                            id="image"
                            type="file"
                            name="image"
                            accept="image/jpeg,image/png,image/webp"
                            class="hidden"
                            onchange="showFileName(this)"
                        >
                    </div>

                    <button
                        type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-full
                               bg-maroon-900 px-5 py-3 text-sm font-semibold text-cream
                               transition hover:bg-maroon-800"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="1.8"
                                  d="M12 4v16m8-8H4"/>
                        </svg>

                        Tambah Kategori
                    </button>
                </form>
            </div>
        </div>

        {{-- Category List --}}
        <div class="overflow-hidden rounded-3xl border border-maroon-100
                    bg-white shadow-sm">

            <div class="flex flex-col gap-3 border-b border-maroon-100
                        bg-gradient-to-r from-maroon-950 to-maroon-800
                        px-6 py-5 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h2 class="font-serif text-xl font-semibold text-cream">
                        Daftar Kategori
                    </h2>

                    <p class="mt-1 text-xs text-cream/60">
                        Kelola kategori yang tersedia pada sistem.
                    </p>
                </div>

                <div class="rounded-full border border-white/15 bg-white/10
                            px-3 py-1.5 text-xs font-semibold text-gold-200">
                    {{ $categories->count() }} Data
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead class="bg-maroon-50/70">
                        <tr class="text-left text-xs uppercase tracking-wider text-maroon-500">
                            <th class="px-6 py-4 font-semibold">Kategori</th>
                            <th class="px-6 py-4 font-semibold">Deskripsi</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-maroon-50">
                        @forelse($categories as $category)
                            <tr class="transition hover:bg-maroon-50/40">

                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">

                                        @if($category->image)
                                            <img
                                                src="{{ asset('storage/' . $category->image) }}"
                                                alt="{{ $category->name }}"
                                                class="h-14 w-14 shrink-0 rounded-2xl object-cover
                                                       ring-1 ring-maroon-100"
                                            >
                                        @else
                                            <div class="flex h-14 w-14 shrink-0 items-center
                                                        justify-center rounded-2xl bg-gold-100
                                                        font-serif text-xl font-semibold
                                                        text-maroon-900">
                                                {{ strtoupper(substr($category->name, 0, 1)) }}
                                            </div>
                                        @endif

                                        <div>
                                            <p class="font-semibold text-maroon-950">
                                                {{ $category->name }}
                                            </p>

                                            <p class="mt-0.5 text-xs text-maroon-400">
                                                ID #{{ $category->id }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="max-w-sm px-6 py-5">
                                    <p class="line-clamp-2 text-maroon-500">
                                        {{ $category->description ?: 'Belum ada deskripsi' }}
                                    </p>
                                </td>

                                <td class="px-6 py-5">
                                    @if($category->is_active)
                                        <span class="inline-flex items-center gap-2 rounded-full
                                                     bg-emerald-50 px-3 py-1.5 text-xs
                                                     font-semibold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-2 rounded-full
                                                     bg-maroon-50 px-3 py-1.5 text-xs
                                                     font-semibold text-maroon-400">
                                            <span class="h-1.5 w-1.5 rounded-full bg-maroon-300"></span>
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-5">
                                    <div class="flex justify-end gap-2">

                                        <button
                                            type="button"
                                            onclick="openEditModal({{ $category->id }})"
                                            class="inline-flex items-center gap-2 rounded-xl
                                                   border border-gold-300 bg-gold-50 px-3 py-2
                                                   text-xs font-semibold text-gold-800 transition
                                                   hover:bg-gold-100"
                                        >
                                            <svg class="h-4 w-4" fill="none"
                                                 stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      stroke-width="1.8"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M16.5 3.5a2.121 2.121 0 013 3L12 14l-4 1 1-4 7.5-7.5z"/>
                                            </svg>

                                            Edit
                                        </button>

                                        <form
                                            method="POST"
                                            action="{{ route('admin.categories.destroy', $category) }}"
                                            onsubmit="return confirm('Yakin ingin menghapus kategori ini?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="inline-flex items-center gap-2 rounded-xl
                                                       border border-red-200 px-3 py-2 text-xs
                                                       font-semibold text-red-600 transition
                                                       hover:bg-red-50"
                                            >
                                                <svg class="h-4 w-4" fill="none"
                                                     stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="1.8"
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-9 0h14"/>
                                                </svg>

                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-20 text-center">
                                    <h3 class="font-serif text-lg font-semibold text-maroon-900">
                                        Belum Ada Kategori
                                    </h3>

                                    <p class="mt-1 text-sm text-maroon-400">
                                        Tambahkan kategori pertama untuk mulai mengatur produk.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div
    id="editModal"
    class="fixed inset-0 z-50 hidden items-center justify-center
           bg-maroon-950/60 p-4 backdrop-blur-sm"
>
    <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-3xl
                border border-maroon-100 bg-white shadow-2xl">

        <div class="flex items-center justify-between
                    bg-gradient-to-r from-maroon-950 to-maroon-800
                    px-6 py-5">
            <div>
                <h2 class="font-serif text-xl font-semibold text-cream">
                    Edit Kategori
                </h2>

                <p class="mt-1 text-xs text-cream/60">
                    Perbarui informasi kategori produk.
                </p>
            </div>

            <button
                type="button"
                onclick="closeEditModal()"
                class="rounded-full p-2 text-cream/70 hover:bg-white/10 hover:text-white"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form
            id="editCategoryForm"
            method="POST"
            enctype="multipart/form-data"
            class="space-y-5 p-6"
        >
            @csrf
            @method('PUT')

            <div>
                <label for="edit_name"
                       class="mb-2 block text-sm font-semibold text-maroon-800">
                    Nama Kategori
                </label>

                <input
                    id="edit_name"
                    type="text"
                    name="name"
                    required
                    class="w-full rounded-2xl border border-maroon-200 bg-maroon-50/20
                           px-4 py-3 text-sm text-maroon-900 outline-none
                           focus:border-gold-500 focus:ring-4 focus:ring-gold-500/10"
                >
            </div>

            <div>
                <label for="edit_description"
                       class="mb-2 block text-sm font-semibold text-maroon-800">
                    Deskripsi
                </label>

                <textarea
                    id="edit_description"
                    name="description"
                    rows="4"
                    class="w-full resize-none rounded-2xl border border-maroon-200
                           bg-maroon-50/20 px-4 py-3 text-sm text-maroon-900
                           outline-none focus:border-gold-500 focus:ring-4
                           focus:ring-gold-500/10"
                ></textarea>
            </div>

            <div>
                <label for="edit_is_active"
                       class="mb-2 block text-sm font-semibold text-maroon-800">
                    Status
                </label>

                <select
                    id="edit_is_active"
                    name="is_active"
                    required
                    class="w-full rounded-2xl border border-maroon-200 bg-maroon-50/20
                           px-4 py-3 text-sm text-maroon-900 outline-none
                           focus:border-gold-500 focus:ring-4 focus:ring-gold-500/10"
                >
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>

            <div>
                <label for="edit_image"
                       class="mb-2 block text-sm font-semibold text-maroon-800">
                    Ganti Gambar
                    <span class="font-normal text-maroon-400">(Opsional)</span>
                </label>

                <div id="currentImageWrapper" class="mb-3 hidden">
                    <p class="mb-2 text-xs text-maroon-400">
                        Gambar saat ini
                    </p>

                    <img
                        id="currentImage"
                        src=""
                        alt="Gambar kategori"
                        class="h-32 w-full rounded-2xl object-cover ring-1 ring-maroon-100"
                    >
                </div>

                <label
                    for="edit_image"
                    class="flex cursor-pointer items-center justify-center rounded-2xl
                           border-2 border-dashed border-maroon-200 bg-maroon-50/20
                           px-4 py-5 text-center hover:border-gold-500"
                >
                    <span id="editImageName" class="text-sm text-maroon-500">
                        Pilih gambar baru
                    </span>
                </label>

                <input
                    id="edit_image"
                    type="file"
                    name="image"
                    accept="image/jpeg,image/png,image/webp"
                    class="hidden"
                    onchange="showEditFileName(this)"
                >
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    onclick="closeEditModal()"
                    class="rounded-full border border-maroon-200 px-5 py-3 text-sm
                           font-semibold text-maroon-700 hover:bg-maroon-50"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="rounded-full bg-maroon-900 px-5 py-3 text-sm
                           font-semibold text-cream hover:bg-maroon-800"
                >
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const categories = @json($categories);

    function showFileName(input) {
        const nameElement = document.getElementById('image-name');

        if (input.files && input.files.length > 0) {
            nameElement.textContent = input.files[0].name;
            nameElement.classList.remove('hidden');
        } else {
            nameElement.textContent = '';
            nameElement.classList.add('hidden');
        }
    }

    function showEditFileName(input) {
        const nameElement = document.getElementById('editImageName');

        if (input.files && input.files.length > 0) {
            nameElement.textContent = input.files[0].name;
        } else {
            nameElement.textContent = 'Pilih gambar baru';
        }
    }

    function openEditModal(categoryId) {
        const category = categories.find(function(item) {
            return Number(item.id) === Number(categoryId);
        });

        if (!category) {
            return;
        }

        document.getElementById('edit_name').value = category.name || '';
        document.getElementById('edit_description').value = category.description || '';
        document.getElementById('edit_is_active').value = category.is_active ? '1' : '0';

        document.getElementById('editCategoryForm').action =
            "{{ url('admin/categories') }}/" + category.id;

        const imageWrapper = document.getElementById('currentImageWrapper');
        const currentImage = document.getElementById('currentImage');

        if (category.image) {
            currentImage.src = "{{ asset('storage') }}/" + category.image;
            imageWrapper.classList.remove('hidden');
        } else {
            currentImage.src = '';
            imageWrapper.classList.add('hidden');
        }

        document.getElementById('edit_image').value = '';
        document.getElementById('editImageName').textContent = 'Pilih gambar baru';

        const modal = document.getElementById('editModal');

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.classList.add('overflow-hidden');
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.classList.remove('overflow-hidden');
    }

    document.getElementById('editModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeEditModal();
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeEditModal();
        }
    });
</script>

@endsection