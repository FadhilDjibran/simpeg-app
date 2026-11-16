<?php
session_start();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli('localhost', 'root', '', 'db_kepegawaian');
$db->set_charset('utf8mb4');

if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function wajib_login() {
    if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
}

function is_admin() { return ($_SESSION['user']['role'] ?? '') === 'admin'; }

// Cek admin + CSRF
function wajib_admin_post() {
    if (!is_admin()) { http_response_code(403); die('Akses ditolak.'); }
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) { http_response_code(400); die('Token tidak valid.'); }
}

function csrf_field() { return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">'; }

function flash($pesan = null, $tipe = 'success') {
    if ($pesan !== null) { $_SESSION['flash'] = [$pesan, $tipe]; return; }
    if (empty($_SESSION['flash'])) return '';
    [$p, $t] = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return "<div class=\"alert alert-$t alert-dismissible fade show\">" . e($p) .
           '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
}

function kembali($url) { header("Location: $url"); exit; }

// Tombol hapus / pulihkan
function tombol_status($id, $aktif) {
    $aksi = $aktif ? 'hapus' : 'pulihkan';
    $btn  = $aktif
        ? '<button class="btn btn-sm btn-outline-danger" title="Hapus (inaktifkan)"><i class="bi bi-trash"></i></button>'
        : '<button class="btn btn-sm btn-outline-success" title="Pulihkan"><i class="bi bi-arrow-counterclockwise"></i></button>';
    $konfirmasi = $aktif ? 'Data akan dinonaktifkan. Lanjutkan?' : 'Aktifkan kembali data ini?';
    $warna = $aktif ? 'danger' : 'success';
    return "<form method=\"post\" class=\"d-inline\" data-confirm=\"$konfirmasi\" data-btn=\"$warna\">" . csrf_field() .
           "<input type=\"hidden\" name=\"aksi\" value=\"$aksi\"><input type=\"hidden\" name=\"id\" value=\"" . (int)$id . "\">$btn</form>";
}

function tombol_kembali($url) {
    return '<a href="' . e($url) . '" class="btn btn-sm btn-light"><i class="bi bi-arrow-left"></i> Kembali</a>';
}

function badge_status($aktif) {
    return $aktif ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Inaktif</span>';
}
