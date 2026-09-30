<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = [

        'name',
        'code',
        'type',
        'value',
        'minimum_order',
        'starts_at',
        'ends_at',
        'is_active',
        'image',

        // NEW: dipakai admin buat atur tampilan kartu promo di homepage
        'theme',
        'card_style',

    ];


    protected $casts = [

        'is_active' => 'boolean',

        'starts_at' => 'datetime',

        'ends_at' => 'datetime',

        'value' => 'decimal:2',

        'minimum_order' => 'decimal:2',

    ];


    /*
    |--------------------------------------------------------------------------
    | DEFAULTS
    |--------------------------------------------------------------------------
    | Supaya promo lama (sebelum kolom theme/card_style ditambahkan) tetap
    | tampil rapi tanpa perlu diedit manual satu-satu.
    */
    protected $attributes = [
        'theme' => 'maroon_gold',
        'card_style' => 'ticket',
    ];


    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Hanya promo yang aktif DAN sedang dalam periode berlaku.
     * Dipakai di homepage supaya promo kadaluarsa/nonaktif otomatis hilang.
     */
    public function scopeActive($query)
    {
        return $query
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    /**
     * Promo yang sedang berjalan diurutkan: yang paling cepat berakhir duluan.
     * Cocok untuk homepage biar promo yang "hampir habis" lebih menonjol.
     */
    public function scopeEndingSoon($query)
    {
        return $query->orderByRaw('ends_at IS NULL, ends_at ASC');
    }


    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /**
     * Nilai diskon yang sudah diformat sesuai tipenya.
     * type = percent -> "25%"
     * type = fixed      -> "Rp 50.000"
     */
    public function getFormattedValueAttribute(): string
    {
        if ($this->type === 'percent') {
            return rtrim(rtrim(number_format((float) $this->value, 2, ',', '.'), '0'), ',') . '%';
        }

        return 'Rp ' . number_format((float) $this->value, 0, ',', '.');
    }

    /**
     * True jika promo akan habis dalam <= 3 hari ke depan.
     * Bisa dipakai untuk badge "Segera Berakhir" di homepage.
     */
    public function getIsEndingSoonAttribute(): bool
    {
        if (!$this->ends_at) {
            return false;
        }

        return now()->diffInDays($this->ends_at, false) <= 3
            && now()->lessThanOrEqualTo($this->ends_at);
    }
}