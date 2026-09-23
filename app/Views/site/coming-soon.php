<?php
/** Standalone "under construction" splash. No site layout. Served with HTTP 503 + noindex. */
use App\Core\Settings;

$brand = Settings::get('site_name', 'Gürdoğa Kooperatifi');
$logo  = Settings::raw('logo');
$email = Settings::raw('contact_email');
$phone = Settings::raw('contact_phone');
$tagline = Settings::get('site_tagline', 'Doğal ve yerel kooperatif ürünleri');
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($brand) ?> — Çok yakında</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  :root{--bg:#faf8f4;--ink:#26251f;--soft:#6f6a5f;--gold:#b3934f;--line:#e8e2d5;--dark:#1b1a17}
  html,body{height:100%}
  body{
    font-family:"Inter",system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;
    background:var(--dark);color:#fff;min-height:100%;
    display:flex;align-items:center;justify-content:center;padding:32px;
    background-image:
      radial-gradient(ellipse 70% 60% at 15% 10%, rgba(179,147,79,.22), transparent 60%),
      repeating-linear-gradient(45deg, rgba(203,183,141,.06) 0 1px, transparent 1px 8px);
  }
  .card{max-width:560px;width:100%;text-align:center}
  .logo{height:52px;width:auto;margin:0 auto 34px;display:block;filter:brightness(0) invert(1)}
  .wordmark{font-family:"Plus Jakarta Sans","Inter",sans-serif;font-weight:700;font-size:1.6rem;letter-spacing:.14em;text-transform:uppercase;margin-bottom:34px}
  .wordmark span{color:var(--gold)}
  .eyebrow{font-size:.75rem;font-weight:600;letter-spacing:.22em;text-transform:uppercase;color:var(--gold);margin-bottom:18px}
  h1{font-family:"Plus Jakarta Sans","Inter",sans-serif;font-size:clamp(2rem,6vw,2.9rem);line-height:1.12;letter-spacing:-.02em;margin-bottom:18px}
  p.lead{color:rgba(255,255,255,.72);font-size:1.05rem;line-height:1.7;margin-bottom:34px}
  .contact{display:flex;gap:14px;flex-wrap:wrap;justify-content:center}
  .contact a{
    color:#fff;text-decoration:none;font-size:.92rem;
    border:1px solid rgba(255,255,255,.22);border-radius:999px;padding:11px 20px;transition:.25s
  }
  .contact a:hover{background:var(--gold);border-color:var(--gold)}
  .foot{margin-top:40px;font-size:.78rem;color:rgba(255,255,255,.4);letter-spacing:.04em}
</style>
</head>
<body>
  <div class="card">
    <?php if ($logo): ?>
      <img class="logo" src="<?= e(media($logo)) ?>" alt="<?= e($brand) ?>">
    <?php else: ?>
      <div class="wordmark">GÜRDOĞA <span>KOOPERATİFİ</span></div>
    <?php endif; ?>
    <p class="eyebrow"><?= e($tagline) ?></p>
    <h1>Yeni web sitemiz çok yakında</h1>
    <p class="lead">Sitemiz şu anda güncelleniyor. Ürün grupları, özel üretim ve numune talepleriniz için bize doğrudan ulaşabilirsiniz.</p>
    <div class="contact">
      <?php if ($email): ?><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a><?php endif; ?>
      <?php if ($phone): ?><a href="tel:<?= e(preg_replace('/\s+/', '', $phone)) ?>"><?= e($phone) ?></a><?php endif; ?>
    </div>
    <p class="foot">&copy; <?= date('Y') ?> <?= e($brand) ?></p>
  </div>
</body>
</html>
