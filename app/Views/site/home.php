<?php
/** @var array $categories @var array $featured */
use App\Core\Image;
use App\Core\Settings;
use App\Core\View;
use App\Models\Page;
use App\Models\Slider;

$withImg = array_values(array_filter($categories, fn($c) => !empty($c['image'])));
$slides  = Slider::activeList();
$about   = Page::findBySlug('about');
$prod    = Page::findBySlug('production');
?>

<div class="hero">
  <section class="slider" data-slider aria-roledescription="carousel" aria-label="<?= e(Settings::get('site_name', 'Gürdoğa Kooperatifi')) ?>">
    <div class="slider-grid">
      <?php foreach ($slides as $k => $s): ?>
        <?php
          $pos = $k === 0 ? 'pos-main' : ($k === 1 ? 'pos-sec1' : ($k === 2 ? 'pos-sec2' : 'pos-hidden'));
          $isMain = $pos === 'pos-main';
          $tag = (!$isMain && !empty($s['link_url'])) ? 'a' : 'div';
        ?>
        <<?= $tag ?> class="slide <?= $pos ?> <?= $isMain ? 'active' : '' ?>"
            <?= $tag === 'a' ? 'href="' . e(lang_url($s['link_url'])) . '"' : '' ?>
            role="group" aria-roledescription="slide" aria-label="<?= e($s['title'] ?: ('Slide ' . ($k + 1))) ?>">
          <div class="slide-media">
            <?php if (!empty($s['image'])): ?>
              <img src="<?= e(media($s['image'])) ?>" alt="<?= e($s['title'] ?: '') ?>" width="1600" height="900"
                   <?= $isMain ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
            <?php endif; ?>
          </div>
          <div class="slide-content">
            <div class="slide-inner">
              <?php if (!empty($s['eyebrow'])): ?><p class="eyebrow tag"><?= e($s['eyebrow']) ?></p><?php endif; ?>
              <?php if ($isMain): ?>
                <h1><?= e($s['title'] ?: '') ?></h1>
                <p><?= e($s['description'] ?: '') ?></p>
                <div class="btn-row">
                  <?php if (!empty($s['link_url'])): ?>
                    <a class="btn btn-light" href="<?= e(lang_url($s['link_url'])) ?>"><?= e($s['button_text'] ?: t('common.discover', 'Discover')) ?>
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a>
                  <?php endif; ?>
                  <a class="btn btn-ghost" href="<?= e(lang_url('quote')) ?>"><?= e(t('home.cta', 'Request a Quote')) ?></a>
                </div>
              <?php else: ?>
                <h2><?= e($s['title'] ?: '') ?></h2>
                <span class="slide-more"><?= e(t('common.discover', 'Discover')) ?>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg></span>
              <?php endif; ?>
            </div>
          </div>
        </<?= $tag ?>>
      <?php endforeach; ?>

      <div class="slider-nav">
        <div class="slider-arrows">
          <button type="button" data-slide-prev aria-label="Previous"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M15 18l-6-6 6-6"/></svg></button>
          <button type="button" data-slide-next aria-label="Next"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 6l6 6-6 6"/></svg></button>
        </div>
        <span class="slider-count"><b data-slide-cur>01</b> &mdash; <?= str_pad((string) count($slides), 2, '0', STR_PAD_LEFT) ?></span>
      </div>
    </div>
  </section>
</div>

<div class="marquee" aria-hidden="true">
  <div class="marquee-track">
    <?php
    $mq = array_filter(array_map('trim', explode(',', Settings::get('marquee_items',
        'Zeytinyağı, Bal & Pekmez, Reçel & Turşu, Süt Ürünleri, Doğal Üretim, Kooperatif Güvencesi, Lezzet Kurye ile Satın Al'))));
    for ($rep = 0; $rep < 2; $rep++): ?>
      <div class="marquee-item">
        <?php foreach ($mq as $item): ?>
          <span><?= e($item) ?></span>
          <svg viewBox="0 0 8 8" fill="currentColor" aria-hidden="true"><path d="M4 0l1.2 2.8L8 4 5.2 5.2 4 8 2.8 5.2 0 4l2.8-1.2z"/></svg>
        <?php endforeach; ?>
      </div>
    <?php endfor; ?>
  </div>
