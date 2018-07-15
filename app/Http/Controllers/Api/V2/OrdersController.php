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
        $status = $request->route('state', null);
        $limit  = $request->input('limit', 20);

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

        $result = $this->getOrdersService()->setUid($uid)->orders($where);

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
        $lang      = $request->input('lang', null);
        $market    = $request->input('market', null);
        $isBuy     = $request->input('isBuy', null);
        $unitPrice = $request->input('unitPrice', null);
        $number    = $request->input('number', null);
        $tradeCode = $request->input('tradeCode', null);
        $uid       = $this->uid;
        $user      = $this->getUserModel()->getInfo($uid);

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $lockTime = $this->getCache('tradeLockTime@uid:' . $user->id);
        $time = 60*60;

        // 间隔1小时输入密码
        if ($user->trade_option == 1 && (time() - $lockTime) > $time && empty($tradeCode))
        {
            return $this->setStatusCode(403)->responseNotFound(__('api.member.tradeCode_empty'));
        }

        // 方案为“间隔1小时输入密码”时，密码错误返回
        if($user->trade_option == 1 && (time() - $lockTime) > $time && !Hash::check($tradeCode, $user->trade_code))
        {
            return $this->setStatusCode(400)
                ->responseError(__('api.member.tradeCode_error'));
        }

        // 每次输入密码
        if ($user->trade_option == 2 && empty($tradeCode))
        {
            return $this->setStatusCode(403)->responseNotFound(__('api.member.tradeCode_empty'));
        }

        // 方案为“必输密码”时，密码错误返回
        if($user->trade_option == 2 && !Hash::check($tradeCode, $user->trade_code))
        {
            return $this->setStatusCode(400)
                ->responseError(__('api.member.tradeCode_error'));
        }

        if(empty($market)) {
            return $this->setStatusCode(400)
                ->responseError(__('api.account.buy_currency_empty'));
        }
        $market = strtoupper($market);

        $price = (float) $unitPrice;
        if(empty($unitPrice) || $price == 0) {
            return $this->setStatusCode(400)
                ->responseError(__('api.trade.price_empty'));
        }

        if(empty($number) || $number == 0) {
            return $this->setStatusCode(400)
                ->responseError(__('api.trade.price_empty'));
        }

        if((int)$isBuy === 1) {
            $result = $this->getEntrustService()->buyOrder($uid, $market, $unitPrice, $number);
        } else {
            $result = $this->getEntrustService()->sellOrder($uid, $market, $unitPrice, $number);
        }

        // if((int)$isBuy === 1) {
        //     $result = $this->getTradesQueue()->buyOrder($uid, $market, $unitPrice, $number);
        // } else {
        //     $result = $this->getTradesQueue()->sellOrder($uid, $market, $unitPrice, $number);
        // }

        if($result['status'] == 1) {
            // 方案为“间隔1小时输入密码”时，设置时间
            if($user->trade_option == 1 && (time() - $lockTime) > $time)
            {
                $this->setCache('tradeLockTime@uid:' . $user->id, time(), 60);
            }
            return $this->setStatusCode(200)
                ->responseSuccess($result['data'], __('api.trade.successed'));
        } else {
            return $this->setStatusCode(404)
                ->responseError($result['error']);
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
        $lang = $request->input('lang', null);
        $id   = $request->input('id', null);
        $uid  = $this->uid;

        if(empty($id)) {
            return $this->setStatusCode(403)
                ->responseError(__('api.trade.trades_lack_id'));
        }

        $result = $this->getEntrustService()->cancelOrder($id);

        if($result['status'] == 1) {
            return $this->setStatusCode(200)
                ->responseSuccess($result['data']);
        } else {
            return $this->setStatusCode(404)
                ->responseError($result['error']);
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
        $lang = $request->input('lang', null);
        $id   = $request->input('id', null);
        $uid  = $this->uid;

        if(empty($id)) {
            return $this->setStatusCode(403)
                ->responseError(__('api.trade.trades_lack_id'));
        }

        $result = $this->getEntrustService()->cancelOrder($id);

        if($result['status'] == 1) {
            return $this->setStatusCode(200)
                ->responseSuccess($result['data']);
        } else {
            return $this->setStatusCode(404)
                ->responseError($result['error']);
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