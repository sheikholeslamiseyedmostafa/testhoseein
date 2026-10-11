<?php
declare(strict_types=1);

use App\Core\Auth;
use App\Core\Csrf;
use App\Support\Jalali;
use App\Support\Money;
use App\Support\Text;

/** مقدار تنظیمات با کلید نقطه‌ای، مثلاً config('db.host') */
function config(string $key, mixed $default = null): mixed
{
    $value = $GLOBALS['app_config'] ?? [];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

/** خروجی امن HTML */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** ساخت آدرس داخلی برنامه (روتر با پارامتر r) */
function url(string $route = 'dashboard', array $params = []): string
{
    $query = http_build_query(array_merge(['r' => $route], $params), '', '&', PHP_QUERY_RFC3986);
    return 'index.php?' . str_replace('%2F', '/', $query);
}

function asset(string $path): string
{
    return 'assets/' . ltrim($path, '/') . '?v=' . e(config('asset_version', '1'));
}

function redirect(string $route = 'dashboard', array $params = []): never
{
    header('Location: ' . url($route, $params), true, 303);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $items = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $items;
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
}

function can(string $access): bool
{
    return Auth::allows($access);
}

/** نمایش مبلغ (ارقام فارسی و جداکننده هزارگان) */
function money(int $cents): string
{
    return Money::format($cents);
}

/** نمایش تاریخ میلادی ذخیره‌شده به صورت شمسی */
function jdate(?string $ymd): string
{
    if ($ymd === null || $ymd === '') {
        return '';
    }
    return Text::fa(Jalali::format($ymd));
}

function fa(string $text): string
{
    return Text::fa($text);
}

/** نمایش یک پیام مرتبط با نوع کاربر */
function role_label(string $role): string
{
    return App\Models\User::ROLES[$role] ?? $role;
}

/**
 * رندر یک قالب داخل layout اصلی.
 * متغیرهای داده در قالب در دسترس‌اند؛ $content خروجی قالب است.
 */
function view(string $template, array $data = []): void
{
    $data['user'] ??= Auth::user();
    $data['errors'] ??= [];
    extract($data, EXTR_SKIP);

    ob_start();
    require APP_ROOT . '/app/views/' . $template . '.php';
    $content = ob_get_clean();

    require APP_ROOT . '/app/views/layout.php';
}
