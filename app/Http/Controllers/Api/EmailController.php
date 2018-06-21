<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

use App\Http\Controllers\ApiController as Controller;
use App\Models\UserModel;
use App\Models\VerifyCodeModel;
use App\Models\WithdrawsOrdersModel;
use App\Services\Authenticate;
use App\Services\Withdraw;
use User,Mail;

class EmailController extends Controller
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

    public function send(Request $request){

        $uid = $this->uid;

        if(empty($uid)){
            return $this->setStatusCode(403)
                        ->responseNotFound(__('api.public.please_login'));
        }

        $user  = $this->getUser();
        $email = $user->email;
        $code  = (string) rand(10000,99999);
        $id    = $this->_getVerifyCodeModel()->setExpireTime(30)->sendEmailCode($email, $code);

        if($id > 0) {
            $emailContent = __('auth.register.email_verify_content', ['code' => $code]);
            
            $flag = Mail::send('auth.registerMail',['emailContent'=>$emailContent],function($message) use($email){
                $message ->to($email)->subject(__('auth.register.email_verify_subject'));
            });
            return $this->responseSuccess([], __('auth.register.send_email_success'));
        } else {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.register.send_email_error'));
        }
        
    }

    public function verify(Request $request){
        $input = $request->input();
        $uid = $this->uid;

        if(!isset($input['emailCode']) || empty($input['emailCode'])) {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.register.empty_code'));
        }

        if(empty($uid)){
            return $this->setStatusCode(403)
                        ->responseNotFound(__('api.public.please_login'));
        }

        $email  = $this->getUser()->email;
        $code   = $input['emailCode'];
        $result = $this->_getVerifyCodeModel()->checkCode($email, $code);

        if($result > 0) {
            $this->getAuthService()->setUser($this->getUser())->isEmail();
            return $this->responseSuccess(__('auth.register.verify_code_success'));
        } else {
            return $this->setStatusCode(403)
                        ->responseNotFound(__('auth.register.verify_code_error'));
        }
    }

    public function sendWithdrawConfirm(Request $request){
        $id = $request->input('id', null);

        if(empty($id)) {
            return $this->setStatusCode(403)->responseNotFound(__('api.public.illegal_operation'));
        }

        $order = WithdrawsOrdersModel::find($id);
        if(empty($order)) {
            return $this->setStatusCode(403)->responseNotFound(__('api.public.empty_data'));
        }

        $seconds = $this->getCountDown('withdraw_email:' . $id);
        if($seconds > 0) {
            return $this->setStatusCode(403)->responseNotFound(__('api.public.send_regain', ['seconds' => $seconds]));
        }

        $user = $this->getUser();
        if(empty($user->email)){
            return $this->setStatusCode(403)->responseNotFound(__('api.member.email_empty'));
        }

        $seconds = $this->setCountDown('withdraw_email:' . $id, 1);
        $mailResult = (new Withdraw())->mailTo($id);
        return $this->responseSuccess([], __('api.public.send_successed'));
    }

    public function isUser($email){
        $user = User::where('email', '=', $email)->first();
        return empty($user) ? 0: 1;
    }

    public function getUserModel()
    {
        return new UserModel();
    }

    public function getAuthService()
    {
        return new Authenticate();
    }

    protected function _getVerifyCodeModel() 
    {
        return new VerifyCodeModel();
    }
}
