<?php
/** @var array $product @var array $translations @var array $images @var array $attrText @var array $categories @var array $languages */
use App\Core\View;
$isNew = empty($product['id']);
$title = $isNew ? 'Yeni ürün' : 'Ürünü düzenle';
$action = $isNew ? url('/admin/products') : url('/admin/products/' . $product['id']);
?>
<a class="back-link" href="<?= e(url('/admin/products')) ?>">&larr; Ürünler</a>

<form method="post" action="<?= e($action) ?>" class="form">
  <?= csrf_field() ?>
  <div class="row">
    <label class="col"><span>Kategori</span>
      <select name="category_id">
        <option value="">—</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= $c['id'] ?>" <?= (int) $product['category_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name'] ?: $c['slug']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label class="col"><span>Stok kodu / Ref</span><input type="text" name="sku" value="<?= e($product['sku']) ?>"></label>
    <label class="col col-sm"><span>Sıra</span><input type="number" name="sort" value="<?= (int) $product['sort'] ?>"></label>
  </div>
  <div class="row">
    <label class="col"><span>Satın Al linki (Lezzet Kurye)</span><input type="url" name="buy_url" value="<?= e($product['buy_url'] ?? '') ?>" placeholder="https://lezzetkurye.com/..."></label>
  </div>
  <div class="row">
    <label class="chk"><input type="checkbox" name="is_active" value="1" <?= $product['is_active'] ? 'checked' : '' ?>> Yayında (sitede görünür)</label>
    <label class="chk"><input type="checkbox" name="is_featured" value="1" <?= $product['is_featured'] ? 'checked' : '' ?>> Ana sayfada öne çıkar</label>
  </div>

  <h3>İçerik &amp; SEO (dile göre)</h3>
  <?= View::renderPartial('admin/partials/translatable', [
      'languages' => $languages, 'translations' => $translations, 'namePrefix' => 'tr',
      'fields' => [
          ['name' => 'name', 'label' => 'Ürün adı', 'type' => 'text'],
          ['name' => 'slug', 'label' => 'URL adresi (boş = otomatik)', 'type' => 'text'],
          ['name' => 'short_desc', 'label' => 'Kısa açıklama', 'type' => 'textarea'],
          ['name' => 'description', 'label' => 'Tam açıklama', 'type' => 'rte'],
          ['name' => 'meta_title', 'label' => 'Meta başlık (SEO)', 'type' => 'text'],
          ['name' => 'meta_description', 'label' => 'Meta açıklama (SEO)', 'type' => 'text'],
      ],
  ]) ?>

  <h3>Özellik tablosu (dile göre)</h3>
  <p class="muted">Her satıra bir özellik — <code>Etiket | Değer</code> (ör. <code>Kompozisyon | %100 taranmış pamuk</code>).</p>
  <div class="tt" data-tt>
    <div class="tt-tabs">
      <?php foreach ($languages as $i => $l): ?>
        <button type="button" class="tt-tab <?= $i === 0 ? 'active' : '' ?>" data-tt-tab="attr<?= $l['id'] ?>"><?= e(strtoupper($l['code'])) ?><?= $l['is_default'] ? ' ★' : '' ?></button>
      <?php endforeach; ?>
    </div>
    <?php foreach ($languages as $i => $l): ?>
      <div class="tt-panel <?= $i === 0 ? 'active' : '' ?>" id="attr<?= $l['id'] ?>">
        <textarea name="attr[<?= $l['id'] ?>]" rows="7"><?= e($attrText[(int) $l['id']] ?? '') ?></textarea>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="form-actions"><button class="btn-primary" type="submit">Kaydet</button></div>
</form>

<?php if (!$isNew): ?>
<h3>Görseller</h3>
<div class="dropzone" data-dropzone
     data-upload-url="<?= e(url('/admin/products/' . $product['id'] . '/image')) ?>"
     data-reorder-url="<?= e(url('/admin/products/' . $product['id'] . '/images/reorder')) ?>"
     data-cover-url="<?= e(url('/admin/products/' . $product['id'] . '/cover')) ?>"
     data-delete-url-tpl="<?= e(url('/admin/product-images/__ID__/delete')) ?>"
     data-csrf="<?= e(csrf_token()) ?>">
  <input type="file" name="images[]" id="dz-input" multiple accept="image/*" hidden>
  <label for="dz-input" class="dropzone-inner">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 16V4m0 0 5 5m-5-5-5 5M4 16v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3"/></svg>
    <b>Görselleri sürükle bırak</b>
    <span>ya da tıklayıp bilgisayarından seç — JPG / PNG / WebP, birden fazla dosya olabilir</span>
  </label>
</div>
<div class="img-grid" data-img-grid>
  <?php foreach ($images as $im): ?>
    <?= View::renderPartial('admin/products/_img-cell', ['im' => $im, 'product' => $product]) ?>
  <?php endforeach; ?>
</div>
<p class="muted" style="margin-top:-8px">Sırasını değiştirmek için görselleri sürükleyip bırak — otomatik kaydedilir.</p>

<?php if ($images): ?>
<form method="post" action="<?= e(url('/admin/products/' . $product['id'] . '/image-alts')) ?>" class="form">
  <?= csrf_field() ?>
  <p class="muted" style="margin-top:0">Görsel alt metinleri (SEO — her görseli kısaca tanımlayın, ör. “pembe taranmış pamuk banyo havlusu seti”).</p>
  <?php foreach ($images as $im): ?>
    <label><span>Görsel #<?= (int) $im['id'] ?></span><input type="text" name="alt[<?= (int) $im['id'] ?>]" value="<?= e($im['alt']) ?>" placeholder="Kısa görsel açıklaması"></label>
  <?php endforeach; ?>
  <div class="form-actions"><button class="btn-primary" type="submit">Alt metinlerini kaydet</button></div>
</form>
<?php endif; ?>

<form method="post" action="<?= e(url('/admin/products/' . $product['id'] . '/delete')) ?>" onsubmit="return confirm('Bu ürün kalıcı olarak silinsin mi?')" class="danger-zone">
  <?= csrf_field() ?><button type="submit" class="link danger">Ürünü sil</button>
</form>
<?php endif; ?>
