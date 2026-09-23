<?php
/** @var array $langs @var array $tkeys @var array $tr @var array $meta @var array $settings */
use App\Core\View;
$title = 'İletişim Sayfası';
$s = fn($k) => $settings[$k] ?? '';
?>
<p><a href="<?= e(url('/admin/pages')) ?>" class="muted">&larr; Sayfalar</a></p>
<p class="muted">Sitedeki <strong>/iletişim</strong> ve <strong>/teklif</strong> sayfalarının tüm içeriği burada.
Bu sayfalar “Sayfalar” listesinde yer almaz çünkü metin + ayar + iletişim bilgilerinin birleşiminden oluşur.</p>

<form method="post" action="<?= e(url('/admin/contact-page')) ?>" enctype="multipart/form-data" class="form">
  <?= csrf_field() ?>

  <h3>Sayfa metinleri (dile göre)</h3>
  <?= View::renderPartial('admin/partials/langtabs', ['languages' => $langs, 'scope' => 'cp']) ?>
  <div class="form" style="border-top-left-radius:0;border-top-right-radius:0;margin-top:0;box-shadow:none">
    <?php foreach ($tkeys as $key => $label): ?>
      <div class="lt-block">
        <span class="lt-key"><?= e($label) ?></span>
        <?php foreach ($langs as $l): $v = $tr[$key][(int) $l['id']] ?? ''; ?>
          <span data-langcol="cp" data-lang="<?= e($l['code']) ?>">
            <input type="text" name="t[<?= e($key) ?>][<?= $l['id'] ?>]" value="<?= e($v) ?>" placeholder="<?= e(strtoupper($l['code'])) ?>">
          </span>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
    <div class="lt-block">
      <span class="lt-key">Meta açıklama (SEO — Google’da görünür)</span>
      <?php foreach ($langs as $l): $v = $meta[(int) $l['id']] ?? ''; ?>
        <span data-langcol="cp" data-lang="<?= e($l['code']) ?>">
          <textarea name="meta[<?= $l['id'] ?>]" rows="2" maxlength="180" placeholder="<?= e(strtoupper($l['code'])) ?>"><?= e($v) ?></textarea>
        </span>
      <?php endforeach; ?>
    </div>
  </div>

  <h3>İletişim bilgileri (bilgi kartında görünür)</h3>
  <div class="row">
    <label class="col"><span>E-posta</span><input type="email" name="contact_email" value="<?= e($s('contact_email')) ?>"></label>
    <label class="col"><span>Telefon 1</span><input type="text" name="contact_phone" value="<?= e($s('contact_phone')) ?>"></label>
    <label class="col"><span>Telefon 2</span><input type="text" name="contact_phone2" value="<?= e($s('contact_phone2')) ?>"></label>
  </div>
  <label><span>Çalışma saatleri</span><input type="text" name="working_hours" value="<?= e($s('working_hours')) ?>" placeholder="Hafta içi 08:30 – 18:00"></label>

  <div class="row">
    <label class="col"><span>1. Adres — başlık</span><input type="text" name="office1_label" value="<?= e($s('office1_label')) ?>" placeholder="Merkez Ofis"></label>
    <label class="col" style="flex:2"><span>1. Adres — açık adres</span><textarea name="office1_address" rows="2"><?= e($s('office1_address')) ?></textarea></label>
  </div>
  <div class="row">
    <label class="col"><span>2. Adres — başlık</span><input type="text" name="office2_label" value="<?= e($s('office2_label')) ?>" placeholder="Fabrika"></label>
    <label class="col" style="flex:2"><span>2. Adres — açık adres</span><textarea name="office2_address" rows="2"><?= e($s('office2_address')) ?></textarea></label>
  </div>

  <h3>Harita &amp; bildirim</h3>
  <label><span>Google Maps gömme kodu (iframe HTML) — boş bırakılırsa harita gösterilmez</span><textarea name="map_embed" rows="3"><?= e($s('map_embed')) ?></textarea></label>
  <label><span>Form gönderimlerinin düşeceği e-posta adres(ler)i — virgülle ayırın</span><input type="text" name="quote_recipients" value="<?= e($s('quote_recipients')) ?>"></label>

  <div class="form-actions"><button class="btn-primary" type="submit">Kaydet</button></div>
</form>

<p class="muted" style="margin-top:22px">
  <strong>Not:</strong> Form alan etiketleri (“Ad Soyad”, “E-posta”, “Mesaj” vb.), butonlar ve hata mesajları
  <a href="<?= e(url('/admin/translations')) ?>">Arayüz Metinleri</a> bölümünden düzenlenir.
  Sosyal medya bağlantıları ise <a href="<?= e(url('/admin/settings')) ?>">Genel Ayarlar</a>’dadır.
</p>
