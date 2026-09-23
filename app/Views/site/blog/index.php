<?php
/** @var array $posts @var int $total @var int $page @var int $perPage @var array $crumbs */
use App\Core\View;
$pages = (int) ceil($total / max(1, $perPage));
?>
<section class="page-hero">
  <div class="wrap">
    <p class="eyebrow"><?= e(t('blog.eyebrow', 'Knowledge')) ?></p>
    <h1><?= e(t('blog.title', 'Journal')) ?></h1>
    <?= View::renderPartial('site/partials/breadcrumb', ['items' => $crumbs ?? []]) ?>
  </div>
</section>
<section class="section">
  <div class="wrap">
    <p class="section-intro" data-reveal><?= e(t('blog.intro', 'Kooperatifimizden haberler ve ürünlerimize dair yazılar.')) ?></p>
    <?php if (!$posts): ?>
      <p class="muted"><?= e(t('blog.empty', 'No articles yet.')) ?></p>
    <?php else: ?>
      <div class="post-grid" data-reveal>
        <?php foreach ($posts as $p) { echo View::renderPartial('site/partials/post-card', ['p' => $p]); } ?>
      </div>
    <?php endif; ?>
    <?php if ($pages > 1): ?>
      <nav class="pager" aria-label="Pagination">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
          <a class="<?= $i === $page ? 'active' : '' ?>" href="<?= e(lang_url('blog') . ($i > 1 ? '?page=' . $i : '')) ?>"><?= $i ?></a>
        <?php endfor; ?>
      </nav>
    <?php endif; ?>
  </div>
</section>
