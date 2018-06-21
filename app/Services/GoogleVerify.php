<?php

namespace App\Services;

use App\Services\BaseService;
use App\Models\UserModel;

use App\Lib\GoogleAuthenticator;

use DB;

class GoogleVerify extends BaseService
{	
	public static function create($user)
	{
		$goole = new GoogleAuthenticator();
        $data['googleSecret'] = $goole->createSecret();

        $data['qrCodeUrl'] = $goole->getQRCodeGoogleUrl('hash:' . $user->email, $data['googleSecret']);
	
        return $data;
	}

	// 直接通过秘钥校验
	public static function verifyBySecret($googleSecret, $oneCode, $offset = 2)
	{
		$goole = new GoogleAuthenticator();
        return $goole->verifyCode($googleSecret, $oneCode, $offset);
	}

	// 通过uid获取秘钥校验
	public static function verifyByUid($uid, $oneCode, $offset = 2)
	{	
		$UserModel = new UserModel();
		$user = $UserModel->getInfo($uid);
        $goole = new GoogleAuthenticator();
        return $goole->verifyCode($user->google_secret, $oneCode, $offset);
	}


}