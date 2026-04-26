<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = [
            [
                'name' => 'Ford Transit',
                'registration' => 'NL64 OCA',
                'fuel_type' => 'diesel',
                'tank_capacity' => 70,
                'current_mileage' => 132000,
            ],
            [
                'name' => 'Van 2',
                'registration' => 'FG34 HIJ',
                'fuel_type' => 'diesel',
                'tank_capacity' => 70,
                'current_mileage' => 95000,
            ],
            [
                'name' => 'Car 1',
                'registration' => 'S333 LEV',
                'fuel_type' => 'diesel',
                'tank_capacity' => 50,
                'current_mileage' => 100000,
            ],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::updateOrCreate(
                ['registration' => $vehicle['registration']],
                $vehicle
            );
        }
    }
}