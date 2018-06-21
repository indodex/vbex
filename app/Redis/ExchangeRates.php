<?php

namespace App\Redis;

use App\Redis\BaseRedis;

class ExchangeRates extends BaseRedis
{
    public function createRates($data)
    {
    	$key = 'rate:btc';
    	$value = json_encode($data);
    	return $this->set($key, $value);
    }

    public function createRate($key, $score, $member)
    {
    	return $this->zadd($key, $score, $member);
    }

    public function getRate($key, $member)
    {
    	return $this->zscore($key, $member);
    }
}
