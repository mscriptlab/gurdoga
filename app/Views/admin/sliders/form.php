<?php
/** @var array $slider @var array $translations @var array $languages */
use App\Core\View;
$isNew = empty($slider['id']);
$title = $isNew ? 'Yeni slayt' : 'Slaytı düzenle';
$action = $isNew ? url('/admin/sliders') : url('/admin/sliders/' . $slider['id']);
?>
<a class="back-link" href="<?= e(url('/admin/sliders')) ?>">&larr; Anasayfa Slider</a>
<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="form">
  <?= csrf_field() ?>
  <div class="row">
    <label class="col col-sm"><span>Sıra</span><input type="number" name="sort" value="<?= (int) $slider['sort'] ?>"></label>
    <label class="chk"><input type="checkbox" name="is_active" value="1" <?= $slider['is_active'] ? 'checked' : '' ?>> Yayında</label>
  </div>
  <label><span>Görsel</span><input type="file" name="image" accept="image/*"></label>
  <?php if (!empty($slider['image'])): ?><img class="thumb-lg" src="<?= e(media($slider['image'])) ?>" alt=""><?php endif; ?>

  <h3>İçerik (dile göre)</h3>
  <p class="muted">Bu metinler doğrudan ana sayfadaki slider'da görünür — SEO meta açıklamasıyla ilgisi yoktur, uzunluk sınırı yoktur.</p>
  <?= View::renderPartial('admin/partials/translatable', [
      'languages' => $languages, 'translations' => $translations, 'namePrefix' => 'tr',
      'fields' => [
          ['name' => 'eyebrow',     'label' => 'Üst etiket (ör. "Gürdoğa Kooperatifi — Banyo Havlusu")', 'type' => 'text'],
          ['name' => 'title',       'label' => 'Başlık', 'type' => 'text'],
          ['name' => 'description', 'label' => 'Açıklama metni', 'type' => 'textarea'],
          ['name' => 'button_text', 'label' => 'Buton yazısı (boş = "Keşfet")', 'type' => 'text'],
          ['name' => 'link_url',    'label' => 'Buton linki (ör. products/banyo-havlusu)', 'type' => 'text'],
      ],
  ]) ?>
  <div class="form-actions"><button class="btn-primary" type="submit">Kaydet</button></div>
</form>
