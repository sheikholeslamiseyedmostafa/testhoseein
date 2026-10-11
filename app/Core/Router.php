<?php
declare(strict_types=1);

namespace App\Core;

/**
 * مسیریاب ساده: index.php?r=نام-مسیر
 * تعریف مسیرها در app/routes.php است.
 */
final class Router
{
    public static function dispatch(): void
    {
        $routes = require APP_ROOT . '/app/routes.php';
        $name = is_string($_GET['r'] ?? null) ? $_GET['r'] : 'dashboard';

        // تا وقتی برنامه نصب نشده، فقط صفحه‌ی نصب در دسترس است
        if (!Installation::isInstalled() && !in_array($name, ['setup', 'setup/store'], true)) {
            redirect('setup');
        }

        if (!isset($routes[$name])) {
            self::abort(404);
        }

        [$class, $method, $verb, $access] = $routes[$name];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== $verb) {
            self::abort(405);
        }

        if ($access !== 'guest') {
            if (Auth::user() === null) {
                redirect('login');
            }
            if (!Auth::allows($access)) {
                self::abort(403);
            }
        }

        if ($verb === 'POST' && !Csrf::verify()) {
            if ($name === 'setup/store') {
                // صفحه‌ی نصب راهنمای مشخص‌تری نشان می‌دهد (مثلاً مشکل نشست یا کوکی)
                \App\Controllers\SetupController::csrfFailed();
            }
            self::abort(419);
        }

        $controller = new $class();
        $controller->$method();
    }

    public static function abort(int $code): never
    {
        http_response_code($code);
        $message = match ($code) {
            403 => 'دسترسی به این بخش برای نقش کاربری شما مجاز نیست.',
            404 => 'صفحه مورد نظر پیدا نشد.',
            405 => 'روش درخواست پشتیبانی نمی‌شود.',
            419 => 'نشست فرم منقضی شده است. صفحه را تازه‌سازی کرده و دوباره تلاش کنید.',
            default => 'خطا',
        };
        require APP_ROOT . '/app/views/error.php';
        exit;
    }
}
