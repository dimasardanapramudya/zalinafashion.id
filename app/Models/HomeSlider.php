<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HomeSlider extends Model { protected $fillable=['eyebrow','title','accent_text','description','button_text','button_url','image','theme','sort_order','is_active']; protected function casts(): array { return ['is_active'=>'boolean']; } }
