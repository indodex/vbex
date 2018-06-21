<?php

if (! function_exists('make_cards')) {
    /**
     * 生成哈希码
     *
     * @param string $version
     * @param string $name
     * @param string $params
     *
     * @return string
     */
    function make_cards($number, $numLen = 16)
    {
        $numLen  = $numLen;
        $pwdLen  = $numLen;
        $c       = $number;//生成100组卡号密码
        $sNumArr = range(0,9);
        $sPwdArr = array_merge($sNumArr,range('A','Z'));
        $cards   = array();

        for($x=0;$x<$c;$x++){
            // $tempNumStr = array();
            // for($i=0;$i< $numLen;$i++){
            //     $tempNumStr[] = array_rand($sNumArr);
            // }
            $tempPwdStr=array();
            for($i=0;$i<$pwdLen;$i++){
                $tempPwdStr[] = $sPwdArr[array_rand($sPwdArr)];
            }
            $cards[] = implode('',$tempPwdStr);
            // $cards[] = implode('',$tempNumStr);
        }
        array_unique($cards);
        return $cards;
    }
}

if (! function_exists('my_number_format')) {
    /**
     * 格式化价格
     *
     * @param string $number
     * @param string $decimals
     *
     * @return string
     */
    function my_number_format($number, $decimals = 2)
    {
        if($number > 0) {
            $number = (float) $number;
            $number = sprintf("%1.{$decimals}f", $number);
            return (float) $number;
        } else {
            return 0;
        }
        
    }
}

if (! function_exists('my_format_money')) {
    /**
     * 格式化价格
     *
     * @param string $number
     * @param string $decimals
     *
     * @return string
     */
    function my_format_money($money){
        if($money >= 10000){
         return (float) sprintf("%.2f", $money/10000);
        }else{
         return (float) $money;
        }
     }
}

if (! function_exists('array_to_object')) {
    
    function array_to_object($e){
        if( gettype($e)!='array' ) return;
        foreach($e as $k=>$v){
            if( gettype($v)=='array' || getType($v)=='object' )
                $e[$k]=(object)arrayToObject($v);
        }
        return (object)$e;
    }
}
     
if (! function_exists('object_to_array')) {
    
    function object_to_array($e){
        $e=(array)$e;
        foreach($e as $k=>$v){
            if( gettype($v)=='resource' ) return;
            if( gettype($v)=='object' || gettype($v)=='array' )
                $e[$k]=(array)objectToArray($v);
        }
        return $e;
    }
}

if (! function_exists('string_to_array')) {
    /**
     * 将字符串转换为数组
     *
     * @param string  $data 字符串
     * @return  array
     */
    function string_to_array($data) {
        return $data ? (is_array($data) ? $data : unserialize(stripslashes($data))) : array();
    }
}

if (! function_exists('array_to_string')) {
    /**
     * 将数组转换为字符串
     *
     * @param array $data 数组
     * @return  string
     */
    function array_to_string($data) {
        return $data ? addslashes(serialize($data)) : '';
    }
}

if (! function_exists('check_password')) {
    /**
    * 检查密码复杂度
    */
    function check_password($pwd) {
        if ($pwd == null) {
            return ['code' => 0, 'msg' => __('passwords.empty_password')];
        }
        $pwd = trim($pwd);
        if (!strlen($pwd) >= 6) {//必须大于6个字符
            return ['code' => 0, 'msg' => __('passwords.password')];
        }
        if (preg_match("/^[0-9]+$/", $pwd)) { //必须含有特殊字符
            return ['code' => 0, 'msg' => __('passwords.not_all_number')];
        }
        if (preg_match("/^[a-zA-Z]+$/", $pwd)) {
            return ['code' => 0, 'msg' => __('passwords.not_all_abc')];
        }
        if (preg_match("/^[0-9A-Z]+$/", $pwd)) {
            return ['code' => 0, 'msg' => __('passwords.format_error1')];
        }
        if (preg_match("/^[0-9a-z]+$/", $pwd)) {
            return ['code' => 0, 'msg' => __('passwords.format_error2')];
        }
        return ['code' => 1, 'msg' => __('passwords.pass')];
    }
}

if (! function_exists('get_config')) {
    /**
    * 获取配置文件
    */
    function get_config($name) {
        $data = \DB::table('admin_config')->where('name', $name)->first();
        if(!empty($data)) {
            return $data->value;
        } else {
            return null;
        }
    }
}

if (! function_exists('get_config')) {
    function get_brower_lang() {
        preg_match('/^([a-z\-]+)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);
        $lang = $matches[1];
        switch ($lang) {
            case 'zh-cn' :
                //header('Location: http://cn.test.com/');
                return "zh_CN";
                break;
            case 'zh-tw' :
                // header('Location: http://tw.test.com/');
                return "zh_TW";
                break;
            default:
                // header('Location: http://en.test.com/');
                return "en";
                break;
        }
    }
}
