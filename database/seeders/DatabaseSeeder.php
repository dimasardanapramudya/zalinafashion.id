<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            PaymentMethodSeeder::class,
            DiscountSeeder::class,
            CustomerSeeder::class,
            OrderDemoSeeder::class,
            SettingSeeder::class,
            HomeSliderSeeder::class,
        ]);
    }
}
