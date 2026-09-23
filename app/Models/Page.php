<?php
namespace App\Models;

use App\Core\Database as DB;
use App\Core\Lang;

class Page
{
    public static function findBySlug(string $slug, ?int $langId = null): ?array
    {
        $langId ??= Lang::id();
        $select =
            'SELECT p.*, COALESCE(NULLIF(pt.slug,""), p.slug) AS slug,
                    pt.title, pt.body, pt.meta_title, pt.meta_description, pt.og_image
             FROM pages p
             LEFT JOIN page_translations pt ON pt.page_id = p.id AND pt.language_id = ?
             WHERE p.is_active = 1 AND ';
        return DB::one($select . 'pt.slug = ? LIMIT 1', [$langId, $slug])
            ?: DB::one($select . 'p.slug = ? LIMIT 1', [$langId, $slug]);
    }

    /** [langCode => slug] for a page (localized slug, or base slug as fallback). */
    public static function slugsByLanguage(int $id): array
    {
        $rows = DB::all(
            'SELECT l.code, COALESCE(NULLIF(pt.slug,""), p.slug) AS slug
             FROM languages l
             JOIN pages p ON p.id = ?
             LEFT JOIN page_translations pt ON pt.page_id = p.id AND pt.language_id = l.id
             WHERE l.is_active = 1',
            [$id]
        );
        $out = [];
        foreach ($rows as $r) {
            $out[$r['code']] = $r['slug'];
        }
        return $out;
    }

    public static function find(int $id): ?array
    {
        return DB::one('SELECT * FROM pages WHERE id = ?', [$id]);
    }

    public static function all(): array
    {
        // Title for the admin list falls back to the default language; slug is localized
        // to the *current* language so front-end navigation links are correct.
        $def = Lang::default()['id'];
        return DB::all(
            'SELECT p.*,
                    COALESCE(NULLIF(ptc.slug,""), p.slug) AS slug,
                    COALESCE(NULLIF(ptc.title,""), ptd.title) AS title
             FROM pages p
             LEFT JOIN page_translations ptc ON ptc.page_id = p.id AND ptc.language_id = ?
             LEFT JOIN page_translations ptd ON ptd.page_id = p.id AND ptd.language_id = ?
             ORDER BY p.sort, p.id',
            [Lang::id(), $def]
        );
    }

    public static function translations(int $id): array
    {
        $out = [];
        foreach (DB::all('SELECT * FROM page_translations WHERE page_id = ?', [$id]) as $r) {
            $out[(int) $r['language_id']] = $r;
        }
        return $out;
    }

    public static function activeSlugs(): array
    {
        return DB::all('SELECT slug, updated_at FROM pages WHERE is_active = 1 ORDER BY sort, id');
    }
}
