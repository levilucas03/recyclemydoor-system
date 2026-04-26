<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FuelLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'date',
        'litres',
        'cost',
        'price_per_litre',
        'mileage',
        'location',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'litres' => 'decimal:2',
        'cost' => 'decimal:2',
        'price_per_litre' => 'decimal:3',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors (this is where the value is)
    |--------------------------------------------------------------------------
    */

    // Auto calculate price per litre if not set
    public function getPricePerLitreAttribute($value)
    {
        if ($value) return $value;

        if ($this->litres > 0) {
            return round($this->cost / $this->litres, 3);
        }

        return null;
    }

    // Previous fuel log (for calculations)
    public function previousLog()
    {
        return self::where('vehicle_id', $this->vehicle_id)
            ->where('mileage', '<', $this->mileage)
            ->orderBy('mileage', 'desc')
            ->first();
    }

    // Miles since last fill
    public function getMilesDrivenAttribute()
    {
        $previous = $this->previousLog();

        if (!$previous) return null;

        return $this->mileage - $previous->mileage;
    }

    // Cost per mile
    public function getCostPerMileAttribute()
    {
        $miles = $this->miles_driven;

        if (!$miles || $miles <= 0) return null;

        return round($this->cost / $miles, 2);
    }

    // MPG (miles per gallon approx)
    public function getMpgAttribute()
    {
        $miles = $this->miles_driven;

        if (!$miles || $this->litres <= 0) return null;

        $gallons = $this->litres * 0.219969; // litres → UK gallons

        return round($miles / $gallons, 2);
    }
}