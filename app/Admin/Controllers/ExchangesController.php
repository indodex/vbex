<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\AdminController as Controller;

use App\Models\ExchangeRatesModel;
use App\Models\CurrencyModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class ExchangesController extends Controller
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
            $content->header('汇率设置');
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
            $content->header('编辑汇率');
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
            $content->header('添加汇率');
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
        return Admin::grid(ExchangeRatesModel::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->market('汇率名称');
            $grid->price('价格');
            $grid->created_at('创建时间');
            $grid->filter(function ($filter) {
            	// 去掉默认的id过滤器
              $filter->disableIdFilter();
              $filter->like('name', '货币名称');
            });

            $grid->tools(function (Grid\Tools $tools) {
                $tools->batch(function (Grid\Tools\BatchActions $actions) {
                    $actions->disableDelete();
                });
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
        return Admin::form(ExchangeRatesModel::class, function (Form $form) {

            $form->disableDeletion();

            // 基本资料
            $form->display('id', 'ID');
            $form->select('buy_currency', '兑换货币')->options(CurrencyModel::where(['status' => 1])->get()->pluck('name', 'code'))->help('如，美元 对 人民币，此处为美元')->default(function($form){
              $market = $form->model()->market;
              $coin = explode('_', $market);
              return current($coin);
            });
            $form->select('sell_currency', '兑换货币')->options(CurrencyModel::where(['status' => 1])->get()->pluck('name', 'code'))->help('如，美元 对 人民币，此处为人民币')->default(function($form){
              $market = $form->model()->market;
              $coin = explode('_', $market);
              return end($coin);
            });

            $form->text('price', '兑换价格')->help('如，1美元 兑 多少个人民币');
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
        });
    }
}