<?php
/** @var string $content */
use App\Core\Lang;
use App\Core\Seo;
use App\Core\Settings;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;

$curLang     = Lang::current();
$cats        = Category::activeList();
$pages       = Page::all();
$activePages = array_values(array_filter($pages, fn($p) => (int) $p['is_active'] === 1));
$corporatePages = array_values(array_filter($activePages, fn($p) => ($p['nav_placement'] ?? 'corporate') === 'corporate'));
$topPages       = array_values(array_filter($activePages, fn($p) => ($p['nav_placement'] ?? '') === 'top'));
$ga   = Settings::raw('ga4_id');
$gsc  = Settings::raw('gsc_verification');
$logo = Settings::raw('logo');
$alts = Seo::$alternates;
$megaImg = '';
foreach ($cats as $c) { if (!empty($c['image'])) { $megaImg = $c['image']; break; } }

$chev = '<svg class="chev" viewBox="0 0 12 12" fill="none" aria-hidden="true"><path d="M2.5 4.5 6 8l3.5-3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';

$socials = [
  'linkedin'  => ['Gürdoğa Kooperatifi · LinkedIn', '<path d="M4.98 3.5a2.5 2.5 0 11-.02 5 2.5 2.5 0 01.02-5zM3 8.98h4V21H3zM9 8.98h3.8v1.64h.05c.53-1 1.83-2.05 3.77-2.05 4.03 0 4.78 2.65 4.78 6.1V21h-4v-5.32c0-1.27-.02-2.9-1.77-2.9-1.77 0-2.04 1.38-2.04 2.8V21H9z"/>'],
  'instagram' => ['Gürdoğa Kooperatifi · Instagram', '<path d="M12 2.2c3.2 0 3.6 0 4.85.07 1.17.05 1.8.25 2.23.42.56.22.96.48 1.38.9.42.42.68.82.9 1.38.17.42.37 1.06.42 2.23.06 1.27.07 1.65.07 4.85s0 3.58-.07 4.85c-.05 1.17-.25 1.8-.42 2.23a3.7 3.7 0 01-.9 1.38 3.7 3.7 0 01-1.38.9c-.42.17-1.06.37-2.23.42-1.27.06-1.65.07-4.85.07s-3.58 0-4.85-.07c-1.17-.05-1.8-.25-2.23-.42a3.7 3.7 0 01-1.38-.9 3.7 3.7 0 01-.9-1.38c-.17-.42-.37-1.06-.42-2.23C2.2 15.58 2.2 15.2 2.2 12s0-3.58.07-4.85c.05-1.17.25-1.8.42-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.17 1.06-.37 2.23-.42C8.42 2.2 8.8 2.2 12 2.2zm0 3.05A6.75 6.75 0 1012 18.75 6.75 6.75 0 0012 5.25zm0 11.13a4.38 4.38 0 110-8.76 4.38 4.38 0 010 8.76zm6.9-11.4a1.58 1.58 0 11-3.15 0 1.58 1.58 0 013.15 0z"/>'],
  'facebook'  => ['Gürdoğa Kooperatifi · Facebook', '<path d="M14 8.5h2.5V5h-2.5C11.8 5 10 6.8 10 9v2H8v3.5h2V22h3.5v-7.5H16l.5-3.5h-3V9c0-.3.2-.5.5-.5z"/>'],
  'youtube'   => ['Gürdoğa Kooperatifi · YouTube', '<path d="M22 12s0-3.2-.4-4.7a2.6 2.6 0 00-1.8-1.8C18.3 5 12 5 12 5s-6.3 0-7.8.5a2.6 2.6 0 00-1.8 1.8C2 8.8 2 12 2 12s0 3.2.4 4.7c.2.9.9 1.6 1.8 1.8C5.7 19 12 19 12 19s6.3 0 7.8-.5a2.6 2.6 0 001.8-1.8C22 15.2 22 12 22 12zm-12 3V9l5 3z"/>'],
];
?>
<!doctype html>
<html lang="<?= e($curLang['code']) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#1b1a17">
<script>document.documentElement.className+=" js";setTimeout(function(){document.querySelectorAll("[data-reveal]:not(.in)").forEach(function(e){e.classList.add("in")})},2600);</script>
<?php if ($gsc): ?><meta name="google-site-verification" content="<?= e($gsc) ?>"><?php endif; ?>
<?php if ($bing = Settings::raw('bing_verification')): ?><meta name="msvalidate.01" content="<?= e($bing) ?>"><?php endif; ?>
    <?= seo() ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@500;600;700&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@500;600;700&display=swap">
<link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>?v=<?= (int) @filemtime(BASE_DIR . '/assets/css/site.css') ?>">
<?php if (!empty($preloadImage)): ?><link rel="preload" as="image" href="<?= e(media($preloadImage)) ?>" fetchpriority="high"><?php endif; ?>
<?php $favUp = Settings::raw('favicon'); ?>
<link rel="icon" type="image/x-icon" href="<?= e(url('favicon.ico')) ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?= e($favUp ? media($favUp) : asset('icons/icon-32.png')) ?>">
<link rel="icon" type="image/png" sizes="192x192" href="<?= e(asset('icons/icon-192.png')) ?>">
<link rel="apple-touch-icon" href="<?= e(asset('icons/apple-touch-icon.png')) ?>">
<link rel="manifest" href="<?= e(url('site.webmanifest')) ?>">
<link rel="alternate" type="application/rss+xml" title="Gürdoğa Kooperatifi — Blog" href="<?= e(lang_abs('rss')) ?>">
<?php if ($ga): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($ga) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= e($ga) ?>');</script>
<?php endif; ?>
</head>
<body>
<a class="skip" href="#main"><?= e(t('common.skip', 'Skip to content')) ?></a>

<div class="topbar">
  <div class="topbar-inner">
    <span class="topbar-left"><?= e(Settings::get('topbar_note', t('topbar.note', 'Gürdoğa Kooperatifi · Doğal ve yerel ürünler'))) ?></span>
    <div class="topbar-right">
      <?php if ($tph = Settings::raw('contact_phone')): ?>
        <a href="tel:<?= e(preg_replace('/\s+/', '', $tph)) ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .3 1.9.6 2.8a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.5 2.8.6a2 2 0 0 1 1.7 2Z"/></svg>
          <?= e($tph) ?></a>
      <?php endif; ?>
      <span class="topbar-sep"></span>
      <?php if ($tem = Settings::raw('contact_email')): ?>
        <a href="mailto:<?= e($tem) ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 6 10 7L22 6"/></svg>
          <?= e($tem) ?></a>
      <?php endif; ?>
      <span class="topbar-sep"></span>
      <?php foreach ($socials as $k => $s): if ($u = social_url(Settings::raw('social_' . $k))): ?>
        <a href="<?= e($u) ?>" target="_blank" rel="noopener" aria-label="<?= e($s[0]) ?>"><svg viewBox="0 0 24 24" fill="currentColor"><?= $s[1] ?></svg></a>
      <?php endif; endforeach; ?>
    </div>
  </div>
</div>

