<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\ApiController as Controller;

use App\Models\AdminConfigModel;

use Illuminate\Support\Facades\Cache;
use App\Models\LanguageModel;
use App\User;
use App\Services\Email;
use App\Services\Mobile;

class PublicController extends Controller
{
	public function getSafeOption(Request $request)
	{	
		$token = $request->input('api_token', null);
        $user = $this->getUser();
        if(empty($user->id)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }
		$data['config'] = $this->getAdminConfigModel()->getConfigFormatting('safe_option', $user->google_secret);
		$data['is_google'] = $user->google_secret;
		if ($data['config']) {

			return $this->setStatusCode(200)
                        ->responseSuccess($data, 'success');
		}
		return $this->setStatusCode(400)->responseNotFound(__('api.public.empty_data'));
	}

    public function getTradeOption(Request $request)
    {   
        $token = $request->input('api_token', null);
        $user = $this->getUser();
        if(empty($user->id)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }
        $data['config'] = $this->getAdminConfigModel()->getConfigFormatting('trade_option', $user->google_secret);
        if ($data['config']) {

            return $this->setStatusCode(200)
                        ->responseSuccess($data, 'success');
        }
        return $this->setStatusCode(400)->responseNotFound(__('api.public.empty_data'));
    }

    public function getWithdrawalOption(Request $request)
    {   
        $token = $request->input('api_token', null);
        $user = $this->getUser();
        
        if(empty($user->id)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $data['config'] = $this->getAdminConfigModel()->getConfigFormatting('withdrawal_option', $user->google_secret);
        if ($data['config']) {

            return $this->setStatusCode(200)
                        ->responseSuccess($data, 'success');
        }
        return $this->setStatusCode(400)->responseNotFound(__('api.public.empty_data'));
    }


    public function getLang(Request $request)
    {   
        $token = $request->input('api_token', null);
        
        $lang = LanguageModel::select('id', 'name', 'land_code', 'flag','package')->where('enable','1')->get();
        if ($lang) {
            $data = array();
            foreach ($lang->toArray() as $key => $value) {
                # code...env('APP_URL').'/uploads'
                $value['flag'] = env('APP_URL').'/uploads/'.$value['flag'];
                $data[] = $value;
            }
            return $this->setStatusCode(200)
                        ->responseSuccess($data, 'success');
        }
        return $this->setStatusCode(400)->responseNotFound(__('api.public.empty_data'));
    }

	public function sendEmailCode(Request $request)
	{	
		$token = $request->input('api_token', null);
        $user = $this->getUser();
        if(empty($user->id)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $res = $this->getEmailService()->sendCode($user);

        if ($res) {
        	$this->setCacheLock($user->id);

        	return $this->setStatusCode(200)
                        ->responseSuccess([], 'success');
        }

		return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
	}

	public function checkEmailLock(Request $request)
	{
		$token = $request->input('api_token', null);
        $uid = $this->uid;

        $lock = $this->getCacheLock($this->uid);
        if ($lock) {
        	$time = time() - $lock;
        	return $this->setStatusCode(200)
                        ->responseSuccess(60 - $time, 'success');
        }

        return $this->setStatusCode(400)->responseNotFound(__('api.public.error'));
	}

    public function sendVerifyCode(Request $request)
    {
        $token = $request->input('api_token', null);

        if(empty($this->uid)) {
            return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));
        }

        $user = User::find($this->uid);

        if ($user->mobile) {
            $res = $this->getMobileService()->sendCode($user);
        }else{
            $res = $this->getEmailService()->sendCode($user);
        }

        if ($res) {
            $this->setCacheLock($this->uid);

            return $this->setStatusCode(200)
                        ->responseSuccess([], 'success');
        }

        return $this->setStatusCode(400)->responseNotFound(__('api.public.please_login'));

    }

	public function setCacheLock($id = 0)
    {
        return Cache::put('lock:' . $id, time(), 1);
    }

    public function getCacheLock($id)
    {
        return Cache::get('lock:' . $id);
    }

	public function getAdminConfigModel()
	{
		return new AdminConfigModel();
	}

	public function getEmailService()
	{
		return new Email();
	}

    public function getMobileService()
    {
        return new Mobile();
    }
}