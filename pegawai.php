<?php
require 'config.php';
require 'layout.php';
require 'class/Pegawai.php';
wajib_login();

$pegawai = new Pegawai($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_admin_post();
    $id = (int)($_POST['id'] ?? 0);
    if ($_POST['aksi'] === 'simpan') {
        $data = $pegawai->dariInput($_POST);
        $error = $pegawai->validasi($data);
        if (!$error) {
            try {
                $pegawai->simpan($data, $id);
                flash($id ? 'Data pegawai diperbarui.' : 'Pegawai ditambahkan.');
                kembali('pegawai.php');
            } catch (mysqli_sql_exception $ex) {
                if ($ex->getCode() !== 1062) throw $ex;
                $error = 'NIP sudah dipakai pegawai lain.';
            }
        }
        flash($error, 'danger');
        kembali($id ? "pegawai.php?edit=$id" : 'pegawai.php?tambah=1');
    } elseif ($_POST['aksi'] === 'hapus_permanen') {
        $pegawai->hapusPermanen($id)
            ? flash('Pegawai dihapus permanen.')
            : flash('Hanya pegawai inaktif yang bisa dihapus permanen.', 'danger');
        kembali('pegawai.php?semua=1');
    } else {
        $aktif = $_POST['aksi'] === 'pulihkan';
        $pegawai->setAktif($id, $aktif);
        flash($aktif ? 'Pegawai diaktifkan kembali.' : 'Pegawai dinonaktifkan.');
    }
    kembali('pegawai.php');
}

$edit = null;
if (is_admin() && isset($_GET['edit'])) {
    $edit = $pegawai->cari((int)$_GET['edit']);
} elseif (is_admin() && isset($_GET['tambah'])) {
    $edit = $pegawai->kosong();
}

$semua = isset($_GET['semua']);
$cari  = trim($_GET['cari'] ?? '');
$rows  = $pegawai->daftar($cari, $semua);

layout_atas('Data Pegawai', 'pegawai');

if ($edit):
    // Opsi dropdown
    $opsi = fn($tabel, $pilih) => $db->query("SELECT id, nama, aktif FROM $tabel WHERE aktif = 1 OR id = " . (int)$pilih . ' ORDER BY nama');
