<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{

    use HasFactory;

    protected $fillable = [
        'name',
        'registration',
        'fuel_type',
        'current_mileage'
    ];

    public function fuelLogs()
    {
        return $this->hasMany(FuelLog::class);
    }
}
