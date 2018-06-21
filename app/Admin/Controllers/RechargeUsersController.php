<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\AdminController as Controller;
use App\Models\RechargeItemsModel;
use App\Models\UserRechargeCodeModel;
use App\Models\RechargeCodeOrdersModel as CodeOrdersModel;
use App\Models\CurrencyModel;
use App\Services\RechargeUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

use App\Admin\Extensions\AuditApply;

class RechargeUsersController extends Controller
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
            $content->header('用户充值码');
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
        return false;
        return Admin::content(function (Content $content) use ($id) {
            $content->header('编辑资讯');
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
            $content->header('生成充值码');
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
        return Admin::grid(UserRechargeCodeModel::class, function (Grid $grid) {
            $grid->model()->orderBy('audit', 'ASC');
            // $grid->model()->where('audit', 0);

            $grid->id('ID')->sortable();
            $grid->member()->name('生成用户');
            $grid->moneyTo()->name('类型');
            $grid->amount('面值')->display(function ($amount) {
            	return "￥" . $amount;
            });
            $grid->code('充值码');
            $grid->created_at('生成时间');
            $grid->done_at('使用时间');
            $states = [
                'on'  => ['value' => '1', 'text' => '已审核', 'color' => 'primary'],
                'off' => ['value' => '0', 'text' => '待审核', 'color' => 'default'],
            ];
            // $grid->audit('审核状态')->switch($states);

            // $grid->actions(function (Grid\Displayers\Actions $actions) {
            //     $actions->disableEdit();
            //     $actions->disableDelete();
            // });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableEdit();
                $actions->disableDelete();
                if($actions->row->audit == 1) {
                    $actions->append('已审核');
                } else if($actions->row->audit == -1) {
                    $actions->append('已退款');
                } else {
                    $actions->append(new AuditApply($actions->row));
                }
            });

            $grid->tools(function (Grid\Tools $tools) {
                $tools->batch(function (Grid\Tools\BatchActions $actions) {
                    $actions->disableDelete();
                });
            });
            $grid->disableCreation();
            $grid->disableExport();
            $grid->disableRowSelector();
            // $grid->disableActions();
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
        return Admin::form(UserRechargeCodeModel::class, function (Form $form) {

            $form->disableDeletion();
            $form->display('id', 'ID');

            $states = [
                'on'  => ['value' => '1', 'text' => '已审核', 'color' => 'primary'],
                'off' => ['value' => '0', 'text' => '待审核', 'color' => 'default'],
            ];
            $form->switch('audit', '审核状态')->states($states);

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');

            $form->saving(function (Form $form) {
                
            });
        });
    }

    public function auditApply(Request $request)
    {
        $id = $request->input('id');
        $status = $request->input('status');
        $remark = $request->input('remark');
        $remark = __('api.recharge.apply_remark', ['admin' => Admin::user()->name]) . $remark;

        $servicesRechargeUser = new RechargeUser();
        $result = $servicesRechargeUser->auditApply($id, $status, $remark);
        
        if ($result['status'] == 1) {
            return response()->json([
                'status'  => true,
                'message' => '操作成功',
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => $result['error'],
            ]);
        }
    }
}
