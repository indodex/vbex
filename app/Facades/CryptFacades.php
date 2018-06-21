<?php
namespace App\Facades;
use \Illuminate\Support\Facades\Facade;

class CryptFacades extends Facade{
    public static function getFacadeAccessor()
    {
        return 'MyCrypt';
    }
}