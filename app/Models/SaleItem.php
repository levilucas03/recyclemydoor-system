<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'type',
        'product_id',
        'title',
        'description',
        'price',
        'qty',
        'discount',
        'vat_amount',
        'total',
        'account_code',
        'note',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}