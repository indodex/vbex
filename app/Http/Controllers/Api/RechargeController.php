<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\ApiController as Controller;

use App\Services\Recharge;
use App\Services\RechargeUser;
use App\Models\UserRechargeCodeModel;
use App\Models\RechargeItemsModel;
use App\Models\UserModel;

use App\Services\GoogleVerify;
use Hash;

class RechargeController extends Controller
{

    public function __construct() 
    {
        parent::__construct();
    }

    /**
     * 充值码充值
     * @return Response
     */
    public function officialCodeUsed(Request $request)
    {
        $uid   = $this->uid;
        $token = $request->input('api_token', null);
        $lang  = $request->input('lang', null);
        $code  = $request->input('code', null);
        
        if(empty($code)) {
            return $this->setStatusCode(400)->responseError(__('api.recharge.code_not_empty'));
        }

        if(empty($uid)) {
            return $this->setStatusCode(404)->responseError(__('api.public.please_login'));
        }

        $result = $this->getRechargeServices()
                       ->rechargeCode($uid, $code);
        if($result['status'] == 1) {
            return $this->setStatusCode(200)->responseSuccess([], __('api.recharge.recharge_successed'));
        } else {
            return $this->setStatusCode(404)->responseError($result['error']);
        }
    }

    /**
     * 个人充值码
     * @return Response
     */
    public function personalCodeUsed(Request $request)
    {
        $uid   = $this->uid;
        $token = $request->input('api_token', null);
        $lang  = $request->input('lang', null);
        $code  = $request->input('code', null);
        if(empty($code)) {
            return $this->setStatusCode(400)->responseError(__('api.recharge.code_not_empty'));
        }

        if(empty($uid)) {
            return $this->setStatusCode(404)->responseError(__('api.public.please_login'));
        }

        $result = $this->getRechargeUserServices()
                       ->rechargeCode($uid, $code);
        if($result['status'] == 1) {
            return $this->setStatusCode(200)->responseSuccess([], __('api.recharge.recharge_complete'));
        } else {
            return $this->setStatusCode(404)->responseError($result['error']);
        }
    }

    // 检查充值码
    public function check(Request $request)
    {
        $token = $request->input('api_token', null);
        $lang = $request->input('lang', null);
        $code = $request->input('code', null);

        if(empty($code)) {
            return $this->setStatusCode(400)->responseError(__('api.recharge.code_not_empty'));
        }

        if(empty($this->uid)) {
            return $this->setStatusCode(404)->responseError(__('api.public.please_login'));
        }

        if(!preg_match("/^[0-9]{1,10}_hac_[0-9A-Z]{18}_[0-9]{10}$/", $code)){
            return $this->setStatusCode(404)->responseError(__('api.recharge.code_unable'));
        }

        $result = $this->getRechargeServices()->checkRechargeCode($code);
        
        if($result) {
            return $this->setStatusCode(200)->responseSuccess([], __('api.recharge.code_allow'));
        } else {
            return $this->setStatusCode(404)->responseError(__('api.recharge.code_unable'));
        }
    }

    // 创建充值码
    public function create(Request $request)
    {
        $token        = $request->input('api_token', null);
        $lang         = $request->input('lang', null);
        $itemId       = $request->input('itemId', null);
        $currencyCode = $request->input('currencyCode', null);
        $oneCode      = $request->input('oneCode', null);       // 谷歌验证码
        $tradesCode   = $request->input('tradesCode', null);    // 交易密码
        $uid          = $this->uid;
        if(empty($itemId)) {
            return $this->setStatusCode(400)->responseError(__('api.recharge.select_create_items'));
        }
        if(empty($currencyCode)) {
            return $this->setStatusCode(400)->responseError(__('api.recharge.select_money_type'));
        }
        if(empty($uid)) {
            return $this->setStatusCode(404)->responseError(__('api.public.please_login'));
        }
        $user = $this->getUserModel()->getInfo($uid);

        if($user->is_freeze == 1) {
            return $this->setStatusCode(404)->responseError(__('api.account.user_is_freeze'));
        }

        if($user->is_lock == 1) {
            return $this->setStatusCode(404)->responseError(__('api.account.user_is_lock'));
        }

        $checkResult = GoogleVerify::verifyBySecret($user->google_secret, $oneCode, 0);

        // 校验谷歌验证码
        if (!$checkResult) {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('api.member.google_verify_error'));
        }

        $checkResult = Hash::check($tradesCode,$user->trade_code);
        if (!$checkResult) {
            // 交易密码不正确
            return $this->setStatusCode(400)->responseNotFound(__('api.member.tradeCode_error'));
        }

