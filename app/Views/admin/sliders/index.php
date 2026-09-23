<?php /** @var array $sliders */ $title = 'Anasayfa Slider'; ?>
<div class="bar">
  <span>Sıra numarası küçük olan önce gösterilir. Ana sayfadaki tüm slaytlar burada yönetilir.</span>
  <a class="btn-primary" href="<?= e(url('/admin/sliders/create')) ?>">+ Yeni slayt</a>
</div>
<table class="tbl">
  <thead><tr><th>Görsel</th><th>Başlık</th><th>Sıra</th><th>Durum</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($sliders as $s): ?>
    <tr>
      <td><?php if (!empty($s['image'])): ?><img class="thumb" src="<?= e(media($s['image'])) ?>" alt=""><?php endif; ?></td>
      <td><a href="<?= e(url('/admin/sliders/' . $s['id'] . '/edit')) ?>"><?= e($s['title'] ?: ('Slayt #' . $s['id'])) ?></a></td>
      <td><?= (int) $s['sort'] ?></td>
      <td><span class="pill <?= $s['is_active'] ? 'pill-replied' : 'pill-new' ?>"><?= $s['is_active'] ? 'yayında' : 'gizli' ?></span></td>
      <td>
        <form method="post" action="<?= e(url('/admin/sliders/' . $s['id'] . '/delete')) ?>" onsubmit="return confirm('Slayt silinsin mi?')">
          <?= csrf_field() ?><button class="link danger" type="submit">sil</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$sliders): ?><tr><td colspan="5" class="muted">Henüz slayt yok.</td></tr><?php endif; ?>
  </tbody>
</table>
