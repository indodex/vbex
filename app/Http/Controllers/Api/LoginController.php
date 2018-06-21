<?php

namespace App\Http\Controllers\Api;

use App;
use App\User;
use App\Http\Proxy\TokenProxy;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use App\Http\Controllers\ApiController;

use Hash;
use App\Services\Email;
use App\Services\Mobile;
use Illuminate\Support\Facades\Cache;
use App\Services\GoogleVerify;

use App\Services\Accounts;

class LoginController extends ApiController
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

    use AuthenticatesUsers;

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
    public function __construct(TokenProxy $proxy)
    {
        $this->middleware('guest')->except('logout');
        $this->proxy = $proxy;
    }

    public function index(Request $request)
    {
        $post = $request->all();
        
        if(strlen(trim($post['email'])) < 1){
            return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.login.empty_email'));
        }
        if(strlen(trim($post['password'])) < 1){
            return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.login.password_error'));
        }

        if (Auth::attempt(['email' => $post['email'], 'password' => $post['password']])) {
            $user = Auth::user();
            return $this->responseSuccess([
                        'user'         => $user,
                        'goUrl'        => url('/'),
                        'my_persimmon' => Session::getId()
                    ], __('auth.login.login_success'));
        } else {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.login.login_fail'));
        }
    }

    public function loginSafeOption(Request $request)
    {
        $account = (string)$request->input('account',null);
        $pwd     = (string)$request->input('password',null);

        if (empty($account)) {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.login.empty_user'));
        }

        if (empty($pwd)) {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.login.password_error'));
        }

        // 登录次数检查
        $logins = $this->getCountLogins($request);
        if($logins >= 5) {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.login.too_many_attempts'));
        }

        $user = User::where($this->getCond($account))->first();

        if (!$user) {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('api.member.member_empty_second'));
        }

        if($user->is_lock == 1) {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.login.user_locked'));
        }

        if ($user && Hash::check($pwd,$user->password)) {

            $lock = $this->getCacheLock($user->id);
            $time = 0;
            if ($lock) {
                $time = time() - $lock;
            }            
            $this->cleanCountLogins($request);
            $is_mobile = empty($user->mobile) ? 0 : 1;
            return $this->responseSuccess(['login_option'=>$user->login_option,'time'=>$time,'is_mobile'=>$is_mobile], __('api.public.success'));
        }

        $this->setCountLogins($request);
        return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.login.empty_user'));
    }

    public function sendVerifyCode(Request $request)
    {
        $post    = $request->all();
        
        $account = (string)$request->input('account',null);
        $pwd     = (string)$request->input('password',null);

        if (empty($account)) {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.login.empty_user'));
        }

        if (empty($pwd)) {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.login.password_error'));
        }

        $user = User::where($this->getCond($account))->first();

        if ($user && Hash::check($pwd,$user->password)) {
            if ($user->mobile) {
                $res = $this->getMobileService()->sendCode($user);
            }else{
                $res = $this->getEmailService()->sendCode($user);
            }

            if ($res) {
                $this->setCacheLock($user->id);

                return $this->setStatusCode(200)
                            ->responseSuccess([], 'success');
            }

            return $this->setStatusCode(400)->responseNotFound(__('api.public.success'));
        }
        return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.login.empty_user'));
    }

    public function setCacheLock($id = 0)
    {
        return Cache::put('lock:' . $id, time(), 1);
    }

    public function getCacheLock($id)
    {
        return Cache::get('lock:' . $id);
    }

    public function login(Request $request)
    {   
        // $post    = $request->all();
        $account    = (string)$request->input('account',null);
        $pwd        = (string)$request->input('password',null);
        $verifyCode  = (string)$request->input('emailCode',null);
        $googleCode = (string)$request->input('googleCode',null);
        
        if (empty($account)) {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.login.empty_user'));
        }

        $user = User::where($this->getCond($account))->first();

        if ($user && Hash::check($pwd,$user->password)) {

            if (in_array($user->login_option, [1,3])) {
                // 谷歌验证
                $checkResult = GoogleVerify::verifyBySecret($user->google_secret, $googleCode, 2);
                if (!$checkResult) {
                    return $this->setStatusCode(421)
                                ->responseNotFound(__('api.member.google_verify_error'));
                }
            }
            if (in_array($user->login_option, [2,3])) {
                // 邮箱验证
                // var_dump($this->getMobileService()->verifyCode($user, $verifyCode));exit;
                if ($user->mobile && !$this->getMobileService()->verifyCode($user, $verifyCode)) {
                    return $this->setStatusCode(421)->responseNotFound(__('api.public.email_verify_code_error'));
                }else if (empty($user->mobile) && !$this->getEmailService()->verifyCode($user, $verifyCode)) {
                    return $this->setStatusCode(421)->responseNotFound(__('api.public.email_verify_code_error'));
                }
            }
        }
        return $this->proxy->login($user->email, request('password'),$user->id);
    }

    public function logout()
    {
        return $this->proxy->logout();
    }

    public function refresh()
    {
        return $this->proxy->refresh();
    }

    public function getEmailService()
    {
        return new Email();
    }

    public function getMobileService()
    {
        return new Mobile();
    }

    public function getCond($account){
        $checkmail="/\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/";
        $cond = [];
        if (preg_match($checkmail,$account)) {
            $cond['email'] = trim($account);
        }else{
            $cond['mobile'] = trim($account);
        }

        return $cond;
    }


    /**
     * 记录登录次数
     * @param Request $request [description]
     */
    private function setCountLogins(Request $request)
    {
        $account = $request->input('account');
        $limit = (int) $this->getCountLogins($request);
        $limit = $limit + 1;
        Cache::put('login_limit@account:' . $account, $limit, 60);
        return $limit;
    }

    private function getCountLogins(Request $request) {
        $account = $request->input('account');
        return Cache::get('login_limit@account:' . $account);
    }

    private function cleanCountLogins(Request $request) {
        $account = $request->input('account');
        Cache::forget('login_limit@account:' . $account);
    }

}
