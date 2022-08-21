<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyCityRequest;
use App\Http\Requests\StoreCityRequest;
use App\Http\Requests\UpdateCityRequest;
use App\Http\Resources\ShowCityResource;
use App\Models\Airline;
use App\Models\City;
use App\Services\CityService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CityController extends Controller
{
    public function index(CityService $cityService): JsonResponse
    {
        return response()->json($cityService->get());
    }
    
    public function getAirlineCities(Airline $airline): Collection
    {
        return $airline->cities;
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
