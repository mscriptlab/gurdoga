<?php
/**
 * Generates the favicon / app-icon set and a default social-share image from
 * the brand mark, writes a web manifest, and stores default_og_image.
 *
 *   php scripts/gen_icons.php
 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { exit("CLI only.\n"); }
define('BASE_DIR', dirname(__DIR__));
require BASE_DIR . '/app/helpers.php';
spl_autoload_register(function ($c) {
    if (strncmp($c, 'App\\', 4) === 0) {
        $p = BASE_DIR . '/app/' . str_replace('\\', '/', substr($c, 4)) . '.php';
        if (is_file($p)) require $p;
    }
});
use App\Core\Database as DB;
$cfg = require BASE_DIR . '/config/config.php';
DB::init($cfg['db']);

$dir = BASE_DIR . '/assets/icons';
if (!is_dir($dir)) { mkdir($dir, 0775, true); }

// Brand colours
$bg   = [0x1b, 0x1a, 0x17];   // --dark
$gold = [0xc9, 0xa8, 0x6a];

/** Draw the square app icon (dark ground, gold roof mark) at $size px. */
function icon(int $size, array $bg, array $gold, bool $transparent = false)
{
    $im = imagecreatetruecolor($size, $size);
    imagealphablending($im, false);
    imagesavealpha($im, true);
    if ($transparent) {
        imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));
    } else {
        // rounded-ish solid fill
        imagefill($im, 0, 0, imagecolorallocate($im, ...$bg));
    }
    imagealphablending($im, true);
    $stroke = imagecolorallocate($im, ...$gold);

    // roof: /\ centred, ~58% width
    $w = $size * 0.56;
    $x0 = ($size - $w) / 2;
    $x1 = $size / 2;
    $x2 = $size - $x0;
    $yb = $size * 0.66;
    $yt = $size * 0.36;
    $th = max(2, (int) round($size * 0.09));

    imagesetthickness($im, $th);
    imageline($im, (int) $x0, (int) $yb, (int) $x1, (int) $yt, $stroke);
    imageline($im, (int) $x1, (int) $yt, (int) $x2, (int) $yb, $stroke);
    // small vertical accent
    imageline($im, (int) $x1, (int) $yt, (int) $x1, (int) ($yt + $size * 0.14), $stroke);
    // round the caps
    $r = (int) round($th / 2);
    foreach ([[$x0, $yb], [$x2, $yb], [$x1, $yt]] as [$px, $py]) {
        imagefilledellipse($im, (int) $px, (int) $py, $r * 2, $r * 2, $stroke);
    }
    return $im;
}

$sizes = [32 => 'icon-32.png', 48 => 'icon-48.png', 180 => 'apple-touch-icon.png', 192 => 'icon-192.png', 512 => 'icon-512.png'];
foreach ($sizes as $s => $name) {
    $im = icon($s, $bg, $gold);
    imagepng($im, "$dir/$name", 6);
    imagedestroy($im);
}
// maskable (extra padding) 512
$mask = icon(512, $bg, $gold);
imagepng($mask, "$dir/maskable-512.png", 6);

// favicon.ico (16 + 32) — minimal ICO writer
function ico_from_pngs(array $pngPaths, string $out): void
{
    $imgs = [];
    foreach ($pngPaths as $p) {
        $data = file_get_contents($p);
        [$w, $h] = getimagesizefromstring($data);
        $imgs[] = ['w' => $w >= 256 ? 0 : $w, 'h' => $h >= 256 ? 0 : $h, 'data' => $data];
    }
    $count = count($imgs);
    $header = pack('vvv', 0, 1, $count);
    $offset = 6 + $count * 16;
    $dirent = '';
    $body = '';
    foreach ($imgs as $i) {
        $len = strlen($i['data']);
        $dirent .= pack('CCCCvvVV', $i['w'], $i['h'], 0, 0, 1, 32, $len, $offset);
        $offset += $len;
        $body .= $i['data'];
    }
    file_put_contents($out, $header . $dirent . $body);
}
$fav16 = icon(16, $bg, $gold); imagepng($fav16, "$dir/_f16.png"); imagedestroy($fav16);
ico_from_pngs(["$dir/_f16.png", "$dir/icon-32.png"], BASE_DIR . '/favicon.ico');
@unlink("$dir/_f16.png");

// -------- default OG / social image (1200x630) --------
$og = imagecreatetruecolor(1200, 630);
imagefill($og, 0, 0, imagecolorallocate($og, ...$bg));
// subtle gold glow from the top
for ($i = 0; $i < 320; $i++) {
    $alpha = 100 + (int) round($i / 320 * 27); // 100 -> 127 (transparent)
    imagefilledrectangle($og, 0, $i, 1200, $i, imagecolorallocatealpha($og, $gold[0], $gold[1], $gold[2], min(127, $alpha)));
}
$roof = icon(220, $bg, $gold, true);
imagecopy($og, $roof, 120, 150, 0, 0, 220, 220);
$white = imagecolorallocate($og, 255, 255, 255);
$goldc = imagecolorallocate($og, ...$gold);
// text — use built-in font scaled up
$font = null;
foreach (['C:/Windows/Fonts/segoeuib.ttf', 'C:/Windows/Fonts/arialbd.ttf', 'C:/Windows/Fonts/Arial.ttf'] as $f) {
    if (is_file($f)) { $font = $f; break; }
}
if ($font) {
    imagettftext($og, 62, 0, 120, 430, $white, $font, 'ZOEN TEKSTİL');
    imagettftext($og, 26, 0, 122, 485, $goldc, $font, 'Havlu Üreticisi · Denizli / Bursa');
    imagettftext($og, 20, 0, 122, 545, imagecolorallocate($og, 200, 195, 180), $font, '%100 Pamuk · OEKO-TEX® & GOTS (talep üzerine)');
} else {
    imagestring($og, 5, 120, 400, 'ZOEN TEKSTIL', $white);
}
$ogRel = 'assets/icons/og-default.png';
imagepng($og, BASE_DIR . '/' . $ogRel, 7);

DB::query('INSERT INTO settings (`key`,`value`) VALUES ("default_og_image", ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)', [$ogRel]);

// -------- web manifest --------
$manifest = [
    'name'             => 'Zoen Tekstil',
    'short_name'       => 'Zoen',
    'start_url'        => rtrim((string) $cfg['app']['base_path'], '/') . '/',
    'display'          => 'standalone',
    'background_color' => '#1b1a17',
    'theme_color'      => '#1b1a17',
    'icons'           => [
        ['src' => 'assets/icons/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png'],
        ['src' => 'assets/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png'],
        ['src' => 'assets/icons/maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
    ],
];
file_put_contents(BASE_DIR . '/site.webmanifest', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

echo "Icons written to assets/icons/, favicon.ico + site.webmanifest at root.\n";
echo "default_og_image set to $ogRel\n";
