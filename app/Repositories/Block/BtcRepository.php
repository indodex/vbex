<?php

namespace App\Repositories\Block;

use GuzzleHttp\Client;
use App\Repositories\Contracts\GuzzieRepositoryInterface;
use App\Repositories\GuzzieRepository;

class BtcRepository extends GuzzieRepository implements GuzzieRepositoryInterface
{
    protected $clientUri;

    public function __construct()
    {
        $this->clientUri = config('currency.blockchain_url');
        $this->getClient($this->clientUri);
    }

    public function getClient($uri)
    {
        return $this->getCurlClient($uri);
    }

    // 获取汇率
    public function getRates()
    {
        $response = $this->sendGet('/ticker', ['cors' => true]);
        return $response;
    }

    public function getTransactionInfoUrl($txid)
    {
        return $this->clientUri . '/tx/' . $txid;
    }
}