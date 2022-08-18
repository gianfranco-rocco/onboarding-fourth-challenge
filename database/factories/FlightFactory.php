<?php

namespace Database\Factories;

use App\Models\Airline;
use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

class FlightFactory extends Factory
{
    public function definition(): array
    {
        $cities = City::skip(0)->take(2)->get()->pluck('id');

        return [
            'departure_city_id' => $cities[0],
            'destination_city_id' => $cities[1],
            'departure_at' => now(),
            'arrival_at' => now()->addDays(2)
        ];
    }
}
