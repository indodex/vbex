<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\ApiController as Controller;

use App\Services\Accounts;
use App\Services\DepositsOrders;
use App\Services\Withdraw;
use App\Services\Withdraws;
use App\Services\WithdrawsOrders;
use App\Services\Authenticate;
use App\Services\Currency;
use App\Lib\GoogleAuthenticator;
use App\Services\GoogleVerify;
use App\Models\UserModel;
use App\Models\UserCertificationModel;
use Hash;
use App\Redis\Withdraw as WithdrawRds;
use App\Services\Email;
use App\Services\Mobile;

class WalletController extends Controller
{

    public function __construct() 
    {
        parent::__construct();
    }

    // 钱包资产
    public function account(Request $request)
    {
        $uid      = $this->uid;
        $token    = $request->input('api_token', null);
        $lang     = $request->input('lang', null);
        $coinType = $request->input('coinType', null);

        if(empty($uid)) {
            return $this->setStatusCode(403)->responseNotFound(__('api.public.please_login'));
        }

        if(empty($coinType)) {
            return $this->setStatusCode(403)
                        ->responseError(__('api.account.lack_currency'));
        }
        $coinType = strtoupper($coinType);

        $result = $this->getAccountsService()->getAccount($uid, $coinType);

        if($result['status'] == 1) {

            $data = $result['data'];

            $account['coinName']          = $data['currency']['name'];
            $account['coinCode']          = $data['currency']['code'];
            $account['coinConfirmations'] = $data['currency']['confirmations'];
            $account['coinFee']           = (float) $data['currency']['withdraw_service_charge'];
            $account['depositAddress']    = $data['address']['address'];
            $account['balance']           = (float) $data['account']['balance'];
            $account['balanceStr']        = my_number_format($data['account']['balance']);
            $account['lockedBalance']     = (float) $data['account']['balance'];
            $account['lockedBalanceStr']  = my_number_format($data['account']['balance']);
            $account['total']             = $account['balance'] + $account['lockedBalance'];
            $account['totalStr']          = my_number_format($account['total']);
            $account['explain']           = __('api.account.tip_explain', ['coin' => $account['coinName'], 'number' => $account['coinConfirmations']]);

            return $this->setStatusCode(200)
                        ->responseSuccess($account, 'success');
        } else {
            return $this->setStatusCode(403)
                        ->responseError(__('api.public.empty_data'));
        }   
    }

