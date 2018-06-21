<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\AdminController as Controller;
use App\Models\LanguageModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class LanguageController extends Controller
{

    /**
     * Index interface.
     *
     * @return Content
     */
    use ModelForm;

    public function index(Request $request)
    {
        return Admin::content(function (Content $content) {
            $content->header('语言包管理');
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
            $content->header('Edit user');
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
            $content->header('新增语言包');
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
        //$_GET['is_delete'] = 0;
        return Admin::grid(LanguageModel::class, function (Grid $grid) {
            //$grid->model()->where('is_delete', 0);
            $grid->model()->orderBy('id', 'DESC');

            $grid->id('ID')->sortable();
            $grid->name('语言');
            $grid->land_code('编码');
            $grid->flag('国旗')->image();
            //$grid->image('flag');
            $states = [
                'on'  => ['value' => 1, 'text' => '启用', 'color' => 'primary'],
                'off' => ['value' => 0, 'text' => '关闭', 'color' => 'default'],
            ];
            $grid->enable('开启状态')->switch($states);
            $grid->filter(function ($filter) {
                // 去掉默认的id过滤器
                $filter->seo_title('SEO标题');
                $filter->seo_keywords('SEO简介');
            });

            $grid->actions(function ($actions) {
                // if ($actions->getKey() % 2 == 0) {
                //     $actions->prepend('<a href="/" class="btn btn-xs btn-danger">detail</a>');
                // }
            });

            // $grid->actions(function (Grid\Displayers\Actions $actions) {
            //     if ($actions->getKey() == 1) {
            //         $actions->disableDelete();
            //     }
            // });

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
        return Admin::form(LanguageModel::class, function (Form $form) {
            //$form->select('parent_id', '上级分类')->options(CategoriesModel::all()->pluck('name', 'id'));
            $form->disableDeletion();
            $form->display('id', 'ID');
            $form->text('name', '语言名称');
            $form->text('land_code', '语言编码');
            $form->image('flag', '国旗');
            $states = [
                'on'  => ['value' => 1, 'text' => '启用', 'color' => 'primary'],
                'off' => ['value' => 0, 'text' => '关闭', 'color' => 'default'],
            ];
            $form->text('package', '语言包URL');
            $form->switch('enable', '启用')->states($states);
            // $form->image('flag')->name(function ($file) {
            //     return 'flag_'.$file->land_code;
            // });
            $form->text('seo_title', 'SEO标题');
            $form->text('seo_keywords', 'SEO关键词');
            $form->text('seo_description', 'SEO简介');
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
        });
    }





}
