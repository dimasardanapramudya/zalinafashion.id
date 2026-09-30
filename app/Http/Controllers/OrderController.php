<?php

/**
 * FILE: app/Http/Controllers/OrderController.php
 * BAGIAN: Method untuk mark order as completed
 *
 * PERBAIKAN:
 * - Dispatch event OrderCompleted
 * - Trigger decrease stok otomatis
 * - Add safety check prevent double decrease
 */

namespace App\Http\Controllers;

use App\Events\OrderCompleted;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Mark order as COMPLETED
     *
     * WHEN: Admin menandai order sebagai selesai/dikirim
     * WHAT: 
     *   1. Update status order ke 'completed'
     *   2. Dispatch OrderCompleted event
     *   3. Event trigger listener untuk decrease stok
     *
     * @param Order $order
     * @return \Illuminate\Http\Response
     */
    public function markAsCompleted(Order $order)
    {
        /**
         * VALIDATION: Cek apakah order sudah di-complete sebelumnya
         *
         * Prevent accidental double-complete yang bisa trigger
         * listener berkali-kali dan decrease stok berkali-kali
         */
        if ($order->status === 'completed') {
            Log::warning(
                "Attempt to mark completed order as completed again",
                ['order_id' => $order->id, 'order_number' => $order->order_number]
            );

            return response()->json([
                'error' => 'Pesanan sudah ditandai selesai sebelumnya',
                'message' => 'Tidak bisa mengubah status pesanan yang sudah completed',
            ], 422);
        }

        /**
         * UPDATE STATUS ORDER
         *
         * Tandai order sebagai completed beserta timestamp
         */
        try {
            $order->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            /**
             * DISPATCH EVENT
             *
             * Trigger event OrderCompleted
             * Listener DecreaseProductStock akan otomatis di-trigger
             * Stok produk akan dikurangi sesuai quantity yang terjual
             */
            event(new OrderCompleted($order));

            Log::info(
                "Order marked as completed",
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'completed_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil ditandai selesai',
                'data' => [
                    'order_id' => $order->id,
                    'status' => $order->status,
                    'completed_at' => $order->completed_at,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error(
                "Error marking order as completed",
                [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]
            );

            return response()->json([
                'error' => 'Terjadi kesalahan saat menandai pesanan selesai',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ALTERNATIVE: Jika ingin handle decrease stok LANGSUNG di controller
     * (tanpa pakai event/listener)
     *
     * Gunakan method ini jika:
     * - Tidak mau pakai event system
     * - Ingin lebih simple dan synchronous
     * - Tidak butuh listener untuk hal lain
     *
     * @param Order $order
     * @return \Illuminate\Http\Response
     */
    public function markAsCompletedDirect(Order $order)
    {
        /**
         * SAFETY CHECK: Prevent double-decrease
         */
        if ($order->stock_decreased === true) {
            return response()->json([
                'error' => 'Stok sudah dikurangi sebelumnya',
            ], 422);
        }

        try {
            /**
             * DATABASE TRANSACTION
             */
            \Illuminate\Support\Facades\DB::transaction(function () use ($order) {

                /**
                 * LOOP SETIAP ITEM ORDER DAN DECREASE STOK
                 */
                foreach ($order->items as $item) {
                    $product = $item->product;

                    if (!$product) {
                        continue;
                    }

                    /**
                     * PESSIMISTIC LOCK
                     * Prevent race condition
                     */
                    $product = $product::lockForUpdate()->find($product->id);

                    $quantity = (int) $item->quantity;
                    $newStock = max(0, (int) $product->stock - $quantity);

                    /**
                     * UPDATE STOK
                     */
                    $product->update([
                        'stock' => $newStock,
                    ]);

                    Log::info("Stock decreased", [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'new_stock' => $newStock,
                    ]);
                }

                /**
                 * UPDATE ORDER: Mark sebagai completed + stock_decreased
                 */
                $order->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'stock_decreased' => true,
                    'stock_decreased_at' => now(),
                ]);

            }, attempts: 3);

            return response()->json([
                'success' => true,
                'message' => 'Pesanan selesai dan stok berkurang',
            ]);

        } catch (\Exception $e) {
            Log::error("Error: " . $e->getMessage());

            return response()->json([
                'error' => 'Terjadi kesalahan',
            ], 500);
        }
    }

    /**
     * Get order detail
     *
     * @param Order $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        return response()->json([
            'data' => $order->load('items', 'payment'),
        ]);
    }

    /**
     * Update order (untuk field lain, bukan status)
     *
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        // Implement sesuai kebutuhan
    }
}