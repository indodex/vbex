<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\ApiController as Controller;

use App\Services\Accounts;
use App\Services\Currency;
use App\Redis\KlineHour;
use App\Redis\Kline6Hour;
use App\Redis\Kline1min;
use App\Redis\Tickers;
use App\Models\ExchangeRatesModel;

use App\Models\TradesCurrenciesModel;
use App\Models\MarketsModel;
use App\Models\CurrencyModel;

use Illuminate\Support\Facades\Storage as Image;

class MarketController extends Controller
{

    public function __construct() 
    {
        parent::__construct();
    }

    // 我的资产
    public function coins(Request $request)
    {

        $token = $request->input('api_token', null);
        $lang  = $request->input('lang', null);
        $uid   = $this->uid;

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $result = $this->getCurrencyService()->getVirtualCurrencies();

        $list = array();
        if($result['status'] == 1) {
            foreach ($result['data'] as $key => $value) {
                if(!$value['enable_deposit'])
                    continue;
                $keyCode                                   = strtolower($value['code']);
                $list[$keyCode]['coin']                    = $value['code'];
                $list[$keyCode]['name']                    = $value['name'];
                $list[$keyCode]['enable_deposit']          = $value['enable_deposit'];
                $list[$keyCode]['enable_withdraw']         = $value['enable_withdraw'];
                $list[$keyCode]['min_trading_val']         = my_number_format($value['min_trading_val'], $value['decimals']);
                $list[$keyCode]['min_withdraw_amount']     = my_number_format($value['min_withdraw_amount'], $value['decimals']);
                $list[$keyCode]['trading_service_rate']    = my_number_format($value['trading_service_rate'], $value['decimals']);
                $list[$keyCode]['withdraw_service_charge'] = my_number_format($value['withdraw_service_charge'], $value['decimals']);
                if($value['logo']) {
                    $list[$keyCode]['logo']                = Image::disk('public')->url($value['logo']);
                } else {
                    $list[$keyCode]['logo']                = '';
                }
            }
        }

        if(!empty($list)) {
            $reutrnData['list'] = $list;
            $reutrnData['timestamp'] = time();
            return $this->setStatusCode(200)
                        ->responseSuccess($reutrnData, 'success');
        } else {
            return $this->setStatusCode(404)
                        ->responseError(__('api.public.empty_data'));
        }
    }

    // 当前所有货币
    public function currencies(Request $request)
    {
        $token = $request->input('api_token', null);
        $lang = $request->input('lang', null);

        $result = $this->getCurrencyService()->getRealCurrencies();

        $list = array();
        if($result['status'] == 1) {
            foreach ($result['data'] as $key => $value) {
                $list[] = [$value['code'], $value['symbol']];
            }
        }

        if(!empty($list)) {
            $reutrnData['list'] = $list;
            $reutrnData['timestamp'] = time();
            return $this->setStatusCode(200)
                        ->responseSuccess($reutrnData, 'success');
        } else {
            return $this->setStatusCode(404)
                        ->responseError(__('api.public.empty_data'));
        }
    }

    public function tradeTypes()
    {
        $reutrnData['list'] = [
                'all'      => '全部',
                'deposit'  => '充值',
                'trading'  => '交易',
                'withdraw' => '提现',
                'other'    => '其他',
            ];
        return $this->responseSuccess($reutrnData, 'success');
    }

    // 交易市场
    public function all()
    {
        $result = $this->getCurrencyService()->getMarkets();
        if($result['status'] == 1) {
            $reutrnData['list'] = $result['data'];
            return $this->responseSuccess($reutrnData, 'success');
        } else {
            return $this->setStatusCode(404)
                        ->responseError(__('api.public.empty_data'));
        }
    }

    // 获取市场账号
    public function getAccount(Request $request)
    {
        $uid = $this->getUserId();
        $market = $request->input('market');

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        if(empty($market)) {
            return $this->setStatusCode(404)->responseError(__('api.market.market_empty'));
        }
        
        $market = strtoupper($market);
        $currencies = explode('_', $market);
        $result = $this->getCurrencyService()->balance($uid, $currencies[0], $currencies[1]);
        if($result['status'] == 1) {
            return $this->responseSuccess($result['data'], 'success');
        } else {
            return $this->responseSuccess([
                'money' => array('balance' => '--', 'balanceUnit' => $currencies[0], 'buy' => '--', 'buyCoin' => $currencies[1]),
                'symbol' => array('balance' => '--', 'balanceUnit' => $currencies[1], 'sell' => '--', 'sellCoin' => $currencies[0]),
            ]);
        }
    }

    // 获取市场账号余额
    public function getAccountBalance(Request $request)
    {
        $uid = $this->getUserId();
        $market = $request->input('market');

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        if(empty($market)) {
            return $this->setStatusCode(404)->responseError(__('api.market.market_empty'));
        }

        $market = strtoupper($market);
        $currencies = explode('_', $market);
        $result = $this->getCurrencyService()->accountBalance($uid, $currencies[0], $currencies[1]);
        if($result['status'] == 1) {
            return $this->responseSuccess($result['data'], 'success');
        } else {
            return $this->responseSuccess([
                'money' => array('balance' => '--', 'balanceUnit' => $currencies[0], 'buy' => '--', 'buyCoin' => $currencies[1]),
                'symbol' => array('balance' => '--', 'balanceUnit' => $currencies[1], 'sell' => '--', 'sellCoin' => $currencies[0]),
            ]);
        }
    }

    public function getAllPrice()
    {
        $data['datas'] = $this->getTickersRds()->getAllPrice();

        $data['usdtcny'] = $this->getExchangeRatesModel()->getRateByMarketNew('USD_CNY');
        if ($data['datas']) {
            return response()->json($data);
        }
        return $this->setStatusCode(404)
                        ->responseError(__('api.public.empty_data'));
    }

