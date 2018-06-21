<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\ApiController as Controller;

use App\Services\Trades;
use App\Services\Orders;
use App\Services\Currency;
use App\Services\TradesQueue;
use App\Redis\Tickers;
use App\Models\CurrencyModel;

class TradesController extends Controller
{

    // 我的委托列表
    public function entrust(Request $request)
    {
        $uid    = $this->uid;
        $token  = $request->input('api_token', null);
        $lang   = $request->input('lang', null);
        $page   = $request->input('page', 1);
        $symbol = $request->input('symbol', null);
        $type   = $request->input('type', null);
        $status = $request->input('status', null);
        $limit  = $request->input('limit', null);

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        if(empty($symbol)) {
            return $this->setStatusCode(400)
                        ->responseError(__('api.account.lack_currency'));
        }
        $symbol = strtoupper($symbol);
        $symbols = explode('_', $symbol);

        if(count($symbols) < 2) {
            return $this->setStatusCode(400)
                        ->responseError(__('api.market.market_non_existent'));
        }

        $where['symbol'] = $symbol;
        $where['type']   = $type;
        $where['status'] = $status;
        $where['page']   = $page;
        $where['limit']  = $limit;
        $result = $this->getTradesService()->setUid($uid)->getAccountTradesOrders($where);

        if($result['status'] == 1) {

            if(empty($result['data']['data'])) {
                return $this->setStatusCode(403)
                        ->responseError(__('api.public.empty_data'));
            }

            $list     = $result['data']['data'];
            unset($result['data']['data']);
            $paginate = $result['data'];
            unset($paginate['path'],
                  $paginate['from'],
                  $paginate['to'],
                  $paginate['first_page_url'],
                  $paginate['last_page_url'],
                  $paginate['next_page_url'],
                  $paginate['prev_page_url']);

            $outputData['list']     = $list;
            $outputData['paginate'] = $paginate;
            return $this->setStatusCode(200)
                        ->responseSuccess($outputData, 'success');
        } else {
            return $this->setStatusCode(403)
                        ->responseError(__('api.public.empty_data'));
        }
    }

    // 委托类型列表
    public function entrustList(Request $request)
    {   
        $uid    = $this->uid;
        $lang   = $request->input('lang', null);
        $page   = $request->input('page', 1);
        $symbol = $request->input('symbol', null);
        $limit  = $request->input('limit', 6);
        $type   = $request->route('type', null);
        $status = $request->input('status', null);
        $token  = $request->input('api_token', null);

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }
        if(empty($symbol)) {
            return $this->setStatusCode(400)
                        ->responseError(__('public.deposits_address.buy_currency_empty'));
        }
        $symbol = strtoupper($symbol);

        $where['symbol'] = $symbol;
        $where['type']   = $type;
        $where['status'] = $status;
        $where['page']   = $page;
        $where['limit']  = $limit;

        $result = $this->getTradesService()->setUid($uid)->getAccountTradesOrders($where);
        // $result = $this->getTradesService()->getTradesOrders($symbol, $type, $page, $limit);
        // $result = $this->getTradesService()->getTradesOrders($symbol, $page, $limit);

