<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WooCommerceOrderItem extends Model
{
    protected $guarded = [];

    protected $table = 'woocommerce_order_items';

    protected $casts = [
        'raw' => 'array',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(
            WooCommerceOrder::class,
            'woocommerce_order_id'
        );
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}