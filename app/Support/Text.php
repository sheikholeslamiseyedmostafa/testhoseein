<?php
declare(strict_types=1);

namespace App\Support;

final class Text
{
    private const PERSIAN_DIGITS = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    private const ARABIC_DIGITS = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    private const ASCII_DIGITS = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    /** تبدیل ارقام فارسی/عربی به لاتین (برای پردازش ورودی کاربر) */
    public static function toEnglishDigits(string $value): string
    {
        return str_replace(
            self::PERSIAN_DIGITS,
            self::ASCII_DIGITS,
            str_replace(self::ARABIC_DIGITS, self::ASCII_DIGITS, $value)
        );
    }

    /** تبدیل ارقام لاتین به فارسی (در صورت فعال بودن تنظیم persian_digits) */
    public static function fa(string $value): string
    {
        if (!config('persian_digits', true)) {
            return $value;
        }
        return str_replace(self::ASCII_DIGITS, self::PERSIAN_DIGITS, $value);
    }
}
