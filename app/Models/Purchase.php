<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\PurchaseStatus;

class Purchase extends Model
{

    protected $fillable = [
        'contact_id',
        'user_id',
        'status',
        'purchase_date',
        'includes_vat',
        'notes',
        'driver_notes',
        'collection_notes',
        'ideal_collection_date',
        'total_amount',
        'deposit_paid',
        'fully_paid',
        'collected_by',
        'collection_address_1',
        'collection_address_2',
        'collection_country',
        'collection_town_city',
        'collection_postcode',
        'printed',
        'stream_id',
        'on_hand_date',
        'collection_date',
        'source_id',
    ];

    protected $casts = [
        'status' => PurchaseStatus::class,
    ];

    // Purchase.php
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    protected $appends = ['status_label'];

    public function getStatusLabelAttribute(): ?string
    {
        return $this->status?->label();
    }

    public function source()
    {
        return $this->belongsTo(Source::class);
    }
}
