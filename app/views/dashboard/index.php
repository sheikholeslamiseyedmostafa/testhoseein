<?php
/** @var array $pl @var array $counts @var string $from @var string $to @var string $today @var array $recent @var int $accounts */
$profit = (int) $pl['profit'];
$profitLabel = $profit >= 0 ? 'سود خالص (ریال)' : 'زیان خالص (ریال)';
?>
<div class="page-head">
    <div>
        <h1>داشبورد</h1>
        <p class="muted">گزارش سال مالی از <?= jdate($from) ?> تا <?= jdate($to) ?></p>
    </div>
    <?php if (can('write')): ?>
        <a class="btn btn-primary" href="<?= e(url('vouchers/create')) ?>"><?= icon('plus') ?><span>ثبت سند جدید</span></a>
    <?php endif; ?>
</div>

<section class="quick-grid" aria-label="میانبرها">
    <?php if (can('write')): ?>
        <a class="quick" href="<?= e(url('vouchers/create')) ?>"><?= icon('vouchers') ?><span>سند جدید</span></a>
        <a class="quick quick-purple" href="<?= e(url('accounts/create')) ?>"><?= icon('accounts') ?><span>حساب جدید</span></a>
    <?php endif; ?>
    <a class="quick quick-teal" href="<?= e(url('reports/ledger')) ?>"><?= icon('ledger') ?><span>دفتر کل</span></a>
    <a class="quick quick-orange" href="<?= e(url('reports/trial')) ?>"><?= icon('trial') ?><span>تراز آزمایشی</span></a>
</section>

<section class="cards">
    <article class="stat stat-blue">
        <span class="stat-icon"><?= icon('accounts') ?></span>
        <span class="stat-body">
            <span class="stat-label">حساب‌های فعال</span>
            <strong class="stat-value"><?= fa((string) $counts['accounts']) ?></strong>
        </span>
    </article>
    <article class="stat stat-purple">
        <span class="stat-icon"><?= icon('vouchers') ?></span>
        <span class="stat-body">
            <span class="stat-label">تعداد اسناد</span>
            <strong class="stat-value"><?= fa((string) $counts['vouchers']) ?></strong>
        </span>
    </article>
    <article class="stat stat-green">
        <span class="stat-icon"><?= icon('up') ?></span>
        <span class="stat-body">
            <span class="stat-label">درآمد سال مالی (ریال)</span>
            <strong class="stat-value"><?= money($pl['revenue']) ?></strong>
        </span>
    </article>
    <article class="stat stat-orange">
        <span class="stat-icon"><?= icon('down') ?></span>
        <span class="stat-body">
            <span class="stat-label">هزینه سال مالی (ریال)</span>
            <strong class="stat-value"><?= money($pl['expense']) ?></strong>
        </span>
    </article>
    <article class="stat <?= $profit >= 0 ? 'stat-teal' : 'stat-red' ?>">
        <span class="stat-icon"><?= icon('chart') ?></span>
        <span class="stat-body">
            <span class="stat-label"><?= e($profitLabel) ?></span>
            <strong class="stat-value"><?= money(abs($profit)) ?></strong>
        </span>
    </article>
</section>

<section class="card">
    <div class="section-head">
        <h2 class="card-title"><?= icon('clock') ?><span>آخرین اسناد ثبت‌شده</span></h2>
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
