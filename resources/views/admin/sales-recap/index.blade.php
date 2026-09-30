@extends('layouts.admin')

@section('title', 'Rekap Penjualan — Zalina Fashion')

@section('content')

@php
    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

    $periodTabs = [
        'harian' => 'Harian',
        'mingguan' => 'Mingguan',
        'bulanan' => 'Bulanan',
        'tahunan' => 'Tahunan',
    ];

    $queryBase = array_filter([
        'period' => $period,
        'date' => $date->toDateString(),
        'include_closed' => $includeClosed ? 1 : null,
    ]);
@endphp

<div class="space-y-6 pb-10">

    {{-- HEADER --}}
    <div class="relative overflow-hidden rounded-[2rem] bg-[#3b101d] px-6 py-8 text-[#fff8f2] shadow-[0_20px_60px_rgba(59,16,29,.22)] sm:px-9">
        <div class="absolute -right-16 -top-20 h-64 w-64 rounded-full bg-[#d9ad68]/10 blur-3xl"></div>

        <div class="relative flex flex-col justify-between gap-5 md:flex-row md:items-end">
            <div>
                <p class="font-serif text-sm italic text-[#e7c487]">Zalina Admin</p>
                <h1 class="mt-1 font-serif text-2xl sm:text-3xl">Rekap Penjualan &amp; Laporan Keuangan</h1>
                <p class="mt-2 max-w-xl text-sm leading-6 text-[#ead6d9]">
                    Data di halaman ini diambil dari buku besar penjualan (bukan riwayat pesanan) —
                    tetap utuh walau riwayat pesanan dihapus di panel Orders.
                </p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/10 px-5 py-4">
                <p class="text-[10px] uppercase tracking-[.18em] text-[#e7c487]">Transaksi berjalan (belum closing)</p>
                <p class="mt-1 text-2xl font-bold">{{ number_format($unclosedCount) }}</p>
            </div>
        </div>
    </div>

    {{-- FLASH --}}
    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('info'))
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700">
            {{ session('info') }}
        </div>
    @endif

    {{-- FILTER PERIODE --}}
    <div class="rounded-3xl border border-maroon-100 bg-white p-5">
        <form method="GET" action="{{ route('admin.sales-recap.index') }}" class="flex flex-wrap items-end gap-3">

            <div class="flex flex-wrap gap-2">
                @foreach ($periodTabs as $key => $label)
                    <a
                        href="{{ route('admin.sales-recap.index', array_merge($queryBase, ['period' => $key])) }}"
                        class="rounded-xl px-4 py-2 text-sm font-semibold transition {{ $period === $key ? 'bg-[#631f2b] text-white' : 'bg-maroon-50 text-maroon-700 hover:bg-maroon-100' }}"
                    >
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-maroon-500">Tanggal acuan</label>
                <input type="date" name="date" value="{{ $date->toDateString() }}"
                       class="rounded-xl border border-maroon-200 px-3 py-2 text-sm">
            </div>

            <label class="flex items-center gap-2 pb-2 text-xs font-semibold text-maroon-600">
                <input type="checkbox" name="include_closed" value="1" {{ $includeClosed ? 'checked' : '' }}
                       class="rounded border-maroon-300 text-[#631f2b]">
                Sertakan yang sudah ditutup buku (semua histori)
            </label>

            <button type="submit" class="rounded-xl bg-maroon-800 px-4 py-2 text-sm font-semibold text-white hover:bg-maroon-900">
                Cek Rekap
            </button>

            <a
                href="{{ route('admin.sales-recap.export', $queryBase) }}"
                class="ml-auto inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v13m0 0l-4-4m4 4l4-4M4 19h16"/>
                </svg>
                Export Excel
            </a>
        </form>

        <p class="mt-3 text-xs text-maroon-400">
            Menampilkan periode {{ $from->translatedFormat('d M Y') }} – {{ $to->translatedFormat('d M Y') }}
        </p>
    </div>

    {{-- RINGKASAN --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border-l-4 border-[#631f2b] bg-[#fbf3f5] p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#7c2638]">Pendapatan Kotor</p>
            <p class="mt-1 text-2xl font-bold text-[#481f2d]">{{ $rp($summary['grand_total']) }}</p>
        </div>

        <div class="rounded-2xl border-l-4 border-emerald-400 bg-emerald-50 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Pendapatan Bersih</p>
            <p class="mt-1 text-2xl font-bold text-emerald-900">{{ $rp($summary['net_revenue']) }}</p>
            <p class="mt-1 text-[11px] text-emerald-700/70">Kotor − ongkir riil ke kurir</p>
        </div>

        <div class="rounded-2xl border-l-4 border-amber-400 bg-amber-50 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Biaya Admin Terkumpul</p>
            <p class="mt-1 text-2xl font-bold text-amber-900">{{ $rp($summary['admin_fee']) }}</p>
        </div>

        <div class="rounded-2xl border-l-4 border-maroon-300 bg-maroon-50 p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-maroon-600">Jumlah Transaksi</p>
            <p class="mt-1 text-2xl font-bold text-maroon-900">{{ number_format($summary['count']) }}</p>
            <p class="mt-1 text-[11px] text-maroon-500">{{ number_format($summary['quantity_total']) }} pcs terjual</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-maroon-100 bg-white p-4 text-sm">
            <p class="text-maroon-400">Diskon diberikan</p>
            <p class="mt-1 font-bold text-maroon-900">- {{ $rp($summary['discount_total']) }}</p>
        </div>

        <div class="rounded-2xl border border-maroon-100 bg-white p-4 text-sm">
            <p class="text-maroon-400">Ongkir ditagihkan ke pembeli</p>
            <p class="mt-1 font-bold text-maroon-900">{{ $rp($summary['shipping_total']) }}</p>
        </div>

        <div class="rounded-2xl border border-maroon-100 bg-white p-4 text-sm">
            <p class="text-maroon-400">Ongkir riil ke kurir (pengeluaran)</p>
            <p class="mt-1 font-bold text-rose-700">- {{ $rp($summary['shipping_cost_actual']) }}</p>
        </div>
    </div>

    {{-- BREAKDOWN PER PERIODE --}}
    @if(count($breakdown) > 0)
        <div class="overflow-hidden rounded-3xl border border-maroon-100 bg-white">
            <div class="border-b border-maroon-100 px-6 py-4">
                <p class="font-serif text-sm italic text-[#8a4b5c]">Rekap per {{ $periodTabs[$period] ?? 'Periode' }}</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-maroon-50 text-xs uppercase tracking-wide text-maroon-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Periode</th>
                            <th class="px-4 py-3 text-right">Transaksi</th>
                            <th class="px-4 py-3 text-right">Qty</th>
                            <th class="px-4 py-3 text-right">Pendapatan Kotor</th>
                            <th class="px-4 py-3 text-right">Pendapatan Bersih</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-maroon-50">
                        @foreach ($breakdown as $b)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-maroon-900">{{ $b['label'] }}</td>
                                <td class="px-4 py-3 text-right">{{ $b['count'] }}</td>
                                <td class="px-4 py-3 text-right">{{ $b['quantity_total'] }}</td>
                                <td class="px-4 py-3 text-right font-semibold">{{ $rp($b['grand_total']) }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-emerald-700">{{ $rp($b['net_revenue']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- AKSI --}}
    <div class="flex flex-wrap gap-3">
        <a href="#form-offline" class="rounded-xl bg-[#631f2b] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#7c2d3a]">
            + Catat Penjualan Offline
        </a>

        <form method="POST" action="{{ route('admin.sales-recap.close') }}"
              onsubmit="return confirmClosing();">
            @csrf

            <button type="submit" class="rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-50">
                Tutup Buku / Reset Pendapatan Berjalan
            </button>
        </form>
    </div>

    {{-- DETAIL TRANSAKSI --}}
    <div class="overflow-hidden rounded-3xl border border-maroon-100 bg-white">
        <div class="border-b border-maroon-100 px-6 py-4">
            <p class="font-serif text-sm italic text-[#8a4b5c]">Detail Transaksi</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-maroon-50 text-xs uppercase tracking-wide text-maroon-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Channel</th>
                        <th class="px-4 py-3 text-left">Referensi</th>
                        <th class="px-4 py-3 text-left">Produk</th>
                        <th class="px-4 py-3 text-right">Qty</th>
                        <th class="px-4 py-3 text-right">Grand Total</th>
                        <th class="px-4 py-3 text-right">Bersih</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-maroon-50">
                    @forelse ($entries as $e)
                        <tr>
                            <td class="px-4 py-3 text-maroon-600">
                                {{ optional($e->entry_date)->format('d M Y') }}
                            </td>

                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $e->channel === 'online' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $e->channel === 'online' ? 'Online' : 'Offline' }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-maroon-600">
                                {{ $e->order_number ?: ('#' . $e->id) }}
                            </td>

                            <td class="px-4 py-3 text-maroon-600">
                                @php
                                    $lines = collect($e->items_snapshot ?? [])->take(2);
                                @endphp

                                {{ $lines->pluck('name')->implode(', ') }}

                                @if(count($e->items_snapshot ?? []) > 2)
                                    +{{ count($e->items_snapshot) - 2 }} lainnya
                                @endif
                            </td>

                            <td class="px-4 py-3 text-right">
                                {{ $e->quantity_total }}
                            </td>

                            <td class="px-4 py-3 text-right font-semibold text-maroon-900">
                                {{ $rp($e->grand_total) }}
                            </td>

                            <td class="px-4 py-3 text-right font-semibold text-emerald-700">
                                {{ $rp($e->net_revenue) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm italic text-maroon-400">
                                Belum ada transaksi pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-maroon-100 px-6 py-4">
            {{ $entries->links() }}
        </div>
    </div>

    {{-- FORM CATAT PENJUALAN OFFLINE --}}
    <div id="form-offline" class="scroll-mt-6 overflow-hidden rounded-3xl border border-maroon-100 bg-white">
        <div class="border-b border-maroon-100 px-6 py-4">
            <p class="font-serif text-sm italic text-[#8a4b5c]">Catat Penjualan Offline</p>
            <p class="mt-1 text-xs text-maroon-400">
                Untuk transaksi di toko fisik / kasir manual, di luar sistem checkout online.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.sales-recap.offline') }}" class="space-y-5 p-6">
            @csrf

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-maroon-500">
                        Tanggal transaksi
                    </label>

                    <input
                        type="date"
                        name="entry_date"
                        required
                        value="{{ now()->toDateString() }}"
                        class="w-full rounded-xl border border-maroon-200 px-3 py-2 text-sm"
                    >
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold text-maroon-500">
                        Nama pembeli (opsional)
                    </label>

                    <input
                        type="text"
                        name="customer_name"
                        class="w-full rounded-xl border border-maroon-200 px-3 py-2 text-sm"
                    >
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold text-maroon-500">
                        Metode bayar
                    </label>

                    <input
                        type="text"
                        name="payment_method"
                        value="Tunai"
                        class="w-full rounded-xl border border-maroon-200 px-3 py-2 text-sm"
                    >
                </div>
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold text-maroon-500">
                    Produk terjual
                </label>

                <div id="offline-items" class="space-y-2">
                    <div class="offline-item grid grid-cols-1 gap-2 sm:grid-cols-[2fr_1fr_90px_140px_auto]">

                        <input
                            type="text"
                            name="items[0][name]"
                            placeholder="Nama produk"
                            required
                            class="rounded-xl border border-maroon-200 px-3 py-2 text-sm"
                        >

                        <input
                            type="text"
                            name="items[0][variant]"
                            placeholder="Varian (opsional)"
                            class="rounded-xl border border-maroon-200 px-3 py-2 text-sm"
                        >

                        <input
                            type="number"
                            name="items[0][qty]"
                            placeholder="Qty"
                            min="1"
                            value="1"
                            required
                            class="rounded-xl border border-maroon-200 px-3 py-2 text-sm"
                        >

                        <input
                            type="number"
                            name="items[0][price]"
                            placeholder="Harga satuan"
                            min="0"
                            required
                            class="rounded-xl border border-maroon-200 px-3 py-2 text-sm"
                        >

                        <button
                            type="button"
                            onclick="this.closest('.offline-item').remove()"
                            class="rounded-xl border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50"
                        >
                            Hapus
                        </button>
                    </div>
                </div>

                <button
                    type="button"
                    onclick="addOfflineItemRow()"
                    class="mt-2 text-xs font-semibold text-[#631f2b] hover:underline"
                >
                    + Tambah baris produk
                </button>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-maroon-500">
                        Diskon (Rp)
                    </label>

                    <input
                        type="number"
                        name="discount_total"
                        min="0"
                        value="0"
                        class="w-full rounded-xl border border-maroon-200 px-3 py-2 text-sm"
                    >
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold text-maroon-500">
                        Ongkir ditagihkan (Rp)
                    </label>

                    <input
                        type="number"
                        name="shipping_total"
                        min="0"
                        value="0"
                        class="w-full rounded-xl border border-maroon-200 px-3 py-2 text-sm"
                    >
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold text-maroon-500">
                        Ongkir riil ke kurir (Rp)
                    </label>

                    <input
                        type="number"
                        name="shipping_cost_actual"
                        min="0"
                        value="0"
                        class="w-full rounded-xl border border-maroon-200 px-3 py-2 text-sm"
                    >
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold text-maroon-500">
                        Biaya admin (Rp)
                    </label>

                    <input
                        type="number"
                        name="admin_fee"
                        min="0"
                        value="0"
                        class="w-full rounded-xl border border-maroon-200 px-3 py-2 text-sm"
                    >
                </div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-maroon-500">
                    Catatan (opsional)
                </label>

                <textarea
                    name="note"
                    rows="2"
                    class="w-full rounded-xl border border-maroon-200 px-3 py-2 text-sm"
                ></textarea>
            </div>

            <button
                type="submit"
                class="rounded-xl bg-[#631f2b] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#7c2d3a]"
            >
                Simpan ke Rekap
            </button>
        </form>
    </div>

    {{-- RIWAYAT CLOSING --}}
    @if($closings->count() > 0)
        <div class="overflow-hidden rounded-3xl border border-maroon-100 bg-white">

            <div class="border-b border-maroon-100 px-6 py-4">
                <p class="font-serif text-sm italic text-[#8a4b5c]">
                    Riwayat Tutup Buku
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-maroon-50 text-xs uppercase tracking-wide text-maroon-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Ditutup pada</th>
                            <th class="px-4 py-3 text-left">Periode</th>
                            <th class="px-4 py-3 text-right">Transaksi</th>
                            <th class="px-4 py-3 text-right">Pendapatan Kotor</th>
                            <th class="px-4 py-3 text-right">Pendapatan Bersih</th>
                            <th class="px-4 py-3 text-left">Oleh</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-maroon-50">
                        @foreach ($closings as $c)
                            <tr>
                                <td class="px-4 py-3 text-maroon-600">
                                    {{ $c->closed_at->format('d M Y, H:i') }}
                                </td>

                                <td class="px-4 py-3 text-maroon-600">
                                    {{ optional($c->period_from)->format('d M Y') }}
                                    –
                                    {{ optional($c->period_to)->format('d M Y') }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ $c->total_entries }}
                                </td>

                                <td class="px-4 py-3 text-right font-semibold">
                                    {{ $rp($c->total_gross) }}
                                </td>

                                <td class="px-4 py-3 text-right font-semibold text-emerald-700">
                                    {{ $rp($c->total_net) }}
                                </td>

                                <td class="px-4 py-3 text-maroon-600">
                                    {{ $c->closed_by }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>

<script>
    let offlineItemIndex = 1;

    function addOfflineItemRow() {
        const wrapper = document.getElementById('offline-items');

        const row = document.createElement('div');

        row.className =
            'offline-item grid grid-cols-1 gap-2 sm:grid-cols-[2fr_1fr_90px_140px_auto]';

        row.innerHTML = `
            <input
                type="text"
                name="items[${offlineItemIndex}][name]"
                placeholder="Nama produk"
                required
                class="rounded-xl border border-maroon-200 px-3 py-2 text-sm"
            >

            <input
                type="text"
                name="items[${offlineItemIndex}][variant]"
                placeholder="Varian (opsional)"
                class="rounded-xl border border-maroon-200 px-3 py-2 text-sm"
            >

            <input
                type="number"
                name="items[${offlineItemIndex}][qty]"
                placeholder="Qty"
                min="1"
                value="1"
                required
                class="rounded-xl border border-maroon-200 px-3 py-2 text-sm"
            >

            <input
                type="number"
                name="items[${offlineItemIndex}][price]"
                placeholder="Harga satuan"
                min="0"
                required
                class="rounded-xl border border-maroon-200 px-3 py-2 text-sm"
            >

            <button
                type="button"
                onclick="this.closest('.offline-item').remove()"
                class="rounded-xl border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50"
            >
                Hapus
            </button>
        `;

        wrapper.appendChild(row);
        offlineItemIndex++;
    }

    function confirmClosing() {
        return confirm(
            'Tutup buku sekarang? Rekap "periode berjalan" akan direset ke 0, ' +
            'tapi seluruh data transaksi TETAP TERSIMPAN dan bisa dilihat di ' +
            'Riwayat Tutup Buku / dengan mencentang "Sertakan yang sudah ditutup buku". ' +
            'Lanjutkan?'
        );
    }
</script>

@endsection
