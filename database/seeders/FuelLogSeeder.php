<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\FuelLog;
use Carbon\Carbon;

class FuelLogSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = Vehicle::all();

        foreach ($vehicles as $vehicle) {

            $date = Carbon::now()->subDays(30); // last 30 days
            $mileage = $vehicle->current_mileage ?? rand(50000, 120000);

            for ($i = 0; $i < 12; $i++) {

                // simulate days between fills
                $date->addDays(rand(2, 4));

                // simulate miles driven
                $milesDriven = rand(150, 400);
                $mileage += $milesDriven;

                // litres + cost
                $litres = rand(40, 70);
                $pricePerLitre = rand(140, 170) / 100; // £1.40 - £1.70
                $cost = $litres * $pricePerLitre;

                FuelLog::create([
                    'vehicle_id' => $vehicle->id,
                    'date' => $date->format('Y-m-d'),
                    'litres' => $litres,
                    'cost' => round($cost, 2),
                    'price_per_litre' => $pricePerLitre,
                    'mileage' => $mileage,
                    'location' => fake()->randomElement([
                        'Shell',
                        'BP',
                        'Tesco',
                        'Esso'
                    ]),
                    'notes' => null,
                ]);
            }
        }
    }
}