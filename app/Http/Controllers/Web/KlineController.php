<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TradesCurrenciesModel;

class KlineController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cond['status'] = 1;
        $markets = $this->getTradesCurrenciesModel()->getAll($cond);
        $moneyDecimal = [];
        $coinDecimal = [];
        foreach ($markets as $key => $value) {
            $code = strtolower($value->mainCurrency->code) . strtolower($value->exchangeCurrency->code);
            $moneyDecimal[$code] = $value->money_decimal;
            $coinDecimal[$code] = $value->coin_decimal;
        }

        return view('Kline.kline', [
            "moneyDecimal" => json_encode($moneyDecimal),
            "coinDecimal" => json_encode($coinDecimal),
        ]);
    }

    private function getTradesCurrenciesModel()
    {
        return new TradesCurrenciesModel();
    }
}
