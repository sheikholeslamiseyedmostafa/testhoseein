<?php
/** @var array $rows @var array $totals @var array $display */
use App\Models\Account;

$totalsBalanced = $totals['period_debit'] === $totals['period_credit']
    && $totals['closing_debit'] === $totals['closing_credit'];
?>
<div class="page-head no-print">
    <div>
        <h1>تراز آزمایشی</h1>
        <p class="muted">حساب‌هایی که در این بازه یا قبل از آن گردش داشته‌اند</p>
    </div>
    <div class="head-actions">
        <button type="button" class="btn btn-ghost" data-print>چاپ</button>
    </div>
</div>

<form method="get" action="index.php" class="toolbar no-print">
    <input type="hidden" name="r" value="reports/trial">
    <label>
        <span>از تاریخ</span>
        <input type="text" name="from" value="<?= e($display['from']) ?>" dir="ltr" inputmode="numeric">
    </label>
    <label>
        <span>تا تاریخ</span>
        <input type="text" name="to" value="<?= e($display['to']) ?>" dir="ltr" inputmode="numeric">
    </label>
    <button type="submit" class="btn btn-primary">نمایش</button>
</form>

<section class="card report-sheet">
    <p class="muted">بازه: از <?= e($display['from']) ?> تا <?= e($display['to']) ?></p>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th rowspan="2">کد</th>
                <th rowspan="2">نام حساب</th>
                <th colspan="2" class="center">مانده ابتدای دوره</th>
                <th colspan="2" class="center">گردش دوره</th>
                <th colspan="2" class="center">مانده پایان دوره</th>
            </tr>
            <tr>
                <th class="num">بدهکار</th>
                <th class="num">بستانکار</th>
                <th class="num">بدهکار</th>
                <th class="num">بستانکار</th>
                <th class="num">بدهکار</th>
                <th class="num">بستانکار</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td dir="ltr" class="code"><?= e(fa($r['code'])) ?></td>
                    <td><?= e($r['name']) ?></td>
                    <td class="num"><?= $r['opening_debit'] ? money($r['opening_debit']) : '' ?></td>
                    <td class="num"><?= $r['opening_credit'] ? money($r['opening_credit']) : '' ?></td>
                    <td class="num"><?= $r['period_debit'] ? money($r['period_debit']) : '' ?></td>
                    <td class="num"><?= $r['period_credit'] ? money($r['period_credit']) : '' ?></td>
                    <td class="num"><?= $r['closing_debit'] ? money($r['closing_debit']) : '' ?></td>
                    <td class="num"><?= $r['closing_credit'] ? money($r['closing_credit']) : '' ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($rows === []): ?>
                <tr><td colspan="8" class="muted">در این بازه سندی ثبت نشده است.</td></tr>
            <?php endif; ?>
            </tbody>
            <tfoot>
            <tr>
                <th colspan="2">جمع</th>
                <th class="num"><?= money($totals['opening_debit']) ?></th>
                <th class="num"><?= money($totals['opening_credit']) ?></th>
                <th class="num"><?= money($totals['period_debit']) ?></th>
                <th class="num"><?= money($totals['period_credit']) ?></th>
                <th class="num"><?= money($totals['closing_debit']) ?></th>
                <th class="num"><?= money($totals['closing_credit']) ?></th>
            </tr>
            </tfoot>
        </table>
    </div>
    <p class="<?= $totalsBalanced ? 'badge badge-ok' : 'badge badge-error' ?>">
        <?= $totalsBalanced
            ? 'تراز برقرار است: جمع بدهکار با جمع بستانکار برابر است.'
            : 'هشدار: جمع بدهکار و بستانکار برابر نیست. داده‌ها را بررسی کنید.' ?>
    </p>
</section>
