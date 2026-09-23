<?php
/** @var array $items @var array $locations @var array $categories @var array $pages @var array $languages */
use App\Core\View;
$title = 'Menüler';

function tt_labels($str) {
    $out = [];
    foreach (explode('|', (string) $str) as $pair) {
        [$lid, $label] = array_pad(explode(':', $pair, 2), 2, '');
        if ($lid !== '') $out[(int) $lid] = $label;
    }
    return $out;
}
$types = ['home' => 'Ana sayfa', 'products' => 'Ürünler sayfası', 'contact' => 'İletişim', 'category' => 'Kategori', 'page' => 'Sayfa', 'url' => 'Özel bağlantı'];
$locNames = ['header' => 'Üst menü', 'footer' => 'Alt menü (footer)'];
?>
<p class="muted">Her satırın etiketini seçili dilde girin. Bir satırın tüm etiketlerini boş bırakırsanız o öğe silinir. Sıralama = satır sırası.</p>

<?= View::renderPartial('admin/partials/langtabs', ['languages' => $languages, 'scope' => 'menu']) ?>

<form method="post" action="<?= e(url('/admin/menus')) ?>" class="form" style="border-top-left-radius:0;border-top-right-radius:0">
  <?= csrf_field() ?>
  <?php foreach ($locations as $loc):
      $rows = $items[$loc] ?? [];
      for ($i = 0; $i < 3; $i++) { $rows[] = ['id' => 0, 'type' => 'url', 'ref_id' => '', 'url' => '', 'labels' => '']; } ?>
    <h3><?= e($locNames[$loc] ?? ucfirst($loc)) ?></h3>
    <table class="tbl menu-tbl">
      <thead><tr><th style="width:150px">Tür</th><th>Hedef</th><th style="width:280px">Etiket</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $idx => $row): $lbls = is_array($row['labels'] ?? null) ? $row['labels'] : tt_labels($row['labels'] ?? ''); $n = "items[$loc][$idx]"; ?>
        <tr>
          <td>
            <select name="<?= e($n) ?>[type]">
              <?php foreach ($types as $tv => $tl): ?><option value="<?= $tv ?>" <?= ($row['type'] ?? '') === $tv ? 'selected' : '' ?>><?= e($tl) ?></option><?php endforeach; ?>
            </select>
          </td>
          <td>
            <select name="<?= e($n) ?>[ref_id]" class="menu-ref-select" data-type-select="<?= e($n) ?>[type]">
              <option value="">— (kategori / sayfa için) —</option>
              <optgroup label="Kategoriler" data-ref-type="category">
                <?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= ($row['type'] ?? '') === 'category' && (int) ($row['ref_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>>Kategori: <?= e($c['name'] ?: $c['slug']) ?></option><?php endforeach; ?>
              </optgroup>
              <optgroup label="Sayfalar" data-ref-type="page">
                <?php foreach ($pages as $p): ?><option value="<?= $p['id'] ?>" <?= ($row['type'] ?? '') === 'page' && (int) ($row['ref_id'] ?? 0) === (int) $p['id'] ? 'selected' : '' ?>>Sayfa: <?= e($p['title'] ?: $p['slug']) ?></option><?php endforeach; ?>
              </optgroup>
            </select>
            <input type="text" name="<?= e($n) ?>[url]" value="<?= e($row['url'] ?? '') ?>" placeholder="/ozel-adres veya https://">
          </td>
          <td>
            <?php foreach ($languages as $l): ?>
              <span data-langcol="menu" data-lang="<?= e($l['code']) ?>">
                <input type="text" name="<?= e($n) ?>[label][<?= $l['id'] ?>]" value="<?= e($lbls[(int) $l['id']] ?? '') ?>" placeholder="<?= e(strtoupper($l['code'])) ?> etiket">
              </span>
            <?php endforeach; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endforeach; ?>
  <div class="form-actions"><button class="btn-primary" type="submit">Menüleri kaydet</button></div>
</form>
<script>
document.querySelectorAll('.menu-ref-select').forEach(function (sel) {
  sel.addEventListener('change', function () {
    var opt = sel.selectedOptions[0];
    var group = opt && opt.parentElement && opt.parentElement.dataset ? opt.parentElement.dataset.refType : null;
    if (!group || !opt.value) return;
    var typeSelect = document.querySelector('select[name="' + sel.dataset.typeSelect.replace(/([[\]])/g, '\\$1') + '"]');
    if (typeSelect) typeSelect.value = group;
  });
});
</script>
