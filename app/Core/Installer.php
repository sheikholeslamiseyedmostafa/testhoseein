<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * نصب اولیه: ساخت جداول، چارت حساب پیش‌فرض و کاربر مدیر.
 * با اجرای دوباره، داده‌های موجود حذف نمی‌شوند.
 */
final class Installer
{
    /** @return array{statements:int,seeded:int,admin_created:bool,admin_password:?string} */
    public static function run(PDO $pdo, string $schemaSql, ?string $adminPassword = null): array
    {
        $statements = self::splitStatements($schemaSql);
        foreach ($statements as $statement) {
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                // ایندکس تکراری در اجرای مجدد نصب طبیعی است
                if (stripos($statement, 'CREATE INDEX') === 0 && self::isAlreadyExists($e)) {
                    continue;
                }
                throw $e;
            }
        }

        $seeded = 0;
        if ((int) $pdo->query('SELECT COUNT(*) FROM accounts')->fetchColumn() === 0) {
            $seeded = self::seedChart($pdo);
        }

        $adminCreated = false;
        $generated = null;
        if ((int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0) {
            $password = $adminPassword ?: self::generatePassword();
            if (mb_strlen($password) < 8) {
                throw new RuntimeException('رمز عبور مدیر باید حداقل ۸ کاراکتر باشد.');
            }
            $stmt = $pdo->prepare(
                'INSERT INTO users (username, full_name, password_hash, role, is_active) VALUES (?, ?, ?, ?, 1)'
            );
            $stmt->execute(['admin', 'مدیر سیستم', password_hash($password, PASSWORD_DEFAULT), Auth::ROLE_ADMIN]);
            $adminCreated = true;
            $generated = $adminPassword ? null : $password;
        }

        return [
            'statements'     => count($statements),
            'seeded'         => $seeded,
            'admin_created'  => $adminCreated,
            'admin_password' => $generated,
        ];
    }

    /** چارت حساب استاندارد (نمونه؛ هر زمان می‌توانید از بخش چارت حساب‌ها ویرایش کنید) */
    public static function seedChart(PDO $pdo): int
    {
        $accounts = [
            ['1101', 'صندوق', 'asset'],
            ['1102', 'بانک', 'asset'],
            ['1201', 'حساب‌های دریافتنی', 'asset'],
            ['1301', 'موجودی کالا', 'asset'],
            ['1401', 'دارایی‌های ثابت', 'asset'],
            ['2101', 'حساب‌های پرداختنی', 'liability'],
            ['2201', 'مالیات بر ارزش افزوده پرداختنی', 'liability'],
            ['2301', 'وام بانکی', 'liability'],
            ['3101', 'سرمایه', 'equity'],
            ['3201', 'سود (زیان) انباشته', 'equity'],
            ['4101', 'فروش', 'revenue'],
            ['4201', 'درآمد خدمات', 'revenue'],
            ['5101', 'بهای تمام‌شده کالای فروش‌رفته', 'expense'],
            ['5201', 'حقوق و دستمزد', 'expense'],
            ['5202', 'اجاره', 'expense'],
            ['5203', 'هزینه‌های اداری', 'expense'],
            ['5204', 'هزینه حمل و نقل', 'expense'],
            ['5205', 'هزینه استهلاک', 'expense'],
        ];

        $stmt = $pdo->prepare('INSERT INTO accounts (code, name, type, is_active) VALUES (?, ?, ?, 1)');
        foreach ($accounts as [$code, $name, $type]) {
            $stmt->execute([$code, $name, $type]);
        }
        return count($accounts);
    }

    /** حذف توضیحات خطی و تقسیم اسکریپت به دستورهای مجزا (بدون ; داخل رشته‌ها) */
    public static function splitStatements(string $sql): array
    {
        // نکته: از \R استفاده نمی‌کنیم؛ در حالت بایتی، \R بایت 0x85 را هم می‌گیرد که جزئی از حروف فارسی (مثلاً «م») است
        $lines = array_filter(
            preg_split('/\r\n|\r|\n/', $sql) ?: [],
            static fn (string $line): bool => !str_starts_with(ltrim($line), '--')
        );
        $sql = implode("\n", $lines);

        $statements = [];
        foreach (explode(';', $sql) as $part) {
            $part = trim($part);
            if ($part !== '') {
                $statements[] = $part;
            }
        }
        return $statements;
    }

    private static function isAlreadyExists(PDOException $e): bool
    {
        $message = strtolower($e->getMessage());
        return str_contains($message, 'duplicate key name')
            || str_contains($message, 'already exists')
            || str_contains($message, 'duplicate');
    }

    private static function generatePassword(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(12)), '+/', '-_'), '=');
    }
}
