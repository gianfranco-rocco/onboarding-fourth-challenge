<?php

namespace App\Services;

use App\Models\Airline;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\CursorPaginator;

class AirlineService
{
    public function get(): Collection
    {
        return Airline::all([
            'id',
            'name'
        ]);
    }

    public function getCursorPaginated(int $total = 15, int $destinationCity = 0, int $activeFlights = 0): CursorPaginator
    {
        return Airline::withCount('activeFlights')
        ->when($destinationCity, function ($query) use ($destinationCity) {
            $query->whereHas('flights', function ($query) use ($destinationCity) {
                $query->where('destination_city_id', $destinationCity);
            });
        })
        ->when($activeFlights, function ($query) use ($activeFlights) {
            $query->having('active_flights_count', '>=', $activeFlights);
        })
        ->orderBy('id', 'desc')
        ->cursorPaginate($total)
        ->withQueryString();
    }
}