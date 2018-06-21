<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\ApiController as Controller;
use Blockchain\Blockchain;
use App\Models\CurrencyModel;
use App\Redis\ExchangeRates;

class BitcoinController extends Controller
{

    public function getRates()
    {
        // $curs = $this->getCurrencyModel()->getCurrenciesCache();
        // $symbols = array_column($curs, 'code');
        $rates  = $this->getBlockchain()->Rates->get();
        $exchangeRates = $this->getExchangeRates();
        $myRates = array();
        foreach ($rates as $key => $cur) {
            $myRates[$key] = object_to_array($cur);
            $curr = strtolower($cur->cur);
            // if(in_array($curr, $symbols))
                $exchangeRates->createRate('market:rate', $cur->last, 'BTC_' . $cur->cur);
        }
        return $this->responseSuccess($myRates);
    }

    public function getRate(Request $request)
    {
        $symbol = $request->input('symbol');
        $symbols = explode('_', $symbol);
        $rate = $this->getExchangeRates()->getRate('market:BTC', $symbols[0]);
        var_dump($rate);exit;
    }

    public function getBlockchain()
    {
        return new Blockchain('affece37-452c-4042-84f2-4d67b8ab0c50');
    }

    public function getCurrencyModel()
    {
        return new CurrencyModel();
    }

    public function getExchangeRates()
    {
        return new ExchangeRates();
    }
}
