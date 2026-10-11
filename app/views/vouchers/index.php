<?php
/** @var array $vouchers @var array $filters */
?>
<div class="page-head">
    <div>
        <h1>اسناد حسابداری</h1>
        <p class="muted">حداکثر ۳۰۰ سند آخر بر اساس فیلتر نمایش داده می‌شود.</p>
    </div>
    <?php if (can('write')): ?>
        <a class="btn btn-primary" href="<?= e(url('vouchers/create')) ?>">سند جدید</a>
    <?php endif; ?>
</div>

<form method="get" action="index.php" class="toolbar no-print">
    <input type="hidden" name="r" value="vouchers">
    <label>
        <span>از تاریخ</span>
        <input type="text" name="from" value="<?= e($filters['from']) ?>" placeholder="۱۴۰۵/۰۱/۰۱" dir="ltr" inputmode="numeric">
    </label>
    <label>
        <span>تا تاریخ</span>
        <input type="text" name="to" value="<?= e($filters['to']) ?>" placeholder="۱۴۰۵/۱۲/۲۹" dir="ltr" inputmode="numeric">
    </label>
    <label class="grow">
        <span>شرح سند</span>
        <input type="search" name="q" value="<?= e($filters['q']) ?>">
    </label>
    <button type="submit" class="btn">فیلتر</button>
    <a class="btn btn-ghost" href="<?= e(url('vouchers')) ?>">پاک کردن</a>
</form>

<div class="card">
    <?php if ($vouchers === []): ?>
        <p class="muted">سندی با این مشخصات پیدا نشد.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>شماره</th>
                <th>تاریخ</th>
                <th>شرح</th>
                <th class="num">ردیف</th>
                <th class="num">جمع مبلغ (ریال)</th>
                <th class="actions">عملیات</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($vouchers as $v): ?>
                <tr>
                    <td><a href="<?= e(url('vouchers/show', ['id' => $v['id']])) ?>"><?= fa((string) $v['voucher_no']) ?></a></td>
                    <td><?= jdate($v['voucher_date']) ?></td>
                    <td><?= e($v['description']) ?></td>
                    <td class="num"><?= fa((string) $v['line_count']) ?></td>
                    <td class="num"><?= money($v['total_cents']) ?></td>
                    <td class="actions">
                        <a href="<?= e(url('vouchers/show', ['id' => $v['id']])) ?>">مشاهده</a>
                        <?php if (can('write')): ?>
                            <a href="<?= e(url('vouchers/edit', ['id' => $v['id']])) ?>">ویرایش</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
