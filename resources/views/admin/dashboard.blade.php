@extends('layouts.admin')

@section('title', 'Dashboard Admin — Zalina Fashion')

@section('content')

@php
    $totalRevenue = $revenue ?? 0;
    $totalOrders = $orders ?? 0;
    $totalProducts = $products ?? 0;
    $totalCustomers = $customers ?? 0;

    $periods = $periods ?? [
        'harian' => [],
        'mingguan' => [],
        'bulanan' => [],
        'tahunan' => [],
    ];
@endphp

<div class="space-y-6 pb-10">

    {{-- =========================================================
         HERO DASHBOARD
    ========================================================== --}}
    <section class="relative overflow-hidden rounded-[2rem] bg-[#3b101d] px-6 py-8 text-[#fff8f2] shadow-[0_20px_60px_rgba(59,16,29,.22)] sm:px-9">

        <div class="absolute -right-20 -top-24 h-80 w-80 rounded-full border-[40px] border-[#d9ad68]/20"></div>
        <div class="absolute -bottom-32 right-24 h-64 w-64 rounded-full bg-[#d9ad68]/10 blur-3xl"></div>
        <div class="absolute bottom-0 left-1/3 h-32 w-32 rounded-full bg-white/5 blur-2xl"></div>

        <div class="relative z-10 flex flex-col justify-between gap-8 lg:flex-row lg:items-end">

            <div>
                <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-[#d9ad68]/30 bg-white/10 px-3 py-1.5 text-[11px] font-bold uppercase tracking-[.18em] text-[#f2d7a5]">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-300"></span>
                    Admin control center
                </div>

                <h1 class="font-serif text-3xl leading-tight sm:text-5xl">
                    Selamat datang,
                    <br>
                    <span class="text-[#e7c487]">Zalina Admin.</span>
                </h1>

                <p class="mt-4 max-w-xl text-sm leading-6 text-[#ead6d9] sm:text-base">
                    Pantau performa toko, penjualan, pesanan, produk, pelanggan, dan aktivitas bisnis dalam satu dashboard.
                </p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/10 px-5 py-4 backdrop-blur-md">
                <p class="text-[10px] uppercase tracking-[.18em] text-[#e7c487]">
                    Store status
                </p>

                <div class="mt-2 flex items-center gap-2 text-sm font-semibold">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-300 shadow-[0_0_12px_rgba(110,231,183,.8)]"></span>
                    Operasional aktif
                </div>

                <p class="mt-1 text-xs text-[#ead6d9]">
                    Sistem Zalina Fashion
                </p>
            </div>

        </div>
    </section>


    {{-- =========================================================
         KPI STATISTICS
    ========================================================== --}}
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="dashboard-kpi">
            <div class="kpi-icon bg-[#f8e9ed] text-[#7c2638]">
                Rp
            </div>

            <div class="min-w-0">
                <p class="kpi-label">Total pendapatan</p>

                <h2 class="kpi-value">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </h2>

                <p class="kpi-caption text-emerald-600">
                    Pendapatan berhasil
                </p>
            </div>
        </div>


        <div class="dashboard-kpi">
            <div class="kpi-icon bg-[#f7edda] text-[#9a6a22]">
                ↗
            </div>

            <div class="min-w-0">
                <p class="kpi-label">Total pesanan</p>

                <h2 class="kpi-value">
                    {{ number_format($totalOrders) }}
                </h2>

                <p class="kpi-caption">
                    Transaksi pelanggan
                </p>
            </div>
        </div>


        <div class="dashboard-kpi">
            <div class="kpi-icon bg-[#eee9f8] text-[#67518f]">
                ◇
            </div>

            <div class="min-w-0">
                <p class="kpi-label">Total produk</p>

                <h2 class="kpi-value">
                    {{ number_format($totalProducts) }}
                </h2>

                <p class="kpi-caption">
                    Produk dalam katalog
                </p>
            </div>
        </div>


        <div class="dashboard-kpi">
            <div class="kpi-icon bg-[#e7f3ee] text-[#267653]">
                ◎
            </div>

            <div class="min-w-0">
                <p class="kpi-label">Total pelanggan</p>

                <h2 class="kpi-value">
                    {{ number_format($totalCustomers) }}
                </h2>

                <p class="kpi-caption">
                    Akun terdaftar
                </p>
            </div>
        </div>

    </section>


    {{-- =========================================================
         CTA REKAP PENJUALAN
         Rekap ini baca dari buku besar penjualan (sales_ledger),
         BUKAN dari riwayat pesanan — jadi laporannya tetap utuh
         walau riwayat pesanan dihapus di panel Orders.
    ========================================================== --}}
    <section class="flex flex-col items-start justify-between gap-4 rounded-[2rem] border border-[#eadde0] bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:px-8">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#fbf3f5] text-[#7c2638]">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 17V9m4 8V5m4 12v-6M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>

            <div>
                <h2 class="font-serif text-xl text-[#451522]">Rekap Penjualan &amp; Laporan Keuangan</h2>
                <p class="mt-1 text-sm text-[#927780]">
                    Rekap harian, mingguan, bulanan, tahunan — online &amp; offline — lengkap dengan export Excel.
                </p>
            </div>
        </div>

        <a
            href="{{ route('admin.sales-recap.index') }}"
            class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-[#631f2b] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#7c2d3a]"
        >
            Cek Rekap
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </section>


    {{-- =========================================================
         ANALYTICS + QUICK ACTION
    ========================================================== --}}
    <section class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">

        {{-- CHART --}}
        <div class="overflow-hidden rounded-[2rem] border border-[#eadde0] bg-white shadow-sm">

            <div class="flex flex-col justify-between gap-5 border-b border-[#f1e7e9] px-6 py-6 sm:flex-row sm:items-center sm:px-8">

                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[.2em] text-[#b58a50]">
                        Analytics overview
                    </p>

                    <h2 class="mt-1 font-serif text-2xl text-[#451522]">
                        Performa penjualan
                    </h2>

                    <p class="mt-1 text-sm text-[#927780]">
                        Perbandingan pendapatan berdasarkan periode.
                    </p>
                </div>


                <div class="flex rounded-xl bg-[#faf3f5] p-1">

                    <button
                        type="button"
                        class="chart-period active-chart-period"
                        data-period="daily"
                    >
                        Hari
                    </button>

                    <button
                        type="button"
                        class="chart-period"
                        data-period="weekly"
                    >
                        Minggu
                    </button>

                    <button
                        type="button"
                        class="chart-period"
                        data-period="monthly"
                    >
                        Bulan
                    </button>

                    <button
                        type="button"
                        class="chart-period"
                        data-period="yearly"
                    >
                        Tahun
                    </button>

                </div>

            </div>


            <div class="px-4 py-6 sm:px-8">

                <div class="mb-5 flex items-end justify-between gap-4">

                    <div>
                        <p class="text-xs text-[#a58a91]">
                            Total periode terpilih
                        </p>

                        <p
                            id="chartTotal"
                            class="mt-1 text-xl font-bold text-[#451522]"
                        >
                            Rp 0
                        </p>
                    </div>

                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                        Data aktual
                    </span>

                </div>


                <div class="relative h-[320px] sm:h-[360px]">
                    <canvas id="salesChart"></canvas>
                </div>

            </div>

        </div>


        {{-- QUICK ACTION --}}
        <div class="rounded-[2rem] border border-[#eadde0] bg-white p-6 shadow-sm">

            <p class="text-[11px] font-bold uppercase tracking-[.2em] text-[#b58a50]">
                Workspace
            </p>

            <h2 class="mt-1 font-serif text-2xl text-[#451522]">
                Aksi cepat
            </h2>

            <p class="mt-2 text-sm leading-6 text-[#927780]">
                Kelola bagian penting toko dengan lebih cepat.
            </p>


            <div class="mt-6 space-y-3">

                <a
                    href="{{ route('admin.products.index') }}"
                    class="quick-action"
                >
                    <span class="quick-action-symbol bg-[#f8e9ed] text-[#7c2638]">
                        ◇
                    </span>

                    <span>
                        <b>Kelola produk</b>
                        <small>Tambah dan ubah katalog</small>
                    </span>

                    <span>→</span>
                </a>


                <a
                    href="{{ route('admin.orders.index') }}"
                    class="quick-action"
                >
                    <span class="quick-action-symbol bg-[#f7edda] text-[#9a6a22]">
                        ↗
                    </span>

                    <span>
                        <b>Kelola pesanan</b>
                        <small>Periksa transaksi pelanggan</small>
                    </span>

                    <span>→</span>
                </a>


                <a
                    href="{{ route('admin.discounts.index') }}"
                    class="quick-action"
                >
                    <span class="quick-action-symbol bg-[#eee9f8] text-[#67518f]">
                        %
                    </span>

                    <span>
                        <b>Atur promo</b>
                        <small>Buat diskon dan campaign</small>
                    </span>

                    <span>→</span>
                </a>


                @if (Route::has('admin.returns.index'))
                    <a
                        href="{{ route('admin.returns.index') }}"
                        class="quick-action"
                    >
                        <span class="quick-action-symbol bg-[#e7f3ee] text-[#267653]">
                            ↩
                        </span>

                        <span>
                            <b>Kelola retur</b>
                            <small>Review pengajuan retur</small>
                        </span>

                        <span>→</span>
                    </a>
                @endif

            </div>

        </div>

    </section>


    {{-- =========================================================
         STORE INSIGHT
    ========================================================== --}}
    <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <div class="insight-card lg:col-span-2">

            <div>
                <p class="text-[11px] font-bold uppercase tracking-[.2em] text-[#b58a50]">
                    Store health
                </p>

                <h2 class="mt-1 font-serif text-2xl text-[#451522]">
                    Ringkasan toko
                </h2>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-[#927780]">
                    Seluruh ringkasan menggunakan data yang sudah dikirim oleh controller dashboard admin.
                </p>
            </div>


            <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-3">

                <div class="mini-stat">
                    <span>Pendapatan</span>
                    <b>
                        Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                    </b>
                </div>

                <div class="mini-stat">
                    <span>Pesanan</span>
                    <b>
                        {{ number_format($totalOrders) }}
                    </b>
                </div>

                <div class="mini-stat">
                    <span>Pelanggan</span>
                    <b>
                        {{ number_format($totalCustomers) }}
                    </b>
                </div>

            </div>

        </div>


        <div class="rounded-[2rem] bg-[#f8f0e6] p-6">

            <p class="text-[11px] font-bold uppercase tracking-[.2em] text-[#b58a50]">
                Brand note
            </p>

            <h2 class="mt-2 font-serif text-2xl leading-tight text-[#5b3021]">
                Elegance in every drape.
            </h2>

            <p class="mt-3 text-sm leading-6 text-[#886b5f]">
                Rawat pengalaman pelanggan melalui produk yang indah, pelayanan yang cepat, dan pengelolaan toko yang konsisten.
            </p>

        </div>

    </section>

</div>


{{-- =========================================================
     STYLE DASHBOARD
========================================================== --}}
<style>
    .dashboard-kpi {
        display: flex;
        align-items: center;
        gap: 16px;
        border: 1px solid #eadde0;
        background: #ffffff;
        border-radius: 1.5rem;
        padding: 22px;
        box-shadow: 0 8px 25px rgba(69, 21, 34, .035);
        transition: all .25s ease;
    }

    .dashboard-kpi:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 38px rgba(69, 21, 34, .10);
    }

    .kpi-icon {
        display: flex;
        height: 52px;
        width: 52px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: 17px;
        font-size: 20px;
        font-weight: 800;
    }

    .kpi-label {
        font-size: 12px;
        color: #a1848c;
    }

    .kpi-value {
        margin-top: 5px;
        color: #451522;
        font-size: 24px;
        font-weight: 800;
        letter-spacing: -.04em;
        line-height: 1.25;
    }

    .kpi-caption {
        margin-top: 5px;
        font-size: 11px;
    }

    .chart-period {
        border-radius: 9px;
        padding: 9px 12px;
        color: #a1848c;
        font-size: 11px;
        font-weight: 700;
        transition: all .2s ease;
    }

    .chart-period:hover {
        background: #f5e5e9;
        color: #7c2638;
    }

    .active-chart-period {
        background: #7c2638 !important;
        color: #ffffff !important;
        box-shadow: 0 5px 15px rgba(124, 38, 56, .2);
    }

    .quick-action {
        display: flex;
        align-items: center;
        gap: 12px;
        border: 1px solid #f0e5e8;
        border-radius: 17px;
        padding: 12px;
        color: #451522;
        transition: all .2s ease;
    }

    .quick-action:hover {
        transform: translateX(4px);
        border-color: #d8b4bd;
        background: #fff8fa;
    }

    .quick-action-symbol {
        display: flex;
        height: 42px;
        width: 42px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: 13px;
        font-weight: 800;
    }

    .quick-action > span:nth-child(2) {
        display: flex;
        min-width: 0;
        flex: 1;
        flex-direction: column;
    }

    .quick-action b {
        font-size: 12px;
    }

    .quick-action small {
        margin-top: 3px;
        font-size: 10px;
        color: #a1848c;
    }

    .insight-card {
        border: 1px solid #eadde0;
        border-radius: 2rem;
        background: #ffffff;
        padding: 24px;
        box-shadow: 0 8px 25px rgba(69, 21, 34, .035);
    }

    .mini-stat {
        border-radius: 15px;
        background: #faf3f5;
        padding: 15px;
    }

    .mini-stat span {
        display: block;
        font-size: 11px;
        color: #a1848c;
    }

    .mini-stat b {
        display: block;
        margin-top: 5px;
        color: #451522;
        font-size: 17px;
    }
</style>


{{-- =========================================================
     CHART.JS
========================================================== --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const canvas = document.getElementById('salesChart');

    if (!canvas) {
        return;
    }

    const periods = @json($periods);

    const formatter = new Intl.NumberFormat('id-ID');

    const chartData = {
        daily: periods.harian || [],
        weekly: periods.mingguan || [],
        monthly: periods.bulanan || [],
        yearly: periods.tahunan || []
    };

    function normalizePeriod(period) {
        const source = chartData[period] || [];

        return {
            labels: source.map(function (item) {
                return item.label ?? '';
            }),

            values: source.map(function (item) {
                return Number(item.value || 0);
            })
        };
    }

    function calculateTotal(values) {
        return values.reduce(function (total, value) {
            return total + value;
        }, 0);
    }

    function formatRupiah(value) {
        return 'Rp ' + formatter.format(value);
    }

    const initialData = normalizePeriod('daily');

    const context = canvas.getContext('2d');

    const gradient = context.createLinearGradient(0, 0, 0, 360);

    gradient.addColorStop(0, 'rgba(124, 45, 58, .28)');
    gradient.addColorStop(0.55, 'rgba(124, 45, 58, .10)');
    gradient.addColorStop(1, 'rgba(124, 45, 58, 0)');


    const salesChart = new Chart(canvas, {
        type: 'line',

        data: {
            labels: initialData.labels,

            datasets: [
                {
                    label: 'Penjualan',
                    data: initialData.values,

                    borderColor: '#7c2d3a',
                    backgroundColor: gradient,

                    fill: true,
                    borderWidth: 3,
                    tension: .42,

                    pointRadius: 3,
                    pointHoverRadius: 8,

                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#7c2d3a',
                    pointBorderWidth: 2,

                    cubicInterpolationMode: 'monotone'
                }
            ]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            animation: {
                duration: 900,
                easing: 'easeInOutQuart'
            },

            interaction: {
                intersect: false,
                mode: 'index'
            },

            plugins: {
                legend: {
                    display: false
                },

                tooltip: {
                    backgroundColor: '#451522',
                    titleColor: '#f8e9ed',
                    bodyColor: '#ffffff',

                    padding: 14,
                    cornerRadius: 14,
                    displayColors: false,

                    callbacks: {
                        label: function (context) {
                            return formatRupiah(context.parsed.y);
                        }
                    }
                }
            },

            scales: {
                x: {
                    grid: {
                        display: false
                    },

                    border: {
                        display: false
                    },

                    ticks: {
                        color: '#a1848c',
                        font: {
                            size: 11
                        }
                    }
                },

                y: {
                    beginAtZero: true,

                    grid: {
                        color: 'rgba(124, 45, 58, .07)'
                    },

                    border: {
                        display: false
                    },

                    ticks: {
                        color: '#a1848c',

                        font: {
                            size: 11
                        },

                        callback: function (value) {
                            if (value >= 1000000) {
                                return (value / 1000000).toFixed(1) + 'jt';
                            }

                            if (value >= 1000) {
                                return (value / 1000).toFixed(0) + 'rb';
                            }

                            return value;
                        }
                    }
                }
            }
        }
    });


    document.getElementById('chartTotal').textContent =
        formatRupiah(calculateTotal(initialData.values));


    document.querySelectorAll('.chart-period').forEach(function (button) {

        button.addEventListener('click', function () {

            document.querySelectorAll('.chart-period').forEach(function (item) {
                item.classList.remove('active-chart-period');
            });

            this.classList.add('active-chart-period');

            const selectedPeriod = this.dataset.period;

            const nextData = normalizePeriod(selectedPeriod);

            salesChart.data.labels = nextData.labels;

            salesChart.data.datasets[0].data = nextData.values;

            salesChart.update();

            document.getElementById('chartTotal').textContent =
                formatRupiah(calculateTotal(nextData.values));

        });

    });

});
</script>

@endsection