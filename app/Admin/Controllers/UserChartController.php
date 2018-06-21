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
use App\Models\LoginLogModel;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Grid;
use Encore\Admin\Widgets\Form;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserChartController extends Controller
{


    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('Dashboard');
            $content->description('');
            $currency  = $this->request->currency ?? 1;
            // $time[] = $this->request->startTime ? date('Y-m-d 00:00:00', strtotime("$request->startTime")) : date('Y-m-d 00:00:00', strtotime('-7 days'));
            // $time[] = $this->request->endTime ? date('Y-m-d 23:59:59', strtotime("$request->endTime")) : date('Y-m-d 23:59:59', strtotime('-1 days'));
            $time[] = $this->request->startTime ?? date('Y-m-d 00:00:00', strtotime('-15 days'));
            $time[] = $this->request->endTime ?? Carbon::parse('today')->toDateTimeString();
            $this->time = $time;
            $order = WithdrawsOrdersModel::select(\DB::raw('DATE(created_at) as date'),\DB::raw('SUM(sum_amount) as sum_amount'))
                            ->where(['currency'=>$currency])
                            ->whereBetween('created_at',$time)
                            ->groupBy(\DB::raw('Date(created_at)'))
                            ->get()
                            ->toArray();

            $data['list'] = json_encode($this->getDates($time, $order));
            $content->body($this->form());

            $currencyInfo = CurrencyModel::find($currency);
            $data['currency'] = $currencyInfo->name;
        });
    }

    public function login(Request $request)
    {
        return Admin::content(function (Content $content) {
            $content->header('用户登录');
            $content->description('');
            // $time[] = $this->request->startTime ? date('Y-m-d 00:00:00', strtotime("$request->startTime")) : date('Y-m-d 00:00:00', strtotime('-7 days'));
            $request = \Request::all();
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
            $loginData = LoginLogModel::where('login_time','>=',$startTime)->where('login_time','<=',$endTime+86400)->get();     
            foreach ($loginData->toArray() as  $value) {
                $dateTime = strtotime($value['created_at']);
                $dateKey = date('Ymd',$dateTime);
                $valArr[$dateKey] =(int) $valArr[$dateKey] + 1;
            }
            $data['startTime'] = date("Y-m-d H:i:s",$startTime);
            $data['endTime'] = date("Y-m-d H:i:s",$endTime);
            $data['lineData']['title'] = json_encode($dayArr); 
            $data['lineData']['val'] = json_encode(array_values($valArr)); 
            $content->body(view('admin.chart.user_login',$data));
            //$this->time = $time;

            // $content->row(function ($row)  {
            //     $row->column(3, new InfoBox('用户', 'users', 'aqua', '/admin/user', 100));
            //     $row->column(3, new InfoBox('充值', 'shopping-cart', 'green', '/admin/apply', 200));
            //     $row->column(3, new InfoBox('奖励', 'book', 'yellow', '/admin/rewards', 400));
            //     $row->column(3, new InfoBox('提现', 'file', 'red', '/admin/withdraw', 300));
            // });
        });
    }
    
    public function registered()
    {
        return Admin::content(function (Content $content) {
            $content->header('注册统计');
            $content->description('');
            // $time[] = $this->request->startTime ? date('Y-m-d 00:00:00', strtotime("$request->startTime")) : date('Y-m-d 00:00:00', strtotime('-7 days'));
            $request = \Request::all();
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
            $loginData = User::where('created_at','>=',$startDate)->where('created_at','<=',date('Y-m-d 00:00:00', $endTime+86400))->get();     
            foreach ($loginData->toArray() as  $value) {
                $dateTime = strtotime($value['created_at']);
                $dateKey = date('Ymd',$dateTime);
                $valArr[$dateKey] =(int) $valArr[$dateKey] + 1;
            }
            $data['startTime'] = date("Y-m-d H:i:s",$startTime);
            $data['endTime'] = date("Y-m-d H:i:s",$endTime);
            $data['lineData']['title'] = json_encode($dayArr); 
            $data['lineData']['val'] = json_encode(array_values($valArr)); 
            $content->body(view('admin.chart.user_login',$data));
            //$this->time = $time;

            // $content->row(function ($row)  {
            //     $row->column(3, new InfoBox('用户', 'users', 'aqua', '/admin/user', 100));
            //     $row->column(3, new InfoBox('充值', 'shopping-cart', 'green', '/admin/apply', 200));
            //     $row->column(3, new InfoBox('奖励', 'book', 'yellow', '/admin/rewards', 400));
            //     $row->column(3, new InfoBox('提现', 'file', 'red', '/admin/withdraw', 300));
            // });
        });
    }


}
