<?php

namespace App\Http\Controllers;

use App\Services\AirlineService;
use App\Services\CityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AirlineController extends Controller
{
    public function __invoke(Request $request, AirlineService $airlineService, CityService $cityService): JsonResponse|View
    {
        $response = [
            'airlines' => $airlineService->getCursorPaginated(10, $request->get('destination_city', 0), $request->get('active_flights', null)),
            'cities' => $cityService->get()
        ];
        
        if ($request->wantsJson()) {
            return response()->json($response);
        }

        return view('airline.index', $response);
    }
}
