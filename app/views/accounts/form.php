<?php
/** @var array $account @var string $action @var string $title */
use App\Models\Account;

$isEdit = !empty($account['id']);
$postUrl = $isEdit ? url($action, ['id' => $account['id']]) : url($action);
?>
<div class="page-head">
    <h1><?= e($title) ?></h1>
    <a href="<?= e(url('accounts')) ?>" class="btn btn-ghost">بازگشت به فهرست</a>
</div>

<form method="post" action="<?= e($postUrl) ?>" class="card form narrow-form">
    <?= csrf_field() ?>
    <label>
        <span>کد حساب</span>
        <input type="text" name="code" value="<?= e($account['code']) ?>" required maxlength="20" dir="ltr"
               pattern="[0-9A-Za-z][0-9A-Za-z.\-]{0,19}" placeholder="مثلاً 1101">
        <small class="muted">حروف لاتین، عدد، نقطه یا خط تیره؛ حداکثر ۲۰ کاراکتر.</small>
    </label>
    <label>
        <span>نام حساب</span>
        <input type="text" name="name" value="<?= e($account['name']) ?>" required maxlength="150">
    </label>
    <label>
        <span>نوع حساب</span>
        <select name="type" required>
            <?php foreach (Account::TYPES as $key => $label): ?>
                <option value="<?= e($key) ?>"<?= $account['type'] === $key ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">ذخیره</button>
        <a href="<?= e(url('accounts')) ?>" class="btn btn-ghost">انصراف</a>
    </div>
</form>
