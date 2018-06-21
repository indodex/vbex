<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\ApiController as Controller;

use App\Redis\KlineTime;
use App\Redis\Kline1min;
use App\Redis\Kline5min;
use App\Redis\Kline15min;
use App\Redis\Kline30min;
use App\Redis\KlineDay;
use App\Redis\KlineHour;
use App\Redis\Kline6Hour;
use App\Redis\KlineWeek;

use App\Repositories\KlineRepository;

class KlineBakController extends Controller
{

    public $markets = ['BTC_USDT', 'ETH_USDT', 'EOS_USDT', 'EOS_BTC', 'ETH_BTC'];
    public $types = ['1min', '5min', '15min', '30min', '1day', '1week','1hour','6hour'];

    public function __construct() 
    {
        parent::__construct();
    }

    public function kline(Request $request)
    {
        $klineModel = '';
        $klineRepository = $this->getKlineRepository();
        foreach ($this->markets as $market) {
            foreach ($this->types as $type) {
                switch ($type) {
                    case '1min':
                        $klineModel = $this->getKline1min();
                        break;

                    case '5min':
                        $klineModel = $this->getKline5min();
                        break;

                    case '15min':
                        $klineModel = $this->getKline15min();
                        break;

                    case '30min':
                        $klineModel = $this->getKline30min();
                        break;

                    case '1day':
                        $klineModel = $this->getKlineDay();
                        break;

                    case '1week':
                        $klineModel = $this->getKlineWeek();
                        break;

                    case '1hour':
                        $klineModel = $this->getKlineHour();
                        break;

                    case '6hour':
                        $klineModel = $this->getKline6Hour();
                        break;
                    
                    default:
                        $klineModel = $this->getKlineTime();
                        break;
                }

                $params['needTickers'] = 1;
                $params['symbol']      = str_replace('_', '', strtolower($market)) ;
                $params['size']        = 1000;
                if($type) {
                    $params['type']    = $type;
                }

                $klines = $klineRepository->getKline($params);
                // $market = trim(strtoupper($market), 'T');

                if(empty($klines['datas'])) {
                    var_dump ($market);
                    exit;
                }

                foreach ($klines['datas']['data'] as $kline) {
                    foreach ($kline as $key => &$kl) {
                        $kl = (string) $kl;
                    }
                    $klineModel->addKline(strtoupper($market), json_encode($kline));
                }
            }
        }
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
