<?php

namespace App\Services;

use Illuminate\Support\Facades\Cookie;
use App\Services\BaseService;
use App\Models\UserModel;
use App\Models\AccountsModel;
use App\Models\CurrencyModel;
use App\Models\CurrencyFeeModel;
use App\Models\TradesOrdersDetailsModel;
use App\Models\TradesCurrenciesModel;

class Currency extends BaseService
{

    public function getVirtualCurrencies()
    {
        $currencies = $this->getCurrencyModel()->getCurrenciesCache(1);
        $coins = array();
        foreach ($currencies as $key => $value) {
            $coins[$value['id']] = $value;
        }
        return $this->success($coins);
    }

    public function getRealCurrencies()
    {
        $currencies = $this->getCurrencyModel()->getCurrenciesCache(0);
        if(!empty($currencies)) {
            return $this->success($currencies);
        } else {
            return $this->error(__('api.public.empty_data'));
        }
        
    }

    public function getCurrency($id)
    {
        $currency = $this->getCurrencyModel()->getInfo($id);
        if(!empty($currency)) {
            return $this->success($currency);
        } else {
            return $this->error(__('api.public.empty_data'));
        }
    }

    public function getMarkets()
    {
        $markets = $this->getTradesCurrenciesModel()->getAll(['status' => 1]);
        if(empty($markets)) {
            return $this->error(__('api.public.empty_data'));
        } else {
            $list = [];
            foreach ($markets as $key => $value) {
                $info             = [];
                $mainCurrency     = $value->mainCurrency()->find($value->main_currency);
                $exchangeCurrency = $value->exchangeCurrency()->find($value->exchange_currency);
                if(!empty($mainCurrency) && !empty($exchangeCurrency)) {
                    $info['market']       = implode('_', [$mainCurrency->code, $exchangeCurrency->code]);
                    $info['buy']['coin']  = $mainCurrency->code;
                    $info['buy']['name']  = $mainCurrency->name;
                    $info['sell']['coin'] = $exchangeCurrency->code;
                    $info['sell']['name'] = $exchangeCurrency->name;
                    $list[]               = $info;
                }
            }
            // $markets = $markets->toArray();
            return $this->success($list);
        }
    }

    public function setCoinUnit($coin) 
    {
        return Cookie::make('sales_unit', $coin, config('cache.cache_minutes'));
    }

    public function getCoinUnit()
    {
        return Cookie::get('sales_unit');
    }

    public function getFee($coinCode) 
    {
        $currency = $this->getCurrencyModel()->getIdByCode($coinCode);
        if(empty($currency)) {
            return $this->error(__('api.account.lack_currency'));
        }

        $cond['currency'] = $currency;
        $cond['status'] = 1;
        $fee = $this->getCurrencyFeeModel()->getList($cond, 1, 99);

        if(!empty($fee)) {
            return $this->success($fee->toArray());
        } else {
            return $this->error(__('api.public.empty_data'));
        }
    }

