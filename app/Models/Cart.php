<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'user_id',
        'session_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Cart Items Relationship
    |--------------------------------------------------------------------------
    */

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}