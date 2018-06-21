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


Route::get('/user', function (Request $request) {
    $user = auth()->guard('api')->user();
    return response()->json($user);
})->middleware('auth:api');




// Route::get('/posts','PostsController@index');
// Route::get('/posts/{post}','PostsController@show');

Route::post('/register','Api\RegisterController@register');
Route::post('/logout','Api\LoginController@logout');
Route::post('/token/refresh','Api\LoginController@refresh');
Route::post('/password/email','Api\ForgotPasswordController@sendResetLinkEmail');
// Route::post('/password/reset','Api\ResetPasswordController@reset');
// Route::post('/auth','Api\LoginController@login');
Route::post('/login','Api\LoginController@login')->name('login');
Route::get('/land', 'Api\PublicController@getLang');

Route::group([
	'middleware' => ['auth:api', 'language'], 
	'prefix' => 'v1', 
	'namespace' => 'Api'], 
	function()
{
    Route::group(['prefix' => 'trades'], function()
	{
		Route::get('/complete', 'TradesController@complete');

	    Route::post('/doEntrust', 'EntrustController@doEntrust');
	    Route::post('/cancel', 'EntrustController@cancelEntrust');
	    Route::post('/check/tradeCode', 'EntrustController@checkTrade');
	    Route::post('/check/isTrade', 'EntrustController@isTrade');
	});

    Route::group(['prefix' => 'recharge'], function()
	{
	    Route::post('/code/used/offical', 'RechargeController@officialCodeUsed');
	    Route::post('/code/used/personal', 'RechargeController@personalCodeUsed');
	    Route::post('/code/used/check', 'RechargeController@check');
	    Route::post('/code/use', 'RechargeController@useHashCode');
	    Route::post('/code/created', 'RechargeController@create');
	    Route::post('/code/show', 'RechargeController@showCode');

	    Route::get('/code/list', 'RechargeController@getCodes');
	    Route::get('/items', 'RechargeController@items');
	});

    Route::group(['prefix' => 'wallet'], function()
	{
	    Route::get('/account', 'WalletController@account');
	    Route::get('/deposit/records', 'WalletController@depostRecords');
	    Route::get('/withdraw/addresses', 'WalletController@withdrawAddresses');
	    Route::get('/withdraw/address', 'WalletController@getWithdrawAddress');
	    Route::get('/withdraw/records', 'WalletController@withdrawRecords');
	    Route::get('/balance', 'AccountController@walletBalance');

	    Route::post('/withdraw/apply', 'WalletController@withdrawApply');
	    Route::post('/withdraw/addresses', 'WalletController@addAddress');
	    Route::post('/withdraw/delete', 'WalletController@delAddress');
	});

    Route::group(['prefix' => 'account'], function()
	{
	    Route::get('/deposits/address', 'DepositsController@address');
	    Route::get('/balance', 'AccountController@balance');
	    Route::get('/allPrice', 'AccountController@getAllPrice');
		Route::get('/bill', 'AccountController@bill');
		Route::get('/info', 'AccountController@info');
		Route::get('/invite', 'AccountController@inviteUser');
		Route::get('/rewards', 'AccountController@userRewards');
		Route::get('/entrust', 'TradesController@entrust');
		Route::get('/getGoogleSecret', 'MemberController@getGoogleSecret');

    	Route::post('/bindGoogleSecret', 'MemberController@bindGoogleSecret');
    	Route::post('/changeGoogleSecret', 'MemberController@changeGoogleSecret');
    	Route::post('/forgetGoogleSecret', 'MemberController@forgetGoogleSecret');
    	Route::post('/verifyGoogleCode', 'MemberController@verifyGoogleCode');
    	Route::post('/resetTradeCode', 'MemberController@resetTradeCode');
    	Route::post('/setTradeCode', 'MemberController@setTradeCode');
    	Route::post('/deductible', 'AccountController@deductible');
    	Route::post('/uploadIamge','UploadController@uploadIamge');
    	Route::post('/baseCertification','MemberController@baseCertification');
    	Route::post('/advancedCertification','MemberController@advancedCertification');
    	Route::get('/getCerInfo','MemberController@getCerInfo');

    	Route::post('/retrieveTradcode', 'MemberController@retrieveTradcode');
    	Route::get('/sendEmailCode', 'PublicController@sendEmailCode');
    	Route::get('/checkEmailLock', 'PublicController@checkEmailLock');
    	Route::get('/sendVerifyCode', 'PublicController@sendVerifyCode');
	});

	Route::group(['prefix' => 'hash'], function()
	{
		Route::get('/record', 'HashController@hashRecord');
		Route::get('/userRechargeOrder', 'HashController@userRechargeOrder');
		Route::get('/systemRechargeOrder', 'HashController@systemRechargeOrder');
		Route::get('/checkBalance', 'AccountController@checkBalance');
		Route::get('/hashDetail', 'HashController@hashDetail');

		Route::post('/createHashCode', 'HashController@createHashCode');
	});

    Route::group(['prefix' => 'user'], function()
	{
	    Route::get('/info', 'MemberController@getBaseInfo');
	    
	    Route::post('/password/rest', 'PasswordController@resetPassword');
	    Route::post('/change/mobile', 'MobileController@change');
	    Route::post('/email/code', 'EmailController@send');
	    Route::post('/email/verify', 'EmailController@verify');
	    Route::post('/send/code', 'MobileController@sendToCode');
	    Route::post('/change/name', 'MemberController@changeName');
	    Route::post('/changeLoginSafeOption', 'MemberController@changeLoginSafeOption');
	    Route::post('/changeTradCodeOption', 'MemberController@changeTradCodeOption');
	    Route::post('/changeWithdrawalOption', 'MemberController@changeWithdrawalOption');
	});

	Route::post('/send/withdraw/confirm', 'EmailController@sendWithdrawConfirm');

	Route::group(['prefix' => 'public'], function()
	{
	    Route::get('/safe/getLoginOption', 'PublicController@getSafeOption');
	    Route::get('/safe/getTradeOption', 'PublicController@getTradeOption');
	    Route::get('/safe/getWithdrawalOption', 'PublicController@getWithdrawalOption');
	});

	Route::group(['prefix' => 'coin'], function()
	{
	   Route::post('/apply', 'CoinAppyController@index');
	});
});

