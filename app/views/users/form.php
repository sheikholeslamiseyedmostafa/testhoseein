<?php
/** @var array $user @var string $action @var bool $isNew @var string $title */
use App\Models\User;

$postUrl = $isNew ? url($action) : url($action, ['id' => $user['id']]);
?>
<div class="page-head">
    <h1><?= e($title) ?></h1>
    <a href="<?= e(url('users')) ?>" class="btn btn-ghost">بازگشت به فهرست</a>
</div>

<form method="post" action="<?= e($postUrl) ?>" class="card form narrow-form" autocomplete="off">
    <?= csrf_field() ?>
    <label>
        <span>نام کاربری</span>
        <input type="text" name="username" value="<?= e($user['username']) ?>" required
               <?= $isNew ? '' : 'readonly' ?> dir="ltr" maxlength="50" pattern="[A-Za-z0-9_.\-]{3,50}"
               autocomplete="off">
        <?php if (!$isNew): ?><small class="muted">نام کاربری قابل تغییر نیست.</small><?php endif; ?>
    </label>
    <label>
        <span>نام و نام خانوادگی</span>
        <input type="text" name="full_name" value="<?= e($user['full_name']) ?>" required maxlength="150">
    </label>
    <label>
        <span>نقش</span>
        <select name="role" required>
            <?php foreach (User::ROLES as $key => $label): ?>
                <option value="<?= e($key) ?>"<?= $user['role'] === $key ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <?php if (!$isNew): ?>
    <label class="checkbox">
        <input type="checkbox" name="is_active" value="1"<?= (int) $user['is_active'] === 1 ? ' checked' : '' ?>>
        <span>حساب فعال باشد</span>
    </label>
    <?php endif; ?>
    <label>
        <span><?= $isNew ? 'رمز عبور' : 'رمز عبور جدید (اختیاری)' ?></span>
        <input type="password" name="password" dir="ltr" maxlength="200" autocomplete="new-password"
               <?= $isNew ? 'required' : '' ?> minlength="8">
        <small class="muted">حداقل ۸ کاراکتر.</small>
    </label>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">ذخیره</button>
        <a href="<?= e(url('users')) ?>" class="btn btn-ghost">انصراف</a>
    </div>
</form>
