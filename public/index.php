<?php
declare(strict_types=1);

// نقطه ورود برنامه (Front Controller). وب‌سرور باید ریشه سایت را روی این پوشه (public) تنظیم کند.
require __DIR__ . '/../app/bootstrap.php';

App\Core\Router::dispatch();
