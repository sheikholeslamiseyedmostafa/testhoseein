<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

/** تغییر رمز عبور توسط خود کاربر */
final class ProfileController extends Controller
{
    public function form(): void
    {
        $this->render('profile/password', ['title' => 'تغییر رمز عبور']);
    }

    public function update(): void
    {
        $user = Auth::user();
        $current = is_string($_POST['current_password'] ?? null) ? $_POST['current_password'] : '';
        $new = is_string($_POST['new_password'] ?? null) ? $_POST['new_password'] : '';
        $confirm = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';

        $errors = [];
        $stored = User::findByUsername($user['username']);
        if ($stored === null || !password_verify($current, $stored['password_hash'])) {
            $errors[] = 'رمز عبور فعلی اشتباه است.';
        }
        if (mb_strlen($new) < 8) {
            $errors[] = 'رمز عبور جدید باید حداقل ۸ کاراکتر باشد.';
        }
        if ($new !== $confirm) {
            $errors[] = 'تکرار رمز عبور جدید با آن یکسان نیست.';
        }
        if ($errors !== []) {
            http_response_code(422);
            $this->render('profile/password', ['title' => 'تغییر رمز عبور', 'errors' => $errors]);
            return;
        }

        User::setPassword($user['id'], password_hash($new, PASSWORD_DEFAULT));
        flash('success', 'رمز عبور با موفقیت تغییر کرد.');
        redirect('dashboard');
    }
}
