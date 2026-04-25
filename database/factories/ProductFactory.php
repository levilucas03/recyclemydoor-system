<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class ProductFactory extends Factory
{

    public function definition(): array
    {
        return [
            'sku' => strtoupper($this->faker->bothify('SKU###')),
            'notes' => $this->faker->paragraph,
            'description' => $this->faker->sentence,
            'quantity' => rand(1, 10),
            'width' => $this->faker->randomFloat(2, 10, 100),
            'height' => $this->faker->randomFloat(2, 10, 100),
            'depth' => $this->faker->randomFloat(2, 10, 100),
        ];
    }

}
