<?php
/** @var array $p */
use App\Core\Image;
$link = lang_url('blog/' . ($p['slug'] ?? ''));
$date = $p['published_at'] ?: ($p['created_at'] ?? null);
?>
<article class="post-card">
  <a href="<?= e($link) ?>" class="post-card-media">
    <?php if (!empty($p['cover_image'])): ?>
      <?= Image::tag($p['cover_image'], $p['title'] ?? '', ['width' => 800, 'height' => 520, 'sizes' => '(max-width:640px) 100vw, 380px']) ?>
    <?php else: ?><span class="post-card-ph">Gürdoğa</span><?php endif; ?>
  </a>
  <div class="post-card-body">
    <?php if ($date): ?><time datetime="<?= e(date('Y-m-d', strtotime($date))) ?>" class="post-date"><?= e(date('d.m.Y', strtotime($date))) ?></time><?php endif; ?>
    <h3><a href="<?= e($link) ?>"><?= etext($p['title'] ?? '') ?></a></h3>
    <?php if (!empty($p['excerpt'])): ?><p><?= e($p['excerpt']) ?></p><?php endif; ?>
    <a class="link-arrow" href="<?= e($link) ?>"><?= e(t('common.read_more', 'Read more')) ?> &rarr;</a>
  </div>
</article>
