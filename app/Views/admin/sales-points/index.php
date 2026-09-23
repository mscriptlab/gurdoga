<?php /** @var array $points */ $title = 'Satış Noktaları'; ?>
<a class="btn-primary" href="<?= e(url('/admin/sales-points/create')) ?>" style="display:inline-block;margin-bottom:16px">+ Yeni satış noktası</a>
<table class="tbl">
  <thead><tr><th>Ad</th><th>Kanal</th><th>Şehir / İlçe</th><th>Telefon</th><th>Yayında</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($points as $p): ?>
    <tr>
      <td><a href="<?= e(url('/admin/sales-points/' . $p['id'] . '/edit')) ?>"><?= e($p['name']) ?></a></td>
      <td><?= e($p['channel']) ?></td>
      <td><?= e($p['district']) ?><?= $p['district'] && $p['city'] ? ' · ' : '' ?><?= e($p['city']) ?></td>
      <td><?= e($p['phone']) ?></td>
      <td><?= $p['is_active'] ? 'Evet' : '—' ?></td>
      <td>
        <form method="post" action="<?= e(url('/admin/sales-points/' . $p['id'] . '/delete')) ?>" onsubmit="return confirm('Bu satış noktası silinsin mi?')">
          <?= csrf_field() ?><button class="link danger" type="submit">sil</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$points): ?><tr><td colspan="6" class="muted">Kayıt yok.</td></tr><?php endif; ?>
  </tbody>
</table>
