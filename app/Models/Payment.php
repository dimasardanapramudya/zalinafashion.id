<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Payment extends Model { protected $fillable=['order_id','payment_method_id','amount_expected','amount_paid','sender_name','sender_account','paid_at','proof_path','status','verified_by','verified_at','rejection_reason']; protected function casts():array{return ['amount_expected'=>'decimal:2','amount_paid'=>'decimal:2','paid_at'=>'datetime','verified_at'=>'datetime'];} public function order(){return $this->belongsTo(Order::class);} public function method(){return $this->belongsTo(PaymentMethod::class,'payment_method_id');} }
