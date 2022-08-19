<?php

namespace App\Http\Controllers;

use App\Services\AirlineService;
use App\Services\CityService;
use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\View\View;

class CityController extends Controller
{
    private CityService $cityService;
    private AirlineService $airlineService;

    public function __construct(CityService $cityService, AirlineService $airlineService)
    {
        $this->cityService = $cityService;
        $this->airlineService = $airlineService;
    }

    public function index(Request $request): View|CursorPaginator
    {
        $cities = $this->cityService->getCursorPaginated(10, $request->get('airline', 0), $request->get('sort', ''));

        if ($request->wantsJson()) {
            return $cities;
        }

        return view('city.index', [
            'cities' => $cities,
            'airlines' => $this->airlineService->get()
        ]);
    }
}
