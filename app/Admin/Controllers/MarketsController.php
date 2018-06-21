<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\AdminController as Controller;

use App\Models\TradesCurrenciesModel;
use App\Models\CurrencyModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\MessageBag;

use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class MarketsController extends Controller
{
    
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index(Request $request)
    {
        return Admin::content(function (Content $content) {
            $content->header('货币区管理');
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
            $content->header('编辑货币区');
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
            $content->header('新增货币区');
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
        return Admin::grid(TradesCurrenciesModel::class, function (Grid $grid) {
            $grid->model()->where('is_delete', 0);
            
            $grid->remark('备注');
            $grid->mainCurrency()->name('主交易货币');
            $grid->exchangeCurrency()->name('对交易货币');
            $states = [
                'on'  => ['value' => 1, 'text' => '启用', 'color' => 'primary'],
                'off' => ['value' => 0, 'text' => '关闭', 'color' => 'default'],
            ];
            $grid->status('开启状态')->switch($states);
            $grid->updated_at('更新时间');

            $grid->filter(function ($filter) {
            	// 去掉默认的id过滤器
            });

            $grid->tools(function (Grid\Tools $tools) {
                $tools->batch(function (Grid\Tools\BatchActions $actions) {
                    $actions->disableDelete();
                });
            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                
            });

            $grid->disableExport();
            $grid->disableRowSelector();
            $grid->disableFilter();
        });
    }

    protected function form()
    {
        Form::extend('map', Form\Field\Map::class);
        Form::extend('editor', Form\Field\Editor::class);
        return Admin::form(TradesCurrenciesModel::class, function (Form $form) {

            $form->disableDeletion();

            $form->tab('基础信息', function (Form $form) {

                // 基本资料
                $form->display('id', 'ID');
                $form->text('remark', '备注')->rules('required');
                $form->select('main_currency', '主交易货币')->options(CurrencyModel::where(['status' => 1, 'is_virtual' => 1])->get()->pluck('name', 'id'))->rules('required');
                $form->select('exchange_currency', '对交易货币')->options(CurrencyModel::where(['status' => 1, 'is_base_currency' => 1])->get()->pluck('name', 'id'))->rules('required');              
                $form->text('money_decimal', '价格精度')->help('线图显示价格精度（小数点）')->rules('required');
                $form->text('coin_decimal', '虚拟币数精度')->help('线图显示价格精度（小数点）')->rules('required');

                $states = [
                    'on'  => ['value' => 1, 'text' => '开启', 'color' => 'success'],
                    'off' => ['value' => 0, 'text' => '关闭', 'color' => 'danger'],
                ];
                $form->switch('status', '是否启用')->states($states);  
                $form->display('created_at', '创建时间');
                $form->display('updated_at', '更新时间');

            })->tab('自动交易', function (Form $form) {

                $states = [
                    'on'  => ['value' => 1, 'text' => '开启', 'color' => 'success'],
                    'off' => ['value' => 0, 'text' => '关闭', 'color' => 'danger'],
                ];
                $form->switch('open_robot', '自动交易')->states($states);
                $form->text('robot_buy_rate', '买价格比')->default('0.01')->rules('required');
                $form->text('robot_buy_num', '买单数')->default('10')->rules('required');
                $form->text('robot_sell_rate', '卖价格比')->default('0.010')->rules('required');
                $form->text('robot_sell_num', '卖数量')->default('10')->rules('required');

            });
            
            $form->saving(function (Form $form) {
              $id = $form->model()->id;
              $currency = $form->model()->getMarket($form->main_currency, $form->exchange_currency);
              if(!empty($currency) && $currency['id'] != $id) {
                $error = new MessageBag([
                    'message' => '不能添加互为交易市场与市场已经存在',
                ]);
                return back()->with(compact('error'));
              }
              $currency = $form->model()->getMarket($form->exchange_currency, $form->main_currency);
              if(!empty($currency) && $currency['id'] != $id) {
                $error = new MessageBag([
                    'message' => '不能添加互为交易市场与市场已经存在',
                ]);
                return back()->with(compact('error'));
              }
            });
        });
    }

    public function destroy($id)
    {
        $ids = explode(',', $id);

        if (TradesCurrenciesModel::deleteByIds(array_filter($ids))) {
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
}