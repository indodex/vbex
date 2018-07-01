<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\AdminController as Controller;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

use Encore\Admin\Widgets\Table;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

use App\Models\CurrencyModel as Currency;
use App\Models\DepositsOrdersModel as DepositsOrders;
use App\Models\ReplenishOrders;
use App\Models\UserModel;
use App\Services\Deposits;
use App\Coin;

class ReplenishController extends Controller
{
    use ModelForm;

    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('补单列表');
            $content->description('description');

            $content->body($this->grid());
            // $content->body($this->popover());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('编辑补单');
            $content->description('description');

            $content->body($this->form()->edit($id));
        });
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(ReplenishOrders::class, function (Grid $grid) {
            $grid->model()->orderBy('id', 'DESC');
            // $grid->disableDeletion();
            $grid->id('ID')->sortable();
            $grid->currencyTo()->code('补单币种');
            $grid->txid('交易哈希')->display(function(){
                return "<a href='https://etherscan.io/tx/{$this->txid}' target='_blank'>{$this->txid}</a>";
            });
            $grid->amount('补单金额')->display(function(){
                return sprintf("%1.4f", $this->amount);
            });
            $grid->remark('备注');
            $grid->created_at('时间');

            // $grid->disableActions();
            $grid->actions(function ($actions){
                // $actions->disableDelete();
                // $actions->disableEdit();
            });
            $grid->disableExport();
            $grid->disableRowSelector();
        });
    }

    /**
     * 补单.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {
            $content->header('新增补单');
            $content->body($this->form());
        });
    }

    protected function form()
    {
        Form::extend('map', Form\Field\Map::class);
        Form::extend('editor', Form\Field\Editor::class);
        return Admin::form(ReplenishOrders::class, function (Form $form) {
            $form->disableDeletion();

            // 基本资料
            $form->display('id', 'ID');
            $form->select('uid', '充值用户')->options(UserModel::where(['is_freeze' => 0, 'is_lock' => 0])->get()->pluck('email', 'id'))->rules('required');
            $form->select('currency', '充值货币')->options(Currency::where(['status' => 1, 'is_virtual' => 1])->get()->pluck('name', 'id'))->rules('required');
            $form->text('amount', '充值金额')->rules('required');
            $form->text('address', '转账地址')->rules('required');
            $form->text('txid', '交易哈希txid')->rules('required');
            $form->text('remark', '备注');
            $form->hidden('hash');
            $form->hidden('deposits_id');

            $form->disableReset();
            $form->saving(function (Form $form) {
                // $depostAddress = new DepositsAddressesModel();
                // $depostAddress = $depostAddress->setCurrency($currencyId);
                // $uid = $depostAddress->getUidByAddress($form->depositAddress);
                // if(is_null($uid)) {
                //     $error = new MessageBag([
                //         'message' => '充值地址不存在',
                //     ]);
                //     return back()->with(compact('error'));
                // }

                $coin = Currency::find($form->currency);
                // print_r($coin);exit;
                $hash = $this->getCoin()->deposit($coin->code, $form->txid);
                if(empty($hash)) {
                    $error = new MessageBag([
                        'message' => '补单出错，请查看参数',
                    ]);
                    return back()->with(compact('error'));
                }

                $depositsOrders = new DepositsOrders();
                $insertId = $depositsOrders->insertGetId([
                    'uid' => $form->uid,
                    'currency' => $form->currency,
                    'fee' => 0,
                    'amount' => $form->amount,
                    'address' => $form->address,
                    'txid' => $form->txid,
                    'confirmations' => 0,
                    'remark' => '充值补单',
                    'status' => 2,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                $form->hash = $hash??'';
                $form->remark = '充值补单';
                $form->deposits_id = $insertId;
            });

            $form->saved(function (Form $form) {

                with(new Deposits())->deposit($form->model()->deposits_id, 1, '补单成功');

            });
        });
    }

    private function getCoin()
    {
        $server = env('WALLET_HOST') . ':' . env('WALLET_PORT');
        return new Coin($server);
    }
}