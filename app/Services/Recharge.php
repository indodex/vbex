<?php

namespace App\Services;


use DB;
use App\Services\BaseService;
use App\Models\UserModel;
use App\Models\RechargeCodeOrdersModel;
use App\Models\RechargeCodeModel;

use App\Models\ArtificialRechargeModel;
use App\Models\CurrencyModel;
use App\Models\AccountsModel;
use App\Models\AccountsDetailsModel;
use App\Models\UserRechargeCodeModel;
use App\Models\DepositsOrdersModel;

use App\Coin;

use App\Monolog\WithdrawsLogs;

class Recharge extends BaseService
{


    public function rechargeCode($uid, $code, $remark = '') 
    {

        $codeInfo = $this->getRechargeCodeModel()->getInfoByCode($code);

        // 查看是否为空
        if(empty($codeInfo)) {
            return $this->error(__('api.recharge.empty_code'));
        }

        // 使用状态
        if($codeInfo->status != 3) {
            return $this->error(__('api.recharge.used_code'));
        }

        // 充值码法币类型
        if((int) $codeInfo->currency == 0) {
            return $this->error(__('api.recharge.code_no_type'));
        }

        // 充值UID不为空已使用
        if($codeInfo->recharge_uid > 0) {
            return $this->error(__('api.recharge.code_user_error'));
        }

        // 充值额度对比
        if($codeInfo->amount != $codeInfo->amount) {
            return $this->error(__('api.recharge.code_amount_error'));
        }

        $user = $this->getUserModel()->getInfo($uid);
        if(empty($user)) {
            return $this->error(__('api.recharge.empty_user'));
        }

        if($user->is_freeze == 1) {
            return $this->error(__('api.recharge.user_is_freeze'));
        }

        if($user->is_lock == 1) {
            return $this->error(__('api.recharge.user_is_lock'));
        }

        if($user->is_delete == 1) {
            return $this->error(__('api.recharge.user_is_delete'));
        }

        // 用户账号
        $accountsModel = $this->getAccountsModel()->setCurrency($codeInfo->currency);
        $account       = $accountsModel->getAccount($user->id, $codeInfo->currency);

        // 开始充值
        DB::beginTransaction();

        // 审核充值记录
        $applyOrder = $this->getCodeOrdersModel()->createOrder([
                            'uid'           => $uid,
                            'code_id'       => $codeInfo->id,
                            'amount'        => $codeInfo->amount,
                            'confirmations' => 0,
                            'remark'        => '',
                            'done_at'       => '',
                            'status'        => 1,
                        ]);
        
        // 审核充值码
        $applyCode  = $this->getRechargeCodeModel()
                           ->usedCode($codeInfo->id, $uid, 1);
        
        // 充值
        $incRes     = $accountsModel->incrementBalance($user->id, $codeInfo->amount);
        
        $account    = $accountsModel->getInfoByUid($user->id);

        // 充值记录
        $capitalDetail = $this->getAccountsDetailsModel()->createDetail([
                            'uid'            => $user->id,
                            'currency'       => $codeInfo->currency,
                            'type'           => 1,
                            'change_balance' => $codeInfo->amount,
                            'balance'        => $account->balance,
                            'remark'         => $remark,
                        ]);

        if($applyOrder && 
            $applyCode && 
            $incRes && 
            $capitalDetail) {
             DB::commit();
            return $this->success('success');
        } else {
            DB::rollback();
            return $this->error(__('api.recharge.recharge_fail'));
        }
    }

    public function rechargeCodeBank($uid, $code) 
    {
        $codeInfo = $this->getRechargeCodeModel()->getInfoByCode($code);

        // 查看是否为空
        if(empty($codeInfo)) {
            return $this->error(__('api.recharge.empty_code'));
        }

        // 使用状态
        if($codeInfo->status != 3) {
            return $this->error(__('api.recharge.used_code'));
        }

        // 充值UID不为空已使用
        if($codeInfo->recharge_uid > 0) {
            return $this->error(__('api.recharge.used_code'));
        }

        // 充值码法币类型
        if((int) $codeInfo->currency == 0) {
            return $this->error(__('api.recharge.code_no_type'));
        }

        $user = $this->getUserModel()->getInfo($uid);
        if(empty($user)) {
            return $this->error(__('api.recharge.empty_user'));
        }

        if($user->is_freeze == 1) {
            return $this->error(__('api.recharge.user_is_freeze'));
        }

        if($user->is_lock == 1) {
            return $this->error(__('api.recharge.user_is_lock'));
        }

        if($user->is_delete == 1) {
            return $this->error(__('api.recharge.user_is_delete'));
        }

        // 开始充值
        DB::beginTransaction();

        $order = $this->getCodeOrdersModel()->createOrder([
                    'uid'           => $uid,
                    'code_id'       => $codeInfo->id,
                    'amount'        => $codeInfo->amount,
                    'confirmations' => 0,
                    'remark'        => '',
                    'done_at'       => '',
                    'status'        => 2,
                ]);

        $isUpdate = $this->getRechargeCodeModel()->usedCode($codeInfo->id, $uid);

        if($order && $isUpdate) {
             DB::commit();
            return $this->success($order);
        } else {
            DB::rollback();
            return $this->error(__('api.recharge.recharge_fail'));
        }
    }