    public function getMarkets(Request $request)
    {
        $isChildren = (int)$request->input('isChildren',0);
        $oCurrency = $this->getTradesCurrenciesModel()->getAllMarketCurrencies();
        $aCurrencies = [];
        foreach ($oCurrency as $key => $m) {
            $row['mId'] = $m->exchange_currency;
            $row['market'] = $m->exchangeCurrency->code;
            
            if ($isChildren) {
                $row['currencies'] = $this->_getCurrenciesByMarkets($m->exchange_currency,$m->exchangeCurrency->code);
            }
            $aCurrencies[] = $row;
        }
        $reutrnData['data'] = $aCurrencies;
        return $this->setStatusCode(200)
                        ->responseSuccess($reutrnData, 'success');
    }

    public function _getCurrenciesByMarkets($mId,$code)
    {
        $currencies = $this->getTradesCurrenciesModel()->getBuyExchange($mId);
        $result = [];
        if ($currencies) {
            foreach ($currencies as $key => $value) {
                $row['id'] = $value->main_currency;
                $row['logo'] = $value->mainCurrency->logo ? '/uploads/'.$value->mainCurrency->logo : '';
                $row['code'] = $value->mainCurrency->code;
                $row['symbol'] = $value->mainCurrency->code.'/'.$code;
                $row['market'] = $value->mainCurrency->code.'_'.$code;
                $result[] = $row;
            }
        }
        return $result;
    }

    public function getMarketsCurrency(Request $request)
    {
        $mId     = (int)$request->input('mId',0);
        $isKline = (int)$request->input('isKline',0);
        $size    = (int)$request->input('size',50);

        if ($mId) {
            $cond['exchange_currency'] = $mId;
        }
        
        $cond['status']            = 1;
        $Markets = $this->getTradesCurrenciesModel()->getAll($cond);
        $list = [];

        $market = 'USD_CNY';
        $cnyToUsd = 6.7;
        $ExchangeRates = ExchangeRatesModel::where(['market'=>$market])->first();
        if ($ExchangeRates) {
            $cnyToUsd = my_number_format($ExchangeRates->price,4);
        }
        foreach ($Markets as $key => $value) {

            $symbol   = $value->mainCurrency->code.'_'.$value->exchangeCurrency->code;
            $newPrice = $this->getKline1minRds()->getKline($symbol, 0, 0);
            $row['marketToUsd'] = 0;
            $row['cnyToUsd']  = $cnyToUsd;

            if (!in_array($value->exchangeCurrency->code,array('USD','CNY'))) {
                $symbol_usd   = $value->exchangeCurrency->code.'_USD';
                $PriceToUsd   = $this->getKline1minRds()->getKline($symbol_usd, 0, 0);

                if ($PriceToUsd) {
                    $PriceToUsd       = json_decode($PriceToUsd[0], true);
                    $row['marketToUsd'] = $PriceToUsd[4];
                }
            }

            if($value->exchangeCurrency->code == 'USD'){
                $row['marketToUsd'] = 1;
            }

            if($value->exchangeCurrency->code == 'CNY' && $ExchangeRates){
                $row['marketToUsd'] = my_number_format($ExchangeRates->price,4);
            }
            
            $row['symbol']   = $value->mainCurrency->code.'/'.$value->exchangeCurrency->code;
            $row['mainCurrency'] = $value->main_currency;
            $row['exchangeCurrency'] = $value->exchange_currency;
            $row['loge']     = $value->logo ? $value->logo : '' ;
            $row['price']    = 0;
            $row['riseRate'] = '0%';
            $row['volume']   = 0;
            $row['height']   = 0;
            $row['low']      = 0;

            if ($newPrice) {
                $newPrice        = json_decode($newPrice[0], true);
                $row['price']    = $newPrice[4];
                $row['height']   = $newPrice[2];
                $row['low']      = $newPrice[3];
                $row['riseRate'] = my_number_format(($newPrice[2]-$newPrice[1])/$newPrice[1]*100,4).'%';
                $row['volume']   = $newPrice[5];
            }

            if ($isKline == 1) {
                $kline = $this->getKline6HourRds()->getKline($symbol, 0, $size-1);
                // echo $symbol.'-'.count($kline).'    ';
                if ($kline) {
                    $klines   = [];
                    foreach ($kline as $key => $val) {
                        $line     = json_decode($val, true);
                        $line[0]  = (int) $line[0];
                        $line[1]  = (float) $line[1];
                        $line[2]  = (float) $line[2];
                        $line[3]  = (float) $line[3];
                        $line[4]  = (float) $line[4];
                        $line[5]  = (float) $line[5];
                        $klines[] = $line;
                    }
                    $row['kline'] = $klines;
                }else{
                    $row['kline'] = '';
                }
            }
            
            
            
            $list[$value->exchangeCurrency->code][] = $row;
        }
        // exit;
        if ($list) {
            $reutrnData['data'] = $list;
            return $this->setStatusCode(200)
                            ->responseSuccess2($reutrnData, 'success');
        }

        return $this->setStatusCode(404)
                        ->responseError(__('api.public.empty_data'));
        
    }

    private function getKline1minRds()
    {
        return new Kline1min();
    }
    public function getKline6HourRds()
    {
        return new KlineHour();
    }

    public function getAccountsService()
    {
        return new Accounts();
    }

    public function getCurrencyService()
    {
        return new Currency();
    }

    public function getTickersRds()
    {
        return new Tickers();
    }

    public function getExchangeRatesModel()
    {
        return new ExchangeRatesModel();
    }

    public function getTradesCurrenciesModel()
    {
        return new TradesCurrenciesModel();
    }
}
