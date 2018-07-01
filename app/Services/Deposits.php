<?php

namespace App\Services;


use DB;
use App\Services\BaseService;
use App\Models\UserModel;
use App\Models\AccountsModel;
use App\Models\AccountsDetailsModel;
use App\Models\DepositsOrdersModel;
use App\Models\CurrencyModel;

use App\Coin;
use App\Monolog\WithdrawsLogs;

class Deposits extends BaseService
{

    public function deposit($id, $status, $remark='')
    {

        $DepositsOrdersModel = new DepositsOrdersModel();
        $accountsModel = $this->getAccountsModel();
        DB::beginTransaction();
        // try{
            $order = $DepositsOrdersModel->where(['id'=>$id])->lockForUpdate()->first();

            // 判断充值记录是否存在
            if (empty($order)) {
                DB::rollback();
                return ['status'=>false,'message'=>'充值订单不存在'];
            }

            if ($order->status == 1) {
                DB::rollback();
                return ['status'=>false,'message'=>'请勿重复操作'];
            }

            // 判断用户是否存在
            $user = $this->getUserModel()->getInfo($order->uid);
            if(empty($user)) {
                DB::rollback();
                $DepositsOrdersModel->applyById($id,0,'用户不存在');
                return ['status'=>false,'message'=>'用户不存在'];
            }

            if($user->is_freeze == 1) {
                DB::rollback();
                $DepositsOrdersModel->applyById($id,0,'用户已冻结');
                return ['status'=>false,'message'=>'账户已冻结'];
            }

            if($user->is_lock == 1) {
                DB::rollback();
                $DepositsOrdersModel->applyById($id,0,'用户已锁定');
                return ['status'=>false,'message'=>'用户已锁定'];
            }

            if($user->is_delete == 1) {
                DB::rollback();
                $DepositsOrdersModel->applyById($id,0,'用户已删除');
                return ['status'=>false,'message'=>'用户已删除'];
            }

            // 审核不通过
            if ($status == 0) {
                $DepositsOrdersModel->applyById($id,$status,$remark);
                DB::commit();
                return ['status'=>true,'message'=>'充值成功'];
            }

            if ($status == 1) {
                $account = $accountsModel->setCurrency($order->currency);

                // 查看用户该币种是否存在账户记录，没有就创建
                $ownAccount = $account->getAccountLock($order->uid);

                // 充值
                $result = $account->incrementBalance($user->id, $order->amount);

                if (!$result) {
                    // 充值失败，回滚
                    DB::rollback();
                    $DepositsOrdersModel->applyById($id,0,'充值失败');
                    return ['status'=>false,'message'=>'充值失败'];
                }

                $DepositsOrdersModel->applyById($id,1,'充值成功');
                // $result = $this->getCoin()->deposit($order->currencyTo->code, $order->txid);

                // 账号余额变动
                $capitalDetail = $this->getAccountsDetailsModel()->createDetail([
                    'uid'            => $order->uid,
                    'currency'       => $order->currency,
                    'type'           => 1,
                    'change_balance' => $order->amount,
                    'balance'        => bcadd($ownAccount->balance, $order->amount, 18),
                    'remark'         => $remark,
                ]);

                // $this->getWithdrawsLogs()->addLogs([
                //     'order_id' => $order->id,
                //     'result'   => $result
                // ]);

                $order->code = $order->currencyTo->code;
                $orderinfo = $order->toArray();
                // 提交事务
                DB::commit();
                return ['status'=>true,'message'=>'充值成功', 'order' => $orderinfo];
            }
        // }catch (\Exception $e){
        //     DB::rollback();
        //     return ['status'=>false,'message'=>'充值失败'];
        // }

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
