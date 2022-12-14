<?php

namespace App\Services;

use App\Models\City;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Facades\Cache;

class CityService
{
    public function get(): Collection
    {
        return Cache::rememberForever('cities', fn () => City::orderBy('name')->get());
    }

    public function getCursorPaginated(int $total = 15, int $airline = 0, string $sort = ''): CursorPaginator
    {
        $sortingColumn = 'id';
        $sortingDirection = 'desc';

        if ($sort) {
            $explodedSort = explode(',', $sort);
    
            if (is_array($explodedSort)) {
                $sortingColumn = $explodedSort[0] ?? 'id';
                $sortingDirection = $explodedSort[1] ?? 'desc';
            }
        }

        return City::withCount([
            'incomingFlights',
            'outgoingFlights'
        ])
        ->when($airline, function ($query) use ($airline) {
            $query
            ->whereHas('incomingFlights', function ($query) use ($airline) {
                $query->where('airline_id', $airline);
            })
            ->orWhereHas('outgoingFlights', function ($query) use ($airline) {
                $query->where('airline_id', $airline);
            });
        })
        ->orderBy($sortingColumn, $sortingDirection)
        ->cursorPaginate($total)
        ->withQueryString();
    }
}