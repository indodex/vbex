<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\ApiController as Controller;
use App\Models\LanguageModel;

use App\Models\ConfigModel;

class ConfigController extends Controller
{

    public function index(Request $request)
    {
        $land = $request->input('land', null);
        //dd($land);
        $configs = ConfigModel::find(1);
        $lang = LanguageModel::select('id', 'name', 'land_code', 'flag','package')->where('enable','1')->get();
        $data = array();
        foreach ($lang->toArray() as $key => $value) {
            # code...env('APP_URL').'/uploads'
            $value['flag'] = env('APP_URL').'/uploads/'.$value['flag'];
            $data[] = $value;
        }
        if(empty($configs)) {
            return $this->setStatusCode(404)->responseError(__('api.public.empty_data'));
        } else {
            $configs = $configs->toArray();
            unset($configs['id']);
            // $configs = array_column($configs, 'value', 'name');
            $configs['land'] = $data;
            return $this->responseSuccess($configs, __('api.public.success'));
        }
    }

}