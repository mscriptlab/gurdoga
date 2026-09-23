<?php
/**
 * CLI installer.  Usage:
 *   php scripts/install.php --email=you@example.com --password=secret [--fresh]
 *
 * --fresh  drops & recreates the database first.
 * Runs schema.sql + seed.sql, then creates (or updates) the admin user.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("CLI only.\n");
}
define('BASE_DIR', dirname(__DIR__));
$cfg = require BASE_DIR . '/config/config.php';

$opts = getopt('', ['email:', 'password:', 'fresh']);
$email = $opts['email'] ?? 'admin@zoentekstil.com';
$pass  = $opts['password'] ?? 'zoen-admin-2026';
$fresh = isset($opts['fresh']);

$db = $cfg['db'];
$pdo = new PDO("mysql:host={$db['host']};port={$db['port']};charset={$db['charset']}", $db['user'], $db['pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

if ($fresh) {
    $pdo->exec("DROP DATABASE IF EXISTS `{$db['name']}`");
    echo "Dropped database {$db['name']}.\n";
}
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `{$db['name']}`");
echo "Database ready: {$db['name']}.\n";

$run = function (string $file) use ($pdo) {
    $sql = file_get_contents($file);
    if ($sql === false) {
        throw new RuntimeException("Cannot read $file");
    }
    $pdo->exec($sql);
    echo 'Ran ' . basename($file) . ".\n";
};

$run(BASE_DIR . '/database/schema.sql');

foreach (glob(BASE_DIR . '/database/migrations/*.sql') ?: [] as $mig) {
    // Migrations run statement-by-statement and tolerate "already applied" errors
    // (duplicate column / key) so re-running install on an existing DB is safe.
    $sql = (string) file_get_contents($mig);
    foreach (array_filter(array_map('trim', preg_split('/;\s*[\r\n]/', $sql))) as $stmt) {
        if ($stmt === '' || strncmp($stmt, '--', 2) === 0) {
            continue;
        }
        try {
            $pdo->exec($stmt);
        } catch (PDOException $e) {
            if (!preg_match('/duplicate (column|key)|already exists|check that column/i', $e->getMessage())) {
                throw $e;
            }
        }
    }
    echo 'Ran ' . basename($mig) . ".\n";
}

$hasRows = (int) $pdo->query('SELECT COUNT(*) FROM languages')->fetchColumn();
if ($hasRows === 0) {
    $run(BASE_DIR . '/database/seed.sql');
} else {
    echo "Seed skipped (languages already present).\n";
}

$hash = password_hash($pass, PASSWORD_DEFAULT);
$exists = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$exists->execute([$email]);
if ($id = $exists->fetchColumn()) {
    $pdo->prepare('UPDATE users SET password_hash = ?, role = "admin", is_active = 1 WHERE id = ?')
        ->execute([$hash, $id]);
    echo "Updated admin user $email.\n";
} else {
    $pdo->prepare('INSERT INTO users (name, email, password_hash, role, is_active, created_at) VALUES (?,?,?,"admin",1,NOW())')
        ->execute(['Administrator', $email, $hash]);
    echo "Created admin user $email.\n";
}

foreach (['uploads', 'storage/logs'] as $d) {
    $p = BASE_DIR . '/' . $d;
    if (!is_dir($p)) {
        mkdir($p, 0775, true);
    }
}

echo "\nDone.\n  Admin:  " . rtrim($cfg['app']['base_url'], '/') . "/admin/login\n";
echo "  Email:  $email\n  Pass:   $pass\n";
echo "\nNext (run in this order):\n";
echo "  php -d memory_limit=1536M scripts/import_media.php \"C:\\Users\\ercan\\Desktop\\zoen tekstil wtransfer\"\n";
echo "  php scripts/fix_logo.php \"C:\\Users\\ercan\\Desktop\\zoen tekstil wtransfer\\Logo maker project - 28 Ağustos 2026 13.40.33.png\"\n";
echo "  php scripts/gen_icons.php       (favicons, app icons, OG image, manifest)\n";
echo "  php scripts/seed_content.php    (demo content: pages, blog, UI strings)\n";
echo "  php scripts/seed_official.php   (REAL company content — must be LAST)\n";
