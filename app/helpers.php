<?php

use App\Core\Lang;
use App\Core\Seo;

function config(string $key, $default = null)
{
    static $cfg;
    if ($cfg === null) {
        $cfg = require BASE_DIR . '/config/config.php';
    }
    $seg = explode('.', $key);
    $val = $cfg;
    foreach ($seg as $s) {
        if (!is_array($val) || !array_key_exists($s, $val)) {
            return $default;
        }
        $val = $val[$s];
    }
    return $val;
}

/** Absolute-path URL under the app base path. */
function url(string $path = '/'): string
{
    $base = rtrim((string) config('app.base_path', ''), '/');
    return $base . '/' . ltrim($path, '/');
}

/** Fully-qualified URL (for canonical, sitemap, emails). */
function abs_url(string $path = '/'): string
{
    return rtrim((string) config('app.base_url'), '/') . '/' . ltrim($path, '/');
}

/**
 * Canonical route segment => localized segment per language code.
 * The canonical (English) segment is what the router and all controllers use internally;
 * public URLs show the localized segment for the active language.
 */
function route_segments(): array
{
    return [
        'products' => ['tr' => 'urunler',  'de' => 'produkte'],
        'product'  => ['tr' => 'urun',     'de' => 'produkt'],
        'page'     => ['tr' => 'sayfa',    'de' => 'seite'],
        'blog'     => ['tr' => 'blog',     'de' => 'blog'],
        'contact'  => ['tr' => 'iletisim', 'de' => 'kontakt'],
        'quote'    => ['tr' => 'teklif',   'de' => 'angebot'],
        'search'   => ['tr' => 'arama',    'de' => 'suche'],
        'rss'      => ['tr' => 'rss',      'de' => 'rss'],
    ];
}

/** Localize the leading route segment of a path for a language code ("product/x" + tr => "urun/x"). */
function loc_path(string $path, string $code): string
{
    $path = ltrim($path, '/');
    if ($path === '') {
        return '';
    }
    $parts = explode('/', $path, 2);
    $map = route_segments()[$parts[0]] ?? null;
    if ($map && !empty($map[$code])) {
        $parts[0] = $map[$code];
    }
    return implode('/', $parts);
}

/** Reverse loc_path: turn a possibly-localized leading segment back to its canonical form. */
function canon_path(string $path): string
{
    $path = ltrim($path, '/');
    if ($path === '') {
        return '';
    }
    $parts = explode('/', $path, 2);
    foreach (route_segments() as $canon => $map) {
        if ($parts[0] === $canon || in_array($parts[0], $map, true)) {
            $parts[0] = $canon;
            break;
        }
    }
    return implode('/', $parts);
}

/** URL to a localized route: lang_url('products/zeytinyagi') => /gurdoga/tr/products/zeytinyagi */
function lang_url(string $path = '', ?string $code = null): string
{
    $code = $code ?: Lang::code();
    $path = loc_path(ltrim($path, '/'), $code);
    return url($code . ($path !== '' ? '/' . $path : ''));
}

/** Fully-qualified localized route URL (canonical / hreflang / sitemap). */
function lang_abs(string $path = '', ?string $code = null): string
{
    $code = $code ?: Lang::code();
    $path = loc_path(ltrim($path, '/'), $code);
    return abs_url($code . ($path !== '' ? '/' . $path : ''));
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/** URL for an uploaded/stored file (path stored relative to project root, e.g. "uploads/..."). */
function media(string $path): string
{
    if (preg_match('#^https?://#', $path)) {
        return $path;
    }
    return url(ltrim($path, '/'));
}

/** Plain-text excerpt from an HTML body. */
function excerpt(?string $html, int $len = 300): string
{
    $html = preg_replace('#</(p|div|li|h[1-6]|br|tr)\s*>#i', ' ', (string) $html);
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($html)));
    if (mb_strlen($text) <= $len) {
        return $text;
    }
    $cut = mb_substr($text, 0, $len);
    $sp = mb_strrpos($cut, ' ');
    return rtrim($sp ? mb_substr($cut, 0, $sp) : $cut, " ,.;:") . '…';
}

/** Returns the social URL only if it points to a real profile (not the bare domain). */
function social_url(?string $u): ?string
{
    $u = trim((string) $u);
    if ($u === '' || !preg_match('#^https?://#i', $u)) {
        return null;
    }
    return trim((string) parse_url($u, PHP_URL_PATH), '/') === '' ? null : $u;
}

function view_flash(): string
{
    $out = '';
    foreach (['success' => 'ok', 'error' => 'err', 'form_error' => 'err'] as $k => $cls) {
        if ($msg = flash($k)) {
            $out .= '<div class="flash flash-' . $cls . '">' . e($msg) . '</div>';
        }
    }
    return $out;
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Escape for output, first normalising any already-encoded entities (avoids &amp;amp;). */
function etext($value): string
{
    return htmlspecialchars(
        html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        ENT_QUOTES,
        'UTF-8'
    );
}

function t(string $key, ?string $fallback = null): string
{
    return Lang::t($key, $fallback);
}

function slugify(string $text): string
{
    $map = [
        'ç'=>'c','ğ'=>'g','ı'=>'i','İ'=>'i','ö'=>'o','ş'=>'s','ü'=>'u',
        'Ç'=>'c','Ğ'=>'g','Ö'=>'o','Ş'=>'s','Ü'=>'u','ä'=>'ae','ö'=>'oe','ü'=>'ue','ß'=>'ss',
    ];
    $text = strtr($text, $map);
    if (function_exists('iconv')) {
        $conv = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($conv !== false) {
            $text = $conv;
        }
    }
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') ?: 'n-a';
}

function redirect(string $to, int $code = 302)/*: never */
{
    header('Location: ' . $to, true, $code);
    exit;
}

function flash(?string $key = null, $value = null)
{
    if ($key === null) {
        $all = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $all;
    }
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }
    $v = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $v;
}

function old(string $key, $default = '')
{
    return $_SESSION['_old'][$key] ?? $default;
}

function remember_old(array $data): void
{
    $_SESSION['_old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

function csrf_field(): string
{
    return \App\Core\Csrf::field();
}

function csrf_token(): string
{
    return \App\Core\Csrf::token();
}

function seo(): string
{
    return Seo::render();
}
