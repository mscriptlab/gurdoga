<?php
namespace App\Core;

class Lang
{
    /** @var array<int,array> all active languages */
    private static array $languages = [];
    private static array $default = [];
    private static array $current = [];
    private static array $strings = []; // key => value for current language

    public static function boot(): void
    {
        self::$languages = Database::all('SELECT * FROM languages WHERE is_active = 1 ORDER BY sort, id');
        if (!self::$languages) {
            // Fallback so the site never hard-crashes before seeding.
            self::$languages = [[
                'id' => 1, 'code' => 'en', 'name' => 'English', 'locale' => 'en_US',
                'is_default' => 1, 'is_active' => 1, 'sort' => 0,
            ]];
        }
        foreach (self::$languages as $l) {
            if (!empty($l['is_default'])) {
                self::$default = $l;
            }
        }
        if (!self::$default) {
            self::$default = self::$languages[0];
        }
        self::$current = self::$default;
    }

    public static function setCurrentByCode(string $code): bool
    {
        foreach (self::$languages as $l) {
            if ($l['code'] === $code) {
                self::$current = $l;
                self::loadStrings();
                return true;
            }
        }
        return false;
    }

    private static function loadStrings(): void
    {
        self::$strings = [];
        $rows = Database::all('SELECT `key`, `value` FROM translations WHERE language_id = ?', [self::$current['id']]);
        foreach ($rows as $r) {
            self::$strings[$r['key']] = $r['value'];
        }
    }

    public static function t(string $key, ?string $fallback = null): string
    {
        return self::$strings[$key] ?? ($fallback ?? $key);
    }

    public static function all(): array      { return self::$languages; }
    public static function current(): array  { return self::$current; }
    public static function default(): array  { return self::$default; }
    public static function code(): string    { return self::$current['code']; }
    public static function id(): int         { return (int) self::$current['id']; }
    public static function locale(): string  { return self::$current['locale'] ?: self::$current['code']; }
    public static function isDefault(): bool { return self::$current['code'] === self::$default['code']; }
}
