<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder; use App\Models\HomeSlider;
class HomeSliderSeeder extends Seeder { public function run(): void { foreach([
['eyebrow'=>'Koleksi Terbaru 2026','title'=>'Elegance in Every Drape','accent_text'=>'Every Drape','description'=>'Premium modest fashion untuk setiap momen — dirancang untuk perempuan yang percaya diri.','button_text'=>'Belanja Koleksi','button_url'=>'/shop','theme'=>'maroon_gold','sort_order'=>1,'is_active'=>true],
['eyebrow'=>'Promo Spesial','title'=>'Warna Baru, Pesona Baru','accent_text'=>'Limited Collection','description'=>'Temukan warna-warna pilihan untuk melengkapi gaya harianmu.','button_text'=>'Lihat Koleksi','button_url'=>'/shop','theme'=>'rose','sort_order'=>2,'is_active'=>true],
['eyebrow'=>'Member Benefit','title'=>'Belanja Lebih Nyaman','accent_text'=>'Di Zalina','description'=>'Nikmati koleksi premium, promo pilihan, dan pengalaman belanja yang elegan.','button_text'=>'Mulai Belanja','button_url'=>'/shop','theme'=>'emerald','sort_order'=>3,'is_active'=>true],
] as $d) HomeSlider::updateOrCreate(['sort_order'=>$d['sort_order']],$d); } }
