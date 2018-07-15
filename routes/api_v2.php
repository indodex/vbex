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

            Route::get('ticker/{symbol}', 'MarketController@ticker');

            Route::get('depth/{level}/{symbol}', 'MarketController@depth');

            Route::get('trades/{symbol}', 'MarketController@trades');
        });

        Route::group(['prefix' => 'accounts'], function()
        {
            Route::get('balance', 'AccountsController@balance');
        });

        Route::group(['prefix' => 'orders'], function()
        {
            Route::post('/', 'OrdersController@store');

            Route::get('/', 'OrdersController@index');

            Route::get('/{order_id}', 'OrdersController@show');

            Route::patch('/{order_id}/submit-cancel', 'OrdersController@cancel');

            Route::get('/{order_id}/match-results', 'OrdersController@match');
        });


        Route::group(['prefix' => 'index'], function()
        {
            Route::get('/mining', 'IndexController@mining');
            Route::get('/currencies', 'IndexController@currencies');
        });


});