<?php
namespace App\Controllers\Admin;

use App\Core\Database as DB;
use App\Core\Image;
use App\Models\Category;
use App\Models\Product;
use App\Models\Redirect;

class ProductController extends AdminController
{
    public function index(): string
    {
        $filters = [
            'q'        => trim((string) $this->request->input('q', '')),
            'category' => (int) $this->request->input('category', 0),
        ];
        return $this->render('products/index', [
            'products'   => Product::adminList($filters),
            'categories' => Category::allForAdmin(),
            'filters'    => $filters,
        ]);
    }

    public function create(): string
    {
        return $this->render('products/form', [
            'product'      => ['id' => 0, 'category_id' => 0, 'sku' => '', 'buy_url' => '', 'is_active' => 1, 'is_featured' => 0, 'sort' => 0, 'cover_image' => ''],
            'translations' => [],
            'images'       => [],
            'attrText'     => [],
            'categories'   => Category::allForAdmin(),
        ]);
    }

    public function edit(string $id): string
    {
        $product = Product::find((int) $id);
        if (!$product) {
            $this->notFound();
        }
        return $this->render('products/form', [
            'product'      => $product,
            'translations' => Product::translations((int) $id),
            'images'       => Product::images((int) $id),
            'attrText'     => $this->attrText((int) $id),
            'categories'   => Category::allForAdmin(),
        ]);
    }

    public function store(): string
    {
        $id = $this->save(0);
        $this->redirectWith('/admin/products/' . $id . '/edit', 'success', 'Ürün oluşturuldu. Şimdi görsel ekleyin.');
    }

    public function update(string $id): string
    {
        $this->save((int) $id);
        $this->redirectWith('/admin/products/' . $id . '/edit', 'success', 'Ürün kaydedildi.');
    }

    private function save(int $id): int
    {
        $r = $this->request;
        $base = [
            'category_id' => (int) $r->input('category_id') ?: null,
            'sku'         => trim((string) $r->input('sku', '')),
            'buy_url'     => trim((string) $r->input('buy_url', '')),
            'is_active'   => $r->input('is_active') ? 1 : 0,
            'is_featured' => $r->input('is_featured') ? 1 : 0,
            'sort'        => (int) $r->input('sort', 0),
        ];

        if ($id === 0) {
            $base['created_at'] = date('Y-m-d H:i:s');
            $base['updated_at'] = date('Y-m-d H:i:s');
            $id = DB::insert('products', $base);
        } else {
            $base['updated_at'] = date('Y-m-d H:i:s');
            DB::update('products', $base, 'id = :id', ['id' => $id]);
        }

        $tr = $this->langInput('tr');
        foreach ($this->languages as $lang) {
            $lid = (int) $lang['id'];
            $row = $tr[$lid] ?? [];
            $name = trim((string) ($row['name'] ?? ''));
            $slug = slugify($row['slug'] ?? '' ?: $name);
            if ($name === '' && $slug === 'n-a') {
                continue;
            }
            // ensure unique slug per language
            $slug = $this->uniqueSlug($slug, $lid, $id);

            $existing = DB::one('SELECT * FROM product_translations WHERE product_id = ? AND language_id = ?', [$id, $lid]);
            $payload = [
                'name'             => $name,
                'slug'             => $slug,
                'short_desc'       => trim((string) ($row['short_desc'] ?? '')),
                'description'      => (string) ($row['description'] ?? ''),
                'meta_title'       => trim((string) ($row['meta_title'] ?? '')),
                'meta_description' => trim((string) ($row['meta_description'] ?? '')),
            ];
            if ($existing) {
                if ($existing['slug'] && $existing['slug'] !== $slug) {
                    Redirect::record('/' . $lang['code'] . '/product/' . $existing['slug'],
                                     '/' . $lang['code'] . '/product/' . $slug);
                }
                DB::update('product_translations', $payload, 'id = :id', ['id' => $existing['id']]);
            } else {
                DB::insert('product_translations', $payload + ['product_id' => $id, 'language_id' => $lid]);
            }
        }

        // attributes: one textarea per language, lines "Label | Value" (or "Label: Value")
        DB::delete('product_attributes', 'product_id = :p', ['p' => $id]);
        $attr = (array) ($r->post['attr'] ?? []);
        foreach ($attr as $lid => $text) {
            foreach (preg_split('/\r\n|\r|\n/', (string) $text) as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                $parts = preg_split('/\s*[|:]\s*/', $line, 2);
                DB::insert('product_attributes', [
                    'product_id'  => $id,
                    'language_id' => (int) $lid,
                    'label'       => trim($parts[0]),
                    'value'       => trim($parts[1] ?? ''),
                ]);
            }
        }

        // cover image selection
        if ($cover = $r->input('cover_image')) {
            DB::update('products', ['cover_image' => $cover], 'id = :id', ['id' => $id]);
        }

        return $id;
    }

    private function uniqueSlug(string $slug, int $langId, int $productId): string
    {
        $try = $slug;
        $n = 2;
        while (Product::slugExists($try, $langId, $productId)) {
            $try = $slug . '-' . $n++;
        }
        return $try;
    }

