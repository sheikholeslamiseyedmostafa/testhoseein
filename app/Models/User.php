<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\Db;

final class User
{
    public const ROLES = [
        Auth::ROLE_ADMIN      => 'مدیر سیستم',
        Auth::ROLE_ACCOUNTANT => 'حسابدار',
        Auth::ROLE_VIEWER     => 'مشاهده‌گر',
    ];

    public static function all(): array
    {
        return array_map([self::class, 'normalize'], Db::all(
            'SELECT id, username, full_name, role, is_active, created_at FROM users ORDER BY username'
        ));
    }

    public static function find(int $id): ?array
    {
        $row = Db::one('SELECT * FROM users WHERE id = ?', [$id]);
        return $row === null ? null : self::normalize($row);
    }

    public static function findByUsername(string $username): ?array
    {
        $row = Db::one('SELECT * FROM users WHERE username = ?', [$username]);
        return $row === null ? null : self::normalize($row);
    }

    public static function usernameExists(string $username, ?int $exceptId = null): bool
    {
        $row = Db::one('SELECT id FROM users WHERE username = ?', [$username]);
        return $row !== null && (int) $row['id'] !== $exceptId;
    }

    public static function create(string $username, string $fullName, string $passwordHash, string $role): int
    {
        Db::run(
            'INSERT INTO users (username, full_name, password_hash, role, is_active) VALUES (?, ?, ?, ?, 1)',
            [$username, $fullName, $passwordHash, $role]
        );
        return Db::lastId();
    }

    public static function update(int $id, string $fullName, string $role, bool $active): void
    {
        Db::run(
            'UPDATE users SET full_name = ?, role = ?, is_active = ? WHERE id = ?',
            [$fullName, $role, $active ? 1 : 0, $id]
        );
    }

    public static function setPassword(int $id, string $passwordHash): void
    {
        Db::run('UPDATE users SET password_hash = ? WHERE id = ?', [$passwordHash, $id]);
    }

    public static function activeAdminCount(): int
    {
        return (int) Db::value(
            'SELECT COUNT(*) FROM users WHERE role = ? AND is_active = 1',
            [Auth::ROLE_ADMIN]
        );
    }

    public static function normalize(array $row): array
    {
        $row['id'] = (int) $row['id'];
        $row['is_active'] = (int) $row['is_active'];
        return $row;
    }
}
