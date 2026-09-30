@extends('layouts.store')

@section('title', 'Receipt ' . $order->order_number)

@section('content')
@php
    $paidAmount = $payment->amount_paid ?? $payment->amount_expected ?? 0;
    $verifiedAt = $payment->verified_at ?? $payment->updated_at;
@endphp

<div class="relative min-h-screen overflow-hidden bg-[#fbf8f6] py-10 sm:py-14">

    {{-- Decorative background --}}
    <div class="pointer-events-none absolute -right-32 -top-32 h-96 w-96 rounded-full bg-[#ead6d1]/40 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-40 -left-32 h-96 w-96 rounded-full bg-[#e8d7c2]/30 blur-3xl"></div>

    <div class="relative mx-auto max-w-5xl px-5 sm:px-8">

        {{-- Top navigation --}}
        <div class="no-print mb-7 flex items-center justify-between gap-4">
            <a href="{{ route('profile') }}"
               class="inline-flex items-center gap-2 text-sm font-medium text-[#805965] transition hover:text-[#5d293b]">
                <span>←</span>
                Kembali ke pesanan
            </a>

            <span class="hidden rounded-full border border-[#dfc9c4] bg-white px-4 py-2 text-[10px] font-bold uppercase tracking-[.22em] text-[#9b756d] sm:inline-flex">
                Official Transaction Document
            </span>
        </div>

        {{-- Receipt card --}}
        <div class="overflow-hidden rounded-[2rem] border border-[#eadbd6] bg-white shadow-[0_25px_90px_rgba(91,48,57,.12)]">

            {{-- Premium header --}}
            <div class="relative overflow-hidden bg-gradient-to-br from-[#4d2534] via-[#642f42] to-[#8a5360] px-6 py-10 text-white sm:px-12 sm:py-14">

                <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full border border-white/10"></div>
                <div class="pointer-events-none absolute -right-8 -top-12 h-48 w-48 rounded-full border border-white/10"></div>
                <div class="pointer-events-none absolute -bottom-24 -left-20 h-64 w-64 rounded-full bg-white/5"></div>

                <div class="relative flex flex-col gap-8 sm:flex-row sm:items-center sm:justify-between">

                    <div>
                        <div class="mb-4 flex items-center gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-full border border-[#d9b27b]/50 bg-[#d9b27b]/10 font-serif text-xl text-[#e5c18b]">
                                Z
                            </span>

                            <div>
                                <p class="font-serif text-xl tracking-[.16em]">
                                    ZALINA
                                </p>

                                <p class="text-[9px] uppercase tracking-[.35em] text-[#e7c9bd]">
                                    Fashion
                                </p>
                            </div>
                        </div>

                        <p class="text-[10px] uppercase tracking-[.3em] text-[#e4bfae]">
                            Elegance in Every Drape
                        </p>

                        <h1 class="mt-4 max-w-xl font-serif text-3xl leading-tight sm:text-5xl">
                            Pembayaran Berhasil
                        </h1>

                        <p class="mt-3 max-w-md text-sm leading-relaxed text-[#ead5d4]">
                            Terima kasih telah mempercayakan pilihan fashion muslimahmu kepada Zalina Fashion.
                        </p>
                    </div>

                    <div class="sm:text-right">
                        <div class="inline-flex h-16 w-16 items-center justify-center rounded-full border border-white/20 bg-white/10 text-3xl text-[#e7c18c] backdrop-blur">
                            ✓
                        </div>

                        <p class="mt-4 text-[10px] uppercase tracking-[.25em] text-[#e4bfae]">
                            Payment Receipt
                        </p>

                        <p class="mt-2 font-serif text-lg text-white">
                            {{ $order->order_number }}
                        </p>
                    </div>
                </div>

                <div class="relative mt-10 flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full border border-[#b9dfc5]/30 bg-[#b9dfc5]/10 px-4 py-2 text-xs font-semibold text-[#d4f0db]">
                        <span class="h-2 w-2 rounded-full bg-[#8ee0a7]"></span>
                        PAID • VERIFIED
                    </span>

                    <span class="rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs text-[#ead5d4]">
                        Zalina Fashion Official
                    </span>
                </div>
            </div>

            {{-- Main content --}}
            <div class="p-6 sm:p-10 lg:p-12">

                {{-- Information grid --}}
                <div class="grid gap-5 md:grid-cols-2">

                    <div class="rounded-2xl border border-[#eadbd6] bg-[#fffaf8] p-5">
                        <p class="mb-4 text-[10px] font-bold uppercase tracking-[.25em] text-[#b18b83]">
                            Customer Information
                        </p>

                        <h2 class="font-serif text-2xl text-[#542b35]">
                            {{ $order->customer_name }}
                        </h2>

                        <p class="mt-2 break-all text-sm text-[#8f7078]">
                            {{ $order->customer_email }}
                        </p>

                        @if($order->customer_phone)
                            <p class="mt-1 text-sm text-[#8f7078]">
                                {{ $order->customer_phone }}
                            </p>
                        @endif
                    </div>

                    <div class="rounded-2xl border border-[#eadbd6] bg-[#fffaf8] p-5">
                        <p class="mb-4 text-[10px] font-bold uppercase tracking-[.25em] text-[#b18b83]">
                            Transaction Information
                        </p>

                        <div class="space-y-3 text-sm">
                            <div class="flex items-start justify-between gap-4">
                                <span class="text-[#9b7b82]">Nomor Pesanan</span>
                                <span class="text-right font-semibold text-[#633743]">
                                    {{ $order->order_number }}
                                </span>
                            </div>

                            <div class="flex items-start justify-between gap-4">
                                <span class="text-[#9b7b82]">Dikonfirmasi</span>
                                <span class="text-right font-semibold text-[#633743]">
                                    {{ $verifiedAt ? $verifiedAt->translatedFormat('d F Y, H:i') : '-' }}
                                </span>
                            </div>

                            <div class="flex items-start justify-between gap-4">
                                <span class="text-[#9b7b82]">Metode</span>
                                <span class="text-right font-semibold text-[#633743]">
                                    {{ $payment->method?->name ?? '-' }}
                                </span>
                            </div>

                            {{--
                                PERBAIKAN: sebelumnya receipt sama sekali tidak
                                menampilkan kurir maupun nomor resi. Ditambahkan
                                di sini, otomatis tersembunyi kalau order belum
                                punya kurir ATAU resi sama sekali (mis. order
                                yang baru dibayar, belum dikirim).
                            --}}
                            @if(filled($order->shipping_courier))
                                <div class="flex items-start justify-between gap-4">
                                    <span class="text-[#9b7b82]">Kurir</span>
                                    <span class="text-right font-semibold text-[#633743]">
                                        {{ strtoupper($order->shipping_courier) }}
                                        @if(filled($order->shipping_service))
                                            &mdash; {{ $order->shipping_service }}
                                        @endif
                                    </span>
                                </div>
                            @endif

                            @if(filled($order->tracking_number))
                                <div class="flex items-start justify-between gap-4">
                                    <span class="text-[#9b7b82]">Nomor Resi</span>
                                    <span class="text-right font-mono font-semibold text-[#633743]">
                                        {{ $order->tracking_number }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Payment amount --}}
                <div class="mt-5 rounded-2xl border border-[#d9c09a] bg-gradient-to-r from-[#fffaf1] to-[#fbf2e7] p-5 sm:p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[.25em] text-[#ad8a58]">
                                Amount Paid
                            </p>

                            <p class="mt-2 font-serif text-3xl text-[#633743] sm:text-4xl">
                                Rp {{ number_format($paidAmount, 0, ',', '.') }}
                            </p>

                            @if($payment->method?->account_name)
                                <p class="mt-2 text-xs text-[#9b7b82]">
                                    {{ $payment->method->account_name }}
                                </p>
                            @endif
                        </div>

                        <div class="flex h-14 w-14 items-center justify-center rounded-full border border-[#d9c09a] bg-white text-2xl text-[#ad8a58]">
                            ✦
                        </div>
                    </div>
                </div>

                {{-- Order details --}}
                <div class="mt-10">
                    <div class="mb-5 flex items-end justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[.25em] text-[#b18b83]">
                                Your Selection
                            </p>

                            <h2 class="mt-2 font-serif text-3xl text-[#542b35]">
                                Rincian Pesanan
                            </h2>
                        </div>

                        <span class="hidden rounded-full bg-[#f8eeeb] px-4 py-2 text-xs font-semibold text-[#8b5963] sm:inline-flex">
                            {{ $order->items->count() }} Item
                        </span>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-[#eadbd6]">
                        <div class="hidden grid-cols-[1fr_auto_auto] gap-5 bg-[#f8eeeb] px-5 py-3 text-[10px] font-bold uppercase tracking-[.15em] text-[#8b5963] sm:grid">
                            <span>Produk</span>
                            <span class="text-right">Jumlah</span>
                            <span class="text-right">Total</span>
                        </div>

                        <div class="divide-y divide-[#eee2de]">
                            @foreach($order->items as $item)
                                <div class="grid gap-3 px-5 py-5 sm:grid-cols-[1fr_auto_auto] sm:items-center sm:gap-5">

                                    <div>
                                        <p class="font-semibold text-[#633743]">
                                            {{ $item->product_name }}
                                        </p>

                                        @if($item->variant_name ?? false)
                                            <p class="mt-1 text-xs text-[#a18487]">
                                                Varian: {{ $item->variant_name }}
                                            </p>
                                        @endif

                                        @if($item->sku)
                                            <p class="mt-1 text-[10px] text-[#b18b83]">
                                                SKU: {{ $item->sku }}
                                            </p>
                                        @endif

                                        <p class="mt-2 text-xs text-[#9b7b82] sm:hidden">
                                            {{ $item->quantity }} × Rp {{ number_format($item->price, 0, ',', '.') }}
                                        </p>
                                    </div>

                                    <div class="text-left text-sm text-[#80616b] sm:text-right">
                                        <span class="sm:hidden">Qty: </span>
                                        {{ $item->quantity }}
                                    </div>

                                    <div class="text-left font-semibold text-[#633743] sm:text-right">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Summary --}}
                <div class="mt-7 flex justify-end">
                    <div class="w-full max-w-md rounded-2xl border border-[#eadbd6] bg-[#fffaf8] p-5 sm:p-6">
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between gap-5">
                                <span class="text-[#92727c]">Subtotal</span>
                                <span class="font-medium text-[#633743]">
                                    Rp {{ number_format($order->subtotal, 0, ',', '.') }}
                                </span>
                            </div>

                            @if((float) ($order->discount_total ?? 0) > 0)
                                <div class="flex justify-between gap-5">
                                    <span class="text-[#92727c]">Diskon</span>
                                    <span class="font-medium text-[#9b6570]">
                                        - Rp {{ number_format((float) $order->discount_total, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endif

                            <div class="flex justify-between gap-5">
                                <span class="text-[#92727c]">Pengiriman</span>
                                <span class="font-medium text-[#633743]">
                                    Rp {{ number_format((float) ($order->shipping_total ?? 0), 0, ',', '.') }}
                                </span>
                            </div>

                            {{--
                                PERBAIKAN: baris "Biaya Admin" sebelumnya tidak ada
                                sama sekali di receipt ini, padahal kolomnya (admin_fee)
                                sudah ada di tabel orders dan sudah ditampilkan di
                                halaman pembayaran. Ditambahkan di sini juga supaya
                                totalnya bisa dipertanggungjawabkan (Subtotal - Diskon
                                + Pengiriman + Biaya Admin = Grand Total).
                            --}}
                            <div class="flex justify-between gap-5">
                                <span class="text-[#92727c]">Biaya Admin</span>
                                <span class="font-medium text-[#633743]">
                                    Rp {{ number_format((float) ($order->admin_fee ?? 0), 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <div class="my-5 border-t border-[#e5d4cf]"></div>

                        <div class="flex items-end justify-between gap-5">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[.2em] text-[#b18b83]">
                                    Grand Total
                                </p>

                                <p class="mt-2 font-serif text-3xl text-[#542b35]">
                                    Total
                                </p>
                            </div>

                            <p class="text-right font-serif text-2xl font-bold text-[#6b3542]">
                                Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Confirmation notice --}}
                <div class="mt-8 rounded-2xl border border-[#cfe3d3] bg-[#f5faf6] p-5">
                    <div class="flex items-start gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#dcefe1] font-bold text-[#39734c]">
                            ✓
                        </span>

                        <div>
                            <h3 class="text-sm font-bold text-[#39734c]">
                                Pembayaran telah diverifikasi
                            </h3>

                            <p class="mt-1 text-sm leading-relaxed text-[#66866f]">
                                Receipt ini merupakan bukti bahwa pembayaran pesanan telah
                                dikonfirmasi oleh admin Zalina Fashion. Simpan dokumen ini
                                sebagai bukti transaksi Anda.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Signature --}}
                <div class="mt-8 text-right">
                    <p class="text-xs text-[#a18487]">
                        With elegance,
                    </p>

                    <p class="mt-2 font-serif text-2xl italic text-[#6b3542]">
                        Zalina Fashion
                    </p>

                    <p class="mt-1 text-[10px] uppercase tracking-[.2em] text-[#b18b83]">
                        Elegance in Every Drape
                    </p>
                </div>

                {{-- Actions --}}
                <div class="no-print mt-10 flex flex-col gap-3 border-t border-[#eadbd6] pt-7 sm:flex-row sm:flex-wrap">

                    <a href="{{ route('receipt.download', $order) }}"
                       class="inline-flex items-center justify-center gap-3 rounded-full bg-gradient-to-r from-[#5d293b] to-[#81485a] px-7 py-3.5 text-sm font-bold text-white shadow-lg shadow-[#633743]/20 transition hover:-translate-y-1 hover:shadow-xl">
                        <span>↓</span>
                        Download Receipt
                    </a>

                    <button onclick="window.print()"
                            class="inline-flex items-center justify-center gap-3 rounded-full border border-[#d9bdb8] bg-white px-7 py-3.5 text-sm font-semibold text-[#633743] transition hover:bg-[#fff7f4]">
                        <span>⎙</span>
                        Cetak / Simpan PDF
                    </button>

                    <a href="{{ route('profile') }}"
                       class="inline-flex items-center justify-center rounded-full border border-[#eadbd6] px-7 py-3.5 text-sm font-semibold text-[#80616b] transition hover:bg-[#fffaf8]">
                        Kembali ke Pesanan
                    </a>
                </div>

            </div>

            {{-- Footer --}}
            <div class="border-t border-[#eadbd6] bg-[#fffaf8] px-6 py-5 sm:px-12">
                <div class="flex flex-col gap-2 text-center text-[10px] text-[#b18b83] sm:flex-row sm:items-center sm:justify-between sm:text-left">
                    <p>
                        © {{ date('Y') }} <span class="font-semibold text-[#805965]">Zalina Fashion</span>
                    </p>

                    <p>
                        Thank you for choosing elegance.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body {
            background: #ffffff !important;
        }

        header,
        footer,
        nav,
        .no-print {
            display: none !important;
        }

        .min-h-screen {
            min-height: auto !important;
            padding: 0 !important;
        }

        .shadow-xl,
        .shadow-2xl,
        .shadow-\[0_25px_90px_rgba\(91\,48\,57\,\.12\)\] {
            box-shadow: none !important;
        }

        .rounded-\[2rem\] {
            border-radius: 0 !important;
        }

        .bg-gradient-to-br {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>
@endsection