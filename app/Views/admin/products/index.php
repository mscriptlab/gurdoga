<?php
/** @var array $products @var array $categories @var array $filters */
$title = 'Ürünler';
?>
<div class="bar">
  <form method="get" class="filters">
    <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Ad / stok kodu ara">
    <select name="category">
      <option value="">Tüm kategoriler</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= $c['id'] ?>" <?= (int) $filters['category'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name'] ?: $c['slug']) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn-ghost" type="submit">Filtrele</button>
  </form>
  <a class="btn-primary" href="<?= e(url('/admin/products/create')) ?>">+ Yeni ürün</a>
</div>

<table class="tbl">
  <thead><tr><th></th><th>Ad</th><th>Stok kodu</th><th>Kategori</th><th>Durum</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($products as $p):
    $catName = '';
    foreach ($categories as $c) { if ((int) $c['id'] === (int) $p['category_id']) { $catName = $c['name'] ?: $c['slug']; } } ?>
    <tr>
      <td><?php if ($p['cover_image']): ?><img class="thumb" src="<?= e(media($p['cover_image'])) ?>" alt=""><?php endif; ?></td>
      <td><a href="<?= e(url('/admin/products/' . $p['id'] . '/edit')) ?>"><?= e($p['name'] ?: '(taslak — adsız)') ?></a></td>
      <td><?= e($p['sku']) ?></td>
      <td><?= e($catName) ?></td>
      <td><span class="pill <?= $p['is_active'] ? 'pill-replied' : 'pill-new' ?>"><?= $p['is_active'] ? 'yayında' : 'taslak' ?></span></td>
      <td><a href="<?= e(url('/admin/products/' . $p['id'] . '/edit')) ?>">Düzenle</a></td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$products): ?><tr><td colspan="6" class="muted">Ürün yok. İçe aktarma betiğini çalıştırın ya da yeni ürün ekleyin.</td></tr><?php endif; ?>
  </tbody>
</table>
