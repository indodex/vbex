<?php
//基于laravel根目录，分隔符最好是用 DIRECTORY_SEPARATOR 常量代替
return [
    'rsa_api' => [
        // 'path'=>DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'rsakey'.DIRECTORY_SEPARATOR,
        'path'=> storage_path('openssl') . DIRECTORY_SEPARATOR,
        'private_key_file_name'=>'private_key.pem',
        'public_key_file_name' =>'rsa_public_key.pem',
        'openssl_config'=>[
            "digest_alg" => "sha512",
            "private_key_bits" => 1024,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]
    ],
    'rsa_data'=>[
        // 'path'=>DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'rsakey'.DIRECTORY_SEPARATOR,
        'path'=> storage_path('openssl') . DIRECTORY_SEPARATOR,
        'private_key_file_name'=>'private_key.pem',
        'public_key_file_name' =>'rsa_public_key.pem',
        'openssl_config'=>[
            "digest_alg" => "sha512",
            "private_key_bits" => 1024,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]
    ]
];