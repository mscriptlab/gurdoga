<?php
namespace App\Models;

use App\Core\Database as DB;

class User
{
    public static function all(): array
    {
        return DB::all('SELECT id, name, email, role, is_active, last_login, created_at FROM users ORDER BY id');
    }

    public static function find(int $id): ?array
    {
        return DB::one('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public static function emailExists(string $email, int $exceptId = 0): bool
    {
        return (bool) DB::value('SELECT 1 FROM users WHERE email = ? AND id <> ?', [$email, $exceptId]);
    }
}
