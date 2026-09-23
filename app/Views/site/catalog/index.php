<?php
/** @var array $categories @var array $crumbs */
use App\Core\View;
?>
<section class="page-hero">
  <div class="wrap">
    <p class="eyebrow"><?= e(t('home.categories', 'Collections')) ?></p>
    <h1><?= e(t('nav.products', 'Products')) ?></h1>
    <?= View::renderPartial('site/partials/breadcrumb', ['items' => $crumbs ?? []]) ?>
  </div>
</section>
<section class="section">
  <div class="wrap">
    <p class="section-intro" data-reveal><?= e(t('products.intro', 'Explore our home textile collections.')) ?></p>
    <div class="coll-grid" data-reveal>
      <?php foreach ($categories as $c): ?>
        <a class="coll" href="<?= e(lang_url('products/' . $c['slug'])) ?>">
          <?php if (!empty($c['image'])): ?><img src="<?= e(media($c['image'])) ?>" alt="<?= e($c['name'] ?: $c['slug']) ?>" loading="lazy"><?php endif; ?>
          <div class="coll-label">
            <span><?= e((string) \App\Models\Product::countByCategory((int) $c['id'])) ?> <?= e(t('products.items', 'items')) ?></span>
            <h3><?= e($c['name'] ?: $c['slug']) ?></h3>
            <span class="go"><?= e(t('common.discover', 'Discover')) ?> &rarr;</span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
