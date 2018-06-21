<?php

namespace App\Services;


use DB;
use App\Services\BaseService;

use App\Models\UserModel;
use App\Models\CurrencyModel;
use App\Models\AccountsModel;
use App\Models\AccountsDetailsModel;
use App\Models\TradesFeeModel;
use App\Models\TradesOrdersDetailsModel;

class TradesFee extends BaseService
{

    public function lockFree($data)
    {
        $uid           = $data['uid'];
        $number        = $data['number'];
        $buyCurrency   = $data['buy_currency'];
        $tradingRate   = $data['trading_service_rate'];
        $tradesFee     = $number * $data['trading_service_rate'];
        $orderId       = $data['order_id'];
        
        $currencyModel = $this->getCurrencyModel();
        $feeCoin       = $currencyModel->getByCode('HAC');
        $feeCurrency   = $feeCoin->id;
        
        $accountsModel = $this->getAccountsModel()
                              ->setCurrency($feeCurrency);

        $account = $accountsModel->getAccountLock($uid);

        // 最新交易
        $where['whereIn']['buy_currency']  = array($buyCurrency, $feeCurrency);
        $where['whereIn']['sell_currency'] = array($buyCurrency, $feeCurrency);
        $order  = $this->getOrdersDetailsModel()->getOne($where);
        if(empty($order)) {
            return false;
        }
        
        $price  = (float) $order->price;
        $amount = bcmul($tradesFee, $price, $feeCoin->decimals);

        if($account->balance < $amount) {
            return false;
        }

        // 扣除余额
        $decbaRes = $accountsModel->decrementBalance($uid, $amount);
        
        // 添加锁金额
        $incLkRes = $accountsModel->incrementLocked($uid, $amount);

        return $this->getTradesFeeModel()->createOrder([
            'uid' => $uid,
            'order_id' => $orderId,
            'buy_currency' => $buyCurrency,
            'sell_currency' => $feeCurrency,
            'price' => $price,
            'successful_num' => 0,
            'successful_count' => 0,
            'done_at' => '',
            'status' => 3,
        ]);
    }

    public function useFree($data)
    {
        $uid         = $data['uid'];
        $num         = $data['num'];
        $orderId     = $data['order_id'];
        $detailId    = $data['detail_id'];
        $sucNum      = $data['successful_num'];
        $buyCurrency = $data['buy_currency'];
        $rate        = $sucNum / $num;
        
        $currencyModel = $this->getCurrencyModel();
        $feeCurrency   = $currencyModel->getIdByCode('HAC');
        
        $accountsModel = $this->getAccountsModel()
                              ->setCurrency($feeCurrency);

        $account = $accountsModel->getAccountLock($uid);

        $feeOrder = $this->getTradesFeeModel()->getOne(['order_id' => $orderId]);
        if(empty($feeOrder)) {
            return false;
        }

        $tradeNum = $feeOrder->num * $rate;
        $fee      = $tradeNum - $feeOrder->successful_num;

        $this->getFeeDetailsModel()->createOrder([
            'uid' => $uid,
            'fee_id' => $feeOrder->id,
            'detail_id' => $detailId,
            'buy_currency' => $buyCurrency,
            'sell_currency' => $feeCurrency,
            'fee' => $fee,
            'num' => $sucNum,
        ]);
        
        // 添加锁金额
        $decLkRes = $accountsModel->decrementLocked($uid, $fee);

        // 充值记录
        $balance = $account->balance - $fee;
        $capitalDetail = $this->getAccountsDetailsModel()->createDetail([
                            'uid'            => $uid,
                            'currency'       => $feeCurrency,
                            'type'           => -2,
                            'change_balance' => $fee,
                            'balance'        => $balance,
                            'remark'         => '交易抵扣',
                        ]);

        return true;
    }

    public function cancelFree($data)
    {
        $uid     = $data['uid'];
        $num     = $data['num'];
        $orderId = $data['order_id'];
        
        $currencyModel = $this->getCurrencyModel();
        $feeCurrency   = $currencyModel->getIdByCode('HAC');
        
        $accountsModel = $this->getAccountsModel()
                              ->setCurrency($feeCurrency);

        $feeOrder = $this->getTradesFeeModel()->getOne(['order_id' => $orderId]);
        if(empty($feeOrder)) {
            return false;
        }

        $surplus = $feeOrder->num - $feeOrder->successful_num;
        
        // 扣除锁定
        $decLkRes = $accountsModel->decrementLocked($uid, $surplus);

        // 增加余额
        $incLkRes = $accountsModel->incrementBalance($uid, $surplus);


        return true;
    }

    public function bindToOrder($id, $orderId)
    {
        return $this->getTradesFeeModel()->bindToOrder($id, $orderId);
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

    private function getAccountsDetailsModel()
    {
        return new AccountsDetailsModel();
    }

    private function getOrdersDetailsModel()
    {
        return new TradesOrdersDetailsModel();
    }

    private function getTradesFeeModel()
    {
        return new TradesFeeModel();
    }

    private function getFeeDetailsModel()
    {
        return new TradesFeeDetailsModel();
    }
}
