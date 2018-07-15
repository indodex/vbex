<?php

namespace App\Models;

use App\Models\BaseModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\User;
use App\Models\CurrencyModel;
use DB;

class TradesOrdersModel extends Model
{
    protected $table = 'trades_orders';

    protected $expire_at;

    protected $primaryKey = "id";        //指定主键

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid', 'buy_currency', 'sell_currency', 'price', 'average_price', 'num', 'successful_num', 'successful_price', 'successful_count', 'fee', 'done_at', 'status',
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
     * 记录状态转变
     * @param  [type] $status [description]
     * @return [type]         [description]
     */
    public function toStatusName($status)
    {
        switch($status){
            case '0':
                return __('api.trade.canceled');
                break;
            case '1':
                return __('api.trade.completed');
                break;
            case '2':
                return __('api.trade.part_completed');
                break;
            case '3':
                return __('api.trade.waited');
                break;
        }
    }

    /**
     * 获取分页列表
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function getListSort($cond, $order = ['id', 'desc'], $limit = 10)
    {
        if(empty($cond)) {
            return null;
        }

        $query = $this->newQuery();
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
        
        return $query->where($cond)->orderBy($order[0], $order[1])->limit($limit)->get();
    }

    /**
     * 获取分页列表
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public function getListSortPage($cond, $order = ['id', 'desc'], $page = 1, $pageSize = 10)
    {
        if(empty($cond)) {
            return null;
        }

        $query = $this->newQuery();
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
        
        return $query->where($cond)->orderBy($order[0], $order[1])->paginate($pageSize, ['*'], 'page', $page);
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

    public function updateOrder($cond, $saveData) 
    {
        if(empty($cond) || empty($saveData)) {
            return null;
        }

        return $this->where($cond)->update($saveData);
    }


    /**
     * 获取委托订单 —— 用于事物查询
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getLockInfo($id) 
    {
        if(empty($id)) {
            return null;
        }

        return $this->where('id', '=', $id)->lockForUpdate()->first();
    }

    public function getInfo($id)
    {
        if(empty($id)) {
            return null;
        }

        return $this->where('id', '=', $id)->first();
    }

    public function cancelOrder($id) 
    {
        if(empty($id)) {
            return null;
        }

        $cond['id'] = $id;
        $saveData['status'] = 0;
        return $this->updateOrder($cond, $saveData);
    }
}
