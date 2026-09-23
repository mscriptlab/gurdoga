<?php
namespace App\Controllers\Site;

use App\Core\Controller;
use App\Core\Lang;
use App\Core\Seo;
use App\Core\Settings;

abstract class SiteController extends Controller
{
    /**
     * Set canonical + hreflang alternates.
     * @param array<string,string> $pathByCode  language code => path WITHOUT lang prefix (e.g. "products/towels")
     *                                           If a code is missing it is skipped.
     */
    protected function localize(string $currentPath, array $pathByCode = []): void
    {
        Seo::$canonical = lang_abs($currentPath);

        $alts = [];
        foreach (Lang::all() as $l) {
            $code = $l['code'];
            if (array_key_exists($code, $pathByCode)) {
                if ($pathByCode[$code] === null) {
                    continue;
                }
                $p = $pathByCode[$code];
            } else {
                $p = $currentPath; // same path structure across languages
            }
            $alts[$code] = lang_abs($p, $code);
        }
        if (isset($alts[Lang::default()['code']])) {
            $alts['x-default'] = $alts[Lang::default()['code']];
        }
        Seo::$alternates = $alts;

        Seo::$locale = str_replace('-', '_', Lang::locale());
        $others = [];
        foreach (Lang::all() as $l) {
            if ($l['is_active'] && $l['code'] !== Lang::code()) {
                $others[] = str_replace('-', '_', $l['locale'] ?: $l['code']);
            }
        }
        Seo::$altLocales = $others;
    }

    protected function breadcrumb(array $items): array
    {
        // $items: [ ['name'=>..., 'url'=>absolute|null], ... ]
        $elements = [];
        foreach (array_values($items) as $i => $it) {
            $el = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $it['name']];
            if (!empty($it['url'])) {
                $el['item'] = $it['url'];
            }
            $elements[] = $el;
        }
        Seo::addJsonLd(['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $elements]);
        return $items;
    }

    /** A social URL is only useful for sameAs if it points to a real profile, not the domain root. */
    protected function realSocial(?string $u): ?string
    {
        $u = trim((string) $u);
        if ($u === '' || !preg_match('#^https?://#i', $u)) {
            return null;
        }
        $path = trim((string) parse_url($u, PHP_URL_PATH), '/');
        return $path === '' ? null : $u;
    }

    protected function organizationJsonLd(): void
    {
        $sameAs = array_values(array_filter(array_map([$this, 'realSocial'], [
            Settings::raw('social_linkedin'), Settings::raw('social_instagram'),
            Settings::raw('social_facebook'), Settings::raw('social_youtube'),
        ])));
        $name = Settings::get('site_name', config('app.name'));
        $node = [
            '@context'      => 'https://schema.org',
            '@type'         => 'Organization',
            '@id'           => abs_url('#organization'),
            'name'          => $name,
            'url'           => abs_url(''),
            'description'   => Settings::get('footer_about', Settings::get('home_meta_description', '')),
            'slogan'        => Settings::get('site_tagline', ''),
            'knowsLanguage' => array_values(array_filter(array_map(
                fn($l) => $l['is_active'] ? $l['code'] : null, Lang::all()
            ))),
            'areaServed'    => 'TR',
        ];
        if ($logo = Settings::raw('logo')) {
            $node['logo'] = abs_url(ltrim($logo, '/'));
        }
        if ($og = Settings::raw('default_og_image')) {
            $node['image'] = preg_match('#^https?://#', $og) ? $og : abs_url(ltrim($og, '/'));
        }
        if ($addr = Settings::raw('contact_address')) {
            $node['address'] = ['@type' => 'PostalAddress', 'streetAddress' => $addr, 'addressCountry' => 'TR'];
        }
        $cp = [];
        if ($tel = Settings::raw('contact_phone')) { $cp['telephone'] = $tel; }
        if ($em = Settings::raw('contact_email')) { $cp['email'] = $em; }
        if ($cp) {
            $node['contactPoint'] = [
                ['@type' => 'ContactPoint', 'contactType' => 'sales', 'availableLanguage' => ['tr']] + $cp,
            ];
        }
        if ($sameAs) {
            $node['sameAs'] = $sameAs;
        }
        Seo::addJsonLd($node);
    }
}
