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
use App\Redis\KlineHour;
use App\Redis\Kline6Hour;
use App\Redis\KlineDay;
use App\Redis\KlineWeek;

use App\Repositories\KlineRepository;
use App\Repositories\ZhongbiRepository;

use App\Services\Trades;

class KlineController extends Controller
{

    public function __construct() 
    {
        parent::__construct();
    }

    // K线
    public function index(Request $request)
    {
        $needTk = (int) $request->input('needTickers');
        $type   = (string) $request->input('type');
        $size   = (int) $request->input('size', 1000);
        $symbol = (string) $request->input('symbol');
        $since  = (int) $request->input('since');

        if(empty($symbol)) {
            return $this->setStatusCode(404)
                        ->responseError(__('api.market.market_empty'));
        }
 
        switch ($type) {
            case '1min':
                $kline = $this->getKline1minRds();
                break;

            case '5min':
                $kline = $this->getKline5minRds();
                break;

            case '15min':
                $kline = $this->getKline15minRds();
                break;

            case '30min':
                $kline = $this->getKline30minRds();
                break;

            case '1hour':
                $kline = $this->getKlineHourRds();
                break;

            case '6hour':
                $kline = $this->getKline6HourRds();
                break;

            case '1day':
                $kline = $this->getKline1dayRds();
                break;

            case '1week':
                $kline = $this->getKline1weekRds();
                break;
            
            default:
                $kline = $this->getKline1minRds();
                break;
        }

        $symbol = strtolower($symbol);
        $symbol = $this->getMarket($symbol);
        $max    = $kline->getllen($symbol);
        $start  = $max > 1000 ? $max - 1000 : 0;
        $data   = $kline->getKline($symbol, $start, -1);

        if(empty($data)) {
            return $this->setStatusCode(404)
                        ->responseError(__('api.public.empty_data'));
        }
        
        $data      = array_map('json_decode', $data, [1]);
        $data      = json_encode($data);
        $data      = json_decode($data, true);
        $klines    = [];
        $newklines = [];

        foreach ($data as $key => $line) {
            $line[0]  = (int) $line[0];
            $line[1]  = (float) $line[1];
            $line[2]  = (float) $line[2];
            $line[3]  = (float) $line[3];
            $line[4]  = (float) $line[4];
            $line[5]  = (float) $line[5];
            $klines[] = $line;
            if($since > 0 && $since < $line[0]) {
                $newklines[] = $line;
            }
        }

        if($since > 0 && !empty($newklines)) {
            $klines = $newklines;
        } else if($since > 0) {
            $klines = end($klines);
        } else {
            $klines = $klines;
        }
        if($klines) {
            $returnData['USDCNY']       = '**';
            $returnData['contractUnit'] = '**';
            $returnData['marketName']   = '**';
            $returnData['data']         = array_values($klines);
            $returnData['moneyType']    = 'cny';
            $returnData['symbol']       = strtolower(str_replace('_', '', $symbol));

            return response()->json([
                'isSuc' => true,
                'des'   => "",
                'datas' => $returnData
            ]);
        } else {
            return $this->setStatusCode(404)
                        ->responseError(__('api.public.empty_data'));
        }
    }

    // 实时行情
    public function getTicker(Request $request)
    {
        $market = $request->input('market');
        $market = strtoupper($market);

        $ticker = $this->getKline1minRds()->getLastPrice($market);

        if(!empty($ticker)) {
            $ticker = current($ticker);
            $ticker = json_decode($ticker, true);

            // $price['date'] = $ticker[0];
            $price['startPrice'] = $ticker[1];
            $price['hightPrice'] = $ticker[2];
            $price['lowPrice']   = $ticker[3];
            $price['lastPrice']  = $ticker[4];
            $price['volume']     = $ticker[5];
            $price['riseRate']   = ($price['lastPrice'] - $price['startPrice'])/$price['startPrice'] * 100;
            $price['riseRate']   = my_number_format($price['riseRate'], 4);

            $result = $this->getTradesService()->getLastSalePrice($market);
            if($result['status'] == 1) {
                $price['type']  = $result['data']['type'];
            }

            return $this->responseSuccess(['ticker' => $price, 'date' => $ticker[0]], 'success');
        } else {
            return $this->setStatusCode(404)
                        ->responseError(__('api.public.empty_data'));
        }
    }

    public function getLastTrades(Request $request)
    {
        
        $lastTradeTid = $request->input('last_trade_tid');
        $symbol       = $request->input('symbol');
        $symbol       = strtolower($symbol);
        $symbol       = str_replace('_', '', $symbol);
        $data         = $this->getZhongbi()->getLastTrades($symbol, $lastTradeTid);

        if($data['data']) {
            return $this->responseSuccess($data['data']);
        } else {
            return $this->setStatusCode(404)
                        ->responseError(__('api.public.empty_data'));
        }
    }

    private function getKlineTimeRds()
    {
        return new KlineTime();
    }

    private function getKline1minRds()
    {
        return new Kline1min();
    }

    private function getKline5minRds()
    {
        return new Kline5min();
    }

    private function getKline15minRds()
    {
        return new Kline15min();
    }

    private function getKline30minRds()
    {
        return new Kline30min();
    }

    private function getKline1dayRds()
    {
        return new KlineDay();
    }

    private function getKlineHourRds()
    {
        return new KlineHour();
    }

    private function getKline6HourRds()
    {
        return new Kline6Hour();
    }

    private function getKline1WeekRds()
    {
        return new KlineWeek();
    }

    private function getZhongbi()
    {
        return new ZhongbiRepository();
    }

    public function getTradesService()
    {
        return new Trades();
    }
}
