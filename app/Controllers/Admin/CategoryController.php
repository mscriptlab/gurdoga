<?php
namespace App\Controllers\Admin;

use App\Core\Database as DB;
use App\Core\Image;
use App\Models\Category;
use App\Models\Redirect;

class CategoryController extends AdminController
{
    public function index(): string
    {
        return $this->render('categories/index', ['categories' => Category::allForAdmin()]);
    }

    public function create(): string
    {
        return $this->render('categories/form', [
            'category'     => ['id' => 0, 'slug' => '', 'sort' => 0, 'is_active' => 1, 'image' => '', 'parent_id' => null],
            'translations' => [],
            'categories'   => Category::allForAdmin(),
        ]);
    }

    public function edit(string $id): string
    {
        $cat = Category::find((int) $id);
        if (!$cat) {
            $this->notFound();
        }
        return $this->render('categories/form', [
            'category'     => $cat,
            'translations' => Category::translations((int) $id),
            'categories'   => Category::allForAdmin(),
        ]);
    }

    public function store(): string
    {
        $id = $this->save(0);
        $this->redirectWith('/admin/categories/' . $id . '/edit', 'success', 'Kategori oluşturuldu.');
    }

    public function update(string $id): string
    {
        $this->save((int) $id);
        $this->redirectWith('/admin/categories', 'success', 'Kategori kaydedildi.');
    }

    private function save(int $id): int
    {
        $r = $this->request;
        $defName = trim((string) ($this->langInput('tr')[$this->languages[0]['id']]['name'] ?? ''));
        $slug = slugify($r->input('slug') ?: $defName ?: 'category');

        $old = $id ? Category::find($id) : null;
        // unique slug
        $try = $slug; $n = 2;
        while (DB::value('SELECT 1 FROM categories WHERE slug = ? AND id <> ?', [$try, $id])) {
            $try = $slug . '-' . $n++;
        }
        $slug = $try;

        $base = [
            'slug'      => $slug,
            'parent_id' => (int) $r->input('parent_id') ?: null,
            'sort'      => (int) $r->input('sort', 0),
            'is_active' => $r->input('is_active') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($file = ($r->files['image'] ?? null)) {
            if (($file['error'] ?? 4) === UPLOAD_ERR_OK) {
                try {
                    $base['image'] = Image::ingest($file, $slug)['path'];
                } catch (\Throwable $e) {
                    flash('error', 'Görsel: ' . $e->getMessage());
                }
            }
        }

        if ($id === 0) {
            $base['created_at'] = date('Y-m-d H:i:s');
            $id = DB::insert('categories', $base);
        } else {
            DB::update('categories', $base, 'id = :id', ['id' => $id]);
            if ($old && $old['slug'] !== $slug) {
                foreach ($this->languages as $l) {
                    if ($l['is_active']) {
                        Redirect::record(
                            '/' . $l['code'] . '/' . loc_path('products/' . $old['slug'], $l['code']),
                            '/' . $l['code'] . '/' . loc_path('products/' . $slug, $l['code'])
                        );
                    }
                }
            }
        }

        $langInput = $this->langInput('tr');
        foreach ($this->languages as $lang) {
            $lid = (int) $lang['id'];
            $row = $langInput[$lid] ?? [];
            $name = trim((string) ($row['name'] ?? ''));

            // per-language slug: explicit value, else from the name, else the base slug;
            // keep it unique within the language.
            $lslug = slugify((string) ($row['slug'] ?? '') ?: $name ?: $slug);
            $try = $lslug; $n = 2;
            while (DB::value(
                'SELECT 1 FROM category_translations WHERE slug = ? AND language_id = ? AND category_id <> ?',
                [$try, $lid, $id]
            )) {
                $try = $lslug . '-' . $n++;
            }
            $lslug = $try;

            $oldSlug = DB::value('SELECT slug FROM category_translations WHERE category_id = ? AND language_id = ?', [$id, $lid]);

            $payload = [
                'slug'             => $lslug,
                'name'             => $name,
                'description'      => (string) ($row['description'] ?? ''),
                'meta_title'       => trim((string) ($row['meta_title'] ?? '')),
                'meta_description' => trim((string) ($row['meta_description'] ?? '')),
            ];
            $ex = DB::one('SELECT id FROM category_translations WHERE category_id = ? AND language_id = ?', [$id, $lid]);
            if ($ex) {
                DB::update('category_translations', $payload, 'id = :id', ['id' => $ex['id']]);
            } else {
                DB::insert('category_translations', $payload + ['category_id' => $id, 'language_id' => $lid]);
            }
            if ($oldSlug && $oldSlug !== $lslug && $lang['is_active']) {
                Redirect::record(
                    '/' . $lang['code'] . '/' . loc_path('products/' . $oldSlug, $lang['code']),
                    '/' . $lang['code'] . '/' . loc_path('products/' . $lslug, $lang['code'])
                );
            }
        }
        return $id;
    }

    public function destroy(string $id): string
    {
        if ((int) DB::value('SELECT COUNT(*) FROM products WHERE category_id = ?', [(int) $id]) > 0) {
            $this->redirectWith('/admin/categories', 'error', 'Silinemez: kategoride ürün var. Önce ürünleri taşıyın.');
        }
        DB::delete('category_translations', 'category_id = :c', ['c' => (int) $id]);
        DB::delete('categories', 'id = :id', ['id' => (int) $id]);
        $this->redirectWith('/admin/categories', 'success', 'Kategori silindi.');
    }
}
