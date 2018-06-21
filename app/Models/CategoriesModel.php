<?php

namespace App\Models;

use App\Models\BaseModel as Model;

use Illuminate\Http\Request;

class CategoriesModel extends Model
{
    protected $table = 'categories_cn';

    protected $expire_at;

    protected $primaryKey = "id";        //指定主键

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id', 'order', 'name', 'slug',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function __construct(array $attributes = [])
    {

        parent::__construct($attributes);
        $routeData = \Request::route()->getAction();
        if(isset($routeData['as']) && strpos($routeData['as'],'_en') !== false){
            $this->setTable('en');
        } else if(isset($routeData['as']) && strpos($routeData['as'],'_tw')!== false){
            $this->setTable('tw');
        } else {
            $this->setTable('cn');
        }
    }

    public function setTable($land = 'cn'){
        if($land == 'en'){
            $this->table = 'categories_en';
        } else if($land == 'tw'){
            $this->table = 'categories_tw';
        } else {
            $this->table = 'categories_cn';
        }
    }

    public function getLandCategories($land = 'cn'){
        $this->setTable($land);
        return $this->select( 'id','parent_id','name')->groupBy('order')->get();
    }

    public function createCategory($data) 
    {
        return $this->insert([
            'parent_id' => (int) $data['parent_id'], 
            'order'     => 0, 
            'slug'      => '',
            'name'      => (string) $data['name'],
        ]);
    }

    // 保存提交的form数据
    public function save(array $options = [])
    {
        $attributes = $this->getAttributes();

        if(!isset($attributes['id'])) {
            return $this->createCategory($attributes);
        }

        $id = $attributes['id'];
        unset($attributes['id']);
        $this->where('id', '=', $id)->update($attributes);
    }


    public function landName()
    {
         return $this->hasMany('App\Models\CategoriesDataModel','cid');
    }
}
