<?php

return [

    'default' => 'mongodb',

    'connections' => [

        'mongodb' => [
            'driver'   => 'mongodb',
            'host'     => 'localhost',
            'port'     => 27017,
            'username' => '',
            'password' => '',
            'database' => 'Address',
        ],

    ],

    'migrations' => 'migrations',
];
