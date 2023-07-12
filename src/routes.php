<?php

Route::group([

    'namespace' => 'Helious\SeatAccountStatus\Http\Controllers',
    'prefix' => 'account-status',
    'middleware' => [
        'web',
        'auth'
    ],
], function()
{

    Route::get('/{character_id}', [
        'uses' => 'TestAccountStatusController@index',
        'as' => 'seat-account-status::index',
    ]);

});