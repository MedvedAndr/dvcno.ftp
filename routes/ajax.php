<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Ajax\FormValidationController;
use App\Http\Controllers\Ajax\ComponentController;
use App\Http\Controllers\Ajax\CookieController;

Route::controller(FormValidationController::class)->group(function() {
    Route::post('/form/validation', 'formValidation')->name('ajax.form.validation');
    Route::post('/slugify', 'stringSlugify')->name('ajax.slugify');
    Route::post('/files/get', 'getFiles')->name('ajax.files.get');
    Route::post('/upload_files', 'uploadFiles')->name('ajax.upload_files');
    Route::post('/settings/get', 'getSettings')->name('ajax.settings.get');
    Route::post('/getInfo', 'getInfo'); //
    // Route::post('/save', 'save');
    // Route::post('/delete', 'delete');
});

Route::controller(ComponentController::class)->group(function() {
    Route::post('/item/get', 'getItem')->name('ajax.item.get');
    Route::post('/component/get', 'getComponent')->name('ajax.component.get');
});

Route::controller(CookieController::class)->group(function() {
    Route::post('/cookie/set', 'setCookie')->name('ajax.cookie.set');
});
