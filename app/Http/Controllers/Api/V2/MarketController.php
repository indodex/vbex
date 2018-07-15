<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

use App\Services\Currency;
use App\Services\Trades;
use App\Services\DepthService;
//use App\Redis\Kline1min;

use App\Redis\KlineTime;
use App\Redis\Kline1min;
use App\Redis\Kline5min;
use App\Redis\Kline15min;
use App\Redis\Kline30min;
use App\Redis\KlineHour;
use App\Redis\Kline6Hour;
use App\Redis\KlineDay;
use App\Redis\KlineWeek;

use App\Http\Controllers\Api\V2\ApiController as Controller;

class MarketController extends Controller
{
    /**
     * 获取推送服务器时间
     * GET api/v2/market/ping
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ping(Request $request)
    {
        $nowTime = $request->input('now_time');
        $ctime = time();
        $gap = $ctime - $nowTime;
        return $this->responseSuccess([
            "type" => 'ping',
            "ts" => $ctime,
            "gap" => $gap
        ]);
    }

    /**
     * 获取 ticker 数据
     * GET api/v2/market/ticker
     * @param Request $request
     * @param string symbol 市场交易对
     * @return \Illuminate\Http\JsonResponse
     */
    public function ticker(Request $request)
    {
        $symbol = $request->route('symbol');

        if(empty($symbol)) {
            return $this->setStatusCode(406)->responseError('Not Acceptable');
        }

        $symbol = strtoupper($symbol);
        $ticker = $this->getKline1minRds()->getLastPrice($symbol);

        if(!empty($ticker)) {
            $ticker = current($ticker);
            $ticker = json_decode($ticker, true);

            $price['date'] = $ticker[0];
            $price['last'] = my_number_format($ticker[4], 8);
            $price['vol'] = my_number_format($ticker[5], 8);
            $price['open'] = my_number_format($ticker[1], 8);
            $price['hight'] = my_number_format($ticker[2], 8);
            $price['low'] = my_number_format($ticker[3], 8);
            $price['ratio'] = ($price['last'] - $price['open'])/$price['open'] * 100;
            $price['ratio'] = my_number_format($price['ratio'], 8);

            $ticker = array_values($price);

            return $this->responseSuccess(['type' => 'ticker.' . $symbol, 'ticker' => $ticker], 'success');
        } else {
            return $this->setStatusCode(404)->responseError('Not Found.');
        }
    }

    /**
     * 获取最新的深度明细
     * GET api/v2/market/depth
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function depth(Request $request)
    {
        $length = (string)$request->route('level', 'L20');
        $symbol = (string)$request->route('symbol', null);

        if(empty($symbol)) {
            return $this->setStatusCode(400)->responseError('Bad Request.');
        }

        $symbol  = strtoupper($symbol);
        $symbols = explode('_', $symbol);

        if(count($symbols) < 2) {
            return $this->setStatusCode(400)->responseError('Bad Request.');
        }

        if($length == 'L20') {
            $length = 20;
        } else if ($length == 'L100') {
            $length = '100';
        } else {
            $length = 'full';
        }

        $data = $this->getDepthService()->getDepth($symbol, $length);
        if ($data['status'] == 1) {
            return $this->responseSuccess([
                "type" => "depth.{$length}.{$symbol}",
                "ts" => time(),
                "asks" => $data['data']['asks'],
                "bids" => $data['data']['bids']
            ]);
        }

        return $this->setStatusCode(404)->responseError('Not Found.');
    }

    /**
     * 获取最新的成交明细
     * GET api/v2/market/trades
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function trades(Request $request)
    {
        $limit  = $request->input('limit', 20);
        $last_id = $request->input('before', 0);
        $symbol = $request->route('symbol', null);

        if(empty($symbol)) {
            return $this->setStatusCode(400)->responseError('Bad Request.');
        }

        $symbol = strtoupper($symbol);
        $result = $this->getTradesService()->getTradeAll($symbol, $last_id, $limit);

        if($result['status'] == 1) {

            if(empty($result['data'])) {
                return $this->setStatusCode(400)->responseError('Bad Request.');
            }

            $list = [];
            foreach ($result['data'] as $key => $val) {
                $row['amount'] = $val['num'];
                $row['ts'] = strtotime($val['created_at']);
                $row['price'] = $val['price'];
                $row['tid'] = $val['id'];
                $row['side'] = $val['type'];
                $list[] = $row;
            }

            unset($result);
             return $this->responseSuccess($list, 'success');
        } else {
            return $this->setStatusCode(404)->responseError('Not Found.');
        }
    }

    // K线
    public function candles(Request $request)
    {
        $needTk = (int) $request->input('needTickers');
        $size   = (int) $request->input('limit', 20);
        $size   = $size == 0 ? 20 : $size;
        $since  = (int) $request->input('before');
        $symbol = (string) $request->route('symbol');
        $type   = (string) $request->route('resolution');

        if(empty($symbol)) {
            return $this->setStatusCode(400)->responseError('Bad Request.');
        }

        if(empty($type)) {
            return $this->setStatusCode(400)->responseError('Bad Request.');
        }

        switch ($type) {
            case 'M1':
                $kline = $this->getKline1minRds();
                break;

            case 'M5':
                $kline = $this->getKline5minRds();
                break;

            case 'M15':
                $kline = $this->getKline15minRds();
                break;

            case 'M30':
                $kline = $this->getKline30minRds();
                break;

            case 'H1':
                $kline = $this->getKlineHourRds();
                break;

            case 'H6':
                $kline = $this->getKline6HourRds();
                break;

            case 'D1':
                $kline = $this->getKline1dayRds();
                break;

            case 'W1':
                $kline = $this->getKline1weekRds();
                break;

            default:
                $kline = $this->getKline1minRds();
                break;
        }

        $symbol = strtoupper($symbol);
//        $symbol = $this->getMarket($symbol);
        $max    = $kline->getllen($symbol);
        $start  = $max > $size ? $max - $size : 0;
        $data   = $kline->getKline($symbol, $start, -1);

        if(empty($data)) {
            return $this->setStatusCode(404)->responseError('Not Found.');
        }

        $data      = array_map('json_decode', $data, [1]);
        $data      = json_encode($data);
        $data      = json_decode($data, true);
        $klines    = [];
        $newklines = [];

        foreach ($data as $key => $line) {
            $info['type'] = "candle.{$type}.{$symbol}";
            $info['id']  = (string) $line[0];
            $info['open']  = my_number_format($line[1], 8);
            $info['hight']  = my_number_format($line[2], 8);
            $info['low']  = my_number_format($line[3], 8);
            $info['close']  = my_number_format($line[4], 8);
            $info['vol']  = my_number_format($line[5], 8);
            $klines[] = $info;
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
            $returnData = array_values($klines);
            return $this->responseSuccess($returnData, 'success');
        } else {
            return $this->setStatusCode(404)->responseError('Not Found.');
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

    private function getTradesService()
    {
        return new Trades();
    }

    private function getDepthService()
    {
        return new DepthService();
    }
}