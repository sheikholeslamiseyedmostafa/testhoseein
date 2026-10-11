<?php
/** @var array $pl @var array $counts @var string $from @var string $to @var string $today @var array $recent @var int $accounts */
?>
<div class="page-head">
    <div>
        <h1>داشبورد</h1>
        <p class="muted">گزارش سال مالی از <?= jdate($from) ?> تا <?= jdate($to) ?></p>
    </div>
    <?php if (can('write')): ?>
        <a class="btn btn-primary" href="<?= e(url('vouchers/create')) ?>">ثبت سند جدید</a>
    <?php endif; ?>
</div>

<section class="cards">
    <article class="stat">
        <span class="stat-label">حساب‌های فعال</span>
        <strong class="stat-value"><?= fa((string) $counts['accounts']) ?></strong>
    </article>
    <article class="stat">
        <span class="stat-label">تعداد اسناد</span>
        <strong class="stat-value"><?= fa((string) $counts['vouchers']) ?></strong>
    </article>
    <article class="stat stat-income">
        <span class="stat-label">درآمد سال مالی (ریال)</span>
        <strong class="stat-value"><?= money($pl['revenue']) ?></strong>
    </article>
    <article class="stat stat-expense">
        <span class="stat-label">هزینه سال مالی (ریال)</span>
        <strong class="stat-value"><?= money($pl['expense']) ?></strong>
    </article>
    <article class="stat <?= $pl['profit'] >= 0 ? 'stat-income' : 'stat-expense' ?>">
        <span class="stat-label"><?= $pl['profit'] >= 0 ? 'سود' : 'زیان' ?> خالص (ریال)</span>
        <strong class="stat-value"><?= money(abs($pl['profit'])) ?></strong>
    </article>
</section>

<section class="card">
    <div class="section-head">
        <h2>آخرین اسناد</h2>
        <a href="<?= e(url('vouchers')) ?>">مشاهده همه</a>
    </div>
    <?php if ($recent === []): ?>
        <p class="muted">هنوز سندی ثبت نشده است. از دکمه «ثبت سند جدید» شروع کنید.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>شماره</th>
                    <th>تاریخ</th>
                    <th>شرح</th>
                    <th class="num">مبلغ (ریال)</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($recent as $v): ?>
                    <tr>
                        <td><a href="<?= e(url('vouchers/show', ['id' => $v['id']])) ?>"><?= fa((string) $v['voucher_no']) ?></a></td>
                        <td><?= jdate($v['voucher_date']) ?></td>
                        <td><?= e($v['description']) ?></td>
                        <td class="num"><?= money($v['total_cents']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
