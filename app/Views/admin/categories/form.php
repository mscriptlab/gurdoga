<?php
/** @var array $category @var array $translations @var array $categories @var array $languages */
use App\Core\View;
$isNew = empty($category['id']);
$title = $isNew ? 'Yeni kategori' : 'Kategoriyi düzenle';
$action = $isNew ? url('/admin/categories') : url('/admin/categories/' . $category['id']);
?>
<a class="back-link" href="<?= e(url('/admin/categories')) ?>">&larr; Kategoriler</a>
<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="form">
  <?= csrf_field() ?>
  <div class="row">
    <label class="col"><span>Sistem anahtarı / yedek URL (boş = otomatik)</span><input type="text" name="slug" value="<?= e($category['slug']) ?>"></label>
    <label class="col col-sm"><span>Sıra</span><input type="number" name="sort" value="<?= (int) $category['sort'] ?>"></label>
    <label class="chk"><input type="checkbox" name="is_active" value="1" <?= $category['is_active'] ? 'checked' : '' ?>> Yayında</label>
  </div>
  <label><span>Görsel</span><input type="file" name="image" accept="image/*"></label>
  <?php if (!empty($category['image'])): ?><img class="thumb-lg" src="<?= e(media($category['image'])) ?>" alt=""><?php endif; ?>

  <h3>İçerik &amp; SEO (dile göre)</h3>
  <?= View::renderPartial('admin/partials/translatable', [
      'languages' => $languages, 'translations' => $translations, 'namePrefix' => 'tr',
      'fields' => [
          ['name' => 'name', 'label' => 'Kategori adı', 'type' => 'text'],
          ['name' => 'slug', 'label' => 'URL adresi (bu dilde) — boş = addan üretilir', 'type' => 'text'],
          ['name' => 'description', 'label' => 'Açıklama', 'type' => 'textarea'],
          ['name' => 'meta_title', 'label' => 'Meta başlık (SEO)', 'type' => 'text'],
          ['name' => 'meta_description', 'label' => 'Meta açıklama (SEO)', 'type' => 'text'],
      ],
  ]) ?>
  <div class="form-actions"><button class="btn-primary" type="submit">Kaydet</button></div>
</form>
