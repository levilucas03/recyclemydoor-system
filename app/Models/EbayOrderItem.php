<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EbayOrderItem extends Model
{
    protected $fillable = [
        'ebay_order_id',
        'product_id',
        'ebay_line_item_id',
        'sku',
        'title',
        'quantity',
        'price',
        'raw',
    ];

    protected $casts = [
        'raw' => 'array',
    ];

    public function ebayOrder()
    {
        return $this->belongsTo(EbayOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}