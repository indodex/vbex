<?php

namespace App\Services;

class BaseService
{

    public $returnData;

    private $user; 

    public function __construct() 
    {

    }

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }
    
    protected function success($data)
    {
        return [
            'status' => 1,
            'data' => $data
        ];
    }

    protected function error($error, $status = 0)
    {
        return [
            'status' => $status,
            'error' => $error
        ];
    }
}
