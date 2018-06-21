<?php

namespace App\Models;

use App\Models\BaseModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\UserRechargeCodeModel;
use App\User;

class UserRechargeCodeOrdersModel extends Model
{
    protected $table = 'user_recharge_code_orders';

    protected $expire_at;

    protected $primaryKey = "id";        //指定主键

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid', 'code_id', 'amount', 'confirmations', 'remark', 'done_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function createOrder($data)
    {
		return $this->insertGetId([
					'uid'           => (int) $data['uid'],
					'code_id'       => (int) $data['code_id'],
					'amount'        => (float) $data['amount'],
					'confirmations' => (int) $data['confirmations'],
                    'remark'        => (string) $data['remark'],
                    'done_at'       => (string) $data['done_at'],
					'status'        => (int) $data['status'],
				]);
    }

    public function getInfo($id)
    {
        if(empty($id)) {
            return null;
        }
        
        return $this->where('id', '=', $id)->first();
    }

    public function updateOrder($cond, $saveData)
    {
        if(empty($cond) || empty($saveData)) {
            return null;
        }

        return $this->where($cond)->update($saveData);
    }

    public function applyById($id, $status = null, $remark = '')
    {
        if(empty($id) || is_null($status)) {
            return null;
        }

        $cond['id'] = $id;
        $saveData['status'] = $status;
        $saveData['remark'] = $remark;
        $saveData['done_at'] = date('Y-m-d H:i:s');

        return $this->updateOrder(array('id' => $id), $saveData);
    }



    /**
     * Log belongs to users.
     *
     * @return BelongsTo
     */
    public function rechargeCode() : BelongsTo
    {
        return $this->belongsTo(UserRechargeCodeModel::class, 'code_id');
    }

    /**
     * Log belongs to users.
     *
     * @return BelongsTo
     */
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class, 'uid');
    }
}
