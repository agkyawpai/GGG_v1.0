<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ShopFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'           => $this->faker->company() . ' Shop',
            'phone'          => '09' . $this->faker->numerify('#########'),
            'address'        => $this->faker->address(),
            'contact_person' => $this->faker->name(),
        ];
    }
}
