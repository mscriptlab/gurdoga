<?php
/**
 * Import product images from the WeTransfer folder.
 *   php scripts/import_media.php "C:\Users\ercan\Desktop\zoen tekstil wtransfer"
 *
 * Subfolder -> category slug mapping below. Each image becomes a DRAFT product
 * (is_active = 0) with one product_image. Enrich name/description/SEO in the admin.
 * The logo file, if found, is set as the site logo.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("CLI only.\n");
}
ini_set('memory_limit', '1024M');
define('BASE_DIR', dirname(__DIR__));
require BASE_DIR . '/app/helpers.php';

spl_autoload_register(function (string $c): void {
    if (strncmp($c, 'App\\', 4) === 0) {
        $p = BASE_DIR . '/app/' . str_replace('\\', '/', substr($c, 4)) . '.php';
        if (is_file($p)) require $p;
    }
});

use App\Core\Database;
use App\Core\Image;

$cfg = require BASE_DIR . '/config/config.php';
Database::init($cfg['db']);

$src = $argv[1] ?? null;
if (!$src || !is_dir($src)) {
    exit("Usage: php scripts/import_media.php \"<folder>\"\n");
}

$map = [
    'BATHROOM'      => 'bathroom',
    'BEACH & POOL'  => 'beach-pool',
    'BEACH &POOL'   => 'beach-pool',
    'BEDDING'       => 'bedding',
    'HOTELS &SAUNA' => 'hotels-sauna',
    'HOTELS & SAUNA'=> 'hotels-sauna',
    'KITCHEN'       => 'kitchen',
    'PROMOTION'     => 'promotion',
];

$catId = [];
foreach (Database::all('SELECT id, slug FROM categories') as $c) {
    $catId[$c['slug']] = (int) $c['id'];
}
$defLang = (int) Database::value('SELECT id FROM languages WHERE is_default = 1');

$ok = 0; $fail = []; $skipLogo = null;
$exts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'tif', 'tiff'];

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS));
foreach ($it as $file) {
    /** @var SplFileInfo $file */
    if ($file->isDir()) continue;
    $ext = strtolower($file->getExtension());
    if (!in_array($ext, $exts, true)) continue;

    $name = $file->getBasename('.' . $file->getExtension());
    $rel  = trim(str_replace($src, '', $file->getPath()), "\\/");
    $topFolder = strtoupper(explode(DIRECTORY_SEPARATOR, $rel)[0] ?? '');

    if (stripos($name, 'logo') !== false && $topFolder === '') {
        try {
            $res = Image::importFromPath($file->getPathname(), 'zoen-logo');
            Database::query('INSERT INTO settings (`key`,`value`) VALUES ("logo", ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)', [$res['path']]);
            $skipLogo = $res['path'];
        } catch (Throwable $e) {
            $fail[] = $file->getFilename() . ' (logo): ' . $e->getMessage();
        }
        continue;
    }

    $slug = $map[$topFolder] ?? null;
    $cid  = $slug ? ($catId[$slug] ?? null) : null;

    try {
        $res = Image::importFromPath($file->getPathname(), $name);
    } catch (Throwable $e) {
        $fail[] = $file->getFilename() . ': ' . $e->getMessage();
        continue;
    }

    $pid = Database::insert('products', [
        'category_id' => $cid,
        'sku'         => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 20)),
        'cover_image' => $res['path'],
        'is_active'   => 0,
        'is_featured' => 0,
        'sort'        => 0,
        'created_at'  => date('Y-m-d H:i:s'),
        'updated_at'  => date('Y-m-d H:i:s'),
    ]);
    Database::insert('product_images', ['product_id' => $pid, 'path' => $res['path'], 'alt' => '', 'sort' => 0]);
    // minimal default-language translation so it has a slug
    $base = slugify($name);
    $try = $base; $n = 2;
    while (Database::value('SELECT 1 FROM product_translations WHERE slug = ? AND language_id = ?', [$try, $defLang])) {
        $try = $base . '-' . $n++;
    }
    Database::insert('product_translations', [
        'product_id' => $pid, 'language_id' => $defLang,
        'slug' => $try, 'name' => ucwords(str_replace(['-', '_'], ' ', $name)),
        'short_desc' => '', 'description' => '', 'meta_title' => '', 'meta_description' => '',
    ]);
    $ok++;
    echo "  + [$topFolder] {$file->getFilename()}\n";
}

echo "\n---------------------------------------------\n";
echo "Imported: $ok draft product(s)\n";
echo $skipLogo ? "Logo set: $skipLogo\n" : "Logo: not found / not set\n";
if ($fail) {
    echo "Failed (" . count($fail) . "):\n  - " . implode("\n  - ", $fail) . "\n";
    echo "\nTIFF files need the Imagick PHP extension. Enable it in php.ini or upload those images manually via the admin.\n";
}
echo "\nOpen the admin -> Products to name, describe and publish the drafts.\n";
