<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Account;
use App\Models\Report;
use App\Models\Voucher;
use App\Support\Jalali;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $today = Jalali::today();
        $from = Jalali::yearStart($today);
        $to = Jalali::yearEnd($today);
        $pl = Report::profitAndLoss($from, $to);
        $counts = Report::counts();

        $this->render('dashboard/index', [
            'title'    => 'داشبورد',
            'pl'       => $pl,
            'counts'   => $counts,
            'from'     => $from,
            'to'       => $to,
            'today'    => $today,
            'recent'   => Voucher::recent(8),
            'accounts' => count(Account::activeOptions()),
        ]);
    }
}
