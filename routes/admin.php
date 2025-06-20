<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AdminController;

Route::controller(AdminController::class)->group(function() {
    Route::get('/', 'index')->name('admin.index');
    Route::get('/settings', 'settings')->name('admin.settings');

    Route::get('/users', 'users')->name('admin.users');
    Route::get('/users/view/{aid}', 'viewUser')->name('admin.users.view');
    Route::get('/users/create', 'createUser')->name('admin.users.create');
    Route::get('/users/edit/{aid}', 'editUser')->name('admin.users.edit');
    Route::get('/roles', 'roles')->name('admin.roles');
    Route::get('/roles/view/{aid}', 'viewRole')->name('admin.roles.view');
    Route::get('/roles/create', 'createRole')->name('admin.roles.create');
    Route::get('/roles/edit/{aid}', 'editRole')->name('admin.roles.edit');
    
    Route::get('/file_manager', 'files')->name('admin.file.manager');
    Route::get('/file_manager_for_select', 'files_select')->name('admin.file.manager_select');

    Route::get('/menus', 'menus')->name('admin.menus');
    Route::get('/menus/view/{aid}', 'viewMenu')->name('admin.menus.view');
    Route::get('/menus/create', 'createMenu')->name('admin.menus.create');
    Route::get('/menus/edit/{aid}', 'editMenu')->name('admin.menus.edit');

    Route::get('/pages', 'pages')->name('admin.pages');
    Route::get('/pages/edit/{aid}', 'editPage')->name('admin.pages.edit');

    Route::get('/events', 'events')->name('admin.events');
    //Route::get('/events/view/{aid}', 'viewEvent')->name('admin.events.view');
    Route::get('/events/create', 'createEvent')->name('admin.events.create');
    //Route::get('/events/edit/{aid}', 'editEvent')->name('admin.events.edit');

    Route::get('/news', 'news')->name('admin.news');
    //Route::get('/news/view/{aid}', 'viewNews')->name('admin.news.view');
    Route::get('/news/create', 'createNews')->name('admin.news.create');
    //Route::get('/news/edit/{aid}', 'editNews')->name('admin.news.edit');

    Route::get('/languages', 'languages')->name('admin.languages');
    Route::get('/languages/view/{aid}', 'viewLanguage')->name('admin.languages.view');
    Route::get('/languages/create', 'createLanguage')->name('admin.languages.create');
    Route::get('/languages/edit/{aid}', 'editLanguage')->name('admin.languages.edit');

    Route::get('/dictionaries', 'dictionaries')->name('admin.dictionaries');
    Route::get('/dictionaries/view/{aid}', 'viewDictionary')->name('admin.dictionaries.view');
    Route::get('/dictionaries/create', 'createDictionary')->name('admin.dictionaries.create');
    Route::get('/dictionaries/edit/{aid}', 'editDictionary')->name('admin.dictionaries.edit');
});