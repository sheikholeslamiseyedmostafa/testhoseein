<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

/** مدیریت کاربران (فقط مدیر سیستم) */
final class UserController extends Controller
{
    private const USERNAME_PATTERN = '/^[A-Za-z0-9_.\-]{3,50}$/';

    public function index(): void
    {
        $this->render('users/index', [
            'title' => 'کاربران',
            'users' => User::all(),
        ]);
    }

    public function create(): void
    {
        $this->render('users/form', [
            'title'  => 'کاربر جدید',
            'user'   => ['id' => 0, 'username' => '', 'full_name' => '', 'role' => Auth::ROLE_VIEWER, 'is_active' => 1],
            'action' => 'users/store',
            'isNew'  => true,
        ]);
    }

    public function store(): void
    {
        $errors = [];
        $username = $this->postString('username', 50);
        $fullName = $this->postString('full_name', 150);
        $role = $this->postString('role', 20);
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

        if (!preg_match(self::USERNAME_PATTERN, $username)) {
            $errors[] = 'نام کاربری باید ۳ تا ۵۰ کاراکتر (حروف لاتین، عدد، نقطه، خط تیره یا زیرخط) باشد.';
        } elseif (User::usernameExists($username)) {
            $errors[] = 'این نام کاربری قبلاً ثبت شده است.';
        }
        if ($fullName === '') {
            $errors[] = 'نام و نام خانوادگی را وارد کنید.';
        }
        if (!array_key_exists($role, User::ROLES)) {
            $errors[] = 'نقش کاربری نامعتبر است.';
        }
        $errors = array_merge($errors, $this->passwordErrors($password));

        $data = ['id' => 0, 'username' => $username, 'full_name' => $fullName, 'role' => $role, 'is_active' => 1];
        if ($errors !== []) {
            http_response_code(422);
            $this->render('users/form', [
                'title' => 'کاربر جدید', 'user' => $data, 'action' => 'users/store', 'isNew' => true, 'errors' => $errors,
            ]);
            return;
        }

        User::create($username, $fullName, password_hash($password, PASSWORD_DEFAULT), $role);
        flash('success', 'کاربر جدید ایجاد شد.');
        redirect('users');
    }

    public function edit(): void
    {
        $user = User::find($this->queryId());
        if ($user === null) {
            $this->notFound();
        }

        $this->render('users/form', [
            'title'  => 'ویرایش کاربر',
            'user'   => $user,
            'action' => 'users/update',
            'isNew'  => false,
        ]);
    }

    public function update(): void
    {
        $id = $this->queryId();
        $existing = User::find($id);
        if ($existing === null) {
            $this->notFound();
        }

        $errors = [];
        $fullName = $this->postString('full_name', 150);
        $role = $this->postString('role', 20);
        $active = isset($_POST['is_active']);
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

        if ($fullName === '') {
            $errors[] = 'نام و نام خانوادگی را وارد کنید.';
        }
        if (!array_key_exists($role, User::ROLES)) {
            $errors[] = 'نقش کاربری نامعتبر است.';
        }
        if ($password !== '') {
            $errors = array_merge($errors, $this->passwordErrors($password));
        }

        $currentUser = Auth::user();
        if ($id === (int) $currentUser['id'] && ($role !== Auth::ROLE_ADMIN || !$active)) {
            $errors[] = 'نمی‌توانید نقش یا وضعیت حساب خودتان را تغییر دهید.';
        }
        $removesLastAdmin = $existing['role'] === Auth::ROLE_ADMIN
            && $existing['is_active']
            && ($role !== Auth::ROLE_ADMIN || !$active);
        if ($removesLastAdmin && User::activeAdminCount() <= 1) {
            $errors[] = 'حداقل یک مدیر فعال باید در سیستم باقی بماند.';
        }

        $data = array_merge($existing, [
            'full_name' => $fullName,
            'role' => $role,
            'is_active' => $active ? 1 : 0,
        ]);

        if ($errors !== []) {
            http_response_code(422);
            $this->render('users/form', [
                'title' => 'ویرایش کاربر', 'user' => $data, 'action' => 'users/update', 'isNew' => false, 'errors' => $errors,
            ]);
            return;
        }

        User::update($id, $fullName, $role, $active);
        if ($password !== '') {
            User::setPassword($id, password_hash($password, PASSWORD_DEFAULT));
        }
        flash('success', 'اطلاعات کاربر ذخیره شد.');
        redirect('users');
    }

    /** @return list<string> */
    private function passwordErrors(string $password): array
    {
        if (mb_strlen($password) < 8) {
            return ['رمز عبور باید حداقل ۸ کاراکتر باشد.'];
        }
        return [];
    }
}
