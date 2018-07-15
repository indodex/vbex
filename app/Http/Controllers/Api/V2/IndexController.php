<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\CurrencyModel;

use App\Http\Controllers\ApiController as Controller;

use App\Repositories\KlineRepository;

class IndexController extends Controller
{

    public $markets = ['BTC_USDT', 'ETH_USDT', 'EOS_USDT', 'EOS_BTC', 'ETH_BTC'];
    public $types = ['1min', '5min', '15min', '30min', '1day', '1week','1hour','6hour'];

    public function __construct() 
    {
        parent::__construct();
    }

    public function mining(Request $request)
    {
        return $this->responseSuccess([
            'earnings_mine' => '100000.100',
            'earnings_wait' => '100000.100',
            'earnings_vb_every' => '0.1345',
            'earnings_vb_static' => '0.1345',
            'earnings_vb_profit' => '0.33',
            'earnings_current' => '800.13454545',
            'earnings_current_vb' => '0.1345',
            'sign' => '฿'
        ]);
    }

    public function currencies(Request $request, KlineRepository $kline)
    {
        $currencyModel = new CurrencyModel();
        $cond['status'] = 1;
        $currencies = $currencyModel->getCurrenciesByType(1);

        $list = [];
        foreach ($currencies as $coin){
            $price = $kline->price($coin->code);
            if(!empty($price)) {
                $list[] = [
                    "sign" => "$",
                    "code" => $coin->code,
                    "price" => $price['price'],
                    "change" => $price['change_daily'],
                ];
            } else {
                $list[] = [
                    "sign" => "$",
                    "code" => $coin->code,
                    "price" => 0,
                    "change" => 0,
                ];
            }

        }
        return $this->responseSuccess($list);
    }

    private function getKlineRepository()
    {
        return new KlineRepository();
    }

    private function getKlineTime()
    {
        return new KlineTime();
    }

    private function getKline1min()
    {
        return new Kline1min();
    }

    private function getKline5min()
    {
        return new Kline5min();
    }

    private function getKline15min()
    {
        return new Kline15min();
    }

    private function getKline30min()
    {
        return new Kline30min();
    }

    private function getKlineDay()
    {
        return new KlineDay();
    }

    private function getKlineHour()
    {
        return new KlineHour();
    }

    private function getKline6Hour()
    {
        return new Kline6Hour();
    }

    private function getKlineWeek()
    {
        return new KlineWeek();
    }
}
