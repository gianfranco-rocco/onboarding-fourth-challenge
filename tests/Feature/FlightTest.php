<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\Flight;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
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

    private function getAirlineWithCities(int $minCitiesAmount = 2): Airline
    {
        return Airline::with('cities')
        ->whereHas('cities')
        ->withCount('cities')
        ->havingRaw("cities_count >= {$minCitiesAmount}")
        ->first();
    }

    private function getAirlineRequiredErrorMessage(): string
    {
        return 'The airline field is required.';
    }

    private function getDepartureCityRequiredErrorMessage(): string
    {
        return 'The departure city field is required.';
    }

    private function getDestinationCityRequiredErrorMessage(): string
    {
        return 'The destination city field is required.';
    }

    private function getDepartureAtDateRequiredErrorMessage(): string
    {
        return 'The departure date field is required.';
    }

    private function getDepartureAtTimeRequiredErrorMessage(): string
    {
        return 'The departure time field is required.';
    }

    private function getArrivalAtDateRequiredErrorMessage(): string
    {
        return 'The arrival date field is required.';
    }

    private function getArrivalAtTimeRequiredErrorMessage(): string
    {
        return 'The arrival time field is required.';
    }

    private function getAttributeDoesNotMatchFormat(string $attribute, string $format): string
    {
        return "The {$attribute} does not match the format {$format}.";
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

        $flights = Flight::where('airline_id', $flight->airline_id)
        ->orderBy('id', 'desc')
        ->cursorPaginate(10)
        ->appends([
            'airline' => $flight->airline_id
        ]);

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
        ->cursorPaginate(10)
        ->appends([
            'departure_city' => $flight->departure_city_id
        ]);

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
        ->cursorPaginate(10)
        ->appends([
            'destination_city' => $flight->destination_city_id
        ]);

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
        ->appends([
            'departure_at' => $flight->departure_at->format('Y-m-d')
        ]);

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
        ->appends([
            'arrival_at' => $flight->arrival_at->format('Y-m-d')
        ]);

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
        ->cursorPaginate(10)
        ->appends([
            'airline' => $flight->airline_id,
            'departure_city' => $flight->departure_city_id,
            'destination_city' => $flight->destination_city_id,
            'departure_at' => $flight->departure_at,
            'arrival_at' => $flight->arrival_at,
        ]);

        $response
            ->assertSuccessful()
            ->assertJsonStructure($this->getIndexJsonStructure())
            ->assertJson($flights->toArray());
    }

    public function test_request_to_api_store_route_creates_flight(): void
    {
        $airline = $this->getAirlineWithCities();

        $departureAt = CarbonImmutable::now();
        $arrivalAt = $departureAt->addDay();

        $departureCity = $airline->cities[0];
        $destinationCity = $airline->cities[1];
        
        $data = [
            'airline' => $airline->id,
            'departure_city' => $departureCity->id,
            'destination_city' => $destinationCity->id,
            'departure_at_date' => $departureAt->format('Y-m-d'),
            'departure_at_time' => $departureAt->format('H:i'),
            'arrival_at_date' => $arrivalAt->format('Y-m-d'),
            'arrival_at_time' => $arrivalAt->format('H:i'),
        ];

        $response = $this->postJson(route('api.flights.store', $data));

        $latestFlight = Flight::orderBy('id', 'desc')->first(['id']);

        $response
            ->assertCreated()
            ->assertJson([
                'message' => "Created flight 'ID {$latestFlight->id}' successfully."
            ]);

        $this->assertDatabaseHas('flights', [
            'airline_id' => $airline->id,
            'departure_city_id' => $departureCity->id,
            'destination_city_id' => $destinationCity->id,
            'departure_at' => $departureAt->format('Y-m-d H:i'),
            'arrival_at' => $arrivalAt->format('Y-m-d H:i'),
        ]);
    }

    public function test_request_to_api_store_route_without_airline_does_not_create_flight(): void
    {
        $airline = $this->getAirlineWithCities();

        $departureAt = CarbonImmutable::now();
        $arrivalAt = $departureAt->addDay();

        $departureCity = $airline->cities[0];
        $destinationCity = $airline->cities[1];
        
        $data = [
            'departure_city' => $departureCity->id,
            'destination_city' => $destinationCity->id,
            'departure_at_date' => $departureAt->format('Y-m-d'),
            'departure_at_time' => $departureAt->format('H:i'),
            'arrival_at_date' => $arrivalAt->format('Y-m-d'),
            'arrival_at_time' => $arrivalAt->format('H:i'),
        ];

        $flightsCountBeforeRequest = Flight::count();

        $response = $this->postJson(route('api.flights.store', $data));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'airline' => $this->getAirlineRequiredErrorMessage()
            ]);

        $this->assertDatabaseCount('flights', $flightsCountBeforeRequest);
    }

    public function test_request_to_api_store_route_without_airline_and_departure_city_does_not_create_flight(): void
    {
        $airline = $this->getAirlineWithCities();

        $departureAt = CarbonImmutable::now();
        $arrivalAt = $departureAt->addDay();

        $destinationCity = $airline->cities[1];
        
        $data = [
            'destination_city' => $destinationCity->id,
            'departure_at_date' => $departureAt->format('Y-m-d'),
            'departure_at_time' => $departureAt->format('H:i'),
            'arrival_at_date' => $arrivalAt->format('Y-m-d'),
            'arrival_at_time' => $arrivalAt->format('H:i'),
        ];

        $flightsCountBeforeRequest = Flight::count();

        $response = $this->postJson(route('api.flights.store', $data));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'airline' => $this->getAirlineRequiredErrorMessage(),
                'departure_city' => $this->getDepartureCityRequiredErrorMessage()
            ]);

        $this->assertDatabaseCount('flights', $flightsCountBeforeRequest);
    }

    public function test_request_to_api_store_route_without_airline_and_departure_city_and_destination_city_does_not_create_flight(): void
    {
        $departureAt = CarbonImmutable::now();
        $arrivalAt = $departureAt->addDay();
        
        $data = [
            'departure_at_date' => $departureAt->format('Y-m-d'),
            'departure_at_time' => $departureAt->format('H:i'),
            'arrival_at_date' => $arrivalAt->format('Y-m-d'),
            'arrival_at_time' => $arrivalAt->format('H:i'),
        ];

        $flightsCountBeforeRequest = Flight::count();

        $response = $this->postJson(route('api.flights.store', $data));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'airline' => $this->getAirlineRequiredErrorMessage(),
                'departure_city' => $this->getDepartureCityRequiredErrorMessage(),
                'destination_city' => $this->getDestinationCityRequiredErrorMessage()
            ]);

        $this->assertDatabaseCount('flights', $flightsCountBeforeRequest);
    }

    public function test_request_to_api_store_route_without_airline_and_departure_city_and_destination_city_and_departure_at_date_does_not_create_flight(): void
    {
        $departureAt = CarbonImmutable::now();
        $arrivalAt = $departureAt->addDay();
        
        $data = [
            'departure_at_time' => $departureAt->format('H:i'),
            'arrival_at_date' => $arrivalAt->format('Y-m-d'),
            'arrival_at_time' => $arrivalAt->format('H:i'),
        ];

        $flightsCountBeforeRequest = Flight::count();

        $response = $this->postJson(route('api.flights.store', $data));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'airline' => $this->getAirlineRequiredErrorMessage(),
                'departure_city' => $this->getDepartureCityRequiredErrorMessage(),
                'destination_city' => $this->getDestinationCityRequiredErrorMessage(),
                'departure_at_date' => $this->getDepartureAtDateRequiredErrorMessage()
            ]);

        $this->assertDatabaseCount('flights', $flightsCountBeforeRequest);
    }

    public function test_request_to_api_store_route_without_airline_and_departure_city_and_destination_city_and_departure_at_date_and_departure_at_time_does_not_create_flight(): void
    {
        $arrivalAt = now()->addDay();
        
        $data = [
            'arrival_at_date' => $arrivalAt->format('Y-m-d'),
            'arrival_at_time' => $arrivalAt->format('H:i'),
        ];

        $flightsCountBeforeRequest = Flight::count();

        $response = $this->postJson(route('api.flights.store', $data));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'airline' => $this->getAirlineRequiredErrorMessage(),
                'departure_city' => $this->getDepartureCityRequiredErrorMessage(),
                'destination_city' => $this->getDestinationCityRequiredErrorMessage(),
                'departure_at_date' => $this->getDepartureAtDateRequiredErrorMessage(),
                'departure_at_time' => $this->getDepartureAtTimeRequiredErrorMessage(),
            ]);

        $this->assertDatabaseCount('flights', $flightsCountBeforeRequest);
    }

    public function test_request_to_api_store_route_without_airline_and_departure_city_and_destination_city_and_departure_at_date_and_departure_at_time_and_arrival_at_date_does_not_create_flight(): void
    {
        $arrivalAt = now()->addDay();
        
        $data = [
            'arrival_at_time' => $arrivalAt->format('H:i'),
        ];

        $flightsCountBeforeRequest = Flight::count();

        $response = $this->postJson(route('api.flights.store', $data));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'airline' => $this->getAirlineRequiredErrorMessage(),
                'departure_city' => $this->getDepartureCityRequiredErrorMessage(),
                'destination_city' => $this->getDestinationCityRequiredErrorMessage(),
                'departure_at_date' => $this->getDepartureAtDateRequiredErrorMessage(),
                'departure_at_time' => $this->getDepartureAtTimeRequiredErrorMessage(),
                'arrival_at_date' => $this->getArrivalAtDateRequiredErrorMessage(),
            ]);

        $this->assertDatabaseCount('flights', $flightsCountBeforeRequest);
    }

    public function test_request_to_api_store_route_without_data_does_not_create_flight(): void
    {
        $flightsCountBeforeRequest = Flight::count();

        $response = $this->postJson(route('api.flights.store'));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'airline' => $this->getAirlineRequiredErrorMessage(),
                'departure_city' => $this->getDepartureCityRequiredErrorMessage(),
                'destination_city' => $this->getDestinationCityRequiredErrorMessage(),
                'departure_at_date' => $this->getDepartureAtDateRequiredErrorMessage(),
                'departure_at_time' => $this->getDepartureAtTimeRequiredErrorMessage(),
                'arrival_at_date' => $this->getArrivalAtDateRequiredErrorMessage(),
                'arrival_at_time' => $this->getArrivalAtTimeRequiredErrorMessage(),
            ]);

        $this->assertDatabaseCount('flights', $flightsCountBeforeRequest);
    }

    public function test_request_to_api_store_route_with_non_existent_airline_does_not_create_flight(): void
    {
        $airline = $this->getAirlineWithCities();

        $departureAt = CarbonImmutable::now();
        $arrivalAt = $departureAt->addDay();

        $departureCity = $airline->cities[0];
        $destinationCity = $airline->cities[1];
        
        $data = [
            'airline' => 9999999999999,
            'departure_city' => $departureCity->id,
            'destination_city' => $destinationCity->id,
            'departure_at_date' => $departureAt->format('Y-m-d'),
            'departure_at_time' => $departureAt->format('H:i'),
            'arrival_at_date' => $arrivalAt->format('Y-m-d'),
            'arrival_at_time' => $arrivalAt->format('H:i'),
        ];

        $flightsCountBeforeRequest = Flight::count();

        $response = $this->postJson(route('api.flights.store', $data));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'airline' => 'The selected airline is invalid.'
            ]);

        $this->assertDatabaseCount('flights', $flightsCountBeforeRequest);
    }

    public function test_request_to_api_store_route_with_departure_city_not_assigned_to_airline_does_not_create_flight(): void
    {
        $airline = $this->getAirlineWithCities();

        $departureAt = CarbonImmutable::now();
        $arrivalAt = $departureAt->addDay();

        $destinationCity = $airline->cities[1];

        $departureCity = DB::table('airline_city')->where('airline_id', '<>', $airline->id)->first();

        $data = [
            'airline' => $airline->id,
            'departure_city' => $departureCity->city_id,
            'destination_city' => $destinationCity->id,
            'departure_at_date' => $departureAt->format('Y-m-d'),
            'departure_at_time' => $departureAt->format('H:i'),
            'arrival_at_date' => $arrivalAt->format('Y-m-d'),
            'arrival_at_time' => $arrivalAt->format('H:i'),
        ];

        $flightsCountBeforeRequest = Flight::count();

        $response = $this->postJson(route('api.flights.store', $data));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'departure_city' => 'The selected departure city is invalid.'
            ]);

        $this->assertDatabaseCount('flights', $flightsCountBeforeRequest);
    }

    public function test_request_to_api_store_route_with_destination_city_not_assigned_to_airline_does_not_create_flight(): void
    {
        $airline = $this->getAirlineWithCities();

        $departureAt = CarbonImmutable::now();
        $arrivalAt = $departureAt->addDay();

        $departureCity = $airline->cities[0];

        $destinationCity = DB::table('airline_city')->where('airline_id', '<>', $airline->id)->first();

        $data = [
            'airline' => $airline->id,
            'departure_city' => $departureCity->id,
            'destination_city' => $destinationCity->city_id,
            'departure_at_date' => $departureAt->format('Y-m-d'),
            'departure_at_time' => $departureAt->format('H:i'),
            'arrival_at_date' => $arrivalAt->format('Y-m-d'),
            'arrival_at_time' => $arrivalAt->format('H:i'),
        ];

        $flightsCountBeforeRequest = Flight::count();

        $response = $this->postJson(route('api.flights.store', $data));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'destination_city' => 'The selected destination city is invalid.'
            ]);

        $this->assertDatabaseCount('flights', $flightsCountBeforeRequest);
    }

    public function test_request_to_api_store_route_with_same_departure_city_as_destination_city_does_not_create_flight(): void
    {
        $airline = $this->getAirlineWithCities();

        $departureAt = CarbonImmutable::now();
        $arrivalAt = $departureAt->addDay();

        $departureCity = $airline->cities[0];
        
        $data = [
            'airline' => $airline->id,
            'departure_city' => $departureCity->id,
            'destination_city' => $departureCity->id,
            'departure_at_date' => $departureAt->format('Y-m-d'),
            'departure_at_time' => $departureAt->format('H:i'),
            'arrival_at_date' => $arrivalAt->format('Y-m-d'),
            'arrival_at_time' => $arrivalAt->format('H:i'),
        ];

        $flightsCountBeforeRequest = Flight::count();

        $response = $this->postJson(route('api.flights.store', $data));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'departure_city' => 'The departure city and destination city must be different.',
                'destination_city' => 'The destination city and departure city must be different.',
            ]);

        $this->assertDatabaseCount('flights', $flightsCountBeforeRequest);
    }

    public function test_request_to_api_store_route_with_departure_at_date_in_wrong_format_does_not_create_flight(): void
    {
        $airline = $this->getAirlineWithCities();

        $departureAt = CarbonImmutable::now();
        $arrivalAt = $departureAt->addDay();

        $departureCity = $airline->cities[0];
        $destinationCity = $airline->cities[1];
        
        $data = [
            'airline' => $airline->id,
            'departure_city' => $departureCity->id,
            'destination_city' => $destinationCity->id,
            'departure_at_date' => $departureAt->format('d/m/Y'),
            'departure_at_time' => $departureAt->format('H:i'),
            'arrival_at_date' => $arrivalAt->format('Y-m-d'),
            'arrival_at_time' => $arrivalAt->format('H:i'),
        ];

        $flightsCountBeforeRequest = Flight::count();

        $response = $this->postJson(route('api.flights.store', $data));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'departure_at_date' => $this->getAttributeDoesNotMatchFormat('departure date', 'Y-m-d')
            ]);

        $this->assertDatabaseCount('flights', $flightsCountBeforeRequest);
    }

    public function test_request_to_api_store_route_with_departure_at_date_before_today_does_not_create_flight(): void
    {
        $airline = $this->getAirlineWithCities();

        $departureAt = now()->subDay();
        $arrivalAt = now()->addDay();

        $departureCity = $airline->cities[0];
        $destinationCity = $airline->cities[1];
        
        $data = [
            'airline' => $airline->id,
            'departure_city' => $departureCity->id,
            'destination_city' => $destinationCity->id,
            'departure_at_date' => $departureAt->format('Y-m-d'),
            'departure_at_time' => $departureAt->format('H:i'),
            'arrival_at_date' => $arrivalAt->format('Y-m-d'),
            'arrival_at_time' => $arrivalAt->format('H:i'),
        ];

        $flightsCountBeforeRequest = Flight::count();

        $response = $this->postJson(route('api.flights.store', $data));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'departure_at_date' => 'The departure date must be a date after or equal to today.'
            ]);

        $this->assertDatabaseCount('flights', $flightsCountBeforeRequest);
    }

    public function test_request_to_api_store_route_with_departure_at_time_in_wrong_format_does_not_create_flight(): void
    {
        $airline = $this->getAirlineWithCities();

        $departureAt = CarbonImmutable::now();
        $arrivalAt = $departureAt->addDay();

        $departureCity = $airline->cities[0];
        $destinationCity = $airline->cities[1];
        
        $data = [
            'airline' => $airline->id,
            'departure_city' => $departureCity->id,
            'destination_city' => $destinationCity->id,
            'departure_at_date' => $departureAt->format('Y-m-d'),
            'departure_at_time' => $departureAt->format('H:i:s'),
            'arrival_at_date' => $arrivalAt->format('Y-m-d'),
            'arrival_at_time' => $arrivalAt->format('H:i'),
        ];

        $flightsCountBeforeRequest = Flight::count();

        $response = $this->postJson(route('api.flights.store', $data));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'departure_at_time' => $this->getAttributeDoesNotMatchFormat('departure time', 'H:i')
            ]);

        $this->assertDatabaseCount('flights', $flightsCountBeforeRequest);
    }

    public function test_request_to_api_store_route_with_arrival_at_date_in_wrong_format_does_not_create_flight(): void
    {
        $airline = $this->getAirlineWithCities();

        $departureAt = CarbonImmutable::now();
        $arrivalAt = $departureAt->addDay();

        $departureCity = $airline->cities[0];
        $destinationCity = $airline->cities[1];
        
        $data = [
            'airline' => $airline->id,
            'departure_city' => $departureCity->id,
            'destination_city' => $destinationCity->id,
            'departure_at_date' => $departureAt->format('Y-m-d'),
            'departure_at_time' => $departureAt->format('H:i'),
            'arrival_at_date' => $arrivalAt->format('d/m/Y'),
            'arrival_at_time' => $arrivalAt->format('H:i'),
        ];

        $flightsCountBeforeRequest = Flight::count();

        $response = $this->postJson(route('api.flights.store', $data));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'arrival_at_date' => $this->getAttributeDoesNotMatchFormat('arrival date', 'Y-m-d')
            ]);

        $this->assertDatabaseCount('flights', $flightsCountBeforeRequest);
    }

    public function test_request_to_api_store_route_with_arrival_at_date_before_departure_at_date_does_not_create_flight(): void
    {
        $airline = $this->getAirlineWithCities();

        $departureAt = CarbonImmutable::now();
        $arrivalAt = $departureAt->subDay();

        $departureCity = $airline->cities[0];
        $destinationCity = $airline->cities[1];
        
        $data = [
            'airline' => $airline->id,
            'departure_city' => $departureCity->id,
            'destination_city' => $destinationCity->id,
            'departure_at_date' => $departureAt->format('Y-m-d'),
            'departure_at_time' => $departureAt->format('H:i'),
            'arrival_at_date' => $arrivalAt->format('Y-m-d'),
            'arrival_at_time' => $arrivalAt->format('H:i'),
        ];

        $flightsCountBeforeRequest = Flight::count();

        $response = $this->postJson(route('api.flights.store', $data));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'arrival_at_date' => 'The arrival date must be a date after or equal to departure date.'
            ]);

        $this->assertDatabaseCount('flights', $flightsCountBeforeRequest);
    }

    public function test_request_to_api_store_route_with_arrival_at_time_in_wrong_format_does_not_create_flight(): void
    {
        $airline = $this->getAirlineWithCities();

        $departureAt = CarbonImmutable::now();
        $arrivalAt = $departureAt->addDay();

        $departureCity = $airline->cities[0];
        $destinationCity = $airline->cities[1];
        
        $data = [
            'airline' => $airline->id,
            'departure_city' => $departureCity->id,
            'destination_city' => $destinationCity->id,
            'departure_at_date' => $departureAt->format('Y-m-d'),
            'departure_at_time' => $departureAt->format('H:i'),
            'arrival_at_date' => $arrivalAt->format('Y-m-d'),
            'arrival_at_time' => $arrivalAt->format('H:i:s'),
        ];

        $flightsCountBeforeRequest = Flight::count();

        $response = $this->postJson(route('api.flights.store', $data));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'arrival_at_time' => $this->getAttributeDoesNotMatchFormat('arrival time', 'H:i')
            ]);

        $this->assertDatabaseCount('flights', $flightsCountBeforeRequest);
    }

    public function test_request_to_api_store_route_with_same_departure_at_and_arrival_at_does_not_create_flight(): void
    {
        $airline = $this->getAirlineWithCities();

        $now = CarbonImmutable::now();

        $departureCity = $airline->cities[0];
        $destinationCity = $airline->cities[1];
        
        $data = [
            'airline' => $airline->id,
            'departure_city' => $departureCity->id,
            'destination_city' => $destinationCity->id,
            'departure_at_date' => $now->format('Y-m-d'),
            'departure_at_time' => $now->format('H:i'),
            'arrival_at_date' => $now->format('Y-m-d'),
            'arrival_at_time' => $now->format('H:i'),
        ];

        $flightsCountBeforeRequest = Flight::count();

        $response = $this->postJson(route('api.flights.store', $data));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'arrival_at_time' => 'The arrival time must be a time after the departure time when both the departure date and arrival date are the same.'
            ]);

        $this->assertDatabaseCount('flights', $flightsCountBeforeRequest);
    }
}
