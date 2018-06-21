<?php
namespace App\Admin\Extensions;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;
use App\Models\LanguageModel;

class LandSelected extends AbstractTool
{ 
    protected $language;
    protected $type;

    public function __construct($language,$type='cate')
    {
        $this->language = $language;
        $this->type = $type;
    }

    protected function script()
    {
        //$url = Request::fullUrlWithQuery(['gender' => '_gender_']);

//         return <<<EOT

//             $('input:radio.user-gender').change(function () {

//                 var url = "$url".replace('_gender_', $(this).val());

//                 $.pjax({container:'#pjax-container', url: url });

//             });

// EOT;
    }

    public function render(){
        //Admin::script($this->script());
        $lang = LanguageModel::select('id', 'name', 'land_code','table_code')->where('enable','1')->get();
        $options = array();
        $url = $this->type == 'cate' ? '/admin/categories' : '/admin/news';
        foreach ($lang->toArray() as  $value) {
            $key = $value['table_code'];
            $options[$key] = $value['name'];
        }
        return view('admin.tools.LandSelected', ['options'=>$options,'url'=>$url]);
    }


    public function __toString()
    {
        return $this->render();
    }
}