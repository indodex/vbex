<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\ApiController as Controller;

use App\Services\Trades;
use App\Services\Orders;
use App\Services\Entrust;
use App\Services\Currency;
use App\Services\TradesQueue;
use App\Redis\Tickers;
use App\Models\CurrencyModel;
use App\Models\UserModel;
use Hash;
use Illuminate\Support\Facades\Cache;

class EntrustController extends Controller
{

    // 委托交易
    public function doEntrust(Request $request)
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

    // 取消订单
    public function cancelEntrust(Request $request)
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

    // 提交交易密码
    public function checkTrade(Request $request)
    {
        $tradeCode = $request->input('tradeCode', null);
        $uid = $this->uid;

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $user = $this->getUserModel()->getInfo($uid);
        $checkResult = Hash::check($tradeCode, $user->trade_code);
        if (!$checkResult) {
            return $this->setStatusCode(400)->responseNotFound(__('api.member.tradeCode_error'));
        }
        
        $data['cookie'] = \Cookie('is_trade@uid:' . $uid, 1, 60);
        return $this->setStatusCode(200)->responseCookie($data);
    }

    public function isTrade(Request $request)
    {
        $uid = $this->uid;
        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $isTrade = \Cookie::get('is_trade@uid:' . $uid);
        if ($isTrade) {
            return $this->responseSuccess([], __('api.public.verify_successed'));
        } else {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.unauthenticated'));
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