    public function uploadImage(string $id): string
    {
        $product = Product::find((int) $id);
        if (!$product) {
            $this->notFound();
        }
        $files = $this->request->files['images'] ?? null;
        $ok = 0;
        $fail = [];
        if ($files && is_array($files['name'])) {
            $count = count($files['name']);
            $maxSort = (int) DB::value('SELECT COALESCE(MAX(sort),0) FROM product_images WHERE product_id = ?', [$id]);
            for ($i = 0; $i < $count; $i++) {
                if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    continue;
                }
                try {
                    $res = Image::ingest([
                        'error'    => $files['error'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'name'     => $files['name'][$i],
                    ], pathinfo($files['name'][$i], PATHINFO_FILENAME));
                    DB::insert('product_images', [
                        'product_id' => $id,
                        'path'       => $res['path'],
                        'alt'        => '',
                        'sort'       => ++$maxSort,
                    ]);
                    if (empty($product['cover_image'])) {
                        DB::update('products', ['cover_image' => $res['path']], 'id = :id', ['id' => $id]);
                        $product['cover_image'] = $res['path'];
                    }
                    $ok++;
                } catch (\Throwable $e) {
                    $fail[] = $files['name'][$i] . ': ' . $e->getMessage();
                }
            }
        }
        $msg = "$ok görsel yüklendi." . ($fail ? ' Başarısız: ' . implode('; ', $fail) : '');

        if ($this->request->isAjax()) {
            $images = array_map(function (array $im) use ($product) {
                return [
                    'id'      => (int) $im['id'],
                    'path'    => $im['path'],
                    'url'     => media($im['path']),
                    'alt'     => $im['alt'],
                    'sort'    => (int) $im['sort'],
                    'isCover' => $im['path'] === $product['cover_image'],
                ];
            }, Product::images((int) $id));
            return $this->json(['ok' => !$fail || $ok > 0, 'message' => $msg, 'fail' => $fail, 'images' => $images]);
        }
        $this->redirectWith('/admin/products/' . $id . '/edit', $fail ? 'error' : 'success', $msg);
    }

    public function reorderImages(string $id): string
    {
        $order = (array) ($this->request->post['order'] ?? []);
        $sort = 0;
        foreach ($order as $imgId) {
            DB::query(
                'UPDATE product_images SET sort = ? WHERE id = ? AND product_id = ?',
                [$sort++, (int) $imgId, (int) $id]
            );
        }
        if ($this->request->isAjax()) {
            return $this->json(['ok' => true]);
        }
        $this->redirectWith('/admin/products/' . $id . '/edit', 'success', 'Sıralama kaydedildi.');
    }

    public function setCover(string $id): string
    {
        $path = (string) $this->request->input('cover_image', '');
        if ($path && DB::value('SELECT 1 FROM product_images WHERE product_id = ? AND path = ?', [(int) $id, $path])) {
            DB::update('products', ['cover_image' => $path], 'id = :id', ['id' => (int) $id]);
        }
        $this->redirectWith('/admin/products/' . $id . '/edit', 'success', 'Kapak görseli güncellendi.');
    }

    public function saveAlts(string $id): string
    {
        foreach ((array) ($this->request->post['alt'] ?? []) as $imgId => $alt) {
            DB::query('UPDATE product_images SET alt = ? WHERE id = ? AND product_id = ?',
                [trim((string) $alt), (int) $imgId, (int) $id]);
        }
        $this->redirectWith('/admin/products/' . $id . '/edit', 'success', 'Görsel alt metinleri kaydedildi.');
    }

    public function deleteImage(string $id): string
    {
        $img = DB::one('SELECT * FROM product_images WHERE id = ?', [(int) $id]);
        if ($img) {
            @unlink(BASE_DIR . '/' . $img['path']);
            DB::delete('product_images', 'id = :id', ['id' => (int) $id]);
        }
        $this->redirectWith('/admin/products/' . ($img['product_id'] ?? '') . '/edit', 'success', 'Görsel silindi.');
    }

    public function destroy(string $id): string
    {
        foreach (Product::images((int) $id) as $img) {
            @unlink(BASE_DIR . '/' . $img['path']);
        }
        DB::delete('product_images', 'product_id = :p', ['p' => (int) $id]);
        DB::delete('product_translations', 'product_id = :p', ['p' => (int) $id]);
        DB::delete('product_attributes', 'product_id = :p', ['p' => (int) $id]);
        DB::delete('products', 'id = :id', ['id' => (int) $id]);
        $this->redirectWith('/admin/products', 'success', 'Ürün silindi.');
    }

    private function attrText(int $id): array
    {
        $out = [];
        foreach (DB::all('SELECT * FROM product_attributes WHERE product_id = ? ORDER BY id', [$id]) as $a) {
            $out[(int) $a['language_id']][] = $a['label'] . ' | ' . $a['value'];
        }
        return array_map(fn($lines) => implode("\n", $lines), $out);
    }
}
