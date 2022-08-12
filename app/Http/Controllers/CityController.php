<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCityRequest;
use App\Http\Requests\UpdateCityRequest;
use App\Models\City;
use App\Services\AirlineService;
use App\Services\CityService;
use Illuminate\Http\Request;
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

    public function index(Request $request): View
    {
        return view('city.index', [
            'cities' => $this->cityService->getCursorPaginated($request->get('cursor', '')),
            'airlines' => $this->airlineService->get()
        ]);
    }

    public function store(StoreCityRequest $request)
    {
        //
    }

    public function edit(City $city)
    {
        //
    }

    public function update(UpdateCityRequest $request, City $city)
    {
        //
    }

    public function destroy(City $city)
    {
        //
    }
}
