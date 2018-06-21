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
}