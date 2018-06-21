<?php

namespace App\Redis;

use App\Redis\BaseRedis;
use App\Services\Trades;

class Kline1min extends BaseRedis
{
	
	public $prefixRds = 'kline:1min:';

	// public function addKline($key, $score, $member)
 //    {
 //    	return $this->zadd($this->prefixRds . $key, $score, $member);
 //    }

    // public function getKlineCount($key)
    // {
    // 	return $this->zcard($this->prefixRds . $key);
    // }

    // public function getKline($key, $start, $stop)
    // {
    // 	return $this->zrange($this->prefixRds . $key, $start, $stop);
    // }

    // public function getLastPrice($key)
    // {
    //     $count = $this->getKlineCount($key);
    //     $start = $count - 1;
    //     return $this->zrange($this->prefixRds . $key, $start, -1);
    // }

    public function addKline($key, $value) 
    {
        return $this->rpush($this->prefixRds . $key, $value);
    }

    public function getKlineCount($key)
    {
        return $this->llen($this->prefixRds . $key);
    }

    public function getKline($key, $start, $stop)
    {
        return $this->lrange($this->prefixRds . $key, $start, $stop);
    }

    public function getLastPrice($key)
    {
        return $this->lrange($this->prefixRds . $key, 0, 1);
    }

    public function getllen($key)
    {
        return $this->llen($this->prefixRds . $key);
    }
}
