<?php
namespace App\Models;

use App\Core\Database as DB;
use App\Core\Lang;

class Menu
{
    /** Returns built menu items (with resolved url + label) for a location. */
    public static function location(string $location, ?int $langId = null): array
    {
        $langId ??= Lang::id();
        $rows = DB::all(
            'SELECT m.*, mt.label
             FROM menu_items m
             LEFT JOIN menu_item_translations mt ON mt.menu_item_id = m.id AND mt.language_id = ?
             WHERE m.location = ?
             ORDER BY m.sort, m.id',
            [$langId, $location]
        );
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'label' => $r['label'] ?: self::autoLabel($r, $langId),
                'url'   => self::resolveUrl($r),
                'raw'   => $r,
            ];
        }
        return $out;
    }

    private static function autoLabel(array $r, int $langId): string
    {
        switch ($r['type']) {
            case 'category':
                return Category::name((int) $r['ref_id'], $langId);
            case 'page':
                return (string) DB::value(
                    'SELECT title FROM page_translations WHERE page_id = ? AND language_id = ?',
                    [$r['ref_id'], $langId]
                ) ?: 'Page';
            default:
                return $r['url'] ?: 'Link';
        }
    }

    private static function resolveUrl(array $r): string
    {
        switch ($r['type']) {
            case 'home':
                return lang_url('');
            case 'products':
                return lang_url('products');
            case 'contact':
                return lang_url('contact');
            case 'category':
                $slug = DB::value('SELECT slug FROM categories WHERE id = ?', [$r['ref_id']]);
                return $slug ? lang_url('products/' . $slug) : lang_url('products');
            case 'page':
                $slug = DB::value('SELECT slug FROM pages WHERE id = ?', [$r['ref_id']]);
                return $slug ? lang_url('page/' . $slug) : lang_url('');
            case 'url':
            default:
                $u = (string) $r['url'];
                return preg_match('#^https?://#', $u) ? $u : lang_url(ltrim($u, '/'));
        }
    }

    public static function raw(string $location): array
    {
        return DB::all('SELECT * FROM menu_items WHERE location = ? ORDER BY sort, id', [$location]);
    }
}
