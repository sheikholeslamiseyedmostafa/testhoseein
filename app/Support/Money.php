<?php
declare(strict_types=1);

namespace App\Support;

/**
 * مبالغ در پایگاه داده به صورت عدد صحیح «سنت» (ضرب در ۱۰۰) ذخیره می‌شوند
 * تا از خطای اعشار شناور جلوگیری شود. مثال: 1,500.25 => 150025
 */
final class Money
{
    private const MAX_DIGITS = 13;

    /**
     * تبدیل ورودی کاربر به سنت.
     * خالی => 0 ، نامعتبر => null
     */
    public static function parse(?string $input): ?int
    {
        $value = Text::toEnglishDigits(trim((string) $input));
        $value = str_replace(['٬', ',', ' ', "\u{00A0}", "\u{200C}"], '', $value);
        $value = str_replace('٫', '.', $value);

        if ($value === '') {
            return 0;
        }
        if (!preg_match('/^\d{1,' . self::MAX_DIGITS . '}(\.\d{1,2})?$/', $value)) {
            return null;
        }

        [$integer, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        $fraction = str_pad($fraction, 2, '0');

        return ((int) $integer) * 100 + (int) $fraction;
    }

    /** نمایش مبلغ؛ اعشار فقط وقتی نشان داده می‌شود که مخالف صفر باشد (مگر forceDecimals) */
    public static function format(int $cents, bool $forceDecimals = false): string
    {
        $abs = abs($cents);
        $integer = intdiv($abs, 100);
        $fraction = $abs % 100;

        $text = number_format($integer);
        if ($fraction !== 0 || $forceDecimals) {
            $text .= '.' . str_pad((string) $fraction, 2, '0', STR_PAD_LEFT);
        }
        if ($cents < 0) {
            $text = '-' . $text;
        }
        return Text::fa($text);
    }

    /** مقدار قابل ویرایش در فرم (بدون جداکننده) */
    public static function toInput(int $cents): string
    {
        if ($cents === 0) {
            return '';
        }
        $integer = intdiv($cents, 100);
        $fraction = $cents % 100;
        return $fraction === 0 ? (string) $integer : sprintf('%d.%02d', $integer, $fraction);
    }
}
