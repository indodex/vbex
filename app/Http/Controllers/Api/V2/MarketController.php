<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

use App\Services\Currency;
use App\Services\Trades;
use App\Services\DepthService;
use App\Redis\Kline1min;

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
        $lang   = $request->input('lang', null);
        $limit  = $request->input('limit', 30);
        $symbol = $request->input('symbol', null);
        $last_id = $request->input('last_trade_tid', 0);

        if(empty($symbol)) {
            return $this->setStatusCode(400)
                ->responseError(__('public.deposits_address.buy_currency_empty'));
        }
        $symbol = strtolower($symbol);
        $symbol = $this->getMarket($symbol);
        $symbol = strtoupper($symbol);

        $result = $this->getTradesService()->getTradeAll($symbol, $last_id, $limit);

        if($result['status'] == 1) {

            if(empty($result['data'])) {
                return $this->setStatusCode(403)
                    ->responseError(__('api.public.empty_data'));
            }

            $list = [];
            foreach ($result['data'] as $key => $val) {
                $row['amount'] = $val['num'];
                $row['price'] = $val['price'];
                $row['tid'] = $val['id'];
                $row['date'] = strtotime($val['created_at']);
                $row['type'] = $val['type'];
                $row['trade_type'] = $val['type'] == 'sell' ? 'ask' : 'bid';
                $list[] = $row;
            }

            $outputData['list'] = $list;
            unset($result);

            return response()->json([
                'data' => array_reverse($list)
            ]);
            // return $this->setStatusCode(200)
            //             ->responseSuccess($outputData, 'success');
        } else {
            return $this->setStatusCode(403)
                ->responseError(__('api.public.empty_data'));
        }
    }

    private function getKline1minRds()
    {
        return new Kline1min();
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