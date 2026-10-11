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
    $relative = 'assets/' . ltrim($path, '/');
    // نسخه‌ی فایل از زمان تغییر آن گرفته می‌شود تا بعد از به‌روزرسانی، مرورگر فایل تازه را بگیرد
    $file = APP_ROOT . '/public/' . $relative;
    $version = is_file($file) ? (string) filemtime($file) : (string) config('asset_version', '1');
    return $relative . '?v=' . e($version);
}

/** آیکون SVG خطی (بدون وابستگی خارجی و بدون style درون‌خطی) */
function icon(string $name, string $class = 'icon'): string
{
    static $paths = [
        'logo'      => '<path d="M4 8l8-4 8 4v8l-8 4-8-4z"/><path d="M4 8l8 4 8-4M12 12v8"/>',
        'dashboard' => '<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>',
        'accounts'  => '<path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/>',
        'vouchers'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h8M8 9h2"/>',
        'ledger'    => '<path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z"/><path d="M8 7h8M8 11h8"/>',
        'trial'     => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18M9 4v16"/>',
        'users'     => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'calendar'  => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'key'       => '<circle cx="7.5" cy="15.5" r="5.5"/><path d="M21 2l-9.6 9.6M15.5 7.5l3 3L22 7l-3-3"/>',
        'logout'    => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/>',
        'menu'      => '<path d="M3 6h18M3 12h18M3 18h18"/>',
        'plus'      => '<path d="M12 5v14M5 12h14"/>',
        'bank'      => '<path d="M3 10l9-6 9 6M5 10v9M9 10v9M15 10v9M19 10v9M3 21h18"/>',
        'up'        => '<path d="M22 7l-8.5 8.5-5-5L2 17"/><path d="M16 7h6v6"/>',
        'down'      => '<path d="M22 17l-8.5-8.5-5 5L2 7"/><path d="M16 17h6v-6"/>',
        'chart'     => '<path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/>',
        'clock'     => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
    ];
    $body = $paths[$name] ?? '';
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" '
        . 'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $body . '</svg>';
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
