<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\AdminController as Controller;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\InfoBox;
use App\Models\RechargeCodeModel;
use App\Models\RechargeCodeOrdersModel;
use App\Models\UserRechargeCodeModel;
use App\Models\UserRechargeCodeOrdersModel;
use App\Models\UserCertificationModel as Cer;
use App\Models\CurrencyModel;
use App\Models\TradesCurrenciesModel;

class HacChartController extends Controller
{


    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('HAC使用统计');
            $content->description('');
            $data = array();
            $total = RechargeCodeModel::sum('amount');
            $useTotal = RechargeCodeModel::where('status',1)->sum('amount');
            $data['hacTotal'] = array('total'=>$total,'useTotal'=>$useTotal,'availableTotal'=>$total - $useTotal);

            $lineData = array();
            $dayArr = array();
            $valArr = array();
            $dayNum = 15;
            for ($i=$dayNum; $i >= 0; $i--) { 
                $time = mktime (0,0,0,date("m"),date("d")-$i,date("Y"));
                $key = date('Ymd',$time);
                $valArr[$key] = 0;
                $dayArr[] = date('m-d',$time);
            }

            $startTime =  date('Y-m-d',mktime (0,0,0,date("m"),date("d")-$dayNum,date("Y"))); 
            $rechargeData = RechargeCodeOrdersModel::where('created_at','>=',$startTime)->get();
            $sumAmount = 0;         
            foreach ($rechargeData->toArray() as  $value) {
                $dateTime = strtotime($value['created_at']);
                $dateKey = date('Ymd',$dateTime);
                $valArr[$dateKey] =(float) bcadd($valArr[$dateKey],$value['amount'],2);
                $sumAmount = bcadd($sumAmount,$value['amount'],2);
            }
            $lineVal = array();
            foreach ($valArr as $value) {
                $lineVal[] = $value;
            }
            $data['lineData']['title'] = json_encode($dayArr); 
            $data['lineData']['val'] = json_encode(array_values($valArr)); 
            $data['lineData']['sumAmount'] = $sumAmount; 
            $content->body(view('admin.chart.hac_home', $data));
            // $content->row(function ($row)  {
            //     $row->column(3, new InfoBox('用户', 'users', 'aqua', '/admin/user', 100));
            //     $row->column(3, new InfoBox('充值', 'shopping-cart', 'green', '/admin/apply', 200));
            //     $row->column(3, new InfoBox('奖励', 'book', 'yellow', '/admin/rewards', 400));
            //     $row->column(3, new InfoBox('提现', 'file', 'red', '/admin/withdraw', 300));
            // });
        });
    }

    public function userHac()
    {
        return Admin::content(function (Content $content) {
            $content->header('用户HAC使用统计');
            $content->description('');
            $data = array();
            $total = UserRechargeCodeModel::where('status','>',0)->sum('amount');
            $useTotal = UserRechargeCodeModel::where('status',1)->sum('amount');
            $data['hacTotal'] = array('total'=>$total,'useTotal'=>$useTotal,'availableTotal'=>$total - $useTotal);

            $createData = array();
            $dayArr = array();
            $createValArr = array();
            $dayNum = 15;
            //根据时间对数据KEY 进行归类
            for ($i=$dayNum; $i >= 0; $i--) { 
                $time = mktime (0,0,0,date("m"),date("d")-$i,date("Y"));
                $key = date('Ymd',$time);
                $createValArr[$key] = 0;
                $dayArr[] = date('m-d',$time);
            }

            $useValArr = $createValArr;         //  赋值初始化使用HAC 的值
            //开始时间
            $startTime =  date('Y-m-d',mktime (0,0,0,date("m"),date("d")-$dayNum,date("Y"))); 

            //生成haC统计
            $rechargeObj = UserRechargeCodeModel::where('status','>',0)->where('created_at','>=',$startTime)->get();
            $sumCreateAmount = 0;         
            foreach ($rechargeObj->toArray() as  $value) {
                $dateTime = strtotime($value['created_at']);
                $dateKey = date('Ymd',$dateTime);
                $createValArr[$dateKey] =(float) bcadd($createValArr[$dateKey],$value['amount'],2);
                $sumCreateAmount = bcadd($sumCreateAmount,$value['amount'],2);
            }
            
            $data['createHac']['title'] = json_encode($dayArr); 
            $data['createHac']['val'] = json_encode(array_values($createValArr)); 
            $data['createHac']['sumAmount'] = $sumCreateAmount;

            //使用haC统计
            $useObj = UserRechargeCodeOrdersModel::where('status',1)->where('created_at','>=',$startTime)->get();
            $sumUseAmount = 0;         
            foreach ($useObj->toArray() as  $value) {
                $dateTime = strtotime($value['created_at']);
                $dateKey = date('Ymd',$dateTime);
                $useValArr[$dateKey] =(float) bcadd($useValArr[$dateKey],$value['amount'],2);
                $sumUseAmount = bcadd($sumUseAmount,$value['amount'],2);
            }
            
            $data['useHac']['title'] = json_encode($dayArr); 
            $data['useHac']['val'] = json_encode(array_values($useValArr)); 
            $data['useHac']['sumAmount'] = $sumUseAmount;

            $content->body(view('admin.chart.hac_user', $data));
            // $content->row(function ($row)  {
            //     $row->column(3, new InfoBox('用户', 'users', 'aqua', '/admin/user', 100));
            //     $row->column(3, new InfoBox('充值', 'shopping-cart', 'green', '/admin/apply', 200));
            //     $row->column(3, new InfoBox('奖励', 'book', 'yellow', '/admin/rewards', 400));
            //     $row->column(3, new InfoBox('提现', 'file', 'red', '/admin/withdraw', 300));
            // });
        });
    }

}
