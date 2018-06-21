<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Workerman\Worker;

class WorkermanHttpserver extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workerman:httpserver {action} {--daemonize}';

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
        //因为workerman需要带参数 所以得强制修改
        global $argv;
        $action=$this->argument('action');
        if(!in_array($action,['start','stop'])){
            $this->error('Error Arguments');
            exit;
        }
        $argv[0]='workerman:httpserver';
        $argv[1]=$action;
        $argv[2]=$this->option('daemonize')?'-d':'';
        $this->httpserver=new Worker('websocket://0.0.0.0:1234');
        // App::instance('workerman:httpserver',$this->httpserver);
        $this->httpserver->onMessage=function($connection,$data){
            $connection->send('laravel workerman hello world');
            $i = 0;
            while (true) {
                $connection->send('laravel workerman hello world ' . $i);
                sleep(10);
                $i++;
            }
        };
        Worker::runAll();
    }
}
