<?php
namespace App\Models;

use App\Core\Database as DB;

class Language
{
    public static function all(): array
    {
        return DB::all('SELECT * FROM languages ORDER BY sort, id');
    }

    public static function find(int $id): ?array
    {
        return DB::one('SELECT * FROM languages WHERE id = ?', [$id]);
    }

    public static function active(): array
    {
        return DB::all('SELECT * FROM languages WHERE is_active = 1 ORDER BY sort, id');
    }
}
