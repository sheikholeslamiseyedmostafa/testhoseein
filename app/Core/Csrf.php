<?php
declare(strict_types=1);

namespace App\Core;

/** محافظت از فرم‌ها در برابر CSRF */
final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function verify(): bool
    {
        $sent = $_POST['_token'] ?? '';
        $known = $_SESSION['_csrf'] ?? '';
        return is_string($sent) && is_string($known) && $known !== '' && hash_equals($known, $sent);
    }
}
