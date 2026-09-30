<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menandai kapan admin menekan "Cetak & Kirim Resi".
     * Selama kolom ini kosong, resi belum tampil di halaman pesanan pelanggan.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'tracking_sent_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('tracking_sent_at')->nullable()->after('tracking_number');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'tracking_sent_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('tracking_sent_at');
            });
        }
    }
};