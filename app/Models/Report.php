<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

/** گزارش‌ها: دفتر کل، تراز آزمایشی، خلاصه داشبورد */
final class Report
{
    /**
     * دفتر کل یک حساب در بازه تاریخی.
     * مانده‌ها همیشه «خالص بدهکار منهای بستانکار» هستند؛ مثبت = بدهکار، منفی = بستانکار.
     *
     * @return array{opening:int,rows:list<array>,closing:int,debit:int,credit:int}
     */
    public static function ledger(int $accountId, string $from, string $to): array
    {
        $opening = (int) Db::value(
            'SELECT COALESCE(SUM(l.debit_cents - l.credit_cents), 0)
             FROM voucher_lines l
             JOIN vouchers v ON v.id = l.voucher_id
             WHERE l.account_id = ? AND v.voucher_date < ?',
            [$accountId, $from]
        );

        $raw = Db::all(
            'SELECT v.id AS voucher_id, v.voucher_no, v.voucher_date, v.description AS voucher_description,
                    l.description AS line_description, l.debit_cents, l.credit_cents
             FROM voucher_lines l
             JOIN vouchers v ON v.id = l.voucher_id
             WHERE l.account_id = ? AND v.voucher_date BETWEEN ? AND ?
             ORDER BY v.voucher_date, v.voucher_no, l.id',
            [$accountId, $from, $to]
        );

        $running = $opening;
        $debit = 0;
        $credit = 0;
        $rows = [];
        foreach ($raw as $row) {
            $d = (int) $row['debit_cents'];
            $c = (int) $row['credit_cents'];
            $running += $d - $c;
            $debit += $d;
            $credit += $c;
            $rows[] = [
                'voucher_id'          => (int) $row['voucher_id'],
                'voucher_no'          => (int) $row['voucher_no'],
                'voucher_date'        => $row['voucher_date'],
                'voucher_description' => $row['voucher_description'],
                'line_description'    => $row['line_description'],
                'debit_cents'         => $d,
                'credit_cents'        => $c,
                'balance_cents'       => $running,
            ];
        }

        return [
            'opening' => $opening,
            'rows'    => $rows,
            'closing' => $running,
            'debit'   => $debit,
            'credit'  => $credit,
        ];
    }

    /**
     * تراز آزمایشی: گردش دوره و مانده افتتاحیه/پایانی هر حساب.
     * @return list<array>
     */
    public static function trialBalance(string $from, string $to): array
    {
        $rows = Db::all(
            'SELECT a.id, a.code, a.name, a.type, a.is_active,
                COALESCE(SUM(CASE WHEN v.voucher_date < ? THEN l.debit_cents - l.credit_cents ELSE 0 END), 0) AS opening_net,
                COALESCE(SUM(CASE WHEN v.voucher_date >= ? THEN l.debit_cents ELSE 0 END), 0) AS period_debit,
                COALESCE(SUM(CASE WHEN v.voucher_date >= ? THEN l.credit_cents ELSE 0 END), 0) AS period_credit
             FROM accounts a
             LEFT JOIN voucher_lines l ON l.account_id = a.id
             LEFT JOIN vouchers v ON v.id = l.voucher_id AND v.voucher_date <= ?
             GROUP BY a.id, a.code, a.name, a.type, a.is_active
             ORDER BY a.code',
            [$from, $from, $from, $to]
        );

        $result = [];
        foreach ($rows as $row) {
            $opening = (int) $row['opening_net'];
            $periodDebit = (int) $row['period_debit'];
            $periodCredit = (int) $row['period_credit'];
            $closing = $opening + $periodDebit - $periodCredit;

            // فقط حساب‌هایی که در این بازه یا قبل از آن گردش داشته‌اند
            if ($opening === 0 && $periodDebit === 0 && $periodCredit === 0) {
                continue;
            }

            $result[] = [
                'id'            => (int) $row['id'],
                'code'          => $row['code'],
                'name'          => $row['name'],
                'type'          => $row['type'],
                'opening_net'   => $opening,
                'period_debit'  => $periodDebit,
                'period_credit' => $periodCredit,
                'closing_net'   => $closing,
            ];
        }
        return $result;
    }

    /**
     * جمع درآمد و هزینه در بازه (برای داشبورد و سود/زیان خلاصه).
     * @return array{revenue:int,expense:int,profit:int}
     */
    public static function profitAndLoss(string $from, string $to): array
    {
        $rows = Db::all(
            'SELECT a.type, COALESCE(SUM(l.debit_cents), 0) AS debit, COALESCE(SUM(l.credit_cents), 0) AS credit
             FROM voucher_lines l
             JOIN accounts a ON a.id = l.account_id
             JOIN vouchers v ON v.id = l.voucher_id
             WHERE v.voucher_date BETWEEN ? AND ?
             GROUP BY a.type',
            [$from, $to]
        );

        $revenue = 0;
        $expense = 0;
        foreach ($rows as $row) {
            if ($row['type'] === 'revenue') {
                $revenue += (int) $row['credit'] - (int) $row['debit'];
            } elseif ($row['type'] === 'expense') {
                $expense += (int) $row['debit'] - (int) $row['credit'];
            }
        }

        return ['revenue' => $revenue, 'expense' => $expense, 'profit' => $revenue - $expense];
    }

    public static function counts(): array
    {
        return [
            'accounts' => (int) Db::value('SELECT COUNT(*) FROM accounts WHERE is_active = 1'),
            'vouchers' => (int) Db::value('SELECT COUNT(*) FROM vouchers'),
        ];
    }
}
