<?php
/** @var array $person @var string $action @var string $title */
use App\Models\Person;

$isEdit = !empty($person['id']);
$postUrl = $isEdit ? url($action, ['id' => $person['id']]) : url($action);
$val = static fn(string $key): string => (string) ($person[$key] ?? '');
?>
<div class="page-head">
    <div>
        <h1><?= e($title) ?></h1>
        <p class="muted">فیلدهای ستاره‌دار الزامی‌اند؛ بقیه اختیاری‌اند.</p>
    </div>
    <a href="<?= e(url('persons')) ?>" class="btn btn-ghost">بازگشت به فهرست</a>
</div>

<form method="post" action="<?= e($postUrl) ?>" class="card form person-form" autocomplete="off">
    <?= csrf_field() ?>

    <h2>اطلاعات اصلی</h2>
    <div class="form-row">
        <label>
            <span>کد شخص *</span>
            <input type="text" name="code" value="<?= e($val('code')) ?>" required maxlength="20" dir="ltr"
                   pattern="[0-9A-Za-z][0-9A-Za-z.\-]{0,19}">
            <small class="muted">حروف لاتین، عدد، نقطه یا خط تیره.</small>
        </label>
        <label>
            <span>نوع شخص *</span>
            <select name="person_type" required>
                <?php foreach (Person::TYPES as $key => $label): ?>
                    <option value="<?= e($key) ?>"<?= $val('person_type') === $key ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>
    <label>
        <span>نام شخص یا شرکت *</span>
        <input type="text" name="name" value="<?= e($val('name')) ?>" required maxlength="150">
    </label>

    <h2>شناسه‌ها</h2>
    <div class="form-row">
        <label>
            <span>کد ملی (حقیقی) یا شناسه ملی (حقوقی)</span>
            <input type="text" name="national_id" value="<?= e($val('national_id')) ?>" maxlength="20" dir="ltr" inputmode="numeric">
            <small class="muted">۱۰ رقم برای شخص حقیقی، ۱۱ رقم برای شخص حقوقی.</small>
        </label>
        <label>
            <span>کد اقتصادی</span>
            <input type="text" name="economic_code" value="<?= e($val('economic_code')) ?>" maxlength="20" dir="ltr" inputmode="numeric">
        </label>
    </div>

    <h2>اطلاعات تماس</h2>
    <div class="form-row">
        <label>
            <span>موبایل</span>
            <input type="text" name="mobile" value="<?= e($val('mobile')) ?>" maxlength="30" dir="ltr" inputmode="tel" placeholder="09121234567">
        </label>
        <label>
            <span>تلفن ثابت</span>
            <input type="text" name="phone" value="<?= e($val('phone')) ?>" maxlength="30" dir="ltr" inputmode="tel" placeholder="021-12345678">
        </label>
    </div>
    <label>
        <span>ایمیل</span>
        <input type="email" name="email" value="<?= e($val('email')) ?>" maxlength="150" dir="ltr">
    </label>
    <label>
        <span>نشانی</span>
        <input type="text" name="address" value="<?= e($val('address')) ?>" maxlength="500">
    </label>
    <label>
        <span>یادداشت</span>
        <textarea name="notes" rows="3" maxlength="500"><?= e($val('notes')) ?></textarea>
    </label>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">ذخیره</button>
        <a href="<?= e(url('persons')) ?>" class="btn btn-ghost">انصراف</a>
    </div>
</form>
