<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan nomor resi / AWB ke orders.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            if (!Schema::hasColumn('orders', 'tracking_number')) {

                $table
                    ->string('tracking_number', 100)
                    ->nullable()
                    ->after('shipping_weight');

            }

        });
    }


    /**
     * Rollback migration.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            if (Schema::hasColumn('orders', 'tracking_number')) {

                $table->dropColumn('tracking_number');

            }

        });
    }
};