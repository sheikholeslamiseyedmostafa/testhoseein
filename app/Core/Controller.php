<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function render(string $template, array $data = []): void
    {
        \view($template, $data);
    }

    /** مقدار رشته‌ای از فرم (آرایه یا غیررشته نادیده گرفته می‌شود) */
    protected function postString(string $key, int $maxLength = 255): string
    {
        $value = $_POST[$key] ?? '';
        return is_string($value) ? mb_substr(trim($value), 0, $maxLength) : '';
    }

    protected function queryString(string $key, int $maxLength = 255): string
    {
        $value = $_GET[$key] ?? '';
        return is_string($value) ? mb_substr(trim($value), 0, $maxLength) : '';
    }

    protected function queryId(): int
    {
        $value = $_GET['id'] ?? '';
        return is_string($value) && ctype_digit($value) ? (int) $value : 0;
    }

    protected function notFound(): never
    {
        Router::abort(404);
    }
}
