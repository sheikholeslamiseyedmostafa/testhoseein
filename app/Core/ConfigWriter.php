<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * ساخت فایل config/config.php از روی آرایه‌ی تنظیمات (برای صفحه‌ی نصب تحت وب).
 * خروجی، PHP معتبری است که با require همان آرایه را برمی‌گرداند.
 */
final class ConfigWriter
{
    public static function render(array $config): string
    {
        return "<?php\n"
            . "/**\n"
            . " * تنظیمات برنامه - تولید شده توسط صفحه نصب در " . date('Y-m-d H:i') . "\n"
            . " * این فایل رمز پایگاه داده را دارد؛ آن را در گیت یا پشتیبان عمومی قرار ندهید.\n"
            . " */\n\n"
            . 'return ' . self::export($config, 0) . ";\n";
    }

    public static function write(string $path, string $content): void
    {
        $tmp = $path . '.tmp';
        if (file_put_contents($tmp, $content, LOCK_EX) === false || !rename($tmp, $path)) {
            @unlink($tmp);
            throw new RuntimeException(
                'نوشتن فایل تنظیمات ممکن نیست. دسترسی نوشتن را به کاربر IIS روی پوشه config بدهید.'
            );
        }
    }

    private static function export(mixed $value, int $level): string
    {
        if (is_array($value)) {
            if ($value === []) {
                return '[]';
            }
            $pad = str_repeat('    ', $level + 1);
            $lines = [];
            foreach ($value as $key => $item) {
                $prefix = is_int($key) ? '' : var_export((string) $key, true) . ' => ';
                $lines[] = $pad . $prefix . self::export($item, $level + 1) . ',';
            }
            return "[\n" . implode("\n", $lines) . "\n" . str_repeat('    ', $level) . ']';
        }

        return var_export($value, true);
    }
}
