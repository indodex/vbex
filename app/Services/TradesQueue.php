<?php

namespace App\Services;

use App\Services\BaseService;
use App\Models\AccountsModel;
use App\Models\TradesOrdersModel;
use App\Models\CurrencyModel;
use App\Models\UserModel;
use App\Models\TradesCurrenciesModel;
use App\Redis\TradesRadis;

use DB;

class TradesQueue extends BaseService
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

    /**
     * 委托买单
     * @param  [type] $uid    [description]
     * @param  [type] $market [description]
     * @param  [type] $price  [description]
     * @param  [type] $number [description]
     * @return [type]         [description]
     */
    public function buyOrder($uid, $market, $price, $number)
    {
        list($buyCurr, $sellCurr) = explode('_', $market);

        $sellCurrency = $this->getCurrencyModel()
                             ->getByCode($sellCurr);
        if(empty($sellCurrency)) {
            return $this->error(__('api.market.currency_non_existent'));
        }
        if($sellCurrency->status == 0) {
            return $this->error(__('api.market.currency_close'));
        }

        $buyCurrency = $this->getCurrencyModel()
                            ->getByCode($buyCurr);

        if(empty($buyCurrency)) {
            return $this->error(__('api.market.currency_non_existent'));
        }
        if($buyCurrency->status == 0) {
            return $this->error(__('api.market.currency_close'));
        }

        $tradeCurrency = $this->getTradesCurrenciesModel()->getMarket($buyCurrency->id, $sellCurrency->id);
        if(empty($tradeCurrency)) {
            return $this->error(__('api.market.currency_non_existent'));
        }

        $accountsModel = $this->getAccountsModel()
                              ->setCurrency($sellCurrency->id);

        // 账号创建
        $account = $accountsModel->getAccount($uid);

        // 余额检查
        $amount  = $price * $number;
        if($account->balance < $amount) {
            return $this->error(__('api.account.not_sufficient_funds'));
        }

        $result = $this->getTradesRadis()->publishEntrust([
                    'uid'    => $uid,
                    'market' => $market,
                    'price'  => $price,
                    'number' => $number,
                    'isBuy'  => 1,
                ]);

        if($result) {
            return $this->success('success');
        } else {
            DB::rollback();
            return $this->error(__('api.trade.trades_failed'));
        }
    }

    /**
     * 卖出订单
     * @param  [type] $uid    [description]
     * @param  [type] $market [description]
     * @param  [type] $price  [description]
     * @param  [type] $number [description]
     * @return [type]         [description]
     */
    public function sellOrder($uid, $market, $price, $number)
    {
        list($firstCurr, $secondCurr) = explode('_', $market);

        $firstCurrency = $this->getCurrencyModel()
                         ->getByCode($firstCurr);
        if(empty($firstCurrency)) {
            return $this->error(__('api.market.currency_non_existent'));
        }
        if($firstCurrency->status == 0) {
            return $this->error(__('api.market.currency_close'));
        }

        $secondCurrency = $this->getCurrencyModel()
                         ->getByCode($secondCurr);
        if(empty($secondCurrency)) {
            return $this->error(__('api.market.currency_non_existent'));
        }
        if($secondCurrency->status == 0) {
            return $this->error(__('api.market.currency_close'));
        }

        $tradeCurrency = $this->getTradesCurrenciesModel()->getMarket($firstCurrency->id, $secondCurrency->id);
        if(empty($tradeCurrency)) {
            return $this->error(__('api.market.currency_non_existent'));
        }

        $accountsModel = $this->getAccountsModel()
                              ->setCurrency($firstCurrency->id);

        // 账号创建
        $account = $accountsModel->getAccount($uid);
        if(empty($account)) {
            return $this->error(__('api.account.account_empty'));
        }

        // 余额检查
        $amount  = $price * $number;
        if($account->balance < $amount) {
            return $this->error(__('api.account.not_sufficient_funds'));
        }

        $result = $this->getTradesRadis()->publishEntrust([
                    'uid'    => $uid,
                    'market' => $market,
                    'price'  => $price,
                    'number' => $number,
                    'isBuy'  => 0,
                ]);

        if($result) {
            return $this->success('success');
        } else {
            return $this->error(__('api.trade.trades_failed'));
        }
    }

    private function getTradesOrdersModel()
    {
        return new TradesOrdersModel();
    }

    private function getCurrencyModel()
    {
        return new CurrencyModel();
    }

    private function getUserModel()
    {
        return new UserModel();
    }

    private function getAccountsModel()
    {
        return new AccountsModel();
    }

    public function getTradesRadis()
    {
        return new TradesRadis();
    }

    private function getTradesBuyLogs()
    {
        return new TradesBuyLogs();
    }

    private function getTradesCurrenciesModel()
    {
        return new TradesCurrenciesModel();
    }
}
