<?php

namespace App\Services;

use App\Services\BaseService;
use App\Models\UserModel;
use App\Models\VerifyCodeModel;
use User,Mail;

use DB;

class Email extends BaseService
{
	public function sendCode($user){

        if(empty($user->id)){
            return false;
        }

        $email = $user->email;
        $code  = (string) rand(10000,99999);
        $id    = $this->_getVerifyCodeModel()->setExpireTime(30)->sendEmailCode($email, $code);

        if($id > 0) {
            $emailContent = __('auth.register.email_verify_content', ['code' => $code]);
            
            $flag = Mail::send('auth.registerMail',['emailContent'=>$emailContent],function($message) use($email){
                $message ->to($email)->subject(__('auth.register.email_verify_subject'));
            });
            return true;
        }
        return false; 
    }

    public function verifyCode($user,$code){

        if(empty($code)) {
            return false;
        }

        if(empty($user->id)){
            return false;
        }

        $result = $this->_getVerifyCodeModel()->checkCode($user->email, $code);

        if($result > 0) {
            return true;
        } 
        return false;
    }


	public function getUserModel()
    {
        return new UserModel();
    }

	protected function _getVerifyCodeModel() 
    {
        return new VerifyCodeModel();
    }
}