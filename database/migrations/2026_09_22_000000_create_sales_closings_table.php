<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| TABEL sales_closings — "Tutup Buku"
|--------------------------------------------------------------------------
|
| Saat admin klik "Tutup Buku / Reset Pendapatan", sistem TIDAK menghapus
| satupun baris di sales_ledger. Yang terjadi:
|
| 1. Sebuah baris baru dibuat di sini berisi SNAPSHOT total periode
|    berjalan (dari closing terakhir sampai sekarang).
| 2. Semua baris sales_ledger yang belum punya sales_closing_id diisi
|    dengan id closing ini (lihat SalesRecapController::close()).
| 3. Rekap "periode berjalan" berikutnya otomatis mulai dari 0 lagi,
|    karena query rekap default hanya menghitung baris dengan
|    sales_closing_id = null. Tapi baris lama tetap bisa dilihat lewat
|    Riwayat Closing kapan saja — data tidak pernah hilang.
|
| Migrasi ini dibuat dengan timestamp LEBIH AWAL dari sales_ledger
| karena sales_ledger.sales_closing_id adalah foreign key ke tabel ini.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_closings', function (Blueprint $table) {
            $table->id();

            $table->dateTime('closed_at');
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();

            $table->unsignedInteger('total_entries')->default(0);
            $table->unsignedInteger('total_quantity')->default(0);

            $table->bigInteger('total_gross')->default(0);
            $table->bigInteger('total_discount')->default(0);
            $table->bigInteger('total_shipping')->default(0);
            $table->bigInteger('total_shipping_actual')->default(0);
            $table->bigInteger('total_admin_fee')->default(0);
            $table->bigInteger('total_net')->default(0);

            $table->string('closed_by')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_closings');
    }
};