<header class="site-header">
  <div class="wrap header-inner">
    <a class="brand <?= $logo ? 'brand--img' : '' ?>" href="<?= e(lang_url('')) ?>" aria-label="Gürdoğa Kooperatifi">
      <?php if ($logo):
        $lw = (int) Settings::raw('logo_w', 0); $lh = (int) Settings::raw('logo_h', 0);
        $dispH = 46; $dispW = ($lw && $lh) ? (int) round($lw * $dispH / $lh) : 156; ?>
        <img src="<?= e(media($logo)) ?>" alt="Gürdoğa Kooperatifi" width="<?= $dispW ?>" height="<?= $dispH ?>">
      <?php else: ?>
        <svg class="brand-mark" viewBox="0 0 40 24" fill="none" aria-hidden="true">
          <path d="M3 21 L20 5 L37 21" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M20 5 V13" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
        </svg>
        <span class="brand-name">GÜRDOĞA</span>
      <?php endif; ?>
    </a>

    <nav class="primary-nav" aria-label="Primary">
      <ul>
        <li class="nav-item"><a href="<?= e(lang_url('')) ?>"><?= e(t('nav.home', 'Home')) ?></a></li>

        <li class="nav-item has-mega" data-mega>
          <button type="button" aria-expanded="false"><?= e(t('nav.products', 'Products')) ?> <?= $chev ?></button>
          <div class="mega">
            <div class="mega-inner">
              <div class="mega-visual">
                <?php if ($megaImg): ?><img src="<?= e(media($megaImg)) ?>" alt="" loading="lazy" width="600" height="600"><?php endif; ?>
                <div class="cap">
                  <span><?= e(Settings::get('site_name', 'Gürdoğa Kooperatifi')) ?></span>
                  <h3><?= e(t('home.categories', 'Collections')) ?></h3>
                </div>
              </div>
              <div class="mega-list">
                <?php foreach ($cats as $c): ?>
                  <a href="<?= e(lang_url('products/' . $c['slug'])) ?>">
                    <span><?= e($c['name'] ?: $c['slug']) ?></span>
                    <i><?= (int) Product::countByCategory((int) $c['id']) ?></i>
                  </a>
                <?php endforeach; ?>
                <div class="mega-foot">
                  <a class="link-arrow" href="<?= e(lang_url('products')) ?>"><?= e(t('products.all', 'All Products')) ?> &rarr;</a>
                </div>
              </div>
            </div>
          </div>
        </li>

        <?php if ($corporatePages): ?>
        <li class="nav-item" data-dd>
          <button type="button" aria-expanded="false"><?= e(t('nav.corporate', 'Corporate')) ?> <?= $chev ?></button>
          <ul class="dropdown">
            <?php foreach ($corporatePages as $p): ?>
              <li><a href="<?= e(lang_url('page/' . $p['slug'])) ?>"><?= etext($p['title'] ?: $p['slug']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </li>
        <?php endif; ?>

        <?php foreach ($topPages as $p): ?>
          <li class="nav-item"><a href="<?= e(lang_url('page/' . $p['slug'])) ?>"><?= etext($p['title'] ?: $p['slug']) ?></a></li>
        <?php endforeach; ?>

        <li class="nav-item"><a href="<?= e(lang_url('blog')) ?>"><?= e(t('blog.title', 'Journal')) ?></a></li>
        <li class="nav-item"><a href="<?= e(lang_url('contact')) ?>"><?= e(t('nav.contact', 'Contact')) ?></a></li>
      </ul>
    </nav>

    <div class="header-tools">
      <div class="lang" data-lang>
        <button type="button" class="lang-toggle" aria-expanded="false" aria-label="Language">
          <?= e(strtoupper($curLang['code'])) ?> <?= $chev ?>
        </button>
        <div class="lang-menu">
          <?php foreach (Lang::all() as $l): if (!$l['is_active']) continue;
            $href = $alts[$l['code']] ?? abs_url($l['code']); ?>
            <a href="<?= e($href) ?>" hreflang="<?= e($l['code']) ?>" class="<?= $l['code'] === $curLang['code'] ? 'active' : '' ?>">
              <?= e($l['name']) ?><span><?= e(strtoupper($l['code'])) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <a class="btn btn-primary nav-cta hide-sm" href="<?= e(lang_url('quote')) ?>">
        <?= e(t('nav.quote', 'Request a Quote')) ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
      </a>
      <button class="nav-toggle" data-nav-toggle aria-label="Menu" aria-expanded="false">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>
    </div>
  </div>
</header>

<nav class="mobile-nav" data-mobile-nav aria-label="Mobile">
  <a href="<?= e(lang_url('')) ?>"><?= e(t('nav.home', 'Home')) ?></a>
  <a href="<?= e(lang_url('products')) ?>"><?= e(t('nav.products', 'Products')) ?></a>
  <div class="m-group"><span><?= e(t('home.categories', 'Collections')) ?></span>
    <?php foreach ($cats as $c): ?><a href="<?= e(lang_url('products/' . $c['slug'])) ?>"><?= e($c['name'] ?: $c['slug']) ?></a><?php endforeach; ?>
  </div>
  <?php if ($corporatePages): ?><div class="m-group"><span><?= e(t('nav.corporate', 'Corporate')) ?></span>
    <?php foreach ($corporatePages as $p): ?><a href="<?= e(lang_url('page/' . $p['slug'])) ?>"><?= etext($p['title'] ?: $p['slug']) ?></a><?php endforeach; ?>
  </div><?php endif; ?>
  <?php foreach ($topPages as $p): ?><a href="<?= e(lang_url('page/' . $p['slug'])) ?>"><?= etext($p['title'] ?: $p['slug']) ?></a><?php endforeach; ?>
  <a href="<?= e(lang_url('blog')) ?>"><?= e(t('blog.title', 'Journal')) ?></a>
  <a href="<?= e(lang_url('contact')) ?>"><?= e(t('nav.contact', 'Contact')) ?></a>
  <a href="<?= e(lang_url('quote')) ?>"><?= e(t('nav.quote', 'Request a Quote')) ?></a>
  <div class="m-lang">
    <?php foreach (Lang::all() as $l): if (!$l['is_active']) continue;
      $href = $alts[$l['code']] ?? abs_url($l['code']); ?>
      <a href="<?= e($href) ?>" class="<?= $l['code'] === $curLang['code'] ? 'active' : '' ?>"><?= e(strtoupper($l['code'])) ?></a>
    <?php endforeach; ?>
  </div>
</nav>

<main id="main">
<?= view_flash() ?>
<?= $content ?>
</main>

<footer class="site-footer">
  <div class="footer-watermark" aria-hidden="true">GÜRDOĞA KOOP</div>
  <div class="wrap">
    <div class="footer-top">
      <div>
        <p class="eyebrow"><?= e(Settings::get('footer_eyebrow', t('footer.eyebrow', 'Home Textile Manufacturer'))) ?></p>
        <p class="footer-lead"><?= e(t('footer.headline_1', 'Comfort, woven')) ?><br><em><?= e(t('footer.headline_2', 'into every thread.')) ?></em></p>
      </div>
      <div class="footer-cta">
        <?php if ($em = Settings::raw('contact_email')): ?>
          <a class="mail" href="mailto:<?= e($em) ?>"><?= e($em) ?> &#8599;</a>
        <?php endif; ?>
        <a class="btn btn-gold" href="<?= e(lang_url('contact')) ?>"><?= e(t('nav.contact', 'Contact')) ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a>
      </div>
    </div>

    <div class="footer-rule"></div>

    <div class="footer-org">
      <div class="footer-brand">
        <div class="mark"><i>Z</i><b><?= e(Settings::get('site_name', 'Gürdoğa Kooperatifi')) ?></b></div>
        <div class="sub"><?= e(t('footer.tagline_caps', 'Zeytinyağı · Bal & Pekmez · Reçel & Turşu')) ?></div>
        <p><?= e(Settings::get('footer_about', '')) ?></p>
      </div>
      <div class="footer-meta">
        <span><?= e(t('form.phone', 'Phone')) ?></span>
        <?php foreach ([Settings::raw('contact_phone'), Settings::raw('contact_phone2')] as $ph): if (!$ph) continue; ?>
          <a href="tel:<?= e(preg_replace('/\s+/', '', $ph)) ?>"><?= e($ph) ?></a>
        <?php endforeach; ?>
        <?php if (!Settings::raw('contact_phone') && !Settings::raw('contact_phone2')): ?><p>—</p><?php endif; ?>
      </div>
      <div class="footer-meta">
        <span><?= e(t('form.email', 'Email')) ?></span>
        <?php if ($em2 = Settings::raw('contact_email')): ?><a href="mailto:<?= e($em2) ?>"><?= e($em2) ?></a><?php else: ?><p>—</p><?php endif; ?>
      </div>
      <div class="footer-meta">
        <span><?= e(t('footer.address', 'Address')) ?></span>
        <?php
          $o1 = trim((string) Settings::raw('office1_address'));
          $o2 = trim((string) Settings::raw('office2_address'));
        ?>
        <?php if ($o1 || $o2): ?>
          <?php if ($o1): ?><p><?php if ($ol1 = Settings::raw('office1_label')): ?><b><?= e($ol1) ?></b><br><?php endif; ?><?= nl2br(e($o1)) ?></p><?php endif; ?>
          <?php if ($o2): ?><p><?php if ($ol2 = Settings::raw('office2_label')): ?><b><?= e($ol2) ?></b><br><?php endif; ?><?= nl2br(e($o2)) ?></p><?php endif; ?>
        <?php else: ?>
          <p><?= nl2br(e(Settings::get('contact_address', '—'))) ?></p>
        <?php endif; ?>
      </div>
    </div>

    <div class="footer-rule"></div>

    <div class="footer-cols">
      <div>
        <h4><?= e(t('nav.corporate', 'Corporate')) ?></h4>
        <ul>
          <?php foreach ($activePages as $p): ?><li><a href="<?= e(lang_url('page/' . $p['slug'])) ?>"><?= etext($p['title'] ?: $p['slug']) ?></a></li><?php endforeach; ?>
        </ul>
      </div>
      <div>
        <h4><?= e(t('home.categories', 'Collections')) ?></h4>
        <ul>
          <?php foreach ($cats as $c): ?><li><a href="<?= e(lang_url('products/' . $c['slug'])) ?>"><?= e($c['name'] ?: $c['slug']) ?></a></li><?php endforeach; ?>
        </ul>
      </div>
      <div>
        <h4><?= e(t('footer.company', 'Company')) ?></h4>
        <ul>
          <li><a href="<?= e(lang_url('products')) ?>"><?= e(t('products.all', 'All Products')) ?></a></li>
          <li><a href="<?= e(lang_url('blog')) ?>"><?= e(t('blog.title', 'Journal')) ?></a></li>
          <li><a href="<?= e(lang_url('quote')) ?>"><?= e(t('nav.quote', 'Request a Quote')) ?></a></li>
          <li><a href="<?= e(lang_url('contact')) ?>"><?= e(t('nav.contact', 'Contact')) ?></a></li>
          <li><a href="<?= e(abs_url('sitemap.xml')) ?>">Sitemap</a></li>
        </ul>
      </div>
      <div>
        <h4><?= e(t('footer.follow', 'Follow')) ?></h4>
        <div class="footer-follow">
          <?php foreach ($socials as $k => $s): $u = trim((string) Settings::raw('social_' . $k)); if ($u === '') continue; ?>
            <a href="<?= e($u) ?>" rel="noopener" target="_blank" aria-label="<?= e($s[0]) ?>">
              <svg viewBox="0 0 24 24" fill="currentColor"><?= $s[1] ?></svg>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <span>&copy; <?= date('Y') ?> <?= e(Settings::get('site_name', 'Gürdoğa Kooperatifi')) ?>. <?= e(t('footer.rights', 'All rights reserved.')) ?></span>
      <div class="footer-social">
        <?php foreach ($socials as $k => $s): if ($u = social_url(Settings::raw('social_' . $k))): ?>
          <a href="<?= e($u) ?>" rel="noopener" target="_blank" aria-label="<?= e($s[0]) ?>">
            <svg viewBox="0 0 24 24" fill="currentColor"><?= $s[1] ?></svg>
          </a>
        <?php endif; endforeach; ?>
      </div>
      <span class="footer-credit"><?= e(t('footer.made_by', 'Design')) ?> <b>TeknoBursa</b> <i></i></span>
    </div>
  </div>
</footer>

<?php $waPhone = preg_replace('/[^0-9]/', '', (string) Settings::raw('contact_phone', '+90 541 281 41 95')); ?>
<?php if ($waPhone): ?>
<a class="whatsapp-fab" href="https://wa.me/<?= e($waPhone) ?>?text=<?= e(rawurlencode(t('whatsapp.default_message', 'Merhaba, bilgi almak istiyorum.'))) ?>" target="_blank" rel="noopener" aria-label="WhatsApp">
  <svg viewBox="0 0 32 32" fill="currentColor"><path d="M16.004 3C9.377 3 4 8.373 4 15c0 2.386.7 4.61 1.91 6.47L4 29l7.72-1.87A11.9 11.9 0 0 0 16.004 27C22.63 27 28 21.627 28 15S22.63 3 16.004 3Zm0 21.7c-2.06 0-3.98-.57-5.62-1.56l-.4-.24-4.58 1.11 1.13-4.47-.26-.42a9.63 9.63 0 0 1-1.53-5.12c0-5.35 4.36-9.7 9.75-9.7 5.38 0 9.75 4.35 9.75 9.7 0 5.35-4.37 9.7-9.75 9.7Zm5.36-7.27c-.29-.15-1.73-.86-2-.96-.27-.1-.46-.15-.66.15-.2.29-.76.96-.93 1.16-.17.2-.34.22-.63.07-.29-.15-1.22-.45-2.32-1.44-.86-.76-1.44-1.71-1.61-2-.17-.29-.02-.45.13-.6.13-.13.29-.34.44-.51.15-.17.2-.29.29-.49.1-.2.05-.37-.02-.51-.07-.15-.66-1.59-.9-2.18-.24-.57-.48-.5-.66-.51h-.56c-.2 0-.51.07-.78.37-.27.29-1.02 1-1.02 2.44 0 1.44 1.04 2.83 1.19 3.03.15.2 2.05 3.13 4.96 4.39.69.3 1.23.48 1.65.61.69.22 1.32.19 1.82.11.55-.08 1.73-.71 1.98-1.39.24-.69.24-1.28.17-1.4-.07-.13-.26-.2-.55-.35Z"/></svg>
</a>
<?php endif; ?>
<button class="back-to-top" data-back-to-top aria-label="<?= e(t('common.back_to_top', 'Back to top')) ?>">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
</button>

<?= App\Core\View::renderPartial('site/partials/quote-modal') ?>

<script src="<?= e(asset('js/site.js')) ?>?v=<?= (int) @filemtime(BASE_DIR . '/assets/js/site.js') ?>" defer></script>

<div class="tb-cookie">

    <button
        type="button"
        class="tb-cookie-settings"
        id="tbCookieSettings"
        aria-label="Çerez ayarları"
        title="Çerez ayarları"
    >
        <svg viewBox="0 0 24 24" fill="none"
             stroke="currentColor"
             stroke-width="1.8"
             stroke-linecap="round"
             stroke-linejoin="round">
            <circle cx="12" cy="12" r="3"></circle>
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06-1.42 1.42-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V20h-2v-.08a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06-1.42-1.42.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H6v-2h.08a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82L7.2 9.62 8.62 8.2l.06.06a1.65 1.65 0 0 0 1.82.33 1.65 1.65 0 0 0 1-1.51V7h2v.08a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06 1.42 1.42-.06.06a1.65 1.65 0 0 0-.33 1.82 1.65 1.65 0 0 0 1.51 1H20v2h-.08a1.65 1.65 0 0 0-1.52 1z"/>
        </svg>
    </button>


    <div
        class="tb-cookie-banner"
        id="tbCookieBanner"
        role="dialog"
        aria-label="Çerez bildirimi"
    >

        <div class="tb-cookie-main">

            <div class="tb-cookie-icon">
                <svg viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.7"
                     stroke-linecap="round"
                     stroke-linejoin="round">

                    <path d="M20.5 13.5A8.5 8.5 0 1 1 10.5 3.5"/>
                    <path d="M10.5 3.5a4 4 0 0 0 4 4"/>
                    <path d="M14.5 7.5a4 4 0 0 0 4 4"/>
                    <circle cx="8" cy="13" r="1"/>
                    <circle cx="12" cy="17" r="1"/>
                    <circle cx="6" cy="9" r="1"/>
                </svg>
            </div>


            <div class="tb-cookie-content">

                <h3 class="tb-cookie-title">
                    Çerez tercihlerinizi yönetiyoruz
                </h3>

                <p class="tb-cookie-text">
                    Web sitemizin düzgün çalışması için gerekli çerezleri
                    kullanıyoruz. İsteğe bağlı çerezler ise site deneyimini
                    geliştirmemize, ziyaretçi istatistiklerini analiz etmemize
                    ve size daha ilgili içerikler sunmamıza yardımcı olur.
                    <a href="/cerez-politikasi">
                        Çerez Politikası
                    </a>
                </p>

            </div>

        </div>


        <div class="tb-cookie-actions">

            <button
                type="button"
                class="tb-cookie-btn tb-cookie-btn-secondary"
                id="tbCookieReject"
            >
                Sadece gerekli
            </button>

            <button
                type="button"
                class="tb-cookie-btn tb-cookie-btn-secondary"
                id="tbCookieCustomize"
            >
                Tercihleri yönet
            </button>

            <button
                type="button"
                class="tb-cookie-btn tb-cookie-btn-primary"
                id="tbCookieAccept"
            >
                Tümünü kabul et
            </button>

        </div>

    </div>


    <!-- =========================
         SETTINGS MODAL
    ========================= -->

    <div
        class="tb-cookie-overlay"
        id="tbCookieOverlay"
        role="dialog"
        aria-modal="true"
        aria-labelledby="tbCookieModalTitle"
    >

        <div class="tb-cookie-modal">

            <div class="tb-cookie-modal-header">

                <h2
                    class="tb-cookie-modal-title"
                    id="tbCookieModalTitle"
                >
                    Çerez tercihleri
                </h2>

                <button
                    type="button"
                    class="tb-cookie-close"
                    id="tbCookieClose"
                    aria-label="Kapat"
                >
                    ×
                </button>

            </div>


            <div class="tb-cookie-modal-body">

                <p class="tb-cookie-modal-intro">
                    Hangi çerez kategorilerine izin verdiğinizi
                    aşağıdan seçebilirsiniz. Tercihleriniz bu cihazda
                    saklanır ve daha sonra istediğiniz zaman
                    değiştirebilirsiniz.
                </p>


                <!-- GEREKLİ -->

                <div class="tb-cookie-category">

                    <div class="tb-cookie-category-header">

                        <div class="tb-cookie-category-info">

                            <h3 class="tb-cookie-category-title">

                                Gerekli çerezler

                                <span class="tb-cookie-required">
                                    Zorunlu
                                </span>

                            </h3>

                            <p class="tb-cookie-category-description">
                                Sitenin temel fonksiyonlarının
                                çalışması için gereklidir.
                            </p>

                        </div>

                        <label class="tb-cookie-toggle">

                            <input
                                type="checkbox"
                                id="tbCookieNecessary"
                                checked
                                disabled
                            >

                            <span class="tb-cookie-slider"></span>

                        </label>

                    </div>

                    <div class="tb-cookie-category-details">

                        Oturum, güvenlik, alışveriş sepeti,
                        dil ve kullanıcı tercihlerinin korunması
                        gibi temel işlemler için kullanılır.
                        Bu çerezler devre dışı bırakılamaz.

                    </div>

                </div>


                <!-- ANALYTICS -->

                <div class="tb-cookie-category">

                    <div class="tb-cookie-category-header">

                        <div class="tb-cookie-category-info">

                            <h3 class="tb-cookie-category-title">
                                Analitik çerezler
                            </h3>

                            <p class="tb-cookie-category-description">
                                Ziyaretçilerin siteyi nasıl kullandığını
                                anlamamıza yardımcı olur.
                            </p>

                        </div>

                        <label class="tb-cookie-toggle">

                            <input
                                type="checkbox"
                                id="tbCookieAnalytics"
                            >

                            <span class="tb-cookie-slider"></span>

                        </label>

                    </div>

                    <div class="tb-cookie-category-details">

                        <strong>Örnek:</strong>
                        Google Analytics, ölçümleme ve
                        anonim istatistik servisleri.

                    </div>

                </div>


                <!-- MARKETING -->

                <div class="tb-cookie-category">

                    <div class="tb-cookie-category-header">

                        <div class="tb-cookie-category-info">

                            <h3 class="tb-cookie-category-title">
                                Pazarlama çerezleri
                            </h3>

                            <p class="tb-cookie-category-description">
                                Reklamların ve pazarlama içeriklerinin
                                daha ilgili hale getirilmesini sağlar.
                            </p>

                        </div>

                        <label class="tb-cookie-toggle">

                            <input
                                type="checkbox"
                                id="tbCookieMarketing"
                            >

                            <span class="tb-cookie-slider"></span>

                        </label>

                    </div>

                    <div class="tb-cookie-category-details">

                        <strong>Örnek:</strong>
                        Meta Pixel, Google Ads ve yeniden
                        pazarlama teknolojileri.

                    </div>

                </div>


                <!-- FUNCTIONAL -->

                <div class="tb-cookie-category">

                    <div class="tb-cookie-category-header">

                        <div class="tb-cookie-category-info">

                            <h3 class="tb-cookie-category-title">
                                İşlevsel çerezler
                            </h3>

                            <p class="tb-cookie-category-description">
                                Siteyi kişiselleştirmek ve tercihlerinizi
                                hatırlamak için kullanılır.
                            </p>

                        </div>

                        <label class="tb-cookie-toggle">

                            <input
                                type="checkbox"
                                id="tbCookieFunctional"
                            >

                            <span class="tb-cookie-slider"></span>

                        </label>

                    </div>

                    <div class="tb-cookie-category-details">

                        Dil, bölge, görüntüleme tercihleri ve
                        geliştirilmiş kullanıcı deneyimi gibi
                        özellikleri destekler.

                    </div>

                </div>

            </div>


            <div class="tb-cookie-modal-footer">

                <button
                    type="button"
                    class="tb-cookie-btn tb-cookie-btn-secondary"
                    id="tbCookieModalReject"
                >
                    Sadece gerekli
                </button>

                <button
                    type="button"
                    class="tb-cookie-btn tb-cookie-btn-primary"
                    id="tbCookieSave"
                >
                    Tercihleri kaydet
                </button>

            </div>

        </div>

    </div>

</div>


<script>
(function () {

    "use strict";

    const COOKIE_KEY = "tb_cookie_consent_v1";

    const banner = document.getElementById("tbCookieBanner");
    const overlay = document.getElementById("tbCookieOverlay");
    const settings = document.getElementById("tbCookieSettings");

    const accept = document.getElementById("tbCookieAccept");
    const reject = document.getElementById("tbCookieReject");

    const customize = document.getElementById("tbCookieCustomize");

    const close = document.getElementById("tbCookieClose");

    const modalReject =
        document.getElementById("tbCookieModalReject");

    const save =
        document.getElementById("tbCookieSave");

    const analytics =
        document.getElementById("tbCookieAnalytics");

    const marketing =
        document.getElementById("tbCookieMarketing");

    const functional =
        document.getElementById("tbCookieFunctional");


    /*
     * --------------------------------
     * COOKIE OKUMA
     * --------------------------------
     */

    function getConsent() {

        try {

            const value =
                localStorage.getItem(COOKIE_KEY);

            if (!value) {
                return null;
            }

            return JSON.parse(value);

        } catch (error) {

            console.warn(
                "Cookie consent okunamadı.",
                error
            );

            return null;
        }
    }


    /*
     * --------------------------------
     * COOKIE KAYDETME
     * --------------------------------
     */

    function saveConsent(consent) {

        const data = {

            necessary: true,

            analytics:
                Boolean(consent.analytics),

            marketing:
                Boolean(consent.marketing),

            functional:
                Boolean(consent.functional),

            timestamp:
                new Date().toISOString()

        };

        localStorage.setItem(
            COOKIE_KEY,
            JSON.stringify(data)
        );

        /*
         * Burada Google Analytics,
         * Meta Pixel vb. sistemleri
         * çalıştırabilirsin.
         */

        applyConsent(data);

        hideBanner();

        closeModal();
    }


    /*
     * --------------------------------
     * CONSENT UYGULA
     * --------------------------------
     */

    function applyConsent(consent) {

        if (!consent) {
            return;
        }


        /*
         * ANALYTICS
         *
         * Google Analytics kodunu burada
         * dinamik olarak yükleyebilirsin.
         */

        if (consent.analytics) {

            console.log(
                "Analytics cookies enabled."
            );

            /*
             * ÖRNEK:

             loadGoogleAnalytics();

             */


        } else {

            console.log(
                "Analytics cookies disabled."
            );

        }


        /*
         * MARKETING
         */

        if (consent.marketing) {

            console.log(
                "Marketing cookies enabled."
            );

            /*
             * ÖRNEK:

             loadMetaPixel();

             */

        } else {

            console.log(
                "Marketing cookies disabled."
            );

        }


        /*
         * FUNCTIONAL
         */

        if (consent.functional) {

            console.log(
                "Functional cookies enabled."
            );

        }

    }


    /*
     * --------------------------------
     * BANNER GÖSTER
     * --------------------------------
     */

    function showBanner() {

        if (!banner) {
            return;
        }

        banner.style.display = "block";

        settings.style.display = "none";

    }


    /*
     * --------------------------------
     * BANNER GİZLE
     * --------------------------------
     */

    function hideBanner() {

        if (!banner) {
            return;
        }

        banner.style.display = "none";

        settings.style.display = "flex";

    }


    /*
     * --------------------------------
     * MODAL AÇ
     * --------------------------------
     */

    function openModal() {

        const consent = getConsent();

        if (consent) {

            analytics.checked =
                Boolean(consent.analytics);

            marketing.checked =
                Boolean(consent.marketing);

            functional.checked =
                Boolean(consent.functional);

        } else {

            analytics.checked = false;
            marketing.checked = false;
            functional.checked = false;

        }

        overlay.style.display = "flex";

        document.body.style.overflow = "hidden";

    }


    /*
     * --------------------------------
     * MODAL KAPAT
     * --------------------------------
     */

    function closeModal() {

        overlay.style.display = "none";

        document.body.style.overflow = "";

    }


    /*
     * --------------------------------
     * TÜMÜNÜ KABUL ET
     * --------------------------------
     */

    function acceptAll() {

        saveConsent({

            analytics: true,

            marketing: true,

            functional: true

        });

    }


    /*
     * --------------------------------
     * SADECE GEREKLİ
     * --------------------------------
     */

    function necessaryOnly() {

        saveConsent({

            analytics: false,

            marketing: false,

            functional: false

        });

    }


    /*
     * --------------------------------
     * EVENTLER
     * --------------------------------
     */

    accept.addEventListener(
        "click",
        acceptAll
    );


    reject.addEventListener(
        "click",
        necessaryOnly
    );


    customize.addEventListener(
        "click",
        openModal
    );


    settings.addEventListener(
        "click",
        openModal
    );


    close.addEventListener(
        "click",
        closeModal
    );


    modalReject.addEventListener(
        "click",
        necessaryOnly
    );


    save.addEventListener(
        "click",
        function () {

            saveConsent({

                analytics:
                    analytics.checked,

                marketing:
                    marketing.checked,

                functional:
                    functional.checked

            });

        }
    );


    /*
     * Overlay dışına tıklayınca kapat.
     */

    overlay.addEventListener(
        "click",
        function (event) {

            if (event.target === overlay) {
                closeModal();
            }

        }
    );


    /*
     * ESC ile kapatma
     */

    document.addEventListener(
        "keydown",
        function (event) {

            if (
                event.key === "Escape" &&
                overlay.style.display === "flex"
            ) {
                closeModal();
            }

        }
    );


    /*
     * --------------------------------
     * SAYFA BAŞLANGICI
     * --------------------------------
     */

    const consent = getConsent();

    if (consent) {

        /*
         * Kullanıcı daha önce seçim yapmış.
         */

        applyConsent(consent);

        hideBanner();

    } else {

        /*
         * İlk ziyaret.
         */

        showBanner();

    }


})();
</script>
</body>
</html>