        if($result['status'] == 1) {

            if(empty($result['data'])) {
                return $this->setStatusCode(403)
                            ->responseError(__('api.public.empty_data'));
            }

            $list     = $result['data']['data'];
            unset($result['data']['data']);
            $paginate = $result['data'];
            unset($paginate['path'],
                  $paginate['from'],
                  $paginate['to'],
                  $paginate['first_page_url'],
                  $paginate['last_page_url'],
                  $paginate['next_page_url'],
                  $paginate['prev_page_url']);

            $outputData['list']     = $list;
            $outputData['paginate'] = $paginate;
            
            return $this->setStatusCode(200)
                        ->responseSuccess($outputData, 'success');
        } else {
            return $this->setStatusCode(403)
                        ->responseError(__('api.public.empty_data'));
        }
    }

    // K线右侧委托列表
    public function entrustListKline(Request $request)
    {
        $lang   = $request->input('lang', null);
        $page   = $request->input('page', 1);
        $symbol = $request->input('symbol', null);
        $limit  = $request->input('limit', 10);
        // $type   = $request->route('type', 'buy');

        if(empty($symbol)) {
            return $this->setStatusCode(400)
                        ->responseError(__('public.deposits_address.buy_currency_empty'));
        }
        $symbol  = strtolower($symbol);
        $symbol  = $this->getMarket($symbol);
        if (!$symbol) {
            return $this->setStatusCode(400)
                        ->responseError(__('api.public.parameter_error'));
        }
        $symbol = strtoupper($symbol);

        // $result = $this->getTradesService()->getTradesOrders($symbol, $type, $page, $limit);
        $result = $this->getTradesService()->getTradesOrders2($symbol, $page, $limit);

        if($result['status'] == 1) {

            if(empty($result['data'])) {
                return $this->setStatusCode(403)
                            ->responseError(__('api.public.empty_data'));
            }

            $outputData['asks'] = array_reverse($result['data']['asks']);
            $outputData['bids'] = $result['data']['bids'];
            
            return response()->json([
                'result' => 'success',
                'return' => $outputData
            ]);
            return $this->setStatusCode(200)
                        ->responseSuccess($outputData, 'success');
        } else {
            return $this->setStatusCode(403)
                        ->responseError(__('api.public.empty_data'));
        }
    }

    // 交易所成交量
    public function volume(Request $request)
    {
        $lang   = $request->input('lang', null);
        $limit  = $request->input('limit', 30);
        $symbol = $request->input('symbol', null);
        $last_id = $request->input('last_trade_tid', 0);

        if(empty($symbol)) {
            return $this->setStatusCode(400)
                        ->responseError(__('public.deposits_address.buy_currency_empty'));
        }
        $symbol = strtolower($symbol);
        $symbol = $this->getMarket($symbol);
        $symbol = strtoupper($symbol);

        $result = $this->getTradesService()->getTradeAll($symbol, $last_id, $limit);

        if($result['status'] == 1) {

            if(empty($result['data'])) {
                return $this->setStatusCode(403)
                            ->responseError(__('api.public.empty_data'));
            }

            $list = [];
            foreach ($result['data'] as $key => $val) {
                $row['amount'] = $val['num'];
                $row['price'] = $val['price'];
                $row['tid'] = $val['id'];
                $row['date'] = strtotime($val['created_at']);
                $row['type'] = $val['type'];
                $row['trade_type'] = $val['type'] == 'sell' ? 'ask' : 'bid';
                $list[] = $row;
            }

            $outputData['list'] = $list;
            unset($result);

            return response()->json([
                'data' => array_reverse($list)
            ]);
            // return $this->setStatusCode(200)
            //             ->responseSuccess($outputData, 'success');
        } else {
            return $this->setStatusCode(403)
                        ->responseError(__('api.public.empty_data'));
        }
    }

    // 成交量
    public function complete(Request $request)
    {
        $uid    = $this->uid;
        $token  = $request->input('api_token', null);
        $lang   = $request->input('lang', null);
        $symbol = $request->input('symbol', null);
        $page   = $request->input('page', 1);
        $limit  = $request->input('limit', 10);

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        if(empty($symbol)) {
            return $this->setStatusCode(400)
                        ->responseError(__('api.market.market_empty'));
        }
        $symbol = strtoupper($symbol);
        $symbols = explode('_', $symbol);

        if(count($symbols) < 2) {
            return $this->setStatusCode(400)
                        ->responseError(__('api.market.market_non_existent'));
        }

        $where['uid']    = $uid;
        $where['symbol'] = $symbol;
        $result = $this->getTradesService()->getOrdersDetails($where, $page, $limit);

        if($result['status'] == 1) {

            if(empty($result['data']['data'])) {
                return $this->setStatusCode(404)
                        ->responseError(__('api.public.empty_data'));
            }

            $list     = $result['data']['data'];
            unset($result['data']['data']);
            $paginate = $result['data'];
            unset($paginate['path'],
                  $paginate['from'],
                  $paginate['to'],
                  $paginate['first_page_url'],
                  $paginate['last_page_url'],
                  $paginate['next_page_url'],
                  $paginate['prev_page_url']);

            $outputData['list']     = $list;
            $outputData['paginate'] = $paginate;
            return $this->setStatusCode(200)
                        ->responseSuccess($outputData, 'success');
        } else {
            return $this->setStatusCode(404)
                        ->responseError(__('api.public.empty_data'));
        }
    }

    // 获取最新价格
    public function lastPrice(Request $request)
    {
        $lang   = $request->input('lang', null);
        $market = $request->input('market', null);

        if(empty($market)) {
            return $this->setStatusCode(400)
                        ->responseError(__('public.deposits_address.buy_currency_empty'));
        }
        $market = strtoupper($market);

        $result = $this->getTradesService()->getLastSalePrice($market);
        if($result['status'] == 1) {
            if(empty($result['data'])) {
                return $this->setStatusCode(403)
                            ->responseError(__('api.public.empty_data'));
            }
            $outputData = $result['data'];
            unset($result);
            return $this->setStatusCode(200)
                        ->responseSuccess($outputData, 'success');
        } else {
            return $this->setStatusCode(403)
                        ->responseError(__('api.public.empty_data'));
        }
    }

    public function entrustTypes()
    {
        $reutrnData['list'] = [
                'sell' => __('api.trade.sell'),
                'buy'  => __('api.trade.buy'),
                'all'  => __('api.trade.all'),
            ];
        return $this->responseSuccess($reutrnData, 'success');
    }

    public function entrustStatus()
    {
        $reutrnData['list'] = [
                'canceled'  => __('api.trade.canceled'),
                'completed' => __('api.trade.completed'),
                'trading'   => __('api.trade.completed'),
                'all'       => __('api.trade.all'),
            ];
        return $this->responseSuccess($reutrnData, 'success');
    }

    public function getLengthDepth(Request $request)
    {
        $length = (int)$request->input('length', 5);
        $a = (int)$request->input('a', 4);
        $symbol = (string)$request->input('symbol', null);

        if(empty($symbol)) {
            return $this->setStatusCode(400)
                        ->responseError(__('api.market.market_empty'));
        }
        $symbol  = strtoupper($symbol);
        $symbols = explode('_', $symbol);

        if(count($symbols) < 2) {
            return $this->setStatusCode(400)
                        ->responseError(__('api.market.market_non_existent'));
        }

        $data = $this->getTradesService()->getLengthDepth($symbol, $a, $length); 

        if ($data) {
            return $this->setStatusCode(200)
                        ->responseSuccess($data);
        }

        return $this->setStatusCode(404)
                        ->responseError(__('api.public.empty_data'));
    }

    public function getAllPrice(Request $request)
    {
        $tickers = new Tickers();
        $data = $tickers->getAllPrice();
        if ($data) {
            return $this->setStatusCode(200)
                        ->responseSuccess($data);
        }

        return $this->setStatusCode(404)
                        ->responseError(__('api.public.empty_data'));
    }

    public function getTradesDetails(Request $request)
    {
        $id = (int) $request->input('id');
        if(empty($id)) {
            return $this->setStatusCode(404)
                        ->responseError(__('api.public.illegal_operation'));
        }

        $order = $this->getOrdersService()->getOrderInfo($id);
        if(empty($order)) {
            return $this->setStatusCode(404)
                        ->responseError(__('api.trade.order_empty'));
        }

        $data = $this->getOrdersService()->getOrdersDetailsById($id, $order->buy_currency);
        if(empty($data)) {
            return $this->setStatusCode(404)
                        ->responseError(__('api.public.empty_data'));
        }

        $market = $request->input('market');
        if(empty($data)) {
            return $this->setStatusCode(404)
                        ->responseError(__('api.market.market_empty'));
        }
        $data = $data->toArray();
        $list = array();
        foreach ($data as $key => $value) {
            $info['created_at'] = $value['created_at'];
            $info['price']     = my_number_format($value['price'], 4);
            $info['num']       = my_number_format($value['num'], 4);
            $info['count']     = bcmul($value['price'], $value['num'], 4);
            $list[] = $info;
        }


        $market = strtoupper($market);
        $markets = explode('_', $market);
        $returnData['currency']['main']     = $markets[0];
        $returnData['currency']['exchange'] = $markets[1];
        $returnData['list'] = $list;

        return $this->setStatusCode(200)
                    ->responseSuccess($returnData);
    }

    public function getTradesService()
    {
        return new Trades();
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
}
