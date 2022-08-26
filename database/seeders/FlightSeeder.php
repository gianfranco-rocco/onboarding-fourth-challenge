<?php

namespace Database\Seeders;

use App\Models\Airline;
use App\Models\Flight;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class FlightSeeder extends Seeder
{
    public function run(): void
    {
        Airline::with('cities')
        ->take(50)
        ->whereHas('cities')
        ->withCount('cities')
        ->havingRaw("cities_count >= 2")
        ->get()
        ->each(function ($airline) {
            Flight::factory()
            ->count(rand(1, 10))
            ->for($airline)
            ->for($airline->cities[0], 'departureCity')
            ->for($airline->cities[0], 'destinationCity')
            ->create()
            ->each(function ($flight) use ($airline) {
                $departureCity = $airline->cities->shuffle()->first();

                /**
                 * Making sure a city isn't both the departure and destination city
                 */
                do {
                    $destinationCity = $airline->cities->shuffle()->first();
                } while ($destinationCity === $departureCity);

                $now = CarbonImmutable::now();

                /**
                 * Just random condition to decide wether to set the departure date as the current timestamp
                 * or to add days and hours
                 */
                $departureAt = rand(0, 1) ? $now : $now->addDays(rand(1, 10))->addHours(rand(1, 24));

                $flight->update([
                    'departure_city_id' => $departureCity->id,
                    'destination_city_id' => $destinationCity->id,
                    'departure_at' => $departureAt,
                    'arrival_at' => $departureAt->addDays(rand(1, 3))
                ]);
            });
        });
    }
}
