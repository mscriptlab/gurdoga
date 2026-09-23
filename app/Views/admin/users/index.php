<?php /** @var array $users */ $title = 'Kullanıcılar';
$roleLabel = ['admin' => 'yönetici', 'editor' => 'editör'];
?>
<table class="tbl">
  <thead><tr><th>Ad</th><th>E-posta</th><th>Rol</th><th>Aktif</th><th>Son giriş</th><th>Yeni şifre</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($users as $u): ?>
    <tr>
      <form method="post" action="<?= e(url('/admin/users/' . $u['id'])) ?>" class="inline-form">
        <?= csrf_field() ?>
        <td><input type="text" name="name" value="<?= e($u['name']) ?>"></td>
        <td><?= e($u['email']) ?></td>
        <td><select name="role"><option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>yönetici</option><option value="editor" <?= $u['role'] === 'editor' ? 'selected' : '' ?>>editör</option></select></td>
        <td><input type="checkbox" name="is_active" value="1" <?= $u['is_active'] ? 'checked' : '' ?>></td>
        <td><?= e($u['last_login'] ? date('d.m.Y H:i', strtotime($u['last_login'])) : '—') ?></td>
        <td><input type="password" name="password" placeholder="boş bırak"></td>
        <td>
          <button class="link" type="submit">kaydet</button>
      </form>
          <form method="post" action="<?= e(url('/admin/users/' . $u['id'] . '/delete')) ?>" onsubmit="return confirm('Kullanıcı silinsin mi?')" class="inline-form">
            <?= csrf_field() ?><button class="link danger" type="submit">sil</button>
          </form>
        </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<h3>Kullanıcı ekle</h3>
<form method="post" action="<?= e(url('/admin/users')) ?>" class="form row">
  <?= csrf_field() ?>
  <label class="col"><span>Ad</span><input type="text" name="name"></label>
  <label class="col"><span>E-posta</span><input type="email" name="email" required></label>
  <label class="col"><span>Şifre (en az 8)</span><input type="password" name="password" required></label>
  <label class="col col-sm"><span>Rol</span><select name="role"><option value="editor">editör</option><option value="admin">yönetici</option></select></label>
  <button class="btn-primary" type="submit">Ekle</button>
</form>
