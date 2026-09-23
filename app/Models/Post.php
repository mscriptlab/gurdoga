<?php
namespace App\Models;

use App\Core\Database as DB;
use App\Core\Lang;

class Post
{
    private const SELECT = 'p.*, pt.slug, pt.title, pt.excerpt, pt.body, pt.meta_title, pt.meta_description';

    public static function published(?int $langId = null, int $limit = 12, int $offset = 0): array
    {
        $langId ??= Lang::id();
        return DB::all(
            'SELECT ' . self::SELECT . '
             FROM posts p
             JOIN post_translations pt ON pt.post_id = p.id AND pt.language_id = ?
             WHERE p.is_active = 1 AND pt.slug <> "" AND (p.published_at IS NULL OR p.published_at <= NOW())
             ORDER BY p.published_at DESC, p.id DESC
             LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset,
            [$langId]
        );
    }

    public static function countPublished(): int
    {
        return (int) DB::value(
            'SELECT COUNT(*) FROM posts p
             JOIN post_translations pt ON pt.post_id = p.id AND pt.language_id = ?
             WHERE p.is_active = 1 AND pt.slug <> "" AND (p.published_at IS NULL OR p.published_at <= NOW())',
            [Lang::id()]
        );
    }

    public static function findBySlug(string $slug, ?int $langId = null): ?array
    {
        $langId ??= Lang::id();
        return DB::one(
            'SELECT ' . self::SELECT . '
             FROM posts p
             JOIN post_translations pt ON pt.post_id = p.id AND pt.language_id = ?
             WHERE pt.slug = ? AND p.is_active = 1',
            [$langId, $slug]
        );
    }

    public static function find(int $id): ?array
    {
        return DB::one('SELECT * FROM posts WHERE id = ?', [$id]);
    }

    public static function recent(int $exceptId = 0, int $limit = 3, ?int $langId = null): array
    {
        $langId ??= Lang::id();
        return DB::all(
            'SELECT ' . self::SELECT . '
             FROM posts p
             JOIN post_translations pt ON pt.post_id = p.id AND pt.language_id = ?
             WHERE p.is_active = 1 AND pt.slug <> "" AND p.id <> ?
             ORDER BY p.published_at DESC, p.id DESC LIMIT ' . (int) $limit,
            [$langId, $exceptId]
        );
    }

    public static function translations(int $id): array
    {
        $out = [];
        foreach (DB::all('SELECT * FROM post_translations WHERE post_id = ?', [$id]) as $r) {
            $out[(int) $r['language_id']] = $r;
        }
        return $out;
    }

    public static function slugsByLanguage(int $id): array
    {
        $out = [];
        foreach (DB::all(
            'SELECT l.code, pt.slug FROM post_translations pt
             JOIN languages l ON l.id = pt.language_id
             WHERE pt.post_id = ? AND pt.slug <> "" AND l.is_active = 1',
            [$id]
        ) as $r) {
            $out[$r['code']] = $r['slug'];
        }
        return $out;
    }

    public static function adminList(): array
    {
        $def = Lang::default()['id'];
        return DB::all(
            'SELECT p.*, pt.title FROM posts p
             LEFT JOIN post_translations pt ON pt.post_id = p.id AND pt.language_id = ?
             ORDER BY p.published_at DESC, p.id DESC LIMIT 500',
            [$def]
        );
    }

    public static function slugExists(string $slug, int $langId, int $exceptId = 0): bool
    {
        return (bool) DB::value(
            'SELECT 1 FROM post_translations WHERE slug = ? AND language_id = ? AND post_id <> ?',
            [$slug, $langId, $exceptId]
        );
    }
}
