<?php

namespace App\Services;

use App\Services\BaseService;
use App\Models\WithdrawsAddressesModel;
use App\Models\WithdrawsOrdersModel;
use App\Models\CurrencyModel;
use App\Models\AccountsModel;
use App\Models\AccountsDetailsModel;
use App\Models\UserModel;
use App\Models\CurrencyFeeModel;

use App\Events\OrderShipped;

use DB;

class Withdraw extends BaseService
{

    public function getUserAddressList($uid, $currencyCode)
    {
        if(empty($uid)) {
            $this->error(__('api.account.lack_user'));
        }
        if(empty($currencyCode)) {
            $this->error(__('api.account.lack_currency'));
        }

        $currency = $this->getCurrencyModel()->getByCode($currencyCode);
        if(empty($currency)) {
            $this->error(__('api.account.account_empty'));
        }

        $addressModel = $this->getAddressesModel()->setCurrency($currency->id);
        $data = $addressModel->getListByUid($uid, 1, 99);
        
        if(!empty($data)) {
            $data = $data->toArray();
            return $this->success($data);
        } else {
            return $this->error(__('api.public.empty_data'));
        }
    }

    /**
     * 添加提现申请码
     * @param  [type] $uid      [description]
     * @param  [type] $currency [description]
     * @return [type]           [description]
     */
    public function createAddress($data) 
    {
        $uid      = (int) $data['uid'];
        $coinType = (string) $data['coinType'];
        $currency = $this->getCurrencyModel()
                         ->getIdByCode($coinType);
        if(is_null($currency)) {
            return null;
        }

        $addressModel = $this->getAddressesModel()->setCurrency($currency);
        $address = $addressModel->getInfoByUid($uid);
        if(!empty($address)) {
            return $address;
        }

        $id = $addressModel->createAddress([
                    'uid'        => $uid,
                    'currency'   => $currency,
                    'name'       => $data['name'],
                    'address'    => $data['address'],
                    'is_default' => 0,
                ]);
        return $addressModel->getInfo($id);
    }

