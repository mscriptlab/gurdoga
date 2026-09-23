<?php
namespace App\Controllers\Site;

use App\Core\Lang;
use App\Core\Seo;
use App\Core\Settings;
use App\Models\Post;

class BlogController extends SiteController
{
    public function index(): string
    {
        $page = max(1, (int) $this->request->input('page', 1));
        $perPage = 9;
        $total = Post::countPublished();
        $posts = Post::published(null, $perPage, ($page - 1) * $perPage);

        Seo::title(t('blog.title', 'Journal'));
        Seo::description(Settings::get('blog_meta_description', t('blog.intro', 'Kooperatifimizden haberler ve ürünlerimize dair yazılar.')));
        $this->localize('blog');
        if ($page > 1) {
            Seo::$canonical = lang_abs('blog') . '?page=' . $page;
        }
        $crumbs = $this->breadcrumb([
            ['name' => t('nav.home', 'Home'), 'url' => abs_url(Lang::code())],
            ['name' => t('blog.title', 'Journal'), 'url' => null],
        ]);
        Seo::addJsonLd([
            '@context' => 'https://schema.org',
            '@type'    => 'Blog',
            'name'     => t('blog.title', 'Journal') . ' — ' . Settings::get('site_name', 'Gürdoğa Kooperatifi'),
            'url'      => lang_abs('blog'),
        ]);

        return $this->view('site/blog/index', [
            'posts'   => $posts,
            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,
            'crumbs'  => $crumbs,
        ]);
    }

    public function feed(): string
    {
        header('Content-Type: application/rss+xml; charset=utf-8');
        $posts = Post::published(null, 30);
        $self  = lang_abs('rss');
        $now   = date(DATE_RSS);
        $esc   = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES | ENT_XML1);

        $items = '';
        foreach ($posts as $p) {
            $url = lang_abs('blog/' . $p['slug']);
            $date = $p['published_at'] ?: $p['created_at'];
            $items .= "\n  <item>\n"
                . '    <title>' . $esc($p['title']) . "</title>\n"
                . '    <link>' . $esc($url) . "</link>\n"
                . '    <guid isPermaLink="true">' . $esc($url) . "</guid>\n"
                . ($date ? '    <pubDate>' . date(DATE_RSS, strtotime($date)) . "</pubDate>\n" : '')
                . '    <description>' . $esc($p['excerpt'] ?: mb_substr(strip_tags((string) $p['body']), 0, 300)) . "</description>\n"
                . '  </item>';
        }

        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n"
            . "<channel>\n"
            . '  <title>' . $esc(Settings::get('site_name', 'Gürdoğa Kooperatifi') . ' — ' . t('blog.title', 'Journal')) . "</title>\n"
            . '  <link>' . $esc(lang_abs('blog')) . "</link>\n"
            . '  <description>' . $esc(Settings::get('blog_meta_description', t('blog.intro', ''))) . "</description>\n"
            . '  <language>' . $esc(Lang::code()) . "</language>\n"
            . '  <lastBuildDate>' . $now . "</lastBuildDate>\n"
            . '  <atom:link href="' . $esc($self) . '" rel="self" type="application/rss+xml"/>'
            . $items . "\n</channel>\n</rss>";
    }

    public function show(string $slug): string
    {
        $post = Post::findBySlug($slug);
        if (!$post) {
            $this->notFound();
        }
        $title = $post['title'] ?: $slug;
        Seo::title($post['meta_title'] ?: $title);
        Seo::description($post['meta_description'] ?: $post['excerpt'] ?: $post['body']);
        Seo::$ogType = 'article';
        if ($post['cover_image']) {
            Seo::$ogImage = $post['cover_image'];
        }

        $slugs = Post::slugsByLanguage((int) $post['id']);
        $paths = [];
        foreach (Lang::all() as $l) {
            $paths[$l['code']] = isset($slugs[$l['code']]) ? 'blog/' . $slugs[$l['code']] : null;
        }
        $this->localize('blog/' . $slug, $paths);

        $crumbs = $this->breadcrumb([
            ['name' => t('nav.home', 'Home'), 'url' => abs_url(Lang::code())],
            ['name' => t('blog.title', 'Journal'), 'url' => lang_abs('blog')],
            ['name' => $title, 'url' => null],
        ]);

        $published = $post['published_at'] ?: $post['created_at'];
        $img = $post['cover_image'] ? abs_url(ltrim($post['cover_image'], '/')) : Settings::raw('default_og_image');
        Seo::addJsonLd([
            '@context'         => 'https://schema.org',
            '@type'            => 'Article',
            'headline'         => $title,
            'description'      => Seo::$description,
            'image'            => $img ? [$img] : [],
            'inLanguage'       => Lang::code(),
            'datePublished'    => $published ? date('c', strtotime($published)) : null,
            'dateModified'     => $post['updated_at'] ? date('c', strtotime($post['updated_at'])) : ($published ? date('c', strtotime($published)) : null),
            'author'           => ['@type' => 'Organization', 'name' => $post['author'] ?: Settings::get('site_name', 'Gürdoğa Kooperatifi')],
            'publisher'        => ['@id' => abs_url('#organization')],
            'mainEntityOfPage' => Seo::$canonical,
        ]);
        $this->organizationJsonLd();

        return $this->view('site/blog/post', [
            'post'   => $post,
            'crumbs' => $crumbs,
            'recent' => Post::recent((int) $post['id'], 5),
        ]);
    }
}
