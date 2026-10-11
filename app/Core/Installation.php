<?php
declare(strict_types=1);

namespace App\Core;

/**
 * وضعیت نصب برنامه. به‌جای define() (که در فرایندهای FastCGI و php-wasm ممکن است
 * مقدار قبلی را نگه دارد) هر بار فایل تنظیمات را بررسی می‌کنیم.
 */
final class Installation
{
    public static function isInstalled(): bool
    {
        return is_file(APP_ROOT . '/config/config.php');
    }
}
