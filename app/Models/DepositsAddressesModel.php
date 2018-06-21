<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\BaseModel as Model;
use App\Models\CurrencyModel;
use App\User;

class DepositsAddressesModel extends Model
{
	protected $table = 'deposits_addresses';

    protected $expire_at;

    protected $primaryKey = "id";        //指定主键

    protected $currency;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid', 'currency', 'address', 'password', 'status',
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
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class, 'uid');
    }

    /**
     * Log belongs to users.
     *
     * @return BelongsTo
     */
    public function currencyTo() : BelongsTo
    {
        return $this->belongsTo(CurrencyModel::class, 'currency');
    }

    public function createAddress($data) 
    {
        if(empty($data)) {
            return null;
        }
        return $this->insertGetId([
            'uid'      => (int) $data['uid'], 
            'currency' => (int) $data['currency'], 
            'address'  => (string) $data['address'], 
            'password' => (string) $data['password'], 
            'status'   => 1,
        ]);
    }

    /**
     * 设置全局货币ID
     * @param [type] $currency [description]
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * 获取货币ID
     * @return [type] [description]
     */
    public function getCurrency()
    {
        return $this->currency;
    }



    public function getInfoByUid($uid)
    {
        if(empty($uid)) {
            return null;
        }

        return $this->where([
            'uid'      => (int) $uid,
            'currency' => (int) $this->getCurrency(),
            'status'   => 1,
        ])->first();
    }

    public function getInfo($id) 
    {
        if(empty($id)) {
            return null;
        }

        return $this->where('id', '=', $id)->first();
    }

    /**
     * 获取所有充值地址
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function getListByUid($uid, $page, $pageSize = 10)
    {
        if(empty($uid)) {
            return null;
        }

        $cond['uid'] = $uid;
        if($this->getCurrency()) {
            $cond['currency'] = $this->getCurrency();
        }

        return $this->where($cond)->orderByDesc('id')->simplePaginate($pageSize, ['*'], 'page', $page);
    }

    public function getUidByAddress($address) 
    {
        if(empty($address)) {
            return null;
        }

        if($this->getCurrency()) {
            $cond['currency'] = $this->getCurrency();
        }
        
        $cond['address'] = (string) $address;
        $cond['status'] = 1;
        $data = $this->where($cond)->first();

        if(!empty($data)) {
            return $data->uid;
        } else {
            return null;
        }
    }

    public function getAddressByUid($uid)
    {
        if(empty($uid)) {
            return null;
        }

        $data = $this->where([
            'uid'      => (int) $uid,
            'currency' => (int) $this->getCurrency(),
            'status'   => 1,
        ])->first();

        if(!empty($data)) {
            return $data;
        } else {
            return null;
        }
    }

}
