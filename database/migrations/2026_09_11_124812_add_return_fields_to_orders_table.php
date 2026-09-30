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
            |--------------------------------------------------------------------------
            | RETURN / REFUND INFORMATION
            |--------------------------------------------------------------------------
            |
            | Sistem retur Zalina Fashion:
            |
            | none
            | requested
            | approved
            | rejected
            | processing
            | completed
            |
            */


            if (!Schema::hasColumn('orders','return_status')) {

                $table->string('return_status')
                    ->default('none')
                    ->after('delivered_at');

            }


            if (!Schema::hasColumn('orders','return_reason')) {

                $table->text('return_reason')
                    ->nullable()
                    ->after('return_status');

            }


            if (!Schema::hasColumn('orders','return_note')) {

                $table->text('return_note')
                    ->nullable()
                    ->after('return_reason');

            }


            if (!Schema::hasColumn('orders','return_admin_note')) {

                $table->text('return_admin_note')
                    ->nullable()
                    ->after('return_note');

            }


            /*
            |--------------------------------------------------------------------------
            | RETURN TIMESTAMP
            |--------------------------------------------------------------------------
            */


            if (!Schema::hasColumn('orders','return_requested_at')) {

                $table->timestamp('return_requested_at')
                    ->nullable()
                    ->after('return_admin_note');

            }


            if (!Schema::hasColumn('orders','return_approved_at')) {

                $table->timestamp('return_approved_at')
                    ->nullable()
                    ->after('return_requested_at');

            }


            if (!Schema::hasColumn('orders','return_rejected_at')) {

                $table->timestamp('return_rejected_at')
                    ->nullable()
                    ->after('return_approved_at');

            }


            if (!Schema::hasColumn('orders','return_completed_at')) {

                $table->timestamp('return_completed_at')
                    ->nullable()
                    ->after('return_rejected_at');

            }


        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {


            $columns = [

                'return_status',

                'return_reason',

                'return_note',

                'return_admin_note',

                'return_requested_at',

                'return_approved_at',

                'return_rejected_at',

                'return_completed_at',

            ];


            foreach($columns as $column){


                if(
                    Schema::hasColumn(
                        'orders',
                        $column
                    )
                ){

                    $table->dropColumn($column);

                }

            }


        });
    }

};