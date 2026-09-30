<?php

namespace App\View\Composers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\View\View;

class AdminNotificationComposer
{
    public function compose(View $view): void
    {
        $pendingPaymentCount = Payment::query()
            ->whereNotNull('proof_path')
            ->where(function ($query) {
                $query
                    ->whereNull('status')
                    ->orWhere('status', '!=', 'verified');
            })
            ->count();

        $pendingOrderCount = Order::query()
            ->where('payment_status', 'paid')
            ->where('status', 'pending')
            ->count();

        $view->with([
            'pendingPaymentCount' => $pendingPaymentCount,
            'pendingOrderCount' => $pendingOrderCount,
        ]);
    }
}
