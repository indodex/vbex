<?php
namespace App\Admin\Controllers;

use App\Admin\Controllers\AdminController as Controller;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
// use Tests\Models\Tag;
use App\Models\CategoriesModel;
use App\Models\CategoriesDataModel;
use App\Models\LanguageModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Admin\Extensions\LandSelected;
use Encore\Admin\Form\Builder;



class CategoriesController extends Controller
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
            $content->header('分类管理');
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
            $content->header('编辑分类');
            $content->description('description');
            $content->body($this->form()->edit($id));
        });
    }

    // public function store(Request $request)
    // {
    //     $data = Input::all();
    //     $cateModel = new CategoriesModel();
    //     $cate['parent_id'] = (int) $data['parent_id'];
    //     $cate['name'] = $data['name_land']['zh-cn'];
    //     if(empty($cate['name'])){
    //         return back()->withInput()->withErrors('主体中文分类名不能为空！');
    //     }
    //     $cate['order'] =  0;
    //     $cate['slug'] =  '';
    //     // dd($cate);
    //     $catId = CategoriesModel::insertGetId($cate);
    //     //$catId = $cateModel->createCategory($cate);
    //     if($catId){
    //         foreach ($data['name_land'] as $key => $value) {
    //             $landData['cid'] = $catId;
    //             $landData['name'] = $value;
    //             $landData['language'] = $key;
    //             CategoriesDataModel::create($landData);
    //         }
            
    //     }
    //     //return $this->form()->store();
    //     $this->redirectAfterStore();
    // }

    /**
     * Get RedirectResponse after store.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectAfterStore()
    {
        admin_toastr(trans('admin.save_succeeded'));

        $url = Input::get(Builder::PREVIOUS_URL_KEY) ?: $this->form()->resource(0);

        return redirect($url);
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {
            $content->header('创建分类');
            $content->body($this->form());
        });
    }

    // public function createData(){

    // }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
    	$_GET['is_delete'] = 0;
        return Admin::grid(CategoriesModel::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->name('分类名');
            $grid->created_at('创建时间');
            // $grid->updated_at();
            
            $grid->filter(function ($filter) {
            	// 去掉默认的id过滤器
    			$filter->disableIdFilter();
                $filter->like('name', '分类名');
            });
            $grid->disableExport();
            $grid->tools(function (Grid\Tools $tools) {
                $tools->append(new LandSelected('en'));
                $tools->batch(function (Grid\Tools\BatchActions $actions) {
                    $actions->disableDelete();
                });
            });
        });
    }


       // /**
    //  * Make a form builder.
    //  *
    //  * @return Form
    //  */
    protected function form()
    {
        Form::extend('map', Form\Field\Map::class);
        Form::extend('editor', Form\Field\Editor::class);
        return Admin::form(CategoriesModel::class, function (Form $form) {
            $form->disableDeletion();
            $form->display('id', 'ID');
            $catData =  CategoriesModel::all()->pluck('name', 'id');
            $catData = array_merge([0=>'顶级'], $catData->toArray()); 
            $form->select('parent_id', '上级分类')->options($catData);
            $form->text('name', '分类名');
            
            
            // $form->divide();
            // $form->text('profile.first_name');
            // $form->text('profile.last_name');
            // $form->text('profile.postcode')->help('Please input your postcode');
            // $form->textarea('profile.address')->rows(15);
            // $form->map('profile.latitude', 'profile.longitude', 'Position');
            // $form->color('profile.color');
            // $form->datetime('profile.start_at');
            // $form->datetime('profile.end_at');
            
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
        });
    }


    // public function update($id)
    // {
    //     $data = Input::all();
    //     //Input::setOption('name',111);
    //     foreach ($data['name_land'] as $key => $value) {
    //         $landData['cid'] = $id;
    //         $landData['name'] = $value;
    //         $landData['language'] = $key;
    //         CategoriesDataModel::updateOrCreate(array('cid'=>$id,'language'=>$key),$landData);
    //     }

    //     $cate['parent_id'] = (int) $data['parent_id'];
    //     $cate['name'] = $data['name_land']['zh-cn'];
    //     Input::merge(['name'=>$cate['name'],"name_land"=>null]);
    //     CategoriesModel::where('id',$id)->update($cate);
    //     return $this->form()->update($id);
    //     //$this->redirectAfterUpdate();
    // }

    //  /**
    //  * Get RedirectResponse after update.
    //  *
    //  * @return \Illuminate\Http\RedirectResponse
    //  */
    // public function redirectAfterUpdate()
    // {
    //     admin_toastr(trans('admin.update_succeeded'));

    //     $url = Input::get(Builder::PREVIOUS_URL_KEY) ?: $this->resource(-1);
    //     return redirect($url);
    // }
    // /**
    //  * Make a form builder.
    //  *
    //  * @return Form
    //  */
    // protected function form()
    // {
    //     Form::extend('map', Form\Field\Map::class);
    //     Form::extend('editor', Form\Field\Editor::class);
    //     return Admin::form(CategoriesModel::class, function (Form $form) {
    //         $form->disableDeletion();
    //         $form->display('id', 'ID');
    //         $form->select('parent_id', '上级分类')->options(CategoriesModel::all()->pluck('name', 'id'));
    //         //$form->text('name', '分类名');
            
    //         $lang = LanguageModel::select('id', 'name', 'land_code')->where('enable','1')->get();
    //         $editId = (int) \Request::route('category');
    //         $landValData = array();
    //         if($editId > 0){
    //             $landVal = CategoriesDataModel::where('cid',$editId)->get();

    //             foreach ($landVal as $value) {
    //                 $landValData[$value->language] = $value->name;
    //             }
    //         }
    //         foreach ($lang->toArray() as  $value) {
    //             $options[$value['land_code']] = $value['name'];
    //             if($editId > 0 && isset($landValData[$value['land_code']])){
    //                 $form->html('<div class="input-group">
    //                     <span class="input-group-addon"><i class="fa fa-pencil"></i></span>
    //                 <input type="text" id="name" name="name_land['.$value['land_code'].']" value="'.$landValData[$value['land_code']].'" class="form-control name" placeholder="输入 分类名">                    
    //             </div>', $label = '分类名_'.$value['name']);
    //             } else {
    //                 $form->html('<div class="input-group">
    //                     <span class="input-group-addon"><i class="fa fa-pencil"></i></span>
    //                 <input type="text" id="name" name="name_land['.$value['land_code'].']" value="" class="form-control name" placeholder="输入 分类名">                    
    //             </div>', $label = '分类名_'.$value['name']);
    //             }
                
    //         }
            
    //         // $form->divide();
    //         // $form->text('profile.first_name');
    //         // $form->text('profile.last_name');
    //         // $form->text('profile.postcode')->help('Please input your postcode');
    //         // $form->textarea('profile.address')->rows(15);
    //         // $form->map('profile.latitude', 'profile.longitude', 'Position');
    //         // $form->color('profile.color');
    //         // $form->datetime('profile.start_at');
    //         // $form->datetime('profile.end_at');
            
    //         $form->display('created_at', '创建时间');
    //         $form->display('updated_at', '更新时间');
    //     });
    // }

}
