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
use App\Models\UserRechargeCodeModel;
use App\Models\AccountsModel;
use App\Models\WithdrawsOrdersModel;

class HomeController extends Controller
{
    // public function index()
    // {
    //     return Admin::content(function (Content $content) {

    //         $content->header('Dashboard');
    //         $content->description('Description...');

    //         $content->row(Dashboard::title());

    //         $content->row(function (Row $row) {
    //             $row->column(3, new InfoBox('用户', 'users', 'aqua', '/admin/user', 100));
    //             $row->column(3, new InfoBox('充值', 'shopping-cart', 'green', '/admin/apply', 200));
    //             $row->column(3, new InfoBox('奖励', 'book', 'yellow', '/admin/rewards', 400));
    //             $row->column(3, new InfoBox('提现', 'file', 'red', '/admin/withdraw', 300));

    //             // $row->column(4, function (Column $column) {
    //             //     $column->append(Dashboard::environment());
    //             // });

    //             // $row->column(4, function (Column $column) {
    //             //     $column->append(Dashboard::extensions());
    //             // });

    //             // $row->column(4, function (Column $column) {
    //             //     $column->append(Dashboard::dependencies());
    //             // });
    //         });
    //     });
    // }


    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('Dashboard');
            $content->description('');
            // $data['userCount'] = User::where('is_delete',0)->count();
            $data['cerCount'] = Cer::where('status',0)->orWhere('advanced_status',0)->count();
            $data['currencyCount'] = CurrencyModel::count();
            // $data['tradesCount'] = TradesCurrenciesModel::count();
            $data['userCodeCount'] = UserRechargeCodeModel::where('audit', 0)->count();
            $data['withdrawCount'] = WithdrawsOrdersModel::where('status', 3)->count();
            $content->row(function ($row)  use($data){
                $row->column(3, new InfoBox('哈希码审核', 'users', 'aqua', '/admin/rechargeUsers', $data['userCodeCount']));
                // $row->column(3, new InfoBox('用户', 'users', 'aqua', '/admin/user', $data['userCount']));
                $row->column(3, new InfoBox('认证申请', 'file', 'green', '/admin/cer', $data['cerCount']));
                $row->column(3, new InfoBox('币种管理', 'book', 'yellow', '/admin/currency', $data['currencyCount']));
                $row->column(3, new InfoBox('提现审核', 'shopping-cart', 'red', '/admin/withdraws/currency', $data['withdrawCount']));
                // $row->column(3, new InfoBox('交易市场', 'shopping-cart', 'red', '/admin/markets', $data['tradesCount']));
                
            });
            // $content->row(function ($row)  {
            //     $row->column(3, new InfoBox('用户', 'users', 'aqua', '/admin/user', 100));
            //     $row->column(3, new InfoBox('充值', 'shopping-cart', 'green', '/admin/apply', 200));
            //     $row->column(3, new InfoBox('奖励', 'book', 'yellow', '/admin/rewards', 400));
            //     $row->column(3, new InfoBox('提现', 'file', 'red', '/admin/withdraw', 300));
            // });
            
            $accounts = AccountsModel::select('currency',\DB::raw('sum(balance) as balance'), \DB::raw('sum(locked) as locked'))->groupBy('currency')->get();
            if(!empty($accounts)) {
                $currencies = CurrencyModel::all()->toArray();
                $currencies = array_column($currencies, 'code', 'id');

                $accounts = $accounts->toArray();
                foreach ($accounts as $key => &$value) {
                    $value['amount'] = (float) bcadd($value['balance'], $value['locked'], 18);
                    $value['code']   = $currencies[$value['currency']];
                    if($currencies[$value['currency']] == 'CNY') {
                        unset($accounts[$key]);
                    }
                }
                $data['chart_amount'] = json_encode(array_column($accounts, 'amount'));
                $data['chart_code']   = json_encode(array_column($accounts, 'code'));
            } else {
                $data['chart_amount'] = json_encode([]);
                $data['chart_code']   = json_encode([]);
            }
            
            $content->body(view('admin.chart.index_content', $data));
        });
    }

}
