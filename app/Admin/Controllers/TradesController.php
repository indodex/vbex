<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\AdminController as Controller;
use App\Models\TradesOrdersModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class TradesController extends Controller
{
    
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function entry(Request $request)
    {
        return Admin::content(function (Content $content) {
            $content->header('挂单');
            $content->description('description');

            $grid = Admin::grid(TradesOrdersModel::class, function (Grid $grid) {
                $grid->model()->orderBy('id', 'DESC');

                $grid->id('ID')->sortable();
                $grid->currencyBuyTo()->name('币种');
                $grid->user()->name('用户名称');
                $grid->num('委托数量')->display(function($num){
                    return (float) $num;
                });
                $grid->price('委托价格')->display(function($price){
                    return (float) $price;
                });
                $grid->successful_num('成交数量')->display(function($successful_num){
                    return (float) $successful_num;
                });
                $grid->successful_price('成交总价格')->display(function($successful_price){
                    return (float) $successful_price;
                });
                $grid->created_at('创建时间');
                $grid->status('操作')->display(function($status){
                    if($status == 0) {
                        return '已取消';
                    } else if($status == 1) {
                        return '已完成';
                    } else if($status == 2) {
                        return '部分成交';
                    } else if($status == 3) {
                        return '未成交';
                    }
                });
                
                $grid->actions(function (Grid\Displayers\Actions $actions) {
                    $actions->disableEdit();
                    $actions->disableDelete();
                    
                    // $actions->append(new CheckRow($actions->getKey()));
                });

                $grid->tools(function (Grid\Tools $tools) {
                    $tools->batch(function (Grid\Tools\BatchActions $actions) {
                        $actions->disableDelete();
                    });
                });

                $grid->disableCreation();
                $grid->disableExport();
                $grid->disableRowSelector();
                $grid->disableActions();
            });

            $content->body($grid);
        });
    }
    /**
     * Index interface.
     *
     * @return Content
     */
    public function buy(Request $request)
    {
        return Admin::content(function (Content $content) {
            $content->header('买单');
            $content->description('description');
            $content->body($this->grid());
        });
    }
    /**
     * Index interface.
     *
     * @return Content
     */
    public function sell(Request $request)
    {
        return Admin::content(function (Content $content) {
            $content->header('卖单');
            $content->description('description');
            $content->body($this->grid());
        });
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(TradesOrdersModel::class, function (Grid $grid) {

            $routeAction = \Route::current()->getActionName();
            $routeArr = explode('@', $routeAction);
            $actName = end($routeArr);

            $grid->id('ID')->sortable();
            if($actName == 'buy') {
                $grid->currencyBuyTo()->name('币种');
            } else {
                $grid->currencySellTo()->name('币种');
            }
            
            $grid->user()->name('用户名称');
            $grid->num('委托数量')->display(function($num){
                return (float) $num;
            });
            $grid->price('委托价格')->display(function($price){
                return (float) $price;
            });
            $grid->created_at('创建时间');
            $grid->status('操作')->display(function($status){
                if($status == 0) {
                    return '已取消';
                } else if($status == 1) {
                    return '已完成';
                } else if($status == 2) {
                    return '部分成交';
                } else if($status == 3) {
                    return '未成交';
                }
            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableEdit();
                $actions->disableDelete();
                
                // $actions->append(new CheckRow($actions->getKey()));
            });

            $grid->tools(function (Grid\Tools $tools) {
                $tools->batch(function (Grid\Tools\BatchActions $actions) {
                    $actions->disableDelete();
                });
            });

            $grid->disableCreation();
            $grid->disableExport();
            $grid->disableRowSelector();
            $grid->disableActions();

            if($actName == 'buy') {
                $grid->model()->where('buy_currency', '>', 0);
            } else {
                $grid->model()->where('sell_currency', '>', 0);
            }
        });
    }

}
