<?php

namespace App\Services;


use DB;
use App\Services\BaseService;
use App\Models\UserModel;
use App\Models\UserRechargeCodeOrdersModel;
use App\Models\CurrencyModel;
use App\Models\AccountsModel;
use App\Models\AccountsDetailsModel;
use App\Models\RechargeItemsModel;
use App\Models\UserRechargeCodeModel;

class RechargeUser extends BaseService
{

    public function createCode($itemsId, $currencyCode) 
    {
        $items = $this->getRechargeItemsModel()->getInfo($itemsId);

        if(empty($items)) {
            return $this->error(__('api.recharge.empty_items'));
        }

        $currency      = $this->getCurrencyModel()->getByCode($currencyCode);
        $user          = $this->getUser();
        $accountsModel = $this->getAccountsModel()->setCurrency($currency->id);
        $account       = $accountsModel->getAccount($user->id);

        if(bccomp($account->balance, $items->amount) == -1) {
            return $this->error(__('api.recharge.balance_not_enough'));
        }
        
        $cards  = make_cards(1);
        $amount = (float) $items->amount;
        $site   = DB::table('admin_config')->where('name', 'site_simple_name')->first();
        $code   = $amount . '_' . $site->value . '_US' . $cards[0] . '_' . date('YmdH');
        $audit  = 1;

        $rechargeCodeModel = $this->getUserRechargeCodeModel();
        $sumAmount = $rechargeCodeModel->getToDaySum($user->id);
        $sumAmount = bcadd($sumAmount, $amount);
        if(bccomp($currency->recharge_number_audit, $sumAmount) == -1) {
            $audit = 0;
        }

        DB::beginTransaction();

        $recharge      = $rechargeCodeModel->createCode([
                            'code'     => $code,
                            'amount'   => $amount,
                            'currency' => $account->currency,
                            'uid'      => $user->id,
                            'audit'    => $audit,
                        ]);

        $resCapital    = $accountsModel->decrementBalance($account->uid, $amount);

        $lockCapital   = $accountsModel->incrementLocked($account->uid, $amount);
        
        $accountInfo   = $accountsModel->getInfoByUid($account->uid);
        
        // 充值记录
        $capitalDetail = $this->getAccountsDetailsModel()
                              ->createDetail([
                                'uid'            => $user->id,
                                'currency'       => $account->currency,
                                'type'           => 3,
                                'change_balance' => $amount,
                                'balance'        => $accountInfo->balance,
                                'remark'         => __('api.recharge.create_code'),
                            ]);
        
        if($recharge && 
            $resCapital && 
            $lockCapital && 
            $capitalDetail) {
             DB::commit();
            return $this->success('');
        } else {
            DB::rollback();
            return $this->error(__('api.recharge.create_fail'));
        }
    }

    public function rechargeCode($uid, $code, $remark = '') 
    {
        $codeInfo = $this->getUserRechargeCodeModel()->getInfoByCode($code);

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

        if($codeInfo->audit == 0) {
            return $this->error(__('api.recharge.code_unaudit'));
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
                            'remark'        => $remark,
                            'done_at'       => date('Y-m-d H:i:s'),
                            'status'        => 1,
                        ]);

        // 充值码
        $applyCode = $this->getUserRechargeCodeModel()
                          ->applyCode($codeInfo->id, $user->id, 1);

        // 充值
        $incRes = $accountsModel->incrementBalance($user->id, $codeInfo->amount);

        // 充值码锁定
        $lockRes = $accountsModel->decrementLocked($codeInfo->uid, $codeInfo->amount);

        $accountInfo = $accountsModel->getInfoByUid($user->id);

        // 充值记录
        $capitalDetail = $this->getAccountsDetailsModel()
                              ->createDetail([
                                'uid'            => $user->id,
                                'currency'       => $codeInfo->currency,
                                'type'           => 1,
                                'change_balance' => $codeInfo->amount,
                                'balance'        => $accountInfo->balance,
                                'remark'         => $remark,
                            ]);

        if($applyOrder && 
            $applyCode && 
            $incRes && 
            $lockRes && 
            $capitalDetail) {
             DB::commit();
            return $this->success('success');
        } else {
            DB::rollback();
            return $this->error(__('api.recharge.recharge_fail'));
        }
    }


    public function auditApply($id, $status, $remark = '') 
    {
        $codeModel = $this->getUserRechargeCodeModel();
        $codeInfo = $codeModel->getInfo($id);

        if($codeInfo->audit != 0) {
            return $this->error('已处理，请不要重复处理一个充值码');
        }

        switch ($status) {
            case '0':
                return $this->error('审核状态有误请重新审核');
                break;

            case '1':
                $rest = $codeModel->applyAudit($id, $status);
                if($rest) {
                    return $this->success('success');
                } else {
                    return $this->error('审核失败请再次审核');
                }
                break;

            case '-1': // 退款
                
                // 用户账号
                $accountsModel = $this->getAccountsModel()->setCurrency($codeInfo->currency);
                $account       = $accountsModel->getAccount($codeInfo->uid, $codeInfo->currency);

                // 开始充值
                DB::beginTransaction();

                // 审核充值记录
                $applyOrder     = $this->getCodeOrdersModel()->createOrder([
                                    'uid'           => $codeInfo->uid,
                                    'code_id'       => $id,
                                    'amount'        => $codeInfo->amount,
                                    'confirmations' => 0,
                                    'remark'        => $remark,
                                    'done_at'       => date('Y-m-d H:i:s'),
                                    'status'        => 1,
                                ]);

                // 充值码
                $applyCode     = $codeModel->applyCode($codeInfo->id, $codeInfo->uid, 4);

                $applyAudit    = $codeModel->applyAudit($codeInfo->id, $status);
                
                // 充值
                $incRes        = $accountsModel->incrementBalance($codeInfo->uid, $codeInfo->amount);
                
                // 充值码锁定
                $lockRes       = $accountsModel->decrementLocked($codeInfo->uid, $codeInfo->amount);
                
                $accountInfo   = $accountsModel->getInfoByUid($codeInfo->uid);
                
                // 充值记录
                $capitalDetail = $this->getAccountsDetailsModel()
                                      ->createDetail([
                                        'uid'            => $codeInfo->uid,
                                        'currency'       => $codeInfo->currency,
                                        'type'           => 1,
                                        'change_balance' => $codeInfo->amount,
                                        'balance'        => $accountInfo->balance,
                                        'remark'         => $remark,
                                    ]);

                if($applyOrder && 
                    $applyCode && 
                    $incRes && 
                    $lockRes && 
                    $applyAudit && 
                    $capitalDetail) {
                     DB::commit();
                    return $this->success('success');
                } else {
                    DB::rollback();
                    return $this->error('操作失败');
                }

                break;
        }
    }


    private function getCodeOrdersModel()
    {
        return new UserRechargeCodeOrdersModel();
    }

    private function getRechargeItemsModel()
    {
        return new RechargeItemsModel();
    }

    private function getUserRechargeCodeModel()
    {
        return new UserRechargeCodeModel();
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

    private function getCurrencyModel()
    {
        return new CurrencyModel();
    }
}
