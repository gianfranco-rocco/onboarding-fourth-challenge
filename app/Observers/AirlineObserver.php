<?php

namespace App\Observers;

use App\Models\Airline;
use Illuminate\Support\Facades\Cache;

class AirlineObserver
{
    public function created(Airline $airline): void
    {
        $this->clearFromCache();
    }

    public function updated(Airline $airline): void
    {
        $this->clearFromCache();
    }

    public function deleted(Airline $airline): void
    {
        $this->clearFromCache();
    }

    public function restored(Airline $airline): void
    {
        $this->clearFromCache();
    }

    public function forceDeleted(Airline $airline): void
    {
        $this->clearFromCache();
    }

    private function clearFromCache()
    {
        Cache::forget('airlines');
    }
}
