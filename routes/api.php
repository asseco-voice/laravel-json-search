<?php

use Illuminate\Support\Facades\Route;
use Voice\JsonSearch\App\Http\Controllers\SearchController;

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

Route::namespace('Voice\JsonSearch\App\Http\Controllers')
    ->prefix('api')
    ->middleware('api')
    ->group(function () {
        Route::post('search/{model}', [SearchController::class, 'index'])->name('search.index');
        Route::put('search/{model}/update', [SearchController::class, 'update'])->name('search.update');
        Route::delete('search/{model}', [SearchController::class, 'destroy'])->name('search.destroy');

        Route::apiResource('search-favorites', 'SearchFavoriteController');
    });
