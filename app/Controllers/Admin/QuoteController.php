<?php
namespace App\Controllers\Admin;

use App\Core\Database as DB;
use App\Models\QuoteRequest;

class QuoteController extends AdminController
{
    public function index(): string
    {
        $status = $this->request->input('status');
        return $this->render('quotes/index', [
            'quotes' => QuoteRequest::all($status ?: null),
            'status' => $status,
        ]);
    }

    public function show(string $id): string
    {
        $q = QuoteRequest::find((int) $id);
        if (!$q) {
            $this->notFound();
        }
        if ($q['status'] === 'new') {
            DB::update('quote_requests', ['status' => 'read'], 'id = :id', ['id' => (int) $id]);
            $q['status'] = 'read';
        }
        $product = $q['product_id'] ? DB::one('SELECT * FROM products WHERE id = ?', [$q['product_id']]) : null;
        return $this->render('quotes/show', ['q' => $q, 'product' => $product]);
    }

    public function setStatus(string $id): string
    {
        $status = $this->request->input('status', 'read');
        if (in_array($status, ['new', 'read', 'replied'], true)) {
            DB::update('quote_requests', ['status' => $status], 'id = :id', ['id' => (int) $id]);
        }
        $this->redirectWith('/admin/quotes/' . $id, 'success', 'Durum güncellendi.');
    }

    public function destroy(string $id): string
    {
        DB::delete('quote_requests', 'id = :id', ['id' => (int) $id]);
        $this->redirectWith('/admin/quotes', 'success', 'Talep silindi.');
    }

    public function export(): string
    {
        $rows = QuoteRequest::all();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="quote-requests-' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['id', 'type', 'date', 'status', 'name', 'company', 'email', 'phone', 'country', 'locale', 'product_id', 'message']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'], $r['type'], $r['created_at'], $r['status'], $r['name'], $r['company'],
                $r['email'], $r['phone'], $r['country'], $r['locale'], $r['product_id'], $r['message'],
            ]);
        }
        fclose($out);
        return '';
    }
}
