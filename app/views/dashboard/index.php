<?php
/** @var array $pl @var array $counts @var string $from @var string $to @var string $today @var array $recent @var int $accounts */
$profit = (int) $pl['profit'];
$profitLabel = $profit >= 0 ? 'سود خالص (ریال)' : 'زیان خالص (ریال)';

// دسته‌بندی اطلاعات پایه: [برچسب، آیکون، رنگ، مسیر(null=به‌زودی)، شمارش]
$categories = [
    ['اشخاص', 'users', 'blue', null, null],
    ['بانک و صندوق', 'bank', 'green', null, null],
    ['کالا و خدمات', 'box', 'orange', null, null],
    ['سرفصل حساب‌ها', 'accounts', 'purple', 'accounts', $counts['accounts']],
    ['پروژه‌ها', 'folder', 'teal', null, null],
    ['مراکز هزینه', 'chart', 'red', null, null],
    ['واحدهای سنجش', 'ruler', 'blue', null, null],
    ['ارزها', 'dollar', 'green', null, null],
    ['مالیات و عوارض', 'percent', 'orange', null, null],
    ['روش‌های پرداخت', 'card', 'purple', null, null],
    ['سال مالی', 'calendar', 'teal', null, null],
    ['تنظیمات پایه', 'gear', 'gray', null, null],
];
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

<div class="dash-grid">
    <section class="card">
        <div class="panel-title">
            <h2><?= icon('database') ?><span>دسته‌بندی اطلاعات پایه</span></h2>
        </div>
        <div class="cat-grid">
            <?php foreach ($categories as [$label, $iconName, $tone, $route, $count]): ?>
                <?php if ($route !== null && can('view')): ?>
                    <a class="cat-tile tone-<?= e($tone) ?>" href="<?= e(url($route)) ?>">
                        <span class="cat-head">
                            <span class="cat-icon"><?= icon($iconName) ?></span>
                            <span class="cat-name"><?= e($label) ?></span>
                        </span>
                        <span class="cat-foot">
                            <span class="cat-count"><?= fa((string) $count) ?> مورد</span>
                            <span class="cat-enter">ورود <?= icon('arrow', 'icon arrow-ico') ?></span>
                        </span>
                    </a>
                <?php else: ?>
                    <div class="cat-tile is-soon tone-<?= e($tone) ?>">
                        <span class="cat-head">
                            <span class="cat-icon"><?= icon($iconName) ?></span>
                            <span class="cat-name"><?= e($label) ?></span>
                        </span>
                        <span class="cat-foot">
                            <span class="cat-count">—</span>
                            <span class="nav-soon">به‌زودی</span>
                        </span>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="card">
        <div class="panel-title">
            <h2><?= icon('plus') ?><span>اقدامات سریع</span></h2>
        </div>
        <div class="quick-list">
            <?php if (can('write')): ?>
                <a class="quick-row tone-blue" href="<?= e(url('vouchers/create')) ?>">
                    <span class="quick-ico"><?= icon('vouchers') ?></span><span>ثبت سند جدید</span>
                </a>
                <a class="quick-row tone-purple" href="<?= e(url('accounts/create')) ?>">
                    <span class="quick-ico"><?= icon('accounts') ?></span><span>ایجاد حساب جدید</span>
                </a>
            <?php endif; ?>
            <a class="quick-row tone-teal" href="<?= e(url('reports/ledger')) ?>">
                <span class="quick-ico"><?= icon('ledger') ?></span><span>گزارش دفتر کل</span>
            </a>
            <a class="quick-row tone-orange" href="<?= e(url('reports/trial')) ?>">
                <span class="quick-ico"><?= icon('trial') ?></span><span>تراز آزمایشی</span>
            </a>
        </div>
    </section>
</div>

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
