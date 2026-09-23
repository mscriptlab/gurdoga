<?php
/** @var array $product @var ?array $category @var ?string $catName @var array $images @var ?string $cover @var array $attributes @var array $crumbs */
use App\Core\Image;
use App\Core\View;
$gallery = $images ?: ($cover ? [['path' => $cover, 'alt' => $product['name']]] : []);
?>
<section class="page-hero page-hero--product">
  <div class="wrap">
    <p class="eyebrow">
      <?php if ($catName): ?><a href="<?= e(lang_url('products/' . $category['slug'])) ?>"><?= e($catName) ?></a><?php else: ?><?= e(t('nav.products', 'Products')) ?><?php endif; ?>
    </p>
    <h1><?= e($product['name'] ?: $product['slug']) ?></h1>
    <?php if (!empty($product['sku'])): ?><p class="post-meta">Ref: <?= e($product['sku']) ?></p><?php endif; ?>
    <?= View::renderPartial('site/partials/breadcrumb', ['items' => $crumbs ?? []]) ?>
  </div>
</section>

<section class="wrap product">
  <div class="product-gallery">
    <?php if ($gallery): ?>
      <div class="pg-main">
        <?= Image::tag($gallery[0]['path'], $gallery[0]['alt'] ?: $product['name'], ['width' => 900, 'height' => 900, 'loading' => 'eager', 'sizes' => '(max-width:900px) 100vw, 560px']) ?>
      </div>
      <?php if (count($gallery) > 1): ?>
        <div class="pg-thumbs">
          <?php foreach ($gallery as $g): ?>
            <button type="button" class="pg-thumb" data-full="<?= e(media($g['path'])) ?>">
              <?= Image::tag($g['path'], $g['alt'] ?: $product['name'], ['width' => 160, 'height' => 160, 'sizes' => '90px']) ?>
            </button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <div class="product-info">
    <?php if (!empty($product['short_desc'])): ?><p class="lead"><?= e($product['short_desc']) ?></p><?php endif; ?>
    <?php if (!empty($product['description'])): ?><div class="rte"><?= $product['description'] ?></div><?php endif; ?>

    <?php if ($attributes): ?>
      <h2 class="specs-title"><?= e(t('product.specs', 'Specifications')) ?></h2>
      <table class="specs">
        <?php foreach ($attributes as $a): ?><tr><th><?= e($a['label']) ?></th><td><?= e($a['value']) ?></td></tr><?php endforeach; ?>
      </table>
    <?php endif; ?>

    <div class="btn-row">
      <?php if (!empty($product['buy_url'])): ?>
        <a class="btn btn-primary" href="<?= e($product['buy_url']) ?>" target="_blank" rel="noopener noreferrer nofollow">
          <?= e(t('product.buy_now', 'Satın Al')) ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
        </a>
      <?php endif; ?>
      <a class="btn <?= empty($product['buy_url']) ? 'btn-primary' : 'btn-ghost' ?>" href="<?= e(lang_url('quote') . '?product=' . (int) $product['id']) ?>"
         data-quote-open
         data-product-id="<?= (int) $product['id'] ?>"
         data-product-name="<?= e($product['name'] ?: $product['slug']) ?>">
        <?= e(t('product.request_quote', 'Request a quote for this product')) ?>
      </a>
    </div>
  </div>
</section>

<?php if (!empty($related)): ?>
<section class="section related">
  <div class="wrap">
    <div class="coll-head" data-reveal>
      <div>
        <p class="eyebrow"><?= e(t('product.related_eyebrow', 'More from Gürdoğa')) ?></p>
        <h2><?= e(t('product.related', 'You may also like')) ?></h2>
      </div>
      <?php if ($category): ?><a class="link-arrow" href="<?= e(lang_url('products/' . $category['slug'])) ?>"><?= e($catName) ?> &rarr;</a><?php endif; ?>
    </div>
    <div class="p-grid" data-reveal>
      <?php foreach ($related as $rp) { echo View::renderPartial('site/partials/product-card', ['p' => $rp]); } ?>
    </div>
  </div>
</section>
<?php endif; ?>
