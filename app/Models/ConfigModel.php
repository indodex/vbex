<?php

namespace App\Models;

use App\Models\BaseModel as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\CurrencyModel;

class ConfigModel extends Model
{
    protected $table = 'config';

    protected $primaryKey = "id";        //指定主键

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'invite_rewards', 'rewards_ratio','default_land','change_phone','change_google','change_trade','lock_type','client_email','cooperation_email', 'recharge_code_switch',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    protected $currency;



}
