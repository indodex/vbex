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
use App\Repositories\BlockchainRepository;

use DB;

class Trades extends BaseService
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
     * 获取交易完成记录
     * @param  [type]  $where [description]
     * @param  integer $page  [description]
     * @return [type]         [description]
     */
    public function getOrdersDetails($where, $page = 1, $limit = 10) 
    {
        $uid    = $where['uid'];
        $symbol = $where['symbol'];
        list($buyCode, $sellCode) = explode('_', $symbol);
        
        $currencyModel = $this->getCurrencyModel();
        $buyCurrency   = $currencyModel->getIdByCode($buyCode);
        $sellCurrency  = $currencyModel->getIdByCode($sellCode);

        
        $cond['orWhere']['buy_uid']  = $uid;
        $cond['orWhere']['sell_uid'] = $uid;
        if(!empty($buyCurrency)) {
            $cond['whereIn']['buy_currency'][]  = (int) $buyCurrency;
            $cond['whereIn']['sell_currency'][] = (int) $buyCurrency;
        }
        if(!empty($sellCurrency)) {
            $cond['whereIn']['buy_currency'][]  = (int) $sellCurrency;
            $cond['whereIn']['sell_currency'][] = (int) $sellCurrency;
        }

        $data = $this->getTradesDetailsModel()->getList($cond, $page, $limit);
        if(!empty($data)) {
            $data = $data->toArray();
            foreach($data['data'] as &$value){
                $value['price']    = my_number_format($value['price']);
                $value['num']      = my_number_format($value['num']);
                $value['sell_num'] = my_number_format($value['sell_num']);
                $value['buy_num']  = my_number_format($value['buy_num']);
                $value['fee']      = my_number_format($value['fee']);
            }
            return $this->success($data);
        } else {
            return $this->error(__('api.public.empty_data'));
        }
    }

    public function getTradesOrders($symbol, $type = 'buy', $page = 1, $limit = 5)
    {
        // 查看当前交易用户
        if(!empty($this->getUid())) {
            $cond['uid'] = $this->getUid();
        }
        list($buyCurrency, $sellCurrency) = explode('_', $symbol);

        $currencyModel = $this->getCurrencyModel();

        $buyCid        = $currencyModel->getIdByCode($buyCurrency);
        $sellCid       = $currencyModel->getIdByCode($sellCurrency);

        $cond['whereIn']['status'] = [3, 2];
        if($type == 'sell') {
            $cond['sell_currency'] = (int) $buyCid;
            $cond['buy_currency']  = (int) $sellCid;
            $order = array('price', 'asc');
        } else {
            $cond['sell_currency'] = (int) $sellCid;
            $cond['buy_currency']  = (int) $buyCid;
            $order = array('price', 'desc');
        }

        $data = $this->getTradesOrdersModel()->getListSortPage($cond, $order, $page, $limit);
        if(!empty($data)) {
            $data = $data->toArray();
            foreach($data['data'] as &$value){
                $value['price']            = my_number_format($value['price']);
                $value['average_price']    = my_number_format($value['average_price']);
                $value['num']              = my_number_format($value['num']);
                $value['successful_num']   = my_number_format($value['successful_num']);
                $value['successful_price'] = my_number_format($value['successful_price']);
                $value['fee']              = my_number_format($value['fee']);
                $value['statusStr']        = $this->getTradesOrdersModel()->toStatusName($value['status']);
            }
            return $this->success($data);
        } else {
                return $this->error(__('api.public.empty_data'));
        }
    }

    public function getTradesOrders2($symbol, $page = 1, $limit = 5)
    {
        // 查看当前交易用户
        if(!empty($this->getUid())) {
            $cond['uid'] = $this->getUid();
        }

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
        $asks = $this->getTradesOrdersModel()->select('price',\DB::raw('SUM(num) as num'),\DB::raw('SUM(successful_num) as successful_num'))->where($cond)->whereIn('status',$status)->groupBy('price')->orderBy('price','asc')->limit($limit)->get();
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
        $bids = $this->getTradesOrdersModel()->select('price',\DB::raw('SUM(num) as num'),\DB::raw('SUM(successful_num) as successful_num'))->where($cond)->whereIn('status',$status)->groupBy('price')->orderBy('price','desc')->limit($limit)->get();;
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

    public function getAccountTradesOrders($where)
    {
        // 查看当前交易用户
        if(!empty($this->getUid())) {
            $cond['uid'] = $this->getUid();
        }

        $status = $where['status'];
        $type   = $where['type'];
        $page   = $where['page'];
        $limit  = $where['limit'];
        $symbol = $where['symbol'];

        list($buyCurrency, $sellCurrency) = explode('_', $symbol);

        $currencyModel = $this->getCurrencyModel();
        $buyCid        = $currencyModel->getIdByCode($buyCurrency);
        $sellCid       = $currencyModel->getIdByCode($sellCurrency);

        if($where['type'] == 'sell') {
            $cond['sell_currency'] = (int) $buyCid;
            $cond['buy_currency']  = (int) $sellCid;
        } else if($type == 'buy') {
            $cond['sell_currency'] = (int) $sellCid;
            $cond['buy_currency']  = (int) $buyCid;
        }else{
            $cond['whereIn']['sell_currency'] = [(int) $sellCid,(int) $buyCid];
            $cond['whereIn']['buy_currency']  = [(int) $sellCid,(int) $buyCid];
        }

        if(isset($where['status'])) {
            switch ($where['status']) {
                case 'trading':
                    $cond['whereIn']['status'] = [3, 2];
                    break;

                case 'completed':
                    $cond['status'] = 1;
                    break;

                case 'canceled':
                    $cond['status'] = 0;
                    break;

                case 'past':
                    $cond['whereIn']['status'] = [1, 0];
                    break;

            }
        }

        $order = array('id', 'DESC');
        $data = $this->getTradesOrdersModel()->getListSortPage($cond, $order, $page, $limit);
        if(!empty($data)) {
            $data = $data->toArray();
            foreach($data['data'] as &$value){
                $value['price']            = my_number_format($value['price'], 8);
                $value['average_price']    = my_number_format($value['average_price'], 8);
                $value['num']              = my_number_format($value['num'], 8);
                $value['successful_num']   = my_number_format($value['successful_num'], 8);
                $value['successful_price'] = my_number_format($value['successful_price'], 8);
                $value['fee']              = my_number_format($value['fee'], 8);
                $value['statusStr']        = $this->getTradesOrdersModel()->toStatusName($value['status']);
                $value['status']           = $value['status'];
                $value['volume']           = bcmul($value['successful_price'], $value['successful_num'], 8);
                // 买卖判断
                if($sellCid == $value['buy_currency']) {
                    $value['type']  = 'sell';
                } else if($sellCid == $value['sell_currency']) {
                    $value['type']  = 'buy';
                }
            }
            return $this->success($data);
        } else {
            return $this->error(__('api.public.empty_data'));
        }
    }

    /**
     * 获取成功交易
     * @param  [type]  $where [description]
     * @param  integer $page  [description]
     * @return [type]         [description]
     */
    public function getTradeAll($symbol, $last_id, $limit = 1) 
    {
        list($buyCode, $sellCode) = explode('_', $symbol);
        
        $currencyModel = $this->getCurrencyModel();
        $buyCurrency   = $currencyModel->getByCode($buyCode);
        $sellCurrency  = $currencyModel->getByCode($sellCode);

        if(!empty($buyCurrency)) {
            $cond['whereIn']['buy_currency'][]  = (int) $buyCurrency->id;
            $cond['whereIn']['sell_currency'][] = (int) $buyCurrency->id;
        }
        if(!empty($sellCurrency)) {
            $cond['whereIn']['buy_currency'][]  = (int) $sellCurrency->id;
            $cond['whereIn']['sell_currency'][] = (int) $sellCurrency->id;
        }

        $data = $this->getTradesDetailsModel()->getListSort($cond, $last_id, array('id', 'desc'), $limit);
        if(!empty($data)) {
            $data = $data->toArray();
            foreach($data as &$value){
                // 买卖判断
                if($sellCurrency->id == $value['buy_currency']) {
                    $value['type']  = 'sell';
                    $decimals = $sellCurrency->decimals;
                } else if($sellCurrency->id == $value['sell_currency']) {
                    $value['type']  = 'buy';
                    $decimals = $buyCurrency->decimals;
                }

                $value['price']    = my_number_format($value['price'], $decimals);
                $value['num']      = my_number_format($value['num'], $decimals);
                $value['sell_num'] = my_number_format($value['sell_num'], $sellCurrency->decimals);
                $value['buy_num']  = my_number_format($value['buy_num'], $buyCurrency->decimals);
                $value['fee']      = my_number_format($value['fee'], $decimals);

            }
            return $this->success($data);
        } else {
            return $this->error(__('api.public.empty_data'));
        }
    }

    /**
     * 获取最新的交易价格
     * @param  [type] $symbol [description]
     * @return [type]         [description]
     */
    public function getLastSalePrice($symbol)
    {
        list($buyCode, $sellCode) = explode('_', $symbol);
        
        $currencyModel = $this->getCurrencyModel();
        $buyCurrency   = $currencyModel->getIdByCode($buyCode);
        $sellCurrency  = $currencyModel->getIdByCode($sellCode);

        $cond['whereIn']['buy_currency'] = array($buyCurrency, $sellCurrency);
        $cond['whereIn']['sell_currency'] = array($buyCurrency, $sellCurrency);
        $data = $this->getTradesDetailsModel()->getOne($cond, array('id', 'desc'));
        if(!empty($data)) {
            $data = $data->toArray();

            $data['price']    = my_number_format($data['price'], 4);
            $data['num']      = my_number_format($data['num'], 4);
            $data['sell_num'] = my_number_format($data['sell_num'], 4);
            $data['buy_num']  = my_number_format($data['buy_num'], 4);
            $data['fee']      = my_number_format($data['fee'], 4);

            if($sellCurrency == $data['sell_currency']) {
                $data['type']  = 'buy';
            }else{
                $data['type']  = 'sell';
            }
            return $this->success($data);
        } else {
            return $this->error(__('api.public.empty_data'));
        }
    }

    // 获取深度
    public function getLengthDepth($symbol, $a = 2, $length = 5)
    {  
        list($buyCode, $sellCode) = explode('_', $symbol);
        
        $currencyModel = $this->getCurrencyModel();
        $buy_currency   = $currencyModel->getByCode($buyCode);
        $sell_currency  = $currencyModel->getByCode($sellCode);
        
        $sell = DB::select("SELECT format(price, ?) as pr,price, SUM(num-successful_num) as n FROM trades_orders WHERE buy_currency = $sell_currency->id AND sell_currency = $buy_currency->id AND status IN(2,3) GROUP BY pr ORDER BY price DESC LIMIT ?",[$a,$length]);
        $buy = DB::select("SELECT format(price, ?) as pr,price, SUM(num-successful_num) as n FROM trades_orders WHERE buy_currency = $buy_currency->id AND sell_currency = $sell_currency->id AND status IN(2,3) GROUP BY pr ORDER BY price ASC LIMIT ?",[$a,$length]);
        // print_r($sell);exit;
        $data['sell'] = [];
        if ($sell) {
            $shellNum = array_column($sell,'n');
            $maxShellNum = max($shellNum);

            $data['sell'] = [];
            foreach ($sell as $val) {
                // $row = (array)$val;
                $row['depth'] = sprintf("%.2f",$val->n/$maxShellNum*100);
                $row['price'] = ($val->pr)>0 ? $val->pr : my_number_format($val->price,$sell_currency->decimals);
                $row['num'] = my_number_format($val->n);
                $data['sell'][] = $row;
            }
        }
        
        $data['buy'] = [];
        if ($buy) {
            $buyNum = array_column($buy,'n');
            $maxbuyNum = max($buyNum);

            $data['buy'] = [];
            foreach ($buy as &$val) {
                // $row = (array)$val;
                $row['depth'] = sprintf("%.2f",$val->n/$maxbuyNum*100);
                $row['price'] = ($val->pr)>0 ? $val->pr : my_number_format($val->price,$sell_currency->decimals);
                $row['num'] = my_number_format($val->n);
                $data['buy'][] = $row;
            }
        }
        

        return $data;
    }

    public function getDefaultCurrency()
    {
        $currency = config('currency');
        $currencyInfo = $this->getCurrencyModel()
                             ->getByCode($currency);
        if(!empty($currencyInfo)) {
            return $currencyInfo->id;
        } else {
            return 0;
        }
        
    }

    private function isMarket($buyCurrency, $sellCurrency)
    {
        return $this->getTradesCurrenciesModel()->getMarket($buyCurrency, $sellCurrency);
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

    private function getBlockchainRep()
    {
        return new BlockchainRepository();
    }

    private function getAccountsModel()
    {
        return new AccountsModel();
    }

    private function getAccDetailsModel()
    {
        return new AccountsDetailsModel();
    }

    private function getTradesCurrenciesModel()
    {
        return new TradesCurrenciesModel();
    }
}
