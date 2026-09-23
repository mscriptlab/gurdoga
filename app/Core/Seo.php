<?php
namespace App\Core;

class Seo
{
    public const ROBOTS_DEFAULT = 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1';

    public static string $title = '';
    public static string $description = '';
    public static string $canonical = '';
    public static string $robots = self::ROBOTS_DEFAULT;
    public static string $ogImage = '';
    public static string $ogType = 'website';
    public static string $locale = '';
    /** @var array<string,string> hreflang code => absolute url */
    public static array $alternates = [];
    /** @var string[] other active locales for og:locale:alternate */
    public static array $altLocales = [];
    /** @var array<int,array> JSON-LD graph nodes */
    public static array $jsonLd = [];

    public static function reset(): void
    {
        self::$title = self::$description = self::$canonical = self::$ogImage = self::$locale = '';
        self::$robots = self::ROBOTS_DEFAULT;
        self::$ogType = 'website';
        self::$alternates = [];
        self::$altLocales = [];
        self::$jsonLd = [];
    }

    public static function title(string $t): void
    {
        self::$title = trim($t);
    }

    public static function description(?string $d): void
    {
        $d = trim(preg_replace('/\s+/', ' ', strip_tags((string) $d)));
        if (mb_strlen($d) > 158) {
            $d = mb_substr($d, 0, 155) . '…';
        }
        self::$description = $d;
    }

    public static function addJsonLd(array $node): void
    {
        self::$jsonLd[] = $node;
    }

    public static function fullTitle(): string
    {
        $site = (string) Settings::get('site_name', config('app.name'));
        $t = trim(self::$title);
        if ($t === '' || $t === $site) {
            return $site;
        }
        if ($site !== '' && stripos($t, $site) !== false) {
            return $t;
        }
        return $t . ' | ' . $site;
    }

    public static function render(): string
    {
        $out = [];
        $esc = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES);

        $out[] = '<title>' . $esc(self::fullTitle()) . '</title>';
        if (self::$description !== '') {
            $out[] = '<meta name="description" content="' . $esc(self::$description) . '">';
        }
        $out[] = '<meta name="robots" content="' . $esc(self::$robots) . '">';
        if (self::$canonical !== '') {
            $out[] = '<link rel="canonical" href="' . $esc(self::$canonical) . '">';
        }

        foreach (self::$alternates as $code => $href) {
            $out[] = '<link rel="alternate" hreflang="' . $esc($code) . '" href="' . $esc($href) . '">';
        }

        $out[] = '<meta name="author" content="' . $esc(Settings::get('site_name', config('app.name'))) . '">';
        if ($kw = Settings::get('meta_keywords', '')) {
            $out[] = '<meta name="keywords" content="' . $esc($kw) . '">';
        }

        // Open Graph / Twitter
        $out[] = '<meta property="og:site_name" content="' . $esc(Settings::get('site_name', config('app.name'))) . '">';
        if (self::$locale !== '') {
            $out[] = '<meta property="og:locale" content="' . $esc(self::$locale) . '">';
            foreach (self::$altLocales as $al) {
                $out[] = '<meta property="og:locale:alternate" content="' . $esc($al) . '">';
            }
        }
        $out[] = '<meta property="og:title" content="' . $esc(self::$title ?: self::fullTitle()) . '">';
        if (self::$description !== '') {
            $out[] = '<meta property="og:description" content="' . $esc(self::$description) . '">';
        }
        $out[] = '<meta property="og:type" content="' . $esc(self::$ogType) . '">';
        if (self::$canonical !== '') {
            $out[] = '<meta property="og:url" content="' . $esc(self::$canonical) . '">';
        }
        $img = self::$ogImage ?: Settings::raw('default_og_image', '');
        if ($img !== '') {
            $abs = preg_match('#^https?://#', $img) ? $img : rtrim(config('app.base_url'), '/') . '/' . ltrim($img, '/');
            $out[] = '<meta property="og:image" content="' . $esc($abs) . '">';
            $out[] = '<meta name="twitter:card" content="summary_large_image">';
        } else {
            $out[] = '<meta name="twitter:card" content="summary">';
        }

        if (self::$jsonLd) {
            $graph = count(self::$jsonLd) === 1
                ? self::$jsonLd[0]
                : ['@context' => 'https://schema.org', '@graph' => self::$jsonLd];
            $out[] = '<script type="application/ld+json">' .
                json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
        }

        return implode("\n    ", $out);
    }
}
