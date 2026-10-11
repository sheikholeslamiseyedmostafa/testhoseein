<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

final class AuthController extends Controller
{
    public function login(): void
    {
        if (Auth::user() !== null) {
            redirect('dashboard');
        }
        $this->render('auth/login', ['title' => 'ورود', 'username' => '']);
    }

    public function authenticate(): void
    {
        $username = $this->postString('username', 50);
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

        if ($username === '' || $password === '') {
            http_response_code(422);
            $this->render('auth/login', [
                'title' => 'ورود',
                'username' => $username,
                'errors' => ['نام کاربری و رمز عبور را وارد کنید.'],
            ]);
            return;
        }

        if (!Auth::attempt($username, $password)) {
            http_response_code(422);
            $this->render('auth/login', [
                'title' => 'ورود',
                'username' => $username,
                'errors' => ['نام کاربری یا رمز عبور اشتباه است، یا حساب کاربری غیرفعال است.'],
            ]);
            return;
        }

        flash('success', 'خوش آمدید.');
        redirect('dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        redirect('login');
    }
}
