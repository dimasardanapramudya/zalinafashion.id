<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Category extends Model { protected $fillable=['name','slug','description','image','is_active','sort_order','show_on_home','home_position']; public function products(){return $this->hasMany(Product::class);} protected function casts(): array{return ['is_active'=>'boolean','show_on_home'=>'boolean','home_position'=>'integer'];} }
