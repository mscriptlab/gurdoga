<?php
/** Shown instead of the admin panel when the licence is not valid. Standalone (no layout). */
/** @var array $state */
$msg = $state['message'] ?? 'Lisans doğrulanamadı.';
$status = $state['status'] ?? 'unknown';
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Lisans — Gürdoğa Kooperatifi</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;background:#17181f;color:#e8e6e0;
       min-height:100vh;display:flex;align-items:center;justify-content:center;padding:32px}
  .box{max-width:460px;width:100%;background:#1f2029;border:1px solid #33343e;border-radius:14px;padding:38px 34px;text-align:center}
  .dot{width:46px;height:46px;border-radius:50%;background:#3a2a2a;border:1px solid #6b3b3b;margin:0 auto 22px;
       display:grid;place-items:center;color:#e8a0a0;font-size:22px}
  h1{font-size:1.25rem;margin-bottom:12px}
  p{color:#a7a6b0;line-height:1.65;font-size:.95rem}
  .code{margin-top:22px;font-size:.78rem;color:#6a6975;letter-spacing:.05em}
  a{color:#b3934f}
</style>
</head>
<body>
  <div class="box">
    <div class="dot">!</div>
    <h1>Yönetim paneli geçici olarak kapalı</h1>
    <p><?= e($msg ?: 'Bu kurulum için geçerli bir lisans bulunamadı. Lütfen tedarikçiniz TeknoBursa ile iletişime geçin.') ?></p>
    <p style="margin-top:14px"><a href="mailto:tasarim@teknobursa.com">tasarim@teknobursa.com</a></p>
    <p class="code">Durum: <?= e($status) ?> · <?= e(($_SERVER['HTTP_HOST'] ?? '')) ?></p>
  </div>
</body>
</html>
