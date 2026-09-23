<?php
/** @var array $stats @var array $latestQuotes */
$title = 'Genel Bakış';
$statusLabel = ['new' => 'yeni', 'read' => 'okundu', 'replied' => 'yanıtlandı'];
$typeLabel = ['quote' => 'teklif', 'contact' => 'iletişim'];
?>
<div class="cards">
  <div class="card"><span class="card-label"><i>▤</i> Toplam ürün</span><span class="card-n"><?= $stats['products'] ?></span></div>
  <div class="card"><span class="card-label"><i>●</i> Yayında ürün</span><span class="card-n"><?= $stats['active'] ?></span></div>
  <div class="card"><span class="card-label"><i>◆</i> Kategori</span><span class="card-n"><?= $stats['categories'] ?></span></div>
  <div class="card"><span class="card-label"><i>▢</i> Sayfa</span><span class="card-n"><?= $stats['pages'] ?></span></div>
  <div class="card <?= $stats['quotes_new'] ? 'card-alert' : '' ?>"><span class="card-label"><i>✉</i> Yeni talep</span><span class="card-n"><?= $stats['quotes_new'] ?></span></div>
</div>

<h2>Son teklif talepleri</h2>
<table class="tbl">
  <thead><tr><th>Tarih</th><th>Ad</th><th>Firma</th><th>E-posta</th><th>Tür</th><th>Durum</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($latestQuotes as $q): ?>
    <tr class="<?= $q['status'] === 'new' ? 'row-new' : '' ?>">
      <td><?= e(date('d.m.Y H:i', strtotime($q['created_at']))) ?></td>
      <td><?= e($q['name']) ?></td>
      <td><?= e($q['company']) ?></td>
      <td><?= e($q['email']) ?></td>
      <td><?= e($typeLabel[$q['type']] ?? $q['type']) ?></td>
      <td><span class="pill pill-<?= e($q['status']) ?>"><?= e($statusLabel[$q['status']] ?? $q['status']) ?></span></td>
      <td><a href="<?= e(url('/admin/quotes/' . $q['id'])) ?>">Aç</a></td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$latestQuotes): ?><tr><td colspan="7" class="muted">Henüz talep yok.</td></tr><?php endif; ?>
  </tbody>
</table>

<h2>Hızlı başlangıç</h2>
<ol class="help">
  <li><a href="<?= e(url('/admin/products')) ?>">Ürünler</a> bölümünden her ürünün adını, açıklamasını ve SEO alanlarını üç dilde girip “Yayında” yapın.</li>
  <li><a href="<?= e(url('/admin/settings')) ?>">Genel Ayarlar</a>: iletişim bilgileri, logo, GA4 kimliği, sosyal medya bağlantıları.</li>
  <li><a href="<?= e(url('/admin/posts')) ?>">Blog</a> yazılarını düzenleyin, <a href="<?= e(url('/admin/menus')) ?>">Menüleri</a> güncelleyin.</li>
</ol>
