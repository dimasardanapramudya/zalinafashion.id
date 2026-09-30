@extends('layouts.admin')

@section('title', 'Kelola Retur - Zalina Fashion')

@section('content')
<div class="min-h-screen bg-[#f8f5f3] px-4 py-6 sm:px-6 lg:px-8">

    {{-- ============================================================
         HEADER
    ============================================================ --}}
    <div class="mx-auto max-w-7xl">

        <div class="mb-8 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="mb-2 inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.22em] text-[#8b1e3f]">
                    <span class="h-2 w-2 rounded-full bg-[#8b1e3f]"></span>
                    Customer Care & After-Sales
                </div>

                <h1 class="font-serif text-3xl font-bold tracking-tight text-[#42101f] sm:text-4xl">
                    Pengajuan Retur
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                    Kelola, periksa, dan tindak lanjuti pengajuan retur customer
                    dengan informasi yang terstruktur dan mudah dipantau.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div class="rounded-2xl border border-[#eadcdf] bg-white px-4 py-3 shadow-sm">
                    <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">
                        Laporan
                    </div>
                    <div class="mt-1 text-sm font-bold text-[#42101f]">
                        {{ now()->translatedFormat('d F Y') }}
                    </div>
                </div>

                <a href="{{ route('admin.dashboard') }}"
                   class="inline-flex items-center gap-2 rounded-2xl border border-[#eadcdf] bg-white px-4 py-3 text-sm font-bold text-[#42101f] shadow-sm transition hover:border-[#8b1e3f] hover:bg-[#fff8fa]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10h14V10"/>
                    </svg>
                    Dashboard
                </a>
            </div>
        </div>

        {{-- ============================================================
             ALERT
        ============================================================ --}}
        @if (session('success'))
            <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800 shadow-sm">
                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>

                <div>
                    <div class="font-bold">Berhasil</div>
                    <div class="mt-1 text-sm">{{ session('success') }}</div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-800 shadow-sm">
                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86l-8.18 14A2 2 0 003.84 21h16.32a2 2 0 001.73-3.14l-8.18-14a2 2 0 00-3.42 0z"/>
                </svg>

                <div>
                    <div class="font-bold">Terjadi kesalahan</div>
                    <div class="mt-1 text-sm">{{ session('error') }}</div>
                </div>
            </div>
        @endif

        {{-- ============================================================
             STATISTICS
        ============================================================ --}}
        @php
            $allReturns = $returns;

            $isPaginated = method_exists($allReturns, 'total');

            $totalReturns = $isPaginated
                ? $allReturns->total()
                : $allReturns->count();

            /*
            |--------------------------------------------------------------
            | HITUNG STATISTIK
            |--------------------------------------------------------------
            | PERBAIKAN: sebelumnya angka per-status dihitung dengan
            | ->where() pada $returns. Saat data dipaginasi, koleksi itu
            | HANYA berisi halaman yang sedang dibuka, sementara
            | "Total Pengajuan" memakai total() dari seluruh data.
            | Akibatnya kartu statistik saling bertentangan, misalnya
            | Total 47 tapi Menunggu 3 karena hanya menghitung halaman 1.
            |
            | Sekarang, jika controller mengirim $returnStatusCounts
            | (hitungan lintas halaman), itu yang dipakai. Kalau tidak,
            | angka tetap dihitung dari koleksi TAPI cakupannya ditandai
            | jelas di UI supaya tidak menyesatkan.
            */

            $statusCounts = collect($returnStatusCounts ?? []);

            $countByStatus = function (string $status) use ($statusCounts, $allReturns) {
                if ($statusCounts->has($status)) {
                    return (int) $statusCounts->get($status);
                }

                return $allReturns->where('return_status', $status)->count();
            };

            $requestedCount = $countByStatus('requested');
            $approvedCount  = $countByStatus('approved');
            $completedCount = $countByStatus('completed');
            $rejectedCount  = $countByStatus('rejected');

            $countsArePartial = $isPaginated
                && $statusCounts->isEmpty()
                && $allReturns->count() < $totalReturns;

            /*
            |--------------------------------------------------------------
            | NORMALISASI URL GAMBAR (DIPAKAI BERSAMA)
            |--------------------------------------------------------------
            | Menangani: URL absolut, JSON array, prefix "public/",
            | prefix "storage/", dan slash di depan. Dipakai untuk bukti
            | foto retur MAUPUN gambar produk supaya perilakunya konsisten.
            */

            $resolveImageUrl = function ($raw) {

                if (is_array($raw)) {
                    $raw = $raw[0] ?? null;
                }

                if (blank($raw)) {
                    return null;
                }

                $raw = trim((string) $raw);

                if (str_starts_with($raw, '[')) {
                    $decoded = json_decode($raw, true);

                    if (is_array($decoded) && count($decoded) > 0) {
                        $raw = trim((string) $decoded[0]);
                    }
                }

                if (blank($raw)) {
                    return null;
                }

                if (
                    str_starts_with($raw, 'http://') ||
                    str_starts_with($raw, 'https://') ||
                    str_starts_with($raw, '//')
                ) {
                    return $raw;
                }

                $path = ltrim($raw, '/');

                if (str_starts_with($path, 'public/')) {
                    $path = substr($path, 7);
                }

                return str_starts_with($path, 'storage/')
                    ? asset($path)
                    : asset('storage/' . $path);
            };

            /*
            |--------------------------------------------------------------
            | AMBIL GAMBAR PRODUK DARI ITEM PESANAN
            |--------------------------------------------------------------
            | Nama kolom gambar berbeda-beda antar tabel (image,
            | image_path, thumbnail, atau relasi images), jadi semua
            | kemungkinan dicek berurutan.
            |
            | relationLoaded() dipakai sebelum menyentuh relasi images
            | supaya tidak memicu lazy loading — kalau aplikasi
            | mengaktifkan Model::preventLazyLoading(), menyentuh relasi
            | yang belum di-eager-load akan melempar exception dan
            | membuat SELURUH halaman admin crash.
            */

            $resolveProductImage = function ($item) use ($resolveImageUrl) {

                $product = $item->product ?? null;

                $candidates = [
                    $item->product_image ?? null,
                    $item->image ?? null,
                    $product->image ?? null,
                    $product->image_path ?? null,
                    $product->thumbnail ?? null,
                ];

                if ($product && method_exists($product, 'relationLoaded') && $product->relationLoaded('images')) {
                    $firstImage = $product->images->first();

                    if ($firstImage) {
                        $candidates[] = $firstImage->path ?? $firstImage->image ?? null;
                    }
                }

                foreach ($candidates as $candidate) {
                    $url = $resolveImageUrl($candidate);

                    if ($url) {
                        return $url;
                    }
                }

                return null;
            };
        @endphp

        <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <div class="relative overflow-hidden rounded-3xl border border-[#eadcdf] bg-white p-5 shadow-sm">
                <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-[#8b1e3f]/5"></div>

                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.15em] text-slate-400">
                            Total Pengajuan
                        </p>

                        <p class="mt-3 text-3xl font-black text-[#42101f]">
                            {{ $totalReturns }}
                        </p>

                        <p class="mt-2 text-xs text-slate-500">
                            Seluruh data retur
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#f8e9ee] text-[#8b1e3f]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 3v5h5"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-3xl border border-amber-200 bg-white p-5 shadow-sm">
                <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-amber-100/70"></div>

                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.15em] text-slate-400">
                            Menunggu Pemeriksaan
                        </p>

                        <p class="mt-3 text-3xl font-black text-amber-700">
                            {{ $requestedCount }}
                        </p>

                        <p class="mt-2 text-xs text-slate-500">
                            Perlu ditindaklanjuti
                            @if ($countsArePartial)
                                <span class="text-slate-400">(halaman ini)</span>
                            @endif
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="9"/>
                            <path stroke-linecap="round" d="M12 7v5l3 2"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-3xl border border-blue-200 bg-white p-5 shadow-sm">
                <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-blue-100/70"></div>

                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.15em] text-slate-400">
                            Disetujui
                        </p>

                        <p class="mt-3 text-3xl font-black text-blue-700">
                            {{ $approvedCount }}
                        </p>

                        <p class="mt-2 text-xs text-slate-500">
                            Menunggu proses berikutnya
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-50 text-blue-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12l4 4L19 6"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-3xl border border-emerald-200 bg-white p-5 shadow-sm">
                <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-emerald-100/70"></div>

                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.15em] text-slate-400">
                            Selesai
                        </p>

                        <p class="mt-3 text-3xl font-black text-emerald-700">
                            {{ $completedCount }}
                        </p>

                        <p class="mt-2 text-xs text-slate-500">
                            Retur telah ditutup
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12l4 4L19 6"/>
                            <circle cx="12" cy="12" r="9"/>
                        </svg>
                    </div>
                </div>
            </div>

        </div>

        {{-- ============================================================
             FILTER AND STATUS NAVIGATION
        ============================================================ --}}
        <div class="mb-6 rounded-3xl border border-[#eadcdf] bg-white p-4 shadow-sm sm:p-5">

            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-black text-[#42101f]">
                        Daftar Pengajuan
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Gunakan pencarian dan filter untuk menemukan retur tertentu.
                    </p>
                </div>

                <div class="text-xs font-semibold text-slate-400">
                    {{ $totalReturns }} data ditemukan
                </div>
            </div>

            <div class="mb-5 flex flex-wrap gap-2">
                @foreach ([
                    null => ['label' => 'Semua', 'icon' => 'grid'],
                    'requested' => ['label' => 'Diajukan', 'icon' => 'clock'],
                    'approved' => ['label' => 'Disetujui', 'icon' => 'check'],
                    'rejected' => ['label' => 'Ditolak', 'icon' => 'x'],
                    'completed' => ['label' => 'Selesai', 'icon' => 'done'],
                ] as $key => $item)

                    <a href="{{ route('admin.returns.index', $key ? ['status' => $key] : []) }}"
                       class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-xs font-bold transition
                       {{ $activeStatus === $key
                            ? 'border-[#8b1e3f] bg-[#8b1e3f] text-white shadow-md shadow-[#8b1e3f]/20'
                            : 'border-[#eadcdf] bg-white text-slate-600 hover:border-[#8b1e3f] hover:bg-[#fff8fa] hover:text-[#8b1e3f]' }}">
                        @if ($item['icon'] === 'clock')
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9"/>
                                <path stroke-linecap="round" d="M12 7v5l3 2"/>
                            </svg>
                        @elseif ($item['icon'] === 'check' || $item['icon'] === 'done')
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12l4 4L19 6"/>
                            </svg>
                        @elseif ($item['icon'] === 'x')
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                            </svg>
                        @else
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <rect x="4" y="4" width="6" height="6" rx="1"/>
                                <rect x="14" y="4" width="6" height="6" rx="1"/>
                                <rect x="4" y="14" width="6" height="6" rx="1"/>
                                <rect x="14" y="14" width="6" height="6" rx="1"/>
                            </svg>
                        @endif

                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>

            <form action="{{ route('admin.returns.index') }}" method="GET"
                  class="grid grid-cols-1 gap-3 md:grid-cols-[1fr_auto_auto]">

                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                         fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7"/>
                        <path stroke-linecap="round" d="M20 20l-4-4"/>
                    </svg>

                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Cari nomor pesanan, nama, email, atau nomor telepon..."
                           class="h-11 w-full rounded-xl border border-[#eadcdf] bg-[#fffdfd] pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-[#8b1e3f] focus:ring-2 focus:ring-[#8b1e3f]/10">
                </div>

                <select name="status"
                        class="h-11 rounded-xl border border-[#eadcdf] bg-[#fffdfd] px-4 text-sm font-semibold text-slate-600 outline-none focus:border-[#8b1e3f] focus:ring-2 focus:ring-[#8b1e3f]/10">
                    <option value="">Semua status</option>
                    <option value="requested" @selected(request('status') === 'requested')>Diajukan</option>
                    <option value="approved" @selected(request('status') === 'approved')>Disetujui</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Ditolak</option>
                    <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
                </select>

                <button type="submit"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#8b1e3f] px-5 text-sm font-bold text-white transition hover:bg-[#6f1732]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M7 12h10M10 18h4"/>
                    </svg>
                    Terapkan
                </button>
            </form>
        </div>

        {{-- ============================================================
             RETURN LIST
        ============================================================ --}}
        <div class="space-y-5">

            @forelse ($returns as $order)

                @php
                    $customer = $order->customer ?? null;

                    $customerName = $customer->name
                        ?? $order->customer_name
                        ?? $order->name
                        ?? 'Customer';

                    $customerEmail = $customer->email
                        ?? $order->customer_email
                        ?? $order->email
                        ?? '-';

                    $customerPhone = $customer->phone
                        ?? $customer->phone_number
                        ?? $order->customer_phone
                        ?? $order->phone
                        ?? '-';

                    $customerAddress = $customer->address
                        ?? $order->shipping_address
                        ?? $order->address
                        ?? '-';

                    $customerInitial = strtoupper(substr(trim($customerName), 0, 1));

                    $status = $order->return_status ?? 'none';

                    $statusLabel = [
                        'requested' => 'Diajukan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'completed' => 'Selesai',
                        'none' => 'Belum Diajukan',
                    ][$status] ?? ucfirst($status);

                    $statusClass = [
                        'requested' => 'border-amber-200 bg-amber-50 text-amber-700',
                        'approved' => 'border-blue-200 bg-blue-50 text-blue-700',
                        'rejected' => 'border-red-200 bg-red-50 text-red-700',
                        'completed' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                        'none' => 'border-slate-200 bg-slate-50 text-slate-500',
                    ][$status] ?? 'border-slate-200 bg-slate-50 text-slate-500';

                    $reasonLabel = [
                        'damaged' => 'Produk rusak',
                        'wrong_item' => 'Produk tidak sesuai',
                        'wrong_size' => 'Ukuran tidak sesuai',
                        'wrong_color' => 'Warna tidak sesuai',
                        'defective' => 'Produk cacat',
                        'other' => 'Alasan lainnya',
                    ][$order->return_reason] ?? ($order->return_reason ?? 'Tidak disebutkan');

                    $submittedAt = $order->return_requested_at
                        ?? $order->updated_at
                        ?? $order->created_at;

                    /*
                    |--------------------------------------------------------------
                    | NORMALISASI BUKTI FOTO RETUR (MULTI-FOTO)
                    |--------------------------------------------------------------
                    | Kolom asli di tabel orders adalah "return_image" (lihat
                    | ReturnManagementController::destroy(), satu-satunya kolom
                    | yang di-null-kan saat riwayat retur dihapus). Kolom itu
                    | ditaruh paling pertama. Sisanya cuma fallback jaga-jaga
                    | kalau skema berubah di lingkungan lain.
                    |
                    | Customer bisa mengirim lebih dari satu foto bukti produk,
                    | jadi isi kolom ini didukung dalam beberapa bentuk: path
                    | tunggal, JSON array berisi banyak path, array PHP asli
                    | (kalau di-cast di model), atau string dipisah koma.
                    | Prefix "public/" dibersihkan, lalu URL dibentuk lewat
                    | asset('storage/...'). Setiap foto juga disiapkan nama
                    | filenya sendiri untuk tombol download.
                    */
                    $returnImagePossibleFields = [
                        'return_image',
                        'return_images',
                        'return_proof',
                        'return_proofs',
                        'return_photo',
                        'return_photos',
                        'return_proof_image',
                        'return_proof_images',
                        'return_proof_photo',
                        'return_proof_photos',
                        'bukti_retur',
                        'bukti_retur_image',
                        'bukti_retur_images',
                        'proof_image',
                        'proof_images',
                    ];

                    $returnImagesRaw = null;

                    foreach ($returnImagePossibleFields as $returnImageField) {
                        if (isset($order->{$returnImageField}) && filled($order->{$returnImageField})) {
                            $returnImagesRaw = $order->{$returnImageField};
                            break;
                        }
                    }

                    $normalizeReturnImagePaths = function ($raw) {

                        if (is_array($raw)) {
                            return $raw;
                        }

                        if ($raw instanceof \Illuminate\Support\Collection) {
                            return $raw->all();
                        }

                        if (blank($raw)) {
                            return [];
                        }

                        $raw = trim((string) $raw);

                        if ($raw === '') {
                            return [];
                        }

                        if (str_starts_with($raw, '[')) {
                            $decoded = json_decode($raw, true);

                            if (is_array($decoded)) {
                                return $decoded;
                            }
                        }

                        if (str_contains($raw, ',')) {
                            $parts = array_values(array_filter(array_map('trim', explode(',', $raw))));

                            if (count($parts) > 1) {
                                return $parts;
                            }
                        }

                        return [$raw];
                    };

                    $returnImagePaths = $normalizeReturnImagePaths($returnImagesRaw);

                    $returnImages = collect($returnImagePaths)
                        ->filter(fn ($path) => filled($path))
                        ->values()
                        ->map(function ($path, $index) use ($resolveImageUrl, $order) {

                            $url = $resolveImageUrl($path);

                            if (! $url) {
                                return null;
                            }

                            $extension = pathinfo((string) (is_array($path) ? ($path[0] ?? '') : $path), PATHINFO_EXTENSION) ?: 'jpg';

                            return [
                                'url' => $url,
                                'download_name' => 'bukti-retur-' . ($order->order_number ?? $order->id) . '-' . ($index + 1) . '.' . $extension,
                            ];
                        })
                        ->filter()
                        ->values();

                    $returnImageCount = $returnImages->count();

                    // Kompatibilitas mundur: beberapa bagian lama memakai foto pertama saja.
                    $returnImageUrl = $returnImages->first()['url'] ?? null;
                    $returnImageDownloadName = $returnImages->first()['download_name'] ?? null;

                    $items = $order->items
                        ?? $order->orderItems
                        ?? collect();

                    $itemCount = $items instanceof \Illuminate\Support\Collection
                        ? $items->count()
                        : (is_countable($items) ? count($items) : 0);

                    /*
                    |--------------------------------------------------------------
                    | PRATINJAU GAMBAR PRODUK
                    |--------------------------------------------------------------
                    | Dipakai di kartu ringkasan supaya admin bisa langsung
                    | mengenali produk yang diretur tanpa harus membuka detail.
                    */

                    $itemsCollection = $items instanceof \Illuminate\Support\Collection
                        ? $items
                        : collect(is_iterable($items) ? $items : []);

                    $previewItems = $itemsCollection->take(4);

                    $extraItemCount = max(0, $itemCount - $previewItems->count());
                @endphp

                <article class="overflow-hidden rounded-3xl border border-[#eadcdf] bg-white shadow-sm transition hover:shadow-lg">

                    {{-- CARD HEADER --}}
                    <div class="border-b border-[#f0e5e8] bg-gradient-to-r from-[#fffafa] to-white px-5 py-5 sm:px-7">
                        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">

                            <div class="flex min-w-0 items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#f8e9ee] font-serif text-lg font-black text-[#8b1e3f]">
                                    {{ $customerInitial }}
                                </div>

                                <div class="min-w-0">
                                    <div class="mb-1 flex flex-wrap items-center gap-2">
                                        <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">
                                            Nomor Pesanan
                                        </span>

                                        <span class="rounded-md bg-[#f8e9ee] px-2 py-1 text-[10px] font-black text-[#8b1e3f]">
                                            RETUR
                                        </span>
                                    </div>

                                    <h2 class="break-all text-lg font-black tracking-tight text-[#42101f] sm:text-xl">
                                        {{ $order->order_number ?? ('#' . $order->id) }}
                                    </h2>

                                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                                        <span class="font-semibold text-slate-700">
                                            {{ $customerName }}
                                        </span>

                                        <span class="hidden text-slate-300 sm:inline">•</span>

                                        <span>
                                            Diajukan
                                            {{ $submittedAt ? $submittedAt->format('d M Y, H:i') : '-' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-black uppercase tracking-wide {{ $statusClass }}">
                                    <span class="h-2 w-2 rounded-full bg-current"></span>
                                    {{ $statusLabel }}
                                </span>

                                <button type="button"
                                        onclick="toggleReturnDetail('return-{{ $order->id }}')"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-[#eadcdf] bg-white text-slate-500 transition hover:border-[#8b1e3f] hover:text-[#8b1e3f]"
                                        aria-label="Buka detail retur">
                                    <svg id="icon-return-{{ $order->id }}"
                                         class="h-4 w-4 transition-transform"
                                         fill="none"
                                         stroke="currentColor"
                                         stroke-width="1.8"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/>
                                    </svg>
                                </button>
                            </div>

                        </div>
                    </div>

                    {{-- SUMMARY --}}
                    <div class="grid grid-cols-1 gap-5 px-5 py-5 sm:px-7 lg:grid-cols-3">

                        {{-- CUSTOMER --}}
                        <div class="rounded-2xl border border-[#f0e5e8] bg-[#fffafa] p-4">
                            <div class="mb-3 flex items-center gap-2 text-xs font-black uppercase tracking-[0.12em] text-[#8b1e3f]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 21a8 8 0 00-16 0M12 11a4 4 0 100-8 4 4 0 000 8z"/>
                                </svg>
                                Identitas Customer
                            </div>

                            <div class="space-y-2 text-sm">
                                <div class="font-bold text-slate-800">
                                    {{ $customerName }}
                                </div>

                                <div class="flex items-start gap-2 text-xs text-slate-500">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16v16H4z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 5l8 7 8-7"/>
                                    </svg>

                                    <span class="break-all">{{ $customerEmail }}</span>
                                </div>

                                <div class="flex items-start gap-2 text-xs text-slate-500">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 2h4l2 5-3 2a16 16 0 006 6l2-3 5 2v4a2 2 0 01-2 2A18 18 0 012 4a2 2 0 012-2h2z"/>
                                    </svg>

                                    <span>{{ $customerPhone }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- RETURN REASON --}}
                        <div class="rounded-2xl border border-[#f0e5e8] bg-white p-4">
                            <div class="mb-3 flex items-center gap-2 text-xs font-black uppercase tracking-[0.12em] text-[#8b1e3f]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86l-8.18 14A2 2 0 003.84 21h16.32a2 2 0 001.73-3.14l-8.18-14a2 2 0 00-3.42 0z"/>
                                </svg>
                                Alasan Retur
                            </div>

                            <div class="text-sm font-bold text-slate-800">
                                {{ $reasonLabel }}
                            </div>

                            <p class="mt-2 line-clamp-3 text-xs leading-5 text-slate-500">
                                {{ $order->return_customer_note ?: 'Tidak ada catatan tambahan dari customer.' }}
                            </p>
                        </div>

                        {{-- ORDER INFORMATION --}}
                        <div class="rounded-2xl border border-[#f0e5e8] bg-white p-4">
                            <div class="mb-3 flex items-center gap-2 text-xs font-black uppercase tracking-[0.12em] text-[#8b1e3f]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 4h10M5 8h14M4 12h16M5 16h14M7 20h10"/>
                                </svg>
                                Informasi Pesanan
                            </div>

                            <div class="space-y-2 text-xs">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-slate-500">Status pesanan</span>
                                    <span class="font-bold text-slate-700">
                                        {{ ucfirst($order->status ?? $order->order_status ?? 'Delivered') }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-slate-500">Jumlah item</span>
                                    <span class="font-bold text-slate-700">
                                        {{ $itemCount ?: 1 }} item
                                    </span>
                                </div>

                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-slate-500">Total pesanan</span>
                                    <span class="font-bold text-[#8b1e3f]">
                                        Rp {{ number_format((float) ($order->total ?? $order->grand_total ?? 0), 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            {{-- ============================================================
                                 PRATINJAU GAMBAR PRODUK
                            ============================================================ --}}
                            @if ($previewItems->isNotEmpty())
                                <div class="mt-4 border-t border-[#f0e5e8] pt-3">
                                    <div class="mb-2 text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400">
                                        Produk Diretur
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2">
                                        @foreach ($previewItems as $previewItem)
                                            @php
                                                $previewImageUrl = $resolveProductImage($previewItem);

                                                $previewName = $previewItem->product->name
                                                    ?? $previewItem->product_name
                                                    ?? $previewItem->name
                                                    ?? 'Produk Zalina Fashion';
                                            @endphp

                                            <div class="group relative h-12 w-12 shrink-0 overflow-hidden rounded-xl border border-[#eadcdf] bg-[#fffafa]"
                                                 title="{{ $previewName }}">

                                                @if ($previewImageUrl)
                                                    <img src="{{ $previewImageUrl }}"
                                                         alt="{{ $previewName }}"
                                                         class="h-full w-full object-cover"
                                                         loading="lazy"
                                                         onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">

                                                    <div class="hidden h-full w-full flex items-center justify-center bg-[#f8e9ee] text-[10px] font-black text-[#8b1e3f]">
                                                        {{ mb_strtoupper(mb_substr($previewName, 0, 1)) }}
                                                    </div>
                                                @else
                                                    <div class="flex h-full w-full items-center justify-center bg-[#f8e9ee] text-[10px] font-black text-[#8b1e3f]">
                                                        {{ mb_strtoupper(mb_substr($previewName, 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach

                                        @if ($extraItemCount > 0)
                                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#8b1e3f] text-xs font-black text-white">
                                                +{{ $extraItemCount }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- ============================================================
                                 PRATINJAU BUKTI RETUR
                                 Foto yang dikirim customer sebagai bukti (bukan foto produk),
                                 supaya admin bisa langsung melihatnya tanpa membuka detail.
                            ============================================================ --}}
                            <div class="mt-4 border-t border-[#f0e5e8] pt-3">
                                <div class="mb-2 flex items-center justify-between gap-2">
                                    <span class="text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400">
                                        Bukti Retur
                                    </span>

                                    @if ($returnImageCount > 0)
                                        <span class="text-[10px] font-bold text-[#8b1e3f]">
                                            {{ $returnImageCount }} foto
                                        </span>
                                    @endif
                                </div>

                                @if ($returnImageCount > 0)
                                    <button type="button"
                                            onclick="openReturnGallery(@js($returnImages), 0, @js($order->order_number ?? ('#' . $order->id))); event.stopPropagation();"
                                            class="group relative block h-12 w-12 shrink-0 overflow-hidden rounded-xl border border-[#eadcdf] bg-[#fffafa]"
                                            title="Lihat bukti retur">
                                        <img src="{{ $returnImageUrl }}"
                                             alt="Bukti foto retur {{ $order->order_number ?? $order->id }}"
                                             class="h-full w-full object-cover transition group-hover:scale-105"
                                             loading="lazy"
                                             onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">

                                        <div class="hidden h-full w-full items-center justify-center bg-[#fdeef1] text-[9px] font-bold text-[#8b1e3f]">
                                            Gagal muat
                                        </div>

                                        @if ($returnImageCount > 1)
                                            <span class="absolute bottom-0 right-0 rounded-tl-md bg-black/60 px-1 text-[9px] font-bold text-white">
                                                +{{ $returnImageCount - 1 }}
                                            </span>
                                        @endif
                                    </button>
                                @else
                                    <div class="flex items-center gap-2 rounded-xl border border-dashed border-[#eadcdf] bg-[#fffafa] px-3 py-2 text-[11px] text-slate-400">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h4l2-2h6l2 2h4v12H3z"/>
                                            <circle cx="12" cy="13" r="3.2"/>
                                        </svg>
                                        Customer belum melampirkan foto
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>

                    {{-- EXPANDABLE DETAIL --}}
                    <div id="return-{{ $order->id }}" class="hidden border-t border-[#f0e5e8] bg-[#fffafa] px-5 py-6 sm:px-7">

                        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

                            {{-- FULL CUSTOMER INFORMATION --}}
                            <div>
                                <div class="mb-4 flex items-center gap-2">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#f8e9ee] text-[#8b1e3f]">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 21a8 8 0 00-16 0M12 11a4 4 0 100-8 4 4 0 000 8z"/>
                                        </svg>
                                    </div>

                                    <div>
                                        <h3 class="text-sm font-black text-[#42101f]">
                                            Detail Identitas Pengaju
                                        </h3>

                                        <p class="text-xs text-slate-500">
                                            Informasi customer yang mengajukan retur.
                                        </p>
                                    </div>
                                </div>

                                <div class="overflow-hidden rounded-2xl border border-[#eadcdf] bg-white">
                                    <div class="grid grid-cols-1 divide-y divide-[#f0e5e8] sm:grid-cols-2 sm:divide-y-0">
                                        <div class="border-b border-[#f0e5e8] p-4 sm:border-r">
                                            <div class="text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400">
                                                Nama Lengkap
                                            </div>

                                            <div class="mt-1 text-sm font-bold text-slate-800">
                                                {{ $customerName }}
                                            </div>
                                        </div>

                                        <div class="border-b border-[#f0e5e8] p-4">
                                            <div class="text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400">
                                                Email
                                            </div>

                                            <div class="mt-1 break-all text-sm font-bold text-slate-800">
                                                {{ $customerEmail }}
                                            </div>
                                        </div>

                                        <div class="border-b border-[#f0e5e8] p-4 sm:border-r">
                                            <div class="text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400">
                                                Nomor Telepon
                                            </div>

                                            <div class="mt-1 text-sm font-bold text-slate-800">
                                                {{ $customerPhone }}
                                            </div>
                                        </div>

                                        <div class="border-b border-[#f0e5e8] p-4">
                                            <div class="text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400">
                                                ID Customer
                                            </div>

                                            <div class="mt-1 text-sm font-bold text-slate-800">
                                                {{ $customer->id ?? $order->user_id ?? $order->customer_id ?? '-' }}
                                            </div>
                                        </div>

                                        <div class="p-4 sm:col-span-2">
                                            <div class="text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400">
                                                Alamat Pengiriman
                                            </div>

                                            <div class="mt-1 text-sm leading-6 text-slate-700">
                                                {{ $customerAddress }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- RETURN DETAILS --}}
                            <div>
                                <div class="mb-4 flex items-center gap-2">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#f8e9ee] text-[#8b1e3f]">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 3v5h5"/>
                                        </svg>
                                    </div>

                                    <div>
                                        <h3 class="text-sm font-black text-[#42101f]">
                                            Detail Pengajuan Retur
                                        </h3>

                                        <p class="text-xs text-slate-500">
                                            Alasan, catatan, dan bukti pengajuan.
                                        </p>
                                    </div>
                                </div>

                                <div class="space-y-4 rounded-2xl border border-[#eadcdf] bg-white p-4">

                                    <div>
                                        <div class="text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400">
                                            Alasan Retur
                                        </div>

                                        <div class="mt-1 text-sm font-bold text-slate-800">
                                            {{ $reasonLabel }}
                                        </div>
                                    </div>

                                    <div>
                                        <div class="text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400">
                                            Catatan Customer
                                        </div>

                                        <div class="mt-1 rounded-xl bg-[#fffafa] p-3 text-sm leading-6 text-slate-700">
                                            {{ $order->return_customer_note ?: 'Tidak ada catatan tambahan.' }}
                                        </div>
                                    </div>

                                    {{-- ============================================================
                                         BUKTI FOTO RETUR / FOTO PRODUK YANG DIKIRIM USER
                                         Mendukung banyak foto sekaligus (galeri) + lightbox zoom.
                                    ============================================================ --}}
                                    <div>
                                        <div class="mb-2 flex items-center justify-between gap-3">
                                            <div class="text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400">
                                                Bukti Foto Produk dari Customer
                                            </div>

                                            @if ($returnImageCount > 0)
                                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                    {{ $returnImageCount }} foto terlampir
                                                </span>
                                            @endif
                                        </div>

                                        @if ($returnImageCount > 0)

                                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                                @foreach ($returnImages as $returnPhoto)
                                                    <div class="group relative aspect-square overflow-hidden rounded-2xl border border-[#eadcdf] bg-[#faf7f8]">

                                                        <button type="button"
                                                                onclick="openReturnGallery(@js($returnImages), {{ $loop->index }}, @js($order->order_number ?? ('#' . $order->id)))"
                                                                class="block h-full w-full"
                                                                aria-label="Perbesar bukti foto {{ $loop->iteration }}">
                                                            <img src="{{ $returnPhoto['url'] }}"
                                                                 alt="Bukti foto produk retur {{ $order->order_number ?? $order->id }} ke-{{ $loop->iteration }}"
                                                                 class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                                                 loading="lazy"
                                                                 onerror="this.closest('[data-return-photo-wrap]')?.classList.add('opacity-40'); this.parentElement.classList.add('hidden'); this.closest('.group').querySelector('[data-return-photo-error]')?.classList.remove('hidden');">
                                                        </button>

                                                        <div data-return-photo-error class="hidden absolute inset-0 flex flex-col items-center justify-center gap-1 bg-[#fdeef1] px-2 text-center">
                                                            <svg class="h-6 w-6 text-red-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                                <rect x="3" y="5" width="18" height="14" rx="2"/>
                                                                <path stroke-linecap="round" d="M8 12l3 3 5-6"/>
                                                            </svg>
                                                            <p class="text-[10px] font-bold text-red-600">
                                                                Gagal dimuat
                                                            </p>
                                                        </div>

                                                        <div class="pointer-events-none absolute inset-0 flex items-center justify-center bg-[#2b0713]/0 opacity-0 transition group-hover:bg-[#2b0713]/40 group-hover:opacity-100">
                                                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                                <circle cx="11" cy="11" r="7"/>
                                                                <path stroke-linecap="round" d="M20 20l-4-4M9 8v6M6 11h6"/>
                                                            </svg>
                                                        </div>

                                                        <a href="{{ $returnPhoto['url'] }}"
                                                           download="{{ $returnPhoto['download_name'] }}"
                                                           onclick="event.stopPropagation();"
                                                           aria-label="Unduh bukti foto {{ $loop->iteration }}"
                                                           class="absolute bottom-2 right-2 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-[#8b1e3f] opacity-0 shadow transition group-hover:opacity-100">
                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14"/>
                                                            </svg>
                                                        </a>

                                                        @if ($returnImageCount > 1)
                                                            <span class="pointer-events-none absolute left-2 top-2 rounded-full bg-black/60 px-2 py-0.5 text-[10px] font-bold text-white">
                                                                {{ $loop->iteration }}/{{ $returnImageCount }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>

                                        @else
                                            <div class="rounded-2xl border border-dashed border-[#eadcdf] bg-[#fffafa] p-5 text-center">
                                                <svg class="mx-auto h-8 w-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                                                    <circle cx="8.5" cy="10" r="1.5"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 15l-5-5L5 19"/>
                                                </svg>

                                                <p class="mt-2 text-xs font-semibold text-slate-500">
                                                    Customer tidak melampirkan foto bukti.
                                                </p>

                                                <p class="mt-1 text-[11px] text-slate-400">
                                                    Kolom bukti retur pada pesanan ini masih kosong, atau nama kolomnya
                                                    belum ada di daftar kemungkinan kolom yang dicek.
                                                </p>
                                            </div>
                                        @endif
                                    </div>

                                </div>
                            </div>

                        </div>

                        {{-- ORDER ITEMS --}}
                        @if ($itemCount > 0)
                            <div class="mt-6">
                                <div class="mb-4 flex items-center gap-2">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#f8e9ee] text-[#8b1e3f]">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 2l1 5h10l1-5M4 7h16l-1 13H5L4 7z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 11v5m6-5v5"/>
                                        </svg>
                                    </div>

                                    <div>
                                        <h3 class="text-sm font-black text-[#42101f]">
                                            Produk dalam Pesanan
                                        </h3>

                                        <p class="text-xs text-slate-500">
                                            Daftar item yang terkait dengan pesanan ini.
                                        </p>
                                    </div>
                                </div>

                                <div class="overflow-hidden rounded-2xl border border-[#eadcdf] bg-white">
                                    <div class="divide-y divide-[#f0e5e8]">
                                        @foreach ($items as $item)
                                            @php
                                                $itemProduct = $item->product ?? null;
                                                $itemName = $itemProduct->name
                                                    ?? $item->product_name
                                                    ?? $item->name
                                                    ?? 'Produk Zalina Fashion';

                                                $itemQuantity = (int) ($item->quantity ?? 1);
                                                $itemPrice = $item->price ?? $item->unit_price ?? 0;

                                                $itemImageUrl = $resolveProductImage($item);

                                                $itemVariant = $item->variant ?? null;

                                                $itemVariantLabel = null;

                                                if ($itemVariant) {
                                                    $itemVariantLabel = collect([
                                                        $itemVariant->name ?? null,
                                                        $itemVariant->color ?? null,
                                                        $itemVariant->size ?? null,
                                                    ])->filter()->implode(' • ');
                                                }

                                                $itemSubtotal = $item->subtotal
                                                    ?? ((float) $itemPrice * $itemQuantity);
                                            @endphp

                                            <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">

                                                <div class="flex min-w-0 items-center gap-4">

                                                    {{-- GAMBAR PRODUK --}}
                                                    <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl border border-[#eadcdf] bg-[#fffafa]">
                                                        @if ($itemImageUrl)
                                                            <a href="{{ $itemImageUrl }}" target="_blank" rel="noopener noreferrer" class="block h-full w-full">
                                                                <img src="{{ $itemImageUrl }}"
                                                                     alt="{{ $itemName }}"
                                                                     class="h-full w-full object-cover transition duration-300 hover:scale-105"
                                                                     loading="lazy"
                                                                     onerror="this.style.display='none'; this.parentElement.nextElementSibling.classList.remove('hidden');">
                                                            </a>

                                                            <div class="hidden h-full w-full flex items-center justify-center bg-[#f8e9ee] text-base font-black text-[#8b1e3f]">
                                                                {{ mb_strtoupper(mb_substr($itemName, 0, 1)) }}
                                                            </div>
                                                        @else
                                                            <div class="flex h-full w-full items-center justify-center bg-[#f8e9ee] text-base font-black text-[#8b1e3f]">
                                                                {{ mb_strtoupper(mb_substr($itemName, 0, 1)) }}
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="min-w-0">
                                                        <div class="text-sm font-bold text-slate-800">
                                                            {{ $itemName }}
                                                        </div>

                                                        @if ($itemVariantLabel)
                                                            <div class="mt-0.5 text-xs text-slate-500">
                                                                Varian: <span class="font-semibold">{{ $itemVariantLabel }}</span>
                                                            </div>
                                                        @endif

                                                        <div class="mt-1 text-xs text-slate-500">
                                                            {{ $itemQuantity }} item &times; Rp {{ number_format((float) $itemPrice, 0, ',', '.') }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="shrink-0 text-sm font-black text-[#8b1e3f] sm:text-right">
                                                    Rp {{ number_format((float) $itemSubtotal, 0, ',', '.') }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>

                    {{-- ACTION FOOTER --}}
                    <div class="border-t border-[#f0e5e8] bg-white px-5 py-4 sm:px-7">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>

                                <span>
                                    Terakhir diperbarui:
                                    <strong class="text-slate-700">
                                        {{ $order->updated_at ? $order->updated_at->format('d M Y, H:i') : '-' }}
                                    </strong>
                                </span>
                            </div>

                            <div class="flex flex-wrap gap-2">

                                @if ($status === 'requested')

                                    <form action="{{ route('admin.returns.approve', $order) }}"
                                          method="POST"
                                          >
                                        @csrf

                                        <button type="submit"
                                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#8b1e3f] px-4 py-2.5 text-xs font-black text-white shadow-sm transition hover:bg-[#6f1732]">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12l4 4L19 6"/>
                                            </svg>
                                            Setujui Retur
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.returns.reject', $order) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menolak pengajuan retur ini?');">
                                        @csrf

                                        <input type="hidden" name="admin_note" value="Pengajuan retur ditolak oleh admin.">

                                        <button type="submit"
                                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-xs font-black text-red-700 transition hover:bg-red-100">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                                            </svg>
                                            Tolak Retur
                                        </button>
                                    </form>

                                @elseif ($status === 'approved')

                                    <form action="{{ route('admin.returns.complete', $order) }}"
                                          method="POST"
                                          >
                                        @csrf

                                        <button type="submit"
                                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-black text-white shadow-sm transition hover:bg-emerald-700">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12l4 4L19 6"/>
                                            </svg>
                                            Tandai Selesai
                                        </button>
                                    </form>

                                @elseif ($status === 'completed')

                                    <span class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-xs font-black text-emerald-700">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12l4 4L19 6"/>
                                        </svg>
                                        Retur Selesai
                                    </span>

                                @elseif ($status === 'rejected')

                                    <span class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-xs font-black text-red-700">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                                        </svg>
                                        Pengajuan Ditolak
                                    </span>

                                @endif

                                {{-- ============================================================
                                     TOMBOL HAPUS RIWAYAT
                                     Form dibuat tersembunyi, submit dikendalikan lewat
                                     modal konfirmasi custom (bukan confirm() bawaan browser)
                                ============================================================ --}}
                                <form id="delete-form-{{ $order->id }}"
                                      action="{{ route('admin.returns.destroy', $order) }}"
                                      method="POST"
                                      class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>

                                <button type="button"
                                        onclick="openDeleteReturnModal(@js((string) $order->id), @js($order->order_number ?? '#' . $order->id))"
                                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-xs font-black text-red-700 transition hover:bg-red-100">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M9 6V4h6v2m-9 0l1 15h10l1-15M10 11v6M14 11v6"/>
                                    </svg>
                                    Hapus Riwayat
                                </button>

                            </div>
                        </div>
                    </div>

                </article>

            @empty

                <div class="rounded-3xl border border-dashed border-[#d9c4ca] bg-white px-6 py-16 text-center shadow-sm">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-[#f8e9ee] text-[#8b1e3f]">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 3v5h5"/>
                        </svg>
                    </div>

                    <h3 class="mt-5 text-lg font-black text-[#42101f]">
                        Belum Ada Pengajuan Retur
                    </h3>

                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                        Belum ada data retur yang sesuai dengan filter atau pencarian yang digunakan.
                    </p>

                    @if (request()->filled('search') || request()->filled('status'))
                        <a href="{{ route('admin.returns.index') }}"
                           class="mt-5 inline-flex items-center gap-2 rounded-xl bg-[#8b1e3f] px-5 py-3 text-xs font-black text-white transition hover:bg-[#6f1732]">
                            Reset Filter
                        </a>
                    @endif
                </div>

            @endforelse

        </div>

        {{-- ============================================================
             PAGINATION
        ============================================================ --}}
        @if (method_exists($returns, 'links'))
            <div class="mt-8 rounded-2xl border border-[#eadcdf] bg-white px-4 py-4 shadow-sm">
                {{ $returns->withQueryString()->links() }}
            </div>
        @endif

    </div>
</div>

{{-- ================================================================
     MODAL KONFIRMASI HAPUS RIWAYAT RETUR (CUSTOM, DI TENGAH LAYAR)
     Mengganti confirm() bawaan browser sepenuhnya.
     Warna disesuaikan dengan ciri khas Zalina Fashion (#8b1e3f).
================================================================ --}}
<div id="delete-return-modal"
     class="fixed inset-0 z-[999] hidden items-center justify-center px-4">

    {{-- OVERLAY --}}
    <div id="delete-return-overlay"
         class="absolute inset-0 bg-[#2b0713]/60 opacity-0 backdrop-blur-sm transition-opacity duration-300"
         onclick="closeDeleteReturnModal()"></div>

    {{-- MODAL BOX --}}
    <div id="delete-return-box"
         class="relative z-10 w-full max-w-md translate-y-4 scale-95 overflow-hidden rounded-[28px] border border-[#f0d9de] bg-white opacity-0 shadow-2xl shadow-[#42101f]/30 transition-all duration-300 ease-out">

        {{-- ACCENT TOP BAR --}}
        <div class="h-1.5 w-full bg-gradient-to-r from-[#8b1e3f] via-[#c23a5e] to-[#8b1e3f]"></div>

        <div class="p-6 sm:p-8">

            {{-- ICON --}}
            <div id="delete-return-icon"
                 class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-[#fdeef1] text-[#8b1e3f] ring-8 ring-[#fdeef1]/60 transition-transform duration-500">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M9 6V4h6v2m-9 0l1 15h10l1-15M10 11v6M14 11v6"/>
                </svg>
            </div>

            <div class="text-center">
                <div class="mb-1 inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.25em] text-[#8b1e3f]">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#8b1e3f]"></span>
                    Konfirmasi Diperlukan
                </div>

                <h3 class="font-serif text-xl font-bold text-[#42101f] sm:text-2xl">
                    Hapus Riwayat Retur?
                </h3>

                <p class="mx-auto mt-3 max-w-sm text-sm leading-6 text-slate-500">
                    Yakin ingin menghapus riwayat retur pesanan
                    <span id="delete-return-order-number" class="font-bold text-[#42101f]"></span>?
                    Tindakan ini akan menghapus seluruh data retur dari daftar admin dan
                    <span class="font-bold text-red-600">tidak dapat dikembalikan</span>.
                </p>
            </div>

            <div class="mt-7 flex gap-3">
                <button type="button"
                        onclick="closeDeleteReturnModal()"
                        class="flex-1 rounded-2xl border border-[#eadcdf] bg-white px-4 py-3.5 text-sm font-bold text-slate-600 transition hover:border-[#8b1e3f] hover:bg-[#fff8fa] hover:text-[#8b1e3f]">
                    Batal
                </button>

                <button type="button"
                        onclick="confirmDeleteReturnModal()"
                        class="group flex-1 rounded-2xl bg-gradient-to-r from-[#8b1e3f] to-[#6f1732] px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-[#8b1e3f]/30 transition hover:shadow-xl hover:shadow-[#8b1e3f]/40 active:scale-[0.98]">
                    <span class="inline-flex items-center justify-center gap-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M9 6V4h6v2m-9 0l1 15h10l1-15M10 11v6M14 11v6"/>
                        </svg>
                        Ya, Hapus
                    </span>
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ================================================================
     LIGHTBOX GALERI BUKTI FOTO RETUR
     Modal tunggal dipakai bersama oleh semua kartu retur di halaman ini.
     Mendukung navigasi berikutnya/sebelumnya kalau foto lebih dari satu.
================================================================ --}}
<div id="return-gallery-modal"
     class="fixed inset-0 z-[999] hidden items-center justify-center px-4">

    {{-- OVERLAY --}}
    <div id="return-gallery-overlay"
         class="absolute inset-0 bg-[#2b0713]/70 opacity-0 backdrop-blur-sm transition-opacity duration-300"
         onclick="closeReturnGallery()"></div>

    {{-- MODAL BOX --}}
    <div id="return-gallery-box"
         class="relative z-10 w-full max-w-3xl scale-95 overflow-hidden rounded-[28px] border border-[#f0d9de] bg-white opacity-0 shadow-2xl shadow-[#42101f]/30 transition-all duration-300 ease-out">

        {{-- ACCENT TOP BAR --}}
        <div class="h-1.5 w-full bg-gradient-to-r from-[#8b1e3f] via-[#c23a5e] to-[#8b1e3f]"></div>

        {{-- HEADER --}}
        <div class="flex items-center justify-between gap-3 px-5 py-4 sm:px-6">
            <div class="min-w-0">
                <div class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#8b1e3f]">
                    Bukti Foto Produk
                </div>
                <div id="return-gallery-order-label" class="mt-0.5 truncate text-sm font-black text-[#42101f]"></div>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <span id="return-gallery-counter" class="rounded-full bg-[#f8e9ee] px-3 py-1.5 text-xs font-bold text-[#8b1e3f]"></span>

                <button type="button"
                        onclick="closeReturnGallery()"
                        aria-label="Tutup"
                        class="flex h-9 w-9 items-center justify-center rounded-xl border border-[#eadcdf] bg-white text-slate-500 transition hover:border-[#8b1e3f] hover:text-[#8b1e3f]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- IMAGE --}}
        <div class="relative flex items-center justify-center bg-[#faf7f8] px-4 py-6 sm:px-10">

            <button type="button"
                    id="return-gallery-prev"
                    onclick="showPrevReturnImage()"
                    aria-label="Foto sebelumnya"
                    class="return-gallery-nav-btn hidden flex absolute left-3 top-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 p-2 text-[#8b1e3f] shadow transition hover:bg-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            <img id="return-gallery-image"
                 src=""
                 alt="Bukti foto produk retur"
                 class="max-h-[65vh] w-full rounded-xl object-contain">

            <button type="button"
                    id="return-gallery-next"
                    onclick="showNextReturnImage()"
                    aria-label="Foto berikutnya"
                    class="return-gallery-nav-btn hidden flex absolute right-3 top-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 p-2 text-[#8b1e3f] shadow transition hover:bg-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        {{-- FOOTER --}}
        <div class="flex items-center justify-between gap-3 border-t border-[#f0e5e8] px-5 py-4 sm:px-6">

            <a id="return-gallery-download"
               href=""
               download=""
               class="ml-auto inline-flex items-center gap-2 rounded-full bg-[#8b1e3f] px-4 py-2 text-xs font-black text-white transition hover:bg-[#6f1832]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14"/>
                </svg>
                Download Foto Ini
            </a>
        </div>

    </div>
</div>

{{-- ================================================================
     JAVASCRIPT
================================================================ --}}
<script>
    let deleteReturnTargetId = null;

    function openDeleteReturnModal(orderId, orderNumber) {
        deleteReturnTargetId = orderId;

        document.getElementById('delete-return-order-number').textContent = orderNumber;

        const modal = document.getElementById('delete-return-modal');
        const overlay = document.getElementById('delete-return-overlay');
        const box = document.getElementById('delete-return-box');
        const icon = document.getElementById('delete-return-icon');

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        requestAnimationFrame(() => {
            overlay.classList.remove('opacity-0');
            overlay.classList.add('opacity-100');

            box.classList.remove('scale-95', 'opacity-0', 'translate-y-4');
            box.classList.add('scale-100', 'opacity-100', 'translate-y-0');

            icon.classList.add('scale-110');
            setTimeout(() => icon.classList.remove('scale-110'), 300);
        });

        document.body.classList.add('overflow-hidden');
    }

    function closeDeleteReturnModal() {
        const modal = document.getElementById('delete-return-modal');
        const overlay = document.getElementById('delete-return-overlay');
        const box = document.getElementById('delete-return-box');

        overlay.classList.remove('opacity-100');
        overlay.classList.add('opacity-0');

        box.classList.remove('scale-100', 'opacity-100', 'translate-y-0');
        box.classList.add('scale-95', 'opacity-0', 'translate-y-4');

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 250);

        document.body.classList.remove('overflow-hidden');
        deleteReturnTargetId = null;
    }

    function confirmDeleteReturnModal() {
        if (!deleteReturnTargetId) {
            return;
        }

        const form = document.getElementById('delete-form-' + deleteReturnTargetId);

        if (form) {
            form.submit();
        }
    }

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') {
            return;
        }

        /*
        | Hanya tutup kalau modal memang sedang terbuka.
        | Sebelumnya fungsi ini dijalankan pada SETIAP penekanan Escape,
        | sehingga ikut menghapus class overflow-hidden dari <body>
        | meski modal tidak pernah dibuka — berpotensi merusak
        | scroll-lock milik komponen lain di halaman yang sama.
        */

        const modal = document.getElementById('delete-return-modal');

        if (modal && !modal.classList.contains('hidden')) {
            closeDeleteReturnModal();
        }

        const galleryModal = document.getElementById('return-gallery-modal');

        if (galleryModal && !galleryModal.classList.contains('hidden')) {
            closeReturnGallery();
        }
    });

    /*
    |--------------------------------------------------------------
    | LIGHTBOX GALERI BUKTI FOTO RETUR
    |--------------------------------------------------------------
    | Dipakai bersama oleh semua kartu retur. Dipanggil dengan array
    | foto ({url, download_name}), index foto yang diklik, dan label
    | nomor pesanan supaya konteksnya jelas di header modal.
    */
    let returnGalleryImages = [];
    let returnGalleryIndex = 0;

    function openReturnGallery(images, startIndex, orderLabel) {
        if (!Array.isArray(images) || images.length === 0) {
            return;
        }

        returnGalleryImages = images;
        returnGalleryIndex = Number.isInteger(startIndex) ? startIndex : 0;

        document.getElementById('return-gallery-order-label').textContent = orderLabel || '';
        renderReturnGalleryImage();

        const modal = document.getElementById('return-gallery-modal');
        const overlay = document.getElementById('return-gallery-overlay');
        const box = document.getElementById('return-gallery-box');

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        requestAnimationFrame(() => {
            overlay.classList.remove('opacity-0');
            overlay.classList.add('opacity-100');

            box.classList.remove('scale-95', 'opacity-0');
            box.classList.add('scale-100', 'opacity-100');
        });

        document.body.classList.add('overflow-hidden');
    }

    function closeReturnGallery() {
        const modal = document.getElementById('return-gallery-modal');
        const overlay = document.getElementById('return-gallery-overlay');
        const box = document.getElementById('return-gallery-box');

        overlay.classList.remove('opacity-100');
        overlay.classList.add('opacity-0');

        box.classList.remove('scale-100', 'opacity-100');
        box.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 200);

        document.body.classList.remove('overflow-hidden');
        returnGalleryImages = [];
        returnGalleryIndex = 0;
    }

    function renderReturnGalleryImage() {
        if (returnGalleryImages.length === 0) {
            return;
        }

        const total = returnGalleryImages.length;
        const current = returnGalleryImages[returnGalleryIndex];

        const img = document.getElementById('return-gallery-image');
        const counter = document.getElementById('return-gallery-counter');
        const download = document.getElementById('return-gallery-download');

        img.src = current.url;
        img.alt = 'Bukti foto produk retur - foto ke-' + (returnGalleryIndex + 1);
        counter.textContent = (returnGalleryIndex + 1) + ' / ' + total;
        download.href = current.url;
        download.setAttribute('download', current.download_name || '');

        const hasMultiple = total > 1;

        ['return-gallery-prev', 'return-gallery-next'].forEach((id) => {
            const el = document.getElementById(id);
            if (el) {
                el.classList.toggle('hidden', !hasMultiple);
            }
        });
    }

    function showNextReturnImage() {
        if (returnGalleryImages.length === 0) {
            return;
        }

        returnGalleryIndex = (returnGalleryIndex + 1) % returnGalleryImages.length;
        renderReturnGalleryImage();
    }

    function showPrevReturnImage() {
        if (returnGalleryImages.length === 0) {
            return;
        }

        returnGalleryIndex = (returnGalleryIndex - 1 + returnGalleryImages.length) % returnGalleryImages.length;
        renderReturnGalleryImage();
    }

    document.addEventListener('keydown', function (e) {
        const galleryModal = document.getElementById('return-gallery-modal');

        if (!galleryModal || galleryModal.classList.contains('hidden')) {
            return;
        }

        if (e.key === 'ArrowRight') {
            showNextReturnImage();
        } else if (e.key === 'ArrowLeft') {
            showPrevReturnImage();
        }
    });

    function toggleReturnDetail(id) {
        const detail = document.getElementById(id);
        const icon = document.getElementById('icon-' + id);

        if (!detail) {
            return;
        }

        detail.classList.toggle('hidden');

        if (icon) {
            icon.classList.toggle('rotate-180');
        }
    }
</script>
@endsection