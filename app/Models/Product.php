<?php
namespace App\Models;

use App\Core\Database as DB;
use App\Core\Lang;

class Product
{
    private const SELECT =
        'p.*, pt.slug, pt.name, pt.short_desc, pt.description, pt.meta_title, pt.meta_description';

    public static function findBySlug(string $slug, ?int $langId = null): ?array
    {
        $langId ??= Lang::id();
        return DB::one(
            'SELECT ' . self::SELECT . '
             FROM products p
             JOIN product_translations pt ON pt.product_id = p.id AND pt.language_id = ?
             WHERE pt.slug = ? AND p.is_active = 1',
            [$langId, $slug]
        );
    }

    public static function find(int $id): ?array
    {
        return DB::one('SELECT * FROM products WHERE id = ?', [$id]);
    }

    public static function byCategory(int $categoryId, ?int $langId = null, int $limit = 60, int $offset = 0): array
    {
        $langId ??= Lang::id();
        return DB::all(
            'SELECT ' . self::SELECT . '
             FROM products p
             LEFT JOIN product_translations pt ON pt.product_id = p.id AND pt.language_id = ?
             WHERE p.category_id = ? AND p.is_active = 1
             ORDER BY p.sort, p.id
             LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset,
            [$langId, $categoryId]
        );
    }

    public static function countByCategory(int $categoryId): int
    {
        return (int) DB::value('SELECT COUNT(*) FROM products WHERE category_id = ? AND is_active = 1', [$categoryId]);
    }

    public static function featured(int $limit = 8, ?int $langId = null): array
    {
        $langId ??= Lang::id();
        return DB::all(
            'SELECT ' . self::SELECT . '
             FROM products p
             LEFT JOIN product_translations pt ON pt.product_id = p.id AND pt.language_id = ?
             WHERE p.is_active = 1 AND p.is_featured = 1
             ORDER BY p.sort, p.id LIMIT ' . (int) $limit,
            [$langId]
        );
    }

    public static function search(string $q, ?int $langId = null, int $limit = 40): array
    {
        $langId ??= Lang::id();
        $like = '%' . $q . '%';
        return DB::all(
            'SELECT ' . self::SELECT . '
             FROM products p
             JOIN product_translations pt ON pt.product_id = p.id AND pt.language_id = ?
             WHERE p.is_active = 1 AND (pt.name LIKE ? OR pt.short_desc LIKE ? OR p.sku LIKE ?)
             ORDER BY p.sort, p.id LIMIT ' . (int) $limit,
            [$langId, $like, $like, $like]
        );
    }

    public static function related(int $productId, ?int $categoryId, ?int $langId = null, int $limit = 4): array
    {
        $langId ??= Lang::id();
        $rows = [];
        if ($categoryId) {
            $rows = DB::all(
                'SELECT ' . self::SELECT . '
                 FROM products p
                 JOIN product_translations pt ON pt.product_id = p.id AND pt.language_id = ?
                 WHERE p.category_id = ? AND p.id <> ? AND p.is_active = 1 AND pt.slug <> ""
                 ORDER BY p.is_featured DESC, RAND() LIMIT ' . (int) $limit,
                [$langId, $categoryId, $productId]
            );
        }
        if (count($rows) < $limit) {
            $have = array_column($rows, 'id');
            $have[] = $productId;
            $more = DB::all(
                'SELECT ' . self::SELECT . '
                 FROM products p
                 JOIN product_translations pt ON pt.product_id = p.id AND pt.language_id = ?
                 WHERE p.is_active = 1 AND pt.slug <> "" AND p.id NOT IN (' . implode(',', array_map('intval', $have ?: [0])) . ')
                 ORDER BY p.is_featured DESC, RAND() LIMIT ' . (int) ($limit - count($rows)),
                [$langId]
            );
            $rows = array_merge($rows, $more);
        }
        return $rows;
    }

    public static function images(int $productId): array
    {
        return DB::all('SELECT * FROM product_images WHERE product_id = ? ORDER BY sort, id', [$productId]);
    }

    public static function cover(array $product): ?string
    {
        if (!empty($product['cover_image'])) {
            return $product['cover_image'];
        }
        return DB::value('SELECT path FROM product_images WHERE product_id = ? ORDER BY sort, id LIMIT 1', [$product['id']]);
    }

    public static function attributes(int $productId, ?int $langId = null): array
    {
        $langId ??= Lang::id();
        $rows = DB::all(
            'SELECT label, value FROM product_attributes WHERE product_id = ? AND language_id = ? ORDER BY id',
            [$productId, $langId]
        );
        if (!$rows) {
            $rows = DB::all(
                'SELECT label, value FROM product_attributes WHERE product_id = ? AND language_id = ? ORDER BY id',
                [$productId, Lang::default()['id']]
            );
        }
        return $rows;
    }

    public static function translations(int $id): array
    {
        $out = [];
        foreach (DB::all('SELECT * FROM product_translations WHERE product_id = ?', [$id]) as $r) {
            $out[(int) $r['language_id']] = $r;
        }
        return $out;
    }

    /** Localized alternates for hreflang: code => slug */
    public static function slugsByLanguage(int $id): array
    {
        $out = [];
        foreach (DB::all(
            'SELECT l.code, pt.slug FROM product_translations pt
             JOIN languages l ON l.id = pt.language_id
             WHERE pt.product_id = ? AND pt.slug <> "" AND l.is_active = 1',
            [$id]
        ) as $r) {
            $out[$r['code']] = $r['slug'];
        }
        return $out;
    }

    public static function adminList(array $filters = []): array
    {
        $def = Lang::default()['id'];
        $where = ['1=1'];
        $params = [$def];
        if (!empty($filters['q'])) {
            $where[] = '(pt.name LIKE ? OR p.sku LIKE ?)';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }
        if (!empty($filters['category'])) {
            $where[] = 'p.category_id = ?';
            $params[] = (int) $filters['category'];
        }
        return DB::all(
            'SELECT p.*, pt.name FROM products p
             LEFT JOIN product_translations pt ON pt.product_id = p.id AND pt.language_id = ?
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY p.sort, p.id DESC LIMIT 500',
            $params
        );
    }

    public static function slugExists(string $slug, int $langId, int $exceptId = 0): bool
    {
        return (bool) DB::value(
            'SELECT 1 FROM product_translations WHERE slug = ? AND language_id = ? AND product_id <> ?',
            [$slug, $langId, $exceptId]
        );
    }
}
