<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\UserModel;
use App\Models\CategoriesModel;
use Illuminate\Http\Request;

class NewsModel extends Model
{
    protected $table = 'news_cn';

    protected $expire_at;

    protected $primaryKey = "id";        //指定主键

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'author_id', 'category_id', 'title', 'content', 'slug', 'status', 'visited',
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
            $this->table = 'news_en';
        } else if($land == 'tw'){
            $this->table = 'news_tw';
        } else {
            $this->table = 'news_cn';
        }
    }

    public function getRow($id)
    {   
        if (!$id) {
            return false;
        }
        return $this->where(['id'=>$id,'status'=>'ACTIVE'])->first();
    }

    public function createNews($data) 
    {
    	return $this->insert([
			'author_id'        => (int) $data['author_id'], 
			'category_id'      => (int) $data['category_id'], 
			'title'            => (string) $data['title'], 
            'description'      => (string) $data['description'],
			'content'             => (string) $data['content'], 
			'image'            => (string) (isset($data['image']) ? $data['image']:''), 
			'status'           => (string) $data['status'],
			'slug'             => '',  
			'visited'          => 0,
			// 'seo_title'        => (int) $data['seo_title'],
			// 'meta_description' => (int) $data['meta_description'],
			// 'meta_keywords'    => (int) $data['meta_keywords'],
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
            return $this->createNews($attributes);
        }

        $id = $attributes['id'];
        unset($attributes['id']);
        return $this->where('id', '=', $id)->update($attributes);
    }

    public function getList($cond, $page = 1, $pageSize = 10)
    {   
        $field = ['id','title','description','category_id','image','author_id','created_at'];
        return $this->where($cond)->orderBy('created_at','desc')->paginate($pageSize, $field, 'page', $page);
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'author_id');
    }

    public function category() : BelongsTo
    {
        return $this->belongsTo(CategoriesModel::class, 'category_id');
    }
}