    public function balance($uid, $money, $symbol)
    {
        if(empty($uid)) {
            return $this->error(false);
        }

        $moneyInfo = $this->getCurrencyModel()->getByCode($money);
        if(empty($moneyInfo)) {
            return $this->error(__('api.account.lack_currency'));
        }

        $symbolInfo = $this->getCurrencyModel()->getByCode($symbol);
        if(empty($symbolInfo)) {
            return $this->error(__('api.account.lack_currency'));
        }

        $moneyId = $moneyInfo->id;
        $symbolId = $symbolInfo->id;

        // 当前用户账号
        $accountsModel = $this->getAccountsModel();
        $orderDetailsModel = $this->getOrderDetailsModel();

        // 买币账号
        $accountsModel->setCurrency($moneyId);
        $moneyAccount = $accountsModel->getAccount($uid);

        $buyWhere['buy_currency'] = $moneyId;
        $buyWhere['sell_currency'] = $symbolId;
        $buyOrder = $orderDetailsModel->getOne($buyWhere, ['id', 'desc']);

        $canBuyMoney = 0;
        if(!empty($buyOrder)) {
            $canBuyMoney = $moneyAccount->balance * $buyOrder->price;
            $canBuyMoney = my_number_format($canBuyMoney, 4);
        }

        // 计价单位账号
        $accountsModel->setCurrency($symbolId);
        $symbolAccount = $accountsModel->getAccount($uid);

        // 最新成交量
        $sellWhere['buy_currency']  = $symbolId;
        $sellWhere['sell_currency'] = $moneyId;
        $sellOrder = $orderDetailsModel->getOne($sellWhere, ['id', 'desc']);

        $canSellSymbol = 0;
        if(!empty($sellOrder)) {
            $canSellSymbol = $symbolAccount->balance / $sellOrder->price;
            $canSellSymbol = my_number_format($canSellSymbol, 4);
        }

        return $this->success([
            'money' => array('balance' => my_number_format($moneyAccount->balance, 4), 'balanceUnit' => $money, 'buy' => $canBuyMoney, 'buyCoin' => $symbol),
            'symbol' => array('balance' => my_number_format($symbolAccount->balance, 4), 'balanceUnit' => $symbol, 'sell' => $canSellSymbol, 'sellCoin' => $money),
        ]);


    }

    
    public function accountBalance($uid, $mainCode, $exchangeCode)
    {
        if(empty($uid)) {
            return $this->error(false);
        }

        $mainCurrency = $this->getCurrencyModel()->getByCode($mainCode);
        if(empty($mainCurrency)) {
            return $this->error(__('api.account.lack_currency'));
        }

        $exchangeCurrency = $this->getCurrencyModel()->getByCode($exchangeCode);
        if(empty($exchangeCurrency)) {
            return $this->error(__('api.account.lack_currency'));
        }

        $mainId      = $mainCurrency->id;
        $exchangelId = $exchangeCurrency->id;

        // 当前用户账号
        $accountsModel = $this->getAccountsModel();

        // 主交易货币账号
        $accountsModel->setCurrency($mainId);
        $mainAccount = $accountsModel->getAccount($uid);

        // 对交易货币账号
        $accountsModel->setCurrency($exchangelId);
        $exchangeAccount = $accountsModel->getAccount($uid);

        // 交易对
        $market = $this->getTradesCurrenciesModel()->getMarket($mainId, $exchangelId);
        if(!empty($market)) {
            $exchangeCurrency->decimals = $market['money_decimal'];
            $mainCurrency->decimals = $market['coin_decimal'];
        }

        $blance['main_balance']['balance']       = my_number_format($mainAccount->balance, 4);
        $blance['main_balance']['code']          = $mainCode;
        $blance['main_balance']['decimals']      = $mainCurrency->decimals;
        $blance['main_balance']['minTradingVal'] = $mainCurrency->min_trading_val;
        
        $blance['exchange_balance']['balance']       = my_number_format($exchangeAccount->balance, 4);
        $blance['exchange_balance']['code']          = $exchangeCode;
        $blance['exchange_balance']['decimals']      = $exchangeCurrency->decimals;
        $blance['exchange_balance']['minTradingVal'] = $exchangeCurrency->min_trading_val;
        

        // 最新交易信息
        $where['whereIn']['buy_currency']  = array($mainId, $exchangelId);
        $where['whereIn']['sell_currency'] = array($mainId, $exchangelId);
        $trade = $this->getOrderDetailsModel()->getOne($where, ['id', 'desc']);

        if(!empty($trade)) {
            $blance['exchange_rate']       = $trade->price;
        } else {
            $blance['exchange_rate']       = 1;
        }
        
        return $this->success($blance);
    }


    private function getAccountsModel()
    {
        return new AccountsModel();
    }

    private function getCurrencyModel()
    {
        return new CurrencyModel();
    }

    private function getUserModel()
    {
        return new UserModel();
    }

    private function getTradesCurrenciesModel()
    {
        return new TradesCurrenciesModel();
    }

    private function getCurrencyFeeModel()
    {
        return new CurrencyFeeModel();
    }

    private function getOrderDetailsModel()
    {
        return new TradesOrdersDetailsModel();
    }
}
