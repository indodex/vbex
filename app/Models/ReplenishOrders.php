<?php

namespace App\Models;


use Encore\Admin\Form;
use Encore\Admin\Grid;
use Illuminate\Database\Eloquent\Model;

class ReplenishOrders extends Model
{
    protected $table = 'replenish_orders';

    protected $expire_at;

    protected $primaryKey = "id";        //指定主键
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid', 'deposits_id', 'currency', 'amount', 'address','txid','remark','status','created_at','updated_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];
    
    public function user()
    {
        $data= $this->hasOne('App\UserModel','id','uid');
        return $data;
    }

    public function currencyTo()
    {
        return $this->hasOne(CurrencyModel::class, 'id', 'currency');
    }

    public static function grid($callback)
    {
        return new Grid(new static, $callback);
    }

    public static function form($callback)
    {
        return new Form(new static, $callback);
    }

}
