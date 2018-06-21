<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class IndexController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    public function index()
    {
        Cache::put('test_my', '12345', 10);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function test()
    {
        // var_dump(Cache::get('test_my'));exit;
        $memcache = new \Memcached;             //创建一个memcache对象
        $memcache->addServer('127.0.0.1', '11211') or die ("Could not connect"); //连接Memcached服务器
        // $memcache->set('key', 'test');
        $get_value = $memcache->get('laravel:test_my');   //从内存中取出key的值
        echo $get_value;
    }
}
