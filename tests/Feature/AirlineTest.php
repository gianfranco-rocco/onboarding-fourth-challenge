<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\City;
use App\Models\Flight;
use App\Services\AirlineService;
use App\Services\CityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Tests\TestCase;

class AirlineTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private CityService $cityService;
    private AirlineService $airlineService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->cityService = new CityService();
        $this->airlineService = new AirlineService();
    }

    private function getCities(int $amount = 3, array $columns = ['*']): Collection
    {
        return City::take($amount)->get($columns);
    }

    private function parseCitiesForRequest(Collection $cities): string
    {
        return $cities->implode(',');
    }

    private function getCreatedJsonResponse(Airline $airline): array
    {
        return [
            'message' => "Created airline 'ID {$airline->id}' successfully."
        ];
    }

    private function getUpdatedJsonResponse(Airline $airline): array
    {
        return [
            'message' => "Updated airline 'ID {$airline->id}' successfully."
        ];
    }

    private function getDeletedJsonResponse(Airline $airline): array
    {
        return [
            'message' => "Deleted airline 'ID {$airline->id}' successfully."
        ];
    }

    private function assertDatabaseHasCities(Airline $airline, Collection $cities): void
    {
        $cities->each(function ($city) use ($airline) {
            $this->assertDatabaseHas('airline_city', [
                'airline_id' => $airline->id,
                'city_id' => $city->id
            ]);
        });
    }

    private function assertDatabaseMissingCities(Airline $airline, Collection $cities): void
    {
        $cities->each(function ($city) use ($airline) {
            $this->assertDatabaseMissing('airline_city', [
                'airline_id' => $airline->id,
                'city_id' => $city->id
            ]);
        });
    }

    private function getAirlineWithCities(int $minCitiesAmount = 2): Airline
    {
        return Airline::with('cities')
        ->whereHas('cities')
        ->withCount('cities')
        ->havingRaw("cities_count >= {$minCitiesAmount}")
        ->first();
    }

    private function createFlightsForAirline(
        Airline $airline,
        ?int $departureCityId = null,
        ?int $destinationCityId = null,
        ?int $count = null
    ): Flight|Collection {
        $attributes = [];

        if ($departureCityId) {
            $attributes['departure_city_id'] = $departureCityId;
        }

        if ($destinationCityId) {
            $attributes['destination_city_id'] = $destinationCityId;
        }

        return Flight::factory()
        ->count($count)
        ->for($airline)
        ->create($attributes);
    }

    public function test_index_route_renders_view_with_airlines_and_cities_data(): void
    {
        $response = $this->get(route('airlines.index'));

        $response
            ->assertSuccessful()
            ->assertViewIs('airline.index')
            ->assertViewHasAll([
                'airlines' => $this->airlineService->getCursorPaginated(10),
                'cities' => $this->cityService->get()
            ]);
    }

    public function test_index_route_returns_json_response_with_airlines_and_cities_when_wants_json(): void
    {
        $response = $this->getJson(route('airlines.index'));

        $response
            ->assertJsonCount(2)
            ->assertJsonStructure([
                'airlines' => [
                    'data',
                    'path',
                    'per_page',
                    'next_cursor',
                    'next_page_url',
                    'prev_cursor',
                    'prev_page_url'
                ],
                'cities'
            ])
            ->assertJsonPath('airlines', $this->airlineService->getCursorPaginated(10)->toArray())
            ->assertJsonPath('cities', $this->cityService->get()->toArray());
    }

    public function test_index_route_with_cursor_returns_view_with_airlines_and_cities_where_airlines_correspond_to_the_cursor(): void
    {
        $airlines = $this->airlineService->getCursorPaginated(10)->toArray();

        $response = $this->get(route('airlines.index', ['cursor' => $airlines['next_cursor']]));

        $response
            ->assertSuccessful()
            ->assertViewIs('airline.index')
            ->assertViewHasAll([
                'cities' => $this->cityService->get(),
                'airlines' => function ($airlines) {
                    /**
                     * This query gets the same query results as the request with the cursor
                     */
                    $airlinesAtCursor = Airline::skip(10)->take(10)->orderBy('id', 'desc')->get(['id'])->pluck('id')->toArray();

                    /**
                     * The IDs correspond to the response's airlines' records
                     */
                    $airlinesIds = collect($airlines->toArray()['data'])->pluck('id');

                    /**
                     * We check if the IDs from the response's airlines differ from the manually fetched airlines.
                     * If they don't it means the fetched results as OK.
                     */
                    $diff = $airlinesIds->diff($airlinesAtCursor)->all();

                    return !count($diff);
                }
            ]);
    }

    public function test_index_route_filtered_by_destination_city_returns_view_with_cities_and_one_airline(): void
    {
        $airline = $this->getAirlineWithCities();

        $destinationCity = $airline->cities[1];

        $flight = $this->createFlightsForAirline($airline, $airline->cities[0]->id, $destinationCity->id);

        $response = $this->get(route('airlines.index', ['destination_city' => $destinationCity]));

        $response
            ->assertSuccessful()
            ->assertViewIs('airline.index')
            ->assertViewHasAll([
                'cities' => $this->cityService->get(),
                'airlines' => function ($airlines) use ($flight) {
                    return $airlines->count() === 1 
                        && $flight->airline_id == $airlines[0]->id;
                }
            ]);
    }

    public function test_index_route_filtered_by_active_flights_returns_view_with_cities_and_one_airline(): void
    {
        $airline = $this->getAirlineWithCities();

        $flights = $this->createFlightsForAirline($airline, $airline->cities[0]->id, $airline->cities[1]->id, 2);

        $response = $this->get(route('airlines.index', ['active_flights' => $flights->count()]));

        $response
            ->assertSuccessful()
            ->assertViewIs('airline.index')
            ->assertViewHasAll([
                'cities' => $this->cityService->get(),
                'airlines' => function ($airlines) use ($flights) {
                    return $airlines->count() === 1
                        && $airlines[0]->active_flights_count === $flights->count();
                }
            ]);
    }

    public function test_index_route_filtered_by_destination_city_and_active_flights_returns_view_with_cities_and_one_airline(): void
    {
        $airline = $this->getAirlineWithCities();

        $destinationCity = $airline->cities[1];
        
        $flights = $this->createFlightsForAirline($airline, $airline->cities[0]->id, $destinationCity->id, 3);

        $response = $this->get(route('airlines.index', [
            'destination_city' => $destinationCity->id,
            'active_flights' => $flights->count()
        ]));

        $response
        ->assertSuccessful()
        ->assertViewIs('airline.index')
        ->assertViewHasAll([
            'cities' => $this->cityService->get(),
            'airlines' => function ($airlines) use ($destinationCity, $flights) {
                $flightsHaveSameDestinationCity = $flights->every(function ($flight) use ($destinationCity) {
                    return $flight->destination_city_id === $destinationCity->id;
                });

                return $airlines->count() === 1
                    && $airlines[0]->active_flights_count === $flights->count()
                    && $flightsHaveSameDestinationCity;
            }
        ]);
    }

    public function test_store_api_creates_airline(): void
    {
        $cities = $this->getCities(3, ['id']);

        $data = [
            'name' => $this->faker()->name(),
            'description' => $this->faker()->text(100),
            'cities' => $this->parseCitiesForRequest($cities->pluck('id'))
        ];

        $response = $this->postJson(route('airlines.store'), $data);

        $airline = Airline::orderBy('id', 'desc')->first(['id']);

        $response
            ->assertCreated()
            ->assertJson($this->getCreatedJsonResponse($airline));

        $this->assertDatabaseHas('airlines', collect($data)->except('cities')->all());

        $this->assertDatabaseHasCities($airline, $cities);
    }

    public function test_store_api_creates_airline_without_cities(): void
    {
        $data = [
            'name' => $this->faker()->name(),
            'description' => $this->faker()->text(100)
        ];

        $response = $this->postJson(route('airlines.store'), $data);

        $airline = Airline::orderBy('id', 'desc')->first(['id']);

        $response
            ->assertCreated()
            ->assertJson($this->getCreatedJsonResponse($airline));

        $this
        ->assertDatabaseHas('airlines', $data)
        ->assertDatabaseMissing('airline_city', [
            'airline_id' => $airline->id
        ]);
    }

    public function test_store_api_does_not_create_airline_without_description(): void
    {
        $data = [
            'name' => $this->faker()->name()
        ];

        $response = $this->postJson(route('airlines.store'), $data);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'description' => 'The description field is required.'
            ]);

        $this->assertDatabaseMissing('airlines', $data);
    }

    public function test_store_api_does_not_create_airline_without_name_and_description(): void
    {
        $airlinesCountBeforeRequest = Airline::count();
        
        $response = $this->postJson(route('airlines.store'));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name' => 'The name field is required.',
                'description' => 'The description field is required.',
            ]);

        $this->assertDatabaseCount('airlines', $airlinesCountBeforeRequest);
    }

    public function test_store_api_does_not_create_airline_with_repeated_name(): void
    {
        $name = $this->faker()->name();
        
        Airline::factory()->create([
            'name' => $name
        ]);

        $data = [
            'name' => $name,
            'description' => $this->faker()->text(100)
        ];

        $response = $this->postJson(route('airlines.store'), $data);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name' => 'The name has already been taken.'
            ]);

        $this->assertDatabaseMissing('airlines', $data);
    }

    public function test_store_api_does_not_create_airline_when_any_city_is_invalid(): void
    {
        $cities = $this->getCities(3, ['id'])->pluck('id')->concat(["123333", "23232"]);

        $data = [
            'name' => $this->faker()->name(),
            'description' => $this->faker()->text(100),
            'cities' => $this->parseCitiesForRequest($cities)
        ];

        $response = $this->postJson(route('airlines.store'), $data);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'cities.3' => "Invalid city",
                'cities.4' => "Invalid city",
            ]);

        $this->assertDatabaseMissing('airlines', collect($data)->except('cities')->all());
    }

    public function test_show_api_returns_airline(): void
    {
        $airline = Airline::with('cities')->first();

        $response = $this->getJson(route('airlines.show', $airline));

        $response
            ->assertSuccessful()
            ->assertJsonPath('data', $airline->toArray());
    }

    public function test_show_api_does_not_return_airline_when_invalid_airline_passed_to_route(): void
    {
        $response = $this->getJson(route('airlines.show', 232323));

        $response->assertNotFound();
    }

    public function test_update_api_updates_name_and_description(): void
    {
        $airline = Airline::first();

        $prevData = [
            'name' => $airline->name,
            'description' => $airline->description
        ];

        $newData = [
            'name' => $this->faker()->name(),
            'description' => $this->faker()->text()
        ];

        $response = $this->putJson(route('airlines.update', $airline), $newData);

        $response
            ->assertSuccessful()
            ->assertJson($this->getUpdatedJsonResponse($airline));

        $this
            ->assertDatabaseHas('airlines', $newData)
            ->assertDatabaseMissing('airlines', $prevData);
    }

    public function test_update_api_does_not_update_when_name_repeated(): void
    {
        $airlineWithExistingName = Airline::first();
        $airlineToUpdate = Airline::skip(1)->first();

        $data = [
            'name' => $airlineWithExistingName->name,
            'description' => $this->faker()->text()
        ];

        $response = $this->putJson(route('airlines.update', $airlineToUpdate->id), $data);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name' => 'The name has already been taken.'
            ]);

        $this->assertDatabaseMissing('airlines', $data);
    }

    public function test_update_api_updates_name_and_description_and_attaches_cities(): void
    {
        $airline = Airline::factory()->create();

        $cities = $this->getCities(3, ['id']);

        $data = [
            'name' => $this->faker()->name(),
            'description' => $this->faker()->text(),
            'cities' => $this->parseCitiesForRequest($cities->pluck('id'))
        ];

        $this->assertDatabaseMissing('airline_city', [
            'airline_id' => $airline->id
        ]);

        $response = $this->putJson(route('airlines.update', $airline), $data);

        $response
            ->assertSuccessful()
            ->assertJson($this->getUpdatedJsonResponse($airline));

        $this->assertDatabaseHas('airlines', collect($data)->except('cities')->all());

        $this->assertDatabaseHasCities($airline, $cities);
    }

    public function test_update_api_updates_name_and_description_and_attaches_cities_while_retaining_previously_attached_cities(): void
    {   
        $airline = $this->getAirlineWithCities();

        $prevCities = $airline->cities;

        $newCities = $this->getCities(3, ['id']);

        $cities = $prevCities->concat($newCities);

        $data = [
            'name' => $this->faker()->name(),
            'description' => $this->faker()->text(),
            'cities' => $this->parseCitiesForRequest($cities->pluck('id'))
        ];

        $response = $this->putJson(route('airlines.update', $airline), $data);

        $response
            ->assertSuccessful()
            ->assertJson($this->getUpdatedJsonResponse($airline));

        $this->assertDatabaseHas('airlines', collect($data)->except('cities')->all());

        $this->assertDatabaseHasCities($airline, $cities);
    }

    public function test_update_api_updates_name_and_description_and_attaches_cities_but_detaches_previously_attached_cities(): void
    {   
        $airline = $this->getAirlineWithCities();
        
        $prevCities = $airline->cities;

        $newCities = $this->getCities(3, ['id']);

        $data = [
            'name' => $this->faker()->name(),
            'description' => $this->faker()->text(),
            'cities' => $this->parseCitiesForRequest($newCities->pluck('id'))
        ];

        $response = $this->putJson(route('airlines.update', $airline), $data);

        $response
            ->assertSuccessful()
            ->assertJson($this->getUpdatedJsonResponse($airline));

        $this->assertDatabaseHas('airlines', collect($data)->except('cities')->all());

        $this->assertDatabaseMissingCities($airline, $prevCities);

        $this->assertDatabaseHasCities($airline, $newCities);
    }

    public function test_delete_api_soft_deletes_record(): void
    {
        $airline = Airline::factory()->create();

        $response = $this->deleteJson(route('airlines.destroy', $airline));

        $response
            ->assertSuccessful()
            ->assertJson($this->getDeletedJsonResponse($airline));

        $this->assertSoftDeleted($airline);
    }

    public function test_delete_api_soft_deletes_record_and_detaches_cities(): void
    {
        $airline = $this->getAirlineWithCities();

        $response = $this->deleteJson(route('airlines.destroy', $airline), [
            'confirmation' => true
        ]);

        $response
            ->assertSuccessful()
            ->assertJson($this->getDeletedJsonResponse($airline));

        $this->assertSoftDeleted($airline);

        $this->assertDatabaseMissingCities($airline, $airline->cities);
    }

    public function test_delete_api_soft_deletes_record_and_detaches_cities_and_soft_deletes_flights(): void
    {
        $airline = $this->getAirlineWithCities();

        $flights = $this->createFlightsForAirline(airline: $airline, count: 4);

        $response = $this->deleteJson(route('airlines.destroy', $airline), [
            'confirmation' => true
        ]);

        $response
            ->assertSuccessful()
            ->assertJson($this->getDeletedJsonResponse($airline));

        $this->assertSoftDeleted($airline);

        $this->assertDatabaseMissingCities($airline, $airline->cities);

        $flights->each(function ($flight) {
            $this->assertSoftDeleted($flight);
        });
    }

    public function test_delete_api_does_not_soft_delete_record_and_does_not_detach_cities_and_does_not_soft_delete_flights_when_confirmation_not_passed_to_request(): void
    {
        $airline = $this->getAirlineWithCities();

        $flights = $this->createFlightsForAirline(airline: $airline, count: 4);

        $response = $this->deleteJson(route('airlines.destroy', $airline));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'confirmation' => "The airline is assigned to <strong>{$flights->count()} flight(s)</strong>, this action will delete the airline as well as every flight assigned to it."
            ]);

        $this->assertNotSoftDeleted($airline);

        $this->assertDatabaseHasCities($airline, $airline->cities);

        $flights->each(function ($flight) {
            $this->assertNotSoftDeleted($flight);
        });
    }
}
