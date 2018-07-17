<?php

namespace App\Models;

use App\Models\BaseModel as Model;

class TradesFocusOnModel extends Model
{
    protected $primaryKey = 'id';

    protected $table = 'trades_focus_on';

    public $fillable = [
        'uid','trade_id'
    ];

    /**
     * 关联交易对
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function trade()
    {
        return $this->hasOne(TradesCurrenciesModel::class, 'id','trade_id');
    }

    /**
     * 关注交易对
     * @param int $uid
     * @param int $trade_id
     * @return null
     */
    public function addFocusOn(int $uid, int $trade_id)
    {
        if (empty($uid) || empty($trade_id)) {
            return null;
        }

        $res = $this->firstOrCreate(
            [
                'uid'=>$uid,
                'trade_id'=>$trade_id
            ],
            [
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);


        return $res;
    }

    /**
     * 删除交易对
     * @param int $uid
     * @param int $trade_id
     * @return null
     */
    public function delFocusOn(int $uid, int $trade_id)
    {
        if (empty($uid) || empty($trade_id)) {
            return null;
        }

        return $this->where(['uid'=>$uid, 'trade_id'=>$trade_id])->delete();

    }

    /**
     * 获取用户所有关注交易对
     * @param int $uid
     * @return mixed
     */
    public function getFocusByUid(int $uid)
    {
        return $this->select('trade_id')->where('uid', '=', $uid)->get();
    }

    /**
     * 是否已关注
     * @param int $uid
     * @return mixed
     */
    public function isFocusOn(int $uid, int $trade_id)
    {
        return $this->select('trade_id')->where([
            'uid' => $uid,
            'trade_id' => $trade_id
        ])->count();
    }

}
