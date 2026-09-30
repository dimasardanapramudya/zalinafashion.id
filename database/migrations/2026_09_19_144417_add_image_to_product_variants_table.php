<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom gambar per varian.
     * Isinya path relatif di disk "public", misal: variants/abc123.jpg
     * (sama seperti kolom products.image).
     */
    public function up(): void
    {
        if (!Schema::hasColumn('product_variants', 'image')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->string('image')->nullable()->after('color');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('product_variants', 'image')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
    }
};