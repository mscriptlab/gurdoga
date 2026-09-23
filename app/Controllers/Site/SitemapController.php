<?php
namespace App\Controllers\Site;

use App\Core\Controller;
use App\Core\Database as DB;
use App\Core\Lang;
use App\Core\Settings;

class SitemapController extends Controller
{
    public function index(): string
    {
        header('Content-Type: application/xml; charset=utf-8');
        header('X-Robots-Tag: noindex');
        $langs = array_values(array_filter(Lang::all(), fn($l) => (int) $l['is_active'] === 1));
        $default = Lang::default()['code'];
        $urls = [];

        // Static localized routes
        $urls[] = $this->entry('', $langs, null, '1.0', 'weekly');
        $urls[] = $this->entry('products', $langs, null, '0.9', 'weekly');
        $urls[] = $this->entry('blog', $langs, null, '0.7', 'weekly');
        $urls[] = $this->entry('contact', $langs, null, '0.5', 'yearly');

        // Categories (per-language slug) + representative image
        foreach (DB::all('SELECT id, slug, image, updated_at FROM categories WHERE is_active = 1 ORDER BY sort, id') as $c) {
            $img = $c['image'] ? [abs_url(ltrim($c['image'], '/'))] : [];
            $slugs = \App\Models\Category::slugsByLanguage((int) $c['id']);
            $alts = [];
            foreach ($langs as $l) {
                $alts[$l['code']] = lang_abs('products/' . ($slugs[$l['code']] ?? $c['slug']), $l['code']);
            }
            $urls[] = $this->urlNode($alts[$default] ?? reset($alts), $c['updated_at'] ?? null, $alts, $default, '0.8', 'weekly', $img);
        }

        // Pages (per-language slug)
        foreach (DB::all('SELECT id, slug, updated_at FROM pages WHERE is_active = 1 ORDER BY sort, id') as $p) {
            $slugs = \App\Models\Page::slugsByLanguage((int) $p['id']);
            $alts = [];
            foreach ($langs as $l) {
                $alts[$l['code']] = lang_abs('page/' . ($slugs[$l['code']] ?? $p['slug']), $l['code']);
            }
            $urls[] = $this->urlNode($alts[$default] ?? reset($alts), $p['updated_at'] ?? null, $alts, $default, '0.6', 'monthly');
        }

        // Blog posts (per-language slug)
        $prows = DB::all(
            'SELECT p.id, COALESCE(p.updated_at, p.published_at) AS lm, p.cover_image, l.code, pt.slug
             FROM posts p
             JOIN post_translations pt ON pt.post_id = p.id
             JOIN languages l ON l.id = pt.language_id AND l.is_active = 1
             WHERE p.is_active = 1 AND pt.slug <> "" AND (p.published_at IS NULL OR p.published_at <= NOW())'
        );
        $byPost = [];
        foreach ($prows as $r) {
            $byPost[$r['id']]['lm'] = $r['lm'];
            $byPost[$r['id']]['img'] = $r['cover_image'];
            $byPost[$r['id']]['slugs'][$r['code']] = $r['slug'];
        }
        foreach ($byPost as $pd) {
            $alts = [];
            foreach ($pd['slugs'] as $code => $slug) {
                $alts[$code] = lang_abs('blog/' . $slug, $code);
            }
            $loc = $alts[$default] ?? reset($alts);
            $img = !empty($pd['img']) ? [abs_url(ltrim($pd['img'], '/'))] : [];
            $urls[] = $this->urlNode($loc, $pd['lm'], $alts, $default, '0.6', 'monthly', $img);
        }

        // Products (per-language slug) + images
        $rows = DB::all(
            'SELECT p.id, p.updated_at, l.code, pt.slug
             FROM products p
             JOIN product_translations pt ON pt.product_id = p.id
             JOIN languages l ON l.id = pt.language_id AND l.is_active = 1
             WHERE p.is_active = 1 AND pt.slug <> ""'
        );
        $byProduct = [];
        foreach ($rows as $r) {
            $byProduct[$r['id']]['lastmod'] = $r['updated_at'] ?? null;
            $byProduct[$r['id']]['slugs'][$r['code']] = $r['slug'];
        }
        foreach ($byProduct as $pid => $pd) {
            $alts = [];
            foreach ($pd['slugs'] as $code => $slug) {
                $alts[$code] = lang_abs('product/' . $slug, $code);
            }
            $loc = $alts[$default] ?? reset($alts);
            $imgs = [];
            foreach (DB::all('SELECT path FROM product_images WHERE product_id = ? ORDER BY sort, id LIMIT 6', [$pid]) as $im) {
                $imgs[] = abs_url(ltrim($im['path'], '/'));
            }
            $urls[] = $this->urlNode($loc, $pd['lastmod'], $alts, $default, '0.7', 'monthly', $imgs);
        }

        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'
            . ' xmlns:xhtml="http://www.w3.org/1999/xhtml"'
            . ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n"
            . implode("\n", $urls) . "\n</urlset>";
    }

    private function entry(string $path, array $langs, ?string $lastmod, string $priority = '0.7', string $freq = 'weekly', array $images = []): string
    {
        $alts = [];
        foreach ($langs as $l) {
            $alts[$l['code']] = lang_abs($path, $l['code']);
        }
        $default = Lang::default()['code'];
        return $this->urlNode($alts[$default], $lastmod, $alts, $default, $priority, $freq, $images);
    }

    private function urlNode(string $loc, ?string $lastmod, array $alts, string $default, string $priority, string $freq, array $images = []): string
    {
        $s = "  <url>\n    <loc>" . e($loc) . "</loc>\n";
        if ($lastmod) {
            $s .= '    <lastmod>' . date('Y-m-d', strtotime($lastmod)) . "</lastmod>\n";
        }
        $s .= "    <changefreq>$freq</changefreq>\n    <priority>$priority</priority>\n";
        foreach ($alts as $code => $href) {
            $s .= '    <xhtml:link rel="alternate" hreflang="' . e($code) . '" href="' . e($href) . "\"/>\n";
        }
        $s .= '    <xhtml:link rel="alternate" hreflang="x-default" href="' . e($alts[$default]) . "\"/>\n";
        foreach ($images as $img) {
            $s .= '    <image:image><image:loc>' . e($img) . "</image:loc></image:image>\n";
        }
        $s .= '  </url>';
        return $s;
    }

    public function robots(): string
    {
        header('Content-Type: text/plain; charset=utf-8');
        $custom = Settings::raw('robots_txt');
        if ($custom) {
            return $custom;
        }
        return "User-agent: *\n"
            . "Allow: /\n"
            . "Disallow: /admin\n"
            . "Disallow: /*/search\n"
            . "Disallow: /*/arama\n"
            . "Disallow: /*/suche\n"
            . "Disallow: /*/quote\n"
            . "Disallow: /*/teklif\n"
            . "Disallow: /*/angebot\n\n"
            . 'Sitemap: ' . abs_url('sitemap.xml') . "\n";
    }
}
