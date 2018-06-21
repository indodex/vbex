<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class TradesDepth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trade:depth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public $sleep_time;
    public $key;
    public $channel;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->sleep_time = 2;
        $this->key = 'depth';
        $this->channel = 'depth';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        while(true){
            $n =  Redis::llen($this->key);

            if ($n > 0) {
                for ($i = 0; $i < $n; $i++) { 
                    $row = Redis::lrang($this->key);
                    $row = json_decode($row,true);

                    // asks 卖， bids 买
                    if ($row['type'] = 'asks') {
                        unset($row['type']);
                        $list['asks'][] = $row;
                    }else{
                        unset($row['type']);
                        $list['bids'][] = $row;
                    }
                }
            }

            Redis::publish($this->channel,json_encode($list));

            sleep($this->sleep_time);//等待时间，进行下一次操作。
        }
    }
}
