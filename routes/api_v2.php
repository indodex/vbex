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

            Route::get('candles/{resolution}/{symbol}', 'MarketController@candles');
        });

        Route::group(['prefix' => 'accounts'], function()
        {
            Route::get('balance', 'AccountsController@balance');
        });

        Route::group(['prefix' => 'orders','middleware' => ['auth:api']], function()
        {
            Route::post('/', 'OrdersController@store');

            Route::get('/', 'OrdersController@index');

            Route::get('/{order}', 'OrdersController@show');

            Route::patch('/{order_id}/submit-cancel', 'OrdersController@cancel');

            Route::get('/{order_id}/match-results', 'OrdersController@matchResult');
        });

        Route::group(['prefix' => 'index'], function()
        {
            Route::get('/mining', 'IndexController@mining');
            Route::get('/currencies', 'IndexController@currencies');
        });

        Route::group(['prefix' => 'focus', 'middleware' => ['auth:api']], function()
        {
            Route::get('/', 'TradeFocusController@index');

            Route::post('/{trade}', 'TradeFocusController@focus');
        });


});