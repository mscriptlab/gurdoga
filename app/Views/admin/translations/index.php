<?php
/**
 * @var array $langs @var array $keys @var array $existing @var array $defaults
 */
use App\Core\View;
$title = 'Arayüz Metinleri';

// human labels for key groups
$groupNames = [
  'nav' => 'Menü', 'products' => 'Ürünler', 'product' => 'Ürün detay', 'home' => 'Ana sayfa',
  'footer' => 'Alt bilgi', 'form' => 'Form', 'contact' => 'İletişim', 'quote' => 'Teklif',
  'blog' => 'Blog', 'search' => 'Arama', 'common' => 'Genel', 'error' => 'Hata sayfaları',
];
$grouped = [];
foreach ($keys as $k) {
    $g = explode('.', $k)[0];
    $grouped[$g][] = $k;
}
ksort($grouped);
?>
<p class="muted">Temanın kullandığı arayüz metinleri. Boş bırakılan alan, gri renkte gösterilen İngilizce varsayılana döner. Dili yukarıdaki sekmelerden seçin.</p>

<?= View::renderPartial('admin/partials/langtabs', ['languages' => $langs, 'scope' => 'ui']) ?>

<form method="post" action="<?= e(url('/admin/translations')) ?>" class="form" style="border-top-left-radius:0;border-top-right-radius:0">
  <?= csrf_field() ?>
  <?php foreach ($grouped as $g => $gkeys): ?>
    <h3><?= e($groupNames[$g] ?? ucfirst($g)) ?></h3>
    <table class="tbl">
      <thead><tr><th style="width:34%">Anahtar</th><th>Metin</th></tr></thead>
      <tbody>
      <?php foreach ($gkeys as $k): ?>
        <tr>
          <td><code><?= e($k) ?></code></td>
          <td>
            <?php foreach ($langs as $l): $v = $existing[$k][(int) $l['id']] ?? ''; ?>
              <span data-langcol="ui" data-lang="<?= e($l['code']) ?>">
                <input type="text" name="t[<?= e($k) ?>][<?= $l['id'] ?>]" value="<?= e($v) ?>" placeholder="<?= e($defaults[$k] ?? '') ?>">
              </span>
            <?php endforeach; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endforeach; ?>
  <div class="form-actions"><button class="btn-primary" type="submit">Kaydet</button></div>
</form>
