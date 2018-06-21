<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\AdminController as Controller;
use App\Models\CoinApplyModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use App\Admin\Extensions\LandSelected;
use Encore\Admin\Form\Builder;
use Encore\Admin\Widgets\Table;
use App\Admin\Extensions\CoinsApply;

class CoinsApplyController extends Controller
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
            $content->header('上币审核管理');
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
            $content->header('编辑审核');
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
            $content->header('创建审核');
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
    	$_GET['is_delete'] = 0;
        return Admin::grid(CoinApplyModel::class, function (Grid $grid) {
            $grid->id('ID')->sortable();
            $grid->name('数字货币名称');
            $grid->code('货币编码');
            $grid->paper_url('白皮书')->display(function($paper_url){
                return "<a href='{$paper_url}' target='_blank'>查看</a>";
            });
            $grid->coin_url('项目地址')->display(function($coin_url){
                return "<a href='{$coin_url}' target='_blank'>查看</a>";
            });
            $grid->code_url('代码开源链接')->display(function($code_url){
                return "<a href='{$code_url}' target='_blank'>查看源码</a>";
            });
            $grid->status('状态')->display(function ($status) {
                if ($status == 0) {
                  return '<span class="label label-default">待审核</span>';
                } else if ($status == 1) {
                  return '<span class="label label-primary">已审核</span>';
                } else if ($status == -1) {
                  return '<span class="label label-info">不通过</span>';
                }
            });
            $grid->column('审核信息')->expand(function (){
                $id = $this->id;
                $profile['电子邮件地址'] = $this->email;
                $profile['微信号'] = $this->weixin;
                $profile['联系电话'] = $this->phone;
                $profile['联系人姓名与职称'] = $this->position;
                $profile['数字货币名称'] = $this->name;
                $profile['货币编码'] = $this->code;
                $profile['项目网址'] = $this->url ? '<a href="'.$this->coin_url.'" target="_blank"> 查看</a>' : '-';
                $profile['发行时间'] = $this->issue_time;
                $profile['发行总量'] = $this->issue_total;
                $profile['筹码分布'] = $this->jetton;
                $profile['数字货币类型'] = $this->coin_type;
                $profile['成本价'] = $this->ico_price;
                $profile['众筹记录'] = $this->ico_record;
                $profile['项目用途'] = $this->purpose;
                $profile['社区用户量'] = $this->user_number;
                $profile['发行国家'] = $this->issue_country;
                $profile['已上线交易平台'] = $this->bourse;
                $profile['团队介绍'] = $this->team;
                $profile['团队地址'] = $this->address;

                $profile['白皮书'] = $this->paper_url ? '<a href="'.$this->paper_url.'" target="_blank"> 查看</a>' : '-';
                $profile['代码开源链接'] = $this->code_url ? '<a href="'.$this->code_url.'" target="_blank"> 查看</a>' : '-';
                if ($this->status == 0) {
                    $profile['审核状态'] = '待审核';
                } else if ($this->status == 1) {
                    $profile['审核状态'] = '已审核';
                } else if ($this->status == -1) {
                    $profile['审核状态'] = '不通过';
                }

                return new Table([], $profile);
            }, '展开');
            $grid->created_at('创建时间');
            // $grid->updated_at();

            $grid->filter(function ($filter) {
            	// 去掉默认的id过滤器
    			$filter->disableIdFilter();
                $filter->like('title', '标题');
            });

            $grid->disableCreation();
            $grid->disableFilter();
            $grid->disableExport();
            // $grid->disableActions();
            $grid->tools(function (Grid\Tools $tools) {
                // $tools->append(new LandSelected('en','news'));
                $tools->batch(function (Grid\Tools\BatchActions $actions) {
                    $actions->disableDelete();
                });
            });
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableEdit();
                $actions->disableDelete();
                if($actions->row->status == 1) {
                    $actions->append('已审核');
                } else {
                    $actions->append(new CoinsApply($actions->row));
                }
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
        return Admin::form(CoinApplyModel::class, function (Form $form) {

            $form->disableDeletion();
            $form->display('id', 'ID');

            // $routeData = \Request::route()->getAction();
            $form->text('title', '文章名称');
            $form->textarea('description', '简介')->rows(3);
            $form->image('image', '封面图片');
            $form->editor('content', '内容');
            $form->hidden('author_id')->default(function ($form) {
                    return Admin::user()->id;
                });

            $states = [
			    'on'  => ['value' => 'ACTIVE', 'text' => '发布', 'color' => 'success'],
			    'off' => ['value' => 'INACTIVE', 'text' => '不发布', 'color' => 'danger'],
			];
            $form->switch('status', '发布状态')->states($states)->default('on');
   			$form->saving(function (Form $form) {
			    if($form->status == 'on') {
			    	$form->status = 'ACTIVE';
			    } else {
			    	$form->status = 'INACTIVE';
			    }
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

        $coinApply = CoinApplyModel::find($id);
        $coinApply->remark = $remark;
        $coinApply->status = $status;

        if ($coinApply->save()) {
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
