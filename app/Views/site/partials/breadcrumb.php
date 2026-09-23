<?php
/** @var array $items  [ ['name'=>..,'url'=>absolute|null], ... ] */
if (empty($items)) return;
?>
<nav class="breadcrumb" aria-label="Breadcrumb">
  <ol>
    <?php foreach ($items as $i => $it): ?>
      <li>
        <?php if (!empty($it['url']) && $i < count($items) - 1): ?>
          <a href="<?= e($it['url']) ?>"><?= etext($it['name']) ?></a>
        <?php else: ?>
          <span aria-current="page"><?= etext($it['name']) ?></span>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ol>
</nav>
