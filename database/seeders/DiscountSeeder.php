<?php
namespace Database\Seeders;

use App\Models\Discount;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    public function run(): void
    {
        $discounts = [
            ['Promo Launch Zalina', 'ZALINA10', 'percentage', 10, 100000],
            ['Potongan Rp20.000', 'HEMAT20K', 'fixed', 20000, 250000],
            ['Member Welcome', 'WELCOME15', 'percentage', 15, 150000],
        ];

        foreach ($discounts as [$name, $code, $type, $value, $minimum]) {
            Discount::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'type' => $type, 'value' => $value, 'minimum_order' => $minimum, 'is_active' => true]
            );
        }
    }
}
