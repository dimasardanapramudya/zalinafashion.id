<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('show_on_home')
                ->default(false)
                ->after('is_active');

            $table->unsignedTinyInteger('home_position')
                ->nullable()
                ->after('show_on_home');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn([
                'show_on_home',
                'home_position',
            ]);
        });
    }
};