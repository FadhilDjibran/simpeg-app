<?php
require 'config.php';
require 'layout.php';
require 'class/Pegawai.php';
wajib_login();

$satu = fn($sql) => $db->query($sql)->fetch_row()[0];
$stat = [
    ['Pegawai Aktif', $satu('SELECT COUNT(*) FROM pegawai WHERE aktif = 1'), 'bi-people-fill'],
    ['Pegawai Inaktif', $satu('SELECT COUNT(*) FROM pegawai WHERE aktif = 0'), 'bi-person-x-fill'],
    ['Departemen', $satu('SELECT COUNT(*) FROM departemen WHERE aktif = 1'), 'bi-building-fill'],
    ['Jabatan', $satu('SELECT COUNT(*) FROM jabatan WHERE aktif = 1'), 'bi-briefcase-fill'],
];

// Data grafik
$perDept = $db->query("SELECT d.nama, COUNT(p.id) jml FROM departemen d
    LEFT JOIN pegawai p ON p.departemen_id = d.id AND p.aktif = 1
    WHERE d.aktif = 1 GROUP BY d.id ORDER BY d.nama")->fetch_all(MYSQLI_ASSOC);
$pegawai = new Pegawai($db);
$gender = $pegawai->jumlahGender();
$pendidikan = $pegawai->jumlahPendidikan();
$usia = $pegawai->kelompokUsia();
$tahun = (int)date('Y');
$perBulan = array_fill(1, 12, 0);
foreach ($db->query("SELECT MONTH(tanggal_masuk) b, COUNT(*) j FROM pegawai WHERE YEAR(tanggal_masuk) = $tahun GROUP BY b") as $r)
    $perBulan[$r['b']] = (int)$r['j'];

$data = json_encode([
    'deptLabel' => array_column($perDept, 'nama'),
    'deptData'  => array_map('intval', array_column($perDept, 'jml')),
    'gender'    => array_values($gender),
    'pendLabel' => array_keys($pendidikan),
    'pendData'  => array_values($pendidikan),
    'usiaLabel' => array_keys($usia),
    'usiaData'  => array_values($usia),
    'status'    => [(int)$stat[0][1], (int)$stat[1][1]],
    'bulan'     => array_values($perBulan),
]);

layout_atas('Dashboard', 'dashboard');
?>
<p class="text-muted">Selamat datang, <b><?= e($_SESSION['user']['nama']) ?></b>.</p>
<div class="row g-3 mb-4">
  <?php foreach ($stat as [$label, $nilai, $icon]): ?>
  <div class="col-6 col-xl-3">
    <div class="card stat h-100"><div class="card-body d-flex justify-content-between align-items-center">
      <div><div class="text-muted small"><?= $label ?></div><div class="fs-3 fw-bold text-biru"><?= $nilai ?></div></div>
      <div class="ikon"><i class="bi <?= $icon ?>"></i></div>
    </div></div>
  </div>
  <?php endforeach; ?>
</div>
<div class="row g-3">
  <div class="col-lg-4"><div class="card h-100"><div class="card-header"><i class="bi bi-gender-ambiguous"></i> Laki-laki vs Perempuan</div><div class="card-body"><div class="chart-box"><canvas id="cGender"></canvas></div></div></div></div>
  <div class="col-lg-4"><div class="card h-100"><div class="card-header"><i class="bi bi-mortarboard"></i> Pendidikan Terakhir</div><div class="card-body"><div class="chart-box"><canvas id="cPendidikan"></canvas></div></div></div></div>
  <div class="col-lg-4"><div class="card h-100"><div class="card-header"><i class="bi bi-hourglass-split"></i> Usia Pegawai</div><div class="card-body"><div class="chart-box"><canvas id="cUsia"></canvas></div></div></div></div>
  <div class="col-lg-12"><div class="card h-100"><div class="card-header"><i class="bi bi-bar-chart"></i> Pegawai per Departemen</div><div class="card-body"><div class="chart-box"><canvas id="cDept"></canvas></div></div></div></div>
  <div class="col-lg-8"><div class="card h-100"><div class="card-header"><i class="bi bi-graph-up"></i> Pegawai Masuk per Bulan (<?= $tahun ?>)</div><div class="card-body"><div class="chart-box"><canvas id="cBulan"></canvas></div></div></div></div>
  <div class="col-lg-4"><div class="card h-100"><div class="card-header"><i class="bi bi-pie-chart"></i> Status Pegawai</div><div class="card-body"><div class="chart-box"><canvas id="cStatus"></canvas></div></div></div></div>
</div>
<?php
layout_bawah(<<<JS
<script src="assets/chartjs/chart.umd.min.js"></script>
<script>
Chart.defaults.maintainAspectRatio = false;
const d = $data, BIRU = '#034694', KUNING = '#fdd000', ABU = '#c5cbd6';
const tanpaLegend = { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } };
new Chart(cDept, { type: 'bar', data: { labels: d.deptLabel, datasets: [{ label: 'Pegawai', data: d.deptData, backgroundColor: BIRU, borderRadius: 6 }] }, options: tanpaLegend });
new Chart(cGender, { type: 'bar', data: { labels: ['Laki-laki', 'Perempuan'], datasets: [{ label: 'Pegawai', data: d.gender, backgroundColor: [BIRU, KUNING], borderRadius: 6 }] }, options: tanpaLegend });
new Chart(cPendidikan, { type: 'bar', data: { labels: d.pendLabel, datasets: [{ label: 'Pegawai', data: d.pendData, backgroundColor: BIRU, borderRadius: 6 }] }, options: tanpaLegend });
new Chart(cUsia, { type: 'bar', data: { labels: d.usiaLabel, datasets: [{ label: 'Pegawai', data: d.usiaData, backgroundColor: KUNING, borderColor: BIRU, borderWidth: 1, borderRadius: 6 }] }, options: tanpaLegend });
new Chart(cBulan, { type: 'line', data: { labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'], datasets: [{ label: 'Pegawai masuk', data: d.bulan, borderColor: BIRU, backgroundColor: 'rgba(253,208,0,.3)', fill: true, tension: .3 }] }, options: tanpaLegend });
new Chart(cStatus, { type: 'pie', data: { labels: ['Aktif', 'Inaktif'], datasets: [{ data: d.status, backgroundColor: [BIRU, ABU] }] } });
</script>
JS);
