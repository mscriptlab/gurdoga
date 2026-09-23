<?php
namespace App\Controllers\Site;

use App\Core\Lang;
use App\Core\Seo;
use App\Core\Settings;
use App\Core\View;
use App\Models\Category;
use App\Models\Product;

class HomeController extends SiteController
{
    public function index(): string
    {
        Seo::title(Settings::get('home_meta_title', Settings::get('site_name', 'Gürdoğa Kooperatifi')));
        Seo::description(Settings::get('home_meta_description', Settings::get('site_tagline', '')));
        Seo::$ogType = 'website';
        $this->localize('');
        $this->organizationJsonLd();
        Seo::addJsonLd([
            '@context'         => 'https://schema.org',
            '@type'            => 'WebSite',
            '@id'              => abs_url('#website'),
            'name'             => Settings::get('site_name', 'Gürdoğa Kooperatifi'),
            'url'              => abs_url(''),
            'inLanguage'       => Lang::code(),
            'publisher'        => ['@id' => abs_url('#organization')],
            'potentialAction'  => [
                '@type'       => 'SearchAction',
                'target'      => [
                    '@type'       => 'EntryPoint',
                    'urlTemplate' => lang_abs('search') . '?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ]);

        $categories = Category::activeList();

        // FAQ (also rendered on the page) — FAQPage rich result
        $faq = $this->faqItems();
        Seo::addJsonLd([
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => array_map(fn($q) => [
                '@type'          => 'Question',
                'name'           => $q[0],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q[1]],
            ], $faq),
        ]);

        // Preload the LCP hero image
        $withImg = array_values(array_filter($categories, fn($c) => !empty($c['image'])));
        if (!empty($withImg[0]['image'])) {
            View::share('preloadImage', $withImg[0]['image']);
        }

        return $this->view('site/home', [
            'categories' => $categories,
            'featured'   => Product::featured(8),
            'faq'        => $faq,
        ]);
    }

    /** @return array<int,array{0:string,1:string}> question / answer pairs in the active language */
    private function faqItems(): array
    {
        $keys = ['moq', 'lead', 'sample', 'cert', 'custom', 'ship'];
        $out = [];
        foreach ($keys as $k) {
            $q = t('faq.' . $k . '_q', '');
            $a = t('faq.' . $k . '_a', '');
            if ($q !== '' && $q !== 'faq.' . $k . '_q') {
                $out[] = [$q, $a];
            }
        }
        return $out;
    }
}
