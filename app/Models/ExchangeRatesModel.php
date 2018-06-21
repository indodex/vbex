<?php

namespace App\Models;

use App\Models\BaseModel as Model;
use Illuminate\Support\Str;

class ExchangeRatesModel extends Model
{
    protected $table = 'exchange_rates';

    protected $primaryKey = "id";        //指定主键

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'market', 'price',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function createRate($data) 
    {
    	return $this->insert([
            'market' => (string) $data['market'], 
            'price'  => (float) $data['price'], 
    	]);
    }

    // 保存提交的form数据
    public function save(array $options = [])
    {
        $attributes = $this->getAttributes();

        $attributes['market'] = $attributes['buy_currency'] . '_' . $attributes['sell_currency'];
        unset($attributes['buy_currency'],$attributes['sell_currency']);

        foreach ($attributes as $key => &$value) {
        	if(is_string($value) || empty($value)) {
        		$attributes[$key] = (string) $value;
        	} 
        }

        $this->setRatesAllCache('market:rate');
        if(!isset($attributes['id'])) {
            return $this->createRate($attributes);
        }

        $id = $attributes['id'];
        unset($attributes['id']);
        return $this->where('id', '=', $id)->update($attributes);
    }

    public function setRatesAllCache($key)
    {
        $data = $this->get();
        if(!empty($data)) {
            $data = $data->toArray();
            $this->setCacheByKey($key, $data);
        }
        return $data;
    }

    public function getRatesAllCache($key)
    {
        // $data = $this->getCacheByKey($key);
        // if(is_null($data)) {
            $data = $this->setRatesAllCache($key);
        // }
        return $data;
    }

    public function getRateByMarket($key, $market)
    {
        $data = $this->getRatesAllCache($key);
        $columns = array_column($data, 'id', 'market');
        if(!empty($columns[$market])) {
            $id = $columns[$market];
            $markets = array_column($data, 'price', 'id');
            return $markets[$id];
        } else {
            return null;
        }
    }

    public function getRateByMarketNew($market)
    {   
        if (empty($market)) {
            return false;
        }
        $row = $this->where(['market'=>$market])->first();
        if ($row) {
           return $row->price;
        }
        return false;
    }
}
