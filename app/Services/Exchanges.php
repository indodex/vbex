<?php

namespace App\Services;

use App\Services\BaseService;
use App\Models\UserModel;
use App\Models\ExchangeRatesModel;
// use App\Redis\ExchangeRates;

class Exchanges extends BaseService
{
    // public function coinToBTC($coin)
    // {
    //     $code = strtoupper($coin['code']);
    //     $amount = $coin['balance'] + $coin['locked'];
    //     if($code == 'BTC') {
    //         return $amount;
    //     }

    //     $score = $this->getExchangeRates()->zscore('market:rate', "{$code}_BTC");
    //     // 1:$score
    //     if($score) {
    //         $total = $amount * $score;
    //         return $total;
    //     }

    //     $score = $this->getExchangeRates()->zscore('market:rate', "BTC_{$code}");
    //     // 1:$score
    //     if($score) {
    //         $rate = 1 / $score;
    //         $total = $amount * $rate;
    //         return $total;
    //     }

    //     // 1:1
    //     return $amount;
    // }

    // public function coinToUSD($coin)
    // {
    //     $code = strtoupper($coin['code']);
    //     $amount = $coin['balance'] + $coin['locked'];
    //     if($code == 'USD') {
    //         return $amount;
    //     }

    //     $btc = $this->coinToBTC($coin);
    //     $score = $this->getExchangeRates()->zscore('market:rate', "BTC_USD");
    //     if($score) {
    //         $total = $btc * $score;
    //         return $total;
    //     }

    //     return $amount;
    // }

    // public function coinToCNY($coin)
    // {
    //     $code = strtoupper($coin['code']);
    //     $amount = $coin['balance'] + $coin['locked'];
    //     if($code == 'CNY') {
    //         return $amount;
    //     }

    //     $btc = $this->coinToBTC($coin);
    //     $score = $this->getExchangeRates()->zscore('market:rate', "BTC_CNY");
    //     if($score) {
    //         $total = $btc * $score;
    //         return $total;
    //     }

    //     return $amount;
    // }

    // public function coinToTWD($coin)
    // {
    //     $code = strtoupper($coin['code']);
    //     $amount = $coin['balance'] + $coin['locked'];
    //     if($code == 'CNY') {
    //         return $amount;
    //     }

    //     $btc = $this->coinToBTC($coin);
    //     $score = $this->getExchangeRates()->zscore('market:rate', "BTC_TWD");
    //     if($score) {
    //         $total = $btc * $score;
    //         return $total;
    //     }

    //     return $amount;
    // }


    /**************** 数据库 START ****************/
    
    public function coinToBTCSql($coin)
    {
        $code = strtoupper($coin['code']);
        $amount = $coin['balance'] + $coin['locked'];
        if($code == 'BTC') {
            return $amount;
        }

        if($amount == 0) {
            return $amount;
        }

        $score = $this->getExchangeModel()->getRateByMarket('market:rate', "{$code}_BTC");
        // 1:$score
        if($score) {
            $total = $amount * $score;
            return $total;
        }

        $score = $this->getExchangeModel()->getRateByMarket('market:rate', "BTC_{$code}");
        // 1:$score
        if($score) {
            $rate = 1 / $score;
            $total = $amount * $rate;
            return $total;
        }

        // 1:1
        return $amount;
    }

    public function coinToUSDSql($coin)
    {
        $code = strtoupper($coin['code']);
        $amount = $coin['balance'] + $coin['locked'];
        if($code == 'USD') {
            return $amount;
        }

        if($amount == 0) {
            return $amount;
        }

        $btc = $this->coinToBTCSql($coin);
        $score = $this->getExchangeModel()->getRateByMarket('market:rate', "BTC_USD");
        if($score) {
            $total = $btc * $score;
            return $total;
        }

        return $amount;
    }

    public function coinToCNYSql($coin)
    {
        $code = strtoupper($coin['code']);
        $amount = $coin['balance'] + $coin['locked'];
        if($code == 'CNY') {
            return $amount;
        }

        if($amount == 0) {
            return $amount;
        }

        $btc = $this->coinToBTCSql($coin);
        $score = $this->getExchangeModel()->getRateByMarket('market:rate', "BTC_CNY");
        if($score) {
            $total = $btc * $score;
            return $total;
        }

        return $amount;
    }

    public function coinToTWDSql($coin)
    {
        $code = strtoupper($coin['code']);
        $amount = $coin['balance'] + $coin['locked'];
        if($code == 'TWD') {
            return $amount;
        }

        $btc = $this->coinToBTCSql($coin);
        $score = $this->getExchangeModel()->getRateByMarket('market:rate', "BTC_TWD");
        if($score) {
            $total = $btc * $score;
            return $total;
        }

        return $amount;
    }

    public function coinToHacSql($coin)
    {
        $code = strtoupper($coin['code']);
        $amount = $coin['balance'] + $coin['locked'];
        if($code == 'HAC') {
            return $amount;
        }

        $btc = $this->coinToBTCSql($coin);
        $score = $this->getExchangeModel()->getRateByMarket('market:rate', "BTC_HAC");
        if($score) {
            $total = $btc * $score;
            return $total;
        }

        return $amount;
    }

    /**************** 数据库 END ****************/

    // private function getExchangeRates()
    // {
    //     return new ExchangeRates();
    // }

    private function getExchangeModel()
    {
        return new ExchangeRatesModel();
    }
}
