<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as Controller;
use Illuminate\Support\Facades\Cache;

use App\Models\CurrencyModel;
use App\Models\AdminConfigModel;

class ApiController extends Controller
{

    public $cacheKey;
	public $statusCode = 200;
    public $uid;

    public function __construct() 
    {
        // parent::__construct();
        $this->uid = $this->getUserId();
    }

    public function getStatusCode() 
    {
    	return $this->statusCode;
    }

    public function setStatusCode($statusCode) 
    {
    	$this->statusCode = $statusCode;
    	return $this;
    }

    public function checkAuth()
    {
        if(!$this->getUser()) {
            return $this->setStatusCode(404)->responseError(__('api.public.please_login'));
        }
        return false;
    }

    public function getUser() 
    {
        $user = auth()->guard('api')->user();
        if ($user) {
            $user->google_secret = !empty($user->google_secret) ? 1 : 0;
            $user->trade_code = !empty($user->trade_code) ? 1 : 0;
        }

        unset($user->password,$user->activation_code,$user->api_token);
        return $user;
    }

    public function getUserId() 
    {
        $user = $this->getUser();
        if(!is_null($user)) {
            return $user->id;
        }
    }

    public function responseNotFound($message = 'Not found')
    {
    	return $this->responseError($message);
    }

    public function responseError($message) {
    	return $this->response([
    		'code' => $this->getStatusCode(),
    		'message' => $message,
            'data' => []
    	]);
    }

    public function responseSuccess($data, $message = 'Success') {
    	return $this->response([
    		'code' => $this->getStatusCode(),
    		'message' => $message,
    		'data' => $data
    	]);
    }

    public function responseSuccess2($data, $message = 'Success') {
        // return response()->json($data);
        return response()->json([
            'code' => $this->getStatusCode(),
            'message' => $message,
            'data' => $data
        ]);
    }

    public function responseCookie($data, $message = 'Success') {
        $cookie = $data['cookie'];
        unset($data['cookie']);
        return $this->response([
            'code' => $this->getStatusCode(),
            'message' => $message,
            'data' => $data
        ])->cookie($cookie);
    }

    public function response($data) {
        $this->_convert_data($data);
        $data = $this->convertHump($data);
    	return response()->json($data);
    }

    public function formatPage($data){
        if(!is_array($data)){
            $data = $data->toArray();
        }
        $pageData['currentPage'] = $data['current_page'];          // 当前页面
        $pageData['perPage'] = $data['per_page'];                  // 每页显示条数
        $pageData['total'] = $data['total'];                        //总条数
        $pageData['lastPage'] = $data['last_page'];                 //最后页数
        $pageData['list'] = $data['data'];
        return $pageData;
        
    }

    public function getMarket($market)
    {
        $currencies = $this->getCurrencyModel()->getAllCache();
        $symbols = [];
        foreach ($currencies as $key => $value) {
            $symbols[] = $value['code'];
        }

        $markets = [];
        foreach ($symbols as $symbol) {
            foreach ($symbols as $sl) {
                if($sl != $symbol) {
                    $markets[strtolower($symbol).strtolower($sl)] = $symbol . '_' . $sl;
                }
            }
        }

        if (!empty($markets[$market])) {
            return $markets[$market];
        }
        return false;
    }

    /**
     * 设置倒计时
     * @param [type]  $key     [description]
     * @param integer $minutes [description]
     */
    public function setCountDown($key, $minutes = 1)
    {
        $seconds = $minutes * 60;
        $limitTime = time() + $seconds;
        return Cache::put('count_down@key:' . $key, $limitTime, $seconds);
    }

    /**
     * 获取倒计时
     * @param [type]  $key     [description]
     * @param integer $minutes [description]
     */
    public function getCountDown($key)
    {
        $limitTime = Cache::get('count_down@key:' . $key);
        if(empty($limitTime)) {
            return 0;
        }
        
        $limit = $limitTime - time();
        return $limit;
    }

    /**
     * 返回参数格式化
     * @param  [type] &$data [description]
     * @return [type]        [description]
     */
    protected function _convert_data(&$data)
    {
        if(!is_array($data)) {
            return $data;
        }
        foreach($data as $k => &$v) {
            if(is_array($v)) {
                $this->_convert_data($v);
            } else if(is_object($v)){
                $this->_convert_data($v);
            } else {
                $data[$k] = (string) $v;
            }
        }
    }

    /*
     * 下划线转驼峰
     */
    private function toCamelCase($str)
    {
        $array = explode('_', $str);  
        $result = '';  
        foreach($array as $value){  
            $result.= ucfirst($value);  
        }
        return lcfirst($result);
    }

    /*
     * 驼峰转下划线
     */
    private function toUnderScore($str){
        $array = array();  
        for($i=0;$i<strlen($str);$i++){  
            if($str[$i] == strtolower($str[$i])){  
                $array[] = $str[$i];  
            }else{  
                if($i>0){  
                    $array[] = '_';  
                }  
                $array[] = strtolower($str[$i]);  
            }  
        }
        $result = implode('',$array);  
        return $result;
    }

    private function convertHump(array $data){
        $result = [];
        foreach ($data as $key => $item) {
            if (is_array($item) || is_object($item)) {
                $result[$this->toCamelCase($key)] = $this->convertHump((array)$item);
            } else {
                $result[$this->toCamelCase($key)] = $item;
            }
        }
        return $result;
    }

    protected function _checkLock($key = '')
    {
        $key  = $key ? $key : $this->cacheKey;
        $lock = Cache::get('post_locked@key:' . $key);
        if($lock === null) {
            Cache::put('post_locked@key:' . $key, 1, 1);
            return false;
        }
        return true;
    }

    protected function _cleanLock($key = '') {
        $key  = $key ? $key : $this->_getLockKey();
        Cache::forget('post_locked@key:' . $key);
    }

    protected function _setLockKey($key = '') {
        $this->cacheKey = $key;
        return $this;
    }

    protected function _getLockKey($key = '') {
        return $this->cacheKey;
    }

    private function getCurrencyModel()
    {
        return new CurrencyModel();
    }

    public function getAdminConfigModel()
    {
        return new AdminConfigModel();
    }
}
