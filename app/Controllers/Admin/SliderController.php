<?php
namespace App\Controllers\Admin;

use App\Core\Database as DB;
use App\Core\Image;
use App\Models\Slider;

class SliderController extends AdminController
{
    public function index(): string
    {
        return $this->render('sliders/index', ['sliders' => Slider::allForAdmin()]);
    }

    public function create(): string
    {
        return $this->render('sliders/form', [
            'slider'       => ['id' => 0, 'sort' => 0, 'is_active' => 1, 'image' => ''],
            'translations' => [],
        ]);
    }

    public function edit(string $id): string
    {
        $s = Slider::find((int) $id);
        if (!$s) {
            $this->notFound();
        }
        return $this->render('sliders/form', [
            'slider'       => $s,
            'translations' => Slider::translations((int) $id),
        ]);
    }

    public function store(): string
    {
        $id = $this->save(0);
        $this->redirectWith('/admin/sliders/' . $id . '/edit', 'success', 'Slayt oluşturuldu.');
    }

    public function update(string $id): string
    {
        $this->save((int) $id);
        $this->redirectWith('/admin/sliders', 'success', 'Slayt kaydedildi.');
    }

    private function save(int $id): int
    {
        $r = $this->request;

        $base = [
            'sort'       => (int) $r->input('sort', 0),
            'is_active'  => $r->input('is_active') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($file = ($r->files['image'] ?? null)) {
            if (($file['error'] ?? 4) === UPLOAD_ERR_OK) {
                try {
                    $base['image'] = Image::ingest($file, 'slider')['path'];
                } catch (\Throwable $e) {
                    flash('error', 'Görsel: ' . $e->getMessage());
                }
            }
        }

        if ($id === 0) {
            $base['image'] = $base['image'] ?? '';
            $base['created_at'] = date('Y-m-d H:i:s');
            $id = DB::insert('sliders', $base);
        } else {
            DB::update('sliders', $base, 'id = :id', ['id' => $id]);
        }

        $langInput = $this->langInput('tr');
        foreach ($this->languages as $lang) {
            $lid = (int) $lang['id'];
            $row = $langInput[$lid] ?? [];
            $payload = [
                'eyebrow'     => trim((string) ($row['eyebrow'] ?? '')),
                'title'       => trim((string) ($row['title'] ?? '')),
                'description' => (string) ($row['description'] ?? ''),
                'button_text' => trim((string) ($row['button_text'] ?? '')),
                'link_url'    => trim((string) ($row['link_url'] ?? '')),
            ];
            $ex = DB::one('SELECT id FROM slider_translations WHERE slider_id = ? AND language_id = ?', [$id, $lid]);
            if ($ex) {
                DB::update('slider_translations', $payload, 'id = :id', ['id' => $ex['id']]);
            } else {
                DB::insert('slider_translations', $payload + ['slider_id' => $id, 'language_id' => $lid]);
            }
        }
        return $id;
    }

    public function destroy(string $id): string
    {
        DB::delete('slider_translations', 'slider_id = :s', ['s' => (int) $id]);
        DB::delete('sliders', 'id = :id', ['id' => (int) $id]);
        $this->redirectWith('/admin/sliders', 'success', 'Slayt silindi.');
    }
}
