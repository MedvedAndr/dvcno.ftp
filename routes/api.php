<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\v1\ApiEventsController as ApiController_v1;

Route::prefix('v1')->group(function() {
    Route::controller(ApiController_v1::class)->group(function() {
        Route::get('/languages', 'getLanguages');

        Route::get('/dictionaries', 'getDictionaries');
        Route::get('/dictionaries/{parameter}', 'getDictionaryByParameter');
        // Route::get('/dictionaries/{parameter}/{key}', 'getTerm');

        Route::get('/menus', 'getMenus');

        Route::get('/events', 'getEvents');
        Route::get('/events/{parameter}', 'getEventsByParameter');
    });
});