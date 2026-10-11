<?php
/** @var string $username */
?>
<section class="login-wrap">
    <div class="card login-card">
        <div class="login-logo" aria-hidden="true">
            <img src="assets/icons/icon-192.png" width="72" height="72" alt="">
        </div>
        <h1><?= e(config('app_name')) ?></h1>
        <p class="muted">برای ورود، نام کاربری و رمز عبور خود را وارد کنید.</p>

        <form method="post" action="<?= e(url('login/submit')) ?>" class="form stack">
            <?= csrf_field() ?>
            <label>
                <span>نام کاربری</span>
                <input type="text" name="username" value="<?= e($username) ?>" required autofocus
                       autocomplete="username" dir="ltr" maxlength="50">
            </label>
            <label>
                <span>رمز عبور</span>
                <input type="password" name="password" required autocomplete="current-password" dir="ltr" maxlength="200">
            </label>
            <button type="submit" class="btn btn-primary btn-block">ورود</button>
        </form>
    </div>
</section>
