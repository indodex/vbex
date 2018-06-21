<?php
namespace App\Services;

use App\Services\BaseService;
use App\Models\UserModel;

use DB;
use Hash;

class Authenticate extends BaseService
{

	public function checkPassword($password)
	{
		$user = $this->getUser();
		if(!Hash::check($password, $user->password)) {
			return false;
		} else {
			return true;
		}
	}

	public function checkTradeCode($tradeCode)
	{
		$user = $this->getUser();
		if(!Hash::check($tradeCode, $user->trade_code)) {
			return false;
		} else {
			return true;
		}
	}

	public function resetPassword($password)
	{
		$user = $this->getUser();
		$result = $this->getUserModel()->updateById($user->id, ['password' => Hash::make($password)]);
		if($result) {
			return $this->success($result);
		} else {
			return $this->error(__('auth.login.edit_passowrd_failed'));
		}
	}

	public function changeMobile($mobile)
	{
		$user = $this->getUser();
		$result = $this->getUserModel()->updateById($user->id, ['mobile' => $mobile]);
		if($result) {
			return $this->success($result);
		} else {
			return $this->error(__('auth.login.edit_passowrd_failed'));
		}
	}

	public function isEmail()
	{
		$user = $this->getUser();
		$result = $this->getUserModel()->updateById($user->id, ['is_email' => 1]);
		if($result) {
			return $this->success($result);
		} else {
			return $this->error(__('auth.login.edit_passowrd_failed'));
		}
	}

	private function getUserModel()
	{
		return new UserModel();
	}
}