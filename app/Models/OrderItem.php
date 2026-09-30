<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'product_name',
        'variant_name',
        'price',
        'quantity',
        'subtotal',
        'weight',
    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'quantity' => 'integer',
            'subtotal' => 'integer',
            'weight' => 'integer',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Order Relationship
    |--------------------------------------------------------------------------
    */

    public function order(): BelongsTo
    {
        return $this->belongsTo(
            Order::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Product Relationship
    |--------------------------------------------------------------------------
    |
    | Relationship ini diperlukan karena OrderController menggunakan:
    |
    | ->with([
    |     'items',
    |     'items.product',
    |     'items.variant',
    | ])
    |
    */

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
            'product_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Variant Relationship
    |--------------------------------------------------------------------------
    */

    public function variant(): BelongsTo
    {
        return $this->belongsTo(
            ProductVariant::class,
            'variant_id'
        );
    }
}