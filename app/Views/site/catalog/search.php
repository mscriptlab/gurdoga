<?php
/** @var string $q @var array $results */
use App\Core\View;
?>
<section class="page-hero">
  <div class="wrap">
    <p class="eyebrow" style="margin-top:26px"><?= e(t('nav.products', 'Products')) ?></p>
    <h1><?= e(t('search.title', 'Search')) ?></h1>
  </div>
</section>
<section class="section">
  <div class="wrap">
    <form class="search-form" method="get" action="<?= e(lang_url('search')) ?>">
      <input type="search" name="q" value="<?= e($q) ?>" placeholder="<?= e(t('search.placeholder', 'Search products…')) ?>" aria-label="<?= e(t('search.title', 'Search')) ?>">
      <button class="btn btn-primary" type="submit"><?= e(t('search.title', 'Search')) ?></button>
    </form>
    <?php if ($q === ''): ?>
    <?php elseif (!$results): ?>
      <p class="muted"><?= e(t('search.no_results', 'No products found.')) ?></p>
    <?php else: ?>
      <div class="p-grid">
        <?php foreach ($results as $p) { echo View::renderPartial('site/partials/product-card', ['p' => $p]); } ?>
      </div>
    <?php endif; ?>
  </div>
</section>
