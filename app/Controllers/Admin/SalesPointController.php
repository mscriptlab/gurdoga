<?php
namespace App\Controllers\Admin;

use App\Core\Database as DB;
use App\Models\SalesPoint;

class SalesPointController extends AdminController
{
    public function index(): string
    {
        return $this->render('sales-points/index', [
            'points' => SalesPoint::adminList(),
        ]);
    }

    public function create(): string
    {
        return $this->render('sales-points/form', [
            'point' => [
                'id' => 0, 'name' => '', 'channel' => '', 'city' => '', 'district' => '',
                'address' => '', 'phone' => '', 'lat' => '', 'lng' => '', 'sort' => 0, 'is_active' => 1,
            ],
        ]);
    }

    public function edit(string $id): string
    {
        $point = SalesPoint::find((int) $id);
        if (!$point) {
            $this->notFound();
        }
        return $this->render('sales-points/form', ['point' => $point]);
    }

    public function store(): string
    {
        $this->save(0);
        $this->redirectWith('/admin/sales-points', 'success', 'Satış noktası oluşturuldu.');
    }

    public function update(string $id): string
    {
        $this->save((int) $id);
        $this->redirectWith('/admin/sales-points', 'success', 'Satış noktası kaydedildi.');
    }

    private function save(int $id): void
    {
        $r = $this->request;
        $lat = trim((string) $r->input('lat', ''));
        $lng = trim((string) $r->input('lng', ''));
        $base = [
            'name'      => trim((string) $r->input('name', '')),
            'channel'   => trim((string) $r->input('channel', '')),
            'city'      => trim((string) $r->input('city', '')),
            'district'  => trim((string) $r->input('district', '')),
            'address'   => trim((string) $r->input('address', '')),
            'phone'     => trim((string) $r->input('phone', '')),
            'lat'       => $lat === '' ? null : (float) $lat,
            'lng'       => $lng === '' ? null : (float) $lng,
            'sort'      => (int) $r->input('sort', 0),
            'is_active' => $r->input('is_active') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($id === 0) {
            $base['created_at'] = date('Y-m-d H:i:s');
            DB::insert('sales_points', $base);
        } else {
            DB::update('sales_points', $base, 'id = :id', ['id' => $id]);
        }
    }

    public function destroy(string $id): string
    {
        DB::delete('sales_points', 'id = :id', ['id' => (int) $id]);
        $this->redirectWith('/admin/sales-points', 'success', 'Satış noktası silindi.');
    }
}
