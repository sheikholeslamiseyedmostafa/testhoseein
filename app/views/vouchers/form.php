<?php
/** @var array $form @var array $accounts @var string $action @var string $title @var array|null $voucher */

$postUrl = !empty($voucher['id']) ? url($action, ['id' => $voucher['id']]) : url($action);
$accountOptions = static function (array $accounts, string $selected): string {
    $html = '<option value="">— انتخاب حساب —</option>';
    foreach ($accounts as $a) {
        $sel = (string) $a['id'] === $selected ? ' selected' : '';
        $html .= '<option value="' . e($a['id']) . '"' . $sel . '>' . e(fa($a['code'])) . ' - ' . e($a['name']) . '</option>';
    }
    return $html;
};
?>
<div class="page-head">
    <h1><?= e($title) ?></h1>
    <a href="<?= e(url('vouchers')) ?>" class="btn btn-ghost">بازگشت به اسناد</a>
</div>

<?php if ($accounts === []): ?>
    <div class="alert alert-error">هنوز حساب فعالی تعریف نشده است. ابتدا از <a href="<?= e(url('accounts/create')) ?>">چارت حساب‌ها</a> حساب بسازید.</div>
<?php endif; ?>

<form method="post" action="<?= e($postUrl) ?>" class="card form" id="voucher-form" data-voucher-form>
    <?= csrf_field() ?>
    <div class="grid-2">
        <label>
            <span>تاریخ سند (شمسی)</span>
            <input type="text" name="voucher_date" value="<?= e($form['voucher_date']) ?>" required dir="ltr"
                   inputmode="numeric" placeholder="۱۴۰۵/۰۷/۱۹">
        </label>
        <label>
            <span>شرح کلی سند</span>
            <input type="text" name="description" value="<?= e($form['description']) ?>" maxlength="500" required>
        </label>
    </div>

    <div class="table-wrap">
        <table class="voucher-lines">
            <thead>
            <tr>
                <th class="row-no">#</th>
                <th>حساب</th>
                <th>شرح ردیف</th>
                <th class="num">بدهکار (ریال)</th>
                <th class="num">بستانکار (ریال)</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($form['lines'] as $i => $line): ?>
                <tr>
                    <td class="row-no"><?= fa((string) ($i + 1)) ?></td>
                    <td>
                        <select name="lines[<?= $i ?>][account_id]" aria-label="حساب ردیف <?= fa((string) ($i + 1)) ?>">
                            <?= $accountOptions($accounts, (string) $line['account_id']) ?>
                        </select>
                    </td>
                    <td>
                        <input type="text" name="lines[<?= $i ?>][description]" value="<?= e($line['description']) ?>" maxlength="255"
                               aria-label="شرح ردیف <?= fa((string) ($i + 1)) ?>">
                    </td>
                    <td>
                        <input type="text" class="num-input" name="lines[<?= $i ?>][debit]" value="<?= e($line['debit']) ?>"
                               inputmode="decimal" dir="ltr" data-debit aria-label="بدهکار ردیف <?= fa((string) ($i + 1)) ?>">
                    </td>
                    <td>
                        <input type="text" class="num-input" name="lines[<?= $i ?>][credit]" value="<?= e($line['credit']) ?>"
                               inputmode="decimal" dir="ltr" data-credit aria-label="بستانکار ردیف <?= fa((string) ($i + 1)) ?>">
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <th colspan="3">جمع</th>
                <td class="num" data-total-debit>۰</td>
                <td class="num" data-total-credit>۰</td>
            </tr>
            </tfoot>
        </table>
    </div>

    <p class="balance-status" data-balance-status aria-live="polite">
        ردیف‌های خالی نادیده گرفته می‌شوند. هر ردیف فقط بدهکار یا فقط بستانکار است.
    </p>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">ذخیره سند</button>
        <a href="<?= e(url('vouchers')) ?>" class="btn btn-ghost">انصراف</a>
    </div>
</form>
