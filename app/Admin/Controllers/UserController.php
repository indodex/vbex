<?php
namespace App\Admin\Controllers;

use App\Admin\Controllers\AdminController as Controller;
use App\Admin\Extensions\Tools\UserGender;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Traits\AdminBuilder;

// use Tests\Models\Tag;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

use App\Models\UserCertificationModel as Cer;
use App\Services\Accounts;

use App\Admin\Extensions\Certification;

class UserController extends Controller
{
    use ModelForm;

    protected $_params;
    /**
     * Index interface.
     *
     * @return Content
     */
    public function index(Request $request)
    {
    	$this->_params = $request->all();
        return Admin::content(function (Content $content) {
            $content->header('用户管理');
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
            $content->header('Create user');
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
        return Admin::grid(User::class, function (Grid $grid) {
        	$grid->model()->where('is_delete', 0);
            $grid->model()->orderBy('id', 'DESC');

            $grid->id('ID')->sortable();
            $grid->name('姓名');
            $grid->column('用户信息')->display(function () {
                return "E-mail: {$this->email}<br/> Mobile: {$this->mobile}";
            });
            $grid->id_card('身份证')->display(function ($currency_total) {
                $cer = (new Cer())->getInfoByUid($this->id);
                if(!empty($cer)) {
                    return $cer->papers_number;
                } else {
                    return '';
                }
                
            });

            $states = [
                'on'  => ['value' => 0, 'text' => '否', 'color' => 'success'],
                'off' => ['value' => 1, 'text' => '是', 'color' => 'danger'],
            ];
            $grid->is_lock('锁定')->switch($states);
            $grid->is_freeze('冻结')->switch($states);
            // $grid->money_total('现金');
            // $grid->currency_total('总资产')->display(function ($currency_total) {
            //     $sum = (new Accounts())->getAccountFormatToUSD($this->id);
            //     if($sum > 0) {
            //         return my_format_money($sum) . '万';
            //     } else {
            //         return 0;
            //     }
            // });
            $grid->register_ip('注册IP');
            // $grid->full_name();
            // $grid->avatar()->display(function ($avatar) {
            //     return "<img src='{$avatar}' />";
            // });
            // $grid->profile()->postcode('Post code');
            // $grid->profile()->address();
            // $grid->position('Position');
            // $grid->profile()->color();
            // $grid->profile()->start_at('开始时间');
            // $grid->profile()->end_at('结束时间');
            // $grid->column('column1_not_in_table')->display(function () {
            //     return 'full name:'.$this->full_name;
            // });
            // $grid->column('column2_not_in_table')->display(function () {
            //     return $this->email.'#'.$this->profile['color'];
            // });
            // $grid->tags()->display(function ($tags) {
            //     $tags = collect($tags)->map(function ($tag) {
            //         return "<code>{$tag['name']}</code>";
            //     })->toArray();
            //     return implode('', $tags);
            // });
            $grid->created_at('注册时间');
            // $grid->updated_at();
            $grid->filter(function ($filter) {
            	// 去掉默认的id过滤器
    			$filter->disableIdFilter();
                $filter->like('name', '姓名');
                $filter->like('email', '电子邮件地址');
                // $filter->equal('is_verified', '认证');
                $filter->equal('is_freeze', '冻结');
                // $filter->equal('is_lock', '锁币');
                // $filter->equal('is_delete', '锁币');
                // $filter->like('is_lock');
                // $filter->like('is_freeze');
                // $filter->like('is_verified');
                

                // $filter->like('profile.postcode');
                // $filter->between('profile.start_at')->datetime();
                // $filter->between('profile.end_at')->datetime();
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
    protected function forms()
    {
        Form::extend('map', Form\Field\Map::class);
        Form::extend('editor', Form\Field\Editor::class);
        return Admin::form(User::class, function (Form $form) {
            $form->disableDeletion();
            $form->display('id', 'ID');
            $form->text('name', '姓名');
            $form->email('email','电子邮箱地址')->rules('required');
            $form->mobile('mobile','手机号');
            $form->id_card('id_card','身份证号');
            $form->image('avatar','头像')->help('上传头像', 'fa-image');

            $form->password('password', trans('admin.password'))->rules('required|confirmed')->default(function ($form) {
                    return $form->model()->password;
                });
            $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
                ->default(function ($form) {
                    return $form->model()->password;
                });
            $form->ignore(['password_confirmation']);
            // $form->divide();
            // $form->text('profile.first_name');
            // $form->text('profile.last_name');
            // $form->text('profile.postcode')->help('Please input your postcode');
            // $form->textarea('profile.address')->rows(15);
            // $form->map('profile.latitude', 'profile.longitude', 'Position');
            // $form->color('profile.color');
            // $form->datetime('profile.start_at');
            // $form->datetime('profile.end_at');
            // $form->multipleSelect('tags', 'Tags')->options(Tag::all()->pluck('name', 'id')); //->rules('max:10|min:3');
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');

            $states = [
			    'on'  => ['value' => 0, 'text' => '否', 'color' => 'success'],
			    'off' => ['value' => 1, 'text' => '是', 'color' => 'danger'],
			];
            $form->switch('is_delete','删除')->states($states);
            $form->switch('is_verified','审核')->states($states);
            $form->switch('is_freeze','冻结')->states($states);
            $form->switch('is_lock','锁定')->states($states);

            // $form->html('<a html-field>html...</a>');

            $form->saving(function (Form $form) {
                if ($form->password && $form->model()->password != $form->password) {
                    $form->password = bcrypt($form->password);
                }
            });
        });
    }



    public function form()
    {
        return User::form(function (Form $form) {
            $form->model()->makeVisible('password');
            $form->tab('基础信息', function (Form $form) {

                $form->display('id');

                //$form->input('name')->rules('required');

                $form->text('name', '姓名');/*->rules('required')*/;
                $form->email('email','电子邮箱地址')->rules('required');
                $form->mobile('mobile','手机号');
                $form->id_card('id_card','身份证号');
                $form->image('avatar','头像')->help('上传头像', 'fa-image');
                $form->display('总资产')->default(function ($form) {
                    $sum = (new Accounts())->getAccountFormatToUSD($form->model()->id);
                    return $sum;
                });
                $form->ip('registere_ip',"注册IP")->default(function ($form) {
                        return $form->model()->registere_ip ? : $_SERVER['SERVER_ADDR'];
                    });
                $form->display('created_at');
                $form->display('updated_at');

                

            })->tab('用户控制', function (Form $form) {

                //$form->url('profile.homepage');
                // $form->money_total('现金');
                // $form->currency_total('总资产')->display(function ($currency_total) {
                //     return $currency_total > 0 ? $currency_total : '0.00';
                // });
                $states = [
                    'on'  => ['value' => 0, 'text' => '否', 'color' => 'success'],
                    'off' => ['value' => 1, 'text' => '是', 'color' => 'danger'],
                ];
                $form->switch('is_delete','删除')->states($states);
                // $form->switch('is_verified','审核')->states($states);
                $form->switch('is_freeze','冻结')->states($states);
                // $form->switch('is_lock','锁定')->states($states);
//                $form->map('profile.lat', 'profile.lng', 'Position')->useTencentMap();
                //$form->slider('profile.age', 'Age')->options(['max' => 50, 'min' => 20, 'step' => 1, 'postfix' => 'years old']);
                //$form->datetimeRange('profile.created_at', 'profile.updated_at', 'Time line');

//             })->tab('Sns info', function (Form $form) {

//                 $form->text('sns.qq');
//                 $form->text('sns.wechat')->rules('required');
//                 $form->text('sns.weibo');
//                 $form->text('sns.github');
//                 $form->text('sns.google');
//                 $form->text('sns.facebook');
//                 $form->text('sns.twitter');
//                 $form->display('sns.created_at');
//                 $form->display('sns.updated_at');

//             })->tab('Address', function (Form $form) {

//                 $form->select('address.province_id')->options(
//                     ChinaArea::province()->pluck('name', 'id')
//                 )
//                     ->load('address.city_id', '/demo/api/china/city')
//                     ->load('test', '/demo/api/china/city');

//                 $form->select('address.city_id')->options(function ($id) {
//                     return ChinaArea::options($id);
//                 })->load('address.district_id', '/demo/api/china/district');

//                 $form->select('address.district_id')->options(function ($id) {
//                     return ChinaArea::options($id);
//                 });

//                 $form->text('address.address');

            })->tab('Password', function (Form $form) {

                $form->password('password','密码')->rules('confirmed');
                $form->password('password_confirmation','重复密码');
                $form->saving(function (Form $form) {
                if ($form->password && $form->model()->password != $form->password) {
                    $form->password = bcrypt($form->password);
                }
            });
            });

            $form->ignore(['password_confirmation']);

            
        });
    }

    public function destroy($id)
    {
        $ids = explode(',', $id);

        if (User::deleteByIds(array_filter($ids))) {
            return response()->json([
                'status'  => true,
                'message' => trans('admin.delete_succeeded'),
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => trans('admin.delete_failed'),
            ]);
        }
    }

    public function certification(Request $request)
    {
        return Admin::content(function (Content $content) {
            $content->header('用户审核');
            $content->description('description');

            $grid = Admin::grid(Cer::class, function (Grid $grid) {
                $grid->model()->orderBy('id', 'DESC');

                $grid->id('ID')->sortable();
                $grid->uid('UID');
                $grid->user()->name('用户名');
                $grid->user()->email('邮箱');
                // $grid->advanced()->filepath('路径');
                $grid->name('姓名');
                
                $grid->status('认证')->display(function ($status) {
                    if($status == 0) {
                        return '<span class="label label-default">初级待审核</span>';
                    }else if($status == 1 && $this->advanced == 0) {
                        return '<span class="label label-success">初级认证通过</span>';
                    } else if($status == -1) {
                        return '<span class="label label-danger">初级认证未通过</span>';
                    } else if($this->advanced_status == 0 && $this->advanced > 0){
                        return '<span class="label label-default">高级待审核</span>';
                    }else if($this->advanced_status == -1 ){
                        return '<span class="label label-default">高级认证未通过</span>';
                    }else if($status == 1 && $this->advanced_status == 1){
                        return '<span class="label label-success">高级认证通过</span>';
                    }
                });

                $grid->created_at('时间');
                

                $grid->actions(function (Grid\Displayers\Actions $actions) {
                    $actions->disableEdit();
                    $actions->disableDelete();
                    $data = $actions->row;

                    // $actions->append(new CheckRow($actions->getKey()));
                    if ($data->status == 0) {
                        $actions->append(new Certification($data));
                    }else if($data->advanced_status == 0 && $data->advanced > 0){
                        $actions->append(new Certification($data));
                    }else if ($data->status == 1 && $data->advanced == 0) {
                        $actions->append('初级认证通过');
                    }else if($data->status == 1 && $data->advanced_status == 1){
                        $actions->append('高级认证通过');
                    }
                    
                });

                $grid->disableCreation();
                $grid->disableExport();
                $grid->disableRowSelector();


                $grid->filter(function($filter){

                    // 去掉默认的id过滤器
                    // $filter->disableIdFilter();

                    // 在这里添加字段过滤器
                    $filter->equal('uid', '用户ID');
                    $filter->equal('status','初级认证')->select(['0'=>'初级待审核','1'=>'初级审核通过','-1'=>'初级审核未通过']);
                    $filter->equal('advanced_status','高级认证')->select(['0'=>'高级待审核','1'=>'高级审核通过','-1'=>'高级审核未通过']);

                });
            });

            $content->body($grid);
        });
    }

    public function toCertification(Request $request)
    {
        $id = $request->input('id',0);
        $data['remark'] = $request->input('remark',null);
        $data['status'] = $request->input('status',0);
        $data['advanced_status'] = $request->input('advanced_status',0);

        $res = Cer::toCertification($id,$data);

        if ($res) {
            return response()->json([
                'status'  => true,
                'message' => '操作成功',
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => '操作失败',
            ]);
        }
    }
}
