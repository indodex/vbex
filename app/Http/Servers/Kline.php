<?php
namespace App\Servers;

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController as Controller;

class Kline extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        echo md5('teaching'.'2017-11-30');exit();
        return view('home');
    }
}
