<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\AdminController as Controller;

use App\Models\CurrencyModel;
use App\Models\AccountsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class CurrencyController extends Controller
{
    
    use ModelForm;

    public $curCode;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function wallet(Request $request)
    {
        return Admin::content(function (Content $content) {
            $content->header('钱包管理');
            $content->description('description');
            $content->body($this->grid());
        });
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index(Request $request)
    {
        return Admin::content(function (Content $content) {
            $content->header('币种管理');
            $content->description('description');
            $content->body($this->grid());
        });
    }
    /**
     * Edit interface.
     *
     * @param $id
     *
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('编辑钱包');
            $content->description('description');
            $content->body($this->form()->edit($id));
        });
    }
    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {
            $content->header('新增钱包');
            $content->body($this->form());
        });
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(CurrencyModel::class, function (Grid $grid) {

            $grid->id('ID')->sortable();

            $grid->name('名称');
            $grid->code('代号');
            $grid->is_virtual('是否虚拟币')->display(function ($isVirtual){
            	if($isVirtual == 1) {
            		return '<code class="badge bg-blue">是</code>';
            	} else {
            		return '<code class="badge bg-green">否</code>';
            	}
            });
            // $grid->column('last_price', '最新价格')->display(function () {
            //     return 0;
            // });
            $grid->column('market_total', '市场总量')->display(function () {
                $sum = (new AccountsModel())->getCurrencySum($this->id);
                return my_format_money($sum) . '万';
            });
            $grid->created_at('创建时间');
            // $grid->updated_at();
            
            

            // $routeAction = \Route::current()->getActionName();
            // $routeArr = explode('@', $routeAction);
            // $actName = end($routeArr);
            // if($actName != 'index') {
                $grid->disableCreation();
              
                $states = [
                    'on'  => ['value' => 1, 'text' => '启用', 'color' => 'primary'],
                    'off' => ['value' => 0, 'text' => '关闭', 'color' => 'default'],
                ];
                $grid->status('开启状态')->switch($states);
            // }

            $grid->filter(function ($filter) {
            	// 去掉默认的id过滤器
    			  $filter->disableIdFilter();
                $filter->like('name', '货币名称');
                $filter->like('code', '代号');
            });

            $grid->tools(function (Grid\Tools $tools) {
                $tools->batch(function (Grid\Tools\BatchActions $actions) {
                    $actions->disableDelete();
                });
            });

            // $grid->actions(function (Grid\Displayers\Actions $actions) {
            //     $routeAction = \Route::current()->getActionName();
            //     $routeArr = explode('@', $routeAction);
            //     $actName = end($routeArr);
            //     if($actName != 'index') {
            //       $actions->disableEdit();
            //       $actions->disableDelete();
            //     }
            // });
            $grid->disableExport();
            $grid->disableRowSelector();
        });
    }

    protected function forms()
    {
        Form::extend('map', Form\Field\Map::class);
        Form::extend('editor', Form\Field\Editor::class);
        return Admin::form(CurrencyModel::class, function (Form $form) {

            $form->disableDeletion();

            $states = [
                'on'  => ['value' => 1, 'text' => '开启', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '关闭', 'color' => 'danger'],
            ];

            // 基本资料
            $form->display('id', 'ID');
            $form->image('logo')->move('currencies');
            // $form->image('logo', 'LOGO');
            $form->text('code', '代号')->rules('required')->help('请录入小写字母');
            $form->text('name', '货币名称')->rules('required');
            $form->text('symbol', '货币符号');
            $form->text('decimals', '小数点');
            $form->text('min_trading_val', '最小交易额');
            $form->text('trading_service_rate', '交易手续费率');
            $form->text('withdraw_service_charge', '提现手续费');
            $form->text('fee', '旷工费')->help('底层提现旷工费用');
            // $form->number('confirmations', '网络确认');
            $form->switch('is_virtual', '是否虚拟币')->states([
                'on'  => ['value' => true, 'text' => '是', 'color' => 'success'],
                'off' => ['value' => false, 'text' => '否', 'color' => 'danger'],
            ]);
            
            $form->switch('enable_deposit', '开启充值')->states($states);
            $form->switch('enable_withdraw', '开启提现')->states($states);
            $form->switch('is_base_currency', '基础货币')->states($states);
            $form->switch('status', '是否启用')->states($states);

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
            
            $form->saving(function (Form $form) {
                $form->code = strtoupper($form->code);
            });
        });
    }


    public function form()
    {
        Form::extend('map', Form\Field\Map::class);
        Form::extend('editor', Form\Field\Editor::class);
        return CurrencyModel::form(function (Form $form) { 

            $form->tab('基础信息', function (Form $form) {

                $form->disableDeletion();

                $states = [
                    'on'  => ['value' => 1, 'text' => '开启', 'color' => 'success'],
                    'off' => ['value' => 0, 'text' => '关闭', 'color' => 'danger'],
                ];

                // 基本资料
                $form->display('id', 'ID');
                $form->image('logo')->move('currencies');
                // $form->image('logo', 'LOGO');
                $form->text('code', '代号')->rules('required')->help('请录入小写字母');
                $form->text('name', '货币名称')->rules('required');
                // $form->text('symbol', '货币符号');
                $form->switch('is_virtual', '是否虚拟币')->states([
                    'on'  => ['value' => true, 'text' => '是', 'color' => 'success'],
                    'off' => ['value' => false, 'text' => '否', 'color' => 'danger'],
                ]);
                $form->switch('is_base_currency', '基础货币')->states($states);
                
                $form->switch('status', '是否启用')->states($states);

                $form->display('created_at', '创建时间');
                $form->display('updated_at', '更新时间');
                
                $form->saving(function (Form $form) {
                    $form->code = strtoupper($form->code);
                });

            })->tab('交易设置', function (Form $form) {
              $states = [
                'on'  => ['value' => 1, 'text' => '开启', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '关闭', 'color' => 'danger'],
              ];
              $form->text('decimals', '小数点');
              $form->text('min_trading_val', '最小交易额');
              $form->text('trading_service_rate', '交易手续费率');
              $form->text('withdraw_service_charge', '提现手续费');
              $form->text('fee', '旷工费')->help('底层提现旷工费用（单位：ETH）');
              // $form->number('confirmations', '网络确认');
              $form->switch('enable_deposit', '开启充值')->states($states);
              $form->switch('enable_withdraw', '开启提现')->states($states);
              


            })->tab('安全设置', function (Form $form) {
              $states = [
                'on'  => ['value' => 1, 'text' => '开启', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '关闭', 'color' => 'danger'],
              ];
              $form->text('up_number_audit', '充值审核');
              $form->text('extract_number_audit', '提现审核');
              $form->text('transfer_number', '转币额度');

            });
            
            
        });
    }

    public function destroy($id)
    {
        $ids = explode(',', $id);

        if (User::deleteByIds(array_filter($ids))) {
            return response()->json([
                'status'  => true,
                'message' => trans('admin.delete_succeeded'),
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => trans('admin.delete_failed'),
            ]);
        }
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
   //  protected function form()
   //  {
   //      Form::extend('map', Form\Field\Map::class);
   //      Form::extend('editor', Form\Field\Editor::class);
   //      return Admin::form(CurrencyModel::class, function (Form $form) {

   //          $form->disableDeletion();

   //          $states = [
			//     'on'  => ['value' => 1, 'text' => '开启', 'color' => 'success'],
			//     'off' => ['value' => 0, 'text' => '关闭', 'color' => 'danger'],
			// ];

			// // 基本资料
   //          $form->display('id', 'ID');
   //          $form->image('logo', 'LOGO');
   //          $form->text('name', '货币名称');
   //          $form->text('coin', '货币简写');
   //          $form->switch('status', '是否启用')->states($states);
   //          $form->switch('trade_enable', '交易活动')->states($states);
   //          $form->text('trade_opening_price', '开盘价格')->help('0无限制');

   //          // 交易设置项
   //          $form->divide();
			// $form->text('trade_buy_amount_limit', '单日买入限额')->placeholder('如 0.01')->help('0无限制');
   //          $form->text('trade_sell_amount_limit', '单日卖出限额')->placeholder('如 0.01')->help('0无限制');
   //          $form->text('trade_price_min', '最小交易价格')->placeholder('如 0.01')->help('0无限制');
   //          $form->text('trade_amount_min', '最小交易量')->placeholder('如 0.01')->help('0无限制');
   //          $form->text('trade_price_max', '最大交易价格')->placeholder('如 0.01')->help('0无限制');
   //          $form->text('trade_amount_max', '最大交易量')->placeholder('如 0.01')->help('0无限制');
   //          $form->text('trade_sell_fee', '买手续费')->value('0.0')->help('0无限制');
   //          $form->text('trade_buy_fee', '卖手续费')->value('0.0')->help('0无限制');
   //          $form->text('trade_increase_limit', '每日涨幅限制')->placeholder('如 0.01')->help('0无限制');

   //          // 时间设置
   //          $form->switch('trade_time_enable', '交易限制时间')->states($states);
   //          $form->time('trade_open_time', '每日交易开始时间')->format('HH:mm:ss')->help('交易限制时间开启才生效');
   //          $form->time('trade_close_time', '每日交易结束时间')->format('HH:mm:ss')->help('交易限制时间开启才生效');
   //          $form->switch('weekend_close_enable', '周末休市')->states($states)->help('交易限制时间开启才生效');
            
   //          // 是否充值
   //          $form->divide();
   //          $form->switch('wallet_enable_deposit', '充值开关')->states($states);
   //          $form->text('wallet_rpc_user', '钱包用户名')->help('需要开启钱包充值');
   //          $form->password('wallet_rpc_password', '钱包密码')->help('需要开启钱包充值');
   //          $form->text('wallet_address', '钱包充值地址')->help('需要开启钱包充值');
   //          $form->text('min_recharge_amount', '最小充值金额')->help('需要开启钱包充值');
   //          $form->switch('wallet_enable_withdraw', '提现开关')->states($states);
   //          $form->text('min_withdraw_amount', '最小提现数量')->help('需要开启钱包充值');
   //          $form->text('max_withdraw_amount', '最大提现数量')->help('需要开启钱包充值');
   //          $form->text('withdraw_fee_rate', '提现手续费率');

   //          $form->display('created_at', '创建时间');
   //          $form->display('updated_at', '更新时间');
            
   // 			$form->saving(function (Form $form) {
                
			// });
   //      });
   //  }
}