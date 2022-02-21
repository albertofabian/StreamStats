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
    return view('index');
});

Route::get('hola', 'TwitchController@hola');

Route::get('dashboard', 'TwitchController@ShowAllResults');
Route::get('usertoken', 'TwitchController@getUserToken');
Route::get('set_user',  'TwitchController@getAndSaveUser');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
