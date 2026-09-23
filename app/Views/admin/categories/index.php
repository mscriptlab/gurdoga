<?php /** @var array $categories */ $title = 'Kategoriler'; ?>
<div class="bar">
  <span></span>
  <a class="btn-primary" href="<?= e(url('/admin/categories/create')) ?>">+ Yeni kategori</a>
</div>
<table class="tbl">
  <thead><tr><th>Ad</th><th>URL</th><th>Sıra</th><th>Durum</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($categories as $c): ?>
    <tr>
      <td><a href="<?= e(url('/admin/categories/' . $c['id'] . '/edit')) ?>"><?= e($c['name'] ?: $c['slug']) ?></a></td>
      <td><code><?= e($c['slug']) ?></code></td>
      <td><?= (int) $c['sort'] ?></td>
      <td><span class="pill <?= $c['is_active'] ? 'pill-replied' : 'pill-new' ?>"><?= $c['is_active'] ? 'yayında' : 'gizli' ?></span></td>
      <td>
        <form method="post" action="<?= e(url('/admin/categories/' . $c['id'] . '/delete')) ?>" onsubmit="return confirm('Kategori silinsin mi?')">
          <?= csrf_field() ?><button class="link danger" type="submit">sil</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
