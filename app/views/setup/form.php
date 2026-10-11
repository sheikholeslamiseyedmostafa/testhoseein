<?php
/** @var array $values @var string|null $manualConfig */
$v = static fn(string $key): string => (string) ($values[$key] ?? '');
?>
<div class="page-head">
    <h1>نصب برنامه و پایگاه داده</h1>
</div>

<?php if (!empty($manualConfig)): ?>
<section class="card narrow-form">
    <h2>تنظیمات نصب</h2>
    <p class="muted">فایل <code>config/config.php</code> را با این محتوا بسازید و سپس به <a href="<?= e(url('login')) ?>">صفحه‌ی ورود</a> بروید.</p>
    <textarea readonly rows="20" dir="ltr" class="code-block"><?= e($manualConfig) ?></textarea>
</section>
<?php else: ?>

<section class="card narrow-form">
    <h2>مرحله ۱: آماده‌سازی پایگاه داده</h2>
    <p class="muted">قبل از نصب، پایگاه داده‌ی خالی و کاربر آن را در MySQL بسازید. نمونه‌ی دستور (نام‌ها و رمز را تغییر دهید):</p>
    <pre dir="ltr" class="code-block">CREATE DATABASE accounting CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'accounting'@'localhost' IDENTIFIED BY 'رمز-قوی-خود';
GRANT ALL PRIVILEGES ON accounting.* TO 'accounting'@'localhost';</pre>
    <p class="muted">این صفحه فقط روی پایگاه داده‌ی خالی یا نصب‌نشده اجرا می‌شود.</p>
</section>

<form method="post" action="<?= e(url('setup/store')) ?>" class="card form narrow-form" autocomplete="off">
    <?= csrf_field() ?>

    <h2>مشخصات برنامه و پایگاه داده</h2>
    <label>
        <span>نام برنامه</span>
        <input type="text" name="app_name" value="<?= e($v('app_name')) ?>" required maxlength="100">
    </label>
    <label>
        <span>نشانی سرور پایگاه داده</span>
        <input type="text" name="db_host" value="<?= e($v('db_host')) ?>" required dir="ltr" maxlength="255">
        <small class="muted">معمولاً <code>localhost</code> یا <code>127.0.0.1</code>.</small>
    </label>
    <label>
        <span>پورت</span>
        <input type="number" name="db_port" value="<?= e($v('db_port')) ?>" required dir="ltr" min="1" max="65535">
    </label>
    <label>
        <span>نام پایگاه داده</span>
        <input type="text" name="db_name" value="<?= e($v('db_name')) ?>" required dir="ltr" maxlength="64" pattern="[A-Za-z0-9_]{1,64}">
    </label>
    <label>
        <span>نام کاربر پایگاه داده</span>
        <input type="text" name="db_user" value="<?= e($v('db_user')) ?>" required dir="ltr" maxlength="64" autocomplete="off">
    </label>
    <label>
        <span>رمز کاربر پایگاه داده</span>
        <input type="password" name="db_password" dir="ltr" maxlength="200" autocomplete="off">
    </label>

    <h2>حساب مدیر</h2>
    <label>
        <span>نام کاربری مدیر</span>
        <input type="text" name="admin_username" value="<?= e($v('admin_username')) ?>" required dir="ltr" maxlength="50" pattern="[A-Za-z0-9_.\-]{3,50}" autocomplete="off">
    </label>
    <label>
        <span>نام و نام خانوادگی مدیر</span>
        <input type="text" name="admin_name" value="<?= e($v('admin_name')) ?>" required maxlength="150">
    </label>
    <label>
        <span>رمز عبور مدیر</span>
        <input type="password" name="admin_password" required dir="ltr" minlength="8" maxlength="200" autocomplete="new-password">
        <small class="muted">حداقل ۸ کاراکتر.</small>
    </label>
    <label>
        <span>تکرار رمز عبور مدیر</span>
        <input type="password" name="admin_password_confirm" required dir="ltr" minlength="8" maxlength="200" autocomplete="new-password">
    </label>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">نصب برنامه</button>
    </div>
</form>
<?php endif; ?>
