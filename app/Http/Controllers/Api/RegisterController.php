<?php

namespace App\Http\Controllers\Api;

use App;
use Mail;
use App\User;
use \Exception;

use App\Http\Proxy\TokenProxy;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use App\Models\VerifyCodeModel;

use App\Http\Controllers\ApiController;

class RegisterController extends ApiController
{

    public function __construct(TokenProxy $proxy)
    {
        $this->proxy = $proxy;
    }

    public function verifications(Request $request){

        $get = $request->only('email');
        $validator = Validator::make($get, [
            'email' => 'required|string|email|max:255|unique:users',
        ]);

        if ($validator->fails()) {
            return $this->setStatusCode(403)
                        ->responseNotFound($validator->messages()->toArray()['email'][0]);
        }

        // 密码不能为空
        if(!isset($get['email']) || empty($get['email'])) {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.register.empty_email'));
        }

        if($this->isUser($get['email'])){
            return $this->setStatusCode(403)
                        ->responseNotFound(__('user.register.repeat_email'));
        }

        $email = $get['email'];
        $code  = (string) rand(10000,99999);
        $id    = $this->_getVerifyCodeModel()->setExpireTime(30)->sendEmailCode($email, $code);

        if($id > 0) {

            $emailContent = __('auth.register.email_verify_content', ['code' => $code]);
            
            try {
                $flag = Mail::send('auth.registerMail',['emailContent'=>$emailContent],function($message) use($email){
                    $message ->to($email)->subject(__('auth.register.email_verify_subject'));
                });
            } catch (Exception $e) {
                return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.register.sending_failure'));
            }
            
            $request->session()->put('verify_code:' . $email, $code);  // 存入session
            return $this->responseSuccess([], __('auth.register.send_email_success'));
            
        } else {

            return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.register.send_email_error'));
        }

    }

    public function verifyCode(Request $request){
        $input = $request->input();

        // 密码不能为空
        if(!isset($input['email']) || empty($input['email'])) {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.register.empty_email'));
        }

        if(!isset($input['code']) || empty($input['code'])) {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.register.empty_code'));
        }

        $email = $input['email'];
        $code = $input['code'];
        // $code  = $request->session()->get('verify_code:' . $email);
        // if($code != $input['code']) {
        //     return $this->setStatusCode(403)
        //                 ->responseNotFound(__('auth.register.verify_code_error'));
        // }
        $result = $this->_getVerifyCodeModel()->checkCode($email, $code);

        if($result > 0) {

            // $request->session()->remove('verify_code:' . $email);  // 存入session
            return $this->responseSuccess(__('auth.register.verify_code_success'));
            
        } else {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.register.verify_code_error'));
        }
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {

        $post = $request->only('email', 'pwd', 'repwd', 'code','name');
        
        if(!isset($post['name']) || !$post['name']){
            return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.register.empty_name'));
        }
        if(!isset($post['email']) || !$post['email']){
            return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.register.empty_email'));
        }

        if(!isset($post['pwd']) || !$post['pwd']){
            return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.register.empty_password'));
        }

        // $pwdResult = check_password($post['pwd']);
        // if($pwdResult['code'] == 0) {
        //     return $this->setStatusCode(403)
        //                 ->responseNotFound($pwdResult['msg']);
        // }

        if($post['pwd'] != $post['repwd']){
            return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.register.confirm_password_error'));
        }

        $code = $post['code'];
        // $code  = $request->session()->get('verify_code:' . $post['email']);
        // if($code != $post['code']) {
        //     return $this->setStatusCode(403)
        //                 ->responseNotFound(__('auth.register.verify_code_error'));
        // }

        if($this->isUser($post['email'])){
            return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.register.repeat_email'));
        }

        $userData = $request->all();

        // 查看邀请用户
        //$inviteUid = (int) Cookie::get('inviteUid');
        $inviteUid = (int) $userData['inviteuid'];

        $user = $this->create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => $userData['pwd'],
                    'activation_code' => $code,
                    'registere_ip' => $request->getClientIp(),
                    'is_email' => 1,            // 默认认证邮箱
                    'invite_uid' => $inviteUid
                ]);
        //Auth::login($user);           //去除了注册自动登录设置
        if(isset($user) && $user->id > 0) {
            $token = $this->proxy->login($post['email'], $post['pwd']);
            //Cookie::make('refreshToken', $token['refresh_token'], 14400);
            $data = [
                        'uid'          => $user->id,
                        'token'        => $token->original['token'],
                        'auth_id'      => $token->original['auth_id'],
                        'expires_in'   => $token->original['expires_in'],
                        'my_persimmon' => Session::getId()
                    ];
            // return response()->json([
            //     'code' => 200,
            //     'message' => 'Success',
            //     'data' => $data
            // ])->cookie('refreshToken', $token['refresh_token'], 14400, null, null, false, true);
            return $this->responseSuccess([
                        'uid'          => $user->id,
                        'token'        => $token->original['token'],
                        'auth_id'      => $token->original['auth_id'],
                        'expires_in'   => $token->original['expires_in'],
                        'my_persimmon' => Session::getId()
                    ], __('api.public.success'));
        } else {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.register.register_error'));
        }
    }

    public function isUser($email){
        $user = User::where('email', '=', $email)->first();
        return empty($user) ? 0: 1;
    }

    protected function create(array $data)
    {
        return User::create([
            'name'            => $data['name'],
            'email'           => $data['email'],
            'password'        => bcrypt($data['password']),
            'activation_code' => (string) $data['activation_code'],
            'invite_uid'      => (int) $data['invite_uid'],
            'registere_ip'    => (string) $data['registere_ip'],
            'is_email'        => 1,
        ]);
    }

    protected function _getVerifyCodeModel() 
    {
        return new VerifyCodeModel();
    }
}
