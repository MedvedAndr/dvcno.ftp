<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\v1\ApiEventsController as ApiController_v1;
use App\Http\Controllers\Ajax\AjaxController;

Route::prefix('v1')->group(function() {
    Route::controller(ApiController_v1::class)->group(function() {
        Route::get('/languages', 'getLanguages');

        Route::get('/dictionaries', 'getDictionaries');
        Route::get('/dictionaries/{parameter}', 'getDictionaryByParameter');
        // Route::get('/dictionaries/{parameter}/{key}', 'getTerm');

        Route::get('/menus', 'getMenus');
        Route::get('/menus/{parameter}', 'getMenusByParameter');

        Route::get('/events', 'getEvents');
        Route::get('/events/{parameter}', 'getEventsByParameter');

        Route::get('/news', 'getNews');
        Route::get('/news/{parameter}', 'getNewsByParameter');

        Route::get('/pages', 'getPages');
        Route::get('/pages/{parameter}', 'getPagesByParameter');
    });
});

Route::post('/ajax/sendMail', [AjaxController::class, 'sendMail']);
Route::post('/ajax/live_search', [AjaxController::class, 'get_search_info']);