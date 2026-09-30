<?php
namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['Pashmina', 'Koleksi pashmina untuk daily dan formal look.'],
            ['Segi Empat', 'Hijab segi empat dengan pilihan motif dan warna.'],
            ['Bergo', 'Bergo praktis untuk aktivitas harian.'],
            ['Inner Hijab', 'Inner dan ciput untuk kenyamanan berhijab.'],
            ['Niqab', 'Koleksi niqab yang nyaman dan elegan.'],
            ['Accessories', 'Pins, bros, magnet dan aksesori hijab.'],
        ];

        foreach ($categories as $index => [$name, $description]) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => $description, 'is_active' => true, 'sort_order' => $index + 1]
            );
        }
    }
}
