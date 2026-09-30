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
        Schema::table('orders', function (Blueprint $table) {

            $table
                ->string('shipping_status')
                ->default('waiting')
                ->after('status');

        });
    }

    /**
     * Rollback migration.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->dropColumn('shipping_status');

        });
    }
};