    /**
     * 数字资产转出
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function apply($data) 
    {
        $uid       = $data['uid'];
        $address   = $data['name'] = $data['address'];
        $coinType  = $data['coinType'];
        $sumAmount = $data['amount'];

        // 提现地址不能为空
        if(empty($address)) {
            return $this->error(__('api.withdraw.address_error'));
        }

        // 获取货币详细
        $currency = $this->getCurrencyModel()->getByCode($coinType);
        if(empty($currency)) {
            return $this->error(__('api.account.currency_empty'));
        }

        if($currency->enable_withdraw == 0) {
            return $this->error(__('api.withdraw.withdraw_closed'));
        }

        // 是否最低提现标准
        if(bccomp($currency->min_withdraw_amount, $sumAmount, 18) == 1) {
            return $this->error(__('api.account.less_min_withdraw_amount'));
        }

        // 不存在提现地址
        $addressInfo = $this->getAddressesModel()
                            ->setCurrency($currency->id)
                            ->getInfoByAddress($uid, $address);

        if(empty($addressInfo)) {
            return $this->error(__('api.withdraw.address_error'));
        }
        $data['name'] = $addressInfo['name'];

        DB::beginTransaction();

        // 当前用户余额
        $accountsModel = $this->getAccountsModel()->setCurrency($currency->id);
        $account       = $accountsModel->getAccountLock($uid);

        // 余额是否足够
        if(bccomp($account->balance, $sumAmount, 18) == -1) {
            DB::rollback();
            return $this->error(__('api.account.not_sufficient_funds'));
        }

        // 创建提现钱包
        $this->createAddress($data);
        
        // 计算手续费
        $fee           = $currency->withdraw_service_charge;

        // 实到金额
        $amount        = bcsub($sumAmount, $fee, 18);
        
        // 余额变动
        $changeBalance = $accountsModel->decrementBalance($account->uid, $sumAmount);
        
        $changeLocked  = $accountsModel->incrementLocked($account->uid, $sumAmount);
        
        $insertOrderId = $this->getOrdersModel()->createOrder([
                                'uid'          => $uid,
                                'currency'     => $currency->id,
                                'fee'          => $fee,
                                'amount'       => $amount,
                                'sum_amount'   => $sumAmount,
                                'address_name' => $data['name'],
                                'address'      => $address,
                                'remark'       => '',
                         ]);

        $accountInfo   = $accountsModel->getInfoByUid($account->uid);
        
        if($changeBalance && $changeLocked && $insertOrderId > 0) {
            DB::commit();

            $this->mailTo($insertOrderId);
            return $this->success($insertOrderId);
        } else {
            DB::rollback();
            return $this->error(__('api.account.withdraw_apply_failed'));
        }
    }

    public function mailTo($orderId)
    {
        return event(new OrderShipped(WithdrawsOrdersModel::findOrFail($orderId)));
    }

    public function addAddress($data) 
    {
        $uid      = (int) $data['uid'];
        $address  = (string) $data['address'];
        $coinType = (string) $data['coinType'];
        $currency = $this->getCurrencyModel()
                         ->getIdByCode($coinType);

        if(is_null($currency)) {
            return null;
        }

        $addressModel = $this->getAddressesModel()->setCurrency($currency);
        $addressInfo = $addressModel->getInfoByAddress($uid, $address);
        if(!empty($addressInfo)) {
            ;
            $addressModel->editAddressById($addressInfo->id, ['name' => $data['name'], 'status' => 1]);
            return $this->success($addressInfo);
        }

        $id = $addressModel->createAddress([
                    'uid'        => $uid,
                    'currency'   => $currency,
                    'name'       => $data['name'],
                    'address'    => $data['address'],
                    'is_default' => 0,
                ]);
        $wallet = $addressModel->getInfo($id);

        if(isset($wallet)) {
            if(is_object($wallet)) {
                $wallet = $wallet->toArray();
            }
            return $this->success($wallet);
        } else {
            return $this->error(__('api.account.created_address_fail'));
        }
    }

    public function editAddress($id, $saveData) 
    {
        $uid      = (int) $data['uid'];
        $name     = (string) $data['name'];
        $address  = (string) $data['address'];
        $coinType = (string) $data['coinType'];

        $currency = $this->getCurrencyModel()
                         ->getIdByCode($coinType);

        if(is_null($currency)) {
            return $this->error(__('api.account.currency_non_existent'));
        }

        $addressInfo = $this->getAddressesModel()->getInfo($id);
        if(empty($addressInfo)) {
            return $this->error(__('api.account.wallet_non_existent'));
        }

        $this->getAddressesModel()->editAddressById($id, [
            'name'    => $name,
            'address' => $address,
        ]);
        $wallet = $this->getAddressesModel()->getInfo($id);

        if(isset($wallet)) {
            if(is_object($wallet)) {
                $wallet = $wallet->toArray();
            }
            return $this->success($wallet);
        } else {
            return $this->error(__('api.account.created_address_fail'));
        }
    }

    public function deleteAddress($id) 
    {
        $addressInfo = $this->getAddressesModel()->getInfo($id);
        if(empty($addressInfo)) {
            return $this->error(__('api.account.wallet_non_existent'));
        }

        $res = $this->getAddressesModel()->editAddressById($id, ['status' => 0]);
        if($res) {
            return $this->success([]);
        } else {
            return $this->error(__('api.account.created_address_fail'));
        }
    }

    public function getWithdrawAddress($id) 
    {
        $address = $this->getAddressesModel()->getInfo($id);
        if(empty($address)) {
            return $this->error(__('api.account.wallet_non_existent'));
        }

        if(is_object($address)) {
            $address = $address->toArray();
        }

        return $this->success($address);
    }

    private function getAccountsModel()
    {
        return new AccountsModel();
    }

    private function getAccountsDetailsModel()
    {
        return new AccountsDetailsModel();
    }

    private function getAddressesModel()
    {
        return new WithdrawsAddressesModel();
    }

    private function getOrdersModel()
    {
        return new WithdrawsOrdersModel();
    }

    private function getCurrencyModel()
    {
        return new CurrencyModel();
    }

    private function getUserModel()
    {
        return new UserModel();
    }

    private function getCurrencyFeeModel()
    {
        return new CurrencyFeeModel();
    }
}
