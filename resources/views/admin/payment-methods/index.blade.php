@extends('layouts.admin')

@section('title', 'Metode Pembayaran')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#5b1025] via-[#761d38] to-[#a64b67] px-6 py-8 text-white shadow-xl md:px-8">
        <div class="absolute -right-20 -top-24 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute -bottom-28 right-24 h-64 w-64 rounded-full bg-[#e8c27d]/20 blur-3xl"></div>

        <div class="relative flex flex-col justify-between gap-6 md:flex-row md:items-center">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-medium backdrop-blur">
                    <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                    Payment Management
                </div>

                <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                    Metode Pembayaran
                </h1>

                <p class="mt-2 max-w-xl text-sm leading-6 text-white/75">
                    Kelola rekening bank dan e-wallet yang digunakan pelanggan
                    untuk menyelesaikan pembayaran pesanan Zalina Fashion.
                </p>
            </div>

            <div class="rounded-2xl border border-white/20 bg-white/10 px-5 py-4 backdrop-blur">
                <p class="text-xs text-white/70">Total metode</p>
                <p class="mt-1 text-3xl font-bold">
                    {{ $methods->count() }}
                </p>
                <p class="mt-1 text-xs text-white/70">
                    Metode pembayaran terdaftar
                </p>
            </div>
        </div>
    </div>

    {{-- SUMMARY --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">
                        Aktif
                    </p>

                    <p class="mt-2 text-3xl font-bold text-emerald-900">
                        {{ $methods->where('is_active', true)->count() }}
                    </p>
                </div>

                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-xs text-emerald-700/80">
                Bisa dipilih oleh pelanggan
            </p>
        </div>

        <div class="rounded-2xl border border-rose-100 bg-gradient-to-br from-rose-50 to-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-rose-700">
                        Nonaktif
                    </p>

                    <p class="mt-2 text-3xl font-bold text-rose-900">
                        {{ $methods->where('is_active', false)->count() }}
                    </p>
                </div>

                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-rose-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-xs text-rose-700/80">
                Tidak ditampilkan saat checkout
            </p>
        </div>

        <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-amber-700">
                        Jenis
                    </p>

                    <p class="mt-2 text-3xl font-bold text-amber-900">
                        {{ $methods->unique('type')->count() }}
                    </p>
                </div>

                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M3 7h18M3 12h18M3 17h18"/>
                    </svg>
                </div>
            </div>

            <p class="mt-3 text-xs text-amber-700/80">
                Bank transfer dan e-wallet
            </p>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="grid items-start gap-6 lg:grid-cols-[360px_1fr]">

        {{-- FORM TAMBAH --}}
        <div class="rounded-[2rem] border border-maroon-100 bg-white p-6 shadow-sm lg:sticky lg:top-24">
            <div class="mb-6">
                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-maroon-100 text-maroon-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M12 4v16m8-8H4"/>
                    </svg>
                </div>

                <h2 class="text-xl font-bold text-maroon-950">
                    Tambah Metode
                </h2>

                <p class="mt-1 text-sm leading-6 text-maroon-500">
                    Tambahkan rekening bank atau e-wallet baru.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('admin.payment-methods.store') }}"
                class="space-y-4"
            >
                @csrf

                {{-- NAMA --}}
                <div>
                    <label class="mb-2 block text-sm font-semibold text-maroon-700">
                        Nama Metode
                    </label>

                    <input
                        type="text"
                        name="name"
                        required
                        placeholder="Contoh: BRI Transfer"
                        class="w-full rounded-xl border border-maroon-100 bg-white px-4 py-3 text-sm text-maroon-900 outline-none transition placeholder:text-maroon-300 focus:border-maroon-400 focus:ring-4 focus:ring-maroon-100"
                    >
                </div>

                {{-- TYPE --}}
                <div>
                    <label class="mb-2 block text-sm font-semibold text-maroon-700">
                        Jenis Pembayaran
                    </label>

                    <select
                        name="type"
                        class="w-full rounded-xl border border-maroon-100 bg-white px-4 py-3 text-sm text-maroon-900 outline-none transition focus:border-maroon-400 focus:ring-4 focus:ring-maroon-100"
                    >
                        <option value="bank_transfer">
                            Bank Transfer
                        </option>

                        <option value="ewallet">
                            E-Wallet
                        </option>
                    </select>
                </div>

                {{-- PEMILIK --}}
                <div>
                    <label class="mb-2 block text-sm font-semibold text-maroon-700">
                        Nama Pemilik Rekening
                    </label>

                    <input
                        type="text"
                        name="account_name"
                        required
                        placeholder="Nama pemilik rekening"
                        class="w-full rounded-xl border border-maroon-100 bg-white px-4 py-3 text-sm text-maroon-900 outline-none transition placeholder:text-maroon-300 focus:border-maroon-400 focus:ring-4 focus:ring-maroon-100"
                    >
                </div>

                {{-- NOMOR --}}
                <div>
                    <label class="mb-2 block text-sm font-semibold text-maroon-700">
                        Nomor Rekening / Akun
                    </label>

                    <input
                        type="text"
                        name="account_number"
                        required
                        placeholder="Contoh: 1234567890"
                        class="w-full rounded-xl border border-maroon-100 bg-white px-4 py-3 text-sm text-maroon-900 outline-none transition placeholder:text-maroon-300 focus:border-maroon-400 focus:ring-4 focus:ring-maroon-100"
                    >
                </div>

                {{-- INSTRUKSI --}}
                <div>
                    <label class="mb-2 block text-sm font-semibold text-maroon-700">
                        Instruksi Pembayaran
                    </label>

                    <textarea
                        name="instructions"
                        rows="3"
                        placeholder="Contoh: Transfer sesuai nominal pesanan."
                        class="w-full resize-none rounded-xl border border-maroon-100 bg-white px-4 py-3 text-sm text-maroon-900 outline-none transition placeholder:text-maroon-300 focus:border-maroon-400 focus:ring-4 focus:ring-maroon-100"
                    ></textarea>
                </div>

                {{-- URUTAN --}}
                <div>
                    <label class="mb-2 block text-sm font-semibold text-maroon-700">
                        Urutan Tampilan
                    </label>

                    <input
                        type="number"
                        name="sort_order"
                        value="0"
                        min="0"
                        class="w-full rounded-xl border border-maroon-100 bg-white px-4 py-3 text-sm text-maroon-900 outline-none transition focus:border-maroon-400 focus:ring-4 focus:ring-maroon-100"
                    >
                </div>

                {{-- STATUS --}}
                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-maroon-100 bg-maroon-50/50 px-4 py-3">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        checked
                        class="h-4 w-4 rounded border-maroon-300 text-maroon-700 focus:ring-maroon-300"
                    >

                    <span class="text-sm font-semibold text-maroon-700">
                        Aktifkan metode pembayaran
                    </span>
                </label>

                {{-- BUTTON --}}
                <button
                    type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-maroon-700 to-maroon-900 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-maroon-900/10 transition hover:-translate-y-0.5 hover:shadow-xl"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4v16m8-8H4"/>
                    </svg>

                    Tambah Metode Pembayaran
                </button>
            </form>
        </div>

        {{-- LIST METODE --}}
        <div class="space-y-4">
            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-xl font-bold text-maroon-950">
                        Daftar Metode Pembayaran
                    </h2>

                    <p class="mt-1 text-sm text-maroon-500">
                        Klik salah satu metode untuk melihat dan mengedit detail.
                    </p>
                </div>

                <span class="w-fit rounded-full bg-maroon-100 px-3 py-1.5 text-xs font-bold text-maroon-700">
                    {{ $methods->count() }} metode
                </span>
            </div>

            @forelse($methods as $m)
                @php
                    $methodName = strtolower(trim($m->name ?? ''));
                    $logo = null;

                    if (str_contains($methodName, 'bca')) {
                        $logo = 'bca.png';
                    } elseif (str_contains($methodName, 'bri')) {
                        $logo = 'bri.png';
                    } elseif (str_contains($methodName, 'bni')) {
                        $logo = 'bni.png';
                    } elseif (str_contains($methodName, 'mandiri')) {
                        $logo = 'mandiri.png';
                    } elseif (str_contains($methodName, 'panin')) {
                        $logo = 'panin.png';
                    } elseif (str_contains($methodName, 'dana')) {
                        $logo = 'dana.png';
                    } elseif (str_contains($methodName, 'ovo')) {
                        $logo = 'ovo.png';
                    } elseif (str_contains($methodName, 'gopay') || str_contains($methodName, 'go pay')) {
                        $logo = 'gopay.png';
                    } elseif (str_contains($methodName, 'shopeepay') || str_contains($methodName, 'shopee pay')) {
                        $logo = 'shopeepay.png';
                    }
                @endphp

                <details class="group overflow-hidden rounded-[2rem] border border-maroon-100 bg-white shadow-sm transition hover:shadow-md">
                    <summary class="cursor-pointer list-none p-5 sm:p-6">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex min-w-0 items-center gap-4">
                                {{-- LOGO --}}
                                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-maroon-100 bg-maroon-50">
                                    @if($logo && file_exists(public_path('images/payment-methods/' . $logo)))
                                        <img
                                            src="{{ asset('images/payment-methods/' . $logo) }}"
                                            alt="{{ $m->name }}"
                                            class="h-12 w-12 object-contain"
                                        >
                                    @else
                                        <svg class="h-8 w-8 text-maroon-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                  d="M3 21h18M5 21V10m4 11V10m6 11V10m4 11V10M2 10h20L12 3 2 10z"/>
                                        </svg>
                                    @endif
                                </div>

                                {{-- NAME --}}
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-lg font-bold text-maroon-950">
                                            {{ $m->name }}
                                        </h3>

                                        <span class="rounded-full border px-2.5 py-1 text-xs font-bold
                                            {{ $m->is_active
                                                ? 'border-emerald-200 bg-emerald-100 text-emerald-700'
                                                : 'border-rose-200 bg-rose-100 text-rose-700'
                                            }}">
                                            {{ $m->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>

                                    <p class="mt-1 text-sm text-maroon-500">
                                        {{ $m->type === 'bank_transfer' ? 'Bank Transfer' : 'E-Wallet' }}
                                    </p>
                                </div>
                            </div>

                            {{-- ACCOUNT NUMBER --}}
                            <div class="hidden text-right sm:block">
                                <p class="text-xs text-maroon-400">
                                    Nomor Rekening
                                </p>

                                <p class="mt-1 font-bold text-maroon-900">
                                    {{ $m->account_number }}
                                </p>

                                <p class="mt-2 text-xs font-semibold text-maroon-400">
                                    Klik untuk edit
                                </p>
                            </div>

                            <svg class="h-5 w-5 shrink-0 text-maroon-400 transition group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                      d="M6 9l6 6 6-6"/>
                            </svg>
                        </div>

                        <div class="mt-4 rounded-xl bg-maroon-50 px-4 py-3 sm:hidden">
                            <p class="text-xs text-maroon-400">
                                Nomor Rekening
                            </p>

                            <p class="mt-1 break-all text-sm font-bold text-maroon-900">
                                {{ $m->account_number }}
                            </p>
                        </div>
                    </summary>

                    {{-- EDIT AREA --}}
                    <div class="border-t border-maroon-100 bg-maroon-50/40 p-5 sm:p-6">
                        <div class="mb-5 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-maroon-100 bg-white p-4">
                                <p class="text-xs font-semibold uppercase tracking-wider text-maroon-400">
                                    Pemilik Rekening
                                </p>

                                <p class="mt-2 font-bold text-maroon-900">
                                    {{ $m->account_name }}
                                </p>
                            </div>

                            <div class="rounded-2xl border border-maroon-100 bg-white p-4">
                                <p class="text-xs font-semibold uppercase tracking-wider text-maroon-400">
                                    Nomor Rekening
                                </p>

                                <p class="mt-2 break-all font-bold text-maroon-900">
                                    {{ $m->account_number }}
                                </p>
                            </div>
                        </div>

                        <form
                            method="POST"
                            action="{{ route('admin.payment-methods.update', $m) }}"
                            class="grid gap-4 sm:grid-cols-2"
                        >
                            @csrf
                            @method('PUT')

                            {{-- NAMA --}}
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-maroon-700">
                                    Nama Metode
                                </label>

                                <input
                                    type="text"
                                    name="name"
                                    value="{{ $m->name }}"
                                    required
                                    class="w-full rounded-xl border border-maroon-100 bg-white px-4 py-3 text-sm text-maroon-900 outline-none transition focus:border-maroon-400 focus:ring-4 focus:ring-maroon-100"
                                >
                            </div>

                            {{-- TYPE --}}
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-maroon-700">
                                    Jenis Pembayaran
                                </label>

                                <select
                                    name="type"
                                    class="w-full rounded-xl border border-maroon-100 bg-white px-4 py-3 text-sm text-maroon-900 outline-none transition focus:border-maroon-400 focus:ring-4 focus:ring-maroon-100"
                                >
                                    <option value="bank_transfer" @selected($m->type === 'bank_transfer')>
                                        Bank Transfer
                                    </option>

                                    <option value="ewallet" @selected($m->type === 'ewallet')>
                                        E-Wallet
                                    </option>
                                </select>
                            </div>

                            {{-- PEMILIK --}}
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-maroon-700">
                                    Nama Pemilik
                                </label>

                                <input
                                    type="text"
                                    name="account_name"
                                    value="{{ $m->account_name }}"
                                    required
                                    class="w-full rounded-xl border border-maroon-100 bg-white px-4 py-3 text-sm text-maroon-900 outline-none transition focus:border-maroon-400 focus:ring-4 focus:ring-maroon-100"
                                >
                            </div>

                            {{-- NOMOR --}}
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-maroon-700">
                                    Nomor Rekening
                                </label>

                                <input
                                    type="text"
                                    name="account_number"
                                    value="{{ $m->account_number }}"
                                    required
                                    class="w-full rounded-xl border border-maroon-100 bg-white px-4 py-3 text-sm text-maroon-900 outline-none transition focus:border-maroon-400 focus:ring-4 focus:ring-maroon-100"
                                >
                            </div>

                            {{-- INSTRUKSI --}}
                            <div class="sm:col-span-2">
                                <label class="mb-2 block text-sm font-semibold text-maroon-700">
                                    Instruksi Pembayaran
                                </label>

                                <textarea
                                    name="instructions"
                                    rows="3"
                                    class="w-full resize-none rounded-xl border border-maroon-100 bg-white px-4 py-3 text-sm text-maroon-900 outline-none transition focus:border-maroon-400 focus:ring-4 focus:ring-maroon-100"
                                >{{ $m->instructions }}</textarea>
                            </div>

                            {{-- URUTAN --}}
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-maroon-700">
                                    Urutan Tampilan
                                </label>

                                <input
                                    type="number"
                                    name="sort_order"
                                    value="{{ $m->sort_order }}"
                                    min="0"
                                    class="w-full rounded-xl border border-maroon-100 bg-white px-4 py-3 text-sm text-maroon-900 outline-none transition focus:border-maroon-400 focus:ring-4 focus:ring-maroon-100"
                                >
                            </div>

                            {{-- STATUS --}}
                            <div class="flex items-end">
                                <label class="flex w-full cursor-pointer items-center gap-3 rounded-xl border border-maroon-100 bg-white px-4 py-3">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        value="1"
                                        @checked($m->is_active)
                                        class="h-4 w-4 rounded border-maroon-300 text-maroon-700 focus:ring-maroon-300"
                                    >

                                    <span class="text-sm font-semibold text-maroon-700">
                                        Metode Aktif
                                    </span>
                                </label>
                            </div>

                            {{-- SAVE --}}
                            <div class="sm:col-span-2">
                                <button
                                    type="submit"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-maroon-700 to-maroon-900 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-maroon-900/10 transition hover:-translate-y-0.5 hover:shadow-xl"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 13l4 4L19 7"/>
                                    </svg>

                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>

                        {{-- DELETE --}}
                        <form
                            method="POST"
                            action="{{ route('admin.payment-methods.destroy', $m) }}"
                            class="mt-4"
                            onsubmit="return confirm('Yakin ingin menghapus metode pembayaran ini?')"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="flex items-center gap-2 text-sm font-semibold text-rose-600 transition hover:text-rose-800"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                          d="M6 7h12M9 7V4h6v3m-8 0l1 13h6l1-13M10 11v5m4-5v5"/>
                                </svg>

                                Hapus Metode Pembayaran
                            </button>
                        </form>
                    </div>
                </details>
            @empty
                <div class="rounded-[2rem] border border-maroon-100 bg-white px-6 py-16 text-center shadow-sm">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-maroon-50 text-maroon-500">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M3 7h18M3 12h18M3 17h18"/>
                        </svg>
                    </div>

                    <h3 class="mt-4 text-lg font-bold text-maroon-950">
                        Belum Ada Metode Pembayaran
                    </h3>

                    <p class="mt-2 text-sm text-maroon-500">
                        Tambahkan rekening bank atau e-wallet pertama Anda.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection