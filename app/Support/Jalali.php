<?php
declare(strict_types=1);

namespace App\Support;

/**
 * تبدیل تاریخ شمسی (هجری خورشیدی) به میلادی و برعکس.
 * در پایگاه داده همه تاریخ‌ها میلادی (Y-m-d) ذخیره می‌شوند؛ شمسی فقط در نمایش و ورودی کاربر است.
 */
final class Jalali
{
    /** @return string|null تاریخ میلادی Y-m-d یا null اگر تاریخ شمسی معتبر نباشد */
    public static function toGregorian(int $jy, int $jm, int $jd): ?string
    {
        if ($jy < 1300 || $jy > 1500 || $jm < 1 || $jm > 12 || $jd < 1 || $jd > 31) {
            return null;
        }

        [$gy, $gm, $gd] = self::jalaliToGregorian($jy, $jm, $jd);

        // بررسی بازگشت: روزهای نامعتبر (مثلاً ۳۱ اسفند) به تاریخ دیگری تبدیل می‌شوند
        if (self::gregorianToJalali($gy, $gm, $gd) !== [$jy, $jm, $jd]) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
    }

    /** @return array{0:int,1:int,2:int} [سال، ماه، روز] شمسی */
    public static function fromGregorian(string $ymd): array
    {
        [$gy, $gm, $gd] = array_map('intval', explode('-', $ymd));
        return self::gregorianToJalali($gy, $gm, $gd);
    }

    /** ورودی کاربر مانند «۱۴۰۵/۰۷/۱۹» را به Y-m-d میلادی تبدیل می‌کند */
    public static function parse(string $input): ?string
    {
        $value = trim(Text::toEnglishDigits($input));
        if (!preg_match('#^(\d{4})[/\-.](\d{1,2})[/\-.](\d{1,2})$#', $value, $m)) {
            return null;
        }
        return self::toGregorian((int) $m[1], (int) $m[2], (int) $m[3]);
    }

    /** نمایش ASCII به صورت YYYY/MM/DD شمسی */
    public static function format(string $ymd): string
    {
        [$jy, $jm, $jd] = self::fromGregorian($ymd);
        return sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
    }

    public static function today(): string
    {
        return date('Y-m-d');
    }

    /** ابتدای سال مالی (سال شمسی جاری) به صورت میلادی */
    public static function yearStart(?string $ymd = null): string
    {
        [$jy] = self::fromGregorian($ymd ?? self::today());
        return self::toGregorian($jy, 1, 1);
    }

    /** آخرین روز سال شمسی به صورت میلادی (۲۹ یا ۳۰ اسفند) */
    public static function yearEnd(?string $ymd = null): string
    {
        [$jy] = self::fromGregorian($ymd ?? self::today());
        return self::toGregorian($jy, 12, 30) ?? self::toGregorian($jy, 12, 29);
    }

    private static function jalaliToGregorian(int $jy, int $jm, int $jd): array
    {
        $jy -= 979;
        $jm -= 1;
        $jd -= 1;

        $jDaysInMonth = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
        $jDayNo = 365 * $jy + intdiv($jy, 33) * 8 + intdiv(($jy % 33) + 3, 4);
        for ($i = 0; $i < $jm; $i++) {
            $jDayNo += $jDaysInMonth[$i];
        }
        $jDayNo += $jd;

        $gDayNo = $jDayNo + 79;
        $gy = 1600 + 400 * intdiv($gDayNo, 146097);
        $gDayNo %= 146097;

        $leap = true;
        if ($gDayNo >= 36525) {
            $gDayNo--;
            $gy += 100 * intdiv($gDayNo, 36524);
            $gDayNo %= 36524;
            if ($gDayNo >= 365) {
                $gDayNo++;
            } else {
                $leap = false;
            }
        }

        $gy += 4 * intdiv($gDayNo, 1461);
        $gDayNo %= 1461;

        if ($gDayNo >= 366) {
            $leap = false;
            $gDayNo--;
            $gy += intdiv($gDayNo, 365);
            $gDayNo %= 365;
        }

        $gDaysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        for ($i = 0; $gDayNo >= $gDaysInMonth[$i] + ($i === 1 && $leap ? 1 : 0); $i++) {
            $gDayNo -= $gDaysInMonth[$i] + ($i === 1 && $leap ? 1 : 0);
        }

        return [$gy, $i + 1, $gDayNo + 1];
    }

    private static function gregorianToJalali(int $gy, int $gm, int $gd): array
    {
        $gDaysBeforeMonth = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];

        if ($gy > 1600) {
            $jy = 979;
            $gy -= 1600;
        } else {
            $jy = 0;
            $gy -= 621;
        }

        $gy2 = $gm > 2 ? $gy + 1 : $gy;
        $days = 365 * $gy
            + intdiv($gy2 + 3, 4)
            - intdiv($gy2 + 99, 100)
            + intdiv($gy2 + 399, 400)
            - 80 + $gd + $gDaysBeforeMonth[$gm - 1];

        $jy += 33 * intdiv($days, 12053);
        $days %= 12053;
        $jy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $jy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        if ($days < 186) {
            $jm = 1 + intdiv($days, 31);
            $jd = 1 + ($days % 31);
        } else {
            $jm = 7 + intdiv($days - 186, 30);
            $jd = 1 + (($days - 186) % 30);
        }

        return [$jy, $jm, $jd];
    }
}
