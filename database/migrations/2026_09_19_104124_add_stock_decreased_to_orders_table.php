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
            $table->boolean('stock_decreased')
                ->default(false)
                ->after('status')
                ->comment('Flag untuk mencegah stok berkurang dua kali');

            $table->timestamp('stock_decreased_at')
                ->nullable()
                ->after('stock_decreased')
                ->comment('Waktu saat stok dikurangi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'stock_decreased',
                'stock_decreased_at',
            ]);
        });
    }
};