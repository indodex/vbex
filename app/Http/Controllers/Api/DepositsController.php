<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\ApiController as Controller;

use App\Services\DepositsAddresses;

class DepositsController extends Controller
{

    public function __construct() 
    {
        parent::__construct();
    }

    // 充值地址查询
    public function address(Request $request)
    {
        $token    = $request->input('api_token', null);
        $coinType = $request->input('coinType', null);
        $uid      = $this->uid;

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }
        if(empty($coinType)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.account.lack_currency'));
        }
        $coinType = strtoupper($coinType);

        $result   = $this->getDepositsAdresseService()->getUserAddress($uid, $coinType);
        if($result['status'] == 1) {
            return $this->setStatusCode(200)->responseSuccess($result['data'], 'success');
        } else {
            return $this->setStatusCode(404)->responseNotFound($result['error']);
        }
    }

    private function getDepositsAdresseService()
    {
        return new DepositsAddresses();
    }
}
