<?php
namespace App\Core;

/**
 * Phone-home licence check. The vendor runs a small endpoint (see scripts/licence-server/)
 * that, given a key + domain, returns an HMAC-signed JSON verdict. The result is cached on
 * disk and re-validated periodically; if the endpoint is unreachable the last good verdict
 * is trusted for a grace window so a network blip never takes the panel down.
 *
 * Enforcement is deliberately soft: an invalid licence blocks the ADMIN PANEL only
 * (with a message), the public site keeps serving. Nothing is ever deleted.
 */
class License
{
    private const CACHE  = BASE_DIR . '/storage/license.json';
    private const RECHECK_SECONDS = 43200;   // 12h between live checks
    private const HTTP_TIMEOUT    = 6;

    /** @var array<string,mixed>|null */
    private static ?array $state = null;

    public static function boot(): void
    {
        $cfg = (array) config('license', []);
        if (empty($cfg['key']) || empty($cfg['server'])) {
            self::$state = ['ok' => true, 'status' => 'unmanaged', 'message' => '', 'checked_at' => time()];
            return;
        }

        $cache = self::readCache();
        $fresh = $cache && (time() - (int) ($cache['checked_at'] ?? 0)) < self::RECHECK_SECONDS;

        if ($fresh) {
            self::$state = $cache;
            return;
        }

        $live = self::fetch($cfg);
        if ($live !== null) {
            $live['checked_at'] = time();
            self::writeCache($live);
            self::$state = $live;
            return;
        }

        // endpoint unreachable -> fall back to cache within the grace window
        $graceDays = (int) ($cfg['grace_days'] ?? 10);
        if ($cache && (time() - (int) ($cache['ok_at'] ?? $cache['checked_at'] ?? 0)) < $graceDays * 86400) {
            $cache['stale'] = true;
            self::$state = $cache;
            return;
        }

        // Never had a single successful check yet (fresh install, endpoint not up).
        // Fail OPEN with a warning — a down/misconfigured vendor server must not brick
        // the panel. Once one good check lands, the grace logic above takes over.
        if (!$cache || empty($cache['ok_at'])) {
            self::$state = [
                'ok' => true,
                'status' => 'pending',
                'message' => 'Lisans sunucusuna henüz ulaşılamadı — doğrulama beklemede.',
                'checked_at' => time(),
            ];
            return;
        }

        self::$state = [
            'ok' => false,
            'status' => 'unreachable',
            'message' => 'Lisans sunucusuna ulaşılamıyor. Lütfen tedarikçinizle (TeknoBursa) iletişime geçin.',
            'checked_at' => time(),
        ];
    }

    public static function ok(): bool
    {
        return (bool) (self::state()['ok'] ?? true);
    }

    public static function status(): string
    {
        return (string) (self::state()['status'] ?? 'unmanaged');
    }

    public static function message(): string
    {
        return (string) (self::state()['message'] ?? '');
    }

    /** True when the panel should be blocked (managed + not ok + enforcement on). */
    public static function blocksAdmin(): bool
    {
        $cfg = (array) config('license', []);
        if (empty($cfg['key']) || empty($cfg['server'])) {
            return false;
        }
        if (!array_key_exists('enforce', $cfg) || $cfg['enforce']) {
            return !self::ok();
        }
        return false;
    }

    /** @return array<string,mixed> */
    public static function state(): array
    {
        if (self::$state === null) {
            self::boot();
        }
        return self::$state ?? ['ok' => true, 'status' => 'unmanaged'];
    }

    // ---------------------------------------------------------------------

    /** @return array<string,mixed>|null  null = could not reach / parse */
    private static function fetch(array $cfg): ?array
    {
        $payload = http_build_query([
            'key'    => $cfg['key'],
            'domain' => self::domain(),
            'v'      => (string) config('app.version', '1'),
        ]);
        $url = rtrim((string) $cfg['server'], '?&');
        $url .= (strpos($url, '?') !== false ? '&' : '?') . $payload;

        $raw = null;
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => self::HTTP_TIMEOUT,
                CURLOPT_CONNECTTIMEOUT => self::HTTP_TIMEOUT,
                CURLOPT_USERAGENT      => 'GürdoğaLicense/1.0',
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            $raw = curl_exec($ch);
            curl_close($ch);
        } else {
            $ctx = stream_context_create(['http' => ['timeout' => self::HTTP_TIMEOUT], 'ssl' => ['verify_peer' => true]]);
            $raw = @file_get_contents($url, false, $ctx);
        }
        if (!is_string($raw) || $raw === '') {
            return null;
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || !isset($data['status'], $data['sig'])) {
            return null;
        }

        // verify HMAC so a spoofed endpoint (hosts file / DNS) can't fake "active"
        $secret = (string) ($cfg['secret'] ?? '');
        $base = ($data['status'] ?? '') . '|' . self::domain() . '|' . ($data['valid_until'] ?? '') . '|' . ($data['nonce'] ?? '');
        $expect = hash_hmac('sha256', $base, $secret);
        if (!hash_equals($expect, (string) $data['sig'])) {
            return ['ok' => false, 'status' => 'bad_signature',
                    'message' => 'Lisans doğrulaması başarısız. Tedarikçinizle (TeknoBursa) iletişime geçin.'];
        }

        $active = in_array($data['status'], ['active', 'trial'], true)
            && (empty($data['valid_until']) || strtotime((string) $data['valid_until']) > time());

        return [
            'ok'          => $active,
            'status'      => (string) $data['status'],
            'message'     => (string) ($data['message'] ?? ''),
            'valid_until' => (string) ($data['valid_until'] ?? ''),
            'ok_at'       => $active ? time() : null,
        ];
    }

    private static function domain(): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? parse_url((string) config('app.base_url'), PHP_URL_HOST) ?? 'cli';
        return strtolower(preg_replace('/^www\./', '', (string) $host));
    }

    /** @return array<string,mixed>|null */
    private static function readCache(): ?array
    {
        if (!is_file(self::CACHE)) {
            return null;
        }
        $d = json_decode((string) file_get_contents(self::CACHE), true);
        return is_array($d) ? $d : null;
    }

    private static function writeCache(array $data): void
    {
        $prev = self::readCache();
        if (($data['ok_at'] ?? null) === null && !empty($prev['ok_at'])) {
            $data['ok_at'] = $prev['ok_at']; // keep last-good timestamp for the grace window
        }
        @file_put_contents(self::CACHE, json_encode($data), LOCK_EX);
    }
}
