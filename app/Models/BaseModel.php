<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use DB;

class BaseModel extends Model
{
    protected $expire_at;

    protected $primaryKey = "id";        //指定主键

    protected $cacheVersion;

    protected $cacheMinutes;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->cacheVersion = config('cache.cache_version');

        $this->cacheMinutes = config('cache.cache_minutes');
    }

    /**
     * 添加缓存
     * @param [type]  $id   [description]
     * @param boolean $data [description]
     */
    public function setCacheData($id, $data = false) {
        if(!$data) {
            $data = DB::table($this->table)->where("{$this->primaryKey}", '=', $id)->first();
            $data = !empty($data) ? $data : null;
        }
        Cache::put("{$this->table}@{$this->primaryKey}.{$this->cacheVersion}:{$id}", $data, $this->cacheMinutes);
        return $data;
    }

    /**
     * 获取缓存
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getCacheData($id) {
        $data = Cache::get("{$this->table}@{$this->primaryKey}.{$this->cacheVersion}:{$id}");
        if($data == null) {
            $data = $this->setCacheData($id);
        }
        return $data;
    }

    /**
     * 获取缓存
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function clearCacheData($id) {
        Cache::forget("{$this->table}@{$this->primaryKey}.{$this->cacheVersion}:{$id}");
    }

    public function setCacheByKey($key, $value, $minutes = 0)
    {
        $minutes = $minutes > 0 ? $minutes : $this->cacheMinutes;
        return Cache::put($key, $value, $minutes);
    }

    public function getCacheByKey($key)
    {
        return Cache::get($key);
    }

    public function clearCacheByKey($key)
    {
        return Cache::forget($key);
    }

    /**
     * 获取分页列表
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function getList($cond, $page, $pageSize = 10)
    {
        if(empty($cond)) {
            return null;
        }

        $query = $this->newQuery();
        if(isset($cond['whereIn'])) {
            $whereIn = $cond['whereIn'];
            foreach ($whereIn as $iKey => $iValue) {
                $query->whereIn($iKey, $iValue);
            }
            unset($cond['whereIn']);
        }

        if(isset($cond['orWhere'])) {
            $orWhere = $cond['orWhere'];
            foreach ($orWhere as $oKey => $oValue) {
                $query->orWhere($oKey, '=',$oValue);
            }
            
            unset($cond['orWhere']);
        }

        // DB::connection()->enableQueryLog(); // 开启查询日志  
          
        // DB::table('trades_orders'); // 要查看的sql  

        return $query->where($cond)->orderByDesc('id')->paginate($pageSize, ['*'], 'page', $page);
          
        // $queries = DB::getQueryLog(); // 获取查询日志  
          
        // print_r($queries); // 即可查看执行的sql，传入的参数等等 
    }
}
