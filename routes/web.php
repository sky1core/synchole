<?php

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
    return view('welcome');
});

Route::prefix('auth')->group(function() {
    Route::get('gate/{level?}', 'Auth\LoginController@gate');
    Route::get('login', 'Auth\LoginController@login')->name('login');
    Route::get('login/callback', 'Auth\LoginController@loginCallback');
    Route::get('logout', 'Auth\LoginController@logout');
    Route::get('check', function() {
        echo 'OK';
    })->middleware('auth');

});

