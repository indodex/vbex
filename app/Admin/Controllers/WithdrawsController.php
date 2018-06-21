<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\AdminController as Controller;
use App\Models\WithdrawsOrdersModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

// Extensions
use App\Admin\Extensions\WithdrawsCheckRow;
use App\Admin\Extensions\WithdrawsApply;
use App\Admin\Extensions\WithdrawsShow;

use App\Services\Withdraws;

use App\Models\UserModel;
use Mail;

class WithdrawsController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function currency()
    {
        return Admin::content(function (Content $content) {
            $content->header('虚拟币');
            $content->description('description');

            $grid = Admin::grid(WithdrawsOrdersModel::class, function (Grid $grid) {
                $grid->model()->orderBy('id', 'DESC');

                $grid->id('ID')->sortable();
                $grid->currencyTo()->name('币种');
                $grid->created_at('时间');
                $grid->uid('UID');
                $grid->user()->name('用户名');
                $grid->address('提现地址');
                $grid->sum_amount('提现数量')->display(function($sum_amount){
                    return (float) $sum_amount;
                });
                $grid->amount('扣除手续费后数量')->display(function($amount){
                    return (float) $amount;
                });
                $grid->status('提现状态')->display(function ($status) {
                    if($status == 3) {
                    	return '<span class="label label-default">等待审核</span>';
                    } else if($status == 2) {
                    	return '<span class="label label-info">等待提现</span>';
                    } else if($status == 1) {
                    	return '<span class="label label-success">提现成功</span>';
                    } else if($status == -1) {
                        return '<span class="label label-danger">提现失败</span>';
                    } else if($status == -2) {
                        return '<span class="label label-danger">操作失败</span>';
                    }else {
                    	return '<span class="label label-warning">审核不通过</span>';
                    }
                });
                
                $grid->actions(function (Grid\Displayers\Actions $actions) {
                    $actions->disableEdit();
                    $actions->disableDelete();
                    $data = $actions->row;

                    // $actions->append(new CheckRow($actions->getKey()));
                    switch ($actions->row->status) {
                        case -1:
                            $actions->append(new WithdrawsShow($data));
                            break;
                        case -2:
                            $actions->append(new WithdrawsShow($data));
                            break;
                        case 0:
                            $actions->append('审核不通过');
                            break;
                        case 1:
                            $actions->append('提现成功');
                            break;
                        case 2:
                            $actions->append('等待提现');
                            break;
                        default:
                            // 审核按钮
                            $actions->append(new WithdrawsApply($data));
                            break;
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
            });

            $content->body($grid);
        });
    }

    public function postCurrency(Request $request)
    {
        $id     = $request->input('id');
        $status = $request->input('status');
        $remark = $request->input('remark');
        $remark = __('api.recharge.apply_remark', ['admin' => Admin::user()->name]) . $remark;

        $Withdraws = new Withdraws();
        $result    = $Withdraws->withdraw($id, $status, $remark);

        if ($result['status'] == 1) {

            $userModel = new UserModel();
            $user = UserModel::find($result['order']['uid']);
            if(!empty($user->email)) {
                $email = $user->email;
                $emailContent = __('api.public.withdraw_notice', ['number' => (float) $result['order']['amount'], 'code' => $result['order']['code'], 'address' => $result['order']['address']]);
                $flag = Mail::send('auth.withdrawMail',['emailContent'=>$emailContent],function($message) use($email){
                    $message ->to($email)->subject(__('api.public.withdraw_email_subject'));
                });
            }
            return response()->json([
                'status'  => true,
                'message' => '操作成功',
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => $result['message'],
            ]);
        }


    }
}
