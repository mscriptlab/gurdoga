<?php
return [
    'app' => [
        'name'        => 'Gürdoğa Kooperatifi',
        'base_path'   => '/gurdoga',
        'base_url'    => 'http://localhost/gurdoga',
        'debug'       => true,
        'timezone'    => 'Europe/Istanbul',
        'version'     => '1.0',
        'maintenance' => false,
    ],
    'license' => [
        'key'        => '',
        'server'     => '',
        'secret'     => '',
        'enforce'    => true,
        'grace_days' => 10,
    ],
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'gurdoga',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        'enabled'   => false,
        'from'      => 'no-reply@gurdoga.com',
        'from_name' => 'Gürdoğa Kooperatifi Website',
        'to'        => ['tasarim@teknobursa.com'],
    ],
    'security' => [
        'session_name' => 'gurdoga_sess',
    ],
];
