<?php

use App\Http\Controllers\CityController;
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

Route::controller(CityController::class)
    ->prefix('cities')
    ->name('cities.')
    ->group(function () {
        Route::post('/', 'store')->name('store');
        Route::get('/{city}', 'show')->name('show');
        Route::put('/{city}', 'update')->name('update');
        Route::delete('/{city}', 'destroy')->name('destroy');
    }
);
