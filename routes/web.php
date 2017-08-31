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
    $data = [
        'event' => 'UserSignedUp',
        'data'  => [
            'username' => 'John Doe'
        ]
    ];
    //Redis::publish('test-channel', json_encode($data));
    event( new \App\Events\TestEventRedis() );
    //return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
