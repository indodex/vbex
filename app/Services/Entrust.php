<?php

namespace App\Services;

use App\Services\BaseService;
use App\Services\Currency;
use App\Services\TradesFee;

use App\Models\AccountsModel;
use App\Models\AccountsDetailsModel;
use App\Models\TradesOrdersModel;
use App\Models\TradesCurrenciesModel;
use App\Models\TradesOrdersDetailsModel;
use App\Models\CurrencyModel;
use App\Models\UserModel;
use App\Models\AdminConfigModel;

use App\Monolog\TradesBuyLogs;
use App\Monolog\TradesSellLogs;

use DB;

class Entrust extends BaseService
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

        $logs = [
            'uid' => $uid,
            'buy_currency' => $buyCurr,
            'sell_currency' => $sellCurr,
        ];

        $sellCurrency = $this->getCurrencyModel()
                             ->getByCode($sellCurr);
        if(empty($sellCurrency)) {
            return $this->_buylogs($logs, __('api.market.currency_non_existent'));
        }
        if($sellCurrency->status == 0) {
            return $this->_buylogs($logs, __('api.market.currency_close'));
        }

        $buyCurrency = $this->getCurrencyModel()
                            ->getByCode($buyCurr);
        if(empty($buyCurrency)) {
            return $this->_buylogs($logs, __('api.market.currency_non_existent'));
        }
        if($buyCurrency->status == 0) {
            return $this->_buylogs($logs, __('api.market.currency_close'));
        }

        // $isMaxPrice = $this->calculateBuyMaxPrice($buyCurrency->id, $sellCurrency->id, $price);
        // if(!$isMaxPrice) {
        //     return $this->_buylogs($logs, __('api.trade.price_hight'));
        // }

        $user = $this->getUserModel()->getInfo($uid);

        if($user->is_freeze == 1) {
            return $this->_buylogs($user, __('api.account.user_is_freeze'));
        }

        if($user->is_lock == 1) {
            return $this->_buylogs($user, __('api.account.user_is_lock'));
        }

        $accountsModel = $this->getAccountsModel()
                              ->setCurrency($sellCurrency->id);

        // 交易开始
        DB::beginTransaction();

        // 账号创建
        $account = $accountsModel->getAccountLock($uid);

        // 余额检查
        $amount  = bcmul($price, $number, 18);
        if(bccomp($account->balance, $amount) == -1) {
            DB::rollback();
            return $this->_buylogs($logs, __('api.account.not_sufficient_funds'));
        }

        // 扣除余额
        $decbaRes = $accountsModel->decrementBalance($uid, $amount);
        
        // 添加锁金额
        $incLkRes = $accountsModel->incrementLocked($uid, $amount);

        $accountsModel = $this->getAccountsModel()
                              ->setCurrency($sellCurrency->id);
        
        // 生成委托单
        $tradeRes = $logs['id'] = $this->getTradesOrdersModel()->createOrder([
                        'uid'           => $uid,
                        'buy_currency'  => $buyCurrency->id,
                        'sell_currency' => $sellCurrency->id,
                        'price'         => $price,
                        'num'           => $number,
                        'deduction'     => $user->is_deduction,
                        'status'        => 3,
                    ]);

        // 账号详情
        $capitalDetail = $this->getAccountsDetailsModel()
                              ->createDetail([
                                'uid'            => $user->id,
                                'currency'       => $sellCurrency->id,
                                'type'           => -2,
                                'change_balance' => $amount,
                                'balance'        => bcsub($account->balance, $amount, 18),
                                'remark'         => __('api.account.entrust'),
                            ]);
        
        if($decbaRes && 
            $incLkRes && 
            $capitalDetail && 
            $tradeRes) {
             DB::commit();
             $this->_buylogs($logs, 'success');
            return $this->success('success');
        } else {
            DB::rollback();
            return $this->_buylogs($logs, __('api.trade.trades_failed'));
        }
    }

    private function _buylogs($data, $message)
    {
        $this->getTradesBuyLogs()->addLogs([
            'id'            => isset($data['id']) ? $data['id'] : 0,
            'uid'           => $data['uid'],
            'message'       => $message,
            'buy_currency'  => $data['buy_currency'],
            'sell_currency' => $data['sell_currency'],
            'data'          => $data,
        ]);
        return $this->error($message);
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

        $logs = [
            'uid' => $uid,
            'buy_currency' => $firstCurr,
            'sell_currency' => $secondCurr,
        ];

        $firstCurrency = $this->getCurrencyModel()
                         ->getByCode($firstCurr);
        if(empty($firstCurrency)) {
            return $this->_selllogs($logs, __('api.market.currency_non_existent'));
        }
        if($firstCurrency->status == 0) {
            return $this->_selllogs($logs, __('api.market.currency_close'));
        }

        $secondCurrency = $this->getCurrencyModel()
                         ->getByCode($secondCurr);
        if(empty($secondCurrency)) {
            return $this->_selllogs($logs, __('api.market.currency_non_existent'));
        }
        if($secondCurrency->status == 0) {
            return $this->_selllogs($logs, __('api.market.currency_close'));
        }

        $user = $this->getUserModel()->getInfo($uid);
        
        if($user->is_freeze == 1) {
            return $this->_selllogs($user, __('api.account.user_is_freeze'));
        }

        if($user->is_lock == 1) {
            return $this->_selllogs($user, __('api.account.user_is_lock'));
        }

        // 开始充值
        DB::beginTransaction();

        $accountsModel = $this->getAccountsModel()
                              ->setCurrency($firstCurrency->id);

        // 账号创建
        $account = $accountsModel->getAccountLock($uid);

        // 余额检查
        $amount  = $number;
        if(bccomp($account->balance, $amount) == -1) {
            DB::rollback();
            return $this->_selllogs($logs, __('api.account.not_sufficient_funds'));
        }

        // 扣除余额
        $decbaRes = $accountsModel->decrementBalance($uid, $amount);
        
        // 添加锁金额
        $incLkRes = $accountsModel->incrementLocked($uid, $amount);
        
        // 生成委托单
        $tradeRes = $logs['id'] = $this->getTradesOrdersModel()->createOrder([
                        'uid'           => $uid,
                        'buy_currency'  => $secondCurrency->id,
                        'sell_currency' => $firstCurrency->id,
                        'price'         => $price,
                        'num'           => $number,
                        'deduction'     => $user->is_deduction,
                        'status'        => 3,
                    ]);

        // 账号详情
        $capitalDetail = $this->getAccountsDetailsModel()
                              ->createDetail([
                                'uid'            => $user->id,
                                'currency'       => $firstCurrency->id,
                                'type'           => -2,
                                'change_balance' => $amount,
                                'balance'        => bcsub($account->balance, $amount, 18),
                                'remark'         => __('api.account.entrust'),
                            ]);

        if($decbaRes && 
            $incLkRes && 
            $capitalDetail && 
            $tradeRes) {
             DB::commit();
             $this->_selllogs($logs, 'success');
            return $this->success('success');
        } else {
            DB::rollback();
            return $this->_selllogs($logs, __('api.trade.trades_failed'));
        }
    }

    private function _selllogs($data, $message)
    {
        $this->getTradesSellLogs()->addLogs([
            'id'            => isset($data['id']) ? $data['id'] : 0,
            'uid'           => $data['uid'],
            'message'       => $message,
            'buy_currency'  => $data['buy_currency'],
            'sell_currency' => $data['sell_currency'],
            'data'          => $data,
        ]);
        return $this->error($message);
    }

    /**
     * 取消订单
     * @param  [type] $uid    [description]
     * @param  [type] $market [description]
     * @param  [type] $price  [description]
     * @param  [type] $number [description]
     * @return [type]         [description]
     */
    public function cancelOrder($id)
    {
        if(empty($id)) {
            return $this->_cancelError($id, __('api.public.missing_params'));
        }

        // 开始充值
        DB::beginTransaction();

        // 订单查询
        $order = $this->getTradesOrdersModel()->getLockInfo($id);

        // 交易不存在
        if(empty($order)) {
            DB::rollback();
            return $this->_cancelError($id, __('api.trade.trades_non_existent'));
        }

        // 交易已完成
        if($order->status == 1) {
            DB::rollback();
            return $this->_cancelError($id, __('api.trade.trades_completed'));
        }

        // 交易已取消
        if($order->status == 0) {
            DB::rollback();
            return $this->_cancelError($id, __('api.trade.trades_canceled'));
        }

        // 设置交易货币基础账号
        $accountsModel = $this->getAccountsModel()
                              ->setCurrency($order->sell_currency);

        // 获取交易货币账号
        $account = $accountsModel->getAccountLock($order->uid);
        
        // 判断是否买入卖出
        $isMarket = $this->isMarket($order->buy_currency, $order->sell_currency);
        if(!empty($isMarket)) {

            // 计算剩余委托总数
            $surplusNumber = bcsub($order->num, $order->successful_num, 18);

            // 剩余委托总价格
            $surplusAmount = bcmul($surplusNumber, $order->price, 18);

            // 对比剩余委托价格与余额
            if(bccomp($account->locked, $surplusAmount) == -1) {
                DB::rollback();
                return $this->_cancelError($id, __('api.trade.trades_cancel_failed'));
            }

            // 扣除锁定额
            $decbaRes = $accountsModel->decrementLocked($order->uid, $surplusAmount);
            
            // 增加余额
            $incLkRes = $accountsModel->incrementBalance($order->uid, $surplusAmount);
        } else {

            // 计算剩余委托总数
            $surplusAmount = bcsub($order->num, $order->successful_num, 18);

            // 对比剩余委托价格与余额
            if(bccomp($account->locked, $surplusAmount) == -1) {
                DB::rollback();
                return $this->_cancelError($id, __('api.trade.trades_cancel_failed'));
            }

            // 扣除锁定额
            $decbaRes = $accountsModel->decrementLocked($order->uid, $surplusAmount);
            
            // 增加余额
            $incLkRes = $accountsModel->incrementBalance($order->uid, $surplusAmount);
        }
        // 取消委托单
        $tradeRes = $this->getTradesOrdersModel()->cancelOrder($id);

        // 账号详情
        $capitalDetail = $this->getAccountsDetailsModel()
                              ->createDetail([
                                'uid'            => $order->uid,
                                'currency'       => $order->sell_currency,
                                'type'           => -2,
                                'change_balance' => $surplusAmount,
                                'balance'        => bcadd($account->balance, $surplusAmount, 18),
                                'remark'         => __('api.trade.trades_canceled'),
                            ]);
        

        // 结果
        if($decbaRes && $incLkRes && $tradeRes) {
             DB::commit();
            $this->_cancelError($id, 'success');
            return $this->success('success');
        } else {
            DB::rollback();
            return $this->_cancelError($id, __('api.trade.trades_failed'));
        }
    }

    private function _cancelError($id, $message)
    {
        // $this->getTradesCancelLogs()->addLogs([
        //     'id'      => $id,
        //     'message' => $message
        // ]);
        return $this->error($message);
    }

    private function isMarket($buyCurrency, $sellCurrency)
    {
        return $this->getTradesCurrenciesModel()->getMarket($buyCurrency, $sellCurrency);
    }

    public function calculateBuyMaxPrice($mainCurrency, $exchangeCurrency, $price)
    {
        $cond['buy_currency'] = $mainCurrency;
        $cond['sell_currency'] = $exchangeCurrency;
        $trade     = $this->getTradesDetailsModel()->getOne($cond);
        if(empty($trade)) {
            return true;
        }
        $lastPrice = (float) $trade->price;
        $multiple  = $price / $lastPrice;

        $value = $this->getAdminConfigModel()->getConfigValue('max_trade_rate');
        if($multiple > $value) {
            return false;
        } else {
            return true;
        }
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

    private function getTradesFeeService()
    {
        return new TradesFee();
    }

    private function getUserModel()
    {
        return new UserModel();
    }

    private function getAccountsModel()
    {
        return new AccountsModel();
    }

    private function getAccDetailsModel()
    {
        return new AccountsDetailsModel();
    }

    private function getTradesBuyLogs()
    {
        return new TradesBuyLogs();
    }

    private function getTradesSellLogs()
    {
        return new TradesSellLogs();
    }

    private function getTradesCurrenciesModel()
    {
        return new TradesCurrenciesModel();
    }

    private function getAdminConfigModel()
    {
        return new AdminConfigModel();
    }

    private function getAccountsDetailsModel()
    {
        return new AccountsDetailsModel();
    }
}
