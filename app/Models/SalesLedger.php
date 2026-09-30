<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class SalesLedger extends Model
{
    use HasFactory;

    protected $table = 'sales_ledger';

    protected $fillable = [
        'entry_date',
        'channel',
        'order_id',
        'order_number',
        'payment_method',
        'items_snapshot',
        'items_count',
        'quantity_total',
        'subtotal',
        'discount_total',
        'shipping_total',
        'shipping_cost_actual',
        'admin_fee',
        'grand_total',
        'net_revenue',
        'customer_name',
        'note',
        'recorded_by',
        'sales_closing_id',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'items_snapshot' => 'array',
        'items_count' => 'integer',
        'quantity_total' => 'integer',
        'subtotal' => 'integer',
        'discount_total' => 'integer',
        'shipping_total' => 'integer',
        'shipping_cost_actual' => 'integer',
        'admin_fee' => 'integer',
        'grand_total' => 'integer',
        'net_revenue' => 'integer',
    ];

    public function closing()
    {
        return $this->belongsTo(SalesClosing::class, 'sales_closing_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /*
    |--------------------------------------------------------------------
    | SCOPES PERIODE
    |--------------------------------------------------------------------
    | Dipakai oleh SalesRecapController untuk filter Harian / Mingguan /
    | Bulanan / Tahunan. Semua berpatokan pada `entry_date` (tanggal
    | transaksi sebenarnya), BUKAN created_at, supaya penjualan offline
    | yang diinput belakangan untuk tanggal kemarin tetap masuk periode
    | yang benar.
    */

    public function scopeHarian(Builder $q, ?Carbon $date = null): Builder
    {
        $date ??= now();

        return $q->whereDate('entry_date', $date->toDateString());
    }

    public function scopeMingguan(Builder $q, ?Carbon $date = null): Builder
    {
        $date ??= now();

        return $q->whereBetween('entry_date', [
            $date->copy()->startOfWeek()->toDateString(),
            $date->copy()->endOfWeek()->toDateString(),
        ]);
    }

    public function scopeBulanan(Builder $q, ?Carbon $date = null): Builder
    {
        $date ??= now();

        return $q->whereBetween('entry_date', [
            $date->copy()->startOfMonth()->toDateString(),
            $date->copy()->endOfMonth()->toDateString(),
        ]);
    }

    public function scopeTahunan(Builder $q, ?Carbon $date = null): Builder
    {
        $date ??= now();

        return $q->whereBetween('entry_date', [
            $date->copy()->startOfYear()->toDateString(),
            $date->copy()->endOfYear()->toDateString(),
        ]);
    }

    public function scopeRentang(Builder $q, string $from, string $to): Builder
    {
        return $q->whereBetween('entry_date', [$from, $to]);
    }

    /*
    | Periode "berjalan" = belum ditutup-buku-kan. Ini yang dipakai
    | sebagai default rekap & dashboard, supaya angka reset ke 0 setiap
    | kali admin closing, TANPA menghapus baris manapun.
    */
    public function scopeBerjalan(Builder $q): Builder
    {
        return $q->whereNull('sales_closing_id');
    }

    public function scopeOnline(Builder $q): Builder
    {
        return $q->where('channel', 'online');
    }

    public function scopeOffline(Builder $q): Builder
    {
        return $q->where('channel', 'offline');
    }
}
