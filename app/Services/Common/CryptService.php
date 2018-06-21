<?php
namespace App\Services\Common;

class CryptService
{
    public $config, $keypath, $prikey_path, $pubkey_path, $prikey, $pubkey , $private_key_size;

    public function select($select = 'rsa_api')
    {
        $config = config('crypt');
        if (array_key_exists($select, $config)) {
            $this->config = $config[$select];
            $this->private_key_size = $this->config['openssl_config']['private_key_bits'];
        } else {
            return false;
        }
        // $this->keypath = dirname(dirname(dirname(__DIR__))) . $this->config['path'];
        $this->keypath = $this->config['path'];
        if(!file_exists($this->keypath)){
            mkdir($this->keypath,"0777",true);
        }
        $this->prikey_path = $this->keypath . $this->config['private_key_file_name'];
        $this->pubkey_path = $this->keypath . $this->config['public_key_file_name'];
        if (file_exists($this->prikey_path))
            $this->prikey = file_get_contents($this->prikey_path);
        if (file_exists($this->pubkey_path))
            $this->pubkey = file_get_contents($this->pubkey_path);
        return $this;
    }

    public function makeKey()
    {
        $res = openssl_pkey_new($this->config['openssl_config']);
        openssl_pkey_export($res, $this->prikey);
        file_put_contents($this->prikey_path, $this->prikey);
        $pubkey = openssl_pkey_get_details($res);
        $this->pubkey = $pubkey['key'];
        file_put_contents($this->pubkey_path, $this->pubkey);
        return $test = ['prikey' => $this->prikey, 'pubkey' => $this->pubkey];
    }

    public function encryptPrivate($data){
        $crypt = $this->encrypt_split($data);
        $crypted = '';
        foreach ($crypt as $k=>$c){
            if($k!=0) $crypted.="@";
            $crypted.=base64_encode($this->doEncryptPrivate($c));
        }
        return $crypted;
    }
    public function encryptPublic($data){
        $crypt = $this->encrypt_split($data);
        $crypted = '';
        foreach ($crypt as $k=>$c){
            if($k!=0) $crypted.="@";
            $crypted.=base64_encode($this->doEncryptPublic($c));
        }
        return $crypted;
    }

    public function decryptPublic($data){
        $decrypt = explode('@',$data);
        $decrypted = "";
        foreach ($decrypt as $k=>$d){
            $decrypted .= $this->doDecryptPublic(base64_decode($d));
        }
        return $decrypted;
    }
    public function decryptPrivate($data){
        $decrypt = explode('@',$data);
        $decrypted = "";
        foreach ($decrypt as $k=>$d){
            $decrypted .= $this->doDecryptPrivate(base64_decode($d));
        }
        return $decrypted;
    }
    private function encrypt_split($data){
        $crypt=[];$index=0;
        for($i=0; $i<strlen($data); $i+=117){
            $src = substr($data, $i, 117);
            $crypt[$index] = $src;
            $index++;
        }
        return $crypt;
    }
    private function doEncryptPrivate($data)
    {
        $rs = '';
        if (@openssl_private_encrypt($data, $rs, $this->prikey) === FALSE) {
            return NULL;
        }
        return $rs;
    }

    private function doDecryptPrivate($data)
    {
        $rs = '';
        if (@openssl_private_decrypt($data, $rs, $this->prikey) === FALSE) {
            return null;
        }
        return $rs;
    }
    private function doEncryptPublic($data){
        $rs = '';
        if (@openssl_public_encrypt($data, $rs, $this->pubkey) === FALSE) {
            return NULL;
        }
        return $rs;
    }
    private function doDecryptPublic($data)
    {
        $rs = '';
        if (@openssl_public_decrypt($data, $rs,  $this->pubkey) === FALSE) {
            return null;
        }
        return $rs;
    }
}