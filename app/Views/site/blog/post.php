<?php
/** @var array $post @var array $crumbs @var array $recent */
use App\Core\Image;
use App\Core\View;
$date = $post['published_at'] ?: $post['created_at'];
?>
<section class="page-hero page-hero--post">
  <div class="wrap">
    <p class="eyebrow"><?= e(t('blog.eyebrow', 'Knowledge')) ?></p>
    <h1><?= etext($post['title']) ?></h1>
    <p class="post-meta">
      <?php if ($date): ?><time datetime="<?= e(date('Y-m-d', strtotime($date))) ?>"><?= e(date('d.m.Y', strtotime($date))) ?></time><?php endif; ?>
      <?php if (!empty($post['author'])): ?> &middot; <?= e($post['author']) ?><?php endif; ?>
    </p>
    <?= View::renderPartial('site/partials/breadcrumb', ['items' => $crumbs ?? []]) ?>
  </div>
</section>

<section class="section">
  <div class="wrap article-layout">
    <div class="article-main">
      <?php if (!empty($post['cover_image'])): ?>
        <figure class="article-cover">
          <?= Image::tag($post['cover_image'], $post['title'], ['width' => 1120, 'height' => 630, 'loading' => 'eager', 'sizes' => '(max-width:960px) 100vw, 760px']) ?>
        </figure>
      <?php endif; ?>
      <div class="rte article-body" data-reveal>
        <?php if (!empty($post['excerpt'])): ?><p class="article-lead"><?= e($post['excerpt']) ?></p><?php endif; ?>
        <?= $post['body'] ?>
      </div>
      <div class="article-cta">
        <p><?= e(t('blog.cta', 'Looking for a manufacturing partner?')) ?></p>
        <a class="btn btn-primary" href="<?= e(lang_url('quote')) ?>"><?= e(t('nav.quote', 'Request a Quote')) ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a>
      </div>
    </div>

    <aside class="article-side">
      <h4><?= e(t('blog.more', 'More articles')) ?></h4>
      <?php foreach ($recent as $rp): $rd = $rp['published_at'] ?: ($rp['created_at'] ?? null); ?>
        <a class="side-post" href="<?= e(lang_url('blog/' . $rp['slug'])) ?>">
          <?php if (!empty($rp['cover_image'])): ?>
            <img class="side-post-img" src="<?= e(media(preg_replace('/\.jpg$/', '-400.jpg', $rp['cover_image']))) ?>" alt="" loading="lazy" width="84" height="64">
          <?php endif; ?>
          <span class="side-post-b">
            <?php if ($rd): ?><time><?= e(date('d.m.Y', strtotime($rd))) ?></time><?php endif; ?>
            <h5><?= etext($rp['title']) ?></h5>
          </span>
        </a>
      <?php endforeach; ?>
      <a class="btn btn-ghost" href="<?= e(lang_url('blog')) ?>"><?= e(t('blog.title', 'Journal')) ?></a>
    </aside>
  </div>
</section>
