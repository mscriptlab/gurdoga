<?php
/** @var array $page @var string $title @var array $crumbs */
use App\Core\View;
?>
<section class="page-hero">
  <div class="wrap">
    <p class="eyebrow"><?= e(t('nav.corporate', 'Corporate')) ?></p>
    <h1><?= etext($title) ?></h1>
    <?= View::renderPartial('site/partials/breadcrumb', ['items' => $crumbs ?? []]) ?>
  </div>
</section>
<article class="section">
  <div class="wrap prose" data-reveal>
    <?php if (!empty($page['meta_description'])): ?><p class="section-intro"><?= e($page['meta_description']) ?></p><?php endif; ?>
    <div class="rte"><?= $page['body'] ?? '' ?></div>
  </div>
</article>
