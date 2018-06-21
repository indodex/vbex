<?php

namespace App\Repositories;

use GuzzleHttp\Client;
use App\Repositories\Contracts\GuzzieRepositoryInterface;
use App\Repositories\GuzzieRepository;

class ZhongbiRepository extends GuzzieRepository implements GuzzieRepositoryInterface
{
    protected $clientUri;

    public function __construct()
    {
        $this->clientUri = 'https://trans.zb.com';
        $this->getClient($this->clientUri);
    }

    public function getClient($uri)
    {
        return $this->getCurlClient($uri);
    }

    // 线图
    public function getKlineLastData($symbol, $type)
    {
        $params['needTickers'] = 1;
        $params['symbol']      = $symbol;
        $params['type']        = $type;
        $params['size']        = 1000;
        $response = $this->sendPost('/markets/klineLastData', $params);
        return $response;
    }

    // 行情
    public function getLastTrades($symbol, $last_trade_tid)
    {
        $params['symbol']         = $symbol;
        $params['last_trade_tid'] = $last_trade_tid;
        $response = $this->sendGet('/getLastTrades', $params);
        return $response;
    }
}