<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

use App\Services\Currency;

use App\Http\Controllers\Api\V2\ApiController as Controller;

class PublicController extends Controller
{
    /**
     * 查询服务器时间
     * GET api/v2/public/server-time
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function serverTime(Request $request)
    {
        return $this->responseSuccess(time());
    }

    /**
     * 查询可用币种
     * GET api/v2/public/currencies
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function currencies(Request $request)
    {
        $result = $this->getCurrencyService()->getVirtualCurrencies();

        $list = array();
        if($result['status'] == 1) {
            foreach ($result['data'] as $key => $value) {
                $list[] = $value['code'];
            }
        }

        if(!empty($list)) {
            $reutrnData = $list;
            return $this->setStatusCode(200)
                        ->responseSuccess($reutrnData, 'success');
        } else {
            return $this->setStatusCode(422)
                        ->responseError('error');
        }
    }

    /**
     * 查询可用交易对
     * GET api/v2/public/symbols
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function symbols(Request $request)
    {
        $result = $this->getCurrencyService()->getMarkets();
        if($result['status'] == 1) {
            $list = $result['data'];
            $symbols = [];
            foreach ($list as $key => $symbol) {
                $symbols[$key]['name'] = $symbol['market'];
                $symbols[$key]['base_currency'] = $symbol['buy']['coin'];
                $symbols[$key]['quote_currency'] = $symbol['sell']['coin'];
            }

            return $this->responseSuccess($symbols, 'success');
        } else {
            return $this->setStatusCode(404)
                        ->responseError('error');
        }
    }

    public function getCurrencyService()
    {
        return new Currency();
    }
}