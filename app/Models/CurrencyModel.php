<?php

namespace App\Models;

use App\Models\BaseModel as Model;
use Encore\Admin\Traits\AdminBuilder;

class CurrencyModel extends Model
{

    use AdminBuilder;
    protected $table = 'currency';

    protected $expire_at;

    protected $primaryKey = "id";        //指定主键

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function createCurrency($data) 
    {
    	$this->fillable(array_keys($data));
        $id = $this->insertGetId($data);
        if($id > 0) {
            $this->setCacheData($id);
            $this->setAllCache($id);
            $this->setCurrenciesCache(0);
            $this->setCurrenciesCache(1);
            return $id;
        } else {
            return null;
        }
    }

    // 保存提交的form数据
    public function save(array $options = [])
    {
        $attributes = $this->getAttributes();

        $this->fillable(array_keys($attributes));
        if(!isset($attributes['id'])) {
            return $this->createCurrency($attributes);
        }

        $id = $attributes['id'];
        unset($attributes['id']);
        $this->where('id', '=', $id)->update($attributes);
        $this->setCacheData($id);
        $this->setAllCache($id);
        $this->setCurrenciesCache(0);
        $this->setCurrenciesCache(1);
    }

    public function getInfo($id) 
    {
        if(empty($id)) {
            return null;
        }
        return $this->where('id', $id)->first();
    }

    public function getSimpleName($id) 
    {
        if(empty($id)) {
            return null;
        }
        return $this->find($id, 'coin');
    }

    public function getAll()
    {
        return $this->where('status', '=', 1)->get();
    }

    public function getAll2()
    {
        return $this->get();
    }

    public function getAllCache()
    {
        $data = $this->getCacheByKey("{$this->table}@all");
        $data = false;
        if($data == null) {
            $data = $this->setAllCache();
        }
        return $data;
    }

    public function setAllCache($id = 0)
    {
        $list = null;
        $data = $this->getAll();
        if(!empty($data)){
            $data = $data->toArray();
            foreach ($data as $value) {
                $list[$value['id']] = $value;
            }
        }

        $this->setCacheByKey("{$this->table}@all", $list);
        return $list;
    }

    public function getIdByCode($code)
    {
        if(empty($code)) {
            return null;
        }
        $cond['code'] = $code;
        $cond['status'] = 1;
        $currency = $this->select(['id'])->where($cond)->first();

        if(!empty($currency)) {
            return $currency->id;
        } else {
            return null;
        }
    }

    public function getByCode($code)
    {
        if(empty($code)) {
            return null;
        }
        $cond['code'] = $code;
        $cond['status'] = 1;
        $currency = $this->where($cond)->first();

        if(!empty($currency)) {
            return $currency;
        } else {
            return null;
        }
    }

    /**
     * 获取所有虚拟币
     * @param  integer $isVirtual 是否虚拟币 1:虚拟币，0:真实货币
     * @return [type]             [description]
     */
    public function getCurrenciesByType($isVirtual = 0)
    {
        $cond['is_virtual'] = $isVirtual;
        $cond['status']     = 1;
        return $this->where($cond)->get();
    }

    /**
     * 获取虚拟获取缓存
     * @param  integer $isVirtual 是否虚拟币 1:虚拟币，0:真实货币
     * @return [type] [description]
     */
    public function getCurrenciesCache($isVirtual = 0)
    {
        $key = $isVirtual == 1 ? 'virtual' : 'real';
        $data = $this->getCacheByKey("{$this->table}@all_" . $key);
        if($data == null) {
            $data = $this->setCurrenciesCache($isVirtual);
        }
        return $data;
    }

    /**
     * 设置货币缓存
     * @param integer $isVirtual 是否虚拟币 1:虚拟币，0:真实货币
     */
    public function setCurrenciesCache($isVirtual = 0)
    {
        $list = null;
        $data = $this->getCurrenciesByType($isVirtual);
        if(!empty($data)){
            $data = $data->toArray();
            foreach ($data as $value) {
                $list[$value['id']] = $value;
            }
        }

        $key = $isVirtual == 1 ? 'virtual' : 'real';
        $this->setCacheByKey("{$this->table}@all_" . $key, $list);
        return $list;
    }

    public function getNotVirtual()
    {
        $cond['is_virtual'] = 0;
        $cond['status'] = 1;

        return $this->where($cond)->orderBy('id','DESC')->get();
    }
}
