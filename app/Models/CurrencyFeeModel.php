<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\BaseModel as Model;
use App\Models\CurrencyModel;

class CurrencyFeeModel extends Model
{
    protected $table = 'currency_fee';

    protected $expire_at;

    protected $primaryKey = "id";        //指定主键

    protected $currency;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'currency', 'fee',
    ];

    /**
     * Log belongs to users.
     *
     * @return BelongsTo
     */
    public function currency() : BelongsTo
    {
        return $this->belongsTo(CurrencyModel::class, 'uid');
    }

    public function getInfo($id) 
    {
        if(empty($id)) {
            return null;
        }

        return $this->where('id', '=', $id)->first();
    }
}
