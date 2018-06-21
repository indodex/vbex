<?php

namespace App\Monolog;

use App\Monolog\Monolog;
use DB;

class TradesSellLogs extends Monolog
{
	public $table = 'trades_sell_logs';

    public function __construct()
    {
        parent::__construct();
        $this->setKey('sell');
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
        $logs['uid']           = (int) $data['uid'];
        $logs['buy_currency']  = (string) $data['buy_currency'];
        $logs['sell_currency'] = (string) $data['sell_currency'];
        $logs['message']       = (string) $data['message'];
        $logs['created_at']    = (string) date('Y-m-d H:i:s');
        $logs['data']          = (array) $data['data'];

        return $this->insertLogs($logs);
    }
}