?>
<div class="card mb-4"><div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="bi bi-person-plus"></i> <?= $edit['id'] ? 'Edit' : 'Tambah' ?> Pegawai</span>
    <?= tombol_kembali('pegawai.php') ?>
  </div>
  <div class="card-body">
    <form method="post" class="row g-3">
      <?= csrf_field() ?>
      <input type="hidden" name="aksi" value="simpan">
      <input type="hidden" name="id" value="<?= e($edit['id']) ?>">
      <div class="col-md-4"><label class="form-label">NIP *</label><input name="nip" class="form-control" required value="<?= e($edit['nip']) ?>"></div>
      <div class="col-md-5"><label class="form-label">Nama Lengkap *</label><input name="nama" class="form-control" required value="<?= e($edit['nama']) ?>"></div>
      <div class="col-md-3"><label class="form-label">Jenis Kelamin *</label>
        <select name="jenis_kelamin" class="form-select" required>
          <option value="">- pilih -</option>
          <option value="L" <?= $edit['jenis_kelamin'] === 'L' ? 'selected' : '' ?>>Laki-laki</option>
          <option value="P" <?= $edit['jenis_kelamin'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
        </select></div>
      <div class="col-md-4"><label class="form-label">Tanggal Lahir</label><input type="date" name="tanggal_lahir" class="form-control" value="<?= e($edit['tanggal_lahir']) ?>"></div>
      <div class="col-md-4"><label class="form-label">Pendidikan Terakhir *</label>
        <select name="pendidikan" class="form-select" required>
          <option value="">- pilih -</option>
          <?php foreach (Pegawai::PENDIDIKAN as $p): ?>
            <option <?= $edit['pendidikan'] === $p ? 'selected' : '' ?>><?= $p ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="col-md-4"><label class="form-label">No. HP</label><input name="no_hp" class="form-control" value="<?= e($edit['no_hp']) ?>"></div>
      <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($edit['email']) ?>"></div>
      <div class="col-md-8"><label class="form-label">Alamat</label><input name="alamat" class="form-control" value="<?= e($edit['alamat']) ?>"></div>
      <?php foreach (['departemen' => 'Departemen', 'jabatan' => 'Jabatan'] as $t => $label): ?>
      <div class="col-md-4"><label class="form-label"><?= $label ?></label>
        <select name="<?= $t ?>_id" class="form-select">
          <option value="">- pilih -</option>
          <?php foreach ($opsi($t, $edit[$t . '_id']) as $o): ?>
            <option value="<?= $o['id'] ?>" <?= $o['id'] == $edit[$t . '_id'] ? 'selected' : '' ?>><?= e($o['nama']) ?><?= $o['aktif'] ? '' : ' (inaktif)' ?></option>
          <?php endforeach; ?>
        </select></div>
      <?php endforeach; ?>
      <div class="col-md-4"><label class="form-label">Tanggal Masuk *</label><input type="date" name="tanggal_masuk" class="form-control" required value="<?= e($edit['tanggal_masuk']) ?>"></div>
      <div class="col-12">
        <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
        <a href="pegawai.php" class="btn btn-light">Batal</a>
      </div>
    </form>
  </div>
</div>
<?php else: ?>

<div class="card"><div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
    <span><i class="bi bi-people"></i> Daftar Pegawai</span>
    <div class="d-flex flex-wrap gap-2">
      <form class="d-flex gap-2">
        <?php if ($semua): ?><input type="hidden" name="semua" value="1"><?php endif; ?>
        <input name="cari" class="form-control form-control-sm" placeholder="Cari nama / NIP" value="<?= e($cari) ?>">
        <button class="btn btn-sm btn-outline-primary"><i class="bi bi-search"></i></button>
      </form>
      <a href="?<?= $semua ? '' : 'semua=1' ?>" class="btn btn-sm btn-warning"><i class="bi bi-funnel"></i> <?= $semua ? 'Hanya aktif' : 'Tampilkan inaktif' ?></a>
      <?php if (is_admin()): ?><a href="?tambah=1" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Tambah</a><?php endif; ?>
    </div>
  </div>
  <div class="card-body table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>#</th><th>NIP</th><th>Nama</th><th>L/P</th><th>Pendidikan</th><th>Departemen</th><th>Jabatan</th><th>Tgl Masuk</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php $no = 1; foreach ($rows as $r): ?>
        <tr class="<?= $r['aktif'] ? '' : 'inaktif' ?>">
          <td><?= $no++ ?></td><td><?= e($r['nip']) ?></td>
          <td><?= e($r['nama']) ?><div class="small text-muted"><?= e($r['email']) ?></div></td>
          <td><?= $r['jenis_kelamin'] ?></td><td><?= e($r['pendidikan'] ?? '-') ?></td>
          <td><?= e($r['departemen'] ?? '-') ?></td><td><?= e($r['jabatan'] ?? '-') ?></td>
          <td><?= date('d/m/Y', strtotime($r['tanggal_masuk'])) ?></td>
          <td><?= badge_status($r['aktif']) ?></td>
          <td class="text-nowrap">
            <a href="pegawai_detail.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Lihat detail"><i class="bi bi-eye"></i></a>
            <?php if (is_admin()): ?>
            <?php if ($r['aktif']): ?><a href="?edit=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a><?php endif; ?>
            <?= tombol_status($r['id'], $r['aktif']) ?>
            <?php if (!$r['aktif']): ?>
            <form method="post" class="d-inline" data-confirm="<?= e("Hapus permanen {$r['nama']}? Data tidak bisa dikembalikan.") ?>" data-btn="danger">
              <?= csrf_field() ?>
              <input type="hidden" name="aksi" value="hapus_permanen">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button class="btn btn-sm btn-danger" title="Hapus permanen"><i class="bi bi-trash3-fill"></i></button>
            </form>
            <?php endif; ?>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if ($no === 1): ?><tr><td colspan="10" class="text-center text-muted">Tidak ada data.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
<?php layout_bawah();
