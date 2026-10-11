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
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="نرم‌افزار حسابداری دوطرفه با گزارش دفتر کل و تراز آزمایشی">
    <meta name="theme-color" content="#1f4e79">
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
<body>
<a class="skip-link" href="#main">رفتن به محتوای اصلی</a>

<?php if ($user !== null): ?>
<header class="topbar no-print">
    <div class="topbar-inner">
        <a class="brand" href="<?= e(url('dashboard')) ?>"><?= e($appName) ?></a>
        <button type="button" class="nav-toggle" aria-expanded="false" aria-controls="main-nav" data-nav-toggle>
            منو
        </button>
        <nav id="main-nav" class="nav" aria-label="منوی اصلی">
            <a href="<?= e(url('dashboard')) ?>"<?= $navActive('dashboard') ?>>داشبورد</a>
            <a href="<?= e(url('accounts')) ?>"<?= $navActive('accounts') ?>>چارت حساب‌ها</a>
            <a href="<?= e(url('vouchers')) ?>"<?= $navActive('vouchers') ?>>اسناد</a>
            <a href="<?= e(url('reports/ledger')) ?>"<?= $navActive('reports/ledger') ?>>دفتر کل</a>
            <a href="<?= e(url('reports/trial')) ?>"<?= $navActive('reports/trial') ?>>تراز آزمایشی</a>
            <?php if (can('admin')): ?>
                <a href="<?= e(url('users')) ?>"<?= $navActive('users') ?>>کاربران</a>
            <?php endif; ?>
        </nav>
        <div class="account-box">
            <span class="user-chip" title="<?= e(role_label($user['role'])) ?>">
                <?= e($user['full_name']) ?>
                <small>(<?= e(role_label($user['role'])) ?>)</small>
            </span>
            <a href="<?= e(url('password')) ?>" class="btn btn-ghost btn-sm">تغییر رمز</a>
            <form method="post" action="<?= e(url('logout')) ?>" class="inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-ghost btn-sm">خروج</button>
            </form>
            <button type="button" class="btn btn-ghost btn-sm" data-install hidden>نصب برنامه</button>
        </div>
    </div>
</header>
<?php endif; ?>

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

<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