</div>

<!-- intro / about -->
<section class="section">
  <div class="wrap split" data-reveal>
    <div class="split-media">
      <?php if (!empty($withImg[0]['image'])): ?>
        <?= Image::tag($withImg[0]['image'], Settings::get('site_name', 'Gürdoğa Kooperatifi'), ['width' => 720, 'height' => 900, 'sizes' => '(max-width:900px) 100vw, 560px']) ?>
      <?php else: ?>
        <div class="split-placeholder" aria-hidden="true"></div>
      <?php endif; ?>
      <div class="badge badge--text"><b><?= e(t('home.badge_n', '100%')) ?></b><span><?= e(t('home.badge_l', 'doğal')) ?></span></div>
    </div>
    <div>
      <p class="eyebrow"><?= e(t('nav.about', 'About')) ?></p>
      <h2><?= e($about['title'] ?? Settings::get('site_name', 'Gürdoğa Kooperatifi')) ?></h2>
      <p class="lead"><?= e($about['meta_description'] ?? Settings::get('home_meta_description', '')) ?></p>
      <p><?= e(excerpt($about['body'] ?? '', 340)) ?></p>
      <div class="stat-row">
        <div class="stat"><b><?= count($categories) ?></b><span><?= e(t('home.stat_lines', 'ürün grubu')) ?></span></div>
        <div class="stat"><b style="font-size:1.4rem">Kooperatif</b><span><?= e(t('home.stat_sites', 'üretici ortaklığı')) ?></span></div>
        <div class="stat"><b style="font-size:1.4rem">Lezzet Kurye</b><span><?= e(t('home.stat_certs', 'ile hızlı teslimat')) ?></span></div>
      </div>
      <p style="margin-top:28px"><a class="link-arrow" href="<?= e(lang_url('page/' . ($about['slug'] ?? 'about'))) ?>"><?= e(t('common.read_more', 'Read more')) ?> &rarr;</a></p>
    </div>
  </div>
</section>

<!-- collections -->
<section class="section" style="background:var(--paper);border-top:1px solid var(--line);border-bottom:1px solid var(--line)">
  <div class="wrap">
    <div class="coll-head" data-reveal>
      <div>
        <p class="eyebrow"><?= e(t('home.categories', 'Collections')) ?></p>
        <h2><?= e(t('home.collections_title', 'Textiles for every room')) ?></h2>
      </div>
      <a class="link-arrow" href="<?= e(lang_url('products')) ?>"><?= e(t('products.all', 'All Products')) ?> &rarr;</a>
    </div>
    <div class="coll-grid" data-reveal>
      <?php foreach ($categories as $c): ?>
        <a class="coll" href="<?= e(lang_url('products/' . $c['slug'])) ?>">
          <?php if (!empty($c['image'])): ?>
            <img src="<?= e(media($c['image'])) ?>" alt="<?= e($c['name'] ?: $c['slug']) ?>" loading="lazy">
          <?php endif; ?>
          <div class="coll-label">
            <span><?= e(Settings::get('site_name', 'Gürdoğa')) ?></span>
            <h3><?= e($c['name'] ?: $c['slug']) ?></h3>
            <span class="go"><?= e(t('common.discover', 'Discover')) ?> &rarr;</span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- production split -->
<?php if ($prod): ?>
<section class="section">
  <div class="wrap split split--reverse" data-reveal>
    <div class="split-media">
      <?php if (!empty($withImg[1]['image'] ?? $withImg[0]['image'] ?? null)): $pi = $withImg[1]['image'] ?? $withImg[0]['image']; ?>
        <?= Image::tag($pi, $prod['title'] ?: '', ['width' => 720, 'height' => 900, 'sizes' => '(max-width:900px) 100vw, 560px']) ?>
      <?php else: ?>
        <div class="split-placeholder" aria-hidden="true"></div>
      <?php endif; ?>
    </div>
    <div>
      <p class="eyebrow"><?= e(t('nav.production', 'Production')) ?></p>
      <h2><?= e($prod['title']) ?></h2>
      <p class="lead"><?= e($prod['meta_description'] ?: '') ?></p>
      <p><?= e(excerpt($prod['body'] ?? '', 340)) ?></p>
      <p style="margin-top:24px"><a class="link-arrow" href="<?= e(lang_url('page/' . ($prod['slug'] ?? 'production'))) ?>"><?= e(t('common.read_more', 'Read more')) ?> &rarr;</a></p>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- why Gürdoğa -->
