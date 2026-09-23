<?php /** @var array $langs */ $title = 'Diller'; ?>
<table class="tbl">
  <thead><tr><th>Kod</th><th>Ad</th><th>Locale</th><th>Sıra</th><th>Varsayılan</th><th>Aktif</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($langs as $l): ?>
    <tr>
      <td><form method="post" action="<?= e(url('/admin/languages/' . $l['id'])) ?>" class="inline-form"><?= csrf_field() ?><code><?= e($l['code']) ?></code></td>
      <td><input type="text" name="name" value="<?= e($l['name']) ?>"></td>
      <td><input type="text" name="locale" value="<?= e($l['locale']) ?>" size="8"></td>
      <td><input type="number" name="sort" value="<?= (int) $l['sort'] ?>" style="width:64px"></td>
      <td><input type="checkbox" name="is_default" value="1" <?= $l['is_default'] ? 'checked' : '' ?>></td>
      <td><input type="checkbox" name="is_active" value="1" <?= $l['is_active'] ? 'checked' : '' ?>></td>
      <td>
        <button class="link" type="submit">kaydet</button></form>
        <?php if (!$l['is_default']): ?>
          <form method="post" action="<?= e(url('/admin/languages/' . $l['id'] . '/delete')) ?>" onsubmit="return confirm('Dil ve tüm çevirileri silinsin mi?')" class="inline-form">
            <?= csrf_field() ?><button class="link danger" type="submit">sil</button>
          </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<h3>Dil ekle</h3>
<form method="post" action="<?= e(url('/admin/languages')) ?>" class="form row">
  <?= csrf_field() ?>
  <label class="col col-sm"><span>Kod</span><input type="text" name="code" placeholder="fr" required></label>
  <label class="col"><span>Ad</span><input type="text" name="name" placeholder="Français" required></label>
  <label class="col col-sm"><span>Locale</span><input type="text" name="locale" placeholder="fr_FR"></label>
  <label class="col col-sm"><span>Sıra</span><input type="number" name="sort" value="9"></label>
  <label class="chk"><input type="checkbox" name="is_active" value="1" checked> Aktif</label>
  <button class="btn-primary" type="submit">Ekle</button>
</form>
<p class="muted">Dil ekledikten sonra her Ürün / Kategori / Sayfa / Blog kaydını açıp yeni dil sekmesini doldurun. Arayüz metinleri için “Arayüz Metinleri” bölümü.</p>
