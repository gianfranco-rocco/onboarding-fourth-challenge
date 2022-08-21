<?php

use App\Http\Controllers\Api\AirlineController as ApiAirlineController;
use App\Http\Controllers\Api\CityController as ApiCityController;
use App\Http\Controllers\Api\FlightController as ApiFlightController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::controller(ApiCityController::class)
    ->prefix('cities')
    ->name('cities.')
    ->group(function () {
        Route::get('/{airline}/cities', 'getAirlineCities')->name('api.airline-cities');
        Route::get('/', 'index')->name('api.index');
        Route::post('/', 'store')->name('store');
        Route::get('/{city}', 'show')->name('show');
        Route::put('/{city}', 'update')->name('update');
        Route::delete('/{city}', 'destroy')->name('destroy');
    }
);

Route::controller(ApiAirlineController::class)
    ->prefix('airlines')
    ->name('airlines.')
    ->group(function () {
        Route::get('/', 'index')->name('api.index');
        Route::post('/', 'store')->name('store');
        Route::get('/{airline}', 'show')->name('show');
        Route::put('/{airline}', 'update')->name('update');
        Route::delete('/{airline}', 'destroy')->name('destroy');
    }
);

Route::controller(ApiFlightController::class)
    ->prefix('flights')
    ->name('flights.api.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{flight}', 'show')->name('show');
        Route::put('/{flight}', 'update')->name('update');
        Route::delete('/{flight}', 'destroy')->name('destroy');
    }
);