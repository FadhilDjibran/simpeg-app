<?php
require 'config.php';
require 'layout.php';
require 'class/Pegawai.php';
wajib_login();

$p = (new Pegawai($db))->detail((int)($_GET['id'] ?? 0));
if (!$p) {
    flash('Data pegawai tidak ditemukan.', 'danger');
    kembali('pegawai.php');
}

$tgl = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '-';
$masaKerja = intdiv($p['masa_kerja_bulan'], 12) . ' tahun ' . $p['masa_kerja_bulan'] % 12 . ' bulan';

// Data tampilan
$pribadi = [
    'NIP'           => e($p['nip']),
    'Nama Lengkap'  => e($p['nama']),
    'Jenis Kelamin' => $p['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan',
    'Tanggal Lahir' => $tgl($p['tanggal_lahir']) . ($p['usia'] !== null ? " ({$p['usia']} tahun)" : ''),
    'Pendidikan'    => e($p['pendidikan'] ?? '-'),
    'Alamat'        => e($p['alamat'] ?: '-'),
    'No. HP'        => e($p['no_hp'] ?: '-'),
    'Email'         => e($p['email'] ?: '-'),
];
$pekerjaan = [
    'Departemen'    => e($p['departemen'] ?? '-'),
    'Jabatan'       => e($p['jabatan'] ?? '-'),
    'Gaji Pokok'    => $p['gaji_pokok'] !== null ? 'Rp ' . number_format($p['gaji_pokok'], 0, ',', '.') : '-',
    'Tanggal Masuk' => $tgl($p['tanggal_masuk']),
    'Masa Kerja'    => $masaKerja,
    'Status'        => badge_status($p['aktif']),
];

layout_atas('Detail Pegawai', 'pegawai');
?>
<div class="card">
  <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
    <span><i class="bi bi-person-vcard"></i> <?= e($p['nama']) ?></span>
    <div class="d-flex gap-2">
      <?php if (is_admin() && $p['aktif']): ?>
        <a href="pegawai.php?edit=<?= $p['id'] ?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i> Edit</a>
      <?php endif; ?>
      <?= tombol_kembali('pegawai.php') ?>
    </div>
  </div>
  <div class="card-body">
    <div class="row g-4">
      <?php foreach (['Data Pribadi' => $pribadi, 'Data Pekerjaan' => $pekerjaan] as $judul => $baris): ?>
      <div class="col-lg-6">
        <h6 class="text-biru fw-bold mb-3"><?= $judul ?></h6>
        <table class="table table-sm table-borderless mb-0">
          <?php foreach ($baris as $label => $nilai): ?>
            <tr><th class="text-muted fw-normal" style="width: 40%"><?= $label ?></th><td><?= $nilai ?></td></tr>
          <?php endforeach; ?>
        </table>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php layout_bawah();
