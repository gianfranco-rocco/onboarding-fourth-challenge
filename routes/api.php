<?php

use App\Http\Controllers\Api\AirlineController as ApiAirlineController;
use App\Http\Controllers\Api\CityController as ApiCityController;
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
        Route::post('/', 'store')->name('store');
        Route::get('/{airline}', 'show')->name('show');
        Route::put('/{airline}', 'update')->name('update');
        Route::delete('/{airline}', 'destroy')->name('destroy');
    }
);
