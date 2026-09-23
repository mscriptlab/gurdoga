<?php /** @var array $q @var ?array $product */ $title = 'Talep #' . $q['id'];
$sl = ['new' => 'yeni', 'read' => 'okundu', 'replied' => 'yanıtlandı'];
$tl = ['quote' => 'teklif', 'contact' => 'iletişim'];
?>
<a class="back-link" href="<?= e(url('/admin/quotes')) ?>">&larr; Tüm talepler</a>
<div class="detail">
  <table class="tbl">
    <tr><th style="width:160px">Tarih</th><td><?= e(date('d.m.Y H:i', strtotime($q['created_at']))) ?></td></tr>
    <tr><th>Tür</th><td><?= e($tl[$q['type']] ?? $q['type']) ?></td></tr>
    <tr><th>Ad</th><td><?= e($q['name']) ?></td></tr>
    <tr><th>Firma</th><td><?= e($q['company']) ?></td></tr>
    <tr><th>E-posta</th><td><a href="mailto:<?= e($q['email']) ?>"><?= e($q['email']) ?></a></td></tr>
    <tr><th>Telefon</th><td><?= e($q['phone']) ?></td></tr>
    <tr><th>Ülke</th><td><?= e($q['country']) ?></td></tr>
    <tr><th>Dil</th><td><?= e(strtoupper($q['locale'])) ?></td></tr>
    <?php if ($product): ?><tr><th>Ürün</th><td><a href="<?= e(url('/admin/products/' . $product['id'] . '/edit')) ?>"><?= e($product['sku'] ?: ('#' . $product['id'])) ?></a></td></tr><?php endif; ?>
    <tr><th>IP</th><td><?= e($q['ip']) ?></td></tr>
  </table>
  <h3>Mesaj</h3>
  <div class="msg"><?= nl2br(e($q['message'])) ?></div>

  <form method="post" action="<?= e(url('/admin/quotes/' . $q['id'] . '/status')) ?>" class="form row">
    <?= csrf_field() ?>
    <label class="col col-sm"><span>Durum</span>
      <select name="status">
        <?php foreach (['new', 'read', 'replied'] as $s): ?><option value="<?= $s ?>" <?= $q['status'] === $s ? 'selected' : '' ?>><?= e($sl[$s]) ?></option><?php endforeach; ?>
      </select>
    </label>
    <button class="btn-primary" type="submit">Durumu güncelle</button>
  </form>
  <form method="post" action="<?= e(url('/admin/quotes/' . $q['id'] . '/delete')) ?>" onsubmit="return confirm('Talep silinsin mi?')" class="danger-zone">
    <?= csrf_field() ?><button class="link danger" type="submit">Talebi sil</button>
  </form>
</div>
