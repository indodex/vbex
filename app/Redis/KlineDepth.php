<?php

namespace App\Redis;

use App\Redis\BaseRedis;

class KlineDepth extends BaseRedis
{	
	public $prefixRds = 'kline:depth';

	public function depthLpush($data)
	{
		$this->lpush($this->prefixRds,json_encode($data));
	}

	public function depthLrange()
	{
		return $this->lrange($this->prefixRds . $key);
	}

    public function getllen($key)
    {
        return $this->llen($this->prefixRds . $key);
    }
}