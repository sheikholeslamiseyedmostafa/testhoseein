<?php
/** @var array $voucher */

$balanced = $voucher['total_debit'] === $voucher['total_credit'];
?>
<div class="page-head no-print">
    <div>
        <h1>سند شماره <?= fa((string) $voucher['voucher_no']) ?></h1>
        <p class="muted">تاریخ <?= jdate($voucher['voucher_date']) ?></p>
    </div>
    <div class="head-actions">
        <a class="btn btn-ghost" href="<?= e(url('vouchers')) ?>">فهرست اسناد</a>
        <button type="button" class="btn btn-ghost" data-print>چاپ</button>
        <?php if (can('write')): ?>
            <a class="btn btn-primary" href="<?= e(url('vouchers/edit', ['id' => $voucher['id']])) ?>">ویرایش</a>
            <form method="post" action="<?= e(url('vouchers/delete', ['id' => $voucher['id']])) ?>" class="inline"
                  data-confirm="این سند حذف شود؟ این عملیات برگشت‌پذیر نیست.">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger">حذف</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<section class="card voucher-sheet">
    <div class="sheet-head">
        <div><strong>شماره سند:</strong> <?= fa((string) $voucher['voucher_no']) ?></div>
        <div><strong>تاریخ:</strong> <?= jdate($voucher['voucher_date']) ?></div>
    </div>
    <p class="sheet-desc"><strong>شرح:</strong> <?= e($voucher['description']) ?></p>

    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th class="row-no">#</th>
                <th>کد</th>
                <th>حساب</th>
                <th>شرح ردیف</th>
                <th class="num">بدهکار (ریال)</th>
                <th class="num">بستانکار (ریال)</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($voucher['lines'] as $i => $line): ?>
                <tr>
                    <td class="row-no"><?= fa((string) ($i + 1)) ?></td>
                    <td dir="ltr" class="code"><?= e(fa($line['account_code'])) ?></td>
                    <td><?= e($line['account_name']) ?></td>
                    <td><?= e($line['description']) ?></td>
                    <td class="num"><?= $line['debit_cents'] > 0 ? money($line['debit_cents']) : '' ?></td>
                    <td class="num"><?= $line['credit_cents'] > 0 ? money($line['credit_cents']) : '' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <th colspan="4">جمع</th>
                <th class="num"><?= money($voucher['total_debit']) ?></th>
                <th class="num"><?= money($voucher['total_credit']) ?></th>
            </tr>
            </tfoot>
        </table>
    </div>

    <p class="<?= $balanced ? 'badge badge-ok' : 'badge badge-error' ?>">
        <?= $balanced ? 'سند متوازن است (بدهکار = بستانکار)' : 'هشدار: سند متوازن نیست' ?>
    </p>
</section>
