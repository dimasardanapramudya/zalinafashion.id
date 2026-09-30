<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            /*
            |----------------------------------------------------------------------
            | SHIPPING COST FIELD
            |----------------------------------------------------------------------
            |
            | Field ini menyimpan biaya pengiriman yang sudah diverifikasi
            | dari RajaOngkir di backend.
            |
            | PENTING: Field ini harus ada untuk memastikan ongkir yang
            | ditampilkan di halaman pembayaran sama dengan ongkir
            | yang dihitung saat checkout.
            |
            | Type: integer (dalam Rupiah)
            | Default: 0
            |
            |----------------------------------------------------------------------
            */
            if (!Schema::hasColumn('orders', 'shipping_cost')) {
                $table->integer('shipping_cost')
                    ->default(0)
                    ->comment('Biaya pengiriman yang sudah diverifikasi dari RajaOngkir')
                    ->after('shipping_total');
            }

            /*
            |----------------------------------------------------------------------
            | OPTIONAL: RENAME shipping_total TO shipping_cost
            |----------------------------------------------------------------------
            |
            | Jika Anda ingin rename column shipping_total menjadi shipping_cost,
            | uncomment blok di bawah ini dan comment blok di atas.
            |
            | Keuntungan: field name lebih konsisten dengan intent-nya
            |
            | CATATAN: Pastikan semua referensi di model & controller
            | sudah di-update sebelum uncomment ini.
            |
            |----------------------------------------------------------------------
            */
            // if (Schema::hasColumn('orders', 'shipping_total') 
            //     && !Schema::hasColumn('orders', 'shipping_cost')) {
            //     $table->renameColumn('shipping_total', 'shipping_cost');
            // }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'shipping_cost')) {
                $table->dropColumn('shipping_cost');
            }

            // Jika Anda menggunakan rename (uncommented option di atas):
            // Uncomment blok di bawah untuk reverse migration
            // if (Schema::hasColumn('orders', 'shipping_cost') 
            //     && !Schema::hasColumn('orders', 'shipping_total')) {
            //     $table->renameColumn('shipping_cost', 'shipping_total');
            // }
        });
    }
};
