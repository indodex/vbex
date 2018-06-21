<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Services\Trades;

class RedisSubscribe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:subscribe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe to a Redis channel';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 执行控制台命令
     *
     * @return mixed
     */
    public function handle()
    {
        Redis::subscribe(['entrust_order'], function($message) {
            $trade = json_decode($message, true);
            if($trade) {
                if((int)$trade['isBuy'] === 1) {
                    $result = $this->getTradesService()->buyOrder($trade['uid'], $trade['market'], $trade['price'], $trade['number']);
                } else {
                    $result = $this->getTradesService()->sellOrder($trade['uid'], $trade['market'], $trade['price'], $trade['number']);
                }
            }
            
        });
    }

    public function getTradesService()
    {
        return new Trades();
    }
}
