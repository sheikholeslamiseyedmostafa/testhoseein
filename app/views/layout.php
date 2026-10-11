<?php
/** @var string $content @var array|null $user @var string|null $title */
$appName = (string) config('app_name', 'حسابداری');
$pageTitle = isset($title) && $title !== '' ? $title . ' · ' . $appName : $appName;
$currentRoute = is_string($_GET['r'] ?? null) ? $_GET['r'] : 'dashboard';
$navActive = static function (string ...$prefixes) use ($currentRoute): string {
    foreach ($prefixes as $prefix) {
        if ($currentRoute === $prefix || str_starts_with($currentRoute, $prefix . '/')) {
            return ' class="active" aria-current="page"';
        }
    }
    return '';
};
// منوی کناری: مسیر، برچسب، آیکون، و سطح دسترسی لازم (خالی = همه‌ی کاربران واردشده)
$navItems = [
    ['dashboard', 'داشبورد', 'dashboard', '', 'dashboard'],
    ['accounts', 'چارت حساب‌ها', 'accounts', 'view', 'accounts'],
    ['vouchers', 'اسناد', 'vouchers', 'view', 'vouchers'],
    ['reports/ledger', 'دفتر کل', 'ledger', 'view', 'reports/ledger'],
    ['reports/trial', 'تراز آزمایشی', 'trial', 'view', 'reports/trial'],
    ['users', 'کاربران', 'users', 'admin', 'users'],
];
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="نرم‌افزار حسابداری دوطرفه با گزارش دفتر کل و تراز آزمایشی">
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="<?= e($appName) ?>">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="icon" href="assets/icons/icon-192.png" type="image/png" sizes="192x192">
    <link rel="apple-touch-icon" href="assets/icons/apple-touch-icon.png">
    <link rel="preload" href="assets/fonts/Vazirmatn-Variable.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body<?= $user !== null ? ' class="has-shell"' : '' ?>>
<a class="skip-link" href="#main">رفتن به محتوای اصلی</a>

<?php if ($user !== null): ?>
<div class="app-shell">
    <aside id="main-nav" class="sidebar no-print" aria-label="منوی اصلی">
        <a class="brand" href="<?= e(url('dashboard')) ?>">
            <span class="brand-mark"><?= icon('logo') ?></span>
            <span class="brand-text">
                <strong><?= e($appName) ?></strong>
                <small>مدیریت مالی و حسابداری</small>
            </span>
        </a>

        <nav class="nav" aria-label="بخش‌های برنامه">
            <?php foreach ($navItems as [$route, $label, $iconName, $access, $prefix]): ?>
                <?php if ($access === '' || can($access)): ?>
                    <a href="<?= e(url($route)) ?>" class="nav-link"<?= $navActive($prefix) ?>>
                        <?= icon($iconName) ?>
                        <span><?= e($label) ?></span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-foot">
            <span class="sidebar-foot-title">ورود با نقش</span>
            <span class="sidebar-foot-role"><?= e(role_label($user['role'])) ?></span>
        </div>
    </aside>

    <div class="app-main">
        <header class="app-header no-print">
            <div class="header-start">
                <button type="button" class="nav-toggle" aria-expanded="false" aria-controls="main-nav" data-nav-toggle>
                    <?= icon('menu') ?>
                    <span class="sr-only">منو</span>
                </button>
                <div class="user-box">
                    <span class="avatar" aria-hidden="true"><?= e(mb_substr((string) $user['full_name'], 0, 1)) ?></span>
                    <span class="user-meta">
                        <strong><?= e($user['full_name']) ?></strong>
                        <small><?= e(role_label($user['role'])) ?></small>
                    </span>
                </div>
            </div>
            <div class="account-box">
                <span class="header-date"><?= icon('calendar') ?><?= e(jdate(date('Y-m-d'))) ?></span>
                <a href="<?= e(url('password')) ?>" class="btn btn-ghost btn-sm"><?= icon('key') ?><span>تغییر رمز</span></a>
                <form method="post" action="<?= e(url('logout')) ?>" class="inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-ghost btn-sm"><?= icon('logout') ?><span>خروج</span></button>
                </form>
                <button type="button" class="btn btn-primary btn-sm" data-install hidden>نصب برنامه</button>
            </div>
        </header>

        <main id="main" class="container">
            <?php foreach (take_flashes() as $flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> no-print" role="status"><?= e($flash['message']) ?></div>
            <?php endforeach; ?>

            <?php if ($errors !== []): ?>
                <div class="alert alert-error no-print" role="alert">
                    <strong>لطفاً موارد زیر را اصلاح کنید:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </main>

        <footer class="footer no-print">
            <span><?= e($appName) ?></span>
            <span class="muted">قابل نصب روی دسکتاپ و موبایل (PWA)</span>
        </footer>
    </div>
</div>
<?php else: ?>
<main id="main" class="container">
    <?php foreach (take_flashes() as $flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> no-print" role="status"><?= e($flash['message']) ?></div>
    <?php endforeach; ?>

    <?php if ($errors !== []): ?>
        <div class="alert alert-error no-print" role="alert">
            <strong>لطفاً موارد زیر را اصلاح کنید:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?= $content ?>
</main>

<footer class="footer no-print">
    <span><?= e($appName) ?></span>
    <span class="muted">قابل نصب روی دسکتاپ و موبایل (PWA)</span>
</footer>
<?php endif; ?>

<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
