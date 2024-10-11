<?php

return [


    'default' => env('REQUEST_LOGGING_DRIVER', 'mockery'),


    'stores' => [
        'mongo' => [
            'driver' => 'mongo',
            'connection' => 'mongo',
            'table' => 'log',
        ],
        'mysql' => [
            'driver' => 'mysql',
            'connection' => 'mysql',
            'table' => 'log',
        ],
        'mockery' => [
            'driver' => 'null',
        ]
    ]

];