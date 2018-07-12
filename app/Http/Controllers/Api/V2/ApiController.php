<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;

use App\Http\Controllers\ApiController as Controller;

class ApiController extends Controller
{
    public function __construct()
    {
//        parent::__construct();
        $this->setHttpStatus(1);
    }
}