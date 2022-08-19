<?php

namespace App\Services;

use App\Models\Flight;
use Carbon\Carbon;
use Illuminate\Pagination\CursorPaginator;

class FlightService
{
    public function getCursorPaginated(
        int $total = 10,
        ?Carbon $departureAt = null,
        ?Carbon $arrivalAt = null,
        ?int $airline = null,
        ?int $departureCity = 0,
        ?int $destinationCity = 0
    ): CursorPaginator
    {
        return Flight::with([
            'airline:id,name',
            'departureCity',
            'destinationCity'
        ])
        ->when($departureAt, function ($query) use ($departureAt) {
            $query->whereDate('departure_at', $departureAt);
        })
        ->when($arrivalAt, function ($query) use ($arrivalAt) {
            $query->whereDate('arrival_at', $arrivalAt);
        })
        ->when($airline, function ($query) use ($airline) {
            $query->where('airline_id', $airline);
        })
        ->when($departureCity, function ($query) use ($departureCity) {
            $query->where('departure_city_id', $departureCity);
        })
        ->when($destinationCity, function ($query) use ($destinationCity) {
            $query->where('destination_city_id', $destinationCity);
        })
        ->orderBy('id', 'desc')
        ->cursorPaginate($total)
        ->withQueryString();
    }
}