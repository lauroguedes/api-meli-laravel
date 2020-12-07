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

Route::get('meli/login', 'MeliController@redirectToProvider')->name('meli.login');
Route::get('meli/callback', 'MeliController@handleProviderCallback');
Route::post('meli/notify', 'MeliController@meliNotify');

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');