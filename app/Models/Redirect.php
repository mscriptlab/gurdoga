<?php
namespace App\Models;

use App\Core\Database as DB;

class Redirect
{
    public static function all(): array
    {
        return DB::all('SELECT * FROM redirects ORDER BY id DESC');
    }

    /** Record a 301 when a slug changes; avoids loops and duplicates. */
    public static function record(string $from, string $to): void
    {
        $from = '/' . ltrim(trim($from), '/');
        $to   = '/' . ltrim(trim($to), '/');
        if ($from === $to || $from === '/' ) {
            return;
        }
        // repoint any existing redirects that pointed at the old path
        DB::query('UPDATE redirects SET to_path = ? WHERE to_path = ?', [$to, $from]);
        if (DB::value('SELECT 1 FROM redirects WHERE from_path = ?', [$from])) {
            DB::query('UPDATE redirects SET to_path = ?, code = 301 WHERE from_path = ?', [$to, $from]);
        } else {
            DB::insert('redirects', ['from_path' => $from, 'to_path' => $to, 'code' => 301, 'hits' => 0]);
        }
        DB::query('DELETE FROM redirects WHERE from_path = to_path', []);
    }

    public static function delete(int $id): void
    {
        DB::delete('redirects', 'id = :id', ['id' => $id]);
    }
}
