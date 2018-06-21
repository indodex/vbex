<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SwooleWebsocket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole:httpserver';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $redisClass = new \Redis();
        $redisClass->connect('127.0.0.1', 6379, 1);
        $result = $redisClass->hmset('mytest:phpredis1', ['aa' => 'aa', 'bb' => 'bb']); // 原生的支持数组
        var_dump($result);

        $redis = new \swoole_redis();
        $redis->connect('127.0.0.1', 6379, 
            function (swoole_redis $client, $result) {
                $client->hmset('mytest:swooleredis_hmset1', ['aa' => 'aa', 'bb' => 'bb'], function(swoole_redis $client, $result){
                    var_dump($result);
                });

                $client->hmset('mytest:swooleredis_hmset2', 'aa', 'aa', 'bb', 'bb', function(swoole_redis $client, $result){
                    var_dump($result);
                });
            });
    }
}
