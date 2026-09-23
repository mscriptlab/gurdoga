<?php /** @var array $posts */ $title = 'Blog Yazıları'; ?>
<div class="bar"><span></span><a class="btn-primary" href="<?= e(url('/admin/posts/create')) ?>">+ Yeni yazı</a></div>
<table class="tbl">
  <thead><tr><th></th><th>Başlık</th><th>Yayın tarihi</th><th>Durum</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($posts as $p): ?>
    <tr>
      <td><?php if ($p['cover_image']): ?><img class="thumb" src="<?= e(media($p['cover_image'])) ?>" alt=""><?php endif; ?></td>
      <td><a href="<?= e(url('/admin/posts/' . $p['id'] . '/edit')) ?>"><?= e($p['title'] ?: '(başlıksız)') ?></a></td>
      <td><?= e($p['published_at'] ? date('d.m.Y', strtotime($p['published_at'])) : '—') ?></td>
      <td><span class="pill <?= $p['is_active'] ? 'pill-replied' : 'pill-new' ?>"><?= $p['is_active'] ? 'yayında' : 'taslak' ?></span></td>
      <td>
        <form method="post" action="<?= e(url('/admin/posts/' . $p['id'] . '/delete')) ?>" onsubmit="return confirm('Yazı silinsin mi?')">
          <?= csrf_field() ?><button class="link danger" type="submit">sil</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$posts): ?><tr><td colspan="5" class="muted">Henüz yazı yok.</td></tr><?php endif; ?>
  </tbody>
</table>
