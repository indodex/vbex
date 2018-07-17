<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Redis\KlineHour;
use App\Redis\Kline6Hour;
use App\Redis\Kline1min;
use App\Services\TradesFocusOn;
use App\Models\ExchangeRatesModel;
use App\Models\TradesCurrenciesModel as TradesModel;
use App\Http\Controllers\ApiController as Controller;

class TradeFocusController extends Controller
{
    public function __construct() 
    {
        parent::__construct();
    }

    /**
     * 我的关注
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user    = $request->user();
        if(empty($user->id)) {
            return $this->setStatusCode(401)->responseNotFound('Unauthorized.');
        }

        $mId     = (int)$request->input('mId',0);
        $isKline = (int)$request->input('isKline',0);
        $size    = (int)$request->input('size',50);

        if ($mId) {
            $cond['exchange_currency'] = $mId;
        }

        $tradesFocusOn = $this->getTradesFocusOn();
        $tradeModel = $this->getTrades();

        $focusIds = $tradesFocusOn->getFocusTradeIds($user->id);

        if(empty($focusIds)) {
            return $this->setStatusCode(404)->responseNotFound('Not Found.');
        }

        $Markets = $tradeModel->whereIn('id', $focusIds)
                              ->where('status', 1)->get();
//        $Markets = $tradeModel->getAll($cond);
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

            $list[] = $row;
        }
        // exit;
        if ($list) {
            $reutrnData = $list;
            return $this->responseSuccess2($reutrnData, 'success');
        }

        return $this->setStatusCode(404)->responseNotFound('Not Found.');

    }

    public function focus(Request $request, TradesModel $trade)
    {

        $user = $request->user();
        if(empty($user->id)) {
            return $this->setStatusCode(401)->responseNotFound('Unauthorized.');
        }

        $state = $request->input('state', 'follow');

        $result = $this->getTradesFocusOn()->focusOn((int) $user->id, (int) $trade->id, $state);

        if(!empty($result)) {
            return $this->responseSuccess('success');
        } else {
            return $this->setStatusCode(404)->responseNotFound('Not Found.');
        }

    }

    private function getKline1minRds()
    {
        return new Kline1min();
    }
    public function getKline6HourRds()
    {
        return new KlineHour();
    }

    private function getTradesFocusOn()
    {
        return new TradesFocusOn();
    }

    private function getTrades()
    {
        return new TradesModel();
    }

    public function getExchangeRatesModel()
    {
        return new ExchangeRatesModel();
    }
}
