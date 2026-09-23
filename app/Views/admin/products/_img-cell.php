<?php
/** @var array $im @var array $product */
$isCover = $product['cover_image'] === $im['path'];
?>
<div class="img-cell" draggable="true" data-img-id="<?= (int) $im['id'] ?>" data-path="<?= e($im['path']) ?>">
  <div class="img-cell-media">
    <img src="<?= e(media($im['path'])) ?>" alt="">
    <a class="img-dl" href="<?= e(media($im['path'])) ?>" download title="Bilgisayara indir">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0 5-5m-5 5-5-5M4 21h16"/></svg>
    </a>
  </div>
  <div class="img-cell-act" style="font-size:.7rem;color:#8a8f9c">#<?= (int) $im['id'] ?></div>
  <div class="img-cell-act">
    <?php if ($isCover): ?><span class="pill pill-read" data-cover-badge>kapak</span>
    <?php else: ?>
      <button type="button" class="link" data-make-cover="<?= (int) $im['id'] ?>">kapak yap</button>
    <?php endif; ?>
    <button type="button" class="link danger" data-delete-img="<?= (int) $im['id'] ?>">sil</button>
  </div>
</div>
