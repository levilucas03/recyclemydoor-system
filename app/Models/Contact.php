<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contact extends Model
{

    use HasFactory;
    protected $appends = ['name'];


    protected $fillable = [
        'first_name',
        'last_name',
        'type',
        'email',
        'telephone',
        'mobile',
        'note',
        'address_1',
        'address_2',
        'country',
        'town_city',
        'postcode',
        'invoice_address_1',
        'invoice_address_2',
        'invoice_country',
        'invoice_town_city',
        'invoice_postcode',
        'ebay_username',
        'latitude',
        'longitude',
        'place_id',
        'xero_id',
        'old_xero_id',
        'company',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function getNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}") ?: 'No Name';
    }

    public function hasValidXeroId(): bool
    {
        return !empty($this->xero_id) &&
            $this->xero_id !== '00000000-0000-0000-0000-000000000000';
    }
}
