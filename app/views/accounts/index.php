<?php
/** @var array $accounts @var string $search */
use App\Models\Account;
?>
<div class="page-head">
    <div>
        <h1>چارت حساب‌ها</h1>
        <p class="muted">حساب‌های معین سیستم. حسابی که در سند استفاده شده، فقط غیرفعال می‌شود.</p>
    </div>
    <?php if (can('write')): ?>
        <a class="btn btn-primary" href="<?= e(url('accounts/create')) ?>">حساب جدید</a>
    <?php endif; ?>
</div>

<form method="get" action="index.php" class="toolbar no-print">
    <input type="hidden" name="r" value="accounts">
    <label class="grow">
        <span class="sr-only">جستجو</span>
        <input type="search" name="q" value="<?= e($search) ?>" placeholder="جستجوی کد یا نام حساب">
    </label>
    <button type="submit" class="btn">جستجو</button>
</form>

<div class="card">
    <?php if ($accounts === []): ?>
        <p class="muted">حسابی پیدا نشد.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>کد</th>
                <th>نام حساب</th>
                <th>نوع</th>
                <th class="num">تعداد ردیف سند</th>
                <th>وضعیت</th>
                <?php if (can('write')): ?><th class="actions">عملیات</th><?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($accounts as $a): ?>
                <tr class="<?= $a['is_active'] ? '' : 'row-muted' ?>">
                    <td dir="ltr" class="code"><?= e(fa($a['code'])) ?></td>
                    <td><?= e($a['name']) ?></td>
                    <td><?= e(Account::TYPES[$a['type']] ?? $a['type']) ?></td>
                    <td class="num"><?= fa((string) $a['usage_count']) ?></td>
                    <td>
                        <?php if ($a['is_active']): ?>
                            <span class="badge badge-ok">فعال</span>
                        <?php else: ?>
                            <span class="badge">غیرفعال</span>
                        <?php endif; ?>
                    </td>
                    <?php if (can('write')): ?>
                        <td class="actions">
                            <a href="<?= e(url('accounts/edit', ['id' => $a['id']])) ?>">ویرایش</a>
                            <form method="post" action="<?= e(url('accounts/toggle', ['id' => $a['id']])) ?>" class="inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="link-button"><?= $a['is_active'] ? 'غیرفعال کردن' : 'فعال کردن' ?></button>
                            </form>
                            <?php if ($a['usage_count'] === 0): ?>
                                <form method="post" action="<?= e(url('accounts/delete', ['id' => $a['id']])) ?>" class="inline"
                                      data-confirm="حساب «<?= e($a['name']) ?>» حذف شود؟">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="link-button danger">حذف</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
