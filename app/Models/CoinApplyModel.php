<?php

namespace App\Models;

use App\Models\BaseModel as Model;
use Illuminate\Support\Str;

class CoinApplyModel extends Model
{
    protected $table = 'currency_apply';

    protected $expire_at;

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

    public function createApply($data) 
    {
        $this->fillable(array_keys($data));
    	return $this->insertGetId($data);
    }

    // 保存提交的form数据
    public function save(array $options = [])
    {
        $attributes = $this->getAttributes();
        foreach ($attributes as $key => &$value) {
        	if(is_string($value) || empty($value)) {
        		$attributes[$key] = (string) $value;
        	} 
        }

        if(!isset($attributes['id'])) {
            return $this->createApply($attributes);
        }

        $id = $attributes['id'];
        unset($attributes['id']);
        return $this->where('id', '=', $id)->update($attributes);
    }
}
