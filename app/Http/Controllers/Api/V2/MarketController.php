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

    public function trades(Request $request)
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

    public function getTradesService()
    {
        return new Trades();
    }
}