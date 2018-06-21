<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\AdminController as Controller;
use App\Models\WithdrawsOrdersModel;
use App\Models\DepositsOrdersModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;

use App\Models\CurrencyModel;
use App\Models\AccountsModel;

use Carbon\Carbon;

class StatisticsController extends Controller{

	public $request;
	public $time;
	public $action;

	public function index()
	{
	    return Admin::content(function (Content $content) {


	        $content->header('资金总览');
	        $content->description('各资金统计');

	        // 美元总量
	        $data = AccountsModel::where(['currency'=> 3])->first(
	        	array(
			        \DB::raw('SUM(balance) as balance'),
			        \DB::raw('SUM(locked) as locked')
			    )
	        )->toArray();

	        // 各虚拟币总量
	        $currencies = CurrencyModel::where(['is_virtual'=>1])->get()->toArray();
			$data['currencies'] = [];
	        foreach ($currencies as $key => $val) {
	        	$row = AccountsModel::where(['currency'=> $val['id']])->first(
			        	array(
					        \DB::raw('SUM(balance) as balance'),
					        \DB::raw('SUM(locked) as locked')
					    )
			        )->toArray();
	        	$row['currency'] = $val['name'];

	        	$data['currencies'][] = $row;

	        }

	        // 美元充值/提现概览
	        $data['withdraws'] = WithdrawsOrdersModel::where(['currency'=>3])->sum('sum_amount');

	        $startTime = date('Y-m-d H:i:s',time()-86400);
	        $endTime = date('Y-m-d H:i:s',time());
	        $data['withdraws24'] = WithdrawsOrdersModel::where(['currency'=>3])->whereBetween('created_at',[$startTime,$endTime])->sum('sum_amount');

			// 填充页面body部分
	        $content->body(view('admin.statis_all', $data));
	        
	    });
	   
	}

	public function withdraws(Request $request)
	{	
		$this->request = $request;
		$this->action = 'withdraws';
		return Admin::content(function (Content $content) {

	        $content->header('提现统计');

	        $request = \Request::all();
	        $currency  = $request['currency'] ?? 0;
            $startDate = $request['startTime'] ?? date('Y-m-d 00:00:00', strtotime('-15 days'));
            $endDate = $request['endTime'] ?? Carbon::parse('today')->toDateTimeString();
            $startTime = strtotime($startDate);
            $endTime = strtotime($endDate);

            $lineData = array();
            $dayArr = array();
            $valArr = array();
            for ($i=$startTime; $i <= $endTime; $i = $i + 86400 ) { 
                //$time = mktime (0,0,0,date("m"),date("d")-$i,date("Y"));
                $key = date('Ymd',$i);
                $valArr[$key] = 0;
                $dayArr[] = date('m-d',$i);
            }

            //获取所有充值币 并按货币进行分类
            if($currency > 0){
            	$ordersData = WithdrawsOrdersModel::where('currency',$currency)->where('created_at','>=',$startDate)->where('created_at','<=',date('Y-m-d 00:00:00', $endTime+86400))->get();   
            } else {
            	$ordersData = WithdrawsOrdersModel::where('created_at','>=',$startDate)->where('created_at','<=',date('Y-m-d 00:00:00', $endTime+86400))->get();   
            }
             
            $ordersGroup = array();
            foreach ($ordersData->toArray() as  $value) {
            	$currKey = $value['currency'];
                $ordersGroup[$currKey][] = $value;
            }
            //
            
            $currencyArr = $this->getCurrency();
            $lineData = array();
            foreach ($currencyArr as $value) {
            	if($currency > 0 && $value['id'] != $currency){
            		 continue;
            	}

            	$line['name'] =  $value['text'];
            	$listKey = $value['id'];
            	if(isset($ordersGroup[$listKey])){
            		$line['data'] =  $this->withdrawFormat($ordersGroup[$listKey],$valArr);
            	} else {
            		$line['data'] =  array_values($valArr);
            	}
            	
            	$lineData[] = $line;
            }
            $data['startTime'] = date("Y-m-d H:i:s",$startTime);
            $data['endTime'] = date("Y-m-d H:i:s",$endTime);
            $data['currencyList'] = $currencyArr;
            $data['currency'] = $currency;
            $data['lineData']['title'] = json_encode($dayArr); 
            $data['lineData']['val'] =  json_encode($lineData); 
            $content->body(view('admin.chart.withdraws',$data));
	    });	        
	    
	}


	function withdrawFormat($data,$lineData){
		foreach ($data as  $value) {
            $dateTime = strtotime($value['created_at']);
            $dateKey = date('Ymd',$dateTime);
            $lineData[$dateKey] = (float) bcadd($lineData[$dateKey],$value['sum_amount'],6);
        }
        return array_values($lineData);
	}


	

