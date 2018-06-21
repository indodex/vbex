<?php

namespace App\Redis;

use App\Redis\BaseRedis;

class Withdraw extends BaseRedis
{
	public $prefixRds = 'withdraw:';

    public function getWithdrawApplyKey($key)
    {
    	return $this->get($this->prefixRds . $key);
    }

    public function setWithdrawApplyKey($key, $value, $minute = 45)
    {
    	$this->set($this->prefixRds . $key, $value);
    	$this->expire($this->prefixRds . $key, $minute * 60);
    	return true;
    }

    public function delWithdrawApplyKey($key)
    {
        return $this->del($this->prefixRds . $key);
    }
}
