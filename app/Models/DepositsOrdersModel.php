<?php

namespace App\Models;

use App\Models\BaseModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\User;
use App\Models\CurrencyModel;

class DepositsOrdersModel extends Model
{
	protected $table = 'deposits_orders';

    protected $expire_at;

    protected $primaryKey = "id";        //指定主键

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid', 'currency', 'fee', 'amount', 'address', 'txid', 'txout', 'confirmations', 'remark', 'done_at', 'status',
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
     * 获取用户下所有充值记录
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
            case '0':
                return __('api.account.deposit_fail');
                break;
            case '1':
                return __('api.account.deposit_success');
                break;
            case '2':
                return __('api.account.deposit_wait');
                break;
            case '3':
                return __('api.account.unused');
                break;
        }
    }

    public function updateOrder($cond, $saveData)
    {
        if(empty($cond) || empty($saveData)) {
            return null;
        }

        return $this->where($cond)->update($saveData);
    }

    public function createOrder($data) 
    {
        $this->fillable(array_keys($data));
        $id = $this->insertGetId($data);
        if($id > 0) {
            return $id;
        } else {
            return null;
        }
    }

    public function applyById($id, $status = null, $remark = '')
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
        
        return $this->updateOrder(array('id' => $id), $saveData);
    }

    // 保存提交的form数据
    public function save(array $options = [])
    {
        $attributes = $this->getAttributes();
        unset($attributes['depositAddress']);
        
        if(!isset($attributes['id'])) {
            return 1;//$this->createOrder($attributes);
        }

        $id = $attributes['id'];
        unset($attributes['id']);
        $this->updateOrder(['id' => $id], $attributes);
    }
}
