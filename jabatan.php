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
            $gaji = (float)$_POST['gaji_pokok'];
            if ($nama === '' || $gaji < 0) { flash('Nama wajib diisi dan gaji tidak boleh negatif.', 'danger'); break; }
            $st = $id
                ? $db->prepare('UPDATE jabatan SET nama = ?, gaji_pokok = ? WHERE id = ?')
                : $db->prepare('INSERT INTO jabatan (nama, gaji_pokok) VALUES (?, ?)');
            $id ? $st->bind_param('sdi', $nama, $gaji, $id) : $st->bind_param('sd', $nama, $gaji);
            $st->execute();
            flash($id ? 'Jabatan diperbarui.' : 'Jabatan ditambahkan.');
            break;
        case 'hapus':
        case 'pulihkan':
            $aktif = $_POST['aksi'] === 'pulihkan' ? 1 : 0;
            $st = $db->prepare('UPDATE jabatan SET aktif = ? WHERE id = ?');
            $st->bind_param('ii', $aktif, $id);
            $st->execute();
            flash($aktif ? 'Jabatan dipulihkan.' : 'Jabatan dinonaktifkan.');
    }
    kembali('jabatan.php');
}

$edit = ['id' => '', 'nama' => '', 'gaji_pokok' => ''];
if (is_admin() && isset($_GET['edit'])) {
    $st = $db->prepare('SELECT * FROM jabatan WHERE id = ?');
    $st->bind_param('i', $_GET['edit']);
    $st->execute();
    $edit = $st->get_result()->fetch_assoc() ?: $edit;
}

$semua = isset($_GET['semua']);
$rows = $db->query('SELECT j.*, (SELECT COUNT(*) FROM pegawai p WHERE p.jabatan_id = j.id AND p.aktif = 1) jml
    FROM jabatan j ' . ($semua ? '' : 'WHERE j.aktif = 1 ') . 'ORDER BY j.aktif DESC, j.gaji_pokok DESC');

layout_atas('Jabatan', 'jabatan');
?>
<div class="row g-3">
  <?php if (is_admin()): ?>
  <div class="col-lg-4">
    <div class="card"><div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-pencil-square"></i> <?= $edit['id'] ? 'Edit' : 'Tambah' ?> Jabatan</span>
        <?php if ($edit['id']) echo tombol_kembali('jabatan.php'); ?>
      </div>
      <div class="card-body">
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="aksi" value="simpan">
          <input type="hidden" name="id" value="<?= e($edit['id']) ?>">
          <div class="mb-3"><label class="form-label">Nama Jabatan</label><input name="nama" class="form-control" required value="<?= e($edit['nama']) ?>"></div>
          <div class="mb-3"><label class="form-label">Gaji Pokok (Rp)</label><input type="number" min="0" step="1000" name="gaji_pokok" class="form-control" required value="<?= e($edit['gaji_pokok']) ?>"></div>
          <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
          <?php if ($edit['id']): ?><a href="jabatan.php" class="btn btn-light">Batal</a><?php endif; ?>
        </form>
      </div>
    </div>
  </div>
  <?php endif; ?>
  <div class="<?= is_admin() ? 'col-lg-8' : 'col-12' ?>">
    <div class="card"><div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-briefcase"></i> Daftar Jabatan</span>
        <a href="?<?= $semua ? '' : 'semua=1' ?>" class="btn btn-sm btn-warning"><i class="bi bi-funnel"></i> <?= $semua ? 'Hanya aktif' : 'Tampilkan inaktif' ?></a>
      </div>
      <div class="card-body table-responsive">
        <table class="table table-hover align-middle">
          <thead><tr><th>#</th><th>Nama</th><th>Gaji Pokok</th><th>Pegawai</th><th>Status</th><?php if (is_admin()): ?><th>Aksi</th><?php endif; ?></tr></thead>
          <tbody>
          <?php $no = 1; foreach ($rows as $r): ?>
            <tr class="<?= $r['aktif'] ? '' : 'inaktif' ?>">
              <td><?= $no++ ?></td><td><?= e($r['nama']) ?></td><td>Rp <?= number_format($r['gaji_pokok'], 0, ',', '.') ?></td><td><?= $r['jml'] ?></td>
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
