<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\ApiController as Controller;

use App\Services\Accounts;
use Illuminate\Support\Facades\Storage;

use App\Models\UserModel;
use App\Models\UsersRewardsModel;
use App\Models\TradesOrdersDetailsModel;
use App\Models\CurrencyModel;
use App\Models\ExchangeRatesModel;

class AccountsController extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    // 账户余额
    public function balance(Request $request)
    {
        $uid = $this->uid;
        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $result = $this->getAccountsService()->checkAccount($uid);

        $list = array();
        if($result['status'] == 1) {
            $m = $this->getOrderdetailsModel();
            $currency_id = $this->getCurrencyModel()->getIdByCode('USD');

            foreach ($result['data'] as $key => $value) {
                if(!empty($value['coin'])) {
                    $code = strtolower($value['coin']);
                    $list[$code]['currency'] = $value['coin'];
                    $list[$code]['available'] = my_number_format($value['balance'], $value['decimals']);
                    $list[$code]['frozen'] = my_number_format($value['locked'], $value['decimals']);
                    $list[$code]['balance'] = bcadd($value['balance'], $value['locked'], $value['decimals']);
                }
            }
        }
        $list = array_values($list);

        if(count($list) > 1) {
            return $this->responseSuccess($list, 'success');
        } else {
            return $this->setStatusCode(404)->responseError('Not Found.');
        }
    }

    public function getAccountsService()
    {
        return new Accounts();
    }

    public function getUserModel()
    {
        return new UserModel();
    }

    public function getOrderdetailsModel()
    {
        return new TradesOrdersDetailsModel();
    }

    public function getCurrencyModel()
    {
        return new CurrencyModel();
    }

    public function getUsersRewardsModel()
    {
        return new UsersRewardsModel();
    }
}
