<?php /** @var ?string $error */ ?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Giriş — Gürdoğa Kooperatifi</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="login-body">
<form class="login-card" method="post" action="<?= e(url('/admin/login')) ?>">
  <h1>GÜRDOĞA <em>Panel</em></h1>
  <?php if ($error): ?><p class="login-err"><?= e($error === 'Invalid credentials.' ? 'E-posta veya şifre hatalı.' : $error) ?></p><?php endif; ?>
  <?= csrf_field() ?>
  <label><span>E-posta</span><input type="email" name="email" required autofocus></label>
  <label><span>Şifre</span><input type="password" name="password" required></label>
  <button class="btn-primary" type="submit">Giriş yap</button>
</form>
</body>
</html>
