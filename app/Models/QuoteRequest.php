<?php
namespace App\Models;

use App\Core\Database as DB;

class QuoteRequest
{
    public static function create(array $data): int
    {
        return DB::insert('quote_requests', $data + ['status' => 'new', 'created_at' => date('Y-m-d H:i:s')]);
    }

    public static function all(?string $status = null): array
    {
        if ($status) {
            return DB::all('SELECT * FROM quote_requests WHERE status = ? ORDER BY created_at DESC', [$status]);
        }
        return DB::all('SELECT * FROM quote_requests ORDER BY created_at DESC LIMIT 1000');
    }

    public static function find(int $id): ?array
    {
        return DB::one('SELECT * FROM quote_requests WHERE id = ?', [$id]);
    }

    public static function countNew(): int
    {
        return (int) DB::value('SELECT COUNT(*) FROM quote_requests WHERE status = "new"');
    }

    public static function recentlyFrom(string $ip, int $seconds = 60): int
    {
        return (int) DB::value(
            'SELECT COUNT(*) FROM quote_requests WHERE ip = ? AND created_at > (NOW() - INTERVAL ? SECOND)',
            [$ip, $seconds]
        );
    }
}
