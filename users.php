<?php
require 'config.php';
require 'layout.php';
wajib_login();
if (!is_admin()) { http_response_code(403); die('Akses ditolak.'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_admin_post();
    $id = (int)($_POST['id'] ?? 0);
    if ($_POST['aksi'] === 'simpan') {
        $username = trim($_POST['username']);
        $nama = trim($_POST['nama']);
        $role = $_POST['role'] === 'admin' ? 'admin' : 'user';
        $pass = $_POST['password'];
        if ($username === '' || $nama === '' || (!$id && strlen($pass) < 6) || ($pass !== '' && strlen($pass) < 6)) {
            flash('Username & nama wajib diisi, password minimal 6 karakter.', 'danger');
            kembali($id ? "users.php?edit=$id" : 'users.php');
        }
        if ($id === $_SESSION['user']['id'] && $role !== 'admin') {
            flash('Tidak bisa menurunkan role akun sendiri.', 'danger');
            kembali('users.php');
        }
        try {
            if (!$id) {
                $db->prepare('INSERT INTO users (username, nama, role, password) VALUES (?, ?, ?, ?)')
                   ->execute([$username, $nama, $role, password_hash($pass, PASSWORD_DEFAULT)]);
            } elseif ($pass !== '') {
                $db->prepare('UPDATE users SET username = ?, nama = ?, role = ?, password = ? WHERE id = ?')
                   ->execute([$username, $nama, $role, password_hash($pass, PASSWORD_DEFAULT), $id]);
            } else {
                $db->prepare('UPDATE users SET username = ?, nama = ?, role = ? WHERE id = ?')
                   ->execute([$username, $nama, $role, $id]);
            }
            flash($id ? 'Pengguna diperbarui.' : 'Pengguna ditambahkan.');
        } catch (mysqli_sql_exception $ex) {
            if ($ex->getCode() !== 1062) throw $ex;
            flash('Username sudah dipakai.', 'danger');
        }
    } elseif ($id === $_SESSION['user']['id']) {
        flash('Tidak bisa menonaktifkan akun sendiri.', 'danger');
    } else {
        $aktif = $_POST['aksi'] === 'pulihkan' ? 1 : 0;
        $db->prepare('UPDATE users SET aktif = ? WHERE id = ?')->execute([$aktif, $id]);
        flash($aktif ? 'Pengguna diaktifkan kembali.' : 'Pengguna dinonaktifkan.');
    }
    kembali('users.php');
}

$edit = ['id' => '', 'username' => '', 'nama' => '', 'role' => 'user'];
if (isset($_GET['edit'])) {
    $st = $db->prepare('SELECT id, username, nama, role FROM users WHERE id = ?');
    $st->execute([$_GET['edit']]);
    $edit = $st->get_result()->fetch_assoc() ?: $edit;
}

$semua = isset($_GET['semua']);
$rows = $db->query('SELECT id, username, nama, role, aktif, created_at FROM users ' . ($semua ? '' : 'WHERE aktif = 1 ') . 'ORDER BY aktif DESC, nama');

layout_atas('Pengguna', 'users');
?>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card"><div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-person-gear"></i> <?= $edit['id'] ? 'Edit' : 'Tambah' ?> Pengguna</span>
        <?php if ($edit['id']) echo tombol_kembali('users.php'); ?>
      </div>
      <div class="card-body">
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="aksi" value="simpan">
          <input type="hidden" name="id" value="<?= e($edit['id']) ?>">
          <div class="mb-3"><label class="form-label">Username</label><input name="username" class="form-control" required value="<?= e($edit['username']) ?>"></div>
          <div class="mb-3"><label class="form-label">Nama</label><input name="nama" class="form-control" required value="<?= e($edit['nama']) ?>"></div>
          <div class="mb-3"><label class="form-label">Role</label>
            <select name="role" class="form-select">
              <option value="user" <?= $edit['role'] === 'user' ? 'selected' : '' ?>>User (lihat saja)</option>
              <option value="admin" <?= $edit['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select></div>
          <div class="mb-3"><label class="form-label">Password <?= $edit['id'] ? '<small class="text-muted">(kosongkan jika tidak diubah)</small>' : '' ?></label>
            <input type="password" name="password" class="form-control" minlength="6" <?= $edit['id'] ? '' : 'required' ?>></div>
          <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
          <?php if ($edit['id']): ?><a href="users.php" class="btn btn-light">Batal</a><?php endif; ?>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card"><div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people"></i> Daftar Pengguna</span>
        <a href="?<?= $semua ? '' : 'semua=1' ?>" class="btn btn-sm btn-warning"><i class="bi bi-funnel"></i> <?= $semua ? 'Hanya aktif' : 'Tampilkan inaktif' ?></a>
      </div>
      <div class="card-body table-responsive">
        <table class="table table-hover align-middle">
          <thead><tr><th>#</th><th>Username</th><th>Nama</th><th>Role</th><th>Status</th><th>Aksi</th></tr></thead>
          <tbody>
          <?php $no = 1; foreach ($rows as $r): ?>
            <tr class="<?= $r['aktif'] ? '' : 'inaktif' ?>">
              <td><?= $no++ ?></td><td><?= e($r['username']) ?></td><td><?= e($r['nama']) ?></td>
              <td><span class="badge <?= $r['role'] === 'admin' ? 'bg-biru' : 'bg-kuning text-dark' ?>"><?= $r['role'] ?></span></td>
              <td><?= badge_status($r['aktif']) ?></td>
              <td class="text-nowrap">
                <?php if ($r['aktif']): ?><a href="?edit=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a><?php endif; ?>
                <?php if ($r['id'] != $_SESSION['user']['id']) echo tombol_status($r['id'], $r['aktif']); ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php layout_bawah();
