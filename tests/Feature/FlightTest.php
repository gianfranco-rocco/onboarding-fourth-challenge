<?php

namespace Tests\Feature;

use App\Models\Flight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FlightTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    private function getIndexJsonStructure(): array
    {
        return [
            'data' => [
                '*' => [
                    'id',
                    'airline_id',
                    'departure_city_id',
                    'destination_city_id',
                    'departure_at',
                    'arrival_at',
                    'airline' => [
                        'id',
                        'name'
                    ],
                    'departure_city' => [
                        'id',
                        'name'
                    ],
                    'destination_city' => [
                        'id',
                        'name'
                    ],
                ]
            ],
            'path',
            'per_page',
            'next_cursor',
            'next_page_url',
            'prev_cursor',
            'prev_page_url'
        ];
    }

    public function test_request_to_index_route_returns_view(): void
    {
        $response = $this->get(route('flights.index'));

        $response
            ->assertSuccessful()
            ->assertViewIs('flight.index');
    }

    public function test_request_to_api_index_route_returns_flights(): void
    {
        $response = $this->getJson(route('api.flights.index'));

        $response
            ->assertSuccessful()
            ->assertJsonStructure($this->getIndexJsonStructure());
    }

    public function test_request_to_api_index_route_filtered_by_airline_returns_flights_belonging_to_airline(): void
    {
        $flight = Flight::first();

        $response = $this->getJson(route('api.flights.index', [
            'airline' => $flight->airline_id
        ]));

        $flights = Flight::where('airline_id', $flight->airline_id)->orderBy('id', 'desc')->cursorPaginate(10);

        $response
            ->assertSuccessful()
            ->assertJsonStructure($this->getIndexJsonStructure())
            ->assertJson($flights->toArray());
    }

    public function test_request_to_api_index_route_filtered_by_departure_city_returns_flights_belonging_to_city(): void
    {
        $flight = Flight::first();

        $response = $this->getJson(route('api.flights.index', [
            'departure_city' => $flight->departure_city_id
        ]));

        $flights = Flight::where('departure_city_id', $flight->departure_city_id)
        ->orderBy('id', 'desc')
        ->cursorPaginate(10);

        $response
            ->assertSuccessful()
            ->assertJsonStructure($this->getIndexJsonStructure())
            ->assertJson($flights->toArray());
    }

    public function test_request_to_api_index_route_filtered_by_destination_city_returns_flights_belonging_to_city(): void
    {
        $flight = Flight::first();

        $response = $this->getJson(route('api.flights.index', [
            'destination_city' => $flight->destination_city_id
        ]));

        $flights = Flight::where('destination_city_id', $flight->destination_city_id)
        ->orderBy('id', 'desc')
        ->cursorPaginate(10);

        $response
            ->assertSuccessful()
            ->assertJsonStructure($this->getIndexJsonStructure())
            ->assertJson($flights->toArray());
    }

    public function test_request_to_api_index_route_filtered_by_departure_at_city_returns_flights_matching_the_date(): void
    {
        $flight = Flight::first();

        $response = $this->getJson(route('api.flights.index', [
            'departure_at' => $flight->departure_at->format('Y-m-d')
        ]));

        $flights = Flight::whereDate('departure_at', $flight->departure_at)
        ->orderBy('id', 'desc')
        ->cursorPaginate(10)
        ->appends(['departure_at' => $flight->departure_at->format('Y-m-d')]);

        $response
            ->assertSuccessful()
            ->assertJsonStructure($this->getIndexJsonStructure())
            ->assertJson($flights->toArray());
    }

    public function test_request_to_api_index_route_filtered_by_arrival_at_city_returns_flights_matching_the_date(): void
    {
        $flight = Flight::first();

        $response = $this->getJson(route('api.flights.index', [
            'arrival_at' => $flight->arrival_at->format('Y-m-d')
        ]));

        $flights = Flight::whereDate('arrival_at', $flight->arrival_at)
        ->orderBy('id', 'desc')
        ->cursorPaginate(10)
        ->appends(['arrival_at' => $flight->arrival_at->format('Y-m-d')]);

        $response
            ->assertSuccessful()
            ->assertJsonStructure($this->getIndexJsonStructure())
            ->assertJson($flights->toArray());
    }

    public function test_request_to_api_index_route_filtered_by_airline_and_departure_city_and_destination_city_and_departure_at_and_arrival_at_city_returns_flights_matching_the_filters(): void
    {
        $flight = Flight::first();

        $response = $this->getJson(route('api.flights.index', [
            'airline' => $flight->airline_id,
            'departure_city' => $flight->departure_city_id,
            'destination_city' => $flight->destination_city_id,
            'departure_at' => $flight->departure_at->format('Y-m-d'),
            'arrival_at' => $flight->arrival_at->format('Y-m-d')
        ]));

        $flights = Flight::whereDate('departure_at', $flight->departure_at)
        ->whereDate('arrival_at', $flight->arrival_at)
        ->where('airline_id', $flight->airline_id)
        ->where('departure_city_id', $flight->departure_city_id)
        ->where('destination_city_id', $flight->destination_city_id)
        ->orderBy('id', 'desc')
        ->cursorPaginate(10);

        $response
            ->assertSuccessful()
            ->assertJsonStructure($this->getIndexJsonStructure())
            ->assertJson($flights->toArray());
    }
}
