<?php

namespace App\Redis;

use App\Redis\BaseRedis;

class Kline15min extends BaseRedis
{
	public $prefixRds = 'kline:15min:';

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

    public function getllen($key)
    {
        return $this->llen($this->prefixRds . $key);
    }
}
