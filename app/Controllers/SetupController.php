<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\ConfigWriter;
use App\Core\Controller;
use App\Core\Db;
use App\Core\Installation;
use App\Core\Installer;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

/**
 * صفحه‌ی نصب تحت وب.
 * - فقط تا وقتی config/config.php نباشد در دسترس است؛ بعد از نصب، 404 می‌دهد.
 * - کد نصب لازم نیست؛ بعد از ساخت config.php، این صفحه غیرفعال می‌شود.
 *   اگر پایگاه داده از قبل کاربر داشته باشد، نصب دوباره انجام نمی‌شود.
 */
final class SetupController extends Controller
{
    private const DB_NAME_PATTERN = '/^[A-Za-z0-9_]{1,64}$/';
    private const USERNAME_PATTERN = '/^[A-Za-z0-9_.\-]{3,50}$/';

    public function form(): void
    {
        $this->guardNotInstalled();
        $errors = [];
        if (($problem = self::sessionProblem()) !== null) {
            $errors[] = $problem;
        }
        $this->render('setup/form', [
            'title'  => 'نصب برنامه',
            'values' => self::defaults(),
            'errors' => $errors,
            'manualConfig' => null,
        ]);
    }

    public function store(): void
    {
        $this->guardNotInstalled();

        $values = $this->collectValues();
        $errors = $this->validate($values);
        $manualConfig = null;

        $db = [
            'driver'   => 'mysql',
            'host'     => $values['db_host'],
            'port'     => (int) $values['db_port'],
            'name'     => $values['db_name'],
            'user'     => $values['db_user'],
            'password' => $values['db_password'],
            'charset'  => 'utf8mb4',
        ];

        $pdo = null;
        if ($errors === []) {
            try {
                $pdo = Db::connect($db);
            } catch (PDOException $e) {
                $errors[] = 'اتصال به پایگاه داده برقرار نشد: ' . $e->getMessage();
            }
        }

        if ($pdo !== null && $this->hasExistingUsers($pdo)) {
            $errors[] = 'این پایگاه داده قبلاً نصب شده است (کاربر وجود دارد). برای جلوگیری از بازنویسی، نصب متوقف شد. '
                . 'اگر فایل config/config.php را ندارید، آن را به‌صورت دستی بسازید.';
        }

        if ($pdo !== null && $errors === []) {
            try {
                Installer::run(
                    $pdo,
                    (string) file_get_contents(APP_ROOT . '/db/schema.sql'),
                    $values['admin_password'],
                    $values['admin_username'],
                    $values['admin_name']
                );
            } catch (Throwable $e) {
                error_log('[setup] ' . $e::class . ': ' . $e->getMessage());
                $errors[] = 'ساخت جداول پایگاه داده ناموفق بود: ' . $e->getMessage();
            }
        }

        if ($pdo !== null && $errors === []) {
            $config = [
                'app_name'         => $values['app_name'],
                'timezone'         => 'Asia/Tehran',
                'persian_digits'   => true,
                'debug'            => false,
                'session_lifetime' => 30,
                'asset_version'    => '1',
                'db'               => $db,
            ];
            $content = ConfigWriter::render($config);
            try {
                ConfigWriter::write(APP_ROOT . '/config/config.php', $content);
            } catch (RuntimeException $e) {
                // جدول‌ها ساخته شده‌اند؛ محتوای تنظیمات را نشان می‌دهیم تا دستی ذخیره شود
                $errors[] = $e->getMessage() . ' پایگاه داده نصب شد؛ محتوای زیر را در config/config.php ذخیره کنید.';
                $manualConfig = $content;
            }
        }

        if ($errors !== []) {
            http_response_code(422);
            $this->render('setup/form', [
                'title'        => 'نصب برنامه',
                'values'       => $this->safeValues($values),
                'errors'       => $errors,
                'manualConfig' => $manualConfig,
            ]);
            return;
        }

        flash('success', 'نصب با موفقیت انجام شد. با نام کاربری و رمز مدیر وارد شوید.');
        redirect('login');
    }

    /**
     * وقتی توکن امنیتی فرم با نشست یکی نیست (خطای 419)، دلیل احتمالی را روی صفحه‌ی نصب نشان می‌دهد.
     * این صفحه قبل از نصب اجرا می‌شود و هیچ چیزی در پایگاه داده نوشته نشده است.
     */
    public static function csrfFailed(): never
    {
        http_response_code(419);
        if (!isset($_COOKIE['ACCTSESSID'])) {
            $reason = 'مرورگر کوکی نشست را ارسال نکرد. کوکی‌ها را فعال کنید، صفحه‌ی نصب را دوباره باز کنید و فرم را از همان صفحه بفرستید.';
        } elseif (empty($_SESSION['_csrf'])) {
            $reason = 'نشست روی سرور پیدا نشد. معمولاً پوشه‌ی session.save_path در php.ini قابل نوشتن نیست.';
        } else {
            $reason = 'کد امنیتی فرم با نشست فعلی یکسان نیست. صفحه را تازه‌سازی کنید و دوباره بفرستید.';
        }
        $errors = [$reason];
        if (($problem = self::sessionProblem()) !== null) {
            $errors[] = $problem;
        }
        view('setup/form', ['title' => 'نصب برنامه', 'values' => self::defaults(), 'errors' => $errors, 'manualConfig' => null]);
        exit;
    }

    /** بررسی قابل نوشتن بودن پوشه‌ی نشست PHP؛ اگر مشکل باشد متن راهنما برمی‌گرداند. */
    public static function sessionProblem(): ?string
    {
        $path = (string) session_save_path();
        if (str_contains($path, ';')) {
            $path = substr($path, strrpos($path, ';') + 1);
        }
        if ($path === '') {
            $path = sys_get_temp_dir();
        }
        if (!is_dir($path) || !is_writable($path)) {
            return 'پوشه‌ی نشست PHP قابل نوشتن نیست (' . $path . '). مقدار session.save_path را در php.ini به یک پوشه‌ی قابل نوشتن (مثلاً C:\\php\\sessions) تنظیم کنید و به کاربر IIS دسترسی نوشتن بدهید.';
        }
        return null;
    }

    private function guardNotInstalled(): void
    {
        if (Installation::isInstalled()) {
            $this->notFound();
        }
    }

    /** @return array<string,string> */
    private function collectValues(): array
    {
        return [
            'app_name'               => $this->postString('app_name', 100),
            'db_host'                => $this->postString('db_host', 255),
            'db_port'                => $this->postString('db_port', 5),
            'db_name'                => $this->postString('db_name', 64),
            'db_user'                => $this->postString('db_user', 64),
            'db_password'            => $this->rawPost('db_password', 200),
            'admin_username'         => $this->postString('admin_username', 50),
            'admin_name'             => $this->postString('admin_name', 150),
            'admin_password'         => $this->rawPost('admin_password', 200),
            'admin_password_confirm' => $this->rawPost('admin_password_confirm', 200),
        ];
    }

    private function rawPost(string $key, int $maxLength): string
    {
        $value = $_POST[$key] ?? '';
        return is_string($value) ? mb_substr($value, 0, $maxLength) : '';
    }

    /**
     * @param array<string,string> $v
     * @return list<string>
     */
    private function validate(array $v): array
    {
        $errors = [];

        if ($v['db_host'] === '') {
            $errors[] = 'نشانی سرور پایگاه داده را وارد کنید.';
        }
        if (!ctype_digit($v['db_port']) || (int) $v['db_port'] < 1 || (int) $v['db_port'] > 65535) {
            $errors[] = 'شماره پورت پایگاه داده باید بین ۱ تا ۶۵۵۳۵ باشد.';
        }
        if (preg_match(self::DB_NAME_PATTERN, $v['db_name']) !== 1) {
            $errors[] = 'نام پایگاه داده فقط می‌تواند شامل حروف انگلیسی، عدد و زیرخط باشد (حداکثر ۶۴ کاراکتر).';
        }
        if ($v['db_user'] === '') {
            $errors[] = 'نام کاربر پایگاه داده را وارد کنید.';
        }

        if ($v['app_name'] === '') {
            $errors[] = 'نام برنامه را وارد کنید.';
        }
        if (preg_match(self::USERNAME_PATTERN, $v['admin_username']) !== 1) {
            $errors[] = 'نام کاربری مدیر باید ۳ تا ۵۰ کاراکتر انگلیسی، عدد، نقطه، خط تیره یا زیرخط باشد.';
        }
        if ($v['admin_name'] === '') {
            $errors[] = 'نام و نام خانوادگی مدیر را وارد کنید.';
        }
        if (mb_strlen($v['admin_password']) < 8) {
            $errors[] = 'رمز عبور مدیر باید حداقل ۸ کاراکتر باشد.';
        } elseif (!hash_equals($v['admin_password'], $v['admin_password_confirm'])) {
            $errors[] = 'تکرار رمز عبور مدیر با رمز اصلی یکسان نیست.';
        }

        return $errors;
    }

    private function hasExistingUsers(PDO $pdo): bool
    {
        try {
            return (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0;
        } catch (PDOException) {
            // جدول users وجود ندارد: پایگاه داده خالی است
            return false;
        }
    }

    /** @return array<string,string> */
    private static function defaults(): array
    {
        return [
            'app_name'       => 'حسابداری',
            'db_host'        => 'localhost',
            'db_port'        => '3306',
            'db_name'        => 'accounting',
            'db_user'        => '',
            'admin_username' => 'admin',
            'admin_name'     => 'مدیر سیستم',
        ];
    }

    /**
     * مقادیر فرم برای نمایش دوباره؛ رمزها عمداً برنمی‌گردند.
     * @param array<string,string> $values
     * @return array<string,string>
     */
    private function safeValues(array $values): array
    {
        unset($values['db_password'], $values['admin_password'], $values['admin_password_confirm']);
        return $values;
    }
}
