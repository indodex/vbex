<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel as Model;

class AdvertModel extends Model
{
    protected $table = 'advert';

    protected $expire_at;

    protected $primaryKey = "id";        //指定主键

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'order', 'image', 'url', 'is_delete', 'is_show'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function createAdvert($data) 
    {
        return $this->insert([
            'title'     => (string) $data['title'],
            'image'     => (string) $data['image'], 
            'url'       => (string) $data['url'], 
            'order'     => (int) $data['order'], 
            'status'    => (string) $data['status'],
            'is_delete' => 0,  
        ]);
    }

    public function updateAdvert($cond, $saveData) 
    {
    	if(empty($cond) || empty($saveData)) {
    		return null;
    	}

        return $this->where($cond)->update($saveData);
    }

    public function updateById($id, $saveData) 
    {
    	if(empty($id) || empty($saveData)) {
    		return null;
    	}
    	
    	$cond['id'] = (int) $id;
        return $this->where($cond)->update($saveData);
    }

    // 保存提交的form数据
    public function save(array $options = [])
    {
        $attributes = $this->getAttributes();

        if(!isset($attributes['id'])) {
            return $this->createAdvert($attributes);
        }

        $id = $attributes['id'];
        unset($attributes['id']);
        return $this->updateById($id, $attributes);
    }
}
