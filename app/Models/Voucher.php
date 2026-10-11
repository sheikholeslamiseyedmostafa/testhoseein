<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Db;
use PDOException;
use Throwable;

/** اسناد حسابداری (سند + ردیف‌های بدهکار/بستانکار) */
final class Voucher
{
    /**
     * @param array{from?:string,to?:string,q?:string} $filters تاریخ‌ها میلادی Y-m-d
     */
    public static function list(array $filters = [], int $limit = 300): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['from'])) {
            $where[] = 'v.voucher_date >= ?';
            $params[] = $filters['from'];
        }
        if (!empty($filters['to'])) {
            $where[] = 'v.voucher_date <= ?';
            $params[] = $filters['to'];
        }
        if (!empty($filters['q'])) {
            $where[] = 'v.description LIKE ?';
            $params[] = '%' . $filters['q'] . '%';
        }

        $sql = 'SELECT v.id, v.voucher_no, v.voucher_date, v.description,
                    COALESCE(SUM(l.debit_cents), 0) AS total_cents,
                    COUNT(l.id) AS line_count
                FROM vouchers v
                LEFT JOIN voucher_lines l ON l.voucher_id = v.id';
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        // LIMIT با عدد ثابت (بدون پارامتر) تا با هر درایور سازگار باشد
        $sql .= ' GROUP BY v.id, v.voucher_no, v.voucher_date, v.description
                  ORDER BY v.voucher_date DESC, v.voucher_no DESC LIMIT ' . max(1, $limit);

        return array_map([self::class, 'normalize'], Db::all($sql, $params));
    }

    public static function recent(int $limit = 8): array
    {
        return self::list([], $limit);
    }

    public static function find(int $id): ?array
    {
        $voucher = Db::one('SELECT * FROM vouchers WHERE id = ?', [$id]);
        if ($voucher === null) {
            return null;
        }

        $voucher = self::normalize($voucher);
        $voucher['lines'] = array_map(
            static function (array $row): array {
                $row['account_id'] = (int) $row['account_id'];
                $row['debit_cents'] = (int) $row['debit_cents'];
                $row['credit_cents'] = (int) $row['credit_cents'];
                return $row;
            },
            Db::all(
                'SELECT l.id, l.account_id, l.description, l.debit_cents, l.credit_cents,
                        a.code AS account_code, a.name AS account_name
                 FROM voucher_lines l
                 JOIN accounts a ON a.id = l.account_id
                 WHERE l.voucher_id = ?
                 ORDER BY l.line_no, l.id',
                [$id]
            )
        );
        $voucher['total_debit'] = array_sum(array_column($voucher['lines'], 'debit_cents'));
        $voucher['total_credit'] = array_sum(array_column($voucher['lines'], 'credit_cents'));

        return $voucher;
    }

    /**
     * ایجاد یا ویرایش سند. ردیف‌ها قبلاً اعتبارسنجی شده‌اند.
     * @param list<array{account_id:int,description:string,debit_cents:int,credit_cents:int}> $lines
     */
    public static function save(?int $id, string $date, string $description, array $lines, int $userId): int
    {
        if ($id === null) {
            return self::insert($date, $description, $lines, $userId);
        }

        return Db::transaction(static function () use ($id, $date, $description, $lines): int {
            Db::run(
                'UPDATE vouchers SET voucher_date = ?, description = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?',
                [$date, $description, $id]
            );
            Db::run('DELETE FROM voucher_lines WHERE voucher_id = ?', [$id]);
            self::insertLines($id, $lines);
            return $id;
        });
    }

    public static function delete(int $id): void
    {
        Db::transaction(static function () use ($id): void {
            Db::run('DELETE FROM voucher_lines WHERE voucher_id = ?', [$id]);
            Db::run('DELETE FROM vouchers WHERE id = ?', [$id]);
        });
    }

    private static function insert(string $date, string $description, array $lines, int $userId): int
    {
        // شماره سند پشت سر هم است؛ در صورت برخورد همزمان دوباره تلاش می‌کنیم
        for ($attempt = 1; ; $attempt++) {
            try {
                return Db::transaction(static function () use ($date, $description, $lines, $userId): int {
                    $no = (int) Db::value('SELECT COALESCE(MAX(voucher_no), 0) + 1 FROM vouchers');
                    Db::run(
                        'INSERT INTO vouchers (voucher_no, voucher_date, description, created_by) VALUES (?, ?, ?, ?)',
                        [$no, $date, $description, $userId]
                    );
                    $id = Db::lastId();
                    self::insertLines($id, $lines);
                    return $id;
                });
            } catch (PDOException $e) {
                if ($attempt >= 3 || !str_contains((string) $e->getCode(), '23')) {
                    throw $e;
                }
            }
        }
    }

    private static function insertLines(int $voucherId, array $lines): void
    {
        foreach ($lines as $index => $line) {
            Db::run(
                'INSERT INTO voucher_lines (voucher_id, account_id, line_no, description, debit_cents, credit_cents)
                 VALUES (?, ?, ?, ?, ?, ?)',
                [
                    $voucherId,
                    $line['account_id'],
                    $index + 1,
                    $line['description'],
                    $line['debit_cents'],
                    $line['credit_cents'],
                ]
            );
        }
    }

    private static function normalize(array $row): array
    {
        $row['id'] = (int) $row['id'];
        $row['voucher_no'] = (int) $row['voucher_no'];
        if (array_key_exists('total_cents', $row)) {
            $row['total_cents'] = (int) $row['total_cents'];
        }
        if (array_key_exists('line_count', $row)) {
            $row['line_count'] = (int) $row['line_count'];
        }
        return $row;
    }
}
