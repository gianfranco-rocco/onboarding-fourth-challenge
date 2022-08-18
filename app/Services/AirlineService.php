<?php

namespace App\Services;

use App\Models\Airline;
use Illuminate\Database\Eloquent\Collection;

class AirlineService
{
    public function get(): Collection
    {
        return Airline::all([
            'id',
            'name'
        ]);
    }
}