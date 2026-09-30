<?php

/**
 * FILE: app/Providers/EventServiceProvider.php
 *
 * KONFIGURASI EVENT DAN LISTENER
 *
 * Di sini kita register:
 * - Event: OrderCompleted
 * - Listener: DecreaseProductStock
 */

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [

        /**
         * EXISTING EVENTS
         * (Jangan dihapus, tinggal tambah di bawah)
         */
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        /**
         * PERBAIKAN: Register OrderCompleted Event
         *
         * Ketika event OrderCompleted di-dispatch,
         * listener DecreaseProductStock akan otomatis di-trigger
         */
        \App\Events\OrderCompleted::class => [
            \App\Listeners\DecreaseProductStock::class,
        ],

        /**
         * OPTIONAL: Tambahan listener untuk order completed
         *
         * Bisa add listener lain sesuai kebutuhan:
         * - SendOrderCompletedNotification (email ke customer)
         * - LogOrderCompletion (audit trail)
         * - UpdateRevenueReport (update laporan penjualan)
         * - Dll
         */
        // \App\Events\OrderCompleted::class => [
        //     \App\Listeners\SendOrderCompletedNotification::class,
        //     \App\Listeners\LogOrderCompletion::class,
        // ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}