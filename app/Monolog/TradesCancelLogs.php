<?php

namespace App\Monolog;

use App\Monolog\Monolog;
use DB;

class TradesCancelLogs extends Monolog
{
	public $table = 'trades_cancel_logs';

    public function __construct()
    {
        parent::__construct();
        $this->setKey('cancel');
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

        $logs['id']            = (int) $data['id'];
        $logs['message']       = (string) $data['message'];
        $logs['created_at']    = (string) date('Y-m-d H:i:s');
        $logs['data']          = (array) $data;
        
        return $this->insertLogs($logs);
    }
}
