<?php

namespace App\Services;

use App\Services\BaseService;
use App\Models\UserModel;
use App\Models\AccountsModel;
use App\Models\AccountsDetailsModel;
use App\Models\CurrencyModel;
use App\Models\TradeCodeRecordModel;
use App\Models\TradesOrdersDetailsModel;
use App\Models\UserRechargeCodeModel;
use App\Services\DepositsAddresses;
use App\Services\Exchanges;

class Accounts extends BaseService
{

    public function getAccount($uid, $currencyCode)
    {

        $currency = $this->getCurrencyModel()->getByCode($currencyCode);
        if(empty($currency)) {
            return $this->error(__('api.account.currency_empty'));
        }

        $account = $this->getAccountsModel()
                        ->setCurrency($currency->id)
                        ->getAccount($uid);
        if(empty($account)) {
            return $this->error(__('api.account.account_empty'));
        }

        $resAddress = $this->getDepositsAddressesService()->getUserAddress($uid, $currencyCode);
        if($resAddress['status'] == 1) {
            if(is_object($resAddress['data'])) {
                $address = $resAddress['data']->toArray();
            } else {
                $address = $resAddress['data'];
            }
            
        } else {
            $address = [];
        }

        return $this->success([
                    'account'  => $account->toArray(),
                    'currency' => $currency->toArray(),
                    'address'  => $address,
                ]);
    }

    private function getAccounts($uid)
    {
        $accounts = $this->getAccountsModel()->getListByUid($uid);

        if(empty($accounts)) {
            return false;
        }

        $myAccounts = [];
        foreach ($accounts as $key => $value) {
            $currency = $value->currency()->find($value->currency, ['code', 'decimals']);
            if(!empty($currency)) {
                $value->code = $currency->code;
                $value->decimals = $currency->decimals;

                $myAccounts[$value->currency] = $value->toArray();
            }
            
            
        }
        return $myAccounts;
    }

    public function checkAccount($uid) {
        $currencies = $this->getCurrencyModel()->getAll2()->toArray();

        $accounts = $this->getAccounts($uid);
        $accountsModel = $this->getAccountsModel();

        foreach ($currencies as $key => $currency) {
            $k = $currency['id'];
            if(!isset($accounts[$k])) {
                $account = [
                    'uid'      => $uid,
                    'currency' => $currency['id'],
                    'balance'  => 0,
                    'locked'   => 0,
                ];
                $accountsModel->createAccount($account);
                $account         = array_merge($account, $currency);
                $account['coin'] = $currency['code'];
                $accounts[$key]  = $account;
            } else {
                $accounts[$k]['logo']            = $currency['logo'];
                $accounts[$k]['coin']            = $currency['code'];
                $accounts[$k]['decimals']        = $currency['decimals'];
                $accounts[$k]['enable_deposit']  = $currency['enable_deposit'];
                $accounts[$k]['enable_withdraw'] = $currency['enable_withdraw'];
                $accounts[$k]['is_virtual']      = $currency['is_virtual'];
            }
            
            // if($currency['is_virtual'] == 0) {
            //     unset($accounts[$key]);
            // }
        }
        // print_r($accounts);exit;
        return $this->success($accounts);
    }

    /**
     * [getAccountDetails description]
     * @param  [type] $where [description]
     * @param  [type] $page  [description]
     * @return [type]        [description]
     */
    public function getAccountDetails($where, $page)
    {
        $uid      = $where['uid'];
        $type     = $where['type'];
        $coinCode = $where['coinCode'];

        $cond['uid'] = $uid;
        if(!empty($coinCode)) {
            $currency = $this->getCurrencyModel()->getIdByCode($coinCode);
            if(!empty($currency)) {
                $cond['currency'] = $currency;
            }
        }
        
        switch ($type) {
            case 'deposit':
                $cond['type'] = 1;
                break;
            case 'trading':
                $cond['whereIn']['type'] = [2, -2];
                break;
            case 'withdraw':
                $cond['type'] = -1;
                break;
            case 'other':
                $cond['whereIn']['type'] = [0, 3];
                break;
        }

        $data = $this->getAccountsDetailsModel()
                     ->getList($cond, $page);
        if(!empty($data)) {
            foreach($data->items() as &$value){
                $value->change_balance = (float) $value->change_balance;
                $value->balance        = (float) $value->balance;
                $value->statusStr      = $this->getAccountsDetailsModel()->toStatusName($value->type);
                $currency              = $value->currency()->find($value->currency);
                if(!empty($currency)) {
                    $value->coin = $currency->code;
                }
            }
            $data = $data->toArray();
            return $this->success($data);
        } else {
            return $this->error(__('api.public.empty_data'));
        }
    }

    public function getHashDetail($uid)
    {
        $m = new UserRechargeCodeModel();
        $currency = $this->getCurrencyModel()->getIdByCode('USD');

        $data1 = $m::select(\DB::raw('COUNT(id) as countAll'),\DB::raw('SUM(amount) as amountAll'))
                    ->where(['uid'=>$uid,'is_delete'=>0,'currency'=>$currency])->first()->toArray();

        $data2 = $m::select(\DB::raw('COUNT(id) as countViable'),\DB::raw('SUM(amount) as amountViable'))
                    ->where(['uid'=>$uid,'is_delete'=>0,'currency'=>$currency,'status'=>3])->first()->toArray();

        $data3 = $m::select(\DB::raw('COUNT(id) as countUnusable'),\DB::raw('SUM(amount) as amountUnusable'))
                    ->where([['uid','=', $uid],['is_delete','=',0],['currency','=',$currency],['status','<>',3]])
                    ->first()->toArray();

        return array_merge($data1,$data2,$data3);
    }

    public function getAccountFormatToUSD($uid)
    {
        $orderModel = $this->getOrdersDetailsModel();
        $accouns = $this->getAccounts($uid);
        $currencyId = $this->getCurrencyModel()->getIdByCode('USD');

        $counts = array();
        foreach ($accouns as $key => $value) {
            $order = $orderModel->getOne([
                'buy_currency'  => $value['currency'],
                'sell_currency' => $currencyId,
            ]);
            if(!empty($order)) {
                $total = $value['balance'] + $value['locked'];
                $counts[] = bcmul($order['price'], $total);
            } else {
                $counts[] = 0;
            }
        }

        return array_sum($counts);
    }

    public function writeTradeCodeLog($uid,$ip,$type)
    {
        $data['user_id'] = (int)$uid;
        $data['ip']      = (string)$ip;
        $data['type']    = (int)$type;

        return $this->getTradeCodeLogModel()->writeLog($data);
    }

    public function checkTradeCodeLog($uid)
    {
        return $this->getTradeCodeLogModel()->getLog($uid);
    }

    private function getAccountsModel()
    {
        return new AccountsModel();
    }

    private function getAccountsDetailsModel()
    {
        return new AccountsDetailsModel();
    }

    private function getOrdersDetailsModel()
    {
        return new TradesOrdersDetailsModel();
    }

    private function getCurrencyModel()
    {
        return new CurrencyModel();
    }

    private function getUserModel()
    {
        return new UserModel();
    }

    private function getDepositsAddressesService()
    {
        return new DepositsAddresses();
    }

    private function getExchangesService()
    {
        return new Exchanges();
    }

    private function getTradeCodeLogModel()
    {
        return new TradeCodeRecordModel();
    }
}
