<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

/** اشخاص: مشتری، تأمین‌کننده و سایر طرف‌های حساب (اطلاعات پایه) */
final class Person
{
    public const TYPES = [
        'customer' => 'مشتری',
        'supplier' => 'تأمین‌کننده',
        'both'     => 'مشتری و تأمین‌کننده',
        'other'    => 'سایر',
    ];

    /** ستون‌های قابل ذخیره از فرم */
    private const FIELDS = ['code', 'name', 'person_type', 'national_id', 'economic_code', 'phone', 'mobile', 'email', 'address', 'notes'];

    /** @return list<array> */
    public static function all(string $search = '', string $type = ''): array
    {
        $where = [];
        $params = [];
        if ($search !== '') {
            $where[] = '(code LIKE ? OR name LIKE ? OR mobile LIKE ? OR phone LIKE ? OR national_id LIKE ?)';
            array_push($params, ...array_fill(0, 5, "%{$search}%"));
        }
        if ($type !== '' && array_key_exists($type, self::TYPES)) {
            $where[] = 'person_type = ?';
            $params[] = $type;
        }
        $sql = 'SELECT id, code, name, person_type, national_id, phone, mobile, is_active FROM persons'
            . ($where === [] ? '' : ' WHERE ' . implode(' AND ', $where))
            . ' ORDER BY code';

        return array_map([self::class, 'normalize'], Db::all($sql, $params));
    }

    public static function find(int $id): ?array
    {
        $row = Db::one('SELECT * FROM persons WHERE id = ?', [$id]);
        return $row === null ? null : self::normalize($row);
    }

    public static function codeExists(string $code, ?int $exceptId = null): bool
    {
        $row = Db::one('SELECT id FROM persons WHERE code = ?', [$code]);
        return $row !== null && (int) $row['id'] !== $exceptId;
    }

    /** کد پیشنهادی بعدی: بزرگ‌ترین کد عددی + ۱ (حداقل ۱۰۰۱) */
    public static function nextCode(): string
    {
        $max = 1000;
        foreach (Db::all('SELECT code FROM persons') as $row) {
            if (ctype_digit((string) $row['code'])) {
                $max = max($max, (int) $row['code']);
            }
        }
        return (string) ($max + 1);
    }

    /** @param array<string,string> $data */
    public static function create(array $data): int
    {
        $values = self::values($data);
        Db::run(
            'INSERT INTO persons (' . implode(', ', self::FIELDS) . ', is_active) VALUES ('
            . implode(', ', array_fill(0, count(self::FIELDS), '?')) . ', 1)',
            $values
        );
        return Db::lastId();
    }

    /** @param array<string,string> $data */
    public static function update(int $id, array $data): void
    {
        $assignments = implode(', ', array_map(static fn(string $f): string => "{$f} = ?", self::FIELDS));
        Db::run(
            "UPDATE persons SET {$assignments}, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
            [...self::values($data), $id]
        );
    }

    public static function setActive(int $id, bool $active): void
    {
        Db::run('UPDATE persons SET is_active = ? WHERE id = ?', [$active ? 1 : 0, $id]);
    }

    public static function countActive(): int
    {
        return (int) Db::value('SELECT COUNT(*) FROM persons WHERE is_active = 1');
    }

    /** کد ملی (۱۰ رقم) یا شناسه ملی شخص حقوقی (۱۱ رقم). خالی بودن مجاز است و جداگانه بررسی می‌شود. */
    public static function isValidNationalId(string $value): bool
    {
        return preg_match('/^(?:\d{10}|\d{11})$/', $value) === 1;
    }

    /** موبایل ایران: ۰۹ و ۹ رقم */
    public static function isValidMobile(string $value): bool
    {
        return preg_match('/^09\d{9}$/', $value) === 1;
    }

    /** تلفن ثابت: ارقام، فاصله، خط تیره، پرانتز و + */
    public static function isValidPhone(string $value): bool
    {
        return preg_match('/^\+?[0-9 \-()]{6,30}$/', $value) === 1;
    }

    /** @return list<string> */
    private static function values(array $data): array
    {
        return array_map(static fn(string $f): string => (string) ($data[$f] ?? ''), self::FIELDS);
    }

    public static function normalize(array $row): array
    {
        $row['id'] = (int) $row['id'];
        if (array_key_exists('is_active', $row)) {
            $row['is_active'] = (int) $row['is_active'];
        }
        return $row;
    }
}
