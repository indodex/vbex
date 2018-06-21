<?php

namespace App\Models;

use App\Models\BaseModel as Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\User;
use App\Models\CurrencyModel;

class AccountsModel extends Model
{   
    use \App\Traits\HasCompositePrimaryKey;

    protected $table = 'accounts';

    protected $primaryKey = ['uid','currency'];        //复合主键

    protected $currency;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid', 'currency', 'balance', 'locked', 'created_at', 'updated_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    // 将锁定资金退回账户
    public function repealByOrder($order)
    {
        if(empty($order)) {
            return false;
        }

        $cond['uid'] = (int)$order->uid;
        $cond['currency'] = (int)$order->currency;
    	$data = $this->where($cond)->first();
        
        $balance = $data->balance + $order->sum_amount;
        $locked = $data->locked - $order->sum_amount;

        if (bccomp($data->locked, $order->sum_amount) == -1) {
            return false;
        }

    	$this->setCurrency($order->currency);
        $decResult = $this->decrementLocked($order->uid, $order->sum_amount);
        $incResult = $this->incrementBalance($order->uid, $order->sum_amount);

        if($decResult && $incResult) {
            return true;
        } else {
            return false;
        }
    }

    // 将锁定资金清除
    public function clearByOrder($order)
    {
        if(empty($order)) {
            return false;
        }

        $cond['uid'] = (int)$order->uid;
        $cond['currency'] = (int)$order->currency;
        $data = $this->where($cond)->first();

        if (bccomp($data->locked, $order->sum_amount) == -1) {
            return false;
        }

        $this->setCurrency($order->currency);
        return $this->decrementLocked($order->uid, $order->sum_amount);
    }

    public function findORcreate($uid, $currency)
    {
        return self::firstOrCreate(
                        [
                            'uid' => $uid,
                            'currency' => $currency
                        ],
                        [
                            'uid' => $uid,
                            'currency' => $currency,
                            'balance' => 0,
                            'locked' => 0,
                        ]
                    );
    }


    /**
     * 创建账号
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function createAccount($data) 
    {
    	return $this->insertGetId([
            'uid'      => (int) $data['uid'], 
            'currency' => (int) $data['currency'], 
            'balance'  => (float) $data['balance'], 
            'locked'   => (float) $data['locked'], 
        ]);
    }

    /**
     * 设置货币ID
     * @return [type] [description]
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
     * 获取余额信息
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function getInfo($uid) 
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
     * 获取余额信息
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
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
     * 获取余额信息 - 排他锁
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function getLockInfo($uid) 
    {
        if(empty($uid)) {
            return null;
        }

        return $this->where([
                    'uid'      => (int) $uid,
                    'currency' => (int) $this->getCurrency()
                ])->lockForUpdate()->first();
    }

    /**
     * 获取用户对应货币的余额
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function getAccount($uid)
    {
    	if(empty($uid)) {
    		return null;
    	}

    	$currency = $this->getCurrency();
    	if(empty($currency)) {
    		return null;
    	}

    	$account = $this->getInfoByUid($uid);
    	if(!empty($account)) {
    		return $account;
    	}

    	$account = $this->createAccount([
			    		'uid' => $uid,
			    		'currency' => $this->getCurrency(),
			    		'balance' => 0,
			    		'locked' => 0,
			    	]);

    	if($account) {
    		return $this->getInfo($uid);
    	} else {
    		return null;
    	}

    }



    /**
     * 获取用户对应货币的余额 - 用作事物处理
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function getAccountLock($uid)
    {
        if(empty($uid)) {
            return null;
        }

        $currency = $this->getCurrency();
        if(empty($currency)) {
            return null;
        }

        $account = $this->getLockInfo($uid);
        if(!empty($account)) {
            return $account;
        }

        $account = $this->createAccount([
                        'uid' => $uid,
                        'currency' => $this->getCurrency(),
                        'balance' => 0,
                        'locked' => 0,
                    ]);

        if($account) {
            return $this->getInfo($uid);
        } else {
            return null;
        }

    }

    // 添加余额
    public function incrementBalance($uid, $amount = 0)
    {
        if(empty($uid)) {
            return null;
        }

        return $this->where([
                    'uid' => $uid,
                    'currency' => $this->getCurrency(),
                ])->increment('balance', $amount);
    }

    public function incrementLocked($uid, $amount = 0)
    {
        if(empty($uid)) {
            return null;
        }

        return $this->where([
                    'uid' => $uid,
                    'currency' => $this->getCurrency(),
                ])->increment('locked', $amount);
    }

    // 余额减少
    public function decrementBalance($uid, $amount = 0)
    {
    	if(empty($uid)) {
    		return null;
    	}

    	return $this->where([
                    'uid' => $uid,
                    'currency' => $this->getCurrency(),
                ])->decrement('balance', $amount);
    }

    public function decrementLocked($uid, $amount = 0)
    {
        if(empty($uid)) {
            return null;
        }

        return $this->where([
                    'uid' => $uid,
                    'currency' => $this->getCurrency(),
                ])->decrement('locked', $amount);
    }

    /**
     * 获取用户所有货币账户
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function getListByUid($uid)
    {
        if(empty($uid)) {
            return null;
        }

        return $this->where('uid', '=', $uid)->get();
    }

    /**
     * Log belongs to users.
     *
     * @return BelongsTo
     */
    public function currency() : BelongsTo
    {
        return $this->belongsTo(CurrencyModel::class, 'currency');
    }

    public function getOrCreate($uid,$currency)
    {
        if (empty($uid) || empty($currency)) {
            return false;
        }

        return $this->firstOrCreate([
                'uid' => $uid,
                'currency'=>$currency
            ],
            [
                'uid' => $uid,
                'currency'=>$currency,
                'balance' => 0,
                'locked' => 0,
            ]);

    }

    public function addBalance($uid,$currency,$num)
    {
        if (empty($uid) || empty($currency) || !($num > 0)) {
            return false;
        }

        $row = $this->where([
                'uid' => $uid,
                'currency'=>$currency
            ])->lockForUpdate()->first();

        $balance = $row->balance + $num;

        return $this->where([
            ['uid','=',$uid],
            ['currency','=',$currency]
        ])->update(['balance'=>$balance]);


    }

    public function getCurrencySum($currency)
    {
        $balance = $this->where('currency', $currency)->sum('balance');
        $locked = $this->where('currency', $currency)->sum('locked');
        if($balance > 0 && $locked > 0) {
            return bcadd($balance, $locked);
        } else if($balance > 0) {
            return $balance;
        } else if($locked > 0) {
            return $locked;
        } else {
            return 0;
        }
    }
}
