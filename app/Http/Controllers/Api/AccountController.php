<?php

namespace App\Http\Controllers\Api;

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

class AccountController extends Controller
{

    public function __construct() 
    {
        parent::__construct();
    }

    public function info(Request $request)
    {
        $token = $request->input('api_token', null);
        $lang  = $request->input('lang', null);
        $uid   = $this->uid;

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $user = $this->getUser();
        if(empty($user)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.account.account_empty'));
        }
        
        if(!empty($user->avatar)) {
            $user->avatar = Storage::disk('public')->url($user->avatar);

            // $user->accounts = $this->getAccountsService()->getAccountFormat($uid);
        } else {
            $user->avatar = Storage::disk('public')->url('img/u005.jpg');
        }
        $user->inviteUrl  = env('APP_URL').'/register/'.$user->id;
        return $this->setStatusCode(200)
                    ->responseSuccess($user->toArray(), 'success');
    }

    // 账户余额
    public function balance(Request $request)
    {
        $token = $request->input('api_token', null);
        $lang  = $request->input('lang', null);
        $uid   = $this->uid;

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $result = $this->getAccountsService()->checkAccount($uid);

        $list = array();
        $price = 0;
        if($result['status'] == 1) {
            $m = $this->getOrderdetailsModel();
            $currency_id = $this->getCurrencyModel()->getIdByCode('USD');

            foreach ($result['data'] as $key => $value) {
                $code                            = strtolower($value['coin']);
                $list[$code]['coin']             = $value['coin'];
                $list[$code]['balance']          = my_number_format($value['balance'], $value['decimals']);
                $list[$code]['balanceStr']       = my_number_format($value['balance'], $value['decimals']);
                $list[$code]['lockedBalance']    = my_number_format($value['locked'], $value['decimals']);
                $list[$code]['lockedBalanceStr'] = my_number_format($value['locked'], $value['decimals']);
                $list[$code]['total']            = my_number_format($value['balance'] + $value['locked'], $value['decimals']);
                $list[$code]['totalStr']         = my_number_format($value['balance'] + $value['locked'], $value['decimals']);
                $list[$code]['enable_deposit']   = $value['enable_deposit'];
                $list[$code]['enable_withdraw']  = $value['enable_withdraw'];
                $list[$code]['price']            = 0;
                if($value['logo']) {
                    $list[$code]['logo']         = Storage::disk('public')->url($value['logo']);
                } else {
                    $list[$code]['logo']         = '';
                }
                
                if ($code == 'usd') {
                    $list[$code]['price'] = 1;
                    $price = $price + $value['balance'] + $value['locked'];
                }else if($value['is_virtual'] == 0){

                    $data = ExchangeRatesModel::where(['market'=>'USD_'.$value['coin']])->first();
                    if ($data) {
                        $list[$code]['price'] = my_number_format($data->price,4);;
                        $price = $price + my_number_format(($value['balance'] + $value['locked']) * $data->price,4);
                    }
                }else{
                    $cond['buy_currency'] = $value['currency'];
                    $cond['sell_currency'] = $currency_id;
                    $newOrder = $m->getOne($cond);

                    if ($newOrder) {
                        $list[$code]['price']            = $newOrder->price;
                        $price = $price + my_number_format(bcadd($value['balance'], $value['locked'], 18) * $newOrder->price, 4);
                    }
                }
                
            }
        }

        $currencies = $this->getCurrencyModel()->getNotVirtual();

        $proportion = [];
        if ($currencies) {
            foreach ($currencies as $key => $value) {
                $row['code'] = $value->code;
                if ($value->code == 'USD') {
                    $row['proportion'] = 1;
                }else{
                    $market = 'USD_'.$value->code;
                    $data = ExchangeRatesModel::where(['market'=>$market])->first();
                    if ($data) {
                        $row['proportion'] = my_number_format($data->price,4);
                    }
                }

                if (!empty($row['proportion'])) {
                   $proportion[] = $row;
                }

            }
        }

        if(count($list) > 1) {
            $reutrnData['list'] = $list;
            $reutrnData['price'] = $price;
            $reutrnData['proportion'] = $proportion;
            $reutrnData['timestamp'] = time();
            return $this->setStatusCode(200)
                        ->responseSuccess($reutrnData, 'success');
        } else {
            return $this->setStatusCode(404)
                        ->responseError(__('api.public.empty_data'));
        }
    }

