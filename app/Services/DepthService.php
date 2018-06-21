<?php

namespace App\Services;

use App\Services\BaseService;
use App\Models\TradesOrdersModel;
use App\Models\TradesOrdersDetailsModel;


use DB;

class DepthService extends BaseService
{	
	public $key;

	public function __construct() 
    {
    	parent::__construct();
    	$this->key = 'depth';

    }

	public function publish($data)
	{
		// Redis::rpush('queue','1');
		if (empty($data)) {
			return false;
		}

		// $b = Redis::rpush($this->key,json_encode($data));
		// 
		$result['result'] = 'success';
		if ($data['type'] = 'asks') {
			unset($data['type']);
			$result['return']['asks'][] = $data;
			$result['return']['bids'] = [];
		}

		if ($data['type'] = 'bids') {
			unset($data['type']);
			$result['return']['asks']= [];
			$result['return']['bids'][] = $data;
		}

		$b = Redis::publish($this->key,json_encode($data));

		if ($b) {
			return true;
		}

		return false;
	}
}