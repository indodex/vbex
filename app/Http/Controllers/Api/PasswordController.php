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
use App\Models\VerifyCodeModel;
use Illuminate\Support\Facades\Cache;
use Hash;

class PasswordController extends Controller
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

    public function resetPassword(Request $request)
    {
        $token           = $request->input('api_token', null);
        $curPassword     = $request->input('oldPassword', null);
        $password        = $request->input('password', null);
        $code            = $request->input('code', null);
        $confirmPassword = $request->input('confirmPassword', null);
        $oneCode         = $request->input('oneCode', null);
        $ip              = $request->getClientIp();
        $uid             = $this->uid;

        if(empty($uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        if(empty($curPassword)) {
            return $this->setStatusCode(400)->responseNotFound(__('auth.register.empty_password'));
        }

        if(empty($password)) {
            return $this->setStatusCode(400)->responseNotFound(__('auth.login.empty_new_password'));
        }

        // 检查密码
        if($password != $confirmPassword) {
            return $this->setStatusCode(400)->responseNotFound(__('auth.register.confirm_password_error'));
        }

        $user = $this->getUserModel()->getInfo($uid);
        $authService = $this->getAuthService()->setUser($user);

        // 验证当前密码
        if(!$authService->checkPassword($curPassword)){
            return $this->setStatusCode(400)->responseNotFound(__('auth.register.password_verify_error'));
        }

        if($user->mobile != '') {
            if($code != $this->getCacheVerifyCode($user->mobile)){
                return $this->setStatusCode(400)->responseNotFound(__('api.public.verify_code_error'));
            }
        } else {
            $check = $this->getVerifyCodeModel()->checkCode($user->email, $code);
            if(!$check) {
                return $this->setStatusCode(400)->responseNotFound(__('api.public.verify_code_error'));
            }
        }
        

        if (!empty($user->google_secret)) {


            // 未绑定谷歌验证
            // return $this->setStatusCode(400)->responseNotFound(__('api.member.google_secret_null'));

            $result = GoogleVerify::verifyBySecret($user->google_secret, $oneCode, 0);
            if (!$result) {
                // 谷歌验证失败
                return $this->setStatusCode(403)
                            ->responseNotFound(__('api.member.google_verify_error'));
            }
        }

        

        $result = $authService->resetPassword($password);
        if($result['status'] == 1) {
            $this->getAccountsService()->writeTradeCodeLog($uid,$ip,4);
            return $this->setStatusCode(200)->responseSuccess($result['data'], __('api.public.success'));
        } else {
            return $this->setStatusCode(404)->responseNotFound(__('auth.register.password_verify_error'));
        }

    }

    public function getCacheVerifyCode($phone)
    {
        return Cache::get('verify_code:' . $phone);
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

    protected function getVerifyCodeModel() 
    {
        return new VerifyCodeModel();
    }
}
