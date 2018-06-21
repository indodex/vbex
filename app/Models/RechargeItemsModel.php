<?php

namespace App\Models;

use App\Models\BaseModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RechargeItemsModel extends Model
{
    protected $table = 'recharge_items';

    protected $expire_at;

    protected $primaryKey = "id";        //指定主键

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'amount', 'enable',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function createItems($data)
    {
        $items['amount'] = (float) $data['amount'];
		$items['enable'] = (int) $data['enable'];
        return $this->insert($items);
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
            return $this->createItems($attributes);
        }

        $id = $attributes['id'];
        unset($attributes['id']);
        return $this->where('id', '=', $id)->update($attributes);
    }

    public function getInfo($id)
    {
        if(empty($id)) {
            return null;
        }
        return $this->where('id', '=', $id)->first();
    }
}
