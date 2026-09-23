<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database as DB;
use App\Models\User;

class UserController extends AdminController
{
    public function index(): string
    {
        return $this->render('users/index', ['users' => User::all()]);
    }

    public function store(): string
    {
        $r = $this->request;
        $email = trim((string) $r->input('email', ''));
        $pass  = (string) $r->input('password', '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 8 || User::emailExists($email)) {
            $this->redirectWith('/admin/users', 'error', 'Geçersiz veri (şifre en az 8 karakter, e-posta benzersiz olmalı).');
        }
        DB::insert('users', [
            'name'          => trim((string) $r->input('name', $email)),
            'email'         => $email,
            'password_hash' => password_hash($pass, PASSWORD_DEFAULT),
            'role'          => in_array($r->input('role'), ['admin', 'editor'], true) ? $r->input('role') : 'editor',
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
        $this->redirectWith('/admin/users', 'success', 'Kullanıcı oluşturuldu.');
    }

    public function update(string $id): string
    {
        $r = $this->request;
        $user = User::find((int) $id);
        if (!$user) {
            $this->notFound();
        }
        $data = [
            'name'      => trim((string) $r->input('name', $user['name'])),
            'role'      => in_array($r->input('role'), ['admin', 'editor'], true) ? $r->input('role') : $user['role'],
            'is_active' => $r->input('is_active') ? 1 : 0,
        ];
        if (($pass = (string) $r->input('password', '')) !== '') {
            if (strlen($pass) < 8) {
                $this->redirectWith('/admin/users', 'error', 'Şifre çok kısa.');
            }
            $data['password_hash'] = password_hash($pass, PASSWORD_DEFAULT);
        }
        // never lock yourself out
        if ((int) $id === (int) Auth::user()['id']) {
            $data['is_active'] = 1;
            $data['role'] = 'admin';
        }
        DB::update('users', $data, 'id = :id', ['id' => (int) $id]);
        $this->redirectWith('/admin/users', 'success', 'Kullanıcı güncellendi.');
    }

    public function destroy(string $id): string
    {
        if ((int) $id === (int) Auth::user()['id']) {
            $this->redirectWith('/admin/users', 'error', 'Kendi hesabınızı silemezsiniz.');
        }
        DB::delete('users', 'id = :id', ['id' => (int) $id]);
        $this->redirectWith('/admin/users', 'success', 'Kullanıcı silindi.');
    }
}
