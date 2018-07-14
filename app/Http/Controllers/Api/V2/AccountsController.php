<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use App\Services\Currency;
use App\Http\Controllers\Api\V2\ApiController as Controller;

class AccountsController extends Controller
{
    /**
     * 查询账户资产
     * GET api/v2/accounts/balance
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function balance(Request $request)
    {
        $token = $request->input('api_token', null);
        $lang  = $request->input('lang', null);
        $uid   = $this->uid;

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $result = $this->getAccountsService()->checkAccount($uid);

        $list = array();
        $price = 0;
        if($result['status'] == 1) {
            $m = $this->getOrderdetailsModel();
            $currency_id = $this->getCurrencyModel()->getIdByCode('USD');

            foreach ($result['data'] as $key => $value) {
                $code                            = strtolower($value['coin']);
                $list[$code]['coin']             = $value['coin'];
                $list[$code]['balance']          = my_number_format($value['balance'], $value['decimals']);
                $list[$code]['balanceStr']       = my_number_format($value['balance'], $value['decimals']);
                $list[$code]['lockedBalance']    = my_number_format($value['locked'], $value['decimals']);
                $list[$code]['lockedBalanceStr'] = my_number_format($value['locked'], $value['decimals']);
                $list[$code]['total']            = my_number_format($value['balance'] + $value['locked'], $value['decimals']);
                $list[$code]['totalStr']         = my_number_format($value['balance'] + $value['locked'], $value['decimals']);
                $list[$code]['enable_deposit']   = $value['enable_deposit'];
                $list[$code]['enable_withdraw']  = $value['enable_withdraw'];
                $list[$code]['price']            = 0;
                if($value['logo']) {
                    $list[$code]['logo']         = Storage::disk('public')->url($value['logo']);
                } else {
                    $list[$code]['logo']         = '';
                }

                if ($code == 'usd') {
                    $list[$code]['price'] = 1;
                    $price = $price + $value['balance'] + $value['locked'];
                }else if($value['is_virtual'] == 0){

                    $data = ExchangeRatesModel::where(['market'=>'USD_'.$value['coin']])->first();
                    if ($data) {
                        $list[$code]['price'] = my_number_format($data->price,4);;
                        $price = $price + my_number_format(($value['balance'] + $value['locked']) * $data->price,4);
                    }
                }else{
                    $cond['buy_currency'] = $value['currency'];
                    $cond['sell_currency'] = $currency_id;
                    $newOrder = $m->getOne($cond);

                    if ($newOrder) {
                        $list[$code]['price']            = $newOrder->price;
                        $price = $price + my_number_format(bcadd($value['balance'], $value['locked'], 18) * $newOrder->price, 4);
                    }
                }

            }
        }

        $currencies = $this->getCurrencyModel()->getNotVirtual();

        $proportion = [];
        if ($currencies) {
            foreach ($currencies as $key => $value) {
                $row['code'] = $value->code;
                if ($value->code == 'USD') {
                    $row['proportion'] = 1;
                }else{
                    $market = 'USD_'.$value->code;
                    $data = ExchangeRatesModel::where(['market'=>$market])->first();
                    if ($data) {
                        $row['proportion'] = my_number_format($data->price,4);
                    }
                }

                if (!empty($row['proportion'])) {
                    $proportion[] = $row;
                }

            }
        }

        if(count($list) > 1) {
            $reutrnData['list'] = $list;
            $reutrnData['price'] = $price;
            $reutrnData['proportion'] = $proportion;
            $reutrnData['timestamp'] = time();
            return $this->setStatusCode(200)
                ->responseSuccess($reutrnData, 'success');
        } else {
            return $this->setStatusCode(404)
                ->responseError(__('api.public.empty_data'));
        }
    }
}