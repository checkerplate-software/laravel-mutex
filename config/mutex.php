<?php

return [

    'default' => env('MUTEX_DRIVER', 'redis'),

    'drivers' => [

        'redis' => [
            'connection' => 'default',
        ],

    ],
];