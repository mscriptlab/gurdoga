<?php

use App\Core\Router;
use App\Controllers\Site\HomeController;
use App\Controllers\Site\CatalogController;
use App\Controllers\Site\PageController;
use App\Controllers\Site\ContactController;
use App\Controllers\Site\SitemapController;
use App\Controllers\Admin\AuthController as AdminAuth;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\ProductController as AdminProduct;
use App\Controllers\Admin\CategoryController as AdminCategory;
use App\Controllers\Admin\SliderController as AdminSlider;
use App\Controllers\Admin\MediaController as AdminMedia;
use App\Controllers\Admin\PageController as AdminPage;
use App\Controllers\Admin\MenuController as AdminMenu;
use App\Controllers\Admin\LanguageController as AdminLanguage;
use App\Controllers\Admin\TranslationController as AdminTranslation;
use App\Controllers\Admin\QuoteController as AdminQuote;
use App\Controllers\Admin\RedirectController as AdminRedirect;
use App\Controllers\Admin\SettingController as AdminSetting;
use App\Controllers\Admin\UserController as AdminUser;
use App\Controllers\Admin\SalesPointController as AdminSalesPoint;

return function (Router $r): void {

    $site  = fn(array $a) => ['localized' => true, 'action' => $a];
    $sys   = fn(array $a) => ['localized' => false, 'action' => $a];

    /* ---------- Localized front-end ---------- */
    $r->get('/', $site([HomeController::class, 'index']));
    $r->get('/products', $site([CatalogController::class, 'index']));
    $r->get('/products/{slug}', $site([CatalogController::class, 'category']));
    $r->get('/product/{slug}', $site([CatalogController::class, 'product']));
    $r->get('/contact', $site([ContactController::class, 'show']));
    $r->post('/contact', $site([ContactController::class, 'submit']));
    $r->get('/quote', $site([ContactController::class, 'quote']));
    $r->post('/quote', $site([ContactController::class, 'submitQuote']));
    $r->get('/search', $site([CatalogController::class, 'search']));
    $r->get('/blog', $site([\App\Controllers\Site\BlogController::class, 'index']));
    $r->get('/rss', $site([\App\Controllers\Site\BlogController::class, 'feed']));
    $r->get('/blog/{slug}', $site([\App\Controllers\Site\BlogController::class, 'show']));
    $r->get('/page/{slug}', $site([PageController::class, 'show']));

    /* ---------- System (non-localized) ---------- */
    $r->get('/sitemap.xml', $sys([SitemapController::class, 'index']));
    $r->get('/robots.txt', $sys([SitemapController::class, 'robots']));

    /* ---------- Admin ---------- */
    $r->get('/admin/login', $sys([AdminAuth::class, 'showLogin']));
    $r->post('/admin/login', $sys([AdminAuth::class, 'login']));
    $r->get('/admin/logout', $sys([AdminAuth::class, 'logout']));

    $r->get('/admin', $sys([DashboardController::class, 'index']));

    $r->get('/admin/products', $sys([AdminProduct::class, 'index']));
    $r->get('/admin/products/create', $sys([AdminProduct::class, 'create']));
    $r->post('/admin/products', $sys([AdminProduct::class, 'store']));
    $r->get('/admin/products/{id}/edit', $sys([AdminProduct::class, 'edit']));
    $r->post('/admin/products/{id}', $sys([AdminProduct::class, 'update']));
    $r->post('/admin/products/{id}/delete', $sys([AdminProduct::class, 'destroy']));
    $r->post('/admin/products/{id}/image', $sys([AdminProduct::class, 'uploadImage']));
    $r->post('/admin/products/{id}/cover', $sys([AdminProduct::class, 'setCover']));
    $r->post('/admin/products/{id}/image-alts', $sys([AdminProduct::class, 'saveAlts']));
    $r->post('/admin/products/{id}/images/reorder', $sys([AdminProduct::class, 'reorderImages']));
    $r->post('/admin/product-images/{id}/delete', $sys([AdminProduct::class, 'deleteImage']));

    $r->get('/admin/categories', $sys([AdminCategory::class, 'index']));
    $r->get('/admin/categories/create', $sys([AdminCategory::class, 'create']));
    $r->post('/admin/categories', $sys([AdminCategory::class, 'store']));
    $r->get('/admin/categories/{id}/edit', $sys([AdminCategory::class, 'edit']));
    $r->post('/admin/categories/{id}', $sys([AdminCategory::class, 'update']));
    $r->post('/admin/categories/{id}/delete', $sys([AdminCategory::class, 'destroy']));

    $r->get('/admin/sliders', $sys([AdminSlider::class, 'index']));
    $r->get('/admin/sliders/create', $sys([AdminSlider::class, 'create']));
    $r->post('/admin/sliders', $sys([AdminSlider::class, 'store']));
    $r->get('/admin/sliders/{id}/edit', $sys([AdminSlider::class, 'edit']));
    $r->post('/admin/sliders/{id}', $sys([AdminSlider::class, 'update']));
    $r->post('/admin/sliders/{id}/delete', $sys([AdminSlider::class, 'destroy']));

    $r->post('/admin/media/upload', $sys([AdminMedia::class, 'upload']));

    $r->get('/admin/posts', $sys([\App\Controllers\Admin\PostController::class, 'index']));
    $r->get('/admin/posts/create', $sys([\App\Controllers\Admin\PostController::class, 'create']));
    $r->post('/admin/posts', $sys([\App\Controllers\Admin\PostController::class, 'store']));
    $r->get('/admin/posts/{id}/edit', $sys([\App\Controllers\Admin\PostController::class, 'edit']));
    $r->post('/admin/posts/{id}', $sys([\App\Controllers\Admin\PostController::class, 'update']));
    $r->post('/admin/posts/{id}/delete', $sys([\App\Controllers\Admin\PostController::class, 'destroy']));

    $r->get('/admin/contact-page', $sys([\App\Controllers\Admin\ContactPageController::class, 'index']));
    $r->post('/admin/contact-page', $sys([\App\Controllers\Admin\ContactPageController::class, 'save']));

    $r->get('/admin/pages', $sys([AdminPage::class, 'index']));
    $r->get('/admin/pages/create', $sys([AdminPage::class, 'create']));
    $r->post('/admin/pages', $sys([AdminPage::class, 'store']));
    $r->get('/admin/pages/{id}/edit', $sys([AdminPage::class, 'edit']));
    $r->post('/admin/pages/{id}', $sys([AdminPage::class, 'update']));
    $r->post('/admin/pages/{id}/delete', $sys([AdminPage::class, 'destroy']));

    $r->get('/admin/sales-points', $sys([AdminSalesPoint::class, 'index']));
    $r->get('/admin/sales-points/create', $sys([AdminSalesPoint::class, 'create']));
    $r->post('/admin/sales-points', $sys([AdminSalesPoint::class, 'store']));
    $r->get('/admin/sales-points/{id}/edit', $sys([AdminSalesPoint::class, 'edit']));
    $r->post('/admin/sales-points/{id}', $sys([AdminSalesPoint::class, 'update']));
    $r->post('/admin/sales-points/{id}/delete', $sys([AdminSalesPoint::class, 'destroy']));

    $r->get('/admin/menus', $sys([AdminMenu::class, 'index']));
    $r->post('/admin/menus', $sys([AdminMenu::class, 'save']));

    $r->get('/admin/languages', $sys([AdminLanguage::class, 'index']));
    $r->post('/admin/languages', $sys([AdminLanguage::class, 'store']));
    $r->post('/admin/languages/{id}', $sys([AdminLanguage::class, 'update']));
    $r->post('/admin/languages/{id}/delete', $sys([AdminLanguage::class, 'destroy']));

    $r->get('/admin/translations', $sys([AdminTranslation::class, 'index']));
    $r->post('/admin/translations', $sys([AdminTranslation::class, 'save']));

    $r->get('/admin/quotes', $sys([AdminQuote::class, 'index']));
    $r->get('/admin/quotes/{id}', $sys([AdminQuote::class, 'show']));
    $r->post('/admin/quotes/{id}/status', $sys([AdminQuote::class, 'setStatus']));
    $r->post('/admin/quotes/{id}/delete', $sys([AdminQuote::class, 'destroy']));
    $r->get('/admin/quotes-export', $sys([AdminQuote::class, 'export']));

    $r->get('/admin/redirects', $sys([AdminRedirect::class, 'index']));
    $r->post('/admin/redirects', $sys([AdminRedirect::class, 'store']));
    $r->post('/admin/redirects/{id}/delete', $sys([AdminRedirect::class, 'destroy']));

    $r->get('/admin/settings', $sys([AdminSetting::class, 'index']));
    $r->post('/admin/settings', $sys([AdminSetting::class, 'save']));

    $r->get('/admin/users', $sys([AdminUser::class, 'index']));
    $r->post('/admin/users', $sys([AdminUser::class, 'store']));
    $r->post('/admin/users/{id}', $sys([AdminUser::class, 'update']));
    $r->post('/admin/users/{id}/delete', $sys([AdminUser::class, 'destroy']));
};
