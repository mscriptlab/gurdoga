<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\View;

class AuthController extends Controller
{
    public function showLogin(): string
    {
        if (Auth::check()) {
            redirect(url('/admin'));
        }
        return View::render('admin/login', ['error' => flash('error')], null);
    }

    public function login(): string
    {
        $email = trim((string) $this->request->input('email', ''));
        $pass  = (string) $this->request->input('password', '');
        usleep(300000); // slow down brute force
        if (Auth::attempt($email, $pass)) {
            redirect(url('/admin'));
        }
        flash('error', 'E-posta veya şifre hatalı.');
        redirect(url('/admin/login'));
    }

    public function logout(): string
    {
        Auth::logout();
        redirect(url('/admin/login'));
    }
}
