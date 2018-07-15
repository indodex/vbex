<?php

namespace App\Services;

use App\Services\BaseService;
use App\Models\CurrencyModel;
use App\Models\TradesOrdersModel;
use App\Models\TradesOrdersDetailsModel;


use DB;

class DepthService extends BaseService
{	
	public $key;

	public function __construct() 
    {
    	parent::__construct();
    	$this->key = 'depth';

    }

	public function publish($data)
	{
		// Redis::rpush('queue','1');
		if (empty($data)) {
			return false;
		}

		// $b = Redis::rpush($this->key,json_encode($data));
		// 
		$result['result'] = 'success';
		if ($data['type'] = 'asks') {
			unset($data['type']);
			$result['return']['asks'][] = $data;
			$result['return']['bids'] = [];
		}

		if ($data['type'] = 'bids') {
			unset($data['type']);
			$result['return']['asks']= [];
			$result['return']['bids'][] = $data;
		}

		$b = Redis::publish($this->key,json_encode($data));

		if ($b) {
			return true;
		}

		return false;
	}

    public function getDepth($symbol, $limit = 5)
    {
        // 查看当前交易用户
        list($buyCurrency, $sellCurrency) = explode('_', $symbol);

        $currencyModel = $this->getCurrencyModel();
        $buy        = $currencyModel->getByCode($buyCurrency);
        $sell       = $currencyModel->getByCode($sellCurrency);

        if (empty($buy) || empty($sell)) {
            return $this->error(__('api.public.parameter_error'));
        }

        $status = [3, 2];

        $cond['sell_currency'] = (int) $buy->id;
        $cond['buy_currency']  = (int) $sell->id;
        // $order = array('price', 'asc');
        $asksQuery = $this->getTradesOrdersModel()->select('price',\DB::raw('SUM(num) as num'),\DB::raw('SUM(successful_num) as successful_num'))->where($cond)->whereIn('status',$status)->groupBy('price')->orderBy('price','asc');
        if($limit > 0) {
            $asksQuery->limit($limit);
        }
        $asks = $asksQuery->get();
        // print_r($asks->toArray());exit;
        if(!empty($asks)) {
            $asks = $asks->toArray();
            $list = [];
            foreach($asks as &$value){
                $row    = [];
                $row[]  = my_number_format($value['price'],$sell->decimals);
                $row[]  = my_number_format($value['num']-$value['successful_num'],$sell->decimals);
                $list[] = $row;
            }

        }
        $data['asks'] = $list;

        $cond['sell_currency'] = (int) $sell->id;
        $cond['buy_currency']  = (int) $buy->id;
        // $order = array('price', 'desc');
        $bidsQuery = $this->getTradesOrdersModel()->select('price',\DB::raw('SUM(num) as num'),\DB::raw('SUM(successful_num) as successful_num'))->where($cond)->whereIn('status',$status)->groupBy('price')->orderBy('price','desc');
        if($limit > 0) {
            $bidsQuery->limit($limit);
        }
        $bids = $bidsQuery->get();
//         print_r($bids->toArray());exit;
        if(!empty($bids)) {
            $bids = $bids->toArray();
            $list = [];
            foreach($bids as &$value){
                $row    = [];
                $row[]  = my_number_format($value['price'],$sell->decimals);
                $row[]  = my_number_format($value['num']-$value['successful_num'],$sell->decimals);
                $list[] = $row;
            }

        }
        $data['bids'] = $list;

        if (empty($bids) && empty($asks)) {
            return $this->error(__('api.public.empty_data'));
        }

        return $this->success($data);
    }

    private function getCurrencyModel()
    {
        return new CurrencyModel();
    }

    private function getTradesOrdersModel()
    {
        return new TradesOrdersModel();
    }
}