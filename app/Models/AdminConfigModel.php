<?php

namespace App\Models;

use App\Models\BaseModel as Model;

class AdminConfigModel extends Model
{
    protected $table = 'admin_config';

    protected $primaryKey = "id";        //指定主键

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    protected $currency;

    public function getConfigValue($name)
    {
        if(empty($name)) {
            return null;
        }
        $data = $this->where(['name' => $name])->first();
        return $data['value'];
    }

    public function getConfigFormatting($name, $googleSecret)
    {
        if(empty($name)) {
            return null;
        }
        $data = $this->where(['name' => $name])->first();

        if ($data['value']) {
            $config = explode(PHP_EOL, trim($data['value'], PHP_EOL));
            $list = [];
            foreach ($config as &$value) {
                $value = explode('|',str_replace(array("\r\n", "\r", "\n"), "", $value));
                
                $list[$value[0]] = $value;

                if (!$googleSecret && in_array($value[0],[1,3]) ) {
                    unset($list[$value[0]]);
                }
            }

            return $list;
        }

        return false;
    }

    public function getConfigString($name,$i)
    {
        if(empty($name) || empty($i)) {
            return null;
        }
        $data = $this->where(['name' => $name])->first();

        if ($data['value']) {
            $config = explode(PHP_EOL, trim($data['value'], PHP_EOL));
            $list = [];
            foreach ($config as &$value) {
                $value = explode('|',str_replace(array("\r\n", "\r", "\n"), "", $value));
                if ($i == $value[0]) {
                    return $value[1];
                }
            }

            return $list;
        }

        return false;
    }

    /**
     * 获取分页列表
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function getConfigs($cond, $select = ['*'])
    {

        $query = $this->newQuery();
        if(empty($cond)) {
            $query->where($cond);
        }

        $query->select($select);

        // DB::connection()->enableQueryLog(); // 开启查询日志  
          
        // DB::table('trades_orders'); // 要查看的sql  

        return $query->orderByDesc('id')->get();
          
        // $queries = DB::getQueryLog(); // 获取查询日志  
          
        // print_r($queries); // 即可查看执行的sql，传入的参数等等 
    }
}
