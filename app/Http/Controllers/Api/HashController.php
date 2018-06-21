<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\ApiController as Controller;

use App\Services\Accounts;
use Illuminate\Support\Facades\Storage;
use App\Models\CurrencyModel;
use App\Models\AccountsModel;
use App\Models\UserModel;

use App\Models\UserRechargeCodeModel;
use App\Models\UserRechargeCodeOrdersModel;
use App\Models\RechargeCodeOrdersModel;

class HashController extends Controller
{
	public function __construct() 
    {
        parent::__construct();
    }

    public function hashRecord(Request $request)
    {	
    	$token = $request->input('api_token', null);
    	$uid   = $this->uid;

    	$lang   = $request->input('lang', null);
        $page   = $request->input('page', 1);
        $limit  = $request->input('limit', 10);

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $cond['uid'] = $uid;
        $cond['currency'] = $this->getCurrencyModel()->getIdByCode('USD');
        $cond['is_delete'] = 0;
        $request = $this->getUserRechargeCodeModel()->getList($cond, $page, $limit);

        if(!$request){
        	return $this->setStatusCode(404)
                        ->responseError(__('api.public.empty_data'));
        }

        $list = [];
        $status = ['0'=>__('api.account.deposit_fail'),'1'=>__('api.account.deposit_success'),'2'=>__('api.account.deposit_wait'),'3'=>__('api.account.unused'), '4' => __('api.account.refunded')];
        foreach ($request->items() as $key => $val) {
            $row['id']           = $val->id;
            $row['code']         = substr_replace($val->code, '******', stripos($val->code, '_'), 24);
            $row['user']         = $val->id;
            $row['rechargeUser'] = $val->recharge_uid ? $val->user->name : __('api.account.unused');
            $row['amount']       = $val->amount;
            $row['currency']     = $val->moneyTo->name;
            $row['status']       = $status[$val->status];
            $row['created_at']   = $val->created_at->toDateTimeString();;
            $row['done_at']      = $val->done_at;
            if($val->audit == 1) {
                $row['auditStr'] = __('api.withdraw.status_pass');
            } else if($val->audit == -1) {
                $row['auditStr'] = __('api.withdraw.status_refuse');
            } else {
                $row['auditStr'] = __('api.withdraw.waited_audit');
            }
            
        	$list[] = $row;
        }
        $request = $request->toArray();

        $paginate['currentPage'] = $request['current_page'];
        $paginate['lastPage'] = $request['last_page'];
        $paginate['perPage'] = $request['per_page'];
        $paginate['total'] = $request['total'];

        $data['list'] = $list;
        $data['paginate'] = $paginate;
        return $this->setStatusCode(200)
                        ->responseSuccess($data, 'success');
    }

    public function hashDetail(Request $request)
    {
        $token = $request->input('api_token', null);
        $uid   = $this->uid;

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

		$currency = $this->getCurrencyModel()->getIdByCode('USD');
        
        $mAccounts = new AccountsModel();
       	$mAccounts->setCurrency($currency);
        $account = $mAccounts->getInfo($uid);

        $data = $this->getAccountsService()->getHashDetail($uid);
        $data['usd'] = 0;
        
        if ($account) {
            $data['usd'] = my_number_format($account->balance,4);
        }
        
        return $this->setStatusCode(200)
                        ->responseSuccess($data, 'success');
    }

    public function userRechargeOrder(Request $request)
    {
        $token = $request->input('api_token', null);
        $uid   = $this->uid;

        $lang   = $request->input('lang', null);
        $page   = $request->input('page', 1);
        $limit  = $request->input('limit', 10);

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $cond['uid'] = $uid;
        $request = $this->getUserRechargeOrdersModel()->getList($cond, $page, $limit);

        if(!$request){
            return $this->setStatusCode(404)
                        ->responseError(__('api.public.empty_data'));
        }

        $list = [];
        $status = ['0'=>__('api.account.deposit_fail'),'1'=>__('api.account.deposit_success'),'2'=>__('api.account.deposit_wait'),'3'=>__('api.account.unused'), '4' => __('api.account.refunded')];
        foreach ($request->items() as $key => $val) {
            $row['id']         = $val->id;
            $row['code']       = $val->rechargeCode->code;
            $row['user']       = UserModel::getName($val->rechargeCode->uid);
            $row['amount']     = $val->amount;
            $row['currency']   = $val->rechargeCode->currency;
            $row['status']     = $status[$val->status] ;
            $row['remark']     = $val->remark;
            $row['created_at'] =$val->created_at->toDateTimeString();;
            $row['done_at']    =$val->done_at;
            $list[]            = $row;
        }
        $request = $request->toArray();

        $paginate['currentPage'] = $request['current_page'];
        $paginate['lastPage']    = $request['last_page'];
        $paginate['perPage']     = $request['per_page'];
        $paginate['total']       = $request['total'];

        $data['list']     = $list;
        $data['paginate'] = $paginate;
        return $this->setStatusCode(200)
                        ->responseSuccess($data, 'success');
    }

    public function systemRechargeOrder(Request $request)
    {
        $token = $request->input('api_token', null);
        $uid   = $this->uid;

        $lang   = $request->input('lang', null);
        $page   = $request->input('page', 1);
        $limit  = $request->input('limit', 10);

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $cond['uid'] = $uid;
        $request = $this->getSystemRechargeOrdersModel()->getList($cond, $page, $limit);
    
        if(!$request){
            return $this->setStatusCode(404)
                        ->responseError(__('api.public.empty_data'));
        }

        $list = [];
        $status = ['0'=>__('api.account.deposit_fail'),'1'=>__('api.account.deposit_success'),'2'=>__('api.account.deposit_wait'),'3'=>__('api.account.unused'), '4' => __('api.account.refunded')];
        foreach ($request->items() as $key => $val) {
            $row['id']         = $val->id;
            $row['code']       = $val->rechargeCode->code;
            $row['user']       = $val->user->name;
            $row['amount']     = $val->amount;
            $row['currency']   = $val->rechargeCode->currency;
            $row['status']     = $status[$val->status];
            $row['remark']     = $val->remark;
            $row['created_at'] =$val->created_at->toDateTimeString();;
            $row['done_at']    =$val->done_at;
            $list[]            = $row;
        }
        $request = $request->toArray();

        $paginate['currentPage'] = $request['current_page'];
        $paginate['lastPage']    = $request['last_page'];
        $paginate['perPage']     = $request['per_page'];
        $paginate['total']       = $request['total'];

        $data['list']     = $list;
        $data['paginate'] = $paginate;
        return $this->setStatusCode(200)
                        ->responseSuccess($data,  __('api.public.success'));
    }

    public function getUserRechargeCodeModel()
    {
    	return new UserRechargeCodeModel();
    }

    public function getUserRechargeOrdersModel()
    {
        return new UserRechargeCodeOrdersModel();
    }

    public function getSystemRechargeOrdersModel()
    {
        return new RechargeCodeOrdersModel();
    }

    public function getAccountsService()
    {
        return new Accounts();
    }

    public function getCurrencyModel()
    {
        return new CurrencyModel();
    }
}