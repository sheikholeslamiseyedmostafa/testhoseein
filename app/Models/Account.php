<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

/** حساب‌های معین (چارت حساب‌ها) */
final class Account
{
    public const TYPES = [
        'asset'     => 'دارایی',
        'liability' => 'بدهی',
        'equity'    => 'سرمایه',
        'revenue'   => 'درآمد',
        'expense'   => 'هزینه',
    ];

    /** @return list<array> همه حساب‌ها همراه تعداد ردیف‌های سند */
    public static function all(string $search = ''): array
    {
        $sql = 'SELECT a.id, a.code, a.name, a.type, a.is_active,
                    (SELECT COUNT(*) FROM voucher_lines l WHERE l.account_id = a.id) AS usage_count
                FROM accounts a';
        $params = [];
        if ($search !== '') {
            $sql .= ' WHERE a.code LIKE ? OR a.name LIKE ?';
            $params = ["%{$search}%", "%{$search}%"];
        }
        $sql .= ' ORDER BY a.code';

        return array_map([self::class, 'normalize'], Db::all($sql, $params));
    }

    /** حساب‌های فعال برای انتخاب در سند */
    public static function activeOptions(): array
    {
        return array_map([self::class, 'normalize'], Db::all(
            'SELECT id, code, name, type FROM accounts WHERE is_active = 1 ORDER BY code'
        ));
    }

    /** همه حساب‌ها (برای گزارش دفتر کل) */
    public static function options(): array
    {
        return array_map([self::class, 'normalize'], Db::all(
            'SELECT id, code, name, type, is_active FROM accounts ORDER BY code'
        ));
    }

    public static function find(int $id): ?array
    {
        $row = Db::one('SELECT * FROM accounts WHERE id = ?', [$id]);
        return $row === null ? null : self::normalize($row);
    }

    public static function codeExists(string $code, ?int $exceptId = null): bool
    {
        $row = Db::one('SELECT id FROM accounts WHERE code = ?', [$code]);
        return $row !== null && (int) $row['id'] !== $exceptId;
    }

    public static function create(string $code, string $name, string $type): int
    {
        Db::run(
            'INSERT INTO accounts (code, name, type, is_active) VALUES (?, ?, ?, 1)',
            [$code, $name, $type]
        );
        return Db::lastId();
    }

    public static function update(int $id, string $code, string $name, string $type): void
    {
        Db::run('UPDATE accounts SET code = ?, name = ?, type = ? WHERE id = ?', [$code, $name, $type, $id]);
    }

    public static function setActive(int $id, bool $active): void
    {
        Db::run('UPDATE accounts SET is_active = ? WHERE id = ?', [$active ? 1 : 0, $id]);
    }

    public static function usageCount(int $id): int
    {
        return (int) Db::value('SELECT COUNT(*) FROM voucher_lines WHERE account_id = ?', [$id]);
    }

    public static function delete(int $id): void
    {
        Db::run('DELETE FROM accounts WHERE id = ?', [$id]);
    }

    public static function normalize(array $row): array
    {
        $row['id'] = (int) $row['id'];
        if (array_key_exists('is_active', $row)) {
            $row['is_active'] = (int) $row['is_active'];
        }
        if (array_key_exists('usage_count', $row)) {
            $row['usage_count'] = (int) $row['usage_count'];
        }
        return $row;
    }
}
