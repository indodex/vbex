<?php

namespace App\Models;

use App\Models\BaseModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\User;
use App\Models\CurrencyModel;
use DB;

class RechargeCodeModel extends Model
{
    protected $table = 'recharge_code';

    protected $expire_at;

    protected $primaryKey = "id";        //指定主键

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'recharge_uid', 'belongto_uid', 'amount', 'code', 'done_at', 'status', 'is_delete', 'currency',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function batchCode($data)
    {
		$number = empty($data['number']) ? 0 : $data['number'];
		$amount = empty($data['amount']) ? 0 : $data['amount'];
        $amount = (float) $amount;
		// $type   = empty($data['type']) ? 1 : $data['type'];
		$currency  = empty($data['currency']) ? 0 : $data['currency'];
		$cards  = make_cards($number);

        $site = DB::table('admin_config')->where('name', 'site_simple_name')->first();
        if(empty($site)) {
            $siteName = 'code';
        } else {
            $siteName = $site->value;
        }

        $recharge = array();
        foreach ($cards as $key => $value) {
            $code = $amount . '_' . $siteName . '_OF' . $value . '_' . date('YmdH');
        	$recharge[$key]['code']         = $code;
			$recharge[$key]['amount']       = $amount;
			$recharge[$key]['currency']     = $currency;
			$recharge[$key]['done_at']      = '';
			$recharge[$key]['recharge_uid'] = 0;
			$recharge[$key]['belongto_uid'] = 0;
			$recharge[$key]['status']       = 3;
			$recharge[$key]['is_delete']    = 0;
        }

        try {
    		return $this->insert($recharge);
	    } catch (Exception $e) {
			print $e->getMessage();   
			exit(); 
		}
    }

    // 保存提交的form数据
    public function save(array $options = [])
    {
        $attributes = $this->getAttributes();
        $this->batchCode($attributes);
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
    public function usedCode($id, $uid, $status = 2)
    {
        if(empty($id)) {
            return null;
        }
        
        $saveData['recharge_uid'] = (int) $uid;
        $saveData['status']       = (int) $status;
        $saveData['done_at']      = date('Y-m-d H:i:s');

        return $this->where('id', '=', $id)->update($saveData);
    }

    /**
     * 用户审核优惠券
     * @param  [type] $id  [description]
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function applyCode($id, $status = 2)
    {
        if(empty($id)) {
            return null;
        }
        
        $saveData['status'] = $status;

        return $this->where('id', '=', $id)->update($saveData);
    }

    public function apply()
    {
        return $id;
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
    public function moneyTo() : BelongsTo
    {
        return $this->belongsTo(CurrencyModel::class, 'currency');
    }
}
