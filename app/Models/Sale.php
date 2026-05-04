<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\SaleStatus;

class Sale extends Model
{

    protected $casts = [
        'status' => SaleStatus::class,
    ];

    protected $fillable = [
        'wc_id',
        'ebay_id',

        'contact_id',
        'user_id',
        'status',

        'xero_id',

        'notes',
        'planning_notes',
        'source',

        'predict_date',
        'invoice_date',

        'total_vat_amount',
        'total_amount',

        'deposit_paid',
        'fully_paid',

        // delivery address
        'deliver_address_1',
        'deliver_address_2',
        'deliver_town_city',
        'deliver_postcode',

        'delivery_method',

        'account_code',

        'internal_note',
        'customer_note',
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

     protected $appends = ['status_label'];

    public function getStatusLabelAttribute(): ?string
    {
        return $this->status?->label();
    }
}