Route::group([
	'middleware' => ['web', 'language'], 
	'prefix' => 'v1', 
	'namespace' => 'Api'], 
	function()
{
    Route::group(['prefix' => 'trades'], function()
	{
    	Route::get('/entrust', 'TradesController@entrustList');
    	Route::get('/entrustKline', 'TradesController@entrustListKline');
	    Route::get('/volume', 'TradesController@volume');
	    Route::get('/setCoin', 'TradesController@setCoinUnit');
	    Route::get('/last/price', 'TradesController@lastPrice');
	    Route::get('/types', 'TradesController@entrustTypes');
	    Route::get('/status', 'TradesController@entrustStatus');

	    Route::get('/rates', 'BitcoinController@getRates');
	    Route::get('/rate', 'BitcoinController@getRate');
	    Route::get('/length', 'TradesController@getLengthDepth');
	    Route::get('/getAllPrice', 'TradesController@getAllPrice');
	    Route::get('/details', 'TradesController@getTradesDetails');
	});

	Route::group(['prefix' => 'wallet'], function()
	{
		Route::get('/withdraw/fee', 'WalletController@withdrawFee');
	});

	Route::group(['prefix' => 'mobile'], function()
	{
		Route::post('/send/code', 'MobileController@codeSend');
		Route::post('/check/code', 'MobileController@checkCode');
	});

    Route::group(['prefix' => 'market'], function()
	{
	    Route::get('/coins', 'MarketController@coins');
    	Route::get('/currencies', 'MarketController@currencies');
    	Route::get('/trade/types', 'MarketController@tradeTypes');
    	Route::get('/all', 'MarketController@all');
    	Route::get('/account', 'MarketController@getAccount');
    	Route::get('/account/balance', 'MarketController@getAccountBalance');
    	Route::get('/price/all', 'MarketController@getAllPrice');
    	
    	Route::get('/ticker', 'KlineController@getTicker');
    	Route::get('/getLastTrades', 'KlineController@getLastTrades');
    	Route::get('/klineLastData', 'KlineController@index');
    	Route::get('/getMarkets', 'MarketController@getMarkets');
    	Route::get('/getMarketsCurrency', 'MarketController@getMarketsCurrency');
	});

	Route::group(['prefix' => 'kline'], function()
	{
	    Route::get('/come', 'KlineBakController@kline');
	});

	Route::group(['prefix' => 'article'], function()
	{
	    Route::get('/news/list', 'NewsController@index');
	    Route::get('/news/categories', 'NewsController@getCategories');
	    Route::get('/news/content', 'NewsController@content');
	    Route::get('/aboutus', 'NewsController@aboutUs');
	    Route::get('/terms', 'NewsController@terms');
	    Route::get('/fees', 'NewsController@fees');
	    Route::get('/privacy', 'NewsController@privacy');
	    Route::get('/contac', 'NewsController@contac');
	    Route::get('/applyList', 'NewsController@applyList');
	});

	Route::group(['prefix' => 'withdraw'], function()
	{
	    Route::get('/confirm', 'WalletController@withdrawConfirm');
	});

	Route::group(['prefix' => 'member'], function()
	{
	    Route::post('/loginSafeOption', 'LoginController@loginSafeOption');
	    Route::post('/sendVerifyCode', 'LoginController@sendVerifyCode');
	});
	
	Route::group(['prefix' => 'config'], function()
	{
	    Route::get('/initialize', 'ConfigController@index');
	});

	Route::get('/coin/show', 'CoinAppyController@show');
	Route::get('/coin/list', 'CoinAppyController@lists');
});