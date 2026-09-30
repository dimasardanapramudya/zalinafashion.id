<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetCode extends Model
{
    // ============================================================
    // TABLE
    // ============================================================

    protected $table = 'password_reset_codes';


    // ============================================================
    // MASS ASSIGNMENT
    // ============================================================

    protected $fillable = [
        'email',
        'code',
        'expires_at',
        'used',
    ];


    // ============================================================
    // CASTS
    // ============================================================

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];
}