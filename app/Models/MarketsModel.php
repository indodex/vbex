<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\BaseModel as Model;
use App\Models\CurrencyModel;
use DB;

class MarketsModel extends Model
{
    protected $table = 'markets';

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

    /**
     * Log belongs to users.
     *
     * @return BelongsTo
     */
    public function currencyBuyTo() : BelongsTo
    {
        return $this->belongsTo(CurrencyModel::class, 'buy_currency');
    }

    /**
     * Log belongs to users.
     *
     * @return BelongsTo
     */
    public function currencySellTo() : BelongsTo
    {
        return $this->belongsTo(CurrencyModel::class, 'sell_currency');
    }

    public function createCurrency($data) 
    {
        $this->fillable(array_keys($data));
        $id = $this->insertGetId($data);
        if($id > 0) {
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
    }

    public function getAll($cond)
    {
        if(empty($cond)) {
            return null;
        }

        return $this->where($cond)->orderByDesc('id')->get();
    }

    public function getBuySell($id)
    {   
        if (!$id) {
            return false;
        }
        $data = $this->select('buy_currency','sell_currency')
                     ->where('id','=',$id)
                     ->where('status','=',1)
                     ->first()
                     ->toArray();

        if (!empty($data)) {
            return $data;
        }

        return false;
    }

    public function isMarket($buyCurrency, $sellCurrency)
    {
        if(empty($buyCurrency) || empty($sellCurrency)) {
            return null;
        }

        $cond['buy_currency'] = $buyCurrency;
        $cond['sell_currency'] = $sellCurrency;
        return $this->where($cond)->get()->isNotEmpty();
    }
}
