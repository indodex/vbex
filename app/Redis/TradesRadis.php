<?php

namespace App\Redis;

use App\Redis\BaseRedis;

class TradesRadis extends BaseRedis
{

    public function publishEntrust($pulishData)
    {
        $pulishData = json_encode($pulishData);
    	return $this->publish('entrust_order', $pulishData);
    }
}
