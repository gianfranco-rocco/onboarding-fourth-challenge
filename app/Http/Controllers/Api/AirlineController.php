<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyAirlineRequest;
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
            'message' => "Created airline 'ID {$airline->id}' successfully."
        ], Response::HTTP_CREATED);
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
            'message' => "Updated airline 'ID {$airline->id}' successfully."
        ]);
    }

    /**
     * 'DestroyAirlineRequest' is used for checking if deletion confirmation
     * is to be required
     */
    public function destroy(DestroyAirlineRequest $request, Airline $airline): JsonResponse
    {
        $airline->cities()->detach();

        $airline->flights()->delete();
        
        $airline->delete();

        return response()->json([
            'message' => "Deleted airline 'ID {$airline->id}' successfully."
        ]);
    }
}
