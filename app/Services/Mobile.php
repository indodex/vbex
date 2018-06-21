<?php

namespace App\Services;

use App\Services\BaseService;
use App\Models\UserModel;
use App\Models\VerifyCodeModel;
use User,Mail;
use Illuminate\Support\Facades\Cache;

use DB;

class Mobile extends BaseService
{
	public function sendCode($user){
        $phone = $user->mobile;

        if(empty($phone)) {
            return false;
        }
        
        if($this->codeCreate($phone)){
            $this->setCodeLocked($phone);
            return true;
        }

        $this->setCodeLocked($phone);
        return false;

    }

    public function codeCreate($phone, $sms_no = 'SMS_71905363'){
        $num =(string) rand(1000,9999); // 组装参数
        
        $retSend = $this->send($phone, array('code' => $num, 'product' => 'HAC'), $sms_no);
        if($retSend){
        	$this->_getVerifyCodeModel()->setExpireTime(30)->sendEmailCode($phone, $num, 2);
            $retData = json_decode($retSend);
            return intval($retData->code) == 0 ? true : false;
        } else {
            return false;
        }
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

    public function verifyCode($user,$code){

        if(empty($code)) {
            return false;
        }

        if(empty($user->id)){
            return false;
        }

        $result = $this->_getVerifyCodeModel()->checkCode($user->mobile, $code);

        if($result > 0) {
            return true;
        } 
        return false;
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

    protected function _getVerifyCodeModel() 
    {
        return new VerifyCodeModel();
    }
}