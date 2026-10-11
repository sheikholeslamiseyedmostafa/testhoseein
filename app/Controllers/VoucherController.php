<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Account;
use App\Models\Voucher;
use App\Support\Jalali;
use App\Support\Money;

final class VoucherController extends Controller
{
    private const MIN_FORM_ROWS = 6;

    public function index(): void
    {
        $filters = $this->filters();
        $this->render('vouchers/index', [
            'title'    => 'اسناد حسابداری',
            'vouchers' => Voucher::list($filters),
            'filters'  => $this->filterDisplay($filters),
        ]);
    }

    public function show(): void
    {
        $voucher = Voucher::find($this->queryId());
        if ($voucher === null) {
            $this->notFound();
        }

        $this->render('vouchers/show', [
            'title'   => 'سند شماره ' . $voucher['voucher_no'],
            'voucher' => $voucher,
        ]);
    }

    public function create(): void
    {
        $this->render('vouchers/form', [
            'title'   => 'سند جدید',
            'action'  => 'vouchers/store',
            'accounts' => Account::activeOptions(),
            'form'    => [
                'voucher_date' => jdate(Jalali::today()),
                'description'  => '',
                'lines'        => $this->blankLines(self::MIN_FORM_ROWS),
            ],
        ]);
    }

    public function store(): void
    {
        $result = $this->collect();
        if ($result['errors'] !== []) {
            $this->renderForm('vouchers/store', 'سند جدید', $result);
            return;
        }

        $id = Voucher::save(null, $result['date'], $result['description'], $result['lines'], $this->userId());
        flash('success', 'سند با موفقیت ثبت شد.');
        redirect('vouchers/show', ['id' => $id]);
    }

    public function edit(): void
    {
        $voucher = Voucher::find($this->queryId());
        if ($voucher === null) {
            $this->notFound();
        }

        $lines = [];
        foreach ($voucher['lines'] as $line) {
            $lines[] = [
                'account_id'  => $line['account_id'],
                'description' => $line['description'],
                'debit'       => Money::toInput($line['debit_cents']),
                'credit'      => Money::toInput($line['credit_cents']),
            ];
        }
        $lines = array_merge($lines, $this->blankLines(max(2, self::MIN_FORM_ROWS - count($lines))));

        $this->render('vouchers/form', [
            'title'    => 'ویرایش سند ' . $voucher['voucher_no'],
            'action'   => 'vouchers/update',
            'voucher'  => $voucher,
            'accounts' => Account::activeOptions(),
            'form'     => [
                'voucher_date' => jdate($voucher['voucher_date']),
                'description'  => $voucher['description'],
                'lines'        => $lines,
            ],
        ]);
    }

    public function update(): void
    {
        $id = $this->queryId();
        $voucher = Voucher::find($id);
        if ($voucher === null) {
            $this->notFound();
        }

        $result = $this->collect();
        if ($result['errors'] !== []) {
            $this->renderForm('vouchers/update', 'ویرایش سند ' . $voucher['voucher_no'], $result, $voucher);
            return;
        }

        Voucher::save($id, $result['date'], $result['description'], $result['lines'], $this->userId());
        flash('success', 'سند ویرایش شد.');
        redirect('vouchers/show', ['id' => $id]);
    }

    public function destroy(): void
    {
        $voucher = Voucher::find($this->queryId());
        if ($voucher === null) {
            $this->notFound();
        }

        Voucher::delete($voucher['id']);
        flash('success', 'سند حذف شد.');
        redirect('vouchers');
    }

    /** اعتبارسنجی سند: تاریخ، ردیف‌ها و تراز بدهکار و بستانکار */
    private function collect(): array
    {
        $errors = [];
        $rawDate = $this->postString('voucher_date', 30);
        $date = Jalali::parse($rawDate);
        if ($date === null) {
            $errors[] = 'تاریخ سند معتبر نیست. نمونه: ۱۴۰۵/۰۷/۱۹';
        }

        $description = $this->postString('description', 500);
        $activeIds = array_column(Account::activeOptions(), 'id');

        $rawRows = is_array($_POST['lines'] ?? null) ? array_values($_POST['lines']) : [];
        $formRows = [];
        $lines = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($rawRows as $index => $row) {
            $row = is_array($row) ? $row : [];
            $accountRaw = is_string($row['account_id'] ?? null) ? $row['account_id'] : '';
            $accountId = ctype_digit($accountRaw) ? (int) $accountRaw : 0;
            $lineDescription = is_string($row['description'] ?? null) ? trim(mb_substr($row['description'], 0, 255)) : '';
            $debitRaw = is_string($row['debit'] ?? null) ? trim($row['debit']) : '';
            $creditRaw = is_string($row['credit'] ?? null) ? trim($row['credit']) : '';

            $formRows[] = [
                'account_id'  => $accountRaw,
                'description' => $lineDescription,
                'debit'       => $debitRaw,
                'credit'      => $creditRaw,
            ];

            if ($accountRaw === '' && $lineDescription === '' && $debitRaw === '' && $creditRaw === '') {
                continue; // ردیف خالی نادیده گرفته می‌شود
            }

            $lineNo = $index + 1;
            $debit = Money::parse($debitRaw);
            $credit = Money::parse($creditRaw);

            if ($accountId === 0 || !in_array($accountId, $activeIds, true)) {
                $errors[] = "ردیف {$lineNo}: حساب معتبر و فعال انتخاب کنید.";
            }
            if ($debit === null || $credit === null) {
                $errors[] = "ردیف {$lineNo}: مبلغ نامعتبر است.";
                continue;
            }
            if (($debit > 0) === ($credit > 0)) {
                $errors[] = "ردیف {$lineNo}: فقط یکی از ستون‌های بدهکار یا بستانکار را پر کنید (و مبلغ آن بزرگ‌تر از صفر باشد).";
                continue;
            }

            $lines[] = [
                'account_id'    => $accountId,
                'description'   => $lineDescription,
                'debit_cents'   => $debit,
                'credit_cents'  => $credit,
            ];
            $totalDebit += $debit;
            $totalCredit += $credit;
        }

        if (count($lines) < 2) {
            $errors[] = 'سند باید حداقل دو ردیف معتبر داشته باشد.';
        }
        if ($totalDebit !== $totalCredit) {
            $errors[] = 'جمع بدهکار (' . money($totalDebit) . ') با جمع بستانکار (' . money($totalCredit) . ') برابر نیست.';
        } elseif ($totalDebit === 0 && $lines !== []) {
            $errors[] = 'مبلغ سند نمی‌تواند صفر باشد.';
        }

        return [
            'errors'      => $errors,
            'date'        => $date,
            'description' => $description,
            'lines'       => $lines,
            'form'        => [
                'voucher_date' => $rawDate,
                'description'  => $description,
                'lines'        => $formRows,
            ],
        ];
    }

    private function renderForm(string $action, string $title, array $result, ?array $voucher = null): void
    {
        http_response_code(422);
        $this->render('vouchers/form', [
            'title'    => $title,
            'action'   => $action,
            'voucher'  => $voucher,
            'accounts' => Account::activeOptions(),
            'form'     => array_merge($result['form'], [
                'lines' => array_merge(
                    $result['form']['lines'],
                    $this->blankLines(max(2, self::MIN_FORM_ROWS - count($result['form']['lines'])))
                ),
            ]),
            'errors'   => $result['errors'],
        ]);
    }

    private function filters(): array
    {
        $from = Jalali::parse($this->queryString('from', 30));
        $to = Jalali::parse($this->queryString('to', 30));
        return [
            'from' => $from ?? '',
            'to'   => $to ?? '',
            'q'    => $this->queryString('q', 100),
        ];
    }

    private function filterDisplay(array $filters): array
    {
        return [
            'from' => $filters['from'] !== '' ? jdate($filters['from']) : '',
            'to'   => $filters['to'] !== '' ? jdate($filters['to']) : '',
            'q'    => $filters['q'],
        ];
    }

    private function blankLines(int $count): array
    {
        return array_fill(0, $count, ['account_id' => '', 'description' => '', 'debit' => '', 'credit' => '']);
    }

    private function userId(): int
    {
        return (int) (Auth::user()['id'] ?? 0);
    }
}
