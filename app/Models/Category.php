<?php
namespace App\Models;

use App\Core\Database as DB;
use App\Core\Lang;

class Category
{
    /** All active categories with current-language translation, ordered. */
    public static function activeList(?int $langId = null): array
    {
        $langId ??= Lang::id();
        return DB::all(
            'SELECT c.*, COALESCE(NULLIF(ct.slug,""), c.slug) AS slug,
                    ct.name, ct.description, ct.meta_title, ct.meta_description
             FROM categories c
             LEFT JOIN category_translations ct ON ct.category_id = c.id AND ct.language_id = ?
             WHERE c.is_active = 1
             ORDER BY c.sort, c.id',
            [$langId]
        );
    }

    public static function allForAdmin(): array
    {
        $def = Lang::default()['id'];
        return DB::all(
            'SELECT c.*, ct.name
             FROM categories c
             LEFT JOIN category_translations ct ON ct.category_id = c.id AND ct.language_id = ?
             ORDER BY c.sort, c.id',
            [$def]
        );
    }

    public static function findBySlug(string $slug, ?int $langId = null): ?array
    {
        $langId ??= Lang::id();
        $select =
            'SELECT c.*, COALESCE(NULLIF(ct.slug,""), c.slug) AS slug,
                    ct.name, ct.description, ct.meta_title, ct.meta_description
             FROM categories c
             LEFT JOIN category_translations ct ON ct.category_id = c.id AND ct.language_id = ?
             WHERE c.is_active = 1 AND ';
        // localized slug first, then fall back to the base slug
        return DB::one($select . 'ct.slug = ? LIMIT 1', [$langId, $slug])
            ?: DB::one($select . 'c.slug = ? LIMIT 1', [$langId, $slug]);
    }

    /** [langCode => slug] for a category (localized slug, or base slug as fallback). */
    public static function slugsByLanguage(int $id): array
    {
        $rows = DB::all(
            'SELECT l.code, COALESCE(NULLIF(ct.slug,""), c.slug) AS slug
             FROM languages l
             JOIN categories c ON c.id = ?
             LEFT JOIN category_translations ct ON ct.category_id = c.id AND ct.language_id = l.id
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
        return DB::one('SELECT * FROM categories WHERE id = ?', [$id]);
    }

    public static function translations(int $id): array
    {
        $rows = DB::all('SELECT * FROM category_translations WHERE category_id = ?', [$id]);
        $out = [];
        foreach ($rows as $r) {
            $out[(int) $r['language_id']] = $r;
        }
        return $out;
    }

    public static function name(int $id, ?int $langId = null): string
    {
        $langId ??= Lang::id();
        return (string) DB::value(
            'SELECT COALESCE(NULLIF(ct.name,""), ct2.name, CONCAT("#", c.id))
             FROM categories c
             LEFT JOIN category_translations ct  ON ct.category_id = c.id AND ct.language_id = ?
             LEFT JOIN category_translations ct2 ON ct2.category_id = c.id AND ct2.language_id = ?
             WHERE c.id = ?',
            [$langId, Lang::default()['id'], $id]
        );
    }
}
