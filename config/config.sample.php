<?php
// Copy to config.php and adjust for your environment.
return [
    'app' => [
        'name'        => 'Gürdoğa Kooperatifi',
        'base_path'   => '/gurdoga',                 // subfolder under document root ('' if vhost root)
        'base_url'    => 'http://localhost/gurdoga', // absolute, no trailing slash (used for canonical/sitemap)
        'debug'       => true,
        'timezone'    => 'Europe/Istanbul',
        'version'     => '1.0',
        'maintenance' => false,                   // true = force the "under construction" splash (also toggleable in admin)
    ],

    // Vendor licence check. Leave key/server empty for an unmanaged install (no checks).
    'license' => [
        'key'        => '',                       // the licence key you issued for this domain
        'server'     => '',                       // e.g. https://lisans.teknobursa.com/check.php
        'secret'     => '',                       // shared HMAC secret (must match the server)
        'enforce'    => true,                     // true = block the admin panel when invalid (site stays up)
        'grace_days' => 10,                       // keep trusting the last good check if the server is unreachable
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
        'enabled'   => true,          // false = only log, no send
        'from'      => 'no-reply@gurdoga.com',
        'from_name' => 'Gürdoğa Kooperatifi Website',
        'to'        => ['tasarim@teknobursa.com'], // quote request recipients (also editable in admin settings)
    ],
    'security' => [
        'session_name' => 'gurdoga_sess',
    ],
];