    // 数字资产转入记录
    public function depostRecords(Request $request)
    {
        $uid      = $this->uid;
        $token    = $request->input('api_token', null);
        $lang     = $request->input('lang', null);
        $page     = $request->input('page', 1);
        $coinType = $request->input('coinType', null);

        if(empty($uid)) {
            return $this->setStatusCode(403)->responseNotFound(__('api.public.please_login'));
        }

        if(empty($coinType)) {
            return $this->setStatusCode(403)
                        ->responseError(__('api.account.lack_currency'));
        }
        $coinType = strtoupper($coinType);

        $result = $this->getDepositsOrdersService()->getOrders($uid, $coinType, $page);

        if($result['status'] == 1) {
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

    // 数字资产转出
    public function withdrawApply(Request $request)
    {
        $uid        = $this->uid;
        $token      = $request->input('api_token', null);
        $lang       = $request->input('lang', null);
        $address    = $request->input('address', null);
        $amount     = $request->input('amount', null);
        $fee        = $request->input('fee', null);
        $tradeCode  = $request->input('tradeCode', null);
        $googleCode = $request->input('googleCode', null);
        $coinType   = $request->input('coinType', null);
        $code       = $request->input('code', null);

        if(empty($uid)) {
            return $this->setStatusCode(403)->responseNotFound(__('api.public.please_login'));
        }

        // 加锁
        $this->_setLockKey("{$uid}:{$coinType}");
        if($this->_checkLock()){
            return $this->setStatusCode(403)->responseNotFound(__('api.public.post_to_often'));
        }

        if(empty($amount) || $amount == 0) {
            $this->_cleanLock();
            return $this->setStatusCode(403)->responseNotFound(__('api.withdraw.amount_empty'));
        }

        if(empty($tradeCode)) {
            $this->_cleanLock();
            return $this->setStatusCode(400)->responseNotFound(__('api.member.tradeCode_empty'));
        }

        $user = $this->getUserModel()->getInfo($uid);

        if($user->is_freeze == 1) {
            $this->_cleanLock();
            return $this->setStatusCode(400)->responseNotFound(__('api.account.user_is_freeze'));
        }

        if($user->is_lock == 1) {
            $this->_cleanLock();
            return $this->setStatusCode(400)->responseNotFound(__('api.account.user_is_lock'));
        }

        //校验交易密码
        $tradeCheck = Hash::check($tradeCode, $user->trade_code);
        if (!$tradeCheck) {
            $this->_cleanLock();
            return $this->setStatusCode(400)->responseNotFound(__('api.member.tradeCode_error'));
        }

        if(empty($coinType)) {
            $this->_cleanLock();
            return $this->setStatusCode(403)
                        ->responseError(__('api.account.lack_currency'));
        }
        $coinType = strtoupper($coinType);

        $cert = $this->getUserCertModel()->getInfoByUid($uid);
        if(empty($cert) || $cert->status != 1) {
            $this->_cleanLock();
            return $this->setStatusCode(403)
                        ->responseError(__('api.public.withdraw_unverified'));
        }

        // 提币安全策略
        if($user->withdrawal_option > 0) {
            list($lcode, $lmsg) = $this->withdrawalOption($request, $user);
            if($lcode != 200) {
                $this->_cleanLock();
                return $this->setStatusCode(400)->responseError($lmsg);
            }
        }

        $checkTrade = $this->getAccountsService()->checkTradeCodeLog($uid);
        if($checkTrade['result'] == false) {
            $this->_cleanLock();
            return $this->setStatusCode(403)
                        ->responseError($checkTrade['message']);
                        // ->responseError(__('api.account.change_trade_pwd_note'));
        }


        $result = $this->getWithdrawService()->apply([
                        'uid'      => $uid,
                        'amount'   => $amount,
                        'address'  => $address,
                        'coinType' => $coinType,
                        'fee'      => $fee,
                  ]);

        $this->_cleanLock();
        if($result['status'] == 1) {
            $orderId = $result['data'];
            $order   = $this->getWithdrawsOrdersService()->getOrder($orderId);
            if(!empty($order)) {
                if(bccomp($order->amount, $order->currencyTo->extract_number_audit, 8) > -1) {
                    $message =  __('api.account.withdraw_apply_successed');
                } else {
                    $message =  __('api.account.withdraw_apply_mailto'); 
                }
            }
            return $this->setStatusCode(200)
                        ->responseSuccess([], $message);
        } else {
            return $this->setStatusCode(403)
                        ->responseError($result['error']);
        }
    }

    public function withdrawAddresses(Request $request)
    {
        $uid      = $this->uid;
        $token    = $request->input('api_token', null);
        $lang     = $request->input('lang', null);
        $coinType = $request->input('coinType', null);

        if(empty($uid)) {
            return $this->setStatusCode(403)->responseNotFound(__('api.public.please_login'));
        }

        if(empty($coinType)) {
            return $this->setStatusCode(403)
                        ->responseError(__('api.account.lack_currency'));
        }
        $coinType = strtoupper($coinType);

        $result = $this->getWithdrawService()
                        ->getUserAddressList($uid, $coinType);

        if($result['status'] == 1) {
            $list = $result['data']['data'];
            return $this->setStatusCode(200)
                        ->responseSuccess(array('list' => $list), 'success');
        } else {
            return $this->setStatusCode(403)
                        ->responseError($result['error']);
        }
    }

    // 添加转出钱包地址
    public function addAddress(Request $request)
    {
        $uid        = $this->uid;
        $token      = $request->input('api_token', null);
        $lang       = $request->input('lang', null);
        $name       = $request->input('name', null);
        $address    = $request->input('address', null);
        $coinType   = $request->input('coinType', null);
        $tradeCode  = $request->input('tradeCode', null);
        $googleCode = $request->input('googleCode', null);
        $code       = $request->input('code', null);
        $user       = $this->getUserModel()->getInfo($uid);


        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        if(empty($address)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.account.please_input_address'));
        }

        if(empty($name)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.account.please_input_address_name'));
        }

        if(empty($coinType)) {
            return $this->setStatusCode(400)
                        ->responseError(__('api.account.lack_currency'));
        }
        $coinType = strtoupper($coinType);

        if(empty($tradeCode)) {
            return $this->setStatusCode(400)
                        ->responseError(__('api.member.tradeCode_empty'));
        }

        // 交易安全密码
        $authService = $this->getAuthService()->setUser($user);
        if(!$authService->checkTradeCode($tradeCode)) {
            return $this->setStatusCode(400)
                        ->responseError(__('api.member.tradeCode_error'));
        }

        // 提币安全策略
        if($user->withdrawal_option > 0) {
            list($lcode, $lmsg) = $this->withdrawalOption($request, $user);
            if($lcode != 200) {
                return $this->setStatusCode(400)
                        ->responseError($lmsg);
            }
        }
        

        $result = $this->getWithdrawService()->addAddress([
                            'uid'      => $uid,
                            'name'     => $name,
                            'address'  => $address,
                            'coinType' => $coinType,
                        ]);

        if($result['status'] == 1) {
            return $this->setStatusCode(200)
                        ->responseSuccess($result['data'], 'success');
        } else {
            return $this->setStatusCode(404)
                        ->responseError($result['error']);
        }
    }

    // 修改转出钱包地址
    public function editAddress(Request $request)
    {
        $uid        = $this->uid;
        $token      = $request->input('api_token', null);
        $lang       = $request->input('lang', null);
        $name       = $request->input('name', null);
        $address    = $request->input('address', null);
        $coinType   = $request->input('coinType', null);
        $id         = $request->input('id', null);
        $oldAddress = $request->input('old_address', null);

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        if(empty($address)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.account.please_input_address'));
        }

        if(empty($name)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.account.please_input_address_name'));
        }

        if(empty($coinType)) {
            return $this->setStatusCode(400)
                        ->responseError(__('api.account.lack_currency'));
        }
        $coinType = strtoupper($coinType);

        $result = $this->getWithdrawService()->editAddress($id, [
                        'name' => $name,
                        'address' => $address
                    ]);

        if($result['status'] == 1) {
            return $this->setStatusCode(200)
                        ->responseSuccess($result['data'], 'success');
        } else {
            return $this->setStatusCode(404)
                        ->responseError($result['error']);
        }
    }

    // 删除钱包地址
    public function delAddress(Request $request)
    {
        $uid        = $this->uid;
        $token      = $request->input('api_token', null);
        $lang       = $request->input('lang', null);
        $id         = $request->input('id', null);
        $tradeCode  = $request->input('tradeCode', null);
        $googleCode = $request->input('googleCode', null);
        $user       = $this->getUserModel()->getInfo($uid);

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        if(empty($id)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.withdraw.lack_argument'));
        }

        if(empty($tradeCode)) {
            return $this->setStatusCode(400)
                        ->responseError(__('auth.register.empty_password'));
        }

        // 密码检查
        $authService = $this->getAuthService()->setUser($user);
        if(!$authService->checkTradeCode($tradeCode)) {
            return $this->setStatusCode(400)
                        ->responseError(__('api.member.tradeCode_error'));
        }

        if(empty($googleCode)) {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('api.member.google_verify_empty'));
        }

        $checkResult = GoogleVerify::verifyBySecret($user->google_secret, $googleCode, 0);
        // 校验谷歌验证码
        if (!$checkResult) {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('api.member.google_verify_error'));
        }

        $result = $this->getWithdrawService()->deleteAddress($id);
        if($result['status'] == 1) {
            return $this->setStatusCode(200)
                        ->responseSuccess($result['data'], 'success');
        } else {
            return $this->setStatusCode(404)
                        ->responseError($result['error']);
        }
    }

