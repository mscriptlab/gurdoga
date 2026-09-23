<?php
namespace App\Core;

class Settings
{
    private static array $data = [];
    private static array $trans = []; // langId => [key => value]
    private static bool $loaded = false;

    public static function boot(): void
    {
        self::$data = [];
        foreach (Database::all('SELECT `key`, `value` FROM settings') as $r) {
            self::$data[$r['key']] = $r['value'];
        }
        foreach (Database::all('SELECT language_id, `key`, `value` FROM setting_translations') as $r) {
            self::$trans[(int) $r['language_id']][$r['key']] = $r['value'];
        }
        self::$loaded = true;
    }

    public static function get(string $key, $default = null)
    {
        $langId = Lang::id();
        if (isset(self::$trans[$langId][$key]) && self::$trans[$langId][$key] !== '') {
            return self::$trans[$langId][$key];
        }
        return self::$data[$key] ?? $default;
    }

    public static function raw(string $key, $default = null)
    {
        return self::$data[$key] ?? $default;
    }

    public static function set(string $key, $value): void
    {
        if (Database::value('SELECT 1 FROM settings WHERE `key` = ?', [$key])) {
            Database::update('settings', ['value' => $value], '`key` = :k', ['k' => $key]);
        } else {
            Database::insert('settings', ['key' => $key, 'value' => $value]);
        }
        self::$data[$key] = $value;
    }

    public static function setTranslation(string $key, int $langId, $value): void
    {
        $exists = Database::value('SELECT 1 FROM setting_translations WHERE `key` = ? AND language_id = ?', [$key, $langId]);
        if ($exists) {
            Database::update('setting_translations', ['value' => $value], '`key` = :k AND language_id = :l', ['k' => $key, 'l' => $langId]);
        } else {
            Database::insert('setting_translations', ['key' => $key, 'language_id' => $langId, 'value' => $value]);
        }
        self::$trans[$langId][$key] = $value;
    }

    public static function all(): array { return self::$data; }
}
