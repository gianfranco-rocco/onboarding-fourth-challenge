<?php

namespace Tests\Feature;

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
}