    public function bill(Request $request)
    {
        $token = $request->input('api_token', null);
        $lang  = $request->input('lang', null);
        $type  = $request->input('type', 'all');
        $ctype = $request->input('coinType', null);
        $page  = $request->input('page', null);
        $uid   = $this->uid;

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $where['uid']      = $uid;
        $where['type']     = $type;
        $where['coinCode'] = $ctype ? strtolower($ctype) : '';
        $result = $this->getAccountsService()->getAccountDetails($where, $page);

        if($result['status'] == 1) {
            if(empty($result['data']['data'])) {
                return $this->setStatusCode(404)
                        ->responseError(__('api.public.empty_data'));
            }
            
            $list     = $result['data']['data'];
            unset($result['data']['data']);
            $paginate = $result['data'];
            unset($paginate['path'],
                  $paginate['from'],
                  $paginate['to'],
                  $paginate['first_page_url'],
                  $paginate['last_page_url'],
                  $paginate['next_page_url'],
                  $paginate['prev_page_url']);

            $outputData['list']     = $list;
            $outputData['paginate'] = $paginate;
            $outputData['timestamp'] = time();

            return $this->setStatusCode(200)
                        ->responseSuccess($outputData, 'success');
        } else {
            return $this->setStatusCode(404)
                        ->responseError(__('api.public.empty_data'));
        }
    }

    public function deductible(Request $request)
    {
        $token = $request->input('api_token', null);
        $lang  = $request->input('lang', null);
        $uid   = $this->uid;

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $user   = $this->getUser();
        $isDeduction  = $user->is_deduction == 1 ? 0 : 1;
        $result = $this->getUserModel()->updateById($user->id, ['is_deduction' => $isDeduction]);
        
        if($result) {
            return $this->setStatusCode(200)->responseNotFound(__('api.public.success'));
        } else {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.error'));
        }

    }

    public function inviteUser()
    {
        $userId = $this->getUserId();
        $usersData = UserModel::where('invite_uid',$userId)->paginate(10);
        //var_dump();exit();
        if($usersData) {
            return $this->setStatusCode(200)
                        ->responseSuccess($this->formatPage($usersData), 'success');
        } else {
            return $this->setStatusCode(404)
                        ->responseError(__('api.public.empty_data'));
        }
    }

    public function userRewards(Request $request)
    {
        $coinType = $request->input('coinType', null);
        $limit    = $request->input('limit', 10);
        $page     = $request->input('page', 1);
        $uid      = $this->getUserId();

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        if(!empty($coinType)) {
            $currency = $this->getCurrencyModel()->getIdByCode($coinType);
            $cond['currency'] = $currency;
        }

        $cond['uid'] = $uid;
        $rewards = $this->getUsersRewardsModel()->getList($cond, $page, $limit);

        if(empty($rewards)) {
            return $this->setStatusCode(404)
                        ->responseError(__('api.public.empty_data'));
        }

        foreach ($rewards->items() as $key => $reward) {
            $reward->val      = (float) $reward->val;
            $reward->ratio    = (float) $reward->ratio;
            $reward->currency = $reward->currencyTo->code;
            $reward->fname    = $reward->user->name;
            unset($reward->currencyTo, $reward->user);
        }
        $data = $this->formatPage($rewards);
        $list = $data['list'];
        unset($data['list']);
        $paginate = $data;
        return $this->setStatusCode(200)
                    ->responseSuccess(['list' => $list, 'paginate' => $paginate], 'success');
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
