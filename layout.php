<?php
// Layout halaman
function layout_atas($judul, $menu) {
    $u = $_SESSION['user'];
    $items = [
        'dashboard'  => ['index.php', 'bi-speedometer2', 'Dashboard'],
        'pegawai'    => ['pegawai.php', 'bi-people', 'Pegawai'],
        'departemen' => ['departemen.php', 'bi-building', 'Departemen'],
        'jabatan'    => ['jabatan.php', 'bi-briefcase', 'Jabatan'],
    ];
    if (is_admin()) $items['users'] = ['users.php', 'bi-person-gear', 'Pengguna'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judul) ?> - SIMPEG</title>
<link href="assets/bootstrap/bootstrap.min.css" rel="stylesheet">
<link href="assets/bootstrap-icons/bootstrap-icons.min.css" rel="stylesheet">
<link rel="icon" type="image/svg+xml" href="favicon.svg?v=<?= filemtime(__DIR__ . "/favicon.svg") ?>">
<link href="style.css?v=<?= filemtime(__DIR__ . "/style.css") ?>" rel="stylesheet">
</head>
<body class="app">
<script>try { if (localStorage.sidebarTutup === '1') document.body.classList.add('sidebar-tutup'); } catch (e) {}</script>
<nav class="navbar navbar-dark topbar sticky-top">
  <div class="container-fluid">
    <button class="btn btn-link text-white me-2 p-0" id="toggleSidebar" title="Buka/tutup sidebar" aria-label="Buka/tutup sidebar"><i class="bi bi-list fs-4"></i></button>
    <span class="navbar-brand fw-bold"><img src="favicon.svg?v=<?= filemtime(__DIR__ . "/favicon.svg") ?>" alt="" class="logo me-1"> SIMPEG</span>
    <div class="dropdown ms-auto">
      <a class="text-white text-decoration-none dropdown-toggle" href="#" data-bs-toggle="dropdown">
        <i class="bi bi-person-circle"></i> <?= e($u['nama']) ?>
        <span class="badge bg-kuning text-biru ms-1"><?= e($u['role']) ?></span>
      </a>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
<div class="d-flex isi">
  <div class="offcanvas-lg offcanvas-start sidebar" id="sidebar">
    <div class="offcanvas-header d-lg-none">
      <span class="fw-bold text-white">Menu</span>
      <button class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebar"></button>
    </div>
    <ul class="nav flex-column p-3 w-100">
      <?php foreach ($items as $key => [$url, $icon, $label]): ?>
        <li class="nav-item"><a class="nav-link <?= $key === $menu ? 'active' : '' ?>" href="<?= $url ?>"><i class="bi <?= $icon ?> me-2"></i><?= $label ?></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <main class="flex-grow-1 p-4">
    <h4 class="judul-halaman mb-4"><?= e($judul) ?></h4>
    <?= flash() ?>
<?php
}

function layout_bawah($script = '') {
?>
    <footer class="footer">&copy; 2026 SIMPEG &middot; Made by Fadhil Djibran</footer>
  </main>
</div>

<!-- Modal konfirmasi -->
<div class="modal fade" id="modalKonfirmasi" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-biru"><i class="bi bi-exclamation-triangle-fill text-warning"></i> Konfirmasi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="pesanKonfirmasi"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn" id="btnKonfirmasi">Ya, lanjutkan</button>
      </div>
    </div>
  </div>
</div>

<script src="assets/bootstrap/bootstrap.bundle.min.js"></script>
<script>
// Modal konfirmasi
let formKonfirmasi;
document.addEventListener('submit', e => {
  if (!e.target.dataset.confirm) return;
  e.preventDefault();
  formKonfirmasi = e.target;
  pesanKonfirmasi.textContent = formKonfirmasi.dataset.confirm;
  btnKonfirmasi.className = 'btn btn-' + (formKonfirmasi.dataset.btn || 'primary');
  bootstrap.Modal.getOrCreateInstance(modalKonfirmasi).show();
});
btnKonfirmasi.onclick = () => formKonfirmasi.submit();
</script>
<script>
// Toggle sidebar
toggleSidebar.onclick = () => {
  if (innerWidth < 992) return bootstrap.Offcanvas.getOrCreateInstance(sidebar).toggle();
  const tutup = document.body.classList.toggle('sidebar-tutup');
  try { localStorage.sidebarTutup = tutup ? '1' : '0'; } catch (e) {}
};
</script>
<?= $script ?>
</body>
</html>
<?php
}
