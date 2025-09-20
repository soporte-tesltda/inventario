<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 'private'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        'private' => [
            'driver' => 's3',
            'key' => 'b08e62c6e3cddddb01214a7e758350f1',
            'secret' => '82f387d7f56fc385e703581ee6303d25f1fc0bc931d7c37d2df98b982ae55349',
            'region' => 'auto',
            'bucket' => 'fls-9fe4b6bb-9be3-4819-bfab-ac2b4a5659ee',
            'url' => 'https://367be3a2035528943240074d0096e0cd.r2.cloudflarestorage.com',
            'endpoint' => 'https://367be3a2035528943240074d0096e0cd.r2.cloudflarestorage.com',
            'use_path_style_endpoint' => true,
            'visibility' => 'public',
            'temporary_url_timeout' => 86400, // 24 horas en segundos
            'throw' => false,
            'options' => [
                'http' => [
                    'timeout' => 10, // Timeout de 10 segundos para operaciones HTTP
                    'connect_timeout' => 5, // Timeout de conexión de 5 segundos
                ]
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
