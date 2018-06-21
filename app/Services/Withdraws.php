<?php

namespace App\Services;


use DB;
use App\Services\BaseService;

use App\Monolog\WithdrawsLogs;

use App\Models\WithdrawsOrdersModel;
use App\Models\AccountsModel;
use App\Models\AccountsDetailsModel;
use App\Coin;

class Withdraws extends BaseService
{
	public function withdraw($id, $status, $remark = ''){

		$accountsModel        = $this->getAccountsModel();
		$accountsDetailsModel = $this->getAccountsDetailsModel();
		$withdrawsOrdersModel = new WithdrawsOrdersModel();

		DB::beginTransaction();
		try{

			// 获取订单
			$order = $withdrawsOrdersModel->lockForUpdate()->find($id);
			if (empty($order)) {
				DB::rollback();
				return ['status'=>false,'message'=>__('api.withdraw.empty_order')];
			}

			if($order->status == 1) {
				DB::rollback();
				return ['status'=>true,'message'=>__('api.withdraw.completed')];
			}

			// 获取账号资金
			$account = $accountsModel->setCurrency($order->currency)
									 ->getLockInfo($order->uid);

			// 对比账号资金
			if (bccomp($account->locked, $order->sum_amount) == -1) {

				DB::rollback();
				// 账号资金异常，提现失败
				$withdrawsOrdersModel->applyById($id, -2, '账号资金异常');
				return ['status'=>false,'message'=>__('api.withdraws.accounts_anomaly')];
			}

			// 审核不通过
			if ($order->status == 3 && $status == 0) 
			{	
				$withdrawsOrdersModel->applyById($id, $status, $remark);
				// 资金退回账号
	        	$result = $this->repeal($order);
				if ($result) {
	    			DB::commit();
	    			return ['status'=>false,'message'=>__('api.withdraw.status_refuse')];
	    		}

	    		DB::rollback();
	    		$withdrawsOrdersModel->applyById($id, -2, '操作：审核不通过 详情：资金回滚失败，请核对用户资金信息');
				return ['status'=>true,'message'=>__('api.withdraw.system_error')];

			} 
			else if ($order->status == 3 && $status == 1) 
			{

				// 清除相应的锁定资金
        		$accountResult = $accountsModel->clearByOrder($order);

        		// 充值记录
    			$accountDetail = $accountsDetailsModel->createDetail([
		                                'uid'            => (int) $order->uid,
		                                'currency'       => (int) $order->currency,
		                                'change_balance' => (float) $order->sum_amount,
		                                'balance'        => (float) $account->balance,
		                                'type'           => -1,
		                                'remark'         => (string) $remark,
		                            ]);

	        	if ($accountResult && $accountDetail) 
	        	{	
	        		$txid = $this->getCoin()->withdraw($order->currencyTo->code, $order->address, (float) $order->amount, (float) $order->currencyTo->fee);

	        		// 更新提现记录表
	        		$withdrawResult = $withdrawsOrdersModel->applyById($id, 1, $remark, $txid);

	        		if (!empty($txid) && $withdrawResult) {
	        			DB::commit();

	        			$order->code = $order->currencyTo->code;
	        			$orderInfo = $order->toArray();
	        			return ['status'=>true,'message'=>__('api.withdraw.status_success'), 'order' => $orderInfo];
	        		}

	        		DB::rollback();
	        		$withdrawsOrdersModel->applyById($id, -2, '操作：审核通过 详情：提现成功，但是锁定资金清除失败，请核对用户资金信息');
	        		return ['status'=>true,'message'=>__('api.withdraw.status_pass')];
	        	}else{
	        		// 提现失败，回滚
	        		$withdrawsOrdersModel->applyById($id, -1, '提现失败');

	        		// 资金退回账号
	        		$result = $accountsModel->repealByOrder($order);
	        		if ($result) {
	        			DB::commit();
	        			return ['status'=>false,'message'=>__('api.withdraw.status_repeal_defeated')];
	        		}

	        		DB::rollback();
	        		$withdrawsOrdersModel->applyById($id, -2, '操作：审核通过 详情：提现失败，并且资金回滚失败，请核对用户资金信息');
	        		return ['status'=>false,'message'=>__('api.withdraws.withdraws_error')];
	        	}
			}
		}catch (\Exception $e){
			DB::rollback();
			return ['status'=>false,'message'=>__('api.withdraw.system_error')];
		}


	}

	/**
	 * 自动充值
	 * @param  [type] $id     [description]
	 * @param  string $remark [description]
	 * @return [type]         [description]
	 */
	public function autoWithdraw($id, $remark = ''){

		$accountsModel        = $this->getAccountsModel();
		$accountsDetailsModel = $this->getAccountsDetailsModel();
		$withdrawsOrdersModel = new WithdrawsOrdersModel();

		DB::beginTransaction();
		try{

			// 获取订单
			$order = $withdrawsOrdersModel->lockForUpdate()->find($id);
			if (empty($order)) {
				DB::rollback();
				return $this->error(__('api.withdraw.empty_order'));
			}

			// 获取账号资金
			$account = $accountsModel->setCurrency($order->currency)
									  ->getLockInfo($order->uid);

			// 对比账号资金
			if ($account->locked < $order->sum_amount) {
				DB::rollback();
				return $this->error(__('api.withdraw.accounts_anomaly'));
			}

			// 审核通过
			if ($order->status == 3 || $order->status == 2) {


				// 清除相应的锁定资金
        		$accountResult = $accountsModel->clearByOrder($order);

        		// 账户详细
    			$accountDetail  = $accountsDetailsModel->createDetail([
		                                'uid'            => (int) $order->uid,
		                                'currency'       => (int) $order->currency,
		                                'change_balance' => (float) $order->sum_amount,
		                                'balance'        => (float) $account->balance,
		                                'type'           => -1,
		                                'remark'         => (string) $remark,
		                            ]);

	        	// 提现成功
	        	if($accountResult && $accountDetail) {

	        		// 开始提现
	        		$txid = $this->getCoin()->withdraw($order->currencyTo->code, $order->address, (float) $order->amount, (float) $order->currencyTo->fee);

        			// 更新提现记录表
        			$withdrawResult = $withdrawsOrdersModel->applyById($id, 1, $remark, $txid);

        			if(!empty($txid) && $withdrawResult) {

        				DB::commit();
						$orderInfo = $order->toArray();
						return $this->success($orderInfo);

        			} else {

        				DB::rollback();
        				$withdrawsOrdersModel->applyById($id, -1, __('api.withdraw.withdraw_failure_txid'));
        				return $this->error(__('api.withdraw.withdraw_failure_txid'));
        			}

	        	}else{

	        		DB::rollback();
	        		$withdrawsOrdersModel->applyById($id, -1, __('api.withdraw.withdraw_failure_data'));
	        		return $this->error(__('api.withdraw.withdraw_failure_data'));
	        	}
			} else {
				return $this->error(__('api.withdraw.withdraw_completed'));
			}
		}catch (\Exception $e){
			DB::rollback();
			return $this->error(__('api.withdraw.system_error'));
		}


	}

	// 资金退回账号
	public function repeal($order)
	{	
		$AccountsModel = new AccountsModel();
		return $AccountsModel->repealByOrder($order);
	}

	private function getWithdrawsLogs()
	{	
		return new WithdrawsLogs();
	}

	private function getAccountsModel()
	{	
		return new AccountsModel();
	}

	private function getAccountsDetailsModel()
	{	
		return new AccountsDetailsModel();
	}

	private function getCoin()
    {
        $server = env('WALLET_HOST') . ':' . env('WALLET_PORT');
        return new Coin($server);
    }
}