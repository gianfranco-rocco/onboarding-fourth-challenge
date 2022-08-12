<?php

namespace App\Observers;

use App\Models\City;

class CityObserver
{
    public function created(City $city): void
    {
        //
    }

    public function updated(City $city): void
    {
        //
    }

    public function deleted(City $city): void
    {
        $city->incomingFlights()->delete();
        $city->outgoingFlights()->delete();
    }

    public function restored(City $city): void
    {
        //
    }

    public function forceDeleted(City $city): void
    {
        //
    }
}
