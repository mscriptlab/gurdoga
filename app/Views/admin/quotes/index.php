<?php /** @var array $quotes @var ?string $status */ $title = 'Teklif Talepleri';
$sl = ['new' => 'yeni', 'read' => 'okundu', 'replied' => 'yanıtlandı'];
$tl = ['quote' => 'teklif', 'contact' => 'iletişim'];
?>
<div class="bar">
  <div class="filters">
    <a class="<?= !$status ? 'active' : '' ?>" href="<?= e(url('/admin/quotes')) ?>">Tümü</a>
    <a class="<?= $status === 'new' ? 'active' : '' ?>" href="<?= e(url('/admin/quotes?status=new')) ?>">Yeni</a>
    <a class="<?= $status === 'read' ? 'active' : '' ?>" href="<?= e(url('/admin/quotes?status=read')) ?>">Okundu</a>
    <a class="<?= $status === 'replied' ? 'active' : '' ?>" href="<?= e(url('/admin/quotes?status=replied')) ?>">Yanıtlandı</a>
  </div>
  <a class="btn-ghost" href="<?= e(url('/admin/quotes-export')) ?>">CSV indir</a>
</div>
<table class="tbl">
  <thead><tr><th>Tarih</th><th>Ad</th><th>Firma</th><th>E-posta</th><th>Ülke</th><th>Tür</th><th>Dil</th><th>Durum</th></tr></thead>
  <tbody>
  <?php foreach ($quotes as $q): ?>
    <tr class="<?= $q['status'] === 'new' ? 'row-new' : '' ?>">
      <td><a href="<?= e(url('/admin/quotes/' . $q['id'])) ?>"><?= e(date('d.m.Y H:i', strtotime($q['created_at']))) ?></a></td>
      <td><?= e($q['name']) ?></td><td><?= e($q['company']) ?></td><td><?= e($q['email']) ?></td>
      <td><?= e($q['country']) ?></td><td><?= e($tl[$q['type']] ?? $q['type']) ?></td><td><?= e(strtoupper($q['locale'])) ?></td>
      <td><span class="pill pill-<?= e($q['status']) ?>"><?= e($sl[$q['status']] ?? $q['status']) ?></span></td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$quotes): ?><tr><td colspan="8" class="muted">Talep yok.</td></tr><?php endif; ?>
  </tbody>
</table>
