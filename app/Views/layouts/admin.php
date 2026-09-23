<?php
/** @var string $content */
use App\Core\Auth;
$u = Auth::user();
$path = $_SERVER['REQUEST_URI'] ?? '';

$ic = [
  'grid'  => '<path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/>',
  'box'   => '<path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/>',
  'tag'   => '<path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0l-7.2-7.2A2 2 0 0 1 3 12V4a1 1 0 0 1 1-1h8a2 2 0 0 1 1.4.6l7.2 7.2a2 2 0 0 1 0 2.6z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
  'file'  => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6"/>',
  'pen'   => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
  'menu'  => '<path d="M3 6h18M3 12h18M3 18h18"/>',
  'inbox' => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5h13l3.5 7v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-6z"/>',
  'cog'   => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-2.9 1.2V21a2 2 0 1 1-4 0v-.2A1.7 1.7 0 0 0 6.3 19l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1A1.7 1.7 0 0 0 4.7 13H4a2 2 0 1 1 0-4h.2a1.7 1.7 0 0 0 1.5-2.9L5.6 6A2 2 0 1 1 8.4 3.2l.1.1A1.7 1.7 0 0 0 11 4.7V4a2 2 0 1 1 4 0v.2a1.7 1.7 0 0 0 2.9 1.2l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1A1.7 1.7 0 0 0 21 12v.7h-.7"/>',
  'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/>',
  'type'  => '<path d="M4 7V5h16v2M9 20h6M12 5v15"/>',
  'link'  => '<path d="M10 13a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1"/><path d="M14 11a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1"/>',
  'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
  'pin'   => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
];
$groups = [
  ['label' => 'Panel', 'items' => [
    ['/admin', 'Genel Bakış', 'grid'],
  ]],
  ['label' => 'Anasayfa', 'items' => [
    ['/admin/sliders', 'Slider', 'type'],
  ]],
  ['label' => 'Katalog', 'items' => [
    ['/admin/products', 'Ürünler', 'box'],
    ['/admin/categories', 'Kategoriler', 'tag'],
  ]],
  ['label' => 'İçerik', 'items' => [
    ['/admin/pages', 'Sayfalar', 'file'],
    ['/admin/sales-points', 'Satış Noktaları', 'pin'],
    ['/admin/posts', 'Blog Yazıları', 'pen'],
    ['/admin/menus', 'Menüler', 'menu'],
  ]],
  ['label' => 'Talepler', 'items' => [
    ['/admin/quotes', 'Teklif Talepleri', 'inbox'],
  ]],
  ['label' => 'Ayarlar', 'items' => [
    ['/admin/settings', 'Genel Ayarlar', 'cog'],
    ['/admin/languages', 'Diller', 'globe'],
    ['/admin/translations', 'Arayüz Metinleri', 'type'],
    ['/admin/redirects', 'Yönlendirmeler', 'link'],
    ['/admin/users', 'Kullanıcılar', 'users'],
  ]],
];
$isActive = function (string $href) use ($path): bool {
    if ($href === '/admin') {
        return rtrim($path, '/') === url('/admin');
    }
    if ($href === '/admin/pages' && strpos($path, url('/admin/contact-page')) === 0) {
        return true;
    }
    return strpos($path, url($href)) === 0;
};
$chevD = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="m6 9 6 6 6-6"/></svg>';
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= e($title ?? 'Panel') ?> — Gürdoğa Kooperatifi</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>?v=<?= (int) @filemtime(BASE_DIR . '/assets/css/admin.css') ?>">
</head>
<body>
<div class="admin">
  <aside class="a-side" data-side>
    <a class="a-brand" href="<?= e(url('/admin')) ?>"><i>G</i>GÜRDOĞA <em>Panel</em></a>
    <nav class="a-nav">
      <?php foreach ($groups as $gi => $g):
        $hasActive = false;
        foreach ($g['items'] as $it) { if ($isActive($it[0])) { $hasActive = true; break; } } ?>
        <div class="a-group" data-group data-key="g<?= $gi ?>">
          <button type="button" class="a-group-label"><?= e($g['label']) ?> <?= $chevD ?></button>
          <div class="a-group-items">
            <?php foreach ($g['items'] as [$href, $label, $icon]): ?>
              <a href="<?= e(url($href)) ?>" class="<?= $isActive($href) ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><?= $ic[$icon] ?></svg>
                <?= e($label) ?>
                <?php if ($href === '/admin/quotes' && !empty($newQuotes)): ?><span class="badge"><?= (int) $newQuotes ?></span><?php endif; ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </nav>
    <div class="a-side-foot">
      <a href="<?= e(url('/')) ?>" target="_blank">Siteyi görüntüle &#8599;</a>
    </div>
  </aside>

  <div class="a-main">
    <header class="a-top">
      <div style="display:flex;align-items:center">
        <button class="a-menu-btn" data-side-toggle aria-label="Menü">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
        </button>
        <span class="a-top-title"><?= e($title ?? 'Panel') ?><small>Gürdoğa Kooperatifi yönetim paneli</small></span>
      </div>
      <div class="a-user">
        <span class="who"><i><?= e(mb_strtoupper(mb_substr($u['name'] ?? 'A', 0, 1))) ?></i><?= e($u['name'] ?? '') ?></span>
        <a href="<?= e(url('/admin/logout')) ?>">Çıkış</a>
      </div>
    </header>
    <main class="a-content">
      <?php if (!empty($licenseNotice)): ?>
        <div class="flash flash-err" style="background:#3a2a2a;border-color:#6b3b3b;color:#e8c9c9">
          <strong>Lisans uyarısı:</strong> <?= e($licenseNotice) ?>
        </div>
      <?php endif; ?>
      <?= view_flash() ?>
      <?= $content ?>
    </main>
  </div>
</div>
<script src="<?= e(asset('js/vendor/sweetalert2.min.js')) ?>" defer></script>
<script src="<?= e(asset('js/admin.js')) ?>?v=<?= (int) @filemtime(BASE_DIR . '/assets/js/admin.js') ?>" defer></script>
</body>
</html>
