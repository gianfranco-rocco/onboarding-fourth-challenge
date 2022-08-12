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

    public function getCursorPaginated(string $cursor = ''): CursorPaginator
    {
        $cacheKey = "cities_cursorPaginate_$cursor";

        City::addCacheKeyToCachedKeys($cacheKey);

        return Cache::rememberForever($cacheKey, fn () => City::withCount([
            'incomingFlights',
            'outgoingFlights'
        ])->cursorPaginate());
    } 
}