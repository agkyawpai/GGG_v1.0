<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TownshipFactory extends Factory
{
    public function definition(): array
    {
        $yangonTownships = [
            'Hlaing', 'Insein', 'Kamayut', 'Lanmadaw', 'Latha',
            'Mayangone', 'Mingalataungnyunt', 'North Okkalapa', 'Pabedan',
            'Pazundaung', 'Sanchaung', 'Shwepyitha', 'South Okkalapa',
            'Tamwe', 'Thingangyun', 'Thinganngyun', 'Yankin',
        ];

        return [
            'name' => $this->faker->unique()->randomElement($yangonTownships),
            'city' => 'Yangon',
        ];
    }
}
