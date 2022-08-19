<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFlightRequest;
use App\Http\Requests\UpdateFlightRequest;
use App\Http\Resources\ShowFlightResource;
use App\Models\Flight;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class FlightController extends Controller
{
    public function store(StoreFlightRequest $request): JsonResponse
    {
        return response()->json([
            'message' => "Created flight 'ID {id}' successfully."
        ]);
    }

    public function show(Flight $flight): JsonResource
    {
        return ShowFlightResource::make($flight);
    }

    public function update(UpdateFlightRequest $request, Flight $flight): JsonResponse
    {
        return response()->json([
            'message' => "Updated flight 'ID {id}' successfully."
        ]);
    }

    public function destroy(Flight $flight): JsonResponse
    {
        return response()->json([
            'message' => "Deleted flight 'ID {id}' successfully."
        ]);
    }
}
