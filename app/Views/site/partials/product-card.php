<?php
/** @var array $p */
use App\Core\Image;
use App\Models\Product;
$cover = Product::cover($p);
$name  = $p['name'] ?: ($p['slug'] ?? '');
$link  = lang_url('product/' . ($p['slug'] ?? ''));
?>
<article class="p-card">
  <a href="<?= e($link) ?>" class="p-card-media">
    <?php if ($cover): ?>
      <?= Image::tag($cover, $name, ['class' => 'p-card-img', 'width' => 800, 'height' => 800, 'sizes' => '(max-width:640px) 100vw, 300px']) ?>
    <?php else: ?><span class="p-card-noimg">Gürdoğa</span><?php endif; ?>
  </a>
  <div class="p-card-body">
    <h3><a href="<?= e($link) ?>"><?= e($name) ?></a></h3>
    <?php if (!empty($p['short_desc'])): ?><p><?= e($p['short_desc']) ?></p><?php endif; ?>
    <a class="p-card-cta" href="<?= e($link) ?>"><?= e(t('common.view', 'View')) ?> &rarr;</a>
  </div>
</article>
