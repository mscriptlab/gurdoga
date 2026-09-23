<?php
/** @var array $page @var array $translations @var array $languages */
use App\Core\View;
$isNew = empty($page['id']);
$title = $isNew ? 'Yeni sayfa' : 'Sayfayı düzenle';
$action = $isNew ? url('/admin/pages') : url('/admin/pages/' . $page['id']);
?>
<a class="back-link" href="<?= e(url('/admin/pages')) ?>">&larr; Sayfalar</a>
<form method="post" action="<?= e($action) ?>" class="form">
  <?= csrf_field() ?>
  <div class="row">
    <label class="col"><span>Sistem anahtarı / yedek URL (boş = otomatik)</span><input type="text" name="slug" value="<?= e($page['slug']) ?>"></label>
    <label class="col col-sm"><span>Sıra</span><input type="number" name="sort" value="<?= (int) $page['sort'] ?>"></label>
    <label class="chk"><input type="checkbox" name="is_active" value="1" <?= $page['is_active'] ? 'checked' : '' ?>> Yayında</label>
  </div>
  <input type="hidden" name="template" value="default">

  <h3>Menüde göster</h3>
  <p class="muted">Bu sayfanın üst menüde (ve mobil menüde) nerede görüneceğini seç.</p>
  <label>
    <span>Menü yeri</span>
    <select name="nav_placement">
      <option value="corporate" <?= ($page['nav_placement'] ?? 'corporate') === 'corporate' ? 'selected' : '' ?>>Kurumsal menüsü altında (mevcut davranış)</option>
      <option value="top" <?= ($page['nav_placement'] ?? '') === 'top' ? 'selected' : '' ?>>Üst menüde ayrı bağlantı olarak (Kurumsal'ın yanına, kendi başlığıyla)</option>
      <option value="none" <?= ($page['nav_placement'] ?? '') === 'none' ? 'selected' : '' ?>>Menüde gösterme (sadece doğrudan linkle erişilsin)</option>
    </select>
  </label>

  <h3>İçerik &amp; SEO (dile göre)</h3>
  <?= View::renderPartial('admin/partials/translatable', [
      'languages' => $languages, 'translations' => $translations, 'namePrefix' => 'tr',
      'fields' => [
          ['name' => 'title', 'label' => 'Başlık', 'type' => 'text'],
          ['name' => 'slug', 'label' => 'URL adresi (bu dilde) — boş = başlıktan üretilir', 'type' => 'text'],
          ['name' => 'body', 'label' => 'İçerik', 'type' => 'rte'],
          ['name' => 'meta_title', 'label' => 'Meta başlık (SEO)', 'type' => 'text'],
          ['name' => 'meta_description', 'label' => 'Meta açıklama (SEO)', 'type' => 'text'],
          ['name' => 'og_image', 'label' => 'Paylaşım görseli yolu (uploads/...)', 'type' => 'text'],
      ],
  ]) ?>
  <div class="form-actions"><button class="btn-primary" type="submit">Kaydet</button></div>
</form>
