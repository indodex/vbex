<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\AdminController as Controller;
use App\Models\AdvertModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Storage;

class AdvertController extends Controller
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
            $content->header('广告管理');
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
            $content->header('编辑广告');
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
            $content->header('新增广告');
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
        return Admin::grid(AdvertModel::class, function (Grid $grid) {
            $grid->model()->orderBy('id', 'DESC');
            $grid->id('ID')->sortable();
            $grid->order('排序')->sortable();
            $grid->title('广告标题');
            $grid->url('跳转地址')->display(function($url) {
                if($url) {
                    return '<a target="_blank" href="'.$url.'" title="'.$url.'">查看地址</a>';
                }
            });
            $grid->image('图片')->display(function($image) {
                if($image) {
                    $img = Storage::disk('public')->url($image);
                    return '<img src="'.$img.'" width="150" height="150"/>';
                }
            });
            $grid->status('状态')->display(function($status) {
            	return $status == 'ACTIVE' ? '<span class="btn btn-xs btn-danger">显示</span>' : '隐藏';
            });
            $grid->created_at('创建时间');
            
            $grid->filter(function ($filter) {
            	// 去掉默认的id过滤器
    			$filter->disableIdFilter();
                $filter->like('title', '广告标题');
            });

            $grid->tools(function (Grid\Tools $tools) {
                $tools->batch(function (Grid\Tools\BatchActions $actions) {
                    $actions->disableDelete();
                });
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
        return Admin::form(AdvertModel::class, function (Form $form) {

            $form->disableDeletion();
            $form->display('id', 'ID');
            $form->text('title', '广告标题');
            $form->image('image', '封面图片')->removable();
            $form->url('url', '跳转地址');
            $form->number('order', '排序')->value(0);

            $states = [
			    'on'  => ['value' => 'ACTIVE', 'text' => '发布', 'color' => 'success'],
			    'off' => ['value' => 'INACTIVE', 'text' => '不发布', 'color' => 'danger'],
			];
            $form->switch('status', '发布状态')->states($states);

   			$form->saving(function (Form $form) {
			    if($form->status == 'on') {
			    	$form->status = 'ACTIVE';
			    } else {
			    	$form->status = 'INACTIVE';
			    }
			});
        });
    }
}