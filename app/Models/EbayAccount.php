<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EbayAccount extends Model
{
    protected $fillable = [
        'name',
        'environment',
        'access_token',
        'refresh_token',
        'access_token_expires_at',
        'is_active',
    ];

    protected $casts = [
        'access_token_expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(EbayOrder::class);
    }
}