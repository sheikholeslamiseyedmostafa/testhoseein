<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Account;
use App\Models\Report;
use App\Support\Jalali;

final class ReportController extends Controller
{
    public function ledger(): void
    {
        [$from, $to, $display] = $this->range();
        $accounts = Account::options();

        $accountId = $this->queryId();
        $account = null;
        $report = null;
        if ($accountId > 0) {
            $account = Account::find($accountId);
            if ($account === null) {
                $this->notFound();
            }
            $report = Report::ledger($accountId, $from, $to);
        }

        $this->render('reports/ledger', [
            'title'    => 'دفتر کل',
            'accounts' => $accounts,
            'account'  => $account,
            'report'   => $report,
            'from'     => $from,
            'to'       => $to,
            'display'  => $display,
        ]);
    }

    public function trial(): void
    {
        [$from, $to, $display] = $this->range();

        $rows = Report::trialBalance($from, $to);
        $totals = [
            'opening_debit' => 0, 'opening_credit' => 0,
            'period_debit' => 0, 'period_credit' => 0,
            'closing_debit' => 0, 'closing_credit' => 0,
        ];
        foreach ($rows as &$row) {
            $row['opening_debit'] = max(0, $row['opening_net']);
            $row['opening_credit'] = max(0, -$row['opening_net']);
            $row['closing_debit'] = max(0, $row['closing_net']);
            $row['closing_credit'] = max(0, -$row['closing_net']);
            foreach (array_keys($totals) as $key) {
                $totals[$key] += $row[$key];
            }
        }
        unset($row);

        $this->render('reports/trial', [
            'title'   => 'تراز آزمایشی',
            'rows'    => $rows,
            'totals'  => $totals,
            'from'    => $from,
            'to'      => $to,
            'display' => $display,
        ]);
    }

    /**
     * بازه تاریخ از ورودی شمسی (GET) یا پیش‌فرض: از ابتدای سال شمسی تا امروز.
     * @return array{0:string,1:string,2:array{from:string,to:string}}
     */
    private function range(): array
    {
        $today = Jalali::today();
        $from = Jalali::parse($this->queryString('from', 30)) ?? Jalali::yearStart($today);
        $to = Jalali::parse($this->queryString('to', 30)) ?? $today;

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to, [
            'from' => jdate($from),
            'to'   => jdate($to),
        ]];
    }
}
