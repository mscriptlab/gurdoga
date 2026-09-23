<?php
/**
 * Linked language tab bar. Elements elsewhere on the page carrying
 * data-langcol="<scope>" data-lang="<code>" are shown/hidden to match.
 * @var array $languages
 * @var string $scope
 */
?>
<div class="tt-tabs langtabs" data-langtabs="<?= e($scope) ?>">
  <?php foreach ($languages as $l): ?>
    <button type="button" class="tt-tab" data-langtab="<?= e($l['code']) ?>">
      <?= e(strtoupper($l['code'])) ?><?= $l['is_default'] ? ' ★' : '' ?>
    </button>
  <?php endforeach; ?>
</div>