        $recharge = $this->getRechargeUserServices()
                         ->setUser($this->getUser());
        $result   = $recharge->createCode($itemId, $currencyCode);
        if($result['status'] == 1) {
            return $this->setStatusCode(200)
                        ->responseSuccess([], __('api.recharge.create_successed'));
        } else {
            return $this->setStatusCode(404)->responseError($result['error']);
        }
    }

    public function useHashCode(Request $request)
    {   
        $token        = $request->input('api_token', null);
        $code         = $request->input('code', null);
        $uid          = $this->uid;

        if(empty($uid)) {
            return $this->setStatusCode(404)->responseError(__('api.public.please_login'));
        }

        if(empty($code)) {
            return $this->setStatusCode(400)->responseError(__('api.recharge.code_not_empty'));
        }

        if(!preg_match("/^[0-9]{1,10}_hac_[0-9A-Z]{18}_[0-9]{10}$/", $code)){
            return $this->setStatusCode(404)->responseError(__('api.recharge.code_unable'));
        }

        $codeArr = explode('_',$code);
        if(preg_match("/^(US).*$/",$codeArr[2])){
            return $this->personalCodeUsed($request);
        }else{
            return $this->officialCodeUsed($request);
        }
    }

    public function getCodes(Request $request)
    {

        $token = $request->input('api_token', null);
        $lang = $request->input('lang', null);
        
        if(empty($this->uid)) {
            return $this->setStatusCode(404)->responseError(__('api.public.please_login'));
        }

        $recharge = $this->getUserRechargeCodeModel();
        $list = $recharge->getListByUid($this->uid);
        if($list) {
            foreach ($list as $key => &$value) {
                $value->currencyType = $value->moneyTo()->first()->name;
                if($value->recharge_uid > 0) {
                    $value->recharge_user = $value->user()->first()->name;
                } else {
                    $value->recharge_user = '';
                }

                switch ($value->status) {
                    case '3':
                        $value->status = '未使用';
                        break;
                    case '2':
                        $value->status = '等待审核';
                        break;
                    case '1':
                        $value->status = '充值成功';
                        break;
                    case '0':
                        $value->status = '充值失败';
                        break;
                }
                unset($value['is_delete']);
            }
            $list = $list->toArray();
            return $this->setStatusCode(200)->responseSuccess($list, 'success');
        } else {
            return $this->setStatusCode(404)->responseError(__('api.public.empty_data'));
        }
    }

    public function items()
    {
        $items = $this->getRechargeItemsModel()->getList(['enable' => 1], 1, 20);

        if(!empty($items)) {
            $items = $items->toArray();
            $user = $this->getUser();

            $data['user']['google_secret'] = $user->google_secret;
            $data['user']['trade_code'] = $user->trade_code;
            $data['data'] = $items['data'];
            return $this->setStatusCode(200)->responseSuccess($data, 'success');
        } else {
            return $this->setStatusCode(404)->responseError(__('api.public.empty_data'));
        }
    }

    public function showCode(Request $request)
    {
        $uid        = $this->uid;
        $cid        = $request->input('cid', null);
        $googleCode = $request->input('googleCode', null);    // 谷歌验证码
        $tradesCode = $request->input('tradesCode', null);    // 交易密码

        if(empty($this->uid)) {
            return $this->setStatusCode(404)->responseError(__('api.public.please_login'));
        }

        if(empty($googleCode)) {
            return $this->setStatusCode(404)->responseError(__('api.member.google_code_empty'));
        }

        // 校验谷歌验证码
        $user = $this->getUserModel()->getInfo($uid);
        $googleCheck = GoogleVerify::verifyBySecret($user->google_secret, $googleCode, 0);
        if (!$googleCheck) {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('api.member.google_verify_error'));
        }

        if(empty($tradesCode)) {
            return $this->setStatusCode(404)->responseError(__('api.member.tradeCode_empty'));
        }

        // 交易密码验证
        $tradesCheck = Hash::check($tradesCode,$user->trade_code);
        if (!$tradesCheck) {
            return $this->setStatusCode(400)->responseNotFound(__('api.member.tradeCode_error'));
        }

        $recharge = $this->getUserRechargeCodeModel();
        $codeInfo = $recharge->getInfo($cid);
        if(empty($codeInfo)) {
            return $this->setStatusCode(404)->responseError(__('api.recharge.code_unable'));
        }

        return $this->responseSuccess(['code' => $codeInfo->code], 'success');
    }

    private function getRechargeServices()
    {
        return new Recharge();
    }

    private function getRechargeUserServices()
    {
        return new RechargeUser();
    }

    private function getUserRechargeCodeModel()
    {
        return new UserRechargeCodeModel();
    }

    private function getRechargeItemsModel()
    {
        return new RechargeItemsModel();
    }

    public function getUserModel()
    {
        return new UserModel();
    }
}
