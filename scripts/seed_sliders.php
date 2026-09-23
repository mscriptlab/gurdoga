<?php
/**
 * One-off seed: create the `sliders` table content from the current 3
 * home-page slides (bathroom, beach-pool, bedding), using the FULL category
 * description (not the truncated SEO meta_description) so nothing regresses
 * once home.php switches to reading from `sliders`.
 *
 *   php scripts/seed_sliders.php
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("CLI only.\n");
}
define('BASE_DIR', dirname(__DIR__));
require BASE_DIR . '/app/helpers.php';

spl_autoload_register(function (string $c): void {
    if (strncmp($c, 'App\\', 4) === 0) {
        $p = BASE_DIR . '/app/' . str_replace('\\', '/', substr($c, 4)) . '.php';
        if (is_file($p)) require $p;
    }
});

use App\Core\Database;

$cfg = require BASE_DIR . '/config/config.php';
Database::init($cfg['db']);

if ((int) Database::value('SELECT COUNT(*) FROM sliders') > 0) {
    exit("sliders already has rows — aborting so nothing gets duplicated.\n");
}

$langs = Database::all('SELECT id, code FROM languages');
$homeDesc = (string) Database::value("SELECT value FROM settings WHERE `key` = 'home_meta_description'");
$siteName = (string) Database::value("SELECT value FROM settings WHERE `key` = 'site_name'") ?: 'Zoen Tekstil';
$tagline  = (string) Database::value("SELECT value FROM settings WHERE `key` = 'site_tagline'")
    ?: 'We weave comfort into every home.';

$catIds = [1, 2, 3]; // bathroom, beach-pool, bedding — same 3 categories the old home.php slider used
$sort = 0;
foreach ($catIds as $catId) {
    $cat = Database::one('SELECT id, image FROM categories WHERE id = ?', [$catId]);
    if (!$cat) continue;

    $sliderId = Database::insert('sliders', [
        'image'      => $cat['image'],
        'sort'       => $sort,
        'is_active'  => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);

    foreach ($langs as $lang) {
        $lid  = (int) $lang['id'];
        $code = $lang['code'];
        $ct = Database::one(
            'SELECT name, slug, description FROM category_translations WHERE category_id = ? AND language_id = ?',
            [$catId, $lid]
        );
        $catName = $ct['name'] ?? '';
        $slug    = $ct['slug'] ?? '';
        $linkUrl = 'products/' . $slug;

        $isFirst = ($sort === 0);
        Database::insert('slider_translations', [
            'slider_id'   => $sliderId,
            'language_id' => $lid,
            'eyebrow'     => $siteName . ' — ' . $catName,
            'title'       => $isFirst ? $tagline : $catName,
            'description' => $isFirst ? $homeDesc : ($ct['description'] ?? ''),
            'button_text' => '',
            'link_url'    => $linkUrl,
        ]);
    }

    echo "  + slider for category #$catId (sort $sort)\n";
    $sort++;
}

echo "\nDone. Edit content at /admin/sliders.\n";
