<?php
/** @var int $code @var string $message */
$code = (int) ($code ?? http_response_code() ?: 500);
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>خطا <?= e((string) $code) ?></title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="error-page">
<main class="container narrow">
    <div class="card">
        <h1>خطا <?= e((string) $code) ?></h1>
        <p><?= e($message ?? 'خطا') ?></p>
        <p><a class="btn btn-primary" href="index.php">بازگشت به صفحه اصلی</a></p>
    </div>
</main>
</body>
</html>
