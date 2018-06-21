<?php

namespace App\Monolog;

use App\Monolog\Monolog;
use DB;

class WithdrawsLogs extends Monolog
{
	public $table = 'Withdraws_Logs';

    public function __construct()
    {
        parent::__construct();
        $this->setKey('buy');
    }

    public function insertLogs($insertData)
    {
    	if(empty($insertData)) {
            return false;
        }
    	return $this->info($insertData);
    }

    public function addLogs($data)
    {   
        if(empty($data)) {
            return false;
        }

        $logs['order_id']   = (string) $data['order_id'];
        $logs['created_at'] = (string) date('Y-m-d H:i:s');
        $logs['data']       = (array) $data;

        return $this->insertLogs($logs);
    }
}
