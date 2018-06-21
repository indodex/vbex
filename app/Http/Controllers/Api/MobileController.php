<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Controllers\ApiController as Controller;
use App\Models\UserModel;
use App\Lib\GoogleAuthenticator;
use App\Services\GoogleVerify;
use App\Services\Authenticate;
use App\Services\Accounts;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use App\User;
use Hash;

class MobileController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    // use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function change(Request $request)
    {
        $token      = $request->input('api_token', null);
        $mobile     = $request->input('mobile', null);
        $tradeCode  = $request->input('tradeCode', null);
        $googleCode = $request->input('googleCode', null);
        $code       = $request->input('code', null);
        $ip         = $request->getClientIp();
        $uid        = $this->uid;
        $is_lock    = false;

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        if(empty($mobile)) {
            return $this->setStatusCode(400)->responseNotFound(__('auth.register.empty_mobile'));
        }

        // 手机号是否存在
        if($this->isUser($mobile) == 1) {
            return $this->setStatusCode(403)->responseNotFound(__('api.public.phone_is_register'));
        }

        if(empty($tradeCode)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.member.tradeCode_empty'));
        }

        if($code != $this->getCacheVerifyCode($mobile)){
            return $this->setStatusCode(400)->responseNotFound(__('api.public.verify_code_error'));
        }

        $user = $this->getUserModel()->getInfo($uid);
        if ($user->mobile) {
            $is_lock = true;
        }
        $authService = $this->getAuthService()->setUser($user);

        // 验证当前密码
        if(!$authService->checkTradeCode($tradeCode)){
            return $this->setStatusCode(400)->responseNotFound(__('api.member.tradeCode_error'));
        }

        if (empty($user->google_secret)) {
            // 未绑定谷歌验证
            return $this->setStatusCode(400)->responseNotFound(__('api.member.google_secret_null'));
        }

        $result = GoogleVerify::verifyBySecret($user->google_secret, $googleCode, 0);
        if (!$result) {
            // 谷歌验证失败
            return $this->setStatusCode(403)
                        ->responseNotFound(__('api.member.google_verify_error'));
        }

        $result = $authService->changeMobile($mobile);
        if($result['status'] == 1) {
            if ($is_lock) {
                $this->getAccountsService()->writeTradeCodeLog($uid,$ip,3);
            }
            return $this->setStatusCode(200)->responseSuccess($result['data'], __('api.public.success'));
        } else {
            return $this->setStatusCode(404)->responseNotFound(__('auth.register.password_verify_error'));
        }

    }

    public function checkCode(Request $request)
    {
        $token = $request->input('api_token', null);
        $code  = $request->input('code', null);
        $user  = $this->getUser();

        if(empty($user)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        if(empty($code)){
            return $this->setStatusCode(400)->responseNotFound(__('api.public.verify_code_empty'));
        }

        if($code != $this->getCacheVerifyCode($user->mobile)){
            return $this->setStatusCode(400)->responseNotFound(__('api.public.verify_code_error'));
        }

        return $this->setStatusCode(200)->responseSuccess([], __('api.public.verify_successed'));
    }

    public function codeSend(Request $request){
        $phone = $request->input('mobile');

        if(empty($phone)) {
            return $this->setStatusCode(403)->responseNotFound(__('api.public.phone_empty'));
        }

        // 手机号是否存在
        // if($this->isUser($phone) == 1) {
        //     return $this->setStatusCode(403)->responseNotFound(__('api.public.phone_is_register'));
        // }

        // 检查锁
        if($seconds = $this->checkCodeLocked($phone)){
            return $this->setStatusCode(403)->responseNotFound(__('api.public.send_regain', ['seconds' => $seconds]));
        }

        if($phone){
            if($this->codeCreate($phone,$request)){
                $this->setCodeLocked($phone);
                return $this->setStatusCode(200)->responseSuccess([], __('api.public.send_successed'));
            } else {
                $this->setCodeLocked($phone);
                return $this->setStatusCode(403)->responseNotFound(__('api.public.send_failed'));
            }
        } else {
            return $this->setStatusCode(403)->responseNotFound(__('api.public.phone_empty'));
        }
    }

    public function sendToCode(Request $request){

        $user = $this->getUser();

        if(empty($user)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        // 检查锁
        if($seconds = $this->checkCodeLocked($user->mobile)){
            return $this->setStatusCode(403)->responseNotFound(__('api.public.send_regain', ['seconds' => $seconds]));
        }

        if($user->mobile){
            if($this->codeCreate($user->mobile, $request)){
                $this->setCodeLocked($user->mobile);
                return $this->setStatusCode(200)->responseSuccess([], __('api.public.send_successed'));
            } else {
                return $this->setStatusCode(403)->responseNotFound(__('api.public.send_failed'));
            }
        } else {
            return $this->setStatusCode(403)->responseNotFound(__('api.public.phone_empty'));
        }
    }

    public function isUser($phone){
        $user = User::where('mobile', '=', $phone)->first();
        return empty($user) ? 0: 1;
    }

    public function codeCreate($phone, $request, $sms_no = 'SMS_71905363'){
        $num =(string) rand(1000,9999); // 组装参数
        $this->setCacheVerifyCode($phone, $num);  // 存入session 后面做数据验证
        $retSend = $this->send($phone, array('code' => $num, 'product' => 'HAC'), $sms_no);
        if($retSend){
            $retData = json_decode($retSend);
            return intval($retData->code) == 0 ? 1 : 0;
        } else {
            return 0;
        }
    }

    // $sms = app('sms');  
    // $sms->send($phone,$smsParam,$templateCode);
    // ###函数使用相关
    // $phone strong 接收的号码
    // $smsParam array 短信模板，详情请参考 开发文档
    // $templateCode string 例如：'SMS_585014' ,请到配置文件中配置默认的值
    // $signName string 例如：'大鱼', 请到配置文件中配置默认的值，这个字段通常情况下不会改变
    public function send($phone,$smsParam,$templateCode=0,$signName=0){
        $sms = app('sms'); 
        return $sms->send($phone,$smsParam,$templateCode);
    }

    public function checkCodeLocked($phone)
    {
        $verifyCode = Cache::get('code_locked:' . $phone);
        if($verifyCode) {
            return abs(time() - $verifyCode);
        }
        return false;
    }

    public function setCodeLocked($phone, $minutes = 1)
    {
        $systime = time() + $minutes * 60;
        return Cache::put('code_locked:' . $phone, $systime, $minutes);
    }

    public function setCacheVerifyCode($phone, $code)
    {
        return Cache::put('verify_code:' . $phone, $code, config('cache.cache_minutes'));
    }

    public function getCacheVerifyCode($phone)
    {
        return Cache::get('verify_code:' . $phone);
    }

    public function clearCacheVerifyCode($phone)
    {
        return Cache::forget('verify_code:' . $phone);
    }

    public function getUserModel()
    {
        return new UserModel();
    }

    public function getAuthService()
    {
        return new Authenticate();
    }

    public function getAccountsService()
    {
        return new Accounts();
    }
}
