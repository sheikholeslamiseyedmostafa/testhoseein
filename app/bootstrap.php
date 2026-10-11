<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

$configPath = APP_ROOT . '/config/config.php';
if (!is_file($configPath)) {
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><html lang="fa" dir="rtl"><meta charset="utf-8"><title>خطای پیکربندی</title>'
        . '<p style="font-family:Tahoma,sans-serif">فایل <code>config/config.php</code> پیدا نشد. '
        . 'فایل <code>config/config.sample.php</code> را کپی و به نام <code>config.php</code> تغییر دهید.</p>';
    exit;
}

$GLOBALS['app_config'] = require $configPath;

require APP_ROOT . '/app/helpers.php';

date_default_timezone_set((string) config('timezone', 'Asia/Tehran'));
mb_internal_encoding('UTF-8');

// Autoloader ساده به سبک PSR-4: App\Core\Db  =>  app/Core/Db.php
spl_autoload_register(static function (string $class): void {
    if (strncmp($class, 'App\\', 4) !== 0) {
        return;
    }
    $file = APP_ROOT . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

// گزارش خطا در فایل لاگ (نه در خروجی برای کاربر)
$logDir = APP_ROOT . '/storage/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0775, true);
}
ini_set('log_errors', '1');
ini_set('error_log', $logDir . '/app.log');
ini_set('display_errors', config('debug') ? '1' : '0');
error_reporting(E_ALL);

set_exception_handler(static function (Throwable $e): void {
    error_log(sprintf('[%s] %s: %s in %s:%d', date('c'), get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()));
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }
    $code = 500;
    $message = 'خطای داخلی سرور رخ داده است. لطفاً دوباره تلاش کنید.';
    if (config('debug')) {
        $message = $e->getMessage();
    }
    require APP_ROOT . '/app/views/error.php';
});

if (PHP_SAPI !== 'cli') {
    $isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    $cookiePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');

    session_name('ACCTSESSID');
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => $cookiePath === '' ? '/' : $cookiePath . '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: same-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; "
        . "script-src 'self'; font-src 'self'; manifest-src 'self'; worker-src 'self'; connect-src 'self'; "
        . "frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
}
