<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\AdminController as Controller;
use App\Models\DepositsOrdersModel;
use App\Models\DepositsAddressesModel;
use App\Models\ArtificialRechargeModel;
use App\Models\CurrencyModel;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\MessageBag;

use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

// Extensions
// use App\Admin\Extensions\CheckRow;
use App\Admin\Extensions\DepositsApply;
use App\Services\Recharge;
use App\Coin;
use Mail;

class DepositsController extends Controller
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

            $grid = Admin::grid(DepositsOrdersModel::class, function (Grid $grid) {
                $grid->model()->orderBy('id', 'DESC');

                $grid->id('ID')->sortable();
                $grid->currencyTo()->name('币种');
                $grid->created_at('时间');
                $grid->uid('UID');
                $grid->user()->name('用户名');
                $grid->address('充值地址');
                $grid->amount('充值金额')->display(function($amount){
                    return (float) $amount;
                });
                $grid->status('充值金额')->display(function ($status) {
                    if($status == 3) {
                    	return '等待足够确认数';
                    } else if($status == 2) {
                    	return '等待审核';
                    } else if($status == 1) {
                    	return '成功';
                    } else {
                    	return '审核失败';
                    }
                });
                
                $grid->actions(function (Grid\Displayers\Actions $actions) {
                    $actions->disableEdit();
                    $actions->disableDelete();
                    $data = $actions->row;

                    switch ($actions->row->status) {
                        case 0:
                            // $actions->append('充值失败');
                            $actions->append(new DepositsApply($data));
                            break;
                        case 1:
                            $actions->append('充值成功');
                            break;
                        case 3:
                            $actions->append('等待足够确认数');
                            break;
                        default:
                            // 审核按钮
                            $actions->append(new DepositsApply($data));
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

    public function artificialRechargeList()
    {
        return Admin::content(function (Content $content) {
            $content->header('人工充值');
            $content->description('列表');

            $grid = Admin::grid(ArtificialRechargeModel::class, function (Grid $grid) {
                $grid->model()->orderBy('id', 'DESC');

                $grid->id('ID')->sortable();
                $grid->currencyTo()->name('币种');
                $grid->created_at('时间');
                $grid->uid('UID');
                $grid->user()->name('用户名');
                $grid->amount('充值金额');
                $grid->status('状态')->display(function ($status) {
                    if($status == 1) {
                        return '充值成功';
                    } else {
                        return '充值失败';
                    }
                });
                
                $grid->disableActions();

                // $grid->disableCreation();
                $grid->disableExport();
                $grid->disableRowSelector();
            });

            $content->body($grid);
        });
    }

    // 人工充值
    public function artificialRecharge()
    {
        return Admin::content(function (Content $content) {
            $content->header('人工充值');
            $content->body($this->forms());
        });
    }

    public function postArtificialRecharge(Request $request)
    {
        $currency = $request->input('currency');
        $uid      = $request->input('uid');
        $amount   = $request->input('amount');

        $result = $this->getRechargeService()->artificialRecharge($uid, $currency, $amount);
        
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
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function forms()
    {
        Form::extend('map', Form\Field\Map::class);
        Form::extend('editor', Form\Field\Editor::class);
        return Admin::form(ArtificialRechargeModel::class, function (Form $form) {
            $form->select('currency', '充值币种')->options(CurrencyModel::all()->pluck('name', 'id'));
            $form->select('uid', '充值用户')->options(UserModel::all()->pluck('name', 'id'));
            $form->text('amount', '充值数量');
            $form->setAction('artificial');
            $form->disableReset();
            // $form->tools(function (Form\Tools $tools) 
            // {
            //     $tools->disableBackButton();        // 去掉返回按钮
            //     $tools->disableListButton();        // 去掉跳转列表按钮
            // });
        });
    }

    private function getRechargeService()
    {
        return new Recharge();
    }

    public function postCurrency(Request $request)
    {
        $id     = $request->input('id');
        $status = $request->input('status');
        $remark = $request->input('remark');
        $remark = __('api.recharge.apply_remark', ['admin' => Admin::user()->name]) . $remark;
        
        $Recharge = new Recharge();
        $result    = $Recharge->deposit($id, $status, $remark);

        if ($result['status'] == 1) {
            $userModel = new UserModel();
            $user = UserModel::find($result['order']['uid']);
            if(!empty($user->email)) {
                $email = $user->email;
                $emailContent = __('api.public.deposits_notice', ['date' => date('Y-m-d H:i:s'), 'number' => (float) $result['order']['amount'], 'code' => $result['order']['code'], 'url' => env('APP_URL')]);
                $flag = Mail::send('auth.depositsMail',['emailContent'=>$emailContent],function($message) use($email){
                    $message ->to($email)->subject(__('api.public.deposits_email_subject'));
                });
            }
            
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

    public function index()
    {
        return $this->currency();
    }

    /**
     * 补单.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {
            $content->header('补单');
            $content->body($this->form());
        });
    }

    protected function form()
    {
        Form::extend('map', Form\Field\Map::class);
        Form::extend('editor', Form\Field\Editor::class);
        return Admin::form(DepositsOrdersModel::class, function (Form $form) {

            $form->disableDeletion();

            // 基本资料
            $form->display('id', 'ID');
            $form->select('currency', '充值货币')->options(CurrencyModel::where(['status' => 1, 'is_virtual' => 1])->get()->pluck('name', 'code'))->rules('required');
            $form->text('amount', '充值金额')->rules('required');
            $form->text('address', '转账地址')->rules('required');
            $form->text('depositAddress', '充值地址')->rules('required');
            $form->text('txid', '交易哈希txid')->rules('required');
            // $form->number('confirmations', '确认数')->rules('required');
            // $form->text('remark', '备注');
            // $form->radio('status', '状态')->options([3 => '等待足够确认数', 2 => '等待审核']);
            // $form->hidden('uid')->default(function($form){
            //   return 0;
            // });
            // $form->hidden('txout')->default(function($form){
            //   return true;
            // });
            // $form->hidden('done_at')->default(function($form){
            //   return date('Y-m-d H:i:s');
            // });

            // $form->display('created_at', '创建时间');
            // $form->display('updated_at', '更新时间');
            
            $form->disableReset();

            $form->saving(function (Form $form) {
                $currencyModel = new CurrencyModel();
                $currencyId = $currencyModel->getIdByCode($form->currency);
                if(empty($currencyId)) {
                    $error = new MessageBag([
                        'message' => '数字货币不存在',
                    ]);
                    return back()->with(compact('error'));
                }

                $depostAddress = new DepositsAddressesModel();
                $depostAddress = $depostAddress->setCurrency($currencyId);
                $uid = $depostAddress->getUidByAddress($form->depositAddress);
                if(is_null($uid)) {
                    $error = new MessageBag([
                        'message' => '充值地址不存在',
                    ]);
                    return back()->with(compact('error'));
                }

                $server = env('WALLET_HOST') . ':' . env('WALLET_PORT');
                $server = new Coin($server);
                $hash = $server->deposit($form->currency, $form->txid);
                if(empty($hash)) {
                    $error = new MessageBag([
                        'message' => '补单出错，请查看参数',
                    ]);
                    return back()->with(compact('error'));
                }

                return redirect('/admin/deposits/currency');
                $form->uid = $uid;
                $form->currency = $currencyId;
                unset($form->depositAddress);

            });
        });
    }

    protected function messageBag($message)
    {
        $error = new MessageBag([
            'message' => $message,
        ]);

        return back()->with(compact('error'));
    }

    protected function _getVerifyCodeModel() 
    {
        return new VerifyCodeModel();
    }
}
