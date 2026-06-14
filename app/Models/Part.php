<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Part extends Model
{

    protected $fillable = [
        'name',
        'sku',
        'notes',
        'total_quantity',
        'total_cost',
        'unit_cost',
        'purchased_at',
    ];

    protected $casts = [
        'total_cost' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'purchased_at' => 'date',
    ];

    protected static function booted()
    {
        static::saving(function ($part) {
            if ($part->total_quantity > 0) {
                $part->unit_cost = $part->total_cost / $part->total_quantity;
            }
        });
    }

    public function allocations()
    {
        return $this->hasMany(ProductPartAllocation::class);
    }

    public function getUsedQuantityAttribute()
    {
        return $this->allocations->sum('quantity_used');
    }

    public function getAvailableQuantityAttribute()
    {
        return $this->total_quantity - $this->allocations->sum('quantity_used');
    }
}