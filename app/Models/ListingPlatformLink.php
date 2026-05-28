<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingPlatformLink extends Model
{
    protected $fillable = [
        'listing_id',
        'listing_platform_id',
        'external_id',
        'status',
        'payload',
        'error',
        'published_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'published_at' => 'datetime',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function platform()
    {
        return $this->belongsTo(ListingPlatform::class, 'listing_platform_id');
    }
}