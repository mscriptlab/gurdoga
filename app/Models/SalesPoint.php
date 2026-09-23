<?php
namespace App\Models;

use App\Core\Database as DB;

class SalesPoint
{
    public static function find(int $id): ?array
    {
        return DB::one('SELECT * FROM sales_points WHERE id = ?', [$id]);
    }

    public static function activeList(): array
    {
        return DB::all('SELECT * FROM sales_points WHERE is_active = 1 ORDER BY sort, id');
    }

    public static function adminList(): array
    {
        return DB::all('SELECT * FROM sales_points ORDER BY sort, id DESC');
    }
}
