<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

use App\Services\Currency;
use App\Services\Trades;

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function ticker(Request $request)
    {
        $symbol = $request->input('symbol');

        if(empty($symbol)) {
            return $this->setStatusCode(406)
                        ->responseError('Not Acceptable');
        }

        $symbol = strtoupper($symbol);

        $ticker = $this->getKline1minRds()->getLastPrice($symbol);

        if(!empty($ticker)) {
            $ticker = current($ticker);
            $ticker = json_decode($ticker, true);

            $price['date'] = $ticker[0];
            $price['open'] = $ticker[1];
            $price['hight'] = $ticker[2];
            $price['low'] = $ticker[3];
            $price['last'] = $ticker[4];
            $price['vol'] = $ticker[5];
            $price['ratio'] = ($price['last'] - $price['open'])/$price['open'] * 100;
            $price['ratio'] = my_number_format($price['ratio'], 4);

            $ticker = array_values($price);

            return $this->responseSuccess(['symbol' => $symbol, 'ticker' => $ticker], 'success');
        } else {
            return $this->setStatusCode(404)
                        ->responseError('error');
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
        $a = (int)$request->input('a', 4);
        $length = (int)$request->input('level', 5);
        $symbol = (string)$request->input('symbol', null);

        if(empty($symbol)) {
            return $this->setStatusCode(400)
                        ->responseError('Bad Request.');
        }
        $symbol  = strtoupper($symbol);
        $symbols = explode('_', $symbol);

        if(count($symbols) < 2) {
            return $this->setStatusCode(400)
                        ->responseError('Bad Request.');
        }

        $data = $this->getTradesService()->getLengthDepth($symbol, $a, $length);

        if ($data) {
            return $this->setStatusCode(200)
                        ->responseSuccess($data);
        }

        return $this->setStatusCode(404)
                    ->responseError('Not Found.');
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

    public function getTradesService()
    {
        return new Trades();
    }
}