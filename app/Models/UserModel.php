<?php

namespace App\Models;

use App\User;

class UserModel extends User
{

    protected $visible = ['id', 'role_id', 'name', 'email', 'mobile', 'id_card', 'avatar', 'remember_token', 'api_token', 'is_email', 'is_mobile', 'is_verified', 'is_freeze', 'is_lock','is_delete','is_deduction','activation_code','registere_ip','google_secret','trade_code','created_at','updated_at'];

    public function getInfo($id)
    {
    	if(empty($id)) {
    		return null;
    	}

    	return $this->where('id', '=', $id)->first();
    }
    
    public function updateData($cond, $saveData)
    {
        if(empty($cond) || empty($saveData)) {
            return null;
        }

        return $this->where($cond)->update($saveData);
    }
    
    public function updateById($id, $saveData)
    {
        if(empty($id) || empty($saveData)) {
            return null;
        }

        $cond['id'] = $id;
        return $this->where($cond)->update($saveData);
    }

    public static function setTradeCode($id,$tradecode)
    {
        if(empty($id) || empty($tradecode)) {
            return null;
        }

        $cond['id'] = (int) $id;
        return self::where($cond)->update([
            'trade_code' => $tradecode
        ]);
    }

    public static function getName($id)
    {
        if (empty($id)) {
            return null;
        }

        $row = self::find($id);

        if (empty($row)) {
            return null;
        }

        return $row->name;
    }
}
