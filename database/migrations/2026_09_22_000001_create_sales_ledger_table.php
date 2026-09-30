<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| TABEL sales_ledger — "Buku Besar Penjualan"
|--------------------------------------------------------------------------
|
| Ini SENGAJA dibuat terpisah total dari tabel `orders`. Setiap baris di
| sini adalah SNAPSHOT permanen dari satu transaksi (nominal, nama
| produk, tanggal) — bukan referensi hidup ke order. Kenapa:
|
| 1. Kalau admin hapus riwayat pesanan di panel Orders, baris rekap di
|    sini TIDAK ikut terhapus (order_id akan diset null lewat
|    onDelete('set null'), tapi seluruh angka & nama produk sudah
|    tersalin permanen ke kolom-kolom di tabel ini).
| 2. Dashboard & halaman rekap query dari tabel ini, BUKAN dari
|    `orders` — jadi laporan keuangan tidak pernah "berpatokan" ke
|    riwayat pesanan yang bisa dihapus.
| 3. Mendukung penjualan offline: `channel` membedakan 'online' (dari
|    pembayaran yang diverifikasi admin) vs 'offline' (dicatat manual
|    oleh admin di kasir toko fisik).
|
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_ledger', function (Blueprint $table) {
            $table->id();

            // Tanggal transaksi sebenarnya (bisa berbeda dari created_at
            // kalau admin input penjualan offline yang terjadi kemarin).
            $table->date('entry_date')->index();

            $table->enum('channel', ['online', 'offline'])
                ->default('online')
                ->index();

            /*
            | Referensi ke order ASLI (kalau ada). Sengaja nullable +
            | onDelete('set null'): kalau order-nya dihapus dari riwayat,
            | baris ledger ini tetap hidup, cuma link-nya jadi null.
            | order_number disimpan terpisah sebagai teks supaya nomor
            | pesanan tetap terbaca di laporan walau order sudah tidak ada.
            */
            $table->foreignId('order_id')->nullable()
                ->constrained('orders')
                ->nullOnDelete();

            $table->string('order_number')->nullable();

            $table->string('payment_method')->nullable();

            /*
            | Snapshot produk yang terjual: JSON array berisi
            | [{ name, variant, qty, price, subtotal }, ...].
            | Disimpan sebagai teks/JSON supaya tetap utuh walau produk
            | aslinya kemudian diubah/dihapus dari katalog.
            */
            $table->json('items_snapshot')->nullable();

            $table->unsignedInteger('items_count')->default(0);
            $table->unsignedInteger('quantity_total')->default(0);

            /*
            |----------------------------------------------------------------
            | RINCIAN KEUANGAN (semua dalam Rupiah, integer — tanpa desimal)
            |----------------------------------------------------------------
            */
            $table->bigInteger('subtotal')->default(0);
            $table->bigInteger('discount_total')->default(0);
            $table->bigInteger('shipping_total')->default(0);
            $table->bigInteger('shipping_cost_actual')->default(0);
            $table->bigInteger('admin_fee')->default(0);
            $table->bigInteger('grand_total')->default(0);

            /*
            | Pendapatan bersih versi toko:
            | grand_total - shipping_cost_actual - discount_total
            | (dihitung & disimpan langsung supaya laporan tidak perlu
            | hitung ulang tiap kali dan konsisten historisnya).
            */
            $table->bigInteger('net_revenue')->default(0);

            $table->string('customer_name')->nullable();
            $table->text('note')->nullable();

            $table->string('recorded_by')->nullable();

            /*
            | Kalau null = baris ini masih "berjalan" (masuk hitungan
            | rekap periode saat ini). Kalau terisi = sudah ditutup-buku-kan
            | lewat fitur Closing, tetap tersimpan untuk histori/audit
            | tapi tidak lagi dihitung sebagai "pendapatan berjalan".
            */
            $table->foreignId('sales_closing_id')->nullable()
                ->constrained('sales_closings')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['channel', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_ledger');
    }
};
