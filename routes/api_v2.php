<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// api/posts

Route::group([
    'prefix' => 'v2',
    'namespace' => 'Api\V2'], function() {

        Route::group(['prefix' => 'public'], function()
        {
            Route::get('server-time', 'PublicController@serverTime');

            Route::get('currencies', 'PublicController@currencies');

            Route::get('symbols', 'PublicController@symbols');
        });

        Route::group(['prefix' => 'market'], function()
        {
            Route::get('ping', 'MarketController@ping');

            Route::get('ticker', 'MarketController@ticker');

            Route::get('depth', 'MarketController@depth');

            Route::get('trades', 'MarketController@trades');
        });
});