<?php
namespace App\Controllers\Admin;

use App\Core\Database as DB;
use App\Models\Language;

class LanguageController extends AdminController
{
    public function index(): string
    {
        return $this->render('languages/index', ['langs' => Language::all()]);
    }

    public function store(): string
    {
        $r = $this->request;
        $code = strtolower(preg_replace('/[^a-z-]/i', '', (string) $r->input('code')));
        if ($code === '' || DB::value('SELECT 1 FROM languages WHERE code = ?', [$code])) {
            $this->redirectWith('/admin/languages', 'error', 'Geçersiz veya zaten var olan dil kodu.');
        }
        DB::insert('languages', [
            'code'       => $code,
            'name'       => trim((string) $r->input('name', $code)),
            'locale'     => trim((string) $r->input('locale', $code)),
            'is_default' => 0,
            'is_active'  => $r->input('is_active') ? 1 : 0,
            'sort'       => (int) $r->input('sort', 99),
        ]);
        $this->redirectWith('/admin/languages', 'success', 'Dil eklendi. Mevcut içeriklerde çevirileri doldurun.');
    }

    public function update(string $id): string
    {
        $r = $this->request;
        $lang = Language::find((int) $id);
        if (!$lang) {
            $this->notFound();
        }
        $makeDefault = (bool) $r->input('is_default');
        DB::update('languages', [
            'name'      => trim((string) $r->input('name', $lang['name'])),
            'locale'    => trim((string) $r->input('locale', $lang['locale'])),
            'is_active' => $r->input('is_active') ? 1 : ($makeDefault ? 1 : 0),
            'sort'      => (int) $r->input('sort', $lang['sort']),
        ], 'id = :id', ['id' => (int) $id]);

        if ($makeDefault) {
            DB::query('UPDATE languages SET is_default = 0');
            DB::update('languages', ['is_default' => 1, 'is_active' => 1], 'id = :id', ['id' => (int) $id]);
        }
        $this->redirectWith('/admin/languages', 'success', 'Dil güncellendi.');
    }

    public function destroy(string $id): string
    {
        $lang = Language::find((int) $id);
        if (!$lang || $lang['is_default']) {
            $this->redirectWith('/admin/languages', 'error', 'Varsayılan dil silinemez.');
        }
        foreach (['category_translations', 'product_translations', 'page_translations',
                  'menu_item_translations', 'setting_translations', 'translations', 'product_attributes'] as $tbl) {
            DB::delete($tbl, 'language_id = :l', ['l' => (int) $id]);
        }
        DB::delete('languages', 'id = :id', ['id' => (int) $id]);
        $this->redirectWith('/admin/languages', 'success', 'Dil kaldırıldı.');
    }
}
