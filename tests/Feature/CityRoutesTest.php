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

class CityRoutesTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private CityService $cityService;
    private AirlineService $airlineService;

    public function setUp(): void
    {
        parent::setUp();

        $this->cityService = new CityService();
        $this->airlineService = new AirlineService();
        $this->seed();
    }

    public function test_index_route_returns_view_and_displays_records(): void
    {
        $response = $this->get(route('cities.index'));

        $cities = $this->cityService->getCursorPaginated(10);

        $response
            ->assertSuccessful()
            ->assertViewIs('city.index')
            ->assertViewMissing('No cities available')
            ->assertViewHasAll([
                'cities' => $cities,
                'airlines' => $this->airlineService->get()
            ]);
    }

    public function test_index_route_returns_view_and_displays_records_sorted_by_id_asc(): void
    {
        $sort = 'id,asc';

        $response = $this->get(route('cities.index', [
            'sort' => $sort
        ]));

        $cities = $this->cityService->getCursorPaginated(total: 10, sort: $sort);

        $response
            ->assertSuccessful()
            ->assertViewIs('city.index')
            ->assertViewMissing('No cities available')
            ->assertViewHasAll([
                'cities' => $cities,
                'airlines' => $this->airlineService->get()
            ]);
    }

    public function test_index_route_returns_view_and_displays_records_sorted_by_id_desc(): void
    {
        $sort = 'id,desc';

        $response = $this->get(route('cities.index', [
            'sort' => $sort
        ]));

        $cities = $this->cityService->getCursorPaginated(total: 10, sort: $sort);

        $response
            ->assertSuccessful()
            ->assertViewIs('city.index')
            ->assertViewMissing('No cities available')
            ->assertViewHasAll([
                'cities' => $cities,
                'airlines' => $this->airlineService->get()
            ]);
    }

    public function test_index_route_returns_view_and_displays_records_sorted_by_name_asc(): void
    {
        $sort = 'name,asc';

        $response = $this->get(route('cities.index', [
            'sort' => $sort
        ]));

        $cities = $this->cityService->getCursorPaginated(total: 10, sort: $sort);

        $response
            ->assertSuccessful()
            ->assertViewIs('city.index')
            ->assertViewMissing('No cities available')
            ->assertViewHasAll([
                'cities' => $cities,
                'airlines' => $this->airlineService->get()
            ]);
    }

    public function test_index_route_returns_view_and_displays_records_sorted_by_name_desc(): void
    {
        $sort = 'name,desc';

        $response = $this->get(route('cities.index', [
            'sort' => $sort
        ]));

        $cities = $this->cityService->getCursorPaginated(total: 10, sort: $sort);

        $response
            ->assertSuccessful()
            ->assertViewIs('city.index')
            ->assertViewMissing('No cities available')
            ->assertViewHasAll([
                'cities' => $cities,
                'airlines' => $this->airlineService->get()
            ]);
    }
    
    public function test_index_route_returns_view_and_displays_records_sorted_filtered_by_airline(): void
    {
        $airline = Airline::factory()->create();

        $airline->cities()->attach(City::skip(0)->take(2)->get()->pluck('id'));

        Flight::factory()
        ->for($airline)
        ->create();

        $response = $this->get(route('cities.index', [
            'airline' => $airline->id
        ]));

        $cities = $this->cityService->getCursorPaginated(total: 10, airline: $airline->id);

        $response
            ->assertSuccessful()
            ->assertViewIs('city.index')
            ->assertViewMissing('No cities available')
            ->assertViewHasAll([
                'cities' => $cities,
                'airlines' => $this->airlineService->get()
            ])
            ->assertViewHas('cities', function ($cities) use ($airline) {
                return $cities->count() === 2
                    && $cities->every(function ($city) use ($airline) {
                        return $city->incomingFlights->contains(function ($value) use ($airline) {
                            return $value->airline_id === $airline->id;
                        })
                        || $city->outgoingFlights->contains(function ($value) use ($airline) {
                            return $value->airline_id === $airline->id;
                        });
                    });
            });
    }
}
