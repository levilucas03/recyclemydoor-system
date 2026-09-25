<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WooCommerceOrder extends Model
{
    protected $guarded = [];

    protected $table = 'woocommerce_orders';

    protected $casts = [
        'raw' => 'array',
        'ordered_at' => 'datetime',
        'total' => 'decimal:2',
        'shipping_total' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(
            WooCommerceOrderItem::class,
            'woocommerce_order_id',
            'id'
        );
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}