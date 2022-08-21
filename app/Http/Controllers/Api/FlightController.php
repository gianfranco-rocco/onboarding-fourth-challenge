<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvokeFlightRequest;
use App\Http\Requests\StoreFlightRequest;
use App\Http\Requests\UpdateFlightRequest;
use App\Http\Resources\ShowFlightResource;
use App\Models\Flight;
use App\Services\FlightService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\CursorPaginator;

class FlightController extends Controller
{
    public function index(InvokeFlightRequest $request, FlightService $flightService): CursorPaginator
    {
        return $flightService->getCursorPaginated(
            10, 
            $request->get('departure_at'), 
            $request->get('arrival_at'), 
            $request->get('airline'), 
            $request->get('departure_city'),
            $request->get('destination_city')
        );
    }

    public function store(StoreFlightRequest $request): JsonResponse
    {
        $flight = Flight::create([
            'airline_id' => $request->airline,
            'departure_city_id' => $request->departure_city,
            'destination_city_id' => $request->destination_city,
            'departure_at' => $request->departure_at,
            'arrival_at' => $request->arrival_at,
        ]);

        return response()->json([
            'message' => "Created flight 'ID {$flight->id}' successfully."
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
