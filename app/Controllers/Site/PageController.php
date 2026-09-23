<?php
namespace App\Controllers\Site;

use App\Core\Lang;
use App\Core\Seo;
use App\Models\Page;
use App\Models\SalesPoint;

class PageController extends SiteController
{
    public function show(string $slug): string
    {
        $page = Page::findBySlug($slug);
        if (!$page) {
            $this->notFound();
        }
        $slugs = Page::slugsByLanguage((int) $page['id']);
        $curSlug = $slugs[Lang::code()] ?? $page['slug'];
        if ($curSlug !== $slug) {
            redirect(lang_url('page/' . $curSlug), 301);
        }

        $title = $page['title'] ?: ucfirst($curSlug);
        Seo::title($page['meta_title'] ?: $title);
        Seo::description($page['meta_description'] ?: strip_tags((string) $page['body']));
        if (!empty($page['og_image'])) {
            Seo::$ogImage = $page['og_image'];
        }
        $paths = [];
        foreach (Lang::all() as $l) {
            $paths[$l['code']] = 'page/' . ($slugs[$l['code']] ?? $page['slug']);
        }
        $this->localize('page/' . $curSlug, $paths);
        $crumbs = $this->breadcrumb([
            ['name' => t('nav.home', 'Home'), 'url' => abs_url(Lang::code())],
            ['name' => $title, 'url' => null],
        ]);

        $view = match ($page['template'] ?? '') {
            'sustainability' => 'site/sustainability',
            'sales-points'   => 'site/sales-points',
            default          => 'site/page',
        };

        $extra = $view === 'site/sales-points' ? ['salesPoints' => SalesPoint::activeList()] : [];

        return $this->view($view, ['page' => $page, 'title' => $title, 'crumbs' => $crumbs] + $extra);
    }
}
