<?php
/**
 * نصب‌کننده خط فرمان: ساخت جداول و کاربر مدیر اولیه.
 *
 *   php db/install.php                       (رمز مدیر به صورت تصادفی ساخته و نمایش داده می‌شود)
 *   php db/install.php --admin-password=...  (رمز دلخواه؛ حداقل ۸ کاراکتر)
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("این اسکریپت فقط از خط فرمان اجرا می‌شود.\n");
}

define('APP_ROOT', dirname(__DIR__));

$configPath = APP_ROOT . '/config/config.php';
if (!is_file($configPath)) {
    fwrite(STDERR, "فایل config/config.php پیدا نشد. ابتدا config/config.sample.php را کپی و تنظیم کنید.\n");
    exit(1);
}

require APP_ROOT . '/app/bootstrap.php';

use App\Core\Db;
use App\Core\Installer;

$options = getopt('', ['admin-password::']);
$adminPassword = isset($options['admin-password']) && is_string($options['admin-password'])
    ? $options['admin-password']
    : null;

try {
    $schema = file_get_contents(APP_ROOT . '/db/schema.sql');
    if ($schema === false) {
        throw new RuntimeException('فایل db/schema.sql خوانده نشد.');
    }

    $result = Installer::run(Db::pdo(), $schema, $adminPassword);

    echo "جداول بررسی/ساخته شدند ({$result['statements']} دستور اجرا شد).\n";
    if ($result['seeded'] > 0) {
        echo "چارت حساب پیش‌فرض ایجاد شد ({$result['seeded']} حساب).\n";
    }
    if ($result['admin_created']) {
        echo "کاربر مدیر ایجاد شد: admin\n";
        if ($result['admin_password'] !== null) {
            echo "رمز عبور مدیر (فقط همین یک بار نمایش داده می‌شود): {$result['admin_password']}\n";
        }
        echo "بلافاصله پس از اولین ورود، رمز عبور را تغییر دهید.\n";
    } else {
        echo "کاربر در پایگاه داده وجود دارد؛ کاربر جدیدی ساخته نشد.\n";
    }
    echo "نصب با موفقیت انجام شد.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'خطا در نصب: ' . $e->getMessage() . "\n");
    exit(1);
}
