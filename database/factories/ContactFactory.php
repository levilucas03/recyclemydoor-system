<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    public function definition(): array
    {

        $address_1 = this->faker->streetAddress();
        $town_city = $this->faker->city();
        $postcode = $this->faker->postcode();

        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'type' => 'customer',

            'email' => $this->faker->safeEmail(),
            'telephone' => $this->faker->numberBetween(100000000, 999999999),
            'mobile' => $this->faker->numberBetween(100000000, 999999999),

            'address_1' => $address_1,
            'address_2' => null,
            'town_city' => $town_city,
            'postcode' => $postcode,

            'invoice_address_1' => $address_1,
            'invoice_address_2' => null,
            'invoice_town_city' => $town_city,
            'invoice_postcode' => $postcode,
        ];
    }
}
