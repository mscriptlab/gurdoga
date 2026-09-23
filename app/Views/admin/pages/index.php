<?php /** @var array $pages */ $title = 'Sayfalar'; ?>
<div class="bar"><span></span><a class="btn-primary" href="<?= e(url('/admin/pages/create')) ?>">+ Yeni sayfa</a></div>
<table class="tbl">
  <thead><tr><th>Başlık</th><th>URL</th><th>Durum</th><th></th></tr></thead>
  <tbody>
    <tr>
      <td><a href="<?= e(url('/admin/contact-page')) ?>">İletişim &amp; Teklif sayfası</a></td>
      <td><code>/iletişim</code> · <code>/teklif</code></td>
      <td><span class="pill pill-replied">yayında</span></td>
      <td><span class="muted" style="font-size:12px">sabit sayfa</span></td>
    </tr>
  <?php foreach ($pages as $p): ?>
    <tr>
      <td><a href="<?= e(url('/admin/pages/' . $p['id'] . '/edit')) ?>"><?= e($p['title'] ?: $p['slug']) ?></a></td>
      <td><code><?= e($p['slug']) ?></code></td>
      <td><span class="pill <?= $p['is_active'] ? 'pill-replied' : 'pill-new' ?>"><?= $p['is_active'] ? 'yayında' : 'gizli' ?></span></td>
      <td>
        <form method="post" action="<?= e(url('/admin/pages/' . $p['id'] . '/delete')) ?>" onsubmit="return confirm('Sayfa silinsin mi?')">
          <?= csrf_field() ?><button class="link danger" type="submit">sil</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<p class="muted" style="margin-top:14px">
  “İletişim &amp; Teklif sayfası” içeriği metin + ayar birleşiminden oluştuğu için ayrı bir ekrandan düzenlenir; diğer tüm sayfalar (sonradan eklenenler dahil) yukarıdaki listede görünür ve çok dilli olarak düzenlenebilir.
</p>