    // 获取钱包地址
    public function getWithdrawAddress(Request $request)
    {
        $uid        = $this->uid;
        $token      = $request->input('api_token', null);
        $lang       = $request->input('lang', null);
        $id         = $request->input('id', null);

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        if(empty($id)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.withdraw.lack_argument'));
        }

        $result = $this->getWithdrawService()->getWithdrawAddress($id);
        if($result['status'] == 1) {
            return $this->setStatusCode(200)
                        ->responseSuccess($result['data'], 'success');
        } else {
            return $this->setStatusCode(404)
                        ->responseError($result['error']);
        }
    }

    // 数字资产转出记录
    public function withdrawRecords(Request $request)
    {
        $uid      = $this->uid;
        $token    = $request->input('api_token', null);
        $lang     = $request->input('lang', null);
        $page     = $request->input('page', 1);
        $coinType = $request->input('coinType', null);

        if(empty($uid)) {
            return $this->setStatusCode(403)->responseNotFound(__('api.public.please_login'));
        }

        if(empty($coinType)) {
            return $this->setStatusCode(403)
                        ->responseError(__('api.account.lack_currency'));
        }
        $coinType = strtoupper($coinType);

        $result = $this->getWithdrawsOrdersService()->getOrders($uid, $coinType, $page);

        if($result['status'] == 1) {
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

    // 获取提现手续费
    public function withdrawFee(Request $request)
    {
        $uid      = $this->uid;
        $token    = $request->input('api_token', null);
        $lang     = $request->input('lang', null);
        $coinType = $request->input('coinType', null);

        $result = $this->getCurrencyService()->getFee($coinType);
        if($result['status'] == 1) {
            $returnData['list'] = $result['data']['data'];
            foreach ($returnData['list'] as &$value) {
                $value['fee'] = (float) $value['fee'];
            }
            return $this->responseSuccess($returnData, 'success');
        } else {
            return $this->setStatusCode(403)
                        ->responseError($result['error']);
        }     
    }

    public function getCacheVerifyCode($phone)
    {
        return Cache::get('verify_code:' . $phone);
    }

    public function withdrawConfirm(Request $request)
    {
        $id = $request->input('id', null);

        if(empty($id)) {
            return $this->setStatusCode(403)->responseNotFound(__('api.public.illegal_operation'));
        }

        $withdrawRds = new WithdrawRds();

        $orderId = $withdrawRds->getWithdrawApplyKey($id);
        if(empty($orderId)) {
            return $this->setStatusCode(403)->responseNotFound(__('api.withdraw.withdraw_url_expired'));
        }

        $order = $this->getWithdrawsOrdersService()->getOrder($orderId);
        if(empty($order)) {
            return $this->setStatusCode(403)->responseNotFound(__('api.public.empty_data'));
        }

        $result = $this->getWithdrawsService()->autoWithdraw($order->id, __('api.withdraw.withdraw_successed', ['date' => date('Y-m-d H:i:s'), 'number' => (float) $order->sum_amount, 'code' => $order->currencyTo->code, 'address' => $order->address]));

        if($result['status'] == 1) {
            $withdrawRds->delWithdrawApplyKey($id);
            return $this->responseSuccess([], __('api.withdraw.withdraw_success_note'));
        } else {
            return $this->setStatusCode(403)->responseNotFound($result['error']);
        }

    }

    public function withdrawalOption(Request $request, $user)
    {
        $googleCode = $request->input('googleCode', null);
        $code       = $request->input('code', null);

        // 验证码验证
        if($user->withdrawal_option == 2 || $user->withdrawal_option == 3) {
            if(empty($code)) {
                return [403, __('api.public.verify_code_empty')];
            }

            if($user->mobile) {
                $checkModel = new Mobile();
            } else {
                $checkModel = new Email();
            }
            $checkResult = $checkModel->verifyCode($user, $code);
            
            // 动态验证码出错
            if (!$checkResult) {
                return [403, __('api.public.verify_code_error')];
            }
        }

        // 谷歌验证
        if($user->withdrawal_option == 1 || $user->withdrawal_option == 3) {
            if(empty($googleCode)) {
                return [403, __('api.member.google_verify_empty')];
            }

            $checkResult = GoogleVerify::verifyBySecret($user->google_secret, $googleCode, 0);
            // 校验谷歌验证码
            if (!$checkResult) {
                return [403, __('api.member.google_verify_error')];
            }
        }

        return [200, 'Successed'];
    }

    public function getWithdrawService()
    {
        return new Withdraw();
    }

    public function getWithdrawsService()
    {
        return new Withdraws();
    }

    public function getAccountsService()
    {
        return new Accounts();
    }

    public function getDepositsOrdersService()
    {
        return new DepositsOrders();
    }

    public function getWithdrawsOrdersService()
    {
        return new WithdrawsOrders();
    }

    public function getUserCertModel()
    {
        return new UserCertificationModel();
    }

    public function getAuthService()
    {
        return new Authenticate();
    }

    public function getCurrencyService()
    {
        return new Currency();
    }

    public function getUserModel()
    {
        return new UserModel();
    }
}
