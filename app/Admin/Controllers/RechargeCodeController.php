<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\AdminController as Controller;
use App\Models\RechargeItemsModel;
use App\Models\RechargeCodeModel;
use App\Models\RechargeCodeOrdersModel as CodeOrdersModel;
use App\Models\CurrencyModel;
use App\Services\Recharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

use App\Admin\Extensions\RechargeApply;

class RechargeCodeController extends Controller
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
            $content->header('充值码管理');
            $content->description('description');
            $content->body($this->grid());
        });
    }

    /**
     * 充值申请
     *
     * @return Content
     */
    public function apply(Request $request)
    {
        return Admin::content(function (Content $content) {
            $content->header('充值申请管理');
            $content->description('description');

            $grid = Admin::grid(CodeOrdersModel::class, function (Grid $grid) {
                // $grid->model()->where('status', 2);

                $grid->id('ID')->sortable();
                $grid->rechargeCode()->amount('面值')->display(function ($amount) {
                    return "￥" . $amount;
                });
                $grid->rechargeCode()->code('充值码');
                $grid->created_at('充值时间');
                // 状态，3：未使用，2：等待审核，1：充值成功，0：充值失败
                $grid->status('状态')->display(function ($status) {
                    if($status == 1) 
                        return '<span class="label label-success">充值成功</span>';
                    else if($status == 2) 
                        return '等待审核';
                    else if($status == 3) 
                        return '-';
                    else if($status == 0) 
                        return '<span class="label bg-red">充值失败</span>';
                });
                $grid->user()->name('充值用户');

                $grid->actions(function (Grid\Displayers\Actions $actions) {
                    $actions->disableEdit();
                    $actions->disableDelete();
                    if($actions->row->status == 1) {
                        $actions->append('已审核');
                    } else {
                        $actions->append(new RechargeApply($actions->row));
                    }
                    
                });

                $grid->tools(function (Grid\Tools $tools) {
                    $tools->batch(function (Grid\Tools\BatchActions $actions) {
                        $actions->disableDelete();
                    });
                });


                $grid->filter(function ($filter) {
                    // 去掉默认的id过滤器
                    $filter->disableIdFilter();
                    $filter->like('code', '充值码');
                    $filter->in('status', '订单状态')
                           ->select([
                                '2' => '等待审核',
                                '1' => '充值成功',
                                '0' => '充值失败',
                            ]);
                });

                $grid->disableCreation();
                $grid->disableExport();
                $grid->disableRowSelector();
            });

            $content->body($grid);
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
        return Admin::grid(RechargeCodeModel::class, function (Grid $grid) {
            $grid->model()->orderBy('id', 'DESC');

            $grid->id('ID')->sortable();
            $grid->moneyTo()->name('类型');
            $grid->amount('面值')->display(function ($amount) {
            	return "￥" . $amount;
            });
            $grid->code('充值码');
            $grid->created_at('生成时间');
            $grid->done_at('使用时间');
            // 状态，3：未使用，2：等待审核，1：充值成功，0：充值失败
            $grid->status('状态')->display(function ($status) {
            	if($status == 1) 
            		return '充值成功';
            	else if($status == 2) 
            		return '等待审核';
            	else if($status == 3) 
            		return '-';
            	else if($status == 0) 
            		return '充值失败';
            });
            $grid->user()->name('充值用户');

            // $grid->actions(function (Grid\Displayers\Actions $actions) {
            //     $actions->disableEdit();
            //     $actions->disableDelete();
            // });
            $grid->disableActions();

            $grid->tools(function (Grid\Tools $tools) {
                $tools->batch(function (Grid\Tools\BatchActions $actions) {
                    $actions->disableDelete();
                });
            });

            $grid->filter(function ($filter) {
                // 去掉默认的id过滤器
                $filter->disableIdFilter();
                $filter->like('code', '充值码');
            });
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
        return Admin::form(RechargeCodeModel::class, function (Form $form) {

            $form->disableDeletion();
            $form->display('id', 'ID');
            $form->select('currency', '法币类型')->options(CurrencyModel::where(['status' => 1, 'is_virtual' => 0, 'code' => 'USD'])->get()->pluck('name', 'id'))->rules('required');
            $form->select('amount', '生成面值')->options(RechargeItemsModel::all()->pluck('amount', 'amount'))->rules('required');
            $form->text('number', '生成数量')->rules('required|regex:/^\d+$/', [
			    'regex' => '必须全部为数字',
			]);
            $form->hidden('type')->default(function ($form) {
                    return 1;
                });
            $form->hidden('belongto_uid')->default(function ($form) {
                    return 0;
                });

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
        });
    }

    public function postApply(Request $request)
    {
        $id = $request->input('id');
        $status = $request->input('status');
        $remark = $request->input('remark');
        $remark = __('api.recharge.apply_remark', ['admin' => Admin::user()->name]) . $remark;

        $result = $this->getRechargeService()->applyRecharge($id, $status, $remark);
        
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

    private function getRechargeService()
    {
        return new Recharge();
    }
}