	public function deposits(Request $request)
	{	
		$this->request = $request;
		$this->action = 'withdraws';
		return Admin::content(function (Content $content) {

	        $content->header('充值统计');

	        $request = \Request::all();
	        $currency  = isset($request['currency'])   ? intval($request['currency']) :0;
            $startDate = $request['startTime'] ?? date('Y-m-d 00:00:00', strtotime('-15 days'));
            $endDate = $request['endTime'] ?? Carbon::parse('today')->toDateTimeString();
            $startTime = strtotime($startDate);
            $endTime = strtotime($endDate);

            $lineData = array();
            $dayArr = array();
            $valArr = array();
            
            for ($i=$startTime; $i <= $endTime; $i = $i + 86400 ) { 
                //$time = mktime (0,0,0,date("m"),date("d")-$i,date("Y"));
                $key = date('Ymd',$i);
                $valArr[$key] = 0;
                $dayArr[] = date('m-d',$i);
            }

            //获取所有充值币 并按货币进行分类
            if($currency > 0){
            	$ordersData = DepositsOrdersModel::where('currency',$currency)->where('created_at','>=',$startDate)->where('created_at','<=',date('Y-m-d 00:00:00', $endTime+86400))->get();   
            } else {
            	$ordersData = DepositsOrdersModel::where('created_at','>=',$startDate)->where('created_at','<=',date('Y-m-d 00:00:00', $endTime+86400))->get();   
            }
            $ordersGroup = array();
            foreach ($ordersData->toArray() as  $value) {
            	$currKey = $value['currency'];
                $ordersGroup[$currKey][] = $value;
            }
            //
            
            $currencyArr = $this->getCurrency();
            $lineData = array();
            foreach ($currencyArr as $value) {
            	if($currency > 0 && $value['id'] != $currency){
            		 continue;
            	}

            	$line['name'] =  $value['text'];
            	$listKey = $value['id'];
            	if(isset($ordersGroup[$listKey])){
            		$line['data'] =  $this->depositsFormat($ordersGroup[$listKey],$valArr);
            	} else {
            		$line['data'] =  array_values($valArr);
            	}
            	
            	$lineData[] = $line;
            }
            $data['startTime'] = date("Y-m-d H:i:s",$startTime);
            $data['endTime'] = date("Y-m-d H:i:s",$endTime);
            $data['currencyList'] = $currencyArr;
            $data['currency'] = $currency;
            $data['lineData']['title'] = json_encode($dayArr); 
            $data['lineData']['val'] =  json_encode($lineData); 
            $content->body(view('admin.chart.tixian',$data));
	    });	        
	    
	}

	function depositsFormat($data,$lineData){
		foreach ($data as  $value) {
            $dateTime = strtotime($value['created_at']);
            $dateKey = date('Ymd',$dateTime);
            $lineData[$dateKey] = (float) bcadd($lineData[$dateKey],$value['amount'],6);
        }
        return array_values($lineData);
	}

	public function getDates($time, $data)
	{
		$stimestamp = strtotime($time[0]);
		$etimestamp = strtotime($time[1]);
		// 计算日期段内有多少天
		$days = ($etimestamp-$stimestamp)/86400;
		// 保存每天日期
		$date = array();
		for($i=0; $i<$days; $i++){
			$date[] = date('Y-m-d', $stimestamp+(86400*$i));
		}

		$data = array_column($data, 'sum_amount', 'date');
		$list = [];
		$title = [];
		$value = [];
		foreach ($date as $key => $val) {
			$row    = [];
			$title[]  = $val;
			$value[]  = $data[$val] ?? 0;
			//$list[] = $row;
		}
		$list['title'] = json_encode($title);
		$list['val'] = json_encode($value);
		return $list;
	}

	protected function form()
    {
        Form::extend('map', Form\Field\Map::class);
        Form::extend('editor', Form\Field\Editor::class);
        return Admin::form(WithdrawsOrdersModel::class, function (Form $form) {

            $form->disableDeletion();
            $form->disableReset();
            $form->display('id', 'ID');
            $form->datetimeRange('startTime', 'endTime', '请选择时间');
            $form->setAction($this->action);

            $form->select('currency', '币种')->options('/admin/statistics/getCurrency');

            $form->tools(function (Form\Tools $tools) {
			    // 去掉返回按钮
			    $tools->disableBackButton();

			    // 去掉跳转列表按钮
			    $tools->disableListButton();
			});
        });
    }

    public function getCurrency()
    {
    	return CurrencyModel::select(['id','name as text'])->where(['is_virtual'=>1])->get()->toArray();
    }
}