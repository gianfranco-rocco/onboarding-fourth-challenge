<?php

namespace App\Observers;

use App\Models\City;

class CityObserver
{
    public function created(City $city): void
    {
        $city->clearCachedKeys();
    }

    public function updated(City $city): void
    {
        $city->clearCachedKeys();
    }

    public function deleted(City $city): void
    {
        $city->incomingFlights()->delete();
        $city->outgoingFlights()->delete();

        $city->clearCachedKeys();
    }

    public function restored(City $city): void
    {
        $city->clearCachedKeys();
    }

    public function forceDeleted(City $city): void
    {
        $city->clearCachedKeys();
    }
}
