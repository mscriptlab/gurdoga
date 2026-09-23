<?php
/** @var array $category @var array $products @var int $total @var int $page @var int $perPage @var array $crumbs */
use App\Core\View;
$pages = (int) ceil($total / $perPage);
?>
<section class="page-hero">
  <div class="wrap">
    <p class="eyebrow"><?= e(t('nav.products', 'Products')) ?></p>
    <h1><?= e($category['name'] ?: $category['slug']) ?></h1>
    <?= View::renderPartial('site/partials/breadcrumb', ['items' => $crumbs ?? []]) ?>
  </div>
</section>
<section class="section">
  <div class="wrap">
    <?php if (!empty($category['description'])):
      $desc = $category['description'];
      $intro = $desc;
      $specs = [];
      $keys = 'Menşe|Origin|Herkunft|Özelleştirme|Customisation|Individualisierung|Sertifikalar|Certificates|Zertifikate|Gramaj';
      if (preg_match('/(' . $keys . ')\s*:/u', $desc, $m, PREG_OFFSET_CAPTURE)) {
        $intro = trim(substr($desc, 0, $m[0][1]));
        $tail  = substr($desc, $m[0][1]);
        if (preg_match_all('/(' . $keys . ')\s*:\s*(.*?)(?=(?:' . $keys . ')\s*:|$)/us', $tail, $mm, PREG_SET_ORDER)) {
          foreach ($mm as $row) {
            $val = trim(rtrim(trim($row[2]), ". "));
            if ($val !== '') { $specs[] = [trim($row[1]), $val]; }
          }
        }
      }
    ?>
      <div class="cat-intro" data-reveal>
        <p class="section-intro"><?= e($intro) ?></p>
        <?php if ($specs): ?>
          <dl class="spec-strip">
            <?php foreach ($specs as [$sk, $sv]): ?>
              <div class="spec-chip"><dt><?= e($sk) ?></dt><dd><?= e($sv) ?></dd></div>
            <?php endforeach; ?>
          </dl>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if (!$products): ?>
      <p class="muted"><?= e(t('search.no_results', 'No products found.')) ?></p>
    <?php else: ?>
      <p class="cat-count"><?= (int) $total ?> <?= e(t('products.items', 'items')) ?></p>
      <div class="p-grid p-grid--3" data-reveal>
        <?php foreach ($products as $p) { echo View::renderPartial('site/partials/product-card', ['p' => $p]); } ?>
      </div>
    <?php endif; ?>
    <?php if ($pages > 1): ?>
      <nav class="pager" aria-label="Pagination">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
          <a class="<?= $i === $page ? 'active' : '' ?>" href="<?= e(lang_url('products/' . $category['slug']) . ($i > 1 ? '?page=' . $i : '')) ?>"><?= $i ?></a>
        <?php endfor; ?>
      </nav>
    <?php endif; ?>
  </div>
</section>
