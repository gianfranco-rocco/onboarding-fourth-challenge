<?php

namespace Tests\Feature;

use App\Http\Resources\ShowFlightResource;
use App\Models\Airline;
use App\Models\City;
use App\Models\Flight;
use App\Services\FlightService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FlightTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private FlightService $flightService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->flightService = new FlightService();
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

    private function getCityNotAssignedToArline(Airline $airline): ?City
    {
        return City::whereDoesntHave('airlines')
        ->orWhereHas('airlines', function ($query) use ($airline) {
            return $query->where('airline_id', '<>', $airline->id);
        })
        ->first();
    }

    private function getDepartureCityDifferentThanCurrent(Airline $airline, Flight $flight): City
    {
        return $airline->cities->first(function ($city) use ($flight) {
            return $city->id != $flight->departure_city_id;
        });
    }

    private function getDestinationCityDifferentThanCurrent(Airline $airline, Flight $flight, ?City $departureCity = null): City
    {
        return $airline->cities->first(function ($city) use ($flight, $departureCity) {
            $passes = $city->id != $flight->destination_city_id;

            if ($departureCity) {
                $passes = $city->id != $departureCity->id;
            }

            return $passes;
        });
    }

    private function getDepartureAtDifferentThanCurrent(Flight $flight): CarbonImmutable
    {
        $now = Carbon::now();

        do {
            $newDepartureAt = $flight->departure_at->addDays(rand(1, 10));
        } while($newDepartureAt->eq($now));


        return CarbonImmutable::createFromMutable($newDepartureAt);
    }

    private function getArrivalAtDifferentThanCurrent(CarbonImmutable $departureAt): CarbonImmutable
    {
        return $departureAt->addDays(rand(1, 5));
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

    private function getSelectedAttributeInvalidErrorMessage(string $attribute): string
    {
        return "The selected {$attribute} is invalid.";
    }

    private function getAttributesMustBeDifferentErrorMessage(string $attribute1, string $attribute2): string
    {
        return "The {$attribute1} and {$attribute2} must be different.";
    }

    private function getAttributeMustBeADateErrorMessage(string $attribute, string $condition, string $dataComparedAgainst): string
    {
        return "The {$attribute} must be a date {$condition} {$dataComparedAgainst}.";
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

        $flights = $this->flightService
        ->getCursorPaginated(
            airline: $flight->airline_id
        );

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

        $flights = $this->flightService
        ->getCursorPaginated(
            departureCity: $flight->departure_city_id
        );

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

        $flights = $this->flightService
        ->getCursorPaginated(
            destinationCity: $flight->destination_city_id
        );

        $response
            ->assertSuccessful()
            ->assertJsonStructure($this->getIndexJsonStructure())
            ->assertJson($flights->toArray());
    }

    public function test_request_to_api_index_route_filtered_by_departure_at_returns_flights_matching_the_date(): void
    {
        $flight = Flight::first();

        $response = $this->getJson(route('api.flights.index', [
            'departure_at' => $flight->departure_at->format('Y-m-d')
        ]));

        $flights = $this->flightService
        ->getCursorPaginated(
            departureAt: $flight->departure_at->format('Y-m-d')
        );

        $response
            ->assertSuccessful()
            ->assertJsonStructure($this->getIndexJsonStructure())
            ->assertJson($flights->toArray());
    }

    public function test_request_to_api_index_route_filtered_by_arrival_at_returns_flights_matching_the_date(): void
    {
        $flight = Flight::first();

        $response = $this->getJson(route('api.flights.index', [
            'arrival_at' => $flight->arrival_at->format('Y-m-d')
        ]));

        $flights = $this->flightService
        ->getCursorPaginated(
            arrivalAt: $flight->arrival_at->format('Y-m-d')
        );

        $response
            ->assertSuccessful()
            ->assertJsonStructure($this->getIndexJsonStructure())
            ->assertJson($flights->toArray());
    }

    public function test_request_to_api_index_route_filtered_by_airline_and_departure_city_and_destination_city_and_departure_at_and_arrival_at_returns_flights_matching_the_filters(): void
    {
        $flight = Flight::first();

        $departureAt = $flight->departure_at->format('Y-m-d');
        $arrivalAt = $flight->arrival_at->format('Y-m-d');

        $response = $this->getJson(route('api.flights.index', [
            'airline' => $flight->airline_id,
            'departure_city' => $flight->departure_city_id,
            'destination_city' => $flight->destination_city_id,
            'departure_at' => $departureAt,
            'arrival_at' => $arrivalAt
        ]));

        $flights = $this->flightService
        ->getCursorPaginated(
            10,
            $departureAt,
            $arrivalAt,
            $flight->airline_id,
            $flight->departure_city_id,
            $flight->destination_city_id
        );

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
                'airline' => $this->getSelectedAttributeInvalidErrorMessage('airline')
            ]);

        $this->assertDatabaseCount('flights', $flightsCountBeforeRequest);
    }

    public function test_request_to_api_store_route_with_departure_city_not_assigned_to_airline_does_not_create_flight(): void
    {
        $airline = $this->getAirlineWithCities();

        $departureAt = CarbonImmutable::now();
        $arrivalAt = $departureAt->addDay();

        $departureCity = $this->getCityNotAssignedToArline($airline);
        $destinationCity = $airline->cities[1];

        $this->assertNotEmpty($departureCity);

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
                'departure_city' => $this->getSelectedAttributeInvalidErrorMessage('departure city')
            ]);

        $this->assertDatabaseCount('flights', $flightsCountBeforeRequest);
    }

    public function test_request_to_api_store_route_with_destination_city_not_assigned_to_airline_does_not_create_flight(): void
    {
        $airline = $this->getAirlineWithCities();

        $departureAt = CarbonImmutable::now();
        $arrivalAt = $departureAt->addDay();

        $departureCity = $airline->cities[0];
        $destinationCity = $this->getCityNotAssignedToArline($airline);

        $this->assertNotEmpty($destinationCity);

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
                'destination_city' => $this->getSelectedAttributeInvalidErrorMessage('destination city')
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
                'departure_city' => $this->getAttributesMustBeDifferentErrorMessage('departure city', 'destination city'),
                'destination_city' => $this->getAttributesMustBeDifferentErrorMessage('destination city', 'departure city'),
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
                'departure_at_date' => $this->getAttributeMustBeADateErrorMessage('departure date', 'after or equal to', 'today')
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
                'arrival_at_date' => $this->getAttributeMustBeADateErrorMessage('arrival date', 'after or equal to', 'departure date')
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

    public function test_request_to_api_show_route_returns_resource(): void
    {
        $flight = Flight::first();

        $flightResource = ShowFlightResource::make($flight);

        $resourceAsArray = $flightResource->toResponse(app('request'))->getData(true);

        $response = $this->getJson(route('api.flights.show', $flight));

        $response
            ->assertSuccessful()
            ->assertJson($resourceAsArray);
    }

    public function test_request_to_api_show_route_with_non_existent_flight_returns_404(): void
    {
        $response = $this->getJson(route('api.flights.show', 999999999));

        $response->assertNotFound();
    }

    public function test_request_to_api_update_route_updates_flight(): void
    {
        $flight = Flight::first();

        $newAirline = $this->getAirlineWithCities(7);

        $newDepartureCity = $this->getDepartureCityDifferentThanCurrent($newAirline, $flight);
        $newDestinationCity = $this->getDestinationCityDifferentThanCurrent($newAirline, $flight, $newDepartureCity);

        $newDepartureAt = $this->getDepartureAtDifferentThanCurrent($flight);
        $newArrivalAt = $this->getArrivalAtDifferentThanCurrent($newDepartureAt);

        $newData = [
            'airline' => $newAirline->id,
            'departure_city' => $newDepartureCity->id,
            'destination_city' => $newDestinationCity->id,
            'departure_at_date' => $newDepartureAt->format('Y-m-d'),
            'departure_at_time' => $newDepartureAt->format('H:i'),
            'arrival_at_date' => $newArrivalAt->format('Y-m-d'),
            'arrival_at_time' => $newArrivalAt->format('H:i'),
        ];

        $response = $this->putJson(route('api.flights.update', $flight), $newData);

        $response
            ->assertSuccessful()
            ->assertJson([
                'message' => "Updated flight 'ID {$flight->id}' successfully."
            ]);

        $this
            ->assertDatabaseMissing('flights', $flight->toArray())
            ->assertDatabaseHas('flights', [
                'id' => $flight->id,
                'airline_id' => $newAirline->id,
                'departure_city_id' => $newDepartureCity->id,
                'destination_city_id' => $newDestinationCity->id,
                'departure_at' => $newDepartureAt->format('Y-m-d H:i'),
                'arrival_at' => $newArrivalAt->format('Y-m-d H:i'),
            ]);
    }

    public function test_request_to_api_update_route_without_airline_does_not_update_flight(): void
    {
        $flight = Flight::first();

        $newAirline = $this->getAirlineWithCities(7);

        $newDepartureCity = $this->getDepartureCityDifferentThanCurrent($newAirline, $flight);
        $newDestinationCity = $this->getDestinationCityDifferentThanCurrent($newAirline, $flight, $newDepartureCity);

        $newDepartureAt = $this->getDepartureAtDifferentThanCurrent($flight);
        $newArrivalAt = $this->getArrivalAtDifferentThanCurrent($newDepartureAt);

        $newData = [
            'departure_city' => $newDepartureCity->id,
            'destination_city' => $newDestinationCity->id,
            'departure_at_date' => $newDepartureAt->format('Y-m-d'),
            'departure_at_time' => $newDepartureAt->format('H:i'),
            'arrival_at_date' => $newArrivalAt->format('Y-m-d'),
            'arrival_at_time' => $newArrivalAt->format('H:i'),
        ];

        $response = $this->putJson(route('api.flights.update', $flight), $newData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'airline' => $this->getAirlineRequiredErrorMessage()
            ]);

        $this
            ->assertDatabaseHas('flights', $flight->toArray())
            ->assertDatabaseMissing('flights', [
                'id' => $flight->id,
                'airline_id' => $flight->airline_id,
                'departure_city_id' => $newDepartureCity->id,
                'destination_city_id' => $newDestinationCity->id,
                'departure_at' => $newDepartureAt->format('Y-m-d H:i'),
                'arrival_at' => $newArrivalAt->format('Y-m-d H:i'),
            ]);
    }

    public function test_request_to_api_update_route_without_airline_and_departure_city_does_not_update_flight(): void
    {
        $flight = Flight::first();

        $newAirline = $this->getAirlineWithCities(7);

        $newDestinationCity = $this->getDestinationCityDifferentThanCurrent($newAirline, $flight);

        $newDepartureAt = $this->getDepartureAtDifferentThanCurrent($flight);
        $newArrivalAt = $this->getArrivalAtDifferentThanCurrent($newDepartureAt);

        $newData = [
            'destination_city' => $newDestinationCity->id,
            'departure_at_date' => $newDepartureAt->format('Y-m-d'),
            'departure_at_time' => $newDepartureAt->format('H:i'),
            'arrival_at_date' => $newArrivalAt->format('Y-m-d'),
            'arrival_at_time' => $newArrivalAt->format('H:i'),
        ];

        $response = $this->putJson(route('api.flights.update', $flight), $newData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'airline' => $this->getAirlineRequiredErrorMessage(),
                'departure_city' => $this->getDepartureCityRequiredErrorMessage()
            ]);

        $this
            ->assertDatabaseHas('flights', $flight->toArray())
            ->assertDatabaseMissing('flights', [
                'id' => $flight->id,
                'airline_id' => $flight->airline_id,
                'departure_city_id' => $flight->departure_city_id,
                'destination_city_id' => $newDestinationCity->id,
                'departure_at' => $newDepartureAt->format('Y-m-d H:i'),
                'arrival_at' => $newArrivalAt->format('Y-m-d H:i'),
            ]);
    }

    public function test_request_to_api_update_route_without_airline_and_departure_city_and_destination_city_does_not_update_flight(): void
    {
        $flight = Flight::first();

        $newDepartureAt = $this->getDepartureAtDifferentThanCurrent($flight);
        $newArrivalAt = $this->getArrivalAtDifferentThanCurrent($newDepartureAt);

        $newData = [
            'departure_at_date' => $newDepartureAt->format('Y-m-d'),
            'departure_at_time' => $newDepartureAt->format('H:i'),
            'arrival_at_date' => $newArrivalAt->format('Y-m-d'),
            'arrival_at_time' => $newArrivalAt->format('H:i'),
        ];

        $response = $this->putJson(route('api.flights.update', $flight), $newData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'airline' => $this->getAirlineRequiredErrorMessage(),
                'departure_city' => $this->getDepartureCityRequiredErrorMessage(),
                'destination_city' => $this->getDestinationCityRequiredErrorMessage(),
            ]);

        $this
            ->assertDatabaseHas('flights', $flight->toArray())
            ->assertDatabaseMissing('flights', [
                'id' => $flight->id,
                'airline_id' => $flight->airline_id,
                'departure_city_id' => $flight->departure_city_id,
                'destination_city_id' => $flight->destination_city_id,
                'departure_at' => $newDepartureAt->format('Y-m-d H:i'),
                'arrival_at' => $newArrivalAt->format('Y-m-d H:i'),
            ]);
    }

    public function test_request_to_api_update_route_without_airline_and_departure_city_and_destination_city_and_departure_at_date_does_not_update_flight(): void
    {
        $flight = Flight::first();

        $newDepartureAt = $this->getDepartureAtDifferentThanCurrent($flight);
        $newArrivalAt = $this->getArrivalAtDifferentThanCurrent($newDepartureAt);

        $newData = [
            'departure_at_time' => $newDepartureAt->format('H:i'),
            'arrival_at_date' => $newArrivalAt->format('Y-m-d'),
            'arrival_at_time' => $newArrivalAt->format('H:i'),
        ];

        $response = $this->putJson(route('api.flights.update', $flight), $newData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'airline' => $this->getAirlineRequiredErrorMessage(),
                'departure_city' => $this->getDepartureCityRequiredErrorMessage(),
                'destination_city' => $this->getDestinationCityRequiredErrorMessage(),
                'departure_at_date' => $this->getDepartureAtDateRequiredErrorMessage()
            ]);

        $this
            ->assertDatabaseHas('flights', $flight->toArray())
            ->assertDatabaseMissing('flights', [
                'id' => $flight->id,
                'airline_id' => $flight->airline_id,
                'departure_city_id' => $flight->departure_city_id,
                'destination_city_id' => $flight->destination_city_id,
                'departure_at' => $flight->departure_at->format('Y-m-d') . " " . $newDepartureAt->format('H:i'),
                'arrival_at' => $newArrivalAt->format('Y-m-d H:i'),
            ]);
    }

    public function test_request_to_api_update_route_without_airline_and_departure_city_and_destination_city_and_departure_at_date_and_departure_at_time_does_not_update_flight(): void
    {
        $flight = Flight::first();

        $newDepartureAt = $this->getDepartureAtDifferentThanCurrent($flight);
        $newArrivalAt = $this->getArrivalAtDifferentThanCurrent($newDepartureAt);

        $newData = [
            'arrival_at_date' => $newArrivalAt->format('Y-m-d'),
            'arrival_at_time' => $newArrivalAt->format('H:i'),
        ];

        $response = $this->putJson(route('api.flights.update', $flight), $newData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'airline' => $this->getAirlineRequiredErrorMessage(),
                'departure_city' => $this->getDepartureCityRequiredErrorMessage(),
                'destination_city' => $this->getDestinationCityRequiredErrorMessage(),
                'departure_at_date' => $this->getDepartureAtDateRequiredErrorMessage(),
                'departure_at_time' => $this->getDepartureAtTimeRequiredErrorMessage(),
            ]);

        $this
            ->assertDatabaseHas('flights', $flight->toArray())
            ->assertDatabaseMissing('flights', [
                'id' => $flight->id,
                'airline_id' => $flight->airline_id,
                'departure_city_id' => $flight->departure_city_id,
                'destination_city_id' => $flight->destination_city_id,
                'departure_at' => $flight->departure_at,
                'arrival_at' => $newArrivalAt->format('Y-m-d H:i'),
            ]);
    }

    public function test_request_to_api_update_route_without_airline_and_departure_city_and_destination_city_and_departure_at_date_and_departure_at_time_and_arrival_at_date_does_not_update_flight(): void
    {
        $flight = Flight::first();

        $newDepartureAt = $this->getDepartureAtDifferentThanCurrent($flight);
        $newArrivalAt = $this->getArrivalAtDifferentThanCurrent($newDepartureAt);

        $newData = [
            'arrival_at_time' => $newArrivalAt->format('H:i'),
        ];

        $response = $this->putJson(route('api.flights.update', $flight), $newData);

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

        $this
            ->assertDatabaseHas('flights', $flight->toArray())
            ->assertDatabaseMissing('flights', [
                'id' => $flight->id,
                'airline_id' => $flight->airline_id,
                'departure_city_id' => $flight->departure_city_id,
                'destination_city_id' => $flight->destination_city_id,
                'departure_at' => $flight->departure_at,
                'arrival_at' => $flight->arrival_at->format('Y-m-d') . " " . $newArrivalAt->format('H:i'),
            ]);
    }

    public function test_request_to_api_update_route_without_data_does_not_update_flight(): void
    {
        $flight = Flight::first();

        $response = $this->putJson(route('api.flights.update', $flight));

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

        $this->assertDatabaseHas('flights', $flight->toArray());
    }

    public function test_request_to_api_update_route_with_non_existent_airline_does_not_update_flight(): void
    {
        $flight = Flight::first();

        $newAirline = $this->getAirlineWithCities(7);

        $newDepartureCity = $this->getDepartureCityDifferentThanCurrent($newAirline, $flight);
        $newDestinationCity = $this->getDestinationCityDifferentThanCurrent($newAirline, $flight, $newDepartureCity);

        $newDepartureAt = $this->getDepartureAtDifferentThanCurrent($flight);
        $newArrivalAt = $this->getArrivalAtDifferentThanCurrent($newDepartureAt);

        $invalidAirlineId = 99999999;

        $newData = [
            'airline' => $invalidAirlineId,
            'departure_city' => $newDepartureCity->id,
            'destination_city' => $newDestinationCity->id,
            'departure_at_date' => $newDepartureAt->format('Y-m-d'),
            'departure_at_time' => $newDepartureAt->format('H:i'),
            'arrival_at_date' => $newArrivalAt->format('Y-m-d'),
            'arrival_at_time' => $newArrivalAt->format('H:i'),
        ];

        $response = $this->putJson(route('api.flights.update', $flight), $newData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'airline' => $this->getSelectedAttributeInvalidErrorMessage('airline')
            ]);

        $this
            ->assertDatabaseHas('flights', $flight->toArray())
            ->assertDatabaseMissing('flights', [
                'id' => $flight->id,
                'airline_id' => $invalidAirlineId,
                'departure_city_id' => $newDepartureCity->id,
                'destination_city_id' => $newDestinationCity->id,
                'departure_at' => $newDepartureAt->format('Y-m-d H:i'),
                'arrival_at' => $newArrivalAt->format('Y-m-d H:i'),
            ]);
    }

    public function test_request_to_api_update_route_with_departure_city_not_assigned_to_airline_does_not_update_flight(): void
    {
        $flight = Flight::first();

        $newAirline = $this->getAirlineWithCities(7);

        $newDepartureCity = $this->getCityNotAssignedToArline($newAirline);
        $newDestinationCity = $newAirline->cities[1];

        $newDepartureAt = $this->getDepartureAtDifferentThanCurrent($flight);
        $newArrivalAt = $this->getArrivalAtDifferentThanCurrent($newDepartureAt);

        $this->assertNotEmpty($newDepartureCity);

        $newData = [
            'airline' => $newAirline->id,
            'departure_city' => $newDepartureCity->id,
            'destination_city' => $newDestinationCity->id,
            'departure_at_date' => $newDepartureAt->format('Y-m-d'),
            'departure_at_time' => $newDepartureAt->format('H:i'),
            'arrival_at_date' => $newArrivalAt->format('Y-m-d'),
            'arrival_at_time' => $newArrivalAt->format('H:i'),
        ];

        $response = $this->putJson(route('api.flights.update', $flight), $newData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'departure_city' => $this->getSelectedAttributeInvalidErrorMessage('departure city')
            ]);

        $this
            ->assertDatabaseHas('flights', $flight->toArray())
            ->assertDatabaseMissing('flights', [
                'id' => $flight->id,
                'airline_id' => $newAirline->id,
                'departure_city_id' => $newDepartureCity->id,
                'destination_city_id' => $newDestinationCity->id,
                'departure_at' => $newDepartureAt->format('Y-m-d H:i'),
                'arrival_at' => $newArrivalAt->format('Y-m-d H:i'),
            ]);
    }

    public function test_request_to_api_update_route_with_destination_city_not_assigned_to_airline_does_not_update_flight(): void
    {
        $flight = Flight::first();

        $newAirline = $this->getAirlineWithCities(7);

        $newDepartureCity = $newAirline->cities[0];
        $newDestinationCity = $this->getCityNotAssignedToArline($newAirline);

        $newDepartureAt = $this->getDepartureAtDifferentThanCurrent($flight);
        $newArrivalAt = $this->getArrivalAtDifferentThanCurrent($newDepartureAt);

        $this->assertNotEmpty($newDepartureCity);

        $newData = [
            'airline' => $newAirline->id,
            'departure_city' => $newDepartureCity->id,
            'destination_city' => $newDestinationCity->id,
            'departure_at_date' => $newDepartureAt->format('Y-m-d'),
            'departure_at_time' => $newDepartureAt->format('H:i'),
            'arrival_at_date' => $newArrivalAt->format('Y-m-d'),
            'arrival_at_time' => $newArrivalAt->format('H:i'),
        ];

        $response = $this->putJson(route('api.flights.update', $flight), $newData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'destination_city' => $this->getSelectedAttributeInvalidErrorMessage('destination city')
            ]);

        $this
            ->assertDatabaseHas('flights', $flight->toArray())
            ->assertDatabaseMissing('flights', [
                'id' => $flight->id,
                'airline_id' => $newAirline->id,
                'departure_city_id' => $newDepartureCity->id,
                'destination_city_id' => $newDestinationCity->id,
                'departure_at' => $newDepartureAt->format('Y-m-d H:i'),
                'arrival_at' => $newArrivalAt->format('Y-m-d H:i'),
            ]);
    }

    public function test_request_to_api_update_route_with_same_departure_city_as_destination_city_does_not_update_flight(): void
    {
        $flight = Flight::first();

        $newAirline = $this->getAirlineWithCities(7);

        $newDepartureCity = $this->getDepartureCityDifferentThanCurrent($newAirline, $flight);

        $newDepartureAt = $this->getDepartureAtDifferentThanCurrent($flight);
        $newArrivalAt = $this->getArrivalAtDifferentThanCurrent($newDepartureAt);

        $newData = [
            'airline' => $newAirline->id,
            'departure_city' => $newDepartureCity->id,
            'destination_city' => $newDepartureCity->id,
            'departure_at_date' => $newDepartureAt->format('Y-m-d'),
            'departure_at_time' => $newDepartureAt->format('H:i'),
            'arrival_at_date' => $newArrivalAt->format('Y-m-d'),
            'arrival_at_time' => $newArrivalAt->format('H:i'),
        ];

        $response = $this->putJson(route('api.flights.update', $flight), $newData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'departure_city' => $this->getAttributesMustBeDifferentErrorMessage('departure city', 'destination city'),
                'destination_city' => $this->getAttributesMustBeDifferentErrorMessage('destination city', 'departure city'),
            ]);

        $this
            ->assertDatabaseHas('flights', $flight->toArray())
            ->assertDatabaseMissing('flights', [
                'id' => $flight->id,
                'airline_id' => $newAirline->airline_id,
                'departure_city_id' => $newDepartureCity->id,
                'destination_city_id' => $newDepartureCity->id,
                'departure_at' => $newDepartureAt->format('Y-m-d H:i'),
                'arrival_at' => $newArrivalAt->format('Y-m-d H:i'),
            ]);
    }

    public function test_request_to_api_update_route_with_departure_at_date_in_wrong_format_does_not_update_flight(): void
    {
        $flight = Flight::first();

        $newAirline = $this->getAirlineWithCities(7);

        $newDepartureCity = $this->getDepartureCityDifferentThanCurrent($newAirline, $flight);
        $newDestinationCity = $this->getDestinationCityDifferentThanCurrent($newAirline, $flight, $newDepartureCity);

        $newDepartureAt = $this->getDepartureAtDifferentThanCurrent($flight);
        $newArrivalAt = $this->getArrivalAtDifferentThanCurrent($newDepartureAt);

        $newData = [
            'airline' => $newAirline->id,
            'departure_city' => $newDepartureCity->id,
            'destination_city' => $newDestinationCity->id,
            'departure_at_date' => $newDepartureAt->format('d/m/Y'),
            'departure_at_time' => $newDepartureAt->format('H:i'),
            'arrival_at_date' => $newArrivalAt->format('Y-m-d'),
            'arrival_at_time' => $newArrivalAt->format('H:i'),
        ];

        $response = $this->putJson(route('api.flights.update', $flight), $newData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'departure_at_date' => $this->getAttributeDoesNotMatchFormat('departure date', 'Y-m-d')
            ]);

        $this
            ->assertDatabaseHas('flights', $flight->toArray())
            ->assertDatabaseMissing('flights', [
                'id' => $flight->id,
                'airline_id' => $newAirline->airline_id,
                'departure_city_id' => $newDepartureCity->id,
                'destination_city_id' => $newDestinationCity->id,
                'departure_at' => $newDepartureAt->format('Y-m-d H:i'),
                'arrival_at' => $newArrivalAt->format('Y-m-d H:i'),
            ]);
    }

    public function test_request_to_api_update_route_with_departure_at_date_before_today_does_not_update_flight(): void
    {
        $flight = Flight::first();

        $newAirline = $this->getAirlineWithCities(7);

        $newDepartureCity = $this->getDepartureCityDifferentThanCurrent($newAirline, $flight);
        $newDestinationCity = $this->getDestinationCityDifferentThanCurrent($newAirline, $flight, $newDepartureCity);

        $newDepartureAt = CarbonImmutable::now()->subDay();
        $newArrivalAt = $this->getArrivalAtDifferentThanCurrent($newDepartureAt);

        $newData = [
            'airline' => $newAirline->id,
            'departure_city' => $newDepartureCity->id,
            'destination_city' => $newDestinationCity->id,
            'departure_at_date' => $newDepartureAt->format('Y-m-d'),
            'departure_at_time' => $newDepartureAt->format('H:i'),
            'arrival_at_date' => $newArrivalAt->format('Y-m-d'),
            'arrival_at_time' => $newArrivalAt->format('H:i'),
        ];

        $response = $this->putJson(route('api.flights.update', $flight), $newData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'departure_at_date' => $this->getAttributeMustBeADateErrorMessage('departure date', 'after or equal to', 'today')
            ]);

        $this
            ->assertDatabaseHas('flights', $flight->toArray())
            ->assertDatabaseMissing('flights', [
                'id' => $flight->id,
                'airline_id' => $newAirline->airline_id,
                'departure_city_id' => $newDepartureCity->id,
                'destination_city_id' => $newDestinationCity->id,
                'departure_at' => $newDepartureAt->format('Y-m-d H:i'),
                'arrival_at' => $newArrivalAt->format('Y-m-d H:i'),
            ]);
    }

    public function test_request_to_api_update_route_with_departure_at_time_in_wrong_format_does_not_update_flight(): void
    {
        $flight = Flight::first();

        $newAirline = $this->getAirlineWithCities(7);

        $newDepartureCity = $this->getDepartureCityDifferentThanCurrent($newAirline, $flight);
        $newDestinationCity = $this->getDestinationCityDifferentThanCurrent($newAirline, $flight, $newDepartureCity);

        $newDepartureAt = $this->getDepartureAtDifferentThanCurrent($flight);
        $newArrivalAt = $this->getArrivalAtDifferentThanCurrent($newDepartureAt);

        $newData = [
            'airline' => $newAirline->id,
            'departure_city' => $newDepartureCity->id,
            'destination_city' => $newDestinationCity->id,
            'departure_at_date' => $newDepartureAt->format('Y-m-d'),
            'departure_at_time' => $newDepartureAt->format('H:i:s'),
            'arrival_at_date' => $newArrivalAt->format('Y-m-d'),
            'arrival_at_time' => $newArrivalAt->format('H:i'),
        ];

        $response = $this->putJson(route('api.flights.update', $flight), $newData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'departure_at_time' => $this->getAttributeDoesNotMatchFormat('departure time', 'H:i')
            ]);

        $this
            ->assertDatabaseHas('flights', $flight->toArray())
            ->assertDatabaseMissing('flights', [
                'id' => $flight->id,
                'airline_id' => $newAirline->id,
                'departure_city_id' => $newDepartureCity->id,
                'destination_city_id' => $newDestinationCity->id,
                'departure_at' => $newDepartureAt->format('Y-m-d H:i'),
                'arrival_at' => $newArrivalAt->format('Y-m-d H:i'),
            ]);
    }

    public function test_request_to_api_update_route_with_arrival_at_date_in_wrong_format_does_not_update_flight(): void
    {
        $flight = Flight::first();

        $newAirline = $this->getAirlineWithCities(7);

        $newDepartureCity = $this->getDepartureCityDifferentThanCurrent($newAirline, $flight);
        $newDestinationCity = $this->getDestinationCityDifferentThanCurrent($newAirline, $flight, $newDepartureCity);

        $newDepartureAt = $this->getDepartureAtDifferentThanCurrent($flight);
        $newArrivalAt = $this->getArrivalAtDifferentThanCurrent($newDepartureAt);

        $newData = [
            'airline' => $newAirline->id,
            'departure_city' => $newDepartureCity->id,
            'destination_city' => $newDestinationCity->id,
            'departure_at_date' => $newDepartureAt->format('Y-m-d'),
            'departure_at_time' => $newDepartureAt->format('H:i'),
            'arrival_at_date' => $newArrivalAt->format('d/m/Y'),
            'arrival_at_time' => $newArrivalAt->format('H:i'),
        ];

        $response = $this->putJson(route('api.flights.update', $flight), $newData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'arrival_at_date' => $this->getAttributeDoesNotMatchFormat('arrival date', 'Y-m-d')
            ]);

        $this
            ->assertDatabaseHas('flights', $flight->toArray())
            ->assertDatabaseMissing('flights', [
                'id' => $flight->id,
                'airline_id' => $newAirline->id,
                'departure_city_id' => $newDepartureCity->id,
                'destination_city_id' => $newDestinationCity->id,
                'departure_at' => $newDepartureAt->format('Y-m-d H:i'),
                'arrival_at' => $newArrivalAt->format('Y-m-d H:i'),
            ]);
    }

    public function test_request_to_api_update_route_with_arrival_at_date_before_departure_at_date_does_not_update_flight(): void
    {
        $flight = Flight::first();

        $newAirline = $this->getAirlineWithCities(7);

        $newDepartureCity = $this->getDepartureCityDifferentThanCurrent($newAirline, $flight);
        $newDestinationCity = $this->getDestinationCityDifferentThanCurrent($newAirline, $flight, $newDepartureCity);

        $newDepartureAt = $this->getDepartureAtDifferentThanCurrent($flight);
        $newArrivalAt = $newDepartureAt->subDay();

        $newData = [
            'airline' => $newAirline->id,
            'departure_city' => $newDepartureCity->id,
            'destination_city' => $newDestinationCity->id,
            'departure_at_date' => $newDepartureAt->format('Y-m-d'),
            'departure_at_time' => $newDepartureAt->format('H:i'),
            'arrival_at_date' => $newArrivalAt->format('Y-m-d'),
            'arrival_at_time' => $newArrivalAt->format('H:i'),
        ];

        $response = $this->putJson(route('api.flights.update', $flight), $newData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'arrival_at_date' => $this->getAttributeMustBeADateErrorMessage('arrival date', 'after or equal to', 'departure date')
            ]);

        $this
            ->assertDatabaseHas('flights', $flight->toArray())
            ->assertDatabaseMissing('flights', [
                'id' => $flight->id,
                'airline_id' => $newAirline->id,
                'departure_city_id' => $newDepartureCity->id,
                'destination_city_id' => $newDestinationCity->id,
                'departure_at' => $newDepartureAt->format('Y-m-d H:i'),
                'arrival_at' => $newArrivalAt->format('Y-m-d H:i'),
            ]);
    }

    public function test_request_to_api_update_route_with_arrival_at_time_in_wrong_format_does_not_update_flight(): void
    {
        $flight = Flight::first();

        $newAirline = $this->getAirlineWithCities(7);

        $newDepartureCity = $this->getDepartureCityDifferentThanCurrent($newAirline, $flight);
        $newDestinationCity = $this->getDestinationCityDifferentThanCurrent($newAirline, $flight, $newDepartureCity);

        $newDepartureAt = $this->getDepartureAtDifferentThanCurrent($flight);
        $newArrivalAt = $this->getArrivalAtDifferentThanCurrent($newDepartureAt);

        $newData = [
            'airline' => $newAirline->id,
            'departure_city' => $newDepartureCity->id,
            'destination_city' => $newDestinationCity->id,
            'departure_at_date' => $newDepartureAt->format('Y-m-d'),
            'departure_at_time' => $newDepartureAt->format('H:i'),
            'arrival_at_date' => $newArrivalAt->format('Y-m-d'),
            'arrival_at_time' => $newArrivalAt->format('H:i:s'),
        ];

        $response = $this->putJson(route('api.flights.update', $flight), $newData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'arrival_at_time' => $this->getAttributeDoesNotMatchFormat('arrival time', 'H:i')
            ]);

        $this
            ->assertDatabaseHas('flights', $flight->toArray())
            ->assertDatabaseMissing('flights', [
                'id' => $flight->id,
                'airline_id' => $newAirline->id,
                'departure_city_id' => $newDepartureCity->id,
                'destination_city_id' => $newDestinationCity->id,
                'departure_at' => $newDepartureAt->format('Y-m-d H:i'),
                'arrival_at' => $newArrivalAt->format('Y-m-d H:i'),
            ]);
    }

    public function test_request_to_api_update_route_with_same_departure_at_and_arrival_at_does_not_update_flight(): void
    {
        $flight = Flight::first();

        $newAirline = $this->getAirlineWithCities(7);

        $newDepartureCity = $this->getDepartureCityDifferentThanCurrent($newAirline, $flight);
        $newDestinationCity = $this->getDestinationCityDifferentThanCurrent($newAirline, $flight, $newDepartureCity);

        $newDepartureAt = $this->getDepartureAtDifferentThanCurrent($flight);

        $newData = [
            'airline' => $newAirline->id,
            'departure_city' => $newDepartureCity->id,
            'destination_city' => $newDestinationCity->id,
            'departure_at_date' => $newDepartureAt->format('Y-m-d'),
            'departure_at_time' => $newDepartureAt->format('H:i'),
            'arrival_at_date' => $newDepartureAt->format('Y-m-d'),
            'arrival_at_time' => $newDepartureAt->format('H:i'),
        ];

        $response = $this->putJson(route('api.flights.update', $flight), $newData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'arrival_at_time' => 'The arrival time must be a time after the departure time when both the departure date and arrival date are the same.'
            ]);

        $this
            ->assertDatabaseHas('flights', $flight->toArray())
            ->assertDatabaseMissing('flights', [
                'id' => $flight->id,
                'airline_id' => $newAirline->airline_id,
                'departure_city_id' => $newDepartureCity->id,
                'destination_city_id' => $newDestinationCity->id,
                'departure_at' => $newDepartureAt->format('Y-m-d H:i'),
                'arrival_at' => $newDepartureAt->format('Y-m-d H:i'),
            ]);
    }
}
