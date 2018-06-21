<?php

namespace App\Models;

use App\Models\BaseModel as Model;
use Illuminate\Support\Str;

class MoneyModel extends Model
{
    protected $table = 'legal_money';

    protected $expire_at;

    protected $primaryKey = "id";        //指定主键

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'simple_name', 'status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function createMoney($data) 
    {
    	return $this->insert([
            'name'        => (string) $data['name'], 
            'simple_name' => (string) $data['simple_name'], 
            'status'      => (int) $data['status'], 
        ]);
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
            return $this->createMoney($attributes);
        }

        $id = $attributes['id'];
        unset($attributes['id']);
        return $this->where('id', '=', $id)->update($attributes);
    }
}
