<?php
/** @var string $title */
?>
<div class="page-head">
    <h1>تغییر رمز عبور</h1>
</div>

<form method="post" action="<?= e(url('password/update')) ?>" class="card form narrow-form" autocomplete="off">
    <?= csrf_field() ?>
    <label>
        <span>رمز عبور فعلی</span>
        <input type="password" name="current_password" required autocomplete="current-password" dir="ltr">
    </label>
    <label>
        <span>رمز عبور جدید</span>
        <input type="password" name="new_password" required minlength="8" autocomplete="new-password" dir="ltr">
    </label>
    <label>
        <span>تکرار رمز عبور جدید</span>
        <input type="password" name="confirm_password" required minlength="8" autocomplete="new-password" dir="ltr">
    </label>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">ذخیره رمز جدید</button>
        <a href="<?= e(url('dashboard')) ?>" class="btn btn-ghost">انصراف</a>
    </div>
</form>
