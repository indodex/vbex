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

// Route::get('/', function () {
//     return view('home');
// });


// Route::group(['prefix' => 'admin'], function () {
//     Voyager::routes();
// });

Route::group(['middleware' => ['web', 'language']], function () {
    Auth::routes();
});

Route::any('/user/{all}', function () {
    return view('layouts.master');
})->where(['all' => '.*']);
Route::any('/user', function () {
    return view('layouts.master');
});

Route::any('/financial/{all}', function () {
    return view('layouts.master');
})->where(['all' => '.*']);
Route::any('/financial', function () {
    return view('layouts.master');
});

Route::any('/news/{all}', function () {
    return view('layouts.master');
})->where(['all' => '.*']);
Route::any('/news', function () {
    return view('layouts.master');
});

Route::any('/market/{all}', function () {
    return view('layouts.master');
})->where(['all' => '.*']);
Route::any('/market', function () {
    return view('layouts.master');
});

Route::get('/login', function () {
    return view('layouts.master');
})->name('login');
Route::get('/regist', function () {
    return view('layouts.master');
})->name('regist');
Route::get('/reset', function () {
    return view('layouts.master');
})->name('reset');
Route::get('/', function () {
    return view('layouts.master');
});

Route::any('/dailog/{all}', function () {
    return view('layouts.master');
})->where(['all' => '.*']);
Route::any('/dailog', function () {
    return view('layouts.master');
});

Route::get('/apply/{all}', function () {
    return view('layouts.master');
})->where(['all' => '.*']);

Route::get('/home', 'HomeController@index')->name('home');

Route::group(['namespace' => 'Web','middleware' => 'language'], function () {
    // Route::get('/{locale?}', function ($locale='tw') {
    //     if ($locale == 'tw'){
    //         return view('web.index_tw');
    //     } else {
    //         return view('web.index_tw');    
    //     }
        
    // });
    // Route::get('/', function () {
    //     return view('web.index_tw');
    // });
    
    Route::get('/kline', 'KlineController@index')->name('kline');
	// Route::get('/kline', function () {
	//     return view('Kline.kline');
	// });
	
});

Route::group([
    'middleware' => ['web', 'language'], 
    'namespace' => 'Api', 
    'prefix' => 'api'], function () {
    Route::get('/verifications', 'RegisterController@verifications')->name('register.verifications');
    Route::get('/verify/code', 'RegisterController@verifyCode')->name('register.verifyCode');
    Route::post('/register', 'RegisterController@register')->name('register.do');
    //Route::post('/login', 'LoginController@index')->name('login.index');

    // 个人中心
    // 用户信息
    Route::post('/baseInfo', 'MemberController@getBaseInfo')->name('member.baseInfo');
    // 充值选项
    Route::post('/rechargeOption', 'MemberController@getRechargeOption')->name('member.rechargeOption');
    // 
    // Route::get('/getGoogleSecret', 'MemberController@getGoogleSecret')->name('member.getGoogleSecret');
    // Route::post('/bindGoogleSecret', 'MemberController@bindGoogleSecret')->name('member.bindGoogleSecret');
    // Route::post('/verifyGoogleCode', 'MemberController@verifyGoogleCode')->name('member.verifyGoogleCode');

    Route::get('/collect','CollectController@index')->name('collect.index');
});

Route::group([
    'namespace' => 'Auth',
    'middleware' => ['web', 'language'], 
    // 'prefix' => 'api'
], function () {
    Route::post('/register', 'RegisterController@register')->name('register');
    Route::get('/register/{inviteUid}', 'RegisterController@showRegistrationForm')->name('InviteUidRegistration');
    Route::get('/findLoginPwd', 'ForgotPasswordController@findLoginPwd')->name('login.find.password');

});

Route::post('/login','Auth\LoginController@login');

Route::group([
    'middleware' => ['web', 'language'], 
    'namespace' => 'Api'], function () {
    Route::get('/depth', 'TradesController@entrustListKline');
    Route::get('/getLastTrades', 'TradesController@volume');
    Route::get('/line/topall', 'MarketController@getAllPrice');
});
