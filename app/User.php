<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\RestPassword as RestPasswordNotification;
use Encore\Admin\Traits\AdminBuilder;

class User extends Authenticatable
{
    use HasApiTokens,Notifiable,AdminBuilder;


    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'activation_code', 'invite_uid', 'is_email','registere_ip'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new RestPasswordNotification($token));
    }

    public static function deleteByIds($ids)
    {
        if(empty($ids)) {
            return false;
        }
        foreach($ids as $id) {
            if($id > 0) {
                User::where('id', '=', $id)->update(['is_delete'=>1]);
            }
        }
        return true;
    }
}
