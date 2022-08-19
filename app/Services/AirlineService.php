<?php

namespace App\Services;

use App\Models\Airline;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Facades\Cache;

class AirlineService
{
    public function get(): Collection
    {
        return Cache::rememberForever('airlines', fn () => Airline::orderBy('name')->get(['id', 'name']));
    }

    public function getCursorPaginated(int $total = 15, int $destinationCity = 0, ?int $activeFlights = null): CursorPaginator
    {
        return Airline::withCount('activeFlights')
        ->when($destinationCity, function ($query) use ($destinationCity) {
            $query->whereHas('flights', function ($query) use ($destinationCity) {
                $query->where('destination_city_id', $destinationCity);
            });
        })
        ->when(is_int($activeFlights), function ($query) use ($activeFlights) {
            $query->having('active_flights_count', $activeFlights);
        })
        ->orderBy('id', 'desc')
        ->cursorPaginate($total)
        ->withQueryString();
    }
}