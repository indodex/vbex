<?php

namespace App\Redis;

use Illuminate\Support\Facades\Redis;

class BaseRedis extends Redis
{
    public function set($key, $value)
    {
    	return Redis::set($key, $value);
    }

    public function get($key)
    {
        return Redis::get($key);
    }

    public function del($key)
    {
        return Redis::del($key);
    }

    public function expire($key, $seconds)
    {
    	return Redis::expire($key, $seconds);
    }

    public function zadd($key, $score, $member)
    {
    	return Redis::zadd($key, $score, $member);
    }

    public function zscore($key, $member)
    {
        return Redis::zscore($key, $member);
    }

    public function zcard($key)
    {
        return Redis::zcard($key);
    }

    public function zrange($key, $start, $stop)
    {
        return Redis::zrange($key, $start, $stop);
    }

    public function zrevrange($key, $start, $stop)
    {
    	return Redis::zrevrange($key, $start, $stop);
    }

    public function publish($channel, $pulishData) 
    {
        return Redis::publish($channel, $pulishData);
    }

    public function lrange($key, $start, $stop) 
    {
        return Redis::lrange($key, $start, $stop);
    }

    public function lpush($key, $value) 
    {
        return Redis::LPUSH($key, $value);
    }

    public function rpush($key, $value) 
    {
        return Redis::RPUSH($key, $value);
    }

    public function llen($key) 
    {
        return Redis::LLEN($key);
    }

    public function lpop($key)
    {
        return Redis::LPOP($key);
    }
}
