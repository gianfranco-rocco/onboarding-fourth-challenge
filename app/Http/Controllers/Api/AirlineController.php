<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAirlineRequest;
use App\Http\Requests\UpdateAirlineRequest;
use App\Http\Resources\ShowAirlineResource;
use App\Models\Airline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class AirlineController extends Controller
{
    public function store(StoreAirlineRequest $request): JsonResponse
    {
        $airline = Airline::create($request->validated());

        if ($request->cities) {
            $airline->cities()->attach($request->cities);
        }

        return response()->json([
            'message' => 'Airline created successfully.'
        ]);
    }

    public function show(Airline $airline): JsonResource
    {
        return ShowAirlineResource::make($airline);
    }

    public function update(UpdateAirlineRequest $request, Airline $airline): JsonResponse
    {
        $airline->update($request->validated());

        $airline->cities()->detach();

        if ($request->cities) {
            $airline->cities()->attach($request->cities);
        }

        return response()->json([
            'message' => 'Airline updated successfully.'
        ], Response::HTTP_CREATED);
    }

    public function destroy(Airline $airline): JsonResponse
    {
        return response()->json([
            'message' => 'Airline deleted successfully.'
        ]);
    }
}
