<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Controllers\ApiController as Controller;

use App\Models\UserModel;
use App\Models\CurrencyModel;
use App\Models\UserRechargeCodeModel;
use App\Models\UserCertificationModel;
use App\Models\AttachmentModel;

use App\Lib\GoogleAuthenticator;
use App\Services\GoogleVerify;
use App\Services\Accounts;
use App\Services\Email;
use App\Services\Mobile;
use Storage;
use Hash;

use Illuminate\Support\Facades\Cache;

class MemberController extends Controller
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

    public function getBaseInfo(Request $request)
    {   
        // 暂时传参，后改直接获取用户id
        // $aPost = $request->all();
        $token = $request->input('api_token', null);
        $uid = $this->uid;
        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }
    
        $user = $this->getUser();
        if (empty($user)) {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('api.public.member_empty'));
        }
        if(is_object($user)) {
            if(!empty($user->avatar)) {
                $user->avatar = Storage::disk('public')->url($user->avatar);
            } else {
                $user->avatar = Storage::disk('public')->url('../img/u005.jpg');
            }
            $user->inviteUrl  = env('APP_URL').'/regist?inviteid='.$user->id;
            $user = $user->toArray();
        }

        $this->getAccountsService()->checkAccount($uid);
        $cer = $this->getCer($uid);
        $user['cer_base_status'] = $cer['cer_base_status'];
        $user['cer_advanced_status'] = $cer['cer_advanced_status'];
        $user['cer_remark'] = $cer['remark'];
        
        return $this->responseSuccess($user, __('api.public.success'));
    }

    public function getRechargeOption()
    {
        $currency = CurrencyModel::where(['status' => 1, 'is_virtual' => 0]);
        $recharge_items = RechargeItemsModel::all()->pluck('amount', 'amount');
        
        return $this->responseSuccess([
                    'currency' => $currency,
                    'recharge_items' => $recharge_items,
                ], __('api.public.success'));
    }

    public function getGoogleSecret(Request $request)
    {
        
        $data = GoogleVerify::create($this->getUser());

        return $this->responseSuccess([
                    'googleSecret' => $data['googleSecret'],
                    'qrCodeUrl' => $data['qrCodeUrl'],
                ], __('api.public.success'));
        // echo $googleSecret.'-'.$qrCodeUrl;
    }

    public function bindGoogleSecret(Request $request)
    {   
        $token        = $request->input('api_token', null);
        $tradeCode    = $request->input('tradeCode', null);
        $verifyCode    = (string)$request->input('verifyCode',null);
        $googleSecret = (string)$request->input('googleSecret',null);
        $googleCode   = (string)$request->input('googleCode',null);
        $uid          = $this->uid;

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $checkResult = GoogleVerify::verifyBySecret($googleSecret, $googleCode, 2);

        // 校验谷歌验证码
        if (!$checkResult) {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('api.member.google_verify_error'));
        }

        $user = $this->getUserModel()->getInfo($uid);
        //校验交易密码
        $checkResult = Hash::check($tradeCode,$user->trade_code);
        if (!$checkResult) {
            // 交易密码不正确
            return $this->setStatusCode(400)->responseNotFound(__('api.member.tradeCode_error'));
        }

        //用户输入验证码错误
        if (!$this->verifyCode($user, $verifyCode)) 
        {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.verify_code_error'));
        }

        // 校验成功,写入secret
        $user->google_secret = $googleSecret;
        $result = $user->save();
        if ($result) {
            return $this->responseSuccess([], __('api.public.success'));
        }
        return $this->setStatusCode(403)
                    ->responseNotFound(__('api.public.error'));
        
        
    }

    public function changeGoogleSecret(Request $request)
    {   
        $token         = $request->input('api_token', null);
        $tradeCode     = $request->input('tradeCode', null);
        $googleSecret  = (string)$request->googleSecret;
        $googleCode    = (string)$request->googleCode;
        $oldGoogleCode = (string)$request->oldGoogleCode;
        $verifyCode    = (string)$request->input('verifyCode',null);
        $ip            = $request->getClientIp();
        $uid           = $this->uid;

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $checkResult = GoogleVerify::verifyBySecret($googleSecret, $googleCode, 1);
        // 校验新谷歌验证码
        if (!$checkResult) {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('api.member.new_google_verify_error'));
        }

        $user = $this->getUserModel()->getInfo($uid);
        $checkResult = GoogleVerify::verifyBySecret($user->google_secret, $oldGoogleCode, 1);
        // 校验旧谷歌验证码
        if (!$checkResult) {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('api.member.old_google_verify_error'));
        }

        //校验交易密码
        $checkResult = Hash::check($tradeCode,$user->trade_code);
        if (!$checkResult) {
            // 交易密码不正确
            return $this->setStatusCode(400)->responseNotFound(__('api.member.tradeCode_error'));
        }

        //用户输入验证码错误
        if (!$this->verifyCode($user, $verifyCode)) 
        {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.verify_code_error'));
        }

        // 校验成功,写入secret
        $user->google_secret = $googleSecret;
        $result = $user->save();
        if ($result) {
            $this->getAccountsService()->writeTradeCodeLog($uid,$ip,2);
            return $this->responseSuccess([], __('api.public.success'));
        }
        return $this->setStatusCode(403)
                        ->responseNotFound(__('api.public.error'));
        
    }

    public function forgetGoogleSecret(Request $request)
    {   
        $token         = $request->input('api_token', null);
        $tradeCode     = $request->input('tradeCode', null);
        $googleSecret  = (string)$request->input('googleSecret',null);
        $googleCode    = (string)$request->input('googleCode',null);
        $verifyCode    = (string)$request->input('verifyCode',null);
        $ip            = $request->getClientIp();
        $uid           = $this->uid;

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $checkResult = GoogleVerify::verifyBySecret($googleSecret, $googleCode, 1);
        // 校验新谷歌验证码
        if (!$checkResult) {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('api.member.new_google_verify_error'));
        }

        $user = $this->getUserModel()->getInfo($uid);

        //用户输入验证码错误
        if (!$this->verifyCode($user, $verifyCode)) 
        {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.verify_code_error'));
        }

        //校验交易密码
        $checkResult = Hash::check($tradeCode,$user->trade_code);
        if (!$checkResult) {
            // 交易密码不正确
            return $this->setStatusCode(400)->responseNotFound(__('api.member.tradeCode_error'));
        }

        // 校验成功,写入secret
        $user->google_secret = $googleSecret;
        $result = $user->save();
        if ($result) {
            $this->getAccountsService()->writeTradeCodeLog($uid,$ip,2);
            return $this->responseSuccess([], __('api.public.success'));
        }
        return $this->setStatusCode(403)
                        ->responseNotFound(__('api.public.error'));
        
    }
    
    public function verifyGoogleCode(Request $request)
    {
        $token = $request->input('api_token', null);
        $oneCode = $request->oneCode;
        $uid = $this->uid;

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $user = $this->getUserModel->getInfo($uid);

        if (empty($user->google_secret)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.member.google_secret_null'));
        }

        $result = GoogleVerify::verifyBySecret($user->google_secret, $oneCode, 1);
        if (!$result) {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('api.member.google_verify_error'));
        }
        return $this->responseSuccess([], __('api.member.google_verify_success'));
    }

    // 设置交易密码
    public function setTradeCode(Request $request)
    {
        $token            = $request->input('api_token', null);
        $tradeCode        = $request->input('tradeCode', null);
        $confirmTradeCode = $request->input('confirmTradeCode', null);
        $verifyCode       = $request->input('verifyCode',null);
        $password         = $request->input('password', null);
        $uid              = $this->uid;
        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $user = $this->getUserModel()->getInfo($uid);

        if($user->trade_code){
            return $this->setStatusCode(400)->responseNotFound(__('api.public.illegal_operation'));
        }

        $checkResult = Hash::check($password,$user->password);
        if (!$checkResult) {
            // 交易密码不正确
            return $this->setStatusCode(400)->responseNotFound(__('auth.login.password_error'));
        }

        if ($tradeCode != $confirmTradeCode) {
            // 两次输入密码不一致
            return $this->setStatusCode(400)->responseNotFound(__('api.member.tradeCode_discrepant'));
        }

        //用户输入验证码错误
        if (!$this->verifyCode($user, $verifyCode)) 
        {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.verify_code_error'));
        }

        $tradeCodeHash = Hash::make($tradeCode);
        UserModel::setTradeCode($uid,$tradeCodeHash);

        return $this->responseSuccess([], __('api.public.success'));
    }

    // 修改交易密码
    public function resetTradeCode(Request $request)
    {
        $token            = $request->input('api_token', null);
        $oldTradeCode     = $request->input('oldTradeCode', null);
        $tradeCode        = $request->input('tradeCode', null);
        $confirmTradeCode = $request->input('confirmTradeCode', null);
        $oneCode          = $request->input('oneCode',null);
        $verifyCode       = $request->input('verifyCode',null);
        $ip               = $request->getClientIp();
        $uid              = $this->uid;
        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $user = $this->getUserModel()->getInfo($uid);

        $checkResult = Hash::check($oldTradeCode,$user->trade_code);
        if (!$checkResult) {
            // 交易密码不正确
            return $this->setStatusCode(400)->responseNotFound(__('api.member.tradeCode_error'));
        }

        if (empty($tradeCode)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.member.tradeCode_empty'));
        }

        if ($tradeCode != $confirmTradeCode) {
            // 两次输入密码不一致
            return $this->setStatusCode(400)->responseNotFound(__('api.member.tradeCode_discrepant'));
        }

        //用户输入验证码错误
        if (!$this->verifyCode($user, $verifyCode)) 
        {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.verify_code_error'));
        }

        if (!empty($user->google_secret)) {
            $result = GoogleVerify::verifyBySecret($user->google_secret, $oneCode, 1);
            if (!$result) {
                // 谷歌验证失败
                return $this->setStatusCode(403)
                            ->responseNotFound(__('api.member.google_verify_error'));
            }
        }

        $tradeCodeHash = Hash::make($tradeCode);
        $result = UserModel::setTradeCode($uid,$tradeCodeHash);
        if ($result) {
            // 锁定
            $this->getAccountsService()->writeTradeCodeLog($uid,$ip,1);
            return $this->responseSuccess([], __('api.public.success'));
        }
        return $this->setStatusCode(403)
                            ->responseNotFound(__('api.member.error'));
    }

    public function baseCertification(Request $request)
    {   
        $token            = $request->input('api_token', null);
        $uid              = $this->uid;

        $data['name']            = (string)$request->input('name',null);
        $data['birthday']        = (string)$request->input('birthday',null);
        $data['papers_type']     = (int)$request->input('papers_type',null);
        $data['papers_number']   = (string)$request->input('papers_number',null);
        $data['papers_before']   = (int)$request->input('papers_before',null);
        $data['papers_after']    = (int)$request->input('papers_after',null);
        $data['sex']             = (int)$request->input('sex',null);
        $data['address']         = (string)$request->input('address',null);
        $data['profession']      = (string)$request->input('profession',null);
        $data['advanced']        = (int)$request->input('advanced',null);
        $data['remark']          = (string)$request->input('remark',null);
        $data['status']          = (int)$request->input('status',null);
        $data['advanced_status'] = (int)$request->input('advanced_status',null);
// print_r($data);exit;
        if (empty($data['papers_type'])) {
            return $this->setStatusCode(400)->responseNotFound(__('api.member.cer_papers_type'));
        }
        if (empty($data['papers_number'])) {
            return $this->setStatusCode(400)->responseNotFound(__('api.member.cer_papers_number'));
        }
        if (empty($data['papers_before'])) {
            return $this->setStatusCode(400)->responseNotFound(__('api.member.cer_base_empty'));
        }
        if (empty($data['papers_after'])) {
            return $this->setStatusCode(400)->responseNotFound(__('api.member.cer_base_empty'));
        }

        $m = new UserCertificationModel();

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('public.please_login'));
        }

        $row = $m->where(['uid'=>$uid])->first();

        if(empty($row)){
            // 用户没有提交过，新增数据
            $data['uid'] = $uid;
            $id = $m->add($data);
            if ($id) {
                return $this->responseSuccess([], __('api.public.success'));
            }
            return $this->responseSuccess([], __('api.public.error'));
        }
        
        if ($row->status == 0) {
            return $this->responseSuccess([], __('api.member.cer_base_repeat'));
        }

        // 高级验证已通过
        if ($row->status == 1 && $row->advanced_status == 1) {
            return $this->responseSuccess([], __('api.member.cer_advanced_repeat'));
        }

        // 初级验证已通过
        if ($row->status == 1 ) {
            return $this->setStatusCode(400)->responseNotFound(__('api.member.cer_base_repeat'));
        }

        // 初级验证不通过，用户重新提交
        if ($row->status == -1) {
            $cond['uid'] = $uid;
            $data['status'] = 0;
            $res = $m->updateRow($data, $cond);
            if ($res) {
                return $this->responseSuccess([], __('api.public.success'));
            }
            return $this->setStatusCode(400)->responseNotFound(__('api.public.error'));
        }

    }

    // 获取认证信息
    public function getCerInfo(Request $request)
    {
        $token            = $request->input('api_token', null);
        $uid              = $this->uid;

        $row = UserCertificationModel::where(['uid'=>$uid])->first();

        if ($row) {
            $row = $row->toArray();

            $papers_after = AttachmentModel::getRow($row['papers_after']);
            $papers_before = AttachmentModel::getRow($row['papers_before']);
            $advanced = AttachmentModel::getRow($row['advanced']);
            
            $row['papers_after_path'] = $papers_after['filepath'];
            $row['papers_before_path'] = $papers_before['filepath'];
            $row['advanced_path'] = $advanced['filepath'];

            return $this->responseSuccess($row, __('api.public.success'));

        }
        return $this->setStatusCode(400)->responseNotFound(__('api.public.error'));

    }

    // 高级认证
    public function advancedCertification(Request $request)
    {
        $token            = $request->input('api_token', null);
        $uid              = $this->uid;

        $m = new UserCertificationModel();

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $row = $m->where(['uid'=>$uid])->first();

        // 初级验证未通过
        if ($row->status != 1 ) {
            return $this->setStatusCode(400)->responseNotFound(__('api.member.cer_base_fail'));
        }

        // 高级验证已通过
        if ($row->advanced_status == 1) {
            return $this->responseSuccess([], __('api.member.cer_advanced_repeat'));
        }

        $advanced = (int)$request->input('advanced',0);

        if (!$advanced) {
            return $this->setStatusCode(400)->responseNotFound(__('api.member.cer_advanced_empty'));
        }

        // 高级验证已提交，未审核
        if ($row->advanced_status == 0 && $row->advanced > 0) {
            return $this->setStatusCode(400)->responseNotFound(__('api.member.cer_advanced_repeat2'));
        }

        // 提交
        $row->advanced = $advanced;
        $row->advanced_status = 0;
        $res = $row->save();

        if ($res) {
            return $this->responseSuccess([], __('api.public.success'));
        }
        return $this->setStatusCode(400)->responseNotFound(__('api.public.error'));
    }

    public function getCer($uid){
        $cer = UserCertificationModel::where(['uid'=>$uid])->first();

        if ($cer) {
            $cer = $cer->toArray();
            if ($cer['status'] == 1) {
                // 初级验证通过
                $res['cer_base_status'] = 1;
            }

            if ($cer['status'] == -1) {
                // 初级验证未通过
               $res['cer_base_status'] = -1;
            }

            if ($cer['status'] == 0) {
                // 初级验证未审核
                $res['cer_base_status'] = 0;
            }

            if ($cer['advanced_status'] == 1) {
                // 高级验证通过
                $res['cer_advanced_status'] = 1;
            }

            if ($cer['advanced_status'] == -1) {
                // 高级验证未通过
               $res['cer_advanced_status'] = -1;
            }

            if ($cer['advanced_status'] == 0 && $cer['advanced'] > 0) {
                // 高级验证未审核
                $res['cer_advanced_status'] = 0;
            }

            if ($cer['advanced_status'] == 0 && $cer['advanced'] == 0) {
                // 高级验证未认证
                $res['cer_advanced_status'] = 2;
            }

        }else{
            $res['cer_base_status'] = 2;
            $res['cer_advanced_status'] = 2;
        }
        $res['remark'] = $cer['remark'];

        return $res;
    }

    public function changeName(Request $request)
    {
        $token = $request->input('api_token', null);
        $lang  = $request->input('lang', null);
        $name  = $request->input('name', null);
        $uid   = $this->uid;

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('public.please_login'));
        }

        if(empty($name)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.member.name_empty'));
        }

        $res = $this->getUserModel()->where('id', '=', $uid)->update(['name' => $name]);
        if ($res) {
            return $this->responseSuccess([], __('api.public.success'));
        }
        return $this->setStatusCode(400)->responseNotFound(__('api.public.error'));
    }

    // 登陆选项
    public function changeLoginSafeOption(Request $request)
    {
        $token      = $request->input('api_token', null);
        $lang       = $request->input('lang', null);
        $safe       = (int)$request->input('safe', 0);
        $googleCode = (string)$request->input('googleCode', null);
        $verifyCode  = (int)$request->input('emailCode', null);
        $uid        = $this->uid;

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $user = $this->getUserModel()->getInfo($uid);

        if ($user->google_secret && !$googleCode) {
            return $this->setStatusCode(400)->responseNotFound(__('api.member.google_code_empty'));
        }

        if ($user->google_secret) {
            $checkResult = GoogleVerify::verifyBySecret($user->google_secret, $googleCode, 2);

            // 校验谷歌验证码
            if (!$checkResult) {
                return $this->setStatusCode(403)
                            ->responseNotFound(__('api.member.google_verify_error'));
            }
        }

        //用户输入验证码错误
        if ($user->mobile && !$this->getMobileService()->verifyCode($user, $verifyCode)) 
        {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.verify_code_error'));
        }
        else if (empty($user->mobile) && !$this->getEmailService()->verifyCode($user, $verifyCode)) 
        {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.verify_code_error'));
        }

        $res = $this->getUserModel()->where('id', '=', $uid)->update(['login_option' => $safe]);
        if ($res) {
            return $this->responseSuccess([], __('api.public.success'));
        }
        return $this->setStatusCode(400)->responseNotFound(__('api.public.error'));
    }

    public function changeTradCodeOption(Request $request)
    {
        $token      = $request->input('api_token', null);
        $lang       = $request->input('lang', null);
        $safe       = (int)$request->input('safe', 0);
        $tradeCode = (string)$request->input('tradeCode', null);
        $uid        = $this->uid;

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $user = $this->getUserModel()->getInfo($uid);

        //用户输入交易密码错误
        if (!Hash::check($tradeCode,$user->trade_code)) 
        {
            return $this->setStatusCode(400)->responseNotFound(__('api.member.tradeCode_error'));
        }

        $res = $this->getUserModel()->where('id', '=', $uid)->update(['trade_option' => $safe]);
        if ($res) {
            return $this->responseSuccess([], __('api.public.success'));
        }
        return $this->setStatusCode(400)->responseNotFound(__('api.public.error'));
    }

    public function changeWithdrawalOption(Request $request)
    {
        $token      = $request->input('api_token', null);
        $lang       = $request->input('lang', null);
        $safe       = (int)$request->input('safe', 0);
        $googleCode = (string)$request->input('googleCode', null);
        $verifyCode  = (int)$request->input('emailCode', null);
        $uid        = $this->uid;

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $user = $this->getUserModel()->getInfo($uid);

        if ($user->google_secret && !$googleCode) {
            return $this->setStatusCode(400)->responseNotFound(__('api.member.google_code_empty'));
        }

        if ($user->google_secret) {
            $checkResult = GoogleVerify::verifyBySecret($user->google_secret, $googleCode, 2);

            // 校验谷歌验证码
            if (!$checkResult) {
                return $this->setStatusCode(403)
                            ->responseNotFound(__('api.member.google_verify_error'));
            }
        }

        //用户输入验证码错误
        if (!$this->verifyCode($user, $verifyCode)) 
        {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.verify_code_error'));
        }

        $res = $this->getUserModel()->where('id', '=', $uid)->update(['withdrawal_option' => $safe]);
        if ($res) {
            return $this->responseSuccess([], __('api.public.success'));
        }
        return $this->setStatusCode(400)->responseNotFound(__('api.public.error'));
    }

    // 找回交易密码
    public function retrieveTradcode(Request $request)
    {   
        $token            = $request->input('api_token', null);
        $pwd              = $request->input('pwd', null);
        $verifyCode       = $request->input('verifyCode',null);
        $googleCode       = $request->input('googleCode', null);
        $tradeCode        = (string)$request->input('tradeCode', null);
        $confirmTradeCode = $request->input('confirmTradeCode', null);
        $ip               = $request->getClientIp();
        $uid = $this->uid;
        $user = $this->getUserModel()->getInfo($uid);

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        if(empty($pwd)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.member.password_empty'));
        }

        // 交易登录密码
        $checkResult = Hash::check($pwd,$user->password);
        if (!$checkResult) {
            return $this->setStatusCode(400)->responseNotFound(__('auth.login.password_error'));
        }

        if ($user->google_secret && empty($googleCode)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.member.google_code_empty'));
        }

        //用户输入验证码错误
        if (!$this->verifyCode($user, $verifyCode)) 
        {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.verify_code_error'));
        }

        // 校验谷歌验证码
        if ($user->google_secret) {
            $checkResult = GoogleVerify::verifyBySecret($user->google_secret, $googleCode, 2);
            if (!$checkResult) {
                return $this->setStatusCode(403)
                            ->responseNotFound(__('api.member.google_verify_error'));
            }
        }

        if (empty($tradeCode)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.member.tradeCode_empty'));
        }

        if ($tradeCode != $confirmTradeCode) {
            // 两次输入密码不一致
            return $this->setStatusCode(400)->responseNotFound(__('api.member.tradeCode_discrepant'));
        }

        $tradeCodeHash = Hash::make($tradeCode);
        $result = UserModel::setTradeCode($uid,$tradeCodeHash);
        if ($result) {
            $this->getAccountsService()->writeTradeCodeLog($uid,$ip,1);
            return $this->responseSuccess([], __('api.public.success'));
        }
        return $this->setStatusCode(403)
                            ->responseNotFound(__('api.member.error'));


    }


    public function getUserModel()
    {
        return new UserModel();
    }

    public function getAccountsService()
    {
        return new Accounts();
    }

    public function getEmailService()
    {
        return new Email();
    }

    public function getMobileService()
    {
        return new Mobile();
    }

    public function verifyCode($user, $verifyCode)
    {
        //用户输入验证码错误
        if ($user->mobile && !$this->getMobileService()->verifyCode($user, $verifyCode)) 
        {
            return false;
        }
        
        if (empty($user->mobile) && !$this->getEmailService()->verifyCode($user, $verifyCode)) 
        {
            return false;
        }

        return true;
    }
}
