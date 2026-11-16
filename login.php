<?php
require 'config.php';
if (!empty($_SESSION['user'])) kembali('index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $st = $db->prepare('SELECT id, username, password, nama, role FROM users WHERE username = ? AND aktif = 1');
    $st->bind_param('s', $_POST['username']);
    $st->execute();
    $u = $st->get_result()->fetch_assoc();
    if ($u && password_verify($_POST['password'], $u['password'])) {
        session_regenerate_id(true);
        unset($u['password']);
        $u['id'] = (int)$u['id'];
        $_SESSION['user'] = $u;
        kembali('index.php');
    }
    $error = 'Username atau password salah, atau akun tidak aktif.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - SIMPEG</title>
<link href="assets/bootstrap/bootstrap.min.css" rel="stylesheet">
<link href="assets/bootstrap-icons/bootstrap-icons.min.css" rel="stylesheet">
<link rel="icon" type="image/svg+xml" href="favicon.svg?v=<?= filemtime(__DIR__ . "/favicon.svg") ?>">
<link href="style.css?v=<?= filemtime(__DIR__ . "/style.css") ?>" rel="stylesheet">
</head>
<body class="login-bg d-flex align-items-center justify-content-center p-3">
<div class="card login-card shadow-lg" style="width:100%;max-width:380px">
  <div class="card-body p-4">
    <div class="text-center mb-4">
      <img src="favicon.svg?v=<?= filemtime(__DIR__ . "/favicon.svg") ?>" alt="Logo SIMPEG" class="logo logo-lg mb-2">
      <h4 class="fw-bold text-biru mb-0">SIMPEG</h4>
      <small class="text-muted">Sistem Informasi Kepegawaian</small>
    </div>
    <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <div class="input-group"><span class="input-group-text"><i class="bi bi-person"></i></span>
          <input name="username" class="form-control" required autofocus value="<?= e($_POST['username'] ?? '') ?>"></div>
      </div>
      <div class="mb-4">
        <label class="form-label">Password</label>
        <div class="input-group"><span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" name="password" class="form-control" required></div>
      </div>
      <button class="btn btn-primary w-100"><i class="bi bi-box-arrow-in-right"></i> Masuk</button>
    </form>
    <p class="text-center text-muted small mt-4 mb-0">&copy; 2026 SIMPEG &middot; Made by Fadhil Djibran</p>
  </div>
</div>
</body>
</html>
