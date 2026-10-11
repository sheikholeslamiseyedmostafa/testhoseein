<?php
/** @var array $persons @var string $search @var string $type */
use App\Models\Person;
?>
<div class="page-head">
    <div>
        <h1>اشخاص</h1>
        <p class="muted">مشتریان، تأمین‌کنندگان و سایر طرف‌های حساب. شخصی که در سند استفاده شود، فقط غیرفعال می‌شود.</p>
    </div>
    <?php if (can('write')): ?>
        <a class="btn btn-primary" href="<?= e(url('persons/create')) ?>"><?= icon('plus') ?><span>شخص جدید</span></a>
    <?php endif; ?>
</div>

<form method="get" action="index.php" class="toolbar no-print">
    <input type="hidden" name="r" value="persons">
    <label class="grow">
        <span>جستجو</span>
        <input type="search" name="q" value="<?= e($search) ?>" placeholder="کد، نام، کد ملی یا موبایل">
    </label>
    <label>
        <span>نوع</span>
        <select name="type">
            <option value="">همه</option>
            <?php foreach (Person::TYPES as $key => $label): ?>
                <option value="<?= e($key) ?>"<?= $type === $key ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit" class="btn">اعمال</button>
</form>

<div class="card">
    <?php if ($persons === []): ?>
        <p class="muted">شخصی پیدا نشد. با دکمه‌ی «شخص جدید» اولین شخص را ثبت کنید.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>کد</th>
                <th>نام</th>
                <th>نوع</th>
                <th>کد ملی / شناسه ملی</th>
                <th>موبایل</th>
                <th>تلفن</th>
                <th>وضعیت</th>
                <?php if (can('write')): ?><th class="actions">عملیات</th><?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($persons as $p): ?>
                <tr class="<?= $p['is_active'] ? '' : 'row-muted' ?>">
                    <td dir="ltr" class="code"><?= e(fa($p['code'])) ?></td>
                    <td><?= e($p['name']) ?></td>
                    <td><?= e(Person::TYPES[$p['person_type']] ?? $p['person_type']) ?></td>
                    <td dir="ltr" class="code"><?= $p['national_id'] !== '' ? e(fa($p['national_id'])) : '<span class="muted">—</span>' ?></td>
                    <td dir="ltr" class="code"><?= $p['mobile'] !== '' ? e(fa($p['mobile'])) : '<span class="muted">—</span>' ?></td>
                    <td dir="ltr" class="code"><?= $p['phone'] !== '' ? e(fa($p['phone'])) : '<span class="muted">—</span>' ?></td>
                    <td>
                        <?php if ($p['is_active']): ?>
                            <span class="badge badge-ok">فعال</span>
                        <?php else: ?>
                            <span class="badge">غیرفعال</span>
                        <?php endif; ?>
                    </td>
                    <?php if (can('write')): ?>
                        <td class="actions">
                            <a href="<?= e(url('persons/edit', ['id' => $p['id']])) ?>">ویرایش</a>
                            <form method="post" action="<?= e(url('persons/toggle', ['id' => $p['id']])) ?>" class="inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="link-button"><?= $p['is_active'] ? 'غیرفعال کردن' : 'فعال کردن' ?></button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
