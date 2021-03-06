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


Auth::routes();

Route::get('/', 'HomeController@startPage')
    ->name('startPage');
Route::get('/home', 'HomeController@index')
    ->name('home')
    ->middleware('auth');
Route::post('/connectUser', 'UserAjaxController@connectUser');
Route::post('/userSendMessage', 'UserAjaxController@sendMessage');
Route::post('/connectAgent', 'AgentAjaxController@connectAgent');
Route::post('/agentSendMessage', 'AgentAjaxController@sendMessage');
Route::post('/connectAgentUser', 'AgentAjaxController@connectAgentUser');
Route::post('/disconnectChat', 'AgentAjaxController@disconnectChat');