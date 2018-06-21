<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Alert;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Callout;
use Encore\Admin\Widgets\Form;
use Encore\Admin\Widgets\InfoBox;
use Encore\Admin\Widgets\Tab;
use Encore\Admin\Widgets\Table;
use App\Models\LanguageModel;
use App\Models\ConfigModel;
use Illuminate\Support\MessageBag;



class SettingController extends Controller
{
    //use ModelForm;

    // public function index()
    // {
    //     return Admin::content(function (Content $content) {
    //         $content->header('设置');

    //         $action = URL::current();
    //         $setting = ConfigModel::find(1);
    //         if(count($setting->toArray()) == 0) $icoData =  false;
    //         $content->row(view('admin/setting', compact('dbTypes', 'action','setting','icoData')));
    //     });
    // }


    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('设置');

            //$this->showFormParameters($content);

            $form = new Form();
            $form->method('post');
            $setting = ConfigModel::find(1);

            $states = [
                'on'  => ['value' => 1, 'text' => '开启', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '关闭', 'color' => 'danger'],
            ];


            $lockStates = [
                'on'  => ['value' => 1, 'text' => '叠加', 'color' => 'primary'],
                'off' => ['value' => 0, 'text' => '最大', 'color' => 'danger'],
            ];
            if($setting){
                $settingArr = $setting->toArray();
                $invite_rewards = $settingArr['rewards_ratio'] > 0  ? $settingArr['rewards_ratio'] * 100 : 0;
                $form->switch('invite_rewards', '邀请赠送')->states($states)->default($settingArr['invite_rewards']);
                $form->rate('rewards_ratio','赠送比率')->default($invite_rewards);
                
                $form->divide();
                $form->email('client_email','客服邮箱')->default($settingArr['client_email']);
                $form->email('cooperation_email','商务合作')->default($settingArr['cooperation_email']);
                $langOpt = LanguageModel::select('land_code','name')->where('enable',1)->get();
                $options = array();
                foreach ($langOpt->toArray() as  $value) {
                    $options[$value['land_code']] = $value['name'];
                }
                $form->select('default_land','默认语言')->options($options)->default($settingArr['default_land']);
                // $form->time('time');
                // $form->datetime('datetime');
                $form->divide();
                // $form->dateRange('date_start', 'date_end', 'Date range');
                // $form->timeRange('time_start', 'time_end', 'Time range');
                // $form->dateTimeRange('date_time_start', 'date_time_end', '时间范围');
                $form->number('change_phone', '手机绑定')->default($settingArr['change_phone'])->help('用户绑定手机修改后锁定账号时间，按分钟计算0为不锁定');
                $form->number('change_google', '谷歌验证')->default($settingArr['change_google'])->help('用户谷歌验证修改后锁定账号时间，按分钟计算0为不锁定');
                $form->number('change_trade', '交易密码')->default($settingArr['change_trade'])->help('用户交易密码修改后锁定账号时间，按分钟计算0为不锁定');
                
                $form->switch('lock_type', '锁定方式')->default($settingArr['lock_type'])->states($lockStates)->default($settingArr['lock_type'])->help('用户安全设定，叠加为多个修改后锁定时间为叠加，最大为取最大单项最大锁定时间');

                $form->divide();

                $form->switch('recharge_code_switch', '哈希码开关')->states($states)->default($settingArr['recharge_code_switch'])->help('个人中心哈希码充值导航开关');
            } else {
                $form->switch('invite_rewards', '邀请赠送')->states($states);
                $form->rate('rewards_ratio','赠送比率');
                
                $form->divide();
                $form->email('client_email','客服邮箱')->default('287517746@qq.com');
                $form->email('cooperation_email','商务合作');
                $langOpt = LanguageModel::select('land_code','name')->where('enable',1)->get();
                $options = array();
                foreach ($langOpt->toArray() as  $value) {
                    $options[$value['land_code']] = $value['name'];
                }
                $form->select('default_land','默认语言')->options($options);
                // $form->time('time');
                // $form->datetime('datetime');
                $form->divide();
                // $form->dateRange('date_start', 'date_end', 'Date range');
                // $form->timeRange('time_start', 'time_end', 'Time range');
                // $form->dateTimeRange('date_time_start', 'date_time_end', '时间范围');
                $form->number('change_phone', '手机绑定')->help('用户绑定手机修改后锁定账号时间，按分钟计算0为不锁定');
                $form->number('change_google', '谷歌验证')->help('用户谷歌验证修改后锁定账号时间，按分钟计算0为不锁定');
                $form->number('change_trade', '交易密码')->help('用户交易密码修改后锁定账号时间，按分钟计算0为不锁定');
                
                $form->switch('lock_type', '锁定方式')->states($lockStates)->default('1')->help('用户安全设定，叠加为多个修改后锁定时间为叠加，最大为取最大单项最大锁定时间');

                $form->divide();

                $form->switch('recharge_code_switch', '哈希码开关')->states($states)->default('0')->help('个人中心哈希码充值导航开关');

                $form->setAction('setting');
            }
            
            
            $content->body(new Box('设置', $form));
        });
    }

    // public function index()
    // {
    //     return Admin::content(function (Content $content) {
    //         $content->header('Tabs');
    //         $content->description('Description...');

    //         //$this->showFormParameters($content);

    //         $tab = new Tab();

    //         $form = new Form();

    //         $form = new Form();
    //         $form->method('post');
    //         $setting = configModel::find(1);

    //         $states = [
    //             'on'  => ['value' => 1, 'text' => '开启', 'color' => 'success'],
    //             'off' => ['value' => 0, 'text' => '关闭', 'color' => 'danger'],
    //         ];


    //         $lockStates = [
    //             'on'  => ['value' => 1, 'text' => '叠加', 'color' => 'primary'],
    //             'off' => ['value' => 0, 'text' => '最大', 'color' => 'danger'],
    //         ];
    //         if($setting){
    //             $settingArr = $setting->toArray();
    //             $invite_rewards = $settingArr['rewards_ratio'] > 0  ? $settingArr['rewards_ratio'] * 100 : 0;
    //             $form->switch('invite_rewards', '邀请赠送')->states($states)->default($settingArr['invite_rewards']);
    //             $form->rate('rewards_ratio','赠送比率')->default($invite_rewards);
                
    //             $form->divide();
    //             $form->email('client_email','客服邮箱')->default($settingArr['client_email']);
    //             $form->email('cooperation_email','商务合作')->default($settingArr['cooperation_email']);
    //             $langOpt = LanguageModel::select('land_code','name')->where('enable',1)->get();
    //             $options = array();
    //             foreach ($langOpt->toArray() as  $value) {
    //                 $options[$value['land_code']] = $value['name'];
    //             }
    //             $form->select('default_land','默认语言')->options($options)->default($settingArr['default_land']);
    //             // $form->time('time');
    //             // $form->datetime('datetime');
    //             $form->divide();
    //             // $form->dateRange('date_start', 'date_end', 'Date range');
    //             // $form->timeRange('time_start', 'time_end', 'Time range');
    //             // $form->dateTimeRange('date_time_start', 'date_time_end', '时间范围');
    //             $form->number('change_phone', '手机绑定')->default($settingArr['change_phone'])->help('用户绑定手机修改后锁定账号时间，按分钟计算0为不锁定');
    //             $form->number('change_google', '谷歌验证')->default($settingArr['change_google'])->help('用户谷歌验证修改后锁定账号时间，按分钟计算0为不锁定');
    //             $form->number('change_trade', '交易密码')->default($settingArr['change_trade'])->help('用户交易密码修改后锁定账号时间，按分钟计算0为不锁定');
                
    //             $form->switch('lock_type', '锁定方式')->default($settingArr['lock_type'])->states($lockStates)->default($settingArr['lock_type'])->help('用户安全设定，叠加为多个修改后锁定时间为叠加，最大为取最大单项最大锁定时间');
    //         } else {
    //             $form->switch('invite_rewards', '邀请赠送')->states($states);
    //             $form->rate('rewards_ratio','赠送比率');
                
    //             $form->divide();
    //             $form->email('client_email','客服邮箱')->default('287517746@qq.com');
    //             $form->email('cooperation_email','商务合作');
    //             $langOpt = LanguageModel::select('land_code','name')->where('enable',1)->get();
    //             $options = array();
    //             foreach ($langOpt->toArray() as  $value) {
    //                 $options[$value['land_code']] = $value['name'];
    //             }
    //             $form->select('default_land','默认语言')->options($options);
    //             // $form->time('time');
    //             // $form->datetime('datetime');
    //             $form->divide();
    //             // $form->dateRange('date_start', 'date_end', 'Date range');
    //             // $form->timeRange('time_start', 'time_end', 'Time range');
    //             // $form->dateTimeRange('date_time_start', 'date_time_end', '时间范围');
    //             $form->number('change_phone', '手机绑定')->help('用户绑定手机修改后锁定账号时间，按分钟计算0为不锁定');
    //             $form->number('change_google', '谷歌验证')->help('用户谷歌验证修改后锁定账号时间，按分钟计算0为不锁定');
    //             $form->number('change_trade', '交易密码')->help('用户交易密码修改后锁定账号时间，按分钟计算0为不锁定');
                
    //             $form->switch('lock_type', '锁定方式')->states($lockStates)->default('1')->help('用户安全设定，叠加为多个修改后锁定时间为叠加，最大为取最大单项最大锁定时间');
    //         }

    //         $tab->add('基础设置', $form);

    //         $box = new Box('Second box', '<p>Lorem ipsum dolor sit amet</p><p>consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua</p>');
    //         $tab->add('Box', $form);

    //         $headers = ['Id', 'Email', 'Name', 'age', 'Company'];
    //         $rows = [
    //             [1, 'labore21@yahoo.com', 'Ms. Clotilde Gibson', 25, 'Goodwin-Watsica'],
    //             [2, 'omnis.in@hotmail.com', 'Allie Kuhic', 28, 'Murphy, Koepp and Morar'],
    //             [3, 'quia65@hotmail.com', 'Prof. Drew Heller', 35, 'Kihn LLC'],
    //             [4, 'xet@yahoo.com', 'William Koss', 20, 'Becker-Raynor'],
    //             [5, 'ipsa.aut@gmail.com', 'Ms. Antonietta Kozey Jr.', 41, 'MicroBist'],
    //         ];

    //         $table = new Table($headers, $rows);
    //         $tab->add('Table', $table);

    //         $content->row($tab);
    //     });
    // }


    public function store(){
        $parameters = request()->except(['_pjax', '_token']);
        $data = $this->formatData($parameters);
        $setting = ConfigModel::find(1);
        if(!$setting){
            $setting = ConfigModel::create($data);
        } else {
            $setting->invite_rewards = $data['invite_rewards'];
            $setting->rewards_ratio = $data['rewards_ratio'];
            $setting->client_email = $data['client_email'];
            $setting->invite_rewards = $data['invite_rewards'];
            $setting->rewards_ratio = $data['rewards_ratio'];
            $setting->client_email = $data['client_email'];
            $setting->cooperation_email = $data['cooperation_email'];
            $setting->default_land = $data['default_land'];
            $setting->change_phone = $data['change_phone'];
            $setting->change_google = $data['change_google'];
            $setting->change_trade = $data['change_trade'];
            $setting->lock_type = $data['lock_type'];
            $setting->recharge_code_switch = $data['recharge_code_switch'];
            $setting->save();
        }
        $paths = [];
        $message = "保存成功";
        return $this->backWithSuccess($paths, $message);
    }


    public function formatData($params){
        $data['invite_rewards'] = $params['invite_rewards'] == 'on' ? 1:0;
        $data['rewards_ratio'] = floatval($params['rewards_ratio']) > 0 ? floatval($params['rewards_ratio']) / 100 : 0 ;
        $data['client_email'] = $params['client_email'];
        $data['cooperation_email'] = $params['cooperation_email'];
        $data['default_land'] = $params['default_land'] ? $params['default_land'] : 'en';
        $data['change_phone'] = intval($params['change_phone']);
        $data['change_google'] = intval($params['change_google']);
        $data['change_trade'] = intval($params['change_trade']);
        $data['lock_type'] = $params['lock_type'] == 'on' ? 1:0;
        $data['recharge_code_switch'] = $params['recharge_code_switch'] == 'on' ? 1:0;
        return $data;
    }



    protected function backWithException(\Exception $exception)
    {
        $error = new MessageBag([
            'title'   => 'Error',
            'message' => $exception->getMessage(),
        ]);

        return back()->withInput()->with(compact('error'));
    }

    protected function backWithSuccess($paths, $message)
    {
        $messages = [];

        foreach ($paths as $name => $path) {
            $messages[] = ucfirst($name).": $path";
        }

        $messages[] = "<br />$message";

        $success = new MessageBag([
            'title'   => 'Success',
            'message' => implode('<br />', $messages),
        ]);

        return back()->with(compact('success'));
    }

}