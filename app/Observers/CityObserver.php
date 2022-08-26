<?php

namespace App\Observers;

use App\Models\City;
use Illuminate\Support\Facades\Cache;

class CityObserver
{
    public function created(City $city): void
    {
        $this->clearFromCache();
    }

    public function updated(City $city): void
    {
        $this->clearFromCache();
    }

    public function deleted(City $city): void
    {
        $this->clearFromCache();
    }

    public function restored(City $city): void
    {
        $this->clearFromCache();
    }

    public function forceDeleted(City $city): void
    {
        $this->clearFromCache();
    }

    private function clearFromCache(): void
    {
        Cache::forget('cities');
    }
}
