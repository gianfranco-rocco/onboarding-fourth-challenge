<?php

use App\Http\Controllers\CityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('flight.index');
})->name('home');

Route::controller(CityController::class)
    ->prefix('cities')
    ->name('city.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
    }
);