    public function applyRecharge($id, $status, $remark = '') 
    {
        $order = $this->getCodeOrdersModel()->getInfo($id);

        // 查看是否为空
        if(empty($order)) {
            return $this->error(__('api.recharge.empty_order'));
        }

        // 使用状态
        if($order->status != 2) {
            return $this->error(__('api.recharge.order_status_error'));
        }

        if(empty($order->code_id)){
            return $this->error(__('api.recharge.empty_code'));
        }

        $codeInfo = $this->getRechargeCodeModel()->getInfo($order->code_id);

        // 查看是否为空
        if(empty($codeInfo)) {
            return $this->error(__('api.recharge.empty_code'));
        }

        // 使用状态
        if($codeInfo->status != 2) {
            return $this->error(__('api.recharge.code_error'));
        }

        // 充值码法币类型
        if((int) $codeInfo->currency == 0) {
            return $this->error(__('api.recharge.code_no_type'));
        }

        // 充值UID不为空已使用
        if($codeInfo->recharge_uid != $order->uid) {
            return $this->error(__('api.recharge.code_user_error'));
        }

        // 充值额度对比
        if($codeInfo->amount != $codeInfo->amount) {
            return $this->error(__('api.recharge.code_amount_error'));
        }

        $user = $this->getUserModel()->getInfo($order['uid']);
        if(empty($user)) {
            return $this->error(__('api.recharge.empty_user'));
        }

        if($user->is_freeze == 1) {
            return $this->error(__('api.recharge.user_is_freeze'));
        }

        if($user->is_lock == 1) {
            return $this->error(__('api.recharge.user_is_lock'));
        }

        if($user->is_delete == 1) {
            return $this->error(__('api.recharge.user_is_delete'));
        }

        // 审核失败
        if($status == 0) {
            DB::beginTransaction();
            // 审核充值记录
            $applyOrder = $this->getCodeOrdersModel()->applyById($id, $status, $remark);

            // 审核充值码
            $applyCode = $this->getRechargeCodeModel()->applyCode($codeInfo->id, $status);

            if($applyOrder && $applyCode) {
                 DB::commit();
                return $this->success($order);
            } else {
                DB::rollback();
                return $this->error(__('api.recharge.recharge_fail'));
            }
        }

        // 用户账号
        $accountsModel = $this->getAccountsModel()->setCurrency($codeInfo->currency);
        $accountsModel->getAccount($user->id, $codeInfo->currency);

        // 开始充值
        DB::beginTransaction();

        // 审核充值记录
        $applyOrder = $this->getCodeOrdersModel()->applyById($id, $status, $remark);
        
        // 审核充值码
        $applyCode  = $this->getRechargeCodeModel()->applyCode($codeInfo->id, $status);
        
        // 充值
        $incRes     = $accountsModel->incrementBalance($user->id, $codeInfo->amount);
        
        $account    = $accountsModel->getInfoByUid($user->id);

        // 充值记录
        $capitalDetail = $this->getAccountsDetailsModel()->createDetail([
                            'uid'            => $user->id,
                            'currency'       => $codeInfo->currency,
                            'type'           => 1,
                            'change_balance' => $codeInfo->amount,
                            'balance'        => $account->balance,
                            'remark'         => $remark,
                        ]);

        if($applyOrder && 
            $applyCode && 
            $incRes && 
            $capitalDetail) {
             DB::commit();
            return $this->success($order);
        } else {
            DB::rollback();
            return $this->error(__('api.recharge.recharge_fail'));
        }
    }

    public function artificialRecharge($uid, $currency, $amount)
    {
        $user = $this->getUserModel()->getInfo($uid);

        if(empty($user)) {
            return $this->error(__('api.recharge.empty_user'));
        }

        if($user->is_freeze == 1) {
            return $this->error(__('api.recharge.user_is_freeze'));
        }

        if($user->is_lock == 1) {
            return $this->error(__('api.recharge.user_is_lock'));
        }

        if($user->is_delete == 1) {
            return $this->error(__('api.recharge.user_is_delete'));
        }

        $currencyIofo = CurrencyModel::find($currency);
        if (empty($currencyIofo)) {
            return $this->error(__('api.recharge.recharge_fail'));
        }

        if ($currencyIofo->enable_deposit != 1) {
            return $this->error(__('api.recharge.currency_is_deposit'));
        }

        if ($currencyIofo->status != 1) {
            return $this->error(__('api.recharge.currency_is_off'));
        }

        $AccountsModel = new AccountsModel();
        
        // 查询对应账号的币种信息
        $Accounts = $AccountsModel->findORcreate($uid, $currency);

        // 增加金额
        $Accounts->balance = $Accounts->balance + $amount;
        $result = $Accounts->save();

        // 人工充值记录
        $Artificial = new ArtificialRechargeModel();
        $result ? $Artificial->status = 1 : $Artificial->status = 0 ;
        
        $Artificial->uid = $uid;
        $Artificial->currency = $currency;
        $Artificial->amount = $amount;

        $Artificial->save();

        return $this->success($Artificial);
    }

