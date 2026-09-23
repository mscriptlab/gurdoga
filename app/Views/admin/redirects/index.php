<?php /** @var array $redirects */ $title = 'Yönlendirmeler'; ?>
<p class="muted">301/302 yönlendirmeleri. URL adresi (slug) değiştiğinde otomatik oluşur; taşınan eski URL'ler için elle ekleyin. Yollar <code><?= e(config('app.base_path')) ?></code> köküne göredir (ör. <code>/tr/eski-sayfa</code>).</p>
<form method="post" action="<?= e(url('/admin/redirects')) ?>" class="form row">
  <?= csrf_field() ?>
  <label class="col"><span>Nereden</span><input type="text" name="from_path" placeholder="/tr/eski-adres" required></label>
  <label class="col"><span>Nereye</span><input type="text" name="to_path" placeholder="/tr/yeni-adres" required></label>
  <label class="col col-sm"><span>Kod</span><select name="code"><option value="301">301 (kalıcı)</option><option value="302">302 (geçici)</option></select></label>
  <button class="btn-primary" type="submit">Ekle</button>
</form>
<table class="tbl">
  <thead><tr><th>Nereden</th><th>Nereye</th><th>Kod</th><th>Tıklanma</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($redirects as $r): ?>
    <tr>
      <td><code><?= e($r['from_path']) ?></code></td>
      <td><code><?= e($r['to_path']) ?></code></td>
      <td><?= (int) $r['code'] ?></td>
      <td><?= (int) $r['hits'] ?></td>
      <td>
        <form method="post" action="<?= e(url('/admin/redirects/' . $r['id'] . '/delete')) ?>">
          <?= csrf_field() ?><button class="link danger" type="submit">sil</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$redirects): ?><tr><td colspan="5" class="muted">Kayıt yok.</td></tr><?php endif; ?>
  </tbody>
</table>
