<?php
/** @var array $accounts @var array|null $account @var array|null $report @var array $display */
use App\Models\Account;

$balanceLabel = static function (int $net): string {
    if ($net === 0) {
        return 'صفر';
    }
    return $net > 0 ? 'بدهکار' : 'بستانکار';
};
?>
<div class="page-head no-print">
    <div>
        <h1>دفتر کل</h1>
        <p class="muted">گردش و مانده یک حساب در بازه تاریخی</p>
    </div>
    <?php if ($report !== null): ?>
        <div class="head-actions">
            <button type="button" class="btn btn-ghost" data-print>چاپ</button>
        </div>
    <?php endif; ?>
</div>

<form method="get" action="index.php" class="toolbar no-print">
    <input type="hidden" name="r" value="reports/ledger">
    <label class="grow">
        <span>حساب</span>
        <select name="id" required>
            <option value="">— انتخاب حساب —</option>
            <?php foreach ($accounts as $a): ?>
                <option value="<?= e($a['id']) ?>"<?= $account !== null && $account['id'] === $a['id'] ? ' selected' : '' ?>>
                    <?= e(fa($a['code'])) ?> - <?= e($a['name']) ?><?= $a['is_active'] ? '' : ' (غیرفعال)' ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
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

<?php if ($account !== null && $report !== null): ?>
<section class="card report-sheet">
    <h2><?= e(fa($account['code'])) ?> - <?= e($account['name']) ?></h2>
    <p class="muted">نوع: <?= e(Account::TYPES[$account['type']] ?? $account['type']) ?> · از <?= e($display['from']) ?> تا <?= e($display['to']) ?></p>

    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>تاریخ</th>
                <th>شماره سند</th>
                <th>شرح سند</th>
                <th>شرح ردیف</th>
                <th class="num">بدهکار</th>
                <th class="num">بستانکار</th>
                <th class="num">مانده</th>
                <th>ماهیت</th>
            </tr>
            </thead>
            <tbody>
            <tr class="row-muted">
                <td colspan="6"><strong>مانده ابتدای دوره</strong></td>
                <td class="num"><?= money(abs($report['opening'])) ?></td>
                <td><?= e($balanceLabel($report['opening'])) ?></td>
            </tr>
            <?php foreach ($report['rows'] as $row): ?>
                <tr>
                    <td><?= jdate($row['voucher_date']) ?></td>
                    <td><a href="<?= e(url('vouchers/show', ['id' => $row['voucher_id']])) ?>"><?= fa((string) $row['voucher_no']) ?></a></td>
                    <td><?= e($row['voucher_description']) ?></td>
                    <td><?= e($row['line_description']) ?></td>
                    <td class="num"><?= $row['debit_cents'] > 0 ? money($row['debit_cents']) : '' ?></td>
                    <td class="num"><?= $row['credit_cents'] > 0 ? money($row['credit_cents']) : '' ?></td>
                    <td class="num"><?= money(abs($row['balance_cents'])) ?></td>
                    <td><?= e($balanceLabel($row['balance_cents'])) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($report['rows'] === []): ?>
                <tr><td colspan="8" class="muted">در این بازه گردشی ثبت نشده است.</td></tr>
            <?php endif; ?>
            </tbody>
            <tfoot>
            <tr>
                <th colspan="4">جمع گردش دوره</th>
                <th class="num"><?= money($report['debit']) ?></th>
                <th class="num"><?= money($report['credit']) ?></th>
                <th class="num"><?= money(abs($report['closing'])) ?></th>
                <th><?= e($balanceLabel($report['closing'])) ?></th>
            </tr>
            </tfoot>
        </table>
    </div>
</section>
<?php endif; ?>
