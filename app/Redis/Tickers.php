<?php

namespace App\Redis;

use App\Redis\BaseRedis;
use App\Models\MarketsModel;

class Tickers extends BaseRedis
{
    public $prefixRds = 'kline:1min:';

    public function getTicker($key)
    {
    	return $this->lrange($this->prefixRds . $key, 0, -1);
    }

    public function getAllPrice()
    {   

        // $markets = $this->getMarketsModel()->select('sell_currency')->groupBy('sell_currency')->get();
        $markets = $this->getMarketsModel()->where('status','=',1)->get();
        $list = [];

        foreach ($markets as $key => $val) {
        	$row['vol'] = 0;
            $row['market']      = $val->currencyBuyTo->code.'/'.$val->currencySellTo->code;
            $row['sell1Price'] = 0;
            $row['lastPrice'] = 0;
            $row['hightPrice'] = 0;
            $row['riseRate'] = 0;
            $row['currrency']  = $val->currencyBuyTo->code;
            $row['lowPrice'] = 0;
            $row['buy1Price'] = 0;
            $row['decimals'] = $val->currencyBuyTo->decimals;
            
            $marketKey = $val->currencyBuyTo->code.'_'.$val->currencySellTo->code;
            $price = $this->getTicker($marketKey);
            if ($price) {
            	$price = json_decode($price[0],true);
                $row['vol'] = $price[5];
	            $row['lastPrice'] = my_number_format($price[4],4);
	            $row['hightPrice'] = $price[2];
	            $row['riseRate']  = my_number_format($price[4]/$price[1] ,2);
	            $row['lowPrice'] = $price[3];
	            $row['sell1Price'] = 0;
	            $row['buy1Price'] = 0;
            }

            $list[] = $row;
        }
        return $list;

    }

    public function getMarketsModel()
    {
        return new MarketsModel();
    }
}
