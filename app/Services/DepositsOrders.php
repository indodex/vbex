<?php

namespace App\Services;

use App\Services\BaseService;

use App\Models\DepositsOrdersModel;
use App\Models\CurrencyModel;
use App\Models\UserModel;
use App\Repositories\BlockFactory;

class DepositsOrders extends BaseService
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

        $ordersModel = $this->getDepositsOrdersModel()
                             ->setCurrency($currency->id);

        $orders = $ordersModel->getListByUid($uid, $page);
        $orders = $orders->toArray();
        $factory = $this->getBlockFactory();
        foreach ($orders['data'] as $key => &$value) {
            $value['fee']        = (float) $value['fee'];
            $value['amount']     = (float) $value['amount'];
            $value['to_url']     = '';
            $value['status_str'] = $ordersModel->toStatusName($value['status']);
            $value['url']        = $factory->create($currencyCode)->getTransactionInfoUrl($value['txid']);
        }

        return $this->success($orders);
    }

    private function getDepositsOrdersModel()
    {
        return new DepositsOrdersModel();
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
