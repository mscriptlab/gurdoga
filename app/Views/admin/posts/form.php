<?php
/** @var array $post @var array $translations @var array $languages */
use App\Core\View;
$isNew = empty($post['id']);
$title = $isNew ? 'Yeni yazı' : 'Yazıyı düzenle';
$action = $isNew ? url('/admin/posts') : url('/admin/posts/' . $post['id']);
?>
<a class="back-link" href="<?= e(url('/admin/posts')) ?>">&larr; Blog Yazıları</a>
<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="form">
  <?= csrf_field() ?>
  <div class="row">
    <label class="col"><span>Yazar</span><input type="text" name="author" value="<?= e($post['author'] ?? '') ?>" placeholder="Gürdoğa Kooperatifi"></label>
    <label class="col col-sm"><span>Yayın tarihi</span><input type="date" name="published_at" value="<?= e(!empty($post['published_at']) ? date('Y-m-d', strtotime($post['published_at'])) : date('Y-m-d')) ?>"></label>
    <label class="chk"><input type="checkbox" name="is_active" value="1" <?= ($post['is_active'] ?? 1) ? 'checked' : '' ?>> Yayında</label>
  </div>
  <label><span>Kapak görseli</span><input type="file" name="cover" accept="image/*"></label>
  <?php if (!empty($post['cover_image'])): ?><img class="thumb-lg" src="<?= e(media($post['cover_image'])) ?>" alt=""><?php endif; ?>

  <h3>İçerik &amp; SEO (dile göre)</h3>
  <?= View::renderPartial('admin/partials/translatable', [
      'languages' => $languages, 'translations' => $translations, 'namePrefix' => 'tr',
      'fields' => [
          ['name' => 'title', 'label' => 'Başlık', 'type' => 'text'],
          ['name' => 'slug', 'label' => 'URL adresi (boş = otomatik)', 'type' => 'text'],
          ['name' => 'excerpt', 'label' => 'Özet / giriş', 'type' => 'textarea'],
          ['name' => 'body', 'label' => 'İçerik', 'type' => 'rte'],
          ['name' => 'meta_title', 'label' => 'Meta başlık (SEO)', 'type' => 'text'],
          ['name' => 'meta_description', 'label' => 'Meta açıklama (SEO)', 'type' => 'text'],
      ],
  ]) ?>
  <div class="form-actions"><button class="btn-primary" type="submit">Kaydet</button></div>
</form>
