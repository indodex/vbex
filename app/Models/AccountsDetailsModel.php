<?php

namespace App\Models;

use App\Models\BaseModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\CurrencyModel;

class AccountsDetailsModel extends Model
{
    protected $table = 'accounts_details';

    protected $primaryKey = "id";        //指定主键

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid', 'currency', 'type', 'change_balance', 'balance', 'remark',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    protected $currency;

    /**
     * Log belongs to users.
     *
     * @return BelongsTo
     */
    public function currency() : BelongsTo
    {
        return $this->belongsTo(CurrencyModel::class, 'currency');
    }

    public function createDetail($data) 
    {
    	return $this->insert([
            'uid'            => (int) $data['uid'], 
            'currency'       => (int) $data['currency'], 
            'type'           => (int) $data['type'], 
            'change_balance' => (float) $data['change_balance'], 
            'balance'        => (float) $data['balance'], 
            'remark'         => (string) $data['remark'], 
        ]);
    }

    /**
     * 记录状态转变
     * @param  [type] $status [description]
     * @return [type]         [description]
     */
    public function toStatusName($status)
    {
        switch($status){
            case '-3':
                return __('api.account.trade_fee');
                break;

            case '-2':
                return __('api.account.trade_sell');
                break;
            case '-1':
                return __('api.account.withdraw');
                break;
            case '0':
                return __('api.account.system');
                break;
            case '1':
                return __('api.account.deposit');
                break;
            case '2':
                return __('api.account.trade_buy');
                break;
            case '3':
                return __('api.account.create_code');
            case '4':
                return __('api.account.invite_award');
                break;
        }
    }
}
