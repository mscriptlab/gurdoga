<?php
namespace App\Controllers\Admin;

use App\Core\Database as DB;
use App\Models\Redirect;

class RedirectController extends AdminController
{
    public function index(): string
    {
        return $this->render('redirects/index', ['redirects' => Redirect::all()]);
    }

    public function store(): string
    {
        $from = '/' . ltrim(trim((string) $this->request->input('from_path', '')), '/');
        $to   = trim((string) $this->request->input('to_path', ''));
        if (!preg_match('#^https?://#', $to)) {
            $to = '/' . ltrim($to, '/');
        }
        $code = (int) $this->request->input('code', 301);
        if ($from === '/' || $to === '' || $from === $to) {
            $this->redirectWith('/admin/redirects', 'error', 'Geçersiz yönlendirme.');
        }
        if (DB::value('SELECT 1 FROM redirects WHERE from_path = ?', [$from])) {
            DB::update('redirects', ['to_path' => $to, 'code' => $code], 'from_path = :f', ['f' => $from]);
        } else {
            DB::insert('redirects', ['from_path' => $from, 'to_path' => $to, 'code' => $code ?: 301, 'hits' => 0]);
        }
        $this->redirectWith('/admin/redirects', 'success', 'Yönlendirme kaydedildi.');
    }

    public function destroy(string $id): string
    {
        Redirect::delete((int) $id);
        $this->redirectWith('/admin/redirects', 'success', 'Yönlendirme silindi.');
    }
}
