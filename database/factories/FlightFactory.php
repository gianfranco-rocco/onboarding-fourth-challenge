<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FlightFactory extends Factory
{
    public function definition(): array
    {
        return [
            'departure_at' => now(),
            'arrival_at' => now()->addDays(2)
        ];
    }
}
