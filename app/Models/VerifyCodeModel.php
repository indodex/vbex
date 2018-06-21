<?php

namespace App\Models;

use App\Models\BaseModel as Model;

class VerifyCodeModel extends Model
{
    protected $table = 'verify_code';

    protected $expire_at;

    protected $primaryKey = "id";        //指定主键

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid', 'account', 'type', 'code', 'expire_at', 'status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * 创建数据
     * @param  [type] $data [description]
     * @return [type] [description]
     */
    public function createCode($data)
    {
        return $this->insert([
				'uid'       => $data['uid'],
				'account'   => $data['account'],
				'type'      => $data['type'],
				'code'      => $data['code'],
				'expire_at' => $data['expire_at'],
				'status'    => $data['status'],
        ]);
    }

    /**
     * 设置有效时间
     * @param  [type] $minute [description]
     * @return [type] [description]
     */
    public function setExpireTime($minute) 
    {
    	$seconds = $minute * 60 + time();
    	$this->expire_at = $seconds;

    	return $this;
    }

    /**
     * 获取有效时间
     * @return [type] [description]
     */
    public function getExpireTime() 
    {
    	return $this->expire_at;
    }

    /**
     * 邮箱验证码
     * @param  [type] $email [description]
     * @param  [type] $code  [description]
     * @return [type]        [description]
     */
    public function sendEmailCode($account, $code,$type = 1) {
    	return $this->createCode([
			'uid'       => 0,
			'account'   => $account,
			'code'      => $code,
			'type'      => $type,
			'expire_at' => $this->getExpireTime(),
			'status'    => 0,
    	]);
    }

    /**
     * 手机验证码
     * @param  [type] $phone [description]
     * @param  [type] $code  [description]
     * @return [type]        [description]
     */
    public function sendPhoneCode($phone, $code) {
    	return $this->createCode([
			'uid'       => 0,
			'account'   => $phone,
			'code'      => $code,
			'type'      => 2,
			'expire_at' => $this->getExpireTime(),
			'status'    => 0,
    	]);
    }

    /**
     * 验证码验证
     * @param  [type] $account [description]
     * @param  [type] $code    [description]
     * @return [type]          [description]
     */
    public function checkCode($account, $code) 
    {
    	if(empty($account) || empty($code)) {
    		return false;
    	}

		$cond['account'] = $account;
		$cond['code']    = $code;
		$cond['status']  = 0;
    	$data = $this->where($cond)->first();
        
    	if(empty($data)) {
    		return false;
    	}
        $data = $data->toArray();

    	$limitTime = $data['expire_at'] - time();

    	if($limitTime > 0) {
            $this->where($cond)->update(['status' => 1]);
    		return true;
    	} else {
    		return false;
    	}
    }

}
