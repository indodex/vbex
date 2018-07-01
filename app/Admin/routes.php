<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->resource('user', UserController::class);
    $router->resource('land', LanguageController::class);
    $router->get('setting', 'SettingController@index');
    $router->post('setting', 'SettingController@store');

    // $router->get('language', 'LanguageController@index');
    // $router->get('language/create', 'LanguageController@create');
    $router->get('cer', 'UserController@certification');
    $router->resource('categories', CategoriesController::class);
    $router->resource('categories_en', CategoriesController::class);
    $router->resource('categories_tw', CategoriesController::class);
    $router->resource('news', NewsController::class);
    $router->resource('news_en', NewsController::class);
    $router->resource('news_tw', NewsController::class);
    $router->resource('buttommenu', ButtomMenuController::class);
    $router->resource('buttommenu_en', ButtomMenuController::class);
    $router->resource('buttommenu_tw', ButtomMenuController::class);
    //$router->get('admin/news/{news}/edit/{land?}', 'NewsController@edit');
    $router->resource('advert', AdvertController::class);
    $router->get('currency/wallet', 'CurrencyController@wallet');
    $router->resource('currency', CurrencyController::class);
    // $router->resource('money', LegalMoneyController::class);

    $router->get('recharge/apply', 'RechargeCodeController@apply');
    $router->put('recharge/apply', 'RechargeCodeController@postApply');
    $router->resource('recharge', RechargeCodeController::class);

    $router->put('audit/apply', 'RechargeUsersController@auditApply');
    $router->resource('rechargeUsers', RechargeUsersController::class);

    $router->resource('items', RechargeItemsController::class);

    // 提现记录
    $router->get('withdraws/currency', 'WithdrawsController@currency');
    $router->put('withdraws/currency', 'WithdrawsController@postCurrency');
    
    // 用户充值记录
    $router->get('deposits/currency', 'DepositsController@currency');
    $router->put('deposits/currency', 'DepositsController@postCurrency');
    $router->get('deposits/artificial_list', 'DepositsController@artificialRechargeList');
    $router->get('deposits/artificial_list/create', 'DepositsController@artificialRecharge');
    $router->post('deposits/artificial', 'DepositsController@postArtificialRecharge');

    // 补单操作
    $router->resource('deposits', DepositsController::class);

    // 交易
    $router->get('trades/entry', 'TradesController@entry');
    $router->get('trades/buy', 'TradesController@buy');
    $router->get('trades/sell', 'TradesController@sell');

    // 交易区
    $router->resource('markets', MarketsController::class);
    $router->resource('exchanges', ExchangesController::class);
    // $router->resource('tradesCurrency', TradesCurrencyController::class);

    $router->get('statistics/index', 'StatisticsController@index');
    $router->get('statistics/withdraws', 'StatisticsController@withdraws');
    $router->post('statistics/withdraws', 'StatisticsController@withdraws');
    $router->get('statistics/deposits', 'StatisticsController@deposits');
    $router->post('statistics/deposits', 'StatisticsController@deposits');
    $router->get('statistics/getCurrency', 'StatisticsController@getCurrency');
    $router->put('certification', 'UserController@toCertification');


    $router->get('stats/hac', 'HacChartController@index');
    $router->get('stats/hac_user', 'HacChartController@userHac');
    $router->get('stats/login', 'UserChartController@login');
    $router->get('stats/register', 'UserChartController@registered');
    $router->get('stats/withdraws', 'StatisticsController@withdraws');
    $router->get('stats/deposits', 'StatisticsController@deposits');
    $router->get('stats/trading', 'TradingChartController@trading');
    $router->get('stats/poundage', 'TradingChartController@poundage');

    
    $router->get('hash/setting', 'HashController@index');
    $router->post('hash/setting', 'HashController@store');

     // 用户充值记录
    $router->get('deposits/currency', 'DepositsController@currency');
    $router->put('deposits/currency', 'DepositsController@postCurrency');
    $router->get('deposits/artificial_list', 'DepositsController@artificialRechargeList');
    $router->get('deposits/artificial_list/create', 'DepositsController@artificialRecharge');
    $router->post('deposits/artificial', 'DepositsController@postArtificialRecharge');

    // 补单操作
    $router->resource('deposits', DepositsController::class);

    // 上币审核
    $router->put('coinsApply', CoinsApplyController::class.'@postApply');
    $router->resource('coinsApply', CoinsApplyController::class);
    $router->resource('replenish', ReplenishController::class);
});
