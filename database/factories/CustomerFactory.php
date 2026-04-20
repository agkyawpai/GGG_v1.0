<?php

namespace Database\Factories;

use App\Models\Township;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'        => $this->faker->name(),
            'phone'       => '09' . $this->faker->numerify('#########'),
            'address'     => $this->faker->address(),
            'township_id' => Township::inRandomOrder()->first()?->id ?? Township::factory(),
        ];
    }
}
