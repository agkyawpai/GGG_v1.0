<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BikerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'          => $this->faker->name('male'),
            'phone'         => '09' . $this->faker->numerify('#########'),
            'address'       => $this->faker->address(),
            'license_plate' => strtoupper($this->faker->bothify('??-####')),
            'is_active'     => true,
        ];
    }
}