    public function checkRechargeCode($code)
    {
        $m = $this->getModel($code);

        return $m::where([
            ['code', '=', $code],
            ['status', '=', 3],
            ['is_delete', '=', 0]
        ])->first();

    }

    public function getModel($code)
    {
        $codeArr = explode('_',$code);

        if(preg_match("/^(US).*$/",$codeArr[2])){
            return new UserRechargeCodeModel();
        }else{
            return new RechargeCodeModel();
        }
        
    }

    public function deposit($id, $status, $remark=''){

        DB::beginTransaction();
        try{
            $DepositsOrdersModel = new DepositsOrdersModel();
            $order = $DepositsOrdersModel->where(['id'=>$id])->lockForUpdate()->first();

            // 判断充值记录是否存在
            if (empty($order)) {
                DB::rollback();
                return ['status'=>false,'message'=>__('api.recharge.empty_order')];
            }

            if ($order->status == 1) {
                DB::rollback();
                return ['status'=>false,'message'=>__('api.public.dont_repeat_action')];
            }

            // 判断用户是否存在
            $user = $this->getUserModel()->getInfo($order->uid);
            if(empty($user)) {
                DB::rollback();
                $DepositsOrdersModel->applyById($id,0,'用户不存在');
                return ['status'=>false,'message'=>__('api.recharge.empty_user')];
            }

            if($user->is_freeze == 1) {
                DB::rollback();
                $DepositsOrdersModel->applyById($id,0,'用户已冻结');
                return $this->error(__('api.recharge.user_is_freeze'));
            }

            if($user->is_lock == 1) {
                DB::rollback();
                $DepositsOrdersModel->applyById($id,0,'用户已锁定');
                return $this->error(__('api.recharge.user_is_lock'));
            }

            if($user->is_delete == 1) {
                DB::rollback();
                $DepositsOrdersModel->applyById($id,0,'用户已删除');
                return $this->error(__('api.recharge.user_is_delete'));
            }

            // 审核不通过
            if ($status == 0) {
                $DepositsOrdersModel->applyById($id,$status,$remark);
                DB::commit();
                return ['status'=>true,'message'=>__('api.recharge.success')];
            }

            if ($status == 1) {
                // 查看用户该币种是否存在账户记录，没有就创建
                $this->getAccountsModel()->getOrCreate($order->uid,$order->currency);

                $this->getAccountsModel()->where(['uid'=>$order->uid,'currency'=>$order->currency])->lockForUpdate()->first();
                // 充值
                $accountsModel = $this->getAccountsModel()->setCurrency($order->currency);
                $result     = $accountsModel->incrementBalance($user->id, $order->amount);
                $accounts = $this->getAccountsModel()->where(['uid'=>$order->uid,'currency'=>$order->currency])->lockForUpdate()->first();

                if (!$result) {
                    // 充值失败，回滚
                    DB::rollback();
                    $DepositsOrdersModel->applyById($id,0,'充值失败');
                    return ['status'=>false,'message'=>__('api.recharge.recharge_fail')];
                }
                $DepositsOrdersModel->applyById($id,1,'充值成功');
                $result = $this->getCoin()->deposit($order->currencyTo->code, $order->txid);

                // 账号余额变动
                $capitalDetail = $this->getAccountsDetailsModel()->createDetail([
                            'uid'            => $order->uid,
                            'currency'       => $order->currency,
                            'type'           => 1,
                            'change_balance' => $order->amount,
                            'balance'        => $accounts->balance,
                            'remark'         => $remark,
                        ]);
                $this->getWithdrawsLogs()->addLogs([
                    'order_id' => $order->id,
                    'result'   => $result
                ]);
                $order->code = $order->currencyTo->code;
                $orderinfo = $order->toArray();
                // 提交事务
                DB::commit();
                return ['status'=>true,'message'=>__('api.recharge.recharge_complete'), 'order' => $orderinfo];
            }
        }catch (\Exception $e){
            DB::rollback();
            return ['status'=>false,'message'=>__('api.recharge.recharge_fail')];
        }
        

    }

    private function getArtificialModel(){
        return new ArtificialRechargeModel();
    }

    private function getCodeOrdersModel()
    {
        return new RechargeCodeOrdersModel();
    }

    private function getRechargeCodeModel()
    {
        return new RechargeCodeModel();
    }

    private function getUserModel()
    {
        return new UserModel();
    }

    private function getAccountsModel()
    {
        return new AccountsModel();
    }

    private function getAccountsDetailsModel()
    {
        return new AccountsDetailsModel();
    }

    private function getCoin()
    {
        $server = env('WALLET_HOST') . ':' . env('WALLET_PORT');
        return new Coin($server);
    }

    // 消除相应锁定资金
    private function getWithdrawsLogs()
    {   
        return new WithdrawsLogs();
    }
}
