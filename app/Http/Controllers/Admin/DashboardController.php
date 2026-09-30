<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Order,Payment,Product,User};

class DashboardController extends Controller {

    public function index() {
        return view('admin.dashboard', [
            'revenue' => Order::where('payment_status', 'paid')->sum('grand_total'),
            'orders' => Order::count(),
            'products' => Product::count(),
            'customers' => User::where('role', 'customer')->count(),
            'pendingPayments' => Payment::whereIn('status', ['pending', 'under_review'])->count(),
            'periods' => [
                'harian' => $this->dailyTrend(),
                'mingguan' => $this->weeklyTrend(),
                'bulanan' => $this->monthlyTrend(),
                'tahunan' => $this->yearlyTrend(),
            ],
        ]);
    }

    // Semua method trend di bawah dibungkus try/catch supaya kalau ada masalah data/kolom,
    // dashboard tetap tampil normal (grafik kosong) dan tidak crash.

    // 14 hari terakhir
    protected function dailyTrend(): array {
        try {
            $trend = [];
            for ($i = 13; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $sum = (float) Order::where('payment_status', 'paid')
                    ->whereDate('created_at', $date->toDateString())
                    ->sum('grand_total');
                $trend[] = ['label' => $date->translatedFormat('d M'), 'value' => $sum];
            }
            return $trend;
        } catch (\Throwable $e) {
            return [];
        }
    }

    // 8 minggu terakhir
    protected function weeklyTrend(): array {
        try {
            $trend = [];
            for ($i = 7; $i >= 0; $i--) {
                $start = now()->subWeeks($i)->startOfWeek();
                $end = $start->copy()->endOfWeek();
                $sum = (float) Order::where('payment_status', 'paid')
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('grand_total');
                $trend[] = ['label' => $start->translatedFormat('d M'), 'value' => $sum];
            }
            return $trend;
        } catch (\Throwable $e) {
            return [];
        }
    }

    // 6 bulan terakhir
    protected function monthlyTrend(): array {
        try {
            $trend = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $sum = (float) Order::where('payment_status', 'paid')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('grand_total');
                $trend[] = ['label' => $date->translatedFormat('M Y'), 'value' => $sum];
            }
            return $trend;
        } catch (\Throwable $e) {
            return [];
        }
    }

    // 5 tahun terakhir
    protected function yearlyTrend(): array {
        try {
            $trend = [];
            for ($i = 4; $i >= 0; $i--) {
                $year = now()->subYears($i)->year;
                $sum = (float) Order::where('payment_status', 'paid')
                    ->whereYear('created_at', $year)
                    ->sum('grand_total');
                $trend[] = ['label' => (string) $year, 'value' => $sum];
            }
            return $trend;
        } catch (\Throwable $e) {
            return [];
        }
    }
}