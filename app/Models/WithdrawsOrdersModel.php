<?php

namespace App\Models;

use App\Models\BaseModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\User;
use App\Models\CurrencyModel;
use App\Models\AccountsModel;

class WithdrawsOrdersModel extends Model
{
    protected $table = 'withdraws_orders';

    protected $expire_at;

    protected $primaryKey = "id";        //指定主键

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid', 'currency', 'fee', 'amount', 'sum_amount', 'address_name', 'address', 'txid', 'remark', 'done_at', 'status',
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

    public function updateOrder($cond, $saveData)
    {
        if(empty($cond) || empty($saveData)) {
            return null;
        }

        return $this->where($cond)->update($saveData);
    }

    public function applyById($id, $status = null, $remark = '',$txid = '')
    {
        if(empty($id) || is_null($status)) {
            return null;
        }

        $cond['id']          = $id;
        $saveData['status']  = $status;
        $saveData['done_at'] = date('Y-m-d H:i:s');

        if ($remark) {
            $saveData['remark'] = $remark;
        }

        if ($txid) {
            $saveData['txid'] = $txid;
        }

        return $this->updateOrder(array('id' => $id), $saveData);
    }

    public function createOrder($data) 
    {
        if(empty($data)) {
            return null;
        }
        
        $this->fillable(array_keys($data));
        return $this->insertGetId([
            'uid'          => (int) $data['uid'], 
            'currency'     => (int) $data['currency'], 
            'fee'          => (string) $data['fee'], 
            'amount'       => (string) $data['amount'], 
            'sum_amount'   => (string) $data['sum_amount'], 
            'address_name' => (string) $data['address_name'], 
            'address'      => (string) $data['address'], 
            'remark'       => (string) $data['remark'],  
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

    /**
     * 获取用户下所有提现记录
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

    /**
     * 记录状态转变
     * @param  [type] $status [description]
     * @return [type]         [description]
     */
    public function toStatusName($status)
    {
        switch($status){
            case '-1':
                return __('api.withdraw.failure');
            case '0':
                return __('api.withdraw.failure_audit');
                break;
            case '1':
                return __('api.withdraw.succeed');
                break;
            case '2':
                return __('api.withdraw.waited');
                break;
            case '3':
                return __('api.withdraw.waited_audit');
                break;
        }
    }
}
