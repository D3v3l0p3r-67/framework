<?php

return [
    'app' => [
        // Never enable this in production: exception details are returned to clients.
        'debug' => false,
        // Temporarily allow and log public methods without #[Action]. Keep false in strict mode.
        'action_audit' => false,
    ],
    'database' => [
        'username' => 'xxx',
        'database' => 'xxx',
        'password' => 'xxx',
        'type' => 'mysql',
        'charset' => 'utf8'
    ]
];
