<?php
namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['BCA Transfer', 'bank_transfer', 'Zalina Fashion', '1234567890', 'Transfer sesuai total pesanan. Setelah transfer, upload bukti pembayaran.', 1],
            ['BRI Transfer', 'bank_transfer', 'Zalina Fashion', '9876543210', 'Gunakan nominal sesuai total order agar mudah diverifikasi.', 2],
            ['DANA', 'ewallet', 'Zalina Fashion', '081234567890', 'Kirim bukti pembayaran setelah transaksi berhasil.', 3],
        ];

        foreach ($methods as [$name, $type, $accountName, $accountNumber, $instructions, $sort]) {
            PaymentMethod::updateOrCreate(
                ['name' => $name],
                [
                    'type' => $type,
                    'account_name' => $accountName,
                    'account_number' => $accountNumber,
                    'instructions' => $instructions,
                    'is_active' => true,
                    'sort_order' => $sort,
                ]
            );
        }
    }
}
