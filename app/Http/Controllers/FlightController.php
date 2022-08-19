<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvokeFlightRequest;
use App\Services\AirlineService;
use App\Services\CityService;
use App\Services\FlightService;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\View\View;

class FlightController extends Controller
{
    public function __invoke(
        InvokeFlightRequest $request,
        AirlineService $airlineService,
        CityService $cityService,
        FlightService $flightService
    ): View|CursorPaginator
    {
        $flights = $flightService->getCursorPaginated(
            10, 
            $request->get('departure_at'), 
            $request->get('arrival_at'), 
            $request->get('airline'), 
            $request->get('departure_city'),
            $request->get('destination_city')
        );

        if ($request->wantsJson()) {
            return $flights;
        }

        return view('flight.index', [
            'airlines' => $airlineService->get(),
            'cities' => $cityService->get(),
            'flights' => $flights
        ]);
    }
}
