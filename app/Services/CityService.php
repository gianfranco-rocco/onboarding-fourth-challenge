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
        return Cache::rememberForever('cities', fn () => City::all());
    }

    public function getCursorPaginated(int $total = 15): CursorPaginator
    {
        return City::withCount([
            'incomingFlights',
            'outgoingFlights'
        ])->orderBy(request()->get('sort', 'id'), request()->get('sort_dir', 'desc'))->cursorPaginate($total)->withQueryString();
    }
}