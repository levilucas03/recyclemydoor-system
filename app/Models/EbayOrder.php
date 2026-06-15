<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EbayOrder extends Model
{
    protected $fillable = [
        'ebay_account_id',
        'ebay_order_id',
        'sale_id',
        'status',
        'buyer_username',
        'buyer_email',
        'total',
        'currency',
        'ordered_at',
        'raw',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
        'raw' => 'array',
    ];

    public function account()
    {
        return $this->belongsTo(EbayAccount::class, 'ebay_account_id');
    }

    public function items()
    {
        return $this->hasMany(EbayOrderItem::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}