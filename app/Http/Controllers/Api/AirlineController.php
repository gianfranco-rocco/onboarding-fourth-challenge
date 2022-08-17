<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAirlineRequest;
use App\Http\Requests\UpdateAirlineRequest;
use App\Models\Airline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AirlineController extends Controller
{
    public function store(StoreAirlineRequest $request): JsonResponse
    {
        return response()->json([
            'message' => 'Airline created successfully.'
        ]);
    }

    public function show(Airline $airline): Airline
    {
        return $airline;
    }

    public function update(UpdateAirlineRequest $request, Airline $airline): JsonResponse
    {
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
