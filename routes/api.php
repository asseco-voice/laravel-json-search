<?php

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

Route::namespace('Voice\JsonSearch\App\Http\Controllers')
    ->prefix('api')
    ->middleware('api')
    ->group(function () {

        Route::apiResource('search-favorites', 'SearchFavoriteController');

    });
