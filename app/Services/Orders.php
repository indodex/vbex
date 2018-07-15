<?php

namespace App\Services;

use App\Services\BaseService;
use App\Services\Currency;

use App\Models\TradesOrdersModel;
use App\Models\TradesCurrenciesModel;
use App\Models\TradesOrdersDetailsModel;
use App\Models\CurrencyModel;
use App\Models\UserModel;
use DB;

class Orders extends BaseService
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

    public function getOrdersDetailsById($id, $buyCurrency)
    {
        $data = $this->getTradesDetailsModel()->where(['buy_id' => $id, 'buy_currency' => $buyCurrency])->get();
        return $data;
    }

    public function getOrderInfo($id)
    {
        return $this->getTradesOrdersModel()->where(['id' => $id])->first();
    }

    public function orders($where)
    {
        // 查看当前交易用户
        if(!empty($this->getUid())) {
            $cond['uid'] = $this->getUid();
        }

        $status = $where['status'];
        $type   = $where['type'];
        $limit  = $where['limit'];
        $symbol = $where['symbol'];

        list($buyCurrency, $sellCurrency) = explode('_', $symbol);

        $currencyModel = $this->getCurrencyModel();
        $buyCid        = $currencyModel->getIdByCode($buyCurrency);
        $sellCid       = $currencyModel->getIdByCode($sellCurrency);

        if($where['type'] == 'sell') {
            $cond[] = ['sell_currency', '=', (int) $buyCid];
            $cond[] = ['buy_currency', '=', (int) $sellCid];
        } else if($type == 'buy') {
            $cond[] = ['sell_currency', '=', (int) $sellCid];
            $cond[] = ['buy_currency', '=', (int) $buyCid];
        }else{
            $cond['whereIn']['sell_currency'] = [(int) $sellCid,(int) $buyCid];
            $cond['whereIn']['buy_currency']  = [(int) $sellCid,(int) $buyCid];
        }

        if(!empty($status)) {
            switch ($status) {
                case 'submitted':
                    $cond[] = ['status', '=', 3];
                    break;

                case 'partial_filled':
                    $cond[] = ['status', '=', 2];
                    break;

                case 'filled':
                    $cond[] = ['status', '=', 1];
                    break;

                case 'canceled':
                    $cond[] = ['status', '=', 0];
                    break;

//                case 'past':
//                    $cond['whereIn']['status'] = [1, 0];
//                    break;

            }
        }

        $states = array('canceled', 'filled', 'partial_filled', 'submitted');
        if(isset($where['before']) && $where['before'] > 0) {
            $cond[] = ['id', '<', $where['before']];
        }
        if(isset($where['after']) && $where['after'] > 0) {
            $cond[] = ['id', '>', $where['after']];
        }


        $order = array('id', 'DESC');
        $data = $this->getTradesOrdersModel()->getListSort($cond, $order, $limit);
        if(!empty($data)) {
//            $data = $data->toArray();
            $list = [];
            foreach($data as $value){
                $info['symbol']           = $symbol;
                $info['id']               = $value->id;
                $info['price']            = my_number_format($value->price, 8);
                $info['amount']           = my_number_format($value->num, 8);
                $info['executed_value']   = my_number_format($value->successful_num, 8);
                $info['successful_price'] = my_number_format($value->successful_price, 8);
                $info['fill_fees']        = my_number_format($value->fee, 8);
                $info['filled_amount']    = bcmul($value->successful_price, $value->successful_num, 8);
                $info['state']            = $states[$value->status];
                $info['created_at']       = (string) $value->created_at;
                $info['source']           = 'web';

                // 买卖判断
                if($sellCid == $value->buy_currency) {
                    $info['side']  = 'sell';
                } else if($sellCid == $value->sell_currency) {
                    $info['side']  = 'buy';
                }
                $list[] = $info;
            }
            return $this->success($list);
        } else {
            return $this->error(__('api.public.empty_data'));
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

    private function getUserModel()
    {
        return new UserModel();
    }
}
