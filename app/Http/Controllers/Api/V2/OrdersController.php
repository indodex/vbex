<?php

namespace App\Http\Controllers\Api\V2;

use Hash;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

use App\Services\Trades;
use App\Services\Orders;
use App\Services\Entrust;
use App\Services\Currency;
use App\Services\TradesQueue;
use App\Redis\Tickers;
use App\Models\CurrencyModel;
use App\Models\UserModel;
use App\Models\TradesOrdersModel;
use App\Models\TradesOrdersDetailsModel;
use App\Http\Controllers\Api\V2\ApiController as Controller;

class OrdersController extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 查询订单列表
     * GET api/v2/orders
     * @param Request $request
     * @param string symbol 交易对
     * @param string states 订单状态
     * @param integer before 查询某个页码之前的订单
     * @param integer after 查询某个页码之后的订单
     * @param integer limit 每页的订单数量，默认为 20 条
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $uid   = $request->user()->id;
        $symbol = $request->input('symbol', null);
        $status = $request->input('states', null);
        $limit  = $request->input('limit', 20);
        $before  = $request->input('before', null);
        $after  = $request->input('after', null);

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound('Bad Request.');
        }
        if(empty($symbol)) {
            return $this->setStatusCode(400)->responseError('Bad Request.');
        }
        $symbol = strtoupper($symbol);

        $where['symbol'] = $symbol;
        $where['status'] = $status;
        $where['type']   = '';
        $where['page']   = 1;
        $where['limit']  = $limit;
        $where['before'] = $before;
        $where['after']  = $after;

        $result = $this->getOrdersService()->setUid($uid)->orders($where);

        if($result['status'] == 1) {

            return $this->setStatusCode(200)
                ->responseSuccess($result['data'], 'success');
        } else {
            return $this->setStatusCode(404)->responseNotFound('Not Found.');
        }
    }

    /**
     * 查询订单列表
     * GET api/v2/orders
     * @param Request $request
     * @param string symbol 交易对
     * @param string states 订单状态
     * @param integer before 查询某个页码之前的订单
     * @param integer after 查询某个页码之后的订单
     * @param integer limit 每页的订单数量，默认为 20 条
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, TradesOrdersModel $order)
    {
        $uid   = $request->user()->id;
        if(empty($uid)) {
            return $this->setStatusCode(401)->responseNotFound('Unauthorized.');
        }

        $states = array('canceled', 'filled', 'partial_filled', 'submitted');
        $data['symbol']           = $order->currencyBuyTo->code . '_' . $order->currencySellTo->code;
        $data['price']            = my_number_format($order->price, 8);
        $data['amount']           = my_number_format($order->num, 8);
        $data['executed_value']   = my_number_format($order->successful_num, 8);
        $data['successful_price'] = my_number_format($order->successful_price, 8);
        $data['fill_fees']        = my_number_format($order->fee, 8);
        $data['filled_amount']    = bcmul($order->successful_price, $order->successful_num, 8);
        $data['state']            = $states[$order->status];
        $data['created_at']       = (string) $order->created_at;
        $data['source']           = 'web';
        if($order->currencySellTo->is_virtual == 0) {
            $data['source'] = 'buy';
        } else {
            $data['source'] = 'sell';
        }

        return $this->setStatusCode(200)
                    ->responseSuccess($data, 'success');
    }

    /**
     * 创建新的订单
     * POST api/v2/order
     * @param Request $request
     * @param string symbol 交易对
     * @param string side 交易方向
     * @param string type 订单类型
     * @param float price 价格
     * @param integer amount 下单量
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $symbol = $request->input('symbol', null);
        $side = $request->input('side', null);
        $price = $request->input('price', null);
        $amount = $request->input('amount', null);
        $user = $request->user();
        $uid = $user->id;

        if(empty($uid)) {
            return $this->setStatusCode(401)->responseNotFound('Unauthorized.');
        }

        if(empty($symbol)) {
            return $this->setStatusCode(400)->responseNotFound('Bad Request.');
        }
        $symbol = strtoupper($symbol);

        $unprice = (float) $price;
        if(empty($price) || $unprice == 0) {
            return $this->setStatusCode(400)->responseNotFound('Bad Request.');
        }

        if(empty($amount) || $amount == 0) {
            return $this->setStatusCode(400)->responseNotFound('Bad Request.');
        }

        if(empty($side)) {
            return $this->setStatusCode(400)->responseNotFound('Bad Request.');
        }

        if($side == 'buy') {
            $result = $this->getEntrustService()->buyOrder($uid, $symbol, $price, $amount);
        } else {
            $result = $this->getEntrustService()->sellOrder($uid, $symbol, $price, $amount);
        }

        // if((int)$isBuy === 1) {
        //     $result = $this->getTradesQueue()->buyOrder($uid, $market, $unitPrice, $number);
        // } else {
        //     $result = $this->getTradesQueue()->sellOrder($uid, $market, $unitPrice, $number);
        // }

        if($result['status'] == 1) {
            return $this->responseSuccess($result['data'], 'success');
        } else {
            return $this->setStatusCode(404)->responseError($result['error']);
        }
    }

    /**
     * 申请撤销订单
     * POST api/v2/orders/{order_id}/submit-cancel
     * @param Request $request
     * @param string order_id 订单 ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request)
    {
        $id = $request->route('order_id', null);
        $uid = $request->user()->id;

        if(empty($id)) {
            return $this->setStatusCode(400)->responseNotFound('Bad Request.');
        }

        if(empty($uid)) {
            return $this->setStatusCode(401)->responseNotFound('Unauthorized.');
        }

        $result = $this->getEntrustService()->cancelOrder($id);

        if($result['status'] == 1) {
            return $this->setStatusCode(200)->responseSuccess($result['data']);
        } else {
            return $this->setStatusCode(404)->responseError($result['error']);
        }
    }

    /**
     * 查询指定订单的成交记录
     * GET api/v2/orders/{order_id}/match-results
     * @param Request $request
     * @param integer order_id 订单 ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function matchResult(Request $request)
    {
        $uid   = $request->user()->id;
        if(empty($uid)) {
            return $this->setStatusCode(401)->responseNotFound('Unauthorized.');
        }

        $id   = $request->route('order_id', null);
        if(empty($id)) {
            return $this->setStatusCode(400)->responseNotFound('Bad Request.');
        }

        $order = TradesOrdersModel::find($id);
        if(empty($order->id)) {
            return $this->setStatusCode(404)->responseNotFound('Not Found.');
        }

        $detailsModel = new TradesOrdersDetailsModel();

        $cond['orWhere']['buy_id'] = $order->id;
        $cond['orWhere']['sell_id'] = $order->id;
        $data = $detailsModel->getListSort($cond);

        if(!$data->isEmpty()) {
            $list = [];
            foreach ($data as $ord) {
                $info['price'] = my_number_format($ord->price, 8);
                $info['fill_fees'] = my_number_format($ord->fee, 8);
                $info['filled_amount'] = my_number_format($ord->num, 8);
                $info['side'] = $uid == $ord->sell_uid ? 'sell' : 'buy';
                $info['created_at'] = (string) $ord->created_at;
                $list[] = $info;
            }
            return $this->responseSuccess($list);
        } else {
            return $this->setStatusCode(404)->responseNotFound('Not Found.');
        }
    }



    public function setCache($key, $value)
    {
        return Cache::put($key, $value, 60);
    }

    public function getCache($key)
    {
        return Cache::get($key);
    }

    public function getEntrustService()
    {
        return new Entrust();
    }

    public function getOrdersService()
    {
        return new Orders();
    }

    public function getCurrencyService()
    {
        return new Currency();
    }

    public function getTradesQueue()
    {
        return new TradesQueue();
    }

    private function getCurrencyModel()
    {
        return new CurrencyModel();
    }

    public function getUserModel()
    {
        return new UserModel();
    }
}