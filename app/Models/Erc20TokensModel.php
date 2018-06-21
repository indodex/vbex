<?php

namespace App\Models;

use App\Models\BaseModel as Model;

class Erc20TokensModel extends Model
{
	protected $table = 'erc20_tokens';

    protected $expire_at;

    protected $primaryKey = "currency";        //指定主键

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'currency', 'contract',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * 
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function checkCurrency($currency)
    {
        if(empty($currency)) {
            return null;
        }

        $cond['currency'] = $currency;
        return $this->where($cond)->first();
    }
}
