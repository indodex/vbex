<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\AdminController as Controller;
use App\Models\NewsModel;
use App\Models\CategoriesModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use App\Admin\Extensions\LandSelected;
use Encore\Admin\Form\Builder;

class ButtomMenuController extends Controller
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
            $content->header('资讯管理');
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
            $content->header('创建资讯');
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
        return Admin::grid(NewsModel::class, function (Grid $grid) {
            $grid->model()->where('category_id', '<', 1);
            $grid->id('ID')->sortable();
            $grid->title('标题');
            $grid->status('状态')->display(function ($status) {
                return $status == 'ACTIVE' ? '发布' :'未发布';
            });
            $grid->created_at('创建时间');
            // $grid->updated_at();
            
            $grid->filter(function ($filter) {
            	// 去掉默认的id过滤器
    			$filter->disableIdFilter();
                $filter->like('title', '标题');
            });

            $grid->disablePagination();
            $grid->disableFilter();
            $grid->tools(function (Grid\Tools $tools) {
                $tools->append(new LandSelected('en','news'));
                $tools->batch(function (Grid\Tools\BatchActions $actions) {
                    $actions->disableDelete();
                    // $actions->disableCreateButton();
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
        return Admin::form(NewsModel::class, function (Form $form) {

            $form->disableDeletion();
            $form->display('id', 'ID');
            $catModel = new CategoriesModel();

            $routeData = \Request::route()->getAction();
            $land = 'cn';

            if(strpos($routeData['as'],'_en') !== false){
                $land = 'en';
            } else if(strpos($routeData['as'],'_tw')!== false){
                $land = 'tw';
            } else {
                $land = 'cn';
            }
            $catData = $catModel->getLandCategories($land);
            $optionsData = array();
            foreach ($catData as  $value) {
                $optionsData[$value['id']] = $value['name'];
            }
            //$form->select('category_id', '分类')->options($optionsData);
            $form->hidden('category_id')->default(function ($form) {
                return 0;
            });
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

//             $form->tools(function (Form\Tools $tools) {

//                 // 添加一个按钮, 参数可以是字符串, 或者实现了Renderable或Htmlable接口的对象实例
//                 //$tools->add(new LandSelected('en'));
//                 $tools->add('<div class="btn-group pull-right open" style="margin-right: 10px">
//     <a class="btn btn-sm btn-twitter"><i class="fa fa-language"></i> 语言</a>
//     <button type="button" class="btn btn-sm btn-twitter dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
//         <span class="caret"></span>
//         <span class="sr-only">Toggle Dropdown</span>
//     </button>
//     <ul class="dropdown-menu" role="menu">
//                 <li><a href="/admin/categories?land=zh-cn" target="_blank" class="export-selected">中国</a></li>
// <!--     <label class="btn btn-default btn-sm ">
//         <input type="radio" class="user-gender" value="zh-cn">中国
//     </label> -->
//             <li><a href="/admin/categories?land=zh-tw" target="_blank" class="export-selected">台湾</a></li>
// <!--     <label class="btn btn-default btn-sm ">
//         <input type="radio" class="user-gender" value="zh-tw">台湾
//     </label> -->
//             <li><a href="/admin/categories?land=en" target="_blank" class="export-selected">英文</a></li>
// <!--     <label class="btn btn-default btn-sm ">
//         <input type="radio" class="user-gender" value="en">英文
//     </label> -->
//         </ul>
// </div>');
//             });
        });
    }
}
