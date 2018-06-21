<?php

namespace App\Models;

use App\Models\BaseModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\User;
use App\Models\CurrencyModel;
use DB;

class TradesFeeModel extends Model
{
    protected $table = 'trades_fee';

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

    public function updateOrder($cond, $saveData) 
    {
        if(empty($cond) || empty($saveData)) {
            return null;
        }

        return $this->where($cond)->update($saveData);
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

    /**
     * Log belongs to users.
     *
     * @return BelongsTo
     */
    public function currencyBuyTo() : BelongsTo
    {
        return $this->belongsTo(CurrencyModel::class, 'buy_currency');
    }

    /**
     * Log belongs to users.
     *
     * @return BelongsTo
     */
    public function currencySellTo() : BelongsTo
    {
        return $this->belongsTo(CurrencyModel::class, 'sell_currency');
    }

    /**
     * 获取分页列表
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function getListSort($cond, $last_id = 0, $order = ['id', 'desc'], $limit = 0)
    {
        if(empty($cond)) {
            return null;
        }

        $query = $this->newQuery();
        if($last_id > 0) {
            $query->where('id','>',$last_id);
        }
        if(isset($cond['whereIn'])) {
            $whereIn = $cond['whereIn'];
            foreach ($whereIn as $iKey => $iValue) {
                $query->whereIn($iKey, $iValue);
            }
            unset($cond['whereIn']);
        }

        if(isset($cond['orWhere'])) {
            $orWhere = $cond['orWhere'];
            foreach ($orWhere as $oKey => $oValue) {
                $query->orWhere($oKey, '=',$oValue);
            }
            
            unset($cond['orWhere']);
        }

        if ($limit > 0) {
            return $query->where($cond)->orderBy($order[0], $order[1])->limit($limit)->get();
        }
        return $query->where($cond)->orderBy($order[0], $order[1])->get();
    }

    public function getOne($cond, $order = ['id', 'desc'])
    {
        if(empty($cond)) {
            return null;
        }
        if(isset($cond['whereIn'])) {
            $whereIn = $cond['whereIn'];
            foreach ($whereIn as $iKey => $iValue) {
                $this->whereIn($iKey, $iValue);
            }
            unset($cond['whereIn']);
        } 
        if(!empty($cond)) {
            $this->where($cond);
        }
        
        return $this->orderBy($order[0], $order[1])
                    ->limit(1)
                    ->first();
    }

    public function bindToOrder($id, $orderId)
    {
        $cond['id'] = (int) $id;
        return $this->updateOrder($cond, ['order_id' => $orderId]);
    }
}
