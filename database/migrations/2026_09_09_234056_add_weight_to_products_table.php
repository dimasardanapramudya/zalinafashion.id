<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Product Weight
            |--------------------------------------------------------------------------
            |
            | Satuan:
            | gram
            |
            | Contoh:
            | 100 = 100 gram
            | 250 = 250 gram
            | 1000 = 1 kilogram
            |
            */

            if (!Schema::hasColumn('products', 'weight')) {

                $table
                    ->unsignedInteger('weight')
                    ->default(0)
                    ->after('stock');

            }

        });
    }


    /**
     * Rollback migration.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {

            if (Schema::hasColumn('products', 'weight')) {

                $table->dropColumn('weight');

            }

        });
    }
};