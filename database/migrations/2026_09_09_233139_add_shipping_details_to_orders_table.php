<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan informasi detail pengiriman.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Destination
            |--------------------------------------------------------------------------
            */

            if (!Schema::hasColumn('orders', 'destination_id')) {

                $table
                    ->unsignedBigInteger('destination_id')
                    ->nullable()
                    ->after('shipping_address');

            }


            if (!Schema::hasColumn('orders', 'destination_name')) {

                $table
                    ->string('destination_name')
                    ->nullable()
                    ->after('destination_id');

            }


            /*
            |--------------------------------------------------------------------------
            | Shipping Courier
            |--------------------------------------------------------------------------
            */

            if (!Schema::hasColumn('orders', 'shipping_courier')) {

                $table
                    ->string('shipping_courier')
                    ->nullable()
                    ->after('destination_name');

            }


            if (!Schema::hasColumn('orders', 'shipping_service')) {

                $table
                    ->string('shipping_service')
                    ->nullable()
                    ->after('shipping_courier');

            }


            if (!Schema::hasColumn('orders', 'shipping_etd')) {

                $table
                    ->string('shipping_etd')
                    ->nullable()
                    ->after('shipping_service');

            }


            if (!Schema::hasColumn('orders', 'shipping_weight')) {

                $table
                    ->unsignedInteger('shipping_weight')
                    ->nullable()
                    ->after('shipping_etd');

            }

        });
    }


    /**
     * Rollback migration.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $columns = [

                'destination_id',

                'destination_name',

                'shipping_courier',

                'shipping_service',

                'shipping_etd',

                'shipping_weight',

            ];


            foreach ($columns as $column) {

                if (Schema::hasColumn('orders', $column)) {

                    $table->dropColumn($column);

                }

            }

        });
    }
};