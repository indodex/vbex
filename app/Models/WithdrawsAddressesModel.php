<?php

namespace App\Models;

use App\Models\BaseModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\User;
use App\Models\CurrencyModel;

class WithdrawsAddressesModel extends Model
{
    protected $table = 'withdraws_addresses';

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
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class, 'uid');
    }

    public function createAddress($data) 
    {
        if(empty($data)) {
            return null;
        }
        
        $this->fillable(array_keys($data));
        return $this->insertGetId([
            'uid'        => (int) $data['uid'], 
            'currency'   => (int) $data['currency'], 
            'name'       => (string) $data['name'], 
            'address'    => (string) $data['address'], 
            'is_default' => (int) $data['is_default'], 
            'status'     => 1,
        ]);
    }

    public function getInfo($id) 
    {
        if(empty($id)) {
            return null;
        }

        return $this->where('id', '=', $id)->first();
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
            'currency' => (int) $this->getCurrency()
        ])->first();
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
        $cond['status'] = 1;
        if($this->getCurrency()) {
            $cond['currency'] = $this->getCurrency();
        }

        return $this->where($cond)->orderByDesc('id')->simplePaginate($pageSize, ['*'], 'page', $page);
    }

    public function getInfoByAddress($uid, $address)
    {
        if(empty($uid) || empty($address)) {
            return null;
        }

        return $this->where([
            'uid'      => (int) $uid,
            'currency' => (int) $this->getCurrency(),
            'address'  => (string) $address,
        ])->first();
    }

    public function editAddress($cond, $saveData) 
    {
        if(empty($saveData) || empty($cond)) {
            return null;
        }
        
        return $this->where($cond)->update($saveData);
    }

    public function editAddressById($id, $saveData) 
    {
        if(empty($id) || empty($saveData)) {
            return null;
        }
        
        return $this->editAddress(array('id' => $id), $saveData);
    }
}
