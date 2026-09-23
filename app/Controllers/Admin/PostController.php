<?php
namespace App\Controllers\Admin;

use App\Core\Database as DB;
use App\Core\Image;
use App\Models\Post;
use App\Models\Redirect;

class PostController extends AdminController
{
    public function index(): string
    {
        return $this->render('posts/index', ['posts' => Post::adminList()]);
    }

    public function create(): string
    {
        return $this->render('posts/form', [
            'post'         => ['id' => 0, 'cover_image' => '', 'author' => '', 'is_active' => 1, 'published_at' => date('Y-m-d')],
            'translations' => [],
        ]);
    }

    public function edit(string $id): string
    {
        $post = Post::find((int) $id);
        if (!$post) {
            $this->notFound();
        }
        return $this->render('posts/form', [
            'post'         => $post,
            'translations' => Post::translations((int) $id),
        ]);
    }

    public function store(): string
    {
        $id = $this->save(0);
        $this->redirectWith('/admin/posts/' . $id . '/edit', 'success', 'Yazı oluşturuldu.');
    }

    public function update(string $id): string
    {
        $this->save((int) $id);
        $this->redirectWith('/admin/posts', 'success', 'Yazı kaydedildi.');
    }

    private function save(int $id): int
    {
        $r = $this->request;
        $pub = trim((string) $r->input('published_at', ''));
        $base = [
            'author'       => trim((string) $r->input('author', '')),
            'is_active'    => $r->input('is_active') ? 1 : 0,
            'published_at' => $pub !== '' ? date('Y-m-d H:i:s', strtotime($pub)) : date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ];
        if ($f = ($r->files['cover'] ?? null)) {
            if (($f['error'] ?? 4) === UPLOAD_ERR_OK) {
                try { $base['cover_image'] = Image::ingest($f, 'post')['path']; }
                catch (\Throwable $e) { flash('error', 'Kapak görseli: ' . $e->getMessage()); }
            }
        }

        if ($id === 0) {
            $base['created_at'] = date('Y-m-d H:i:s');
            $id = DB::insert('posts', $base);
        } else {
            DB::update('posts', $base, 'id = :id', ['id' => $id]);
        }

        $tr = $this->langInput('tr');
        foreach ($this->languages as $lang) {
            $lid = (int) $lang['id'];
            $row = $tr[$lid] ?? [];
            $title = trim((string) ($row['title'] ?? ''));
            $slug = slugify(($row['slug'] ?? '') ?: $title);
            if ($title === '' && $slug === 'n-a') {
                continue;
            }
            $try = $slug; $n = 2;
            while (Post::slugExists($try, $lid, $id)) { $try = $slug . '-' . $n++; }
            $slug = $try;

            $payload = [
                'title'            => $title,
                'slug'             => $slug,
                'excerpt'          => trim((string) ($row['excerpt'] ?? '')),
                'body'             => (string) ($row['body'] ?? ''),
                'meta_title'       => trim((string) ($row['meta_title'] ?? '')),
                'meta_description' => trim((string) ($row['meta_description'] ?? '')),
            ];
            $ex = DB::one('SELECT * FROM post_translations WHERE post_id = ? AND language_id = ?', [$id, $lid]);
            if ($ex) {
                if ($ex['slug'] && $ex['slug'] !== $slug) {
                    Redirect::record('/' . $lang['code'] . '/blog/' . $ex['slug'], '/' . $lang['code'] . '/blog/' . $slug);
                }
                DB::update('post_translations', $payload, 'id = :id', ['id' => $ex['id']]);
            } else {
                DB::insert('post_translations', $payload + ['post_id' => $id, 'language_id' => $lid]);
            }
        }
        return $id;
    }

    public function destroy(string $id): string
    {
        DB::delete('post_translations', 'post_id = :p', ['p' => (int) $id]);
        DB::delete('posts', 'id = :id', ['id' => (int) $id]);
        $this->redirectWith('/admin/posts', 'success', 'Yazı silindi.');
    }
}
