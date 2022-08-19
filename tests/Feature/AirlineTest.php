<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\City;
use App\Models\Flight;
use App\Services\AirlineService;
use App\Services\CityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
        $airline = Airline::with('cities')
        ->whereHas('cities')
        ->withCount('cities')
        ->havingRaw('cities_count >= 2')
        ->first();

        $destinationCity = $airline->cities[1];

        $flight = Flight::factory()
        ->for($airline)
        ->create([
            'departure_city_id' => $airline->cities[0]->id,
            'destination_city_id' => $destinationCity->id,
        ]);

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
        $airline = Airline::with('cities')
        ->whereHas('cities')
        ->withCount('cities')
        ->havingRaw('cities_count >= 2')
        ->first();

        $flights = Flight::factory()
        ->count(2)
        ->for($airline)
        ->create([
            'departure_city_id' => $airline->cities[0]->id,
            'destination_city_id' => $airline->cities[1]->id,
        ]);

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
        $airline = Airline::with('cities')
        ->whereHas('cities')
        ->withCount('cities')
        ->havingRaw('cities_count >= 2')
        ->first();

        $destinationCity = $airline->cities[1];
        
        $flights = Flight::factory()
        ->count(3)
        ->for($airline)
        ->create([
            'departure_city_id' => $airline->cities[0]->id,
            'destination_city_id' => $destinationCity->id,
        ]);

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
        $cities = City::take(3)->get(['id'])->pluck('id');

        $data = [
            'name' => $this->faker()->name(),
            'description' => $this->faker()->text(100),
            'cities' => $cities->implode(',')
        ];

        $response = $this->postJson(route('airlines.store'), $data);

        $airline = Airline::orderBy('id', 'desc')->first(['id']);

        $response
            ->assertCreated()
            ->assertJson([
                'message' => "Created airline 'ID {$airline->id}' successfully."
            ]);

        $this->assertDatabaseHas('airlines', collect($data)->except('cities')->all());

        $cities->each(function ($city) use ($airline) {
            $this->assertDatabaseHas('airline_city', [
                'airline_id' => $airline->id,
                'city_id' => $city
            ]);
        });
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
            ->assertJson([
                'message' => "Created airline 'ID {$airline->id}' successfully."
            ]);;

        $this
        ->assertDatabaseHas('airlines', $data)
        ->assertDatabaseMissing('airline_city', [
            'airline_id' => $airline->id
        ]);
    }

    public function test_store_api_doesnt_create_airline_without_description(): void
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

    public function test_store_api_doesnt_create_airline_without_name_and_description(): void
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

    public function test_store_api_doesnt_create_airline_with_repeated_name(): void
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

    public function test_store_api_doesnt_create_airline_when_any_city_is_invalid(): void
    {
        $cities = City::take(3)->get(['id'])->pluck('id')->concat(["123333", "23232"]);

        $data = [
            'name' => $this->faker()->name(),
            'description' => $this->faker()->text(100),
            'cities' => $cities->implode(',')
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
}
