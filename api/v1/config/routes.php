<?php

return [
    '/register' => [
        'method' => 'POST',
        'controller' => 'nvs\api\v1\Controllers\User',
        'action' => 'register',
        'middleWare' => ['json', 'auth']
    ],
    '/update' => [
        'method' => 'POST',
        'controller' => 'nvs\api\v1\Controllers\User',
        'action' => 'update',
        'middleWare' => ['json', 'auth']
    ],
    '/confirm' => [
        'method' => 'POST',
        'controller' => 'nvs\api\v1\Controllers\User',
        'action' => 'confirm',
        'middleWare' => ['json', 'auth']
    ],
    '/get' => [
        'method' => 'POST',
        'controller' => 'nvs\api\v1\Controllers\User',
        'action' => 'get',
        'middleWare' => ['json', 'auth']
    ],
];
