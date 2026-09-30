<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder; use App\Models\Setting;
class SettingSeeder extends Seeder { public function run(): void { foreach(['site_name'=>'Zalina Fashion','site_tagline'=>'Elegance in Every Drape','announcement_text'=>'✨ Gratis ongkir se-Indonesia untuk pembelian di atas Rp 250.000 ✨','free_shipping_minimum'=>'250000','slider_autoplay_ms'=>'5000'] as $k=>$v) Setting::updateOrCreate(['key'=>$k],['value'=>$v]); } }
