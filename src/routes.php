<?php

Route::group([

    'namespace' => 'Helious\SeatAccountStatus\Http\Controllers',
    'prefix' => 'test',
    'middleware' => [
        'web',
        'auth'
    ],
], function()
{

    Route::get('/account-status', [
        'uses' => 'TestAccountStatusController@index',
        'as' => 'seat-account-status::index',
    ]);

});