<?php
namespace App\Controllers\Site;

use App\Core\Lang;
use App\Core\Seo;
use App\Core\Settings;
use App\Models\Category;
use App\Models\Product;

class CatalogController extends SiteController
{
    public function index(): string
    {
        Seo::title(t('nav.products', 'Products'));
        Seo::description(Settings::get('products_meta_description', t('products.intro', 'Explore our home textile collections.')));
        $this->localize('products');
        $crumbs = $this->breadcrumb([
            ['name' => t('nav.home', 'Home'), 'url' => abs_url(Lang::code())],
            ['name' => t('nav.products', 'Products'), 'url' => null],
        ]);

        return $this->view('site/catalog/index', [
            'categories' => Category::activeList(),
            'crumbs'     => $crumbs,
        ]);
    }

    public function category(string $slug): string
    {
        $cat = Category::findBySlug($slug);
        if (!$cat) {
            $this->notFound();
        }
        $catSlugs = Category::slugsByLanguage((int) $cat['id']);
        $curSlug = $catSlugs[Lang::code()] ?? $cat['slug'];
        if ($curSlug !== $slug) {
            redirect(lang_url('products/' . $curSlug), 301);
        }
        $slug = $curSlug;
        $page = max(1, (int) $this->request->input('page', 1));
        $perPage = 24;
        $total = Product::countByCategory((int) $cat['id']);
        $products = Product::byCategory((int) $cat['id'], null, $perPage, ($page - 1) * $perPage);

        $name = $cat['name'] ?: $slug;
        Seo::title($cat['meta_title'] ?: $name);
        Seo::description($cat['meta_description'] ?: $cat['description'] ?: $name);
        $paths = [];
        foreach (Lang::all() as $l) {
            $paths[$l['code']] = 'products/' . ($catSlugs[$l['code']] ?? $cat['slug']);
        }
        $this->localize('products/' . $slug, $paths);
        if ($page > 1) {
            Seo::$canonical = lang_abs('products/' . $slug) . '?page=' . $page;
        }

        $crumbs = $this->breadcrumb([
            ['name' => t('nav.home', 'Home'), 'url' => abs_url(Lang::code())],
            ['name' => t('nav.products', 'Products'), 'url' => lang_abs('products')],
            ['name' => $name, 'url' => null],
        ]);

        $itemList = [];
        foreach ($products as $i => $pp) {
            $itemList[] = [
                '@type'    => 'ListItem',
                'position' => ($page - 1) * $perPage + $i + 1,
                'url'      => lang_abs('product/' . $pp['slug']),
                'name'     => $pp['name'] ?: $pp['slug'],
            ];
        }
        Seo::addJsonLd([
            '@context'    => 'https://schema.org',
            '@type'       => 'CollectionPage',
            'name'        => $name,
            'description' => Seo::$description,
            'url'         => Seo::$canonical,
            'isPartOf'    => ['@id' => abs_url('#website')],
            'about'       => ['@type' => 'ProductGroup', 'name' => $name],
            'mainEntity'  => [
                '@type'           => 'ItemList',
                'numberOfItems'   => $total,
                'itemListElement' => $itemList,
            ],
        ]);

        return $this->view('site/catalog/category', [
            'crumbs'   => $crumbs,
            'category' => $cat,
            'products' => $products,
            'total'    => $total,
            'page'     => $page,
            'perPage'  => $perPage,
        ]);
    }

    public function product(string $slug): string
    {
        $p = Product::findBySlug($slug);
        if (!$p) {
            $this->notFound();
        }
        $cat = $p['category_id'] ? Category::find((int) $p['category_id']) : null;
        if ($cat) {
            $cat['slug'] = Category::slugsByLanguage((int) $cat['id'])[Lang::code()] ?? $cat['slug'];
        }
        $catName = $cat ? Category::name((int) $cat['id']) : null;
        $images = Product::images((int) $p['id']);
        $cover  = Product::cover($p);

        $name = $p['name'] ?: $slug;
        Seo::title($p['meta_title'] ?: $name);
        Seo::description($p['meta_description'] ?: $p['short_desc'] ?: $p['description'] ?: $name);
        Seo::$ogType = 'product';
        if ($cover) {
            Seo::$ogImage = $cover;
        }

        // hreflang: product slug differs per language
        $slugs = Product::slugsByLanguage((int) $p['id']);
        $paths = [];
        foreach (Lang::all() as $l) {
            $paths[$l['code']] = isset($slugs[$l['code']]) ? 'product/' . $slugs[$l['code']] : null;
        }
        $this->localize('product/' . $slug, $paths);

        $crumbs = [['name' => t('nav.home', 'Home'), 'url' => abs_url(Lang::code())],
                   ['name' => t('nav.products', 'Products'), 'url' => lang_abs('products')]];
        if ($cat) {
            $crumbs[] = ['name' => $catName, 'url' => lang_abs('products/' . $cat['slug'])];
        }
        $crumbs[] = ['name' => $name, 'url' => null];
        $crumbs = $this->breadcrumb($crumbs);

        $ld = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $name,
            'description' => Seo::$description,
            'brand'       => ['@type' => 'Brand', 'name' => Settings::get('site_name', 'Gürdoğa Kooperatifi')],
        ];
        if ($p['sku']) {
            $ld['sku'] = $p['sku'];
        }
        if ($cat) {
            $ld['category'] = $catName;
        }
        $imgs = [];
        foreach ($images as $im) {
            $imgs[] = abs_url(ltrim(preg_replace('/\.jpg$/', '.jpg', $im['path']), '/'));
        }
        if (!$imgs && $cover) {
            $imgs[] = abs_url(ltrim($cover, '/'));
        }
        if ($imgs) {
            $ld['image'] = $imgs;
        }
        Seo::addJsonLd($ld);

        return $this->view('site/catalog/product', [
            'crumbs'   => $crumbs,
            'product'  => $p,
            'category' => $cat,
            'catName'  => $catName,
            'images'   => $images,
            'cover'    => $cover,
            'attributes' => Product::attributes((int) $p['id']),
            'related'  => Product::related((int) $p['id'], $p['category_id'] ? (int) $p['category_id'] : null),
        ]);
    }

    public function search(): string
    {
        $q = trim((string) $this->request->input('q', ''));
        Seo::title(t('search.title', 'Search') . ($q !== '' ? ': ' . $q : ''));
        Seo::$robots = 'noindex,follow';
        $this->localize('search');
        $results = $q !== '' ? Product::search($q) : [];
        return $this->view('site/catalog/search', ['q' => $q, 'results' => $results]);
    }
}
