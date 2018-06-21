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
use Illuminate\Support\MessageBag;
use App\Models\CurrencyModel;



class HashController extends Controller
{

    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('哈希码设置');

            $form = new Form();
            $form->method('post');
            $setting = CurrencyModel::find(5);
            $settingArr = $setting->toArray();
            $form->number('recharge_number_audit', '最大金额')->help('每天用户生成充值码累计最大金额，生成充值码金额超过此限制会进行后台审核才能使用。')->default($settingArr['recharge_number_audit']);

            $form->setAction('hash/setting');
            
            $content->body(new Box('哈希码设置', $form));
        });
    }

    public function store(){
        $parameters = request()->except(['_pjax', '_token']);
        $setting = CurrencyModel::find(5);
        $setting->recharge_number_audit = $parameters['recharge_number_audit'];
        $setting->save();
        $paths = [];
        $message = "保存成功";
        return $this->backWithSuccess($paths, $message);
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