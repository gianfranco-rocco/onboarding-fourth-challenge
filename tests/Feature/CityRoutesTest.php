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
}
