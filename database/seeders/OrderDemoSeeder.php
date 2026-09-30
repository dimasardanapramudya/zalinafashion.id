<?php
namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderDemoSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('role', 'customer')->first();
        $admin = User::where('role', 'admin')->first();
        $method = PaymentMethod::where('is_active', true)->first();
        $products = Product::take(3)->get();

        if (!$customer || !$method || $products->isEmpty()) return;

        foreach ($products as $index => $product) {
            $qty = $index + 1;
            $price = $product->sale_price ?: $product->price;
            $subtotal = $price * $qty;
            $number = 'ZLH-DEMO-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);

            $order = Order::updateOrCreate(
                ['order_number' => $number],
                [
                    'user_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'customer_email' => $customer->email,
                    'customer_phone' => '081234567890',
                    'shipping_address' => 'Alamat demo Surabaya, Indonesia',
                    'subtotal' => $subtotal,
                    'discount_total' => 0,
                    'shipping_total' => 15000,
                    'grand_total' => $subtotal + 15000,
                    'status' => $index === 0 ? 'processing' : 'pending',
                    'payment_status' => $index === 0 ? 'paid' : 'unpaid',
                ]
            );

            OrderItem::updateOrCreate(
                ['order_id' => $order->id, 'product_name' => $product->name],
                ['product_id' => $product->id, 'sku' => $product->sku, 'price' => $price, 'quantity' => $qty, 'subtotal' => $subtotal]
            );

            Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'payment_method_id' => $method->id,
                    'amount_expected' => $order->grand_total,
                    'amount_paid' => $index === 0 ? $order->grand_total : null,
                    'sender_name' => $index === 0 ? $customer->name : null,
                    'status' => $index === 0 ? 'verified' : 'pending',
                    'verified_by' => $index === 0 ? $admin?->id : null,
                    'verified_at' => $index === 0 ? now() : null,
                ]
            );
        }
    }
}
