<?php
declare(strict_types=1);

define('BASE_DIR', __DIR__);

$config = require BASE_DIR . '/config/config.php';

date_default_timezone_set($config['app']['timezone'] ?? 'UTC');
error_reporting(E_ALL);
ini_set('display_errors', !empty($config['app']['debug']) ? '1' : '0');

// PSR-4-ish autoloader for App\*
spl_autoload_register(function (string $class): void {
    if (strncmp($class, 'App\\', 4) !== 0) {
        return;
    }
    $path = BASE_DIR . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

require BASE_DIR . '/app/helpers.php';

use App\Core\App;

$app = new App($config);
$app->run();
