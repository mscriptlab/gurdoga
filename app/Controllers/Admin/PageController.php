<?php
namespace App\Controllers\Admin;

use App\Core\Database as DB;
use App\Models\Page;
use App\Models\Redirect;

class PageController extends AdminController
{
    public function index(): string
    {
        return $this->render('pages/index', ['pages' => Page::all()]);
    }

    public function create(): string
    {
        return $this->render('pages/form', [
            'page'         => ['id' => 0, 'slug' => '', 'template' => 'default', 'is_active' => 1, 'nav_placement' => 'corporate', 'sort' => 0],
            'translations' => [],
        ]);
    }

    public function edit(string $id): string
    {
        $page = Page::find((int) $id);
        if (!$page) {
            $this->notFound();
        }
        return $this->render('pages/form', [
            'page'         => $page,
            'translations' => Page::translations((int) $id),
        ]);
    }

    public function store(): string
    {
        $id = $this->save(0);
        $this->redirectWith('/admin/pages/' . $id . '/edit', 'success', 'Sayfa oluşturuldu.');
    }

    public function update(string $id): string
    {
        $this->save((int) $id);
        $this->redirectWith('/admin/pages', 'success', 'Sayfa kaydedildi.');
    }

    private function save(int $id): int
    {
        $r = $this->request;
        $tr = $this->langInput('tr');
        $defTitle = trim((string) ($tr[$this->languages[0]['id']]['title'] ?? ''));
        $slug = slugify($r->input('slug') ?: $defTitle ?: 'page');

        $try = $slug; $n = 2;
        while (DB::value('SELECT 1 FROM pages WHERE slug = ? AND id <> ?', [$try, $id])) {
            $try = $slug . '-' . $n++;
        }
        $slug = $try;

        $old = $id ? Page::find($id) : null;
        $nav = $r->input('nav_placement', 'corporate');
        if (!in_array($nav, ['corporate', 'top', 'none'], true)) {
            $nav = 'corporate';
        }
        $base = [
            'slug'          => $slug,
            'template'      => $r->input('template', 'default'),
            'is_active'     => $r->input('is_active') ? 1 : 0,
            'nav_placement' => $nav,
            'sort'          => (int) $r->input('sort', 0),
            'updated_at'    => date('Y-m-d H:i:s'),
        ];
        if ($id === 0) {
            $base['created_at'] = date('Y-m-d H:i:s');
            $id = DB::insert('pages', $base);
        } else {
            DB::update('pages', $base, 'id = :id', ['id' => $id]);
            if ($old && $old['slug'] !== $slug) {
                foreach ($this->languages as $l) {
                    if ($l['is_active']) {
                        Redirect::record(
                            '/' . $l['code'] . '/' . loc_path('page/' . $old['slug'], $l['code']),
                            '/' . $l['code'] . '/' . loc_path('page/' . $slug, $l['code'])
                        );
                    }
                }
            }
        }

        foreach ($this->languages as $lang) {
            $lid = (int) $lang['id'];
            $row = $tr[$lid] ?? [];
            $ptitle = trim((string) ($row['title'] ?? ''));

            $lslug = slugify((string) ($row['slug'] ?? '') ?: $ptitle ?: $slug);
            $try = $lslug; $n = 2;
            while (DB::value(
                'SELECT 1 FROM page_translations WHERE slug = ? AND language_id = ? AND page_id <> ?',
                [$try, $lid, $id]
            )) {
                $try = $lslug . '-' . $n++;
            }
            $lslug = $try;
            $oldSlug = DB::value('SELECT slug FROM page_translations WHERE page_id = ? AND language_id = ?', [$id, $lid]);

            $payload = [
                'slug'             => $lslug,
                'title'            => $ptitle,
                'body'             => (string) ($row['body'] ?? ''),
                'meta_title'       => trim((string) ($row['meta_title'] ?? '')),
                'meta_description' => trim((string) ($row['meta_description'] ?? '')),
                'og_image'         => trim((string) ($row['og_image'] ?? '')),
            ];
            $ex = DB::one('SELECT id FROM page_translations WHERE page_id = ? AND language_id = ?', [$id, $lid]);
            if ($ex) {
                DB::update('page_translations', $payload, 'id = :id', ['id' => $ex['id']]);
            } else {
                DB::insert('page_translations', $payload + ['page_id' => $id, 'language_id' => $lid]);
            }
            if ($oldSlug && $oldSlug !== $lslug && $lang['is_active']) {
                Redirect::record(
                    '/' . $lang['code'] . '/' . loc_path('page/' . $oldSlug, $lang['code']),
                    '/' . $lang['code'] . '/' . loc_path('page/' . $lslug, $lang['code'])
                );
            }
        }
        return $id;
    }

    public function destroy(string $id): string
    {
        DB::delete('page_translations', 'page_id = :p', ['p' => (int) $id]);
        DB::delete('pages', 'id = :id', ['id' => (int) $id]);
        $this->redirectWith('/admin/pages', 'success', 'Sayfa silindi.');
    }
}
