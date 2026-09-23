<?php
/**
 * @var array $languages
 * @var array $translations  langId => row
 * @var array $fields        [ ['name'=>'name','label'=>'Name','type'=>'text'], ... ]
 * @var string $namePrefix   e.g. 'tr'  -> field name tr[<langId>][<field>]
 */
$gid = 'tt_' . bin2hex(random_bytes(3));
?>
<div class="tt" data-tt>
  <div class="tt-tabs">
    <?php foreach ($languages as $i => $l): ?>
      <button type="button" class="tt-tab <?= $i === 0 ? 'active' : '' ?>" data-tt-tab="<?= e($gid . $l['id']) ?>">
        <?= e(strtoupper($l['code'])) ?><?= $l['is_default'] ? ' ★' : '' ?>
        <?php if (!empty($l['name'])): ?><span class="muted" style="font-weight:400;text-transform:none;letter-spacing:0"> · <?= e($l['name']) ?></span><?php endif; ?>
      </button>
    <?php endforeach; ?>
  </div>
  <?php foreach ($languages as $i => $l): $row = $translations[(int) $l['id']] ?? []; ?>
    <div class="tt-panel <?= $i === 0 ? 'active' : '' ?>" id="<?= e($gid . $l['id']) ?>">
      <?php foreach ($fields as $f):
        $name = $namePrefix . '[' . $l['id'] . '][' . $f['name'] . ']';
        $val  = $row[$f['name']] ?? '';
        $type = $f['type'] ?? 'text'; ?>
        <label>
          <span><?= e($f['label']) ?></span>
          <?php if ($type === 'textarea' || $type === 'rte'): ?>
            <?php if ($type === 'rte'): $taId = $gid . $l['id'] . '_' . $f['name']; ?>
              <div class="rte-toolbar" data-rte-toolbar data-target="<?= e($taId) ?>" data-upload-url="<?= e(url('/admin/media/upload')) ?>" data-csrf="<?= e(csrf_token()) ?>">
                <button type="button" class="link" data-rte-insert-image>+ Görsel ekle</button>
                <input type="file" accept="image/*" hidden data-rte-file>
              </div>
            <?php endif; ?>
            <textarea id="<?= $type === 'rte' ? e($taId) : '' ?>" name="<?= e($name) ?>" rows="<?= $type === 'rte' ? 12 : 4 ?>" class="<?= $type === 'rte' ? 'rte-editor' : '' ?>"><?= e($val) ?></textarea>
            <?php if ($type === 'rte'): ?><small class="muted">HTML kullanılabilir: &lt;p&gt; &lt;h2&gt; &lt;h3&gt; &lt;ul&gt;&lt;li&gt; &lt;strong&gt; &lt;a href&gt; &lt;img src&gt;</small><?php endif; ?>
          <?php else: ?>
            <input type="text" name="<?= e($name) ?>" value="<?= e($val) ?>"
                   <?= ($f['name'] === 'meta_description') ? 'maxlength="180"' : '' ?>>
          <?php endif; ?>
        </label>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
</div>
