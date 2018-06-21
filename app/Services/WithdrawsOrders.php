<?php

namespace App\Services;

use App\Services\BaseService;

use App\Models\WithdrawsOrdersModel;
use App\Models\CurrencyModel;
use App\Models\UserModel;
use App\Repositories\BlockFactory;

class WithdrawsOrders extends BaseService
{
    /**
     * 获取虚拟货币充值地址
     * @param  [type] $uid      [description]
     * @param  [type] $currency [description]
     * @return [type]           [description]
     */
    public function getOrders($uid, $currencyCode, $page)
    {
        if(empty($uid)) {
            $this->error(__('api.account.lack_user'));
        }
        if(empty($currencyCode)) {
            $this->error(__('api.account.lack_currency'));
        }

        $currency = $this->getCurrencyModel()->getByCode($currencyCode);

        if(empty($currency)) {
            $this->error(__('api.account.currency_non_existent'));
        }

        $ordersModel = $this->getWithdrawsOrdersModel()
                             ->setCurrency($currency->id);

        $orders = $ordersModel->getListByUid($uid, $page);
        $orders = $orders->toArray();
        $factory = $this->getBlockFactory();
        foreach ($orders['data'] as $key => &$value) {
            $value['fee']        = (float) $value['fee'];
            $value['amount']     = $value['amount'] > 0 ? (float) my_number_format($value['amount'], 8) : '--';
            $value['sum_amount'] = (float) my_number_format($value['sum_amount'], 8);
            $value['done_at']    = !empty($value['done_at']) ? $value['done_at'] : '--';
            $value['created_at'] = strtotime($value['created_at']);
            $value['created_at'] = date('y-m-d H:i:s', $value['created_at']);
            $value['to_url']     = '';
            $value['status_str'] = $ordersModel->toStatusName($value['status']);
            $value['url']        = $factory->create($currencyCode)->getTransactionInfoUrl($value['txid']);
            if(bccomp($value['amount'], $currency->extract_number_audit, 8) > -1) {
                $value['allowTransfer'] = false;
            } else {
                $value['allowTransfer'] = true;
            }
        }

        return $this->success($orders);
    }

    public function getOrder($id)
    {
        return WithdrawsOrdersModel::find($id);
    }

    private function getWithdrawsOrdersModel()
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

    private function getBlockFactory()
    {
        return new BlockFactory();
    }
}
