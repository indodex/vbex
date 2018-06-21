<?php

namespace App\Models;

use App\Models\BaseModel as Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\UserModel;
use App\Models\CurrencyModel;
use App\Models\ConfigModel;

class TradeCodeRecordModel extends Model
{   
    protected $table = 'tarde_code_log';

    protected $primaryKey = 'id';

    protected $expire_at;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'ip', 'created_at'
    ];

    /**
     * Log belongs to users.
     *
     * @return BelongsTo
     */
    public function user() : BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }

    public function writeLog($data)
    {
        if (empty($data)) {
            return false;
        }

        $data['user_id'] = (int)$data['user_id'];
        $data['ip']      = (string)$data['ip'];
        $data['type']    = (int)$data['type'];

        $id = $this->insertGetId($data);

        $row = $this->find($id)->toArray();

        $this->setCacheByKey("{$this->table}@tradeCodeLog_user:".$data['user_id'], $row, 1440);

        return $row;
    }

    public function getLog($uid)
    {
        if (!$uid) {
            return false;
        }

        // $data = $this->getCacheByKey("{$this->table}@tradeCodeLog_user:".$uid);

        // if ($data) {
        //     return false;
        // }

        $data = $this->where(['user_id' => $uid])->orderBy('id','DESC')->first();

        $config = ConfigModel::first();

        $time = 0;

        $timeUnit = $config->change_trade > 59 ? __('api.public.hours') : __('api.public.minutes');
        

        if ($data['type'] == 1) {
            $time = 60 * $config->change_trade;
            $timeStr = $config->change_trade > 59 ? round($config->change_trade/60,2) : $config->change_trade;
            $message = __('api.public.lock_for_trade_code').$timeStr.''.$timeUnit;
        }

        if ($data['type'] == 2) {
            $time = 60 * $config->change_google;
            $timeStr = $config->change_trade > 59 ? round($config->change_google/60,2) : $config->change_google;
            $message = __('api.public.lock_for_google_secret').$timeStr.''.$timeUnit;
        }
        
        if ($data['type'] == 3) {
            $time = 60 * $config->change_phone;
            $timeStr = $config->change_trade > 59 ? round($config->change_phone/60,2) : $config->change_phone;
            $message = __('api.public.lock_for_phone').$timeStr.''.$timeUnit;
        }

        if (empty($data) || (time() - strtotime($data->created_at)) > $time) {
            return array(
                'result' => true,
            );
        }

        return array(
                'result'  => false,
                'message' =>$message
            );

    }

}