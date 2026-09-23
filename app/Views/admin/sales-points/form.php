<?php
/** @var array $point */
$isNew = empty($point['id']);
$title = $isNew ? 'Yeni satış noktası' : 'Satış noktasını düzenle';
$action = $isNew ? url('/admin/sales-points') : url('/admin/sales-points/' . $point['id']);
?>
<a class="back-link" href="<?= e(url('/admin/sales-points')) ?>">&larr; Satış Noktaları</a>

<form method="post" action="<?= e($action) ?>" class="form">
  <?= csrf_field() ?>
  <div class="row">
    <label class="col"><span>Ad</span><input type="text" name="name" value="<?= e($point['name']) ?>" placeholder="ör. Gürdoğa Satış Mağazası" required></label>
    <label class="col"><span>Kanal</span><input type="text" name="channel" value="<?= e($point['channel']) ?>" placeholder="ör. Kendi Mağazamız, Lezzet Kurye, Pazar Yeri"></label>
  </div>
  <div class="row">
    <label class="col"><span>Şehir</span><input type="text" name="city" value="<?= e($point['city']) ?>"></label>
    <label class="col"><span>İlçe</span><input type="text" name="district" value="<?= e($point['district']) ?>"></label>
    <label class="col col-sm"><span>Sıra</span><input type="number" name="sort" value="<?= (int) $point['sort'] ?>"></label>
  </div>
  <label><span>Adres</span><textarea name="address" rows="2"><?= e($point['address']) ?></textarea></label>
  <div class="row">
    <label class="col"><span>Telefon</span><input type="text" name="phone" value="<?= e($point['phone']) ?>"></label>
    <label class="col col-sm"><span>Enlem (lat)</span><input type="text" name="lat" value="<?= e((string) $point['lat']) ?>" placeholder="39.9255"></label>
    <label class="col col-sm"><span>Boylam (lng)</span><input type="text" name="lng" value="<?= e((string) $point['lng']) ?>" placeholder="32.8663"></label>
  </div>
  <p class="muted">Enlem/boylam için Google Haritalar'da konuma sağ tıklayıp koordinatları kopyalayabilirsiniz. Boş bırakılırsa bu nokta haritada gösterilmez, sadece listede görünür.</p>
  <div class="row">
    <label class="chk"><input type="checkbox" name="is_active" value="1" <?= $point['is_active'] ? 'checked' : '' ?>> Yayında (sitede görünür)</label>
  </div>
  <div class="form-actions"><button class="btn-primary" type="submit">Kaydet</button></div>
</form>

<?php if (!$isNew): ?>
<form method="post" action="<?= e(url('/admin/sales-points/' . $point['id'] . '/delete')) ?>" onsubmit="return confirm('Bu satış noktası kalıcı olarak silinsin mi?')" class="danger-zone">
  <?= csrf_field() ?><button type="submit" class="link danger">Satış noktasını sil</button>
</form>
<?php endif; ?>
