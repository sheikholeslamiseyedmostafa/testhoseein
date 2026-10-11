<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/**
 * احراز هویت و کنترل دسترسی بر اساس نقش.
 *  - viewer     : فقط مشاهده
 *  - accountant : مشاهده + ثبت و ویرایش حساب و سند
 *  - admin      : همه امکانات + مدیریت کاربران
 */
final class Auth
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_ACCOUNTANT = 'accountant';
    public const ROLE_VIEWER = 'viewer';

    private static ?array $user = null;
    private static bool $loaded = false;
    private static ?string $dummyHash = null;

    public static function user(): ?array
    {
        if (self::$loaded) {
            return self::$user;
        }
        self::$loaded = true;

        $id = $_SESSION['uid'] ?? null;
        if ($id === null) {
            return null;
        }

        $user = User::find((int) $id);
        if ($user === null || !$user['is_active']) {
            self::forget();
            return null;
        }

        $idleLimit = (int) config('session_lifetime', 30) * 60;
        $lastSeen = (int) ($_SESSION['last_seen'] ?? time());
        if (time() - $lastSeen > $idleLimit) {
            self::forget();
            return null;
        }
        $_SESSION['last_seen'] = time();

        self::$user = $user;
        return self::$user;
    }

    public static function attempt(string $username, string $password): bool
    {
        $user = User::findByUsername($username);

        // برای جلوگیری از حدس زدن نام کاربری از روی زمان پاسخ، همیشه یک password_verify انجام می‌شود
        $hash = $user['password_hash'] ?? self::dummyHash();
        $valid = password_verify($password, $hash);

        if ($user === null || !$valid || !$user['is_active']) {
            return false;
        }

        session_regenerate_id(true);
        unset($_SESSION['_csrf']);
        $_SESSION['uid'] = (int) $user['id'];
        $_SESSION['last_seen'] = time();

        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            User::setPassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        }

        self::$loaded = false;
        self::$user = null;
        return true;
    }

    public static function logout(): void
    {
        self::forget();
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function allows(string $access): bool
    {
        $user = self::user();
        if ($user === null) {
            return false;
        }

        return match ($access) {
            'view'  => true,
            'write' => in_array($user['role'], [self::ROLE_ADMIN, self::ROLE_ACCOUNTANT], true),
            'admin' => $user['role'] === self::ROLE_ADMIN,
            default => false,
        };
    }

    private static function forget(): void
    {
        unset($_SESSION['uid'], $_SESSION['last_seen']);
        self::$user = null;
        self::$loaded = true;
    }

    private static function dummyHash(): string
    {
        return self::$dummyHash ??= password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    }
}
