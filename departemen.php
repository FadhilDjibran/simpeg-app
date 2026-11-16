<?php
require 'config.php';
require 'layout.php';
wajib_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_admin_post();
    $id = (int)($_POST['id'] ?? 0);
    switch ($_POST['aksi']) {
        case 'simpan':
            $nama = trim($_POST['nama']);
            $ket  = trim($_POST['keterangan']);
            if ($nama === '') { flash('Nama wajib diisi.', 'danger'); break; }
            $st = $id
                ? $db->prepare('UPDATE departemen SET nama = ?, keterangan = ? WHERE id = ?')
                : $db->prepare('INSERT INTO departemen (nama, keterangan) VALUES (?, ?)');
            $id ? $st->bind_param('ssi', $nama, $ket, $id) : $st->bind_param('ss', $nama, $ket);
            $st->execute();
            flash($id ? 'Departemen diperbarui.' : 'Departemen ditambahkan.');
            break;
        case 'hapus':
        case 'pulihkan':
            $aktif = $_POST['aksi'] === 'pulihkan' ? 1 : 0;
            $st = $db->prepare('UPDATE departemen SET aktif = ? WHERE id = ?');
            $st->bind_param('ii', $aktif, $id);
            $st->execute();
            flash($aktif ? 'Departemen dipulihkan.' : 'Departemen dinonaktifkan.');
    }
    kembali('departemen.php');
}

$edit = ['id' => '', 'nama' => '', 'keterangan' => ''];
if (is_admin() && isset($_GET['edit'])) {
    $st = $db->prepare('SELECT * FROM departemen WHERE id = ?');
    $st->bind_param('i', $_GET['edit']);
    $st->execute();
    $edit = $st->get_result()->fetch_assoc() ?: $edit;
}

$semua = isset($_GET['semua']);
$rows = $db->query('SELECT d.*, (SELECT COUNT(*) FROM pegawai p WHERE p.departemen_id = d.id AND p.aktif = 1) jml
    FROM departemen d ' . ($semua ? '' : 'WHERE d.aktif = 1 ') . 'ORDER BY d.aktif DESC, d.nama');

layout_atas('Departemen', 'departemen');
?>
<div class="row g-3">
  <?php if (is_admin()): ?>
  <div class="col-lg-4">
    <div class="card"><div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-pencil-square"></i> <?= $edit['id'] ? 'Edit' : 'Tambah' ?> Departemen</span>
        <?php if ($edit['id']) echo tombol_kembali('departemen.php'); ?>
      </div>
      <div class="card-body">
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="aksi" value="simpan">
          <input type="hidden" name="id" value="<?= e($edit['id']) ?>">
          <div class="mb-3"><label class="form-label">Nama</label><input name="nama" class="form-control" required value="<?= e($edit['nama']) ?>"></div>
          <div class="mb-3"><label class="form-label">Keterangan</label><input name="keterangan" class="form-control" value="<?= e($edit['keterangan']) ?>"></div>
          <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
          <?php if ($edit['id']): ?><a href="departemen.php" class="btn btn-light">Batal</a><?php endif; ?>
        </form>
      </div>
    </div>
  </div>
  <?php endif; ?>
  <div class="<?= is_admin() ? 'col-lg-8' : 'col-12' ?>">
    <div class="card"><div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-building"></i> Daftar Departemen</span>
        <a href="?<?= $semua ? '' : 'semua=1' ?>" class="btn btn-sm btn-warning"><i class="bi bi-funnel"></i> <?= $semua ? 'Hanya aktif' : 'Tampilkan inaktif' ?></a>
      </div>
      <div class="card-body table-responsive">
        <table class="table table-hover align-middle">
          <thead><tr><th>#</th><th>Nama</th><th>Keterangan</th><th>Pegawai</th><th>Status</th><?php if (is_admin()): ?><th>Aksi</th><?php endif; ?></tr></thead>
          <tbody>
          <?php $no = 1; foreach ($rows as $r): ?>
            <tr class="<?= $r['aktif'] ? '' : 'inaktif' ?>">
              <td><?= $no++ ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['keterangan']) ?></td><td><?= $r['jml'] ?></td>
              <td><?= badge_status($r['aktif']) ?></td>
              <?php if (is_admin()): ?><td class="text-nowrap">
                <?php if ($r['aktif']): ?><a href="?edit=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a><?php endif; ?>
                <?= tombol_status($r['id'], $r['aktif']) ?>
              </td><?php endif; ?>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php layout_bawah();
