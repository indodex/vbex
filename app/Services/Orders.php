<?php

namespace App\Services;

use App\Services\BaseService;
use App\Services\Currency;

use App\Models\TradesOrdersModel;
use App\Models\TradesCurrenciesModel;
use App\Models\TradesOrdersDetailsModel;
use App\Models\CurrencyModel;
use App\Models\UserModel;
use DB;

class Orders extends BaseService
{
    private $uid;

    public function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function getOrdersDetailsById($id, $buyCurrency)
    {
        $data = $this->getTradesDetailsModel()->where(['buy_id' => $id, 'buy_currency' => $buyCurrency])->get();
        return $data;
    }

    public function getOrderInfo($id)
    {
        return $this->getTradesOrdersModel()->where(['id' => $id])->first();
    }

    private function getTradesOrdersModel()
    {
        return new TradesOrdersModel();
    }

    private function getTradesDetailsModel()
    {
        return new TradesOrdersDetailsModel();
    }

    private function getCurrencyModel()
    {
        return new CurrencyModel();
    }

    private function getCurrencyService()
    {
        return new Currency();
    }

    private function getUserModel()
    {
        return new UserModel();
    }
}
