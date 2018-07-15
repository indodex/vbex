<?php

namespace App\Repositories;

use GuzzleHttp\Client;
use App\Repositories\Contracts\GuzzieRepositoryInterface;
use App\Repositories\GuzzieRepository;

class KlineRepository extends GuzzieRepository implements GuzzieRepositoryInterface
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
    public function getKline($params)
    {
        $response = $this->sendPost('/markets/klineLastData', $params);
        return $response;
    }

    /**
     * @param $symbol
     * @return {
                    "code": 0,
                    "message": "success",
                    "data": [
                        {
                        "name": "bitcoin",
                        "symbol": "BTC",
                        "price": 6355.16,
                        "high": 6367.84,
                        "low": 6233.17,
                        "hist_high": 20089,
                        "hist_low": 0,
                        "timestamps": 1531658644823,
                        "volume": 480395,
                        "change_hourly": 0.0002,
                        "change_daily": 0.0176,
                        "change_weekly": -0.0592,
                        "change_monthly": -0.0261
                        }
                    ]
                }
     */
    public function price($symbol)
    {
        $this->clientUri = 'https://data.block.cc';
        $this->getClient($this->clientUri);
        $response = $this->sendGet('/api/v1/price', [
            "symbol" => $symbol
        ]);
        if(!empty($response['data'][0])){
            return $response['data'][0];
        }
        return null;
    }
}