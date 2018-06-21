<?php

namespace App\Models;

use App\Models\BaseModel as Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\CurrencyModel;
use DB;

class TradesCurrenciesModel extends Model
{

    protected $table = 'trades_currencies';

    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'updated_at'
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
    public function mainCurrency() : BelongsTo
    {
        return $this->belongsTo(CurrencyModel::class, 'main_currency');
    }

    /**
     * Log belongs to users.
     *
     * @return BelongsTo
     */
    public function exchangeCurrency() : BelongsTo
    {
        return $this->belongsTo(CurrencyModel::class, 'exchange_currency');
    }

    public function createCurrency($data) 
    {
        $this->fillable(array_keys($data));
        $res = $this->insertGetId($data);
        if($res) {
            return $res;
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

        $cond['id'] = $attributes['id'];
        unset($attributes['id']);
        return $this->where($cond)->update($attributes);
    }

    /**
     * 获取用户下所有提现记录
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function getAll($cond)
    {
        if(empty($cond)) {
            return null;
        }
        $cond['is_delete'] = 0;

        return $this->where($cond)->orderByDesc('id')->get();
    }

    public function getBuySell($id)
    {   
        if (!$id) {
            return null;
        }

        $cond['id']        = $id;
        $cond['status']    = 1;
        $cond['is_delete'] = 0;
        $data = $this->select('main_currency','exchange_currency')
                     ->where($cond)
                     ->first();

        if (!empty($data)) {
            return $data->toArray();
        }

        return null;
    }

    public function getMarket($main_currency, $exchange_currency)
    {   
        if (!$main_currency && $exchange_currency) {
            return null;
        }

        $cond['main_currency']     = $main_currency;
        $cond['exchange_currency'] = $exchange_currency;
        $cond['status']            = 1;
        $cond['is_delete']         = 0;
        $data = $this->where($cond)->first();

        if (!empty($data)) {
            return $data->toArray();
        }

        return null;
    }

    public function getBuyExchange($eId)
    {   
        if (!$eId) {
            return null;
        }

        $cond['exchange_currency'] = $eId;
        $cond['status']            = 1;
        $cond['is_delete']         = 0;
        $data = $this->select('main_currency','exchange_currency')
                     ->where($cond)
                     ->get();

        if (!empty($data)) {
            return $data;
        }

        return null;
    }

    public function getAllMarketCurrencies()
    {
        return $this->select('exchange_currency')->where(['status'=>1])->groupBy('exchange_currency')->get();

    }

    public static function deleteByIds($ids)
    {
        if(empty($ids)) {
            return false;
        }
        foreach($ids as $id) {
            if($id > 0) {
                TradesCurrenciesModel::where('id', '=', $id)->update(['is_delete'=>1]);
            }
        }
        return true;
    }
}
