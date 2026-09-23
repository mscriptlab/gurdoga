<?php
namespace App\Controllers\Admin;

use App\Core\Database as DB;
use App\Models\Category;
use App\Models\Page;

class MenuController extends AdminController
{
    private array $locations = ['header', 'footer'];

    public function index(): string
    {
        $items = [];
        foreach ($this->locations as $loc) {
            $items[$loc] = DB::all(
                'SELECT m.*, GROUP_CONCAT(CONCAT(mt.language_id, ":", mt.label) SEPARATOR "|") AS labels
                 FROM menu_items m
                 LEFT JOIN menu_item_translations mt ON mt.menu_item_id = m.id
                 WHERE m.location = ? GROUP BY m.id ORDER BY m.sort, m.id',
                [$loc]
            );
        }
        return $this->render('menus/index', [
            'items'      => $items,
            'locations'  => $this->locations,
            'categories' => Category::allForAdmin(),
            'pages'      => Page::all(),
        ]);
    }

    public function save(): string
    {
        $rows = (array) ($this->request->post['items'] ?? []);
        foreach ($this->locations as $loc) {
            DB::query(
                'DELETE mt FROM menu_item_translations mt JOIN menu_items m ON m.id = mt.menu_item_id WHERE m.location = ?',
                [$loc]
            );
            DB::delete('menu_items', 'location = :l', ['l' => $loc]);
        }
        foreach ($rows as $loc => $list) {
            if (!in_array($loc, $this->locations, true)) {
                continue;
            }
            $sort = 0;
            foreach ((array) $list as $row) {
                $type = $row['type'] ?? 'url';
                if (($row['_delete'] ?? '') === '1') {
                    continue;
                }
                if ($type === 'url' && trim((string) ($row['url'] ?? '')) === '' && empty($row['label'])) {
                    continue;
                }
                $id = DB::insert('menu_items', [
                    'location' => $loc,
                    'type'     => $type,
                    'ref_id'   => (int) ($row['ref_id'] ?? 0) ?: null,
                    'url'      => trim((string) ($row['url'] ?? '')),
                    'sort'     => $sort++,
                ]);
                foreach ((array) ($row['label'] ?? []) as $lid => $label) {
                    if (trim((string) $label) === '') {
                        continue;
                    }
                    DB::insert('menu_item_translations', [
                        'menu_item_id' => $id, 'language_id' => (int) $lid, 'label' => trim((string) $label),
                    ]);
                }
            }
        }
        $this->redirectWith('/admin/menus', 'success', 'Menüler kaydedildi.');
    }
}
