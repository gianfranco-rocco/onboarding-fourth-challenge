<?php

namespace Database\Seeders;

use App\Models\Airline;
use App\Models\City;
use Illuminate\Database\Seeder;

class AirlineSeeder extends Seeder
{
    public function run(): void
    {
        Airline::factory()
        ->count(50)
        ->create()
        ->each(function ($airline) {
            $skip = rand(0, 80);
            $take = rand(1, 20);

            $airline->cities()->attach(City::skip($skip)->take($take)->get());
        });
    }
}
