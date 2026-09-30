<?php
namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'Pashmina' => [
                ['Pashmina Ceruty Premium', 89000, 75000, 42],
                ['Pashmina Silk Luxe', 125000, null, 24],
            ],
            'Segi Empat' => [
                ['Segi Empat Voal Premium', 69000, 59000, 55],
                ['Segi Empat Motif Floral', 79000, null, 31],
            ],
            'Bergo' => [
                ['Bergo Daily Comfort', 65000, 55000, 38],
                ['Bergo Sport Active', 85000, null, 21],
            ],
            'Inner Hijab' => [
                ['Inner Ninja Soft', 29000, 25000, 90],
                ['Ciput Rajut Premium', 35000, null, 72],
            ],
            'Niqab' => [
                ['Niqab Aisyah Premium', 99000, 89000, 19],
                ['Niqab Silk Flow', 115000, null, 14],
            ],
            'Accessories' => [
                ['Magnet Hijab Premium', 19000, null, 100],
                ['Pin Hijab Elegant Set', 25000, 20000, 67],
            ],
        ];

        $sku = 1001;
        foreach ($catalog as $categoryName => $products) {
            $category = Category::where('name', $categoryName)->firstOrFail();
            foreach ($products as [$name, $price, $salePrice, $stock]) {
                $product = Product::updateOrCreate(
                    ['slug' => Str::slug($name)],
                    [
                        'category_id' => $category->id,
                        'name' => $name,
                        'description' => $name . ' dari Zalina Fashion. Nyaman, premium, dan cocok untuk berbagai aktivitas.',
                        'price' => $price,
                        'sale_price' => $salePrice,
                        'stock' => $stock,
                        'sku' => 'ZLH-' . $sku,
                        'is_active' => true,
                        'is_featured' => true,
                    ]
                );

                foreach ([['Mocca', 0], ['Black', 0], ['Dusty Pink', 5000]] as $i => [$color, $extra]) {
                    ProductVariant::updateOrCreate(
                        ['product_id' => $product->id, 'color' => $color],
                        [
                            'name' => $color,
                            'sku' => 'ZLH-' . $sku . '-' . ($i + 1),
                            'price' => $extra ? $price + $extra : null,
                            'stock' => max(1, intdiv($stock, 3)),
                            'is_active' => true,
                        ]
                    );
                }
                $sku++;
            }
        }
    }
}
