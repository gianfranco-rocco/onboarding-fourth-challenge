<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyCityRequest;
use App\Http\Requests\StoreCityRequest;
use App\Http\Requests\UpdateCityRequest;
use App\Http\Resources\ShowCityResource;
use App\Models\City;
use App\Services\AirlineService;
use App\Services\CityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        $cities = $this->cityService->getCursorPaginated(10);

        if ($request->wantsJson()) {
            return $cities;
        }

        return view('city.index', [
            'cities' => $cities,
            'airlines' => $this->airlineService->get()
        ]);
    }

    public function store(StoreCityRequest $request): JsonResponse
    {
        City::create($request->validated());

        return response()->json([
            'message' => 'City created successfully.'
        ], Response::HTTP_CREATED);
    }

    public function show(City $city): ShowCityResource
    {
        return ShowCityResource::make($city);
    }

    public function update(UpdateCityRequest $request, City $city): JsonResponse
    {
        $city->update($request->validated());

        return response()->json([
            'message' => 'City updated successfully.'
        ]);
    }

    /**
     * 'DestroyCityRequest' is used for checking if deletion confirmation
     * is to be required
     */
    public function destroy(DestroyCityRequest $request, City $city): JsonResponse
    {
        $city->delete();

        return response()->json([
            'message' => 'City deleted successfully.'
        ]);
    }
}
