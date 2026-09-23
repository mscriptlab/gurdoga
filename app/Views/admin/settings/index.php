<?php
/** @var array $settings @var array $langs @var array $tkeys @var array $strans */
use App\Core\View;
$title = 'Genel Ayarlar';
$s = fn($k) => $settings[$k] ?? '';
$tlabels = [
  'site_tagline'              => ['Slogan (slider ilk yazı)', ''],
  'footer_eyebrow'            => ['Alt bilgi üst etiket', ''],
  'topbar_note'               => ['Üst bar notu', 'Header’ın üstündeki ince şeritte görünen kısa metin.'],
  'footer_about'              => ['Alt bilgi tanıtım metni', ''],
  'home_meta_title'           => ['Ana sayfa — meta başlık (SEO)', ''],
  'home_meta_description'     => ['Ana sayfa — meta açıklama (SEO)', ''],
  'products_meta_description' => ['Ürünler sayfası — meta açıklama (SEO)', ''],
  'contact_meta_description'  => ['İletişim sayfası — meta açıklama (SEO)', ''],
];
$longKeys = ['footer_about', 'home_meta_description', 'products_meta_description', 'contact_meta_description'];
?>
<form method="post" action="<?= e(url('/admin/settings')) ?>" enctype="multipart/form-data" class="form">
  <?= csrf_field() ?>
  <input type="hidden" name="_settings_form" value="1">

  <h3>Yayın durumu</h3>
  <label class="chk" style="align-items:flex-start">
    <input type="checkbox" name="maintenance_mode" value="1" <?= (string) $s('maintenance_mode') === '1' ? 'checked' : '' ?>>
    <span><strong>“Yapım aşamasında” sayfasını göster</strong><br>
      <span class="muted">Açıkken ziyaretçilere “çok yakında” sayfası gösterilir (noindex, HTTP 503).
      Panele girişi olan kullanıcılar siteyi normal görür. Yayına hazır olduğunuzda kapatın.</span>
    </span>
  </label>

  <h3>Kimlik</h3>
  <label><span>Site adı</span><input type="text" name="site_name" value="<?= e($s('site_name')) ?>"></label>
  <div class="row">
    <label class="col"><span>Logo <?php if ($s('logo')): ?>— <a href="<?= e(media($s('logo'))) ?>" target="_blank">mevcut</a> (boş bırakılırsa yazı logosu kullanılır)<?php endif; ?></span><input type="file" name="logo" accept="image/*"></label>
    <label class="col"><span>Favicon <?php if ($s('favicon')): ?>— <a href="<?= e(media($s('favicon'))) ?>" target="_blank">mevcut</a><?php endif; ?></span><input type="file" name="favicon" accept="image/*"></label>
  </div>

  <h3>Metinler &amp; SEO (dile göre)</h3>
  <p class="muted">Slogan, alt bilgi metinleri ve ana sayfa / ürünler / iletişim sayfalarının meta başlık &amp; açıklamaları. Dili sekmelerden seçin.</p>
  <?= View::renderPartial('admin/partials/langtabs', ['languages' => $langs, 'scope' => 'set']) ?>
  <div class="form" style="border-top-left-radius:0;border-top-right-radius:0;margin-top:0;box-shadow:none">
    <?php foreach ($tkeys as $k): $long = in_array($k, $longKeys, true); ?>
      <div class="lt-block">
        <span class="lt-key"><?= e($tlabels[$k][0] ?? $k) ?></span>
        <?php if (!empty($tlabels[$k][1])): ?><small class="muted"><?= e($tlabels[$k][1]) ?></small><?php endif; ?>
        <?php foreach ($langs as $l): $v = $strans[$k][(int) $l['id']] ?? ''; ?>
          <span data-langcol="set" data-lang="<?= e($l['code']) ?>">
            <?php if ($long): ?>
              <textarea name="t[<?= e($k) ?>][<?= $l['id'] ?>]" rows="3" placeholder="<?= e(strtoupper($l['code'])) ?>"><?= e($v) ?></textarea>
            <?php else: ?>
              <input type="text" name="t[<?= e($k) ?>][<?= $l['id'] ?>]" value="<?= e($v) ?>" placeholder="<?= e(strtoupper($l['code'])) ?>">
            <?php endif; ?>
          </span>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <h3>Slider altı kayan yazı</h3>
  <label><span>Kayan yazı öğeleri (virgülle ayırın)</span><input type="text" name="marquee_items" value="<?= e($s('marquee_items')) ?>"></label>

  <h3>Ana sayfa rakamları</h3>
  <div class="row">
    <label class="col col-sm"><span>Yıl (rozet)</span><input type="text" name="years_established" value="<?= e($s('years_established')) ?>"></label>
    <label class="col col-sm"><span>Kapasite (ton/ay)</span><input type="text" name="stat_capacity" value="<?= e($s('stat_capacity')) ?>"></label>
    <label class="col col-sm"><span>Ülke sayısı</span><input type="text" name="stat_countries" value="<?= e($s('stat_countries')) ?>"></label>
  </div>

  <h3>İletişim</h3>
  <div class="row">
    <label class="col"><span>E-posta</span><input type="email" name="contact_email" value="<?= e($s('contact_email')) ?>"></label>
    <label class="col"><span>Telefon 1</span><input type="text" name="contact_phone" value="<?= e($s('contact_phone')) ?>"></label>
    <label class="col"><span>Telefon 2</span><input type="text" name="contact_phone2" value="<?= e($s('contact_phone2')) ?>"></label>
  </div>
  <label><span>Çalışma saatleri</span><input type="text" name="working_hours" value="<?= e($s('working_hours')) ?>" placeholder="Hafta içi 08:30 – 18:00"></label>
  <div class="row">
    <label class="col"><span>1. Ofis başlığı</span><input type="text" name="office1_label" value="<?= e($s('office1_label')) ?>" placeholder="Merkez Ofis"></label>
    <label class="col" style="flex:2"><span>1. Ofis adresi</span><textarea name="office1_address" rows="2"><?= e($s('office1_address')) ?></textarea></label>
  </div>
  <div class="row">
    <label class="col"><span>2. Ofis başlığı</span><input type="text" name="office2_label" value="<?= e($s('office2_label')) ?>" placeholder="Fabrika"></label>
    <label class="col" style="flex:2"><span>2. Ofis adresi</span><textarea name="office2_address" rows="2"><?= e($s('office2_address')) ?></textarea></label>
  </div>
  <label><span>Adres (footer / eski alan — tek satır blok)</span><textarea name="contact_address" rows="3"><?= e($s('contact_address')) ?></textarea></label>
  <label><span>Harita gömme kodu (iframe HTML)</span><textarea name="map_embed" rows="3"><?= e($s('map_embed')) ?></textarea></label>
  <label><span>Teklif e-posta alıcıları (virgülle ayırın)</span><input type="text" name="quote_recipients" value="<?= e($s('quote_recipients')) ?>"></label>

  <h3>Sosyal medya</h3>
  <div class="row">
    <label class="col"><span>LinkedIn</span><input type="url" name="social_linkedin" value="<?= e($s('social_linkedin')) ?>"></label>
    <label class="col"><span>Instagram</span><input type="url" name="social_instagram" value="<?= e($s('social_instagram')) ?>"></label>
    <label class="col"><span>Facebook</span><input type="url" name="social_facebook" value="<?= e($s('social_facebook')) ?>"></label>
    <label class="col"><span>YouTube</span><input type="url" name="social_youtube" value="<?= e($s('social_youtube')) ?>"></label>
  </div>

  <h3>SEO &amp; Analitik</h3>
  <label><span>Meta anahtar kelimeler (virgülle ayırın)</span><input type="text" name="meta_keywords" value="<?= e($s('meta_keywords')) ?>"></label>
  <label><span>Varsayılan paylaşım görseli yolu (uploads/...)</span><input type="text" name="default_og_image" value="<?= e($s('default_og_image')) ?>"></label>
  <div class="row">
    <label class="col"><span>GA4 Ölçüm Kimliği</span><input type="text" name="ga4_id" value="<?= e($s('ga4_id')) ?>" placeholder="G-XXXXXXX"></label>
    <label class="col"><span>Google Search Console doğrulama</span><input type="text" name="gsc_verification" value="<?= e($s('gsc_verification')) ?>"></label>
    <label class="col"><span>Bing Webmaster doğrulama</span><input type="text" name="bing_verification" value="<?= e($s('bing_verification')) ?>"></label>
  </div>
  <label><span>Blog sayfası — meta açıklama (SEO)</span><input type="text" name="blog_meta_description" value="<?= e($s('blog_meta_description')) ?>" maxlength="180"></label>
  <label><span>robots.txt (boş = otomatik)</span><textarea name="robots_txt" rows="4"><?= e($s('robots_txt')) ?></textarea></label>

  <div class="form-actions"><button class="btn-primary" type="submit">Ayarları kaydet</button></div>
</form>