<section class="strip">
  <div class="wrap">
    <div class="strip-head" data-reveal>
      <p class="eyebrow"><?= e(t('home.why_eyebrow', 'Neden Gürdoğa Kooperatifi')) ?></p>
      <h2><?= e(t('home.why_title', 'Tarladan sofraya, güvenle')) ?></h2>
    </div>
    <?php
    $icons = [
      '<path d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6"/>',
      '<path d="M12 3l7 4v5c0 5-3 8-7 9-4-1-7-4-7-9V7z"/><path d="M9 12l2 2 4-4"/>',
      '<path d="M4 7h16M4 12h16M4 17h10"/><circle cx="18" cy="17" r="3"/>',
      '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/>',
    ];
    $values = [
      [t('home.value_1_t', 'Kooperatif üretimi'), t('home.value_1_d', 'Ürünlerimiz kooperatif ortağı üreticilerden gelir, kaynağı bellidir.')],
      [t('home.value_2_t', 'Doğal & katkısız'), t('home.value_2_d', 'Katkı maddesi kullanmadan, geleneksel yöntemlerle üretim yapıyoruz.')],
      [t('home.value_3_t', 'Kolay satın alma'), t('home.value_3_d', 'Beğendiğiniz ürünü tek tıkla Lezzet Kurye üzerinden sipariş edin.')],
      [t('home.value_4_t', 'Yerinde satış noktaları'), t('home.value_4_d', 'Ürünlerimizi satış noktalarımızdan da temin edebilirsiniz.')],
    ];
    ?>
    <div class="strip-grid" data-reveal>
      <?php foreach ($values as $i => $v): ?>
        <article class="vcard">
          <span class="vcard-num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
          <span class="vcard-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><?= $icons[$i] ?></svg></span>
          <h3><?= e($v[0]) ?></h3>
          <p><?= e($v[1]) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if ($featured): ?>
<section class="section">
  <div class="wrap">
    <div class="coll-head" data-reveal>
      <div>
        <p class="eyebrow"><?= e(t('home.featured', 'Featured Products')) ?></p>
        <h2><?= e(t('home.featured_title', 'Selected from our range')) ?></h2>
      </div>
      <a class="link-arrow" href="<?= e(lang_url('products')) ?>"><?= e(t('products.all', 'All Products')) ?> &rarr;</a>
    </div>
    <div class="p-grid" data-reveal>
      <?php foreach ($featured as $p) { echo View::renderPartial('site/partials/product-card', ['p' => $p]); } ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($faq)): ?>
<section class="section faq">
  <div class="wrap">
    <div class="coll-head" data-reveal>
      <div>
        <p class="eyebrow"><?= e(t('faq.eyebrow', 'FAQ')) ?></p>
        <h2><?= e(t('faq.title', 'Frequently asked questions')) ?></h2>
      </div>
    </div>
    <div class="faq-list" data-reveal>
      <?php foreach ($faq as $i => $qa): ?>
        <details class="faq-item"<?= $i === 0 ? ' open' : '' ?>>
          <summary><?= e($qa[0]) ?><span class="faq-plus" aria-hidden="true"></span></summary>
          <div class="faq-a"><p><?= e($qa[1]) ?></p></div>
        </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="cta-band">
  <div class="wrap" data-reveal>
    <p class="eyebrow" style="color:var(--gold-soft);justify-content:center"><?= e(t('nav.quote', 'Request a Quote')) ?></p>
    <h2><?= e(t('home.cta_title', 'Let us build your next collection')) ?></h2>
    <p><?= e(t('quote.intro', 'Tell us what you need and we will prepare an offer.')) ?></p>
    <a class="btn btn-gold" href="<?= e(lang_url('quote')) ?>" data-quote-open><?= e(t('form.send', 'Send')) ?> &rarr;</a>
  </div>
</section>