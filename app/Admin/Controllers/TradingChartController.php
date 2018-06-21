<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\AdminController as Controller;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\InfoBox;
use App\User;
use App\Models\UserCertificationModel as Cer;
use App\Models\CurrencyModel;
use App\Models\TradesCurrenciesModel;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\WithdrawsOrdersModel;
use App\Models\DepositsOrdersModel;
use App\Models\AccountsModel;
use App\Models\TradesOrdersDetailsModel;

class TradingChartController extends Controller
{



    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('Dashboard');
            $content->description('');
            $data['userCount'] = User::where('is_delete',0)->count();
            $data['cerCount'] = Cer::where('status',0)->orWhere('advanced_status',0)->count();
            $data['currencyCount'] = CurrencyModel::count();
            $data['tradesCount'] = TradesCurrenciesModel::count();
            $content->row(function ($row)  use($data){
                $row->column(3, new InfoBox('用户', 'users', 'aqua', '/admin/user', $data['userCount']));
                $row->column(3, new InfoBox('认证申请', 'file', 'green', '/admin/cer', $data['cerCount']));
                $row->column(3, new InfoBox('币种管理', 'book', 'yellow', '/admin/currency', $data['currencyCount']));
                $row->column(3, new InfoBox('交易市场', 'shopping-cart', 'red', '/admin/markets', $data['tradesCount']));
            });
            // $content->row(function ($row)  {
            //     $row->column(3, new InfoBox('用户', 'users', 'aqua', '/admin/user', 100));
            //     $row->column(3, new InfoBox('充值', 'shopping-cart', 'green', '/admin/apply', 200));
            //     $row->column(3, new InfoBox('奖励', 'book', 'yellow', '/admin/rewards', 400));
            //     $row->column(3, new InfoBox('提现', 'file', 'red', '/admin/withdraw', 300));
            // });
        });
    }

    public function trading(Request $request)
    {   
        $this->request = $request;
        $this->action = 'withdraws';
        return Admin::content(function (Content $content) {

            $content->header('交易额度统计');

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
                $ordersData = TradesOrdersDetailsModel::where('sell_uid',$currency)->where('created_at','>=',$startDate)->where('created_at','<=',date('Y-m-d 00:00:00', $endTime+86400))->get();   
            } else {
                $ordersData = TradesOrdersDetailsModel::where('created_at','>=',$startDate)->where('created_at','<=',date('Y-m-d 00:00:00', $endTime+86400))->get();   
            }
             
            $ordersGroup = array();
            foreach ($ordersData->toArray() as  $value) {
                $currKey = $value['sell_uid'];
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
                    $line['data'] =  $this->tradingFormat($ordersGroup[$listKey],$valArr);
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
            $content->body(view('admin.chart.trading',$data));
        });         
        
    }


    public function poundage(Request $request)
    {
        $this->request = $request;
        $this->action = 'withdraws';
        return Admin::content(function (Content $content) {

            $content->header('手续费统计');

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
                $ordersData = TradesOrdersDetailsModel::where('sell_uid',$currency)->where('created_at','>=',$startDate)->where('created_at','<=',date('Y-m-d 00:00:00', $endTime+86400))->get();   
            } else {
                $ordersData = TradesOrdersDetailsModel::where('created_at','>=',$startDate)->where('created_at','<=',date('Y-m-d 00:00:00', $endTime+86400))->get();   
            }
             
            $ordersGroup = array();
            foreach ($ordersData->toArray() as  $value) {
                $currKey = $value['sell_uid'];
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
                    $line['data'] =  $this->tradingFormat($ordersGroup[$listKey],$valArr);
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
            $content->body(view('admin.chart.poundage',$data));
        }); 
    }


    function tradingFormat($data,$lineData){
        foreach ($data as  $value) {
            $dateTime = strtotime($value['created_at']);
            $dateKey = date('Ymd',$dateTime);
            $lineData[$dateKey] = (float) bcadd($lineData[$dateKey],$value['fee'],6);
        }
        return array_values($lineData);
    }


    public function getCurrency()
    {
        return CurrencyModel::select(['id','name as text'])->where(['is_virtual'=>1])->get()->toArray();
    }

}
