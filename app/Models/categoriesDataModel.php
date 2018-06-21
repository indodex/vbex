<?php

namespace App\Models;

use App\Models\BaseModel as Model;

class CategoriesDataModel extends Model
{
    protected $table = 'categories_data';

    protected $expire_at;

    protected $primaryKey = "id";        //指定主键

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cid', 'name', 'language',
    ];

}
