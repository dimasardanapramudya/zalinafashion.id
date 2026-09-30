<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'return_rejected_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('return_rejected_at')
                    ->nullable()
                    ->after('return_approved_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'return_rejected_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('return_rejected_at');
            });
        }
    }
};
