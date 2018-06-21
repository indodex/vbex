<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\AdminController as Controller;
use App\Models\RechargeItemsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class RechargeItemsController extends Controller
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
            $content->header('充值选项');
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
            $content->header('编辑充值选项');
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
            $content->header('创建充值选项');
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
        return Admin::grid(RechargeItemsModel::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->amount('充值金额')->display(function ($amount) {
            	return "￥" . $amount;
            });
            $grid->created_at('生成时间');
            // 状态，1：启用，0：充值失败
            // $grid->enable('状态')->display(function ($enable) {
            // 	if($enable == 1) 
            // 		return '启用';
            // 	else if($enable == 0) 
            // 		return '关闭';
            // });
            // 设置text、color、和存储值
            $states = [
                'on'  => ['value' => 1, 'text' => '启用', 'color' => 'primary'],
                'off' => ['value' => 0, 'text' => '关闭', 'color' => 'default'],
            ];
            $grid->enable('状态')->switch($states);

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableEdit();
                // $actions->disableDelete();
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
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        Form::extend('map', Form\Field\Map::class);
        Form::extend('editor', Form\Field\Editor::class);
        return Admin::form(RechargeItemsModel::class, function (Form $form) {

            $form->disableDeletion();
            $form->display('id', 'ID');
            $form->text('amount', '充值金额')->rules('required|regex:/^\d+\.?\d{0,2}$/', [
			    'regex' => '必须全部为数字',
			]);
            $states = [
                'on'  => ['value' => 1, 'text' => '启用', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '关闭', 'color' => 'danger'],
            ];
            $form->switch('enable', '是否启用')->states($states);

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
        });
    }
}
