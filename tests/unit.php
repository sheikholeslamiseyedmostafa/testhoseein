<?php
/**
 * تست‌های واحد ساده (بدون وابستگی خارجی).
 *   php tests/unit.php
 */
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
$GLOBALS['app_config'] = ['persian_digits' => true, 'timezone' => 'Asia/Tehran'];
require APP_ROOT . '/app/helpers.php';
spl_autoload_register(static function (string $class): void {
    if (strncmp($class, 'App\\', 4) === 0) {
        $file = APP_ROOT . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});

use App\Core\ConfigWriter;
use App\Core\Installer;
use App\Support\Jalali;
use App\Support\Money;
use App\Support\Text;

$passed = 0;
$failed = 0;

function check(string $name, bool $condition, string $detail = ''): void
{
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  ok   {$name}\n";
    } else {
        $failed++;
        echo "  FAIL {$name}" . ($detail !== '' ? " ({$detail})" : '') . "\n";
    }
}

echo "Jalali\n";
check('2024-03-20 = 1403/01/01', Jalali::format('2024-03-20') === '1403/01/01');
check('2026-10-11 = 1405/07/19', Jalali::format('2026-10-11') === '1405/07/19');
check('1403/01/01 => 2024-03-20', Jalali::toGregorian(1403, 1, 1) === '2024-03-20');
check('invalid day 1405/07/31 rejected', Jalali::toGregorian(1405, 7, 31) === null);
check('invalid month 1405/13/01 rejected', Jalali::toGregorian(1405, 13, 1) === null);
check('persian input parses', Jalali::parse('۱۴۰۵/۰۷/۱۹') === '2026-10-11');
check('dash separator parses', Jalali::parse('1405-7-19') === '2026-10-11');
check('garbage input rejected', Jalali::parse('abc') === null);
check('year start of 1405', Jalali::yearStart('2026-10-11') === '2026-03-21');
check('year end of 1405 (non-leap, 1405/12/29)', Jalali::yearEnd('2026-10-11') === '2027-03-20');
$roundtripBad = 0;
for ($d = 0; $d < 3650; $d++) {
    $ymd = date('Y-m-d', strtotime('2000-01-01') + $d * 86400);
    [$jy, $jm, $jd] = Jalali::fromGregorian($ymd);
    if (Jalali::toGregorian($jy, $jm, $jd) !== $ymd) {
        $roundtripBad++;
    }
}
check('round-trip over 10 years', $roundtripBad === 0, "mismatches: {$roundtripBad}");

echo "Money\n";
check('parse integer', Money::parse('1000') === 100000);
check('parse with separators', Money::parse('1,000,000') === 100000000);
check('parse persian separators', Money::parse('۱٬۰۰۰٫۵') === 100050);
check('parse two decimals', Money::parse('250.5') === 25050);
check('parse empty is zero', Money::parse('') === 0);
check('reject three decimals', Money::parse('1.234') === null);
check('reject letters', Money::parse('12a') === null);
check('reject negative', Money::parse('-5') === null);
check('format grouping (persian digits on)', Money::format(99970000) === '۹۹۹,۷۰۰');
check('format decimals when non-zero', Money::format(25050) === '۲۵۰.۵۰');
check('format persian digits', Money::format(100000000) === '۱,۰۰۰,۰۰۰');
check('toInput', Money::toInput(25050) === '250.50' && Money::toInput(100000) === '1000' && Money::toInput(0) === '');

echo "Text\n";
check('toEnglishDigits from persian', Text::toEnglishDigits('۱۴۰۵') === '1405');
check('toEnglishDigits from arabic', Text::toEnglishDigits('١٤٠٥') === '1405');

echo "Installer\n";
$sql = "-- نرم‌افزار حسابداری - توضیح فارسی؛ با نقطه‌ویرگول\n"
    . "CREATE TABLE IF NOT EXISTS t (id INT NOT NULL, title VARCHAR(20) DEFAULT 'م');\n"
    . "-- ایندکس‌ها (توضیح)\n"
    . "CREATE INDEX idx_t ON t (id);\n";
$statements = Installer::splitStatements($sql);
check('persian comments do not break statements', count($statements) === 2, (string) count($statements));
check('persian letter with byte 0x85 kept intact', str_contains($statements[0], "'م'"));

echo "ConfigWriter\n";
$original = [
    'app_name' => 'حسابداری "تست" \'ویژه\'',
    'timezone' => 'Asia/Tehran',
    'persian_digits' => true,
    'debug' => false,
    'session_lifetime' => 30,
    'db' => ['driver' => 'mysql', 'host' => 'localhost', 'port' => 3306, 'name' => 'acc', 'user' => 'u', 'password' => "p\$w'o\\rd", 'charset' => 'utf8mb4'],
];
$rendered = ConfigWriter::render($original);
check('rendered config starts with php tag', str_starts_with($rendered, "<?php\n"));
$tmpConfig = sys_get_temp_dir() . '/acc-config-test-' . getmypid() . '.php';
ConfigWriter::write($tmpConfig, $rendered);
$loaded = require $tmpConfig;
@unlink($tmpConfig);
check('written config loads back identically', $loaded === $original, var_export($loaded, true));
check('no temp file left behind', !is_file($tmpConfig . '.tmp'));

echo "\n{$passed} passed, {$failed} failed\n";
exit($failed > 0 ? 1 : 0);
