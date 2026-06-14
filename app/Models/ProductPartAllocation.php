<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPartAllocation extends Model
{
    protected $fillable = [
        'product_id',
        'part_id',
        'quantity_used',
        'unit_cost',
        'cost_allocated',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function part()
    {
        return $this->belongsTo(Part::class);
    }
}