<?php

namespace App\Models;

use App\Models\BaseModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\User;
use App\Models\CurrencyModel;
use DB;

class UserRechargeCodeModel extends Model
{
    protected $table = 'user_recharge_code';

    protected $expire_at;

    protected $primaryKey = "id";        //指定主键

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'recharge_uid', 'belongto_uid', 'amount', 'code', 'done_at', 'status', 'is_delete', 'currency', 'audit',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function createCode($data)
    {
        $code['code']         = $data['code'];
        $code['amount']       = $data['amount'];
        $code['currency']     = $data['currency'];
        $code['uid']          = $data['uid'];
        $code['audit']        = $data['audit'];
        $code['recharge_uid'] = 0;
        $code['done_at']      = '';
        $code['status']       = 3;
        $code['is_delete']    = 0;

        return $this->insert($code);
    }

    public function getInfoByCode($code)
    {
        if(empty($code)) {
            return null;
        }
        
        return $this->where('code', '=', $code)->first();
    }


    public function getInfo($id)
    {
        if(empty($id)) {
            return null;
        }
        
        return $this->where('id', '=', $id)->first();
    }

    // 用后使用优惠券
    public function usedCode($id, $uid)
    {
        if(empty($id)) {
            return null;
        }
        
        $saveData['uid']    = (int) $uid;
        $saveData['status'] = 2;

        return $this->where('id', '=', $id)->update($saveData);
    }

    /**
     * 用户审核优惠券
     * @param  [type] $id  [description]
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function applyCode($id, $rechargeUid, $status = 2)
    {
        if(empty($id)) {
            return null;
        }
        
        $saveData['status']       = $status;
        $saveData['recharge_uid'] = $rechargeUid;
        $saveData['done_at']      = date('Y-m-d H:i:s');

        return $this->where('id', '=', $id)->update($saveData);
    }

    public function getListByUid($uid)
    {
        if(empty($uid)) {
            return false;
        }
        $data = $this->where('uid', '=', $uid)->get();
        if(!empty($data)) {
            return $data;
        } else {
            return null;
        }
    }

    /**
     * Log belongs to users.
     *
     * @return BelongsTo
     */
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class, 'recharge_uid');
    }

    /**
     * Log belongs to users.
     *
     * @return BelongsTo
     */
    public function member() : BelongsTo
    {
        return $this->belongsTo(User::class, 'uid');
    }

    /**
     * Log belongs to users.
     *
     * @return BelongsTo
     */
    public function moneyTo() : BelongsTo
    {
        return $this->belongsTo(CurrencyModel::class, 'currency');
    }

    public function getToDaySum($uid)
    { 
        $startDate = date('Y-m-d 00:00:00');
        $endDate = date('Y-m-d 23:59:59');
        $query = $this;
        $query = $query->where('uid', '=', $uid);
        $query = $query->whereBetween('created_at', array($startDate, $endDate));
        return $query->sum('amount');
    }

    /**
     * 用户审核优惠券
     * @param  [type] $id  [description]
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function applyAudit($id, $audit = 0)
    {
        if(empty($id)) {
            return null;
        }
        
        $saveData['audit'] = $audit;
        return $this->where('id', '=', $id)->update($saveData);
    }
}
