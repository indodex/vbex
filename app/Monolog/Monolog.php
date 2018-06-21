<?php
namespace App\Monolog;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Illuminate\Http\Request;

class Monolog
{

    public $basePath;

    public $logskey;

    public $table;

    public function __construct() 
    {
        $this->basePath = storage_path('logs') . '/';
    }

    public function getKey() 
    {
        return $this->logskey;
    }

    public function setKey($key) 
    {
        $this->logskey = $key;
        return $this;
    }

    public function info($contant) 
    {
        // create a log channel
        $logsFile = $this->basePath . date('Ymd') . '/' . $this->table .'.log';
        return $this->getInfoLogger()
                    ->pushHandler(new StreamHandler($logsFile, Logger::INFO))
                    ->info($this->getKey(), $contant);
    }

    private function getInfoLogger()
    {
        return new Logger('info');
    }
}
