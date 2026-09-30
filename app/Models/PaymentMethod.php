<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PaymentMethod extends Model { protected $fillable=['name','type','account_name','account_number','instructions','is_active','sort_order']; protected function casts():array{return ['is_active'=>'boolean'];} }
