<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesClosing extends Model
{
    use HasFactory;

    protected $table = 'sales_closings';

    protected $fillable = [
        'closed_at',
        'period_from',
        'period_to',
        'total_entries',
        'total_quantity',
        'total_gross',
        'total_discount',
        'total_shipping',
        'total_shipping_actual',
        'total_admin_fee',
        'total_net',
        'closed_by',
        'note',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
        'period_from' => 'date',
        'period_to' => 'date',
        'total_entries' => 'integer',
        'total_quantity' => 'integer',
        'total_gross' => 'integer',
        'total_discount' => 'integer',
        'total_shipping' => 'integer',
        'total_shipping_actual' => 'integer',
        'total_admin_fee' => 'integer',
        'total_net' => 'integer',
    ];

    public function entries()
    {
        return $this->hasMany(SalesLedger::class, 'sales_closing_id');
    }
}
