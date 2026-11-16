# SIMPEG - Sistem Informasi Kepegawaian

Aplikasi web untuk mengelola data pegawai, dibuat dengan PHP native dan MySQL. Logika data pegawai ditulis dalam class `Pegawai`, sedangkan fungsi bantu dan tampilan ditulis dalam bentuk fungsi biasa.

## Teknologi

| Komponen | Versi | Keterangan |
|---|---|---|
| PHP | 8.2 (XAMPP) | Bahasa pemrograman server |
| MySQL / MariaDB | XAMPP | Basis data |
| Bootstrap | 5.3.3 | Tampilan dan komponen UI |
| Bootstrap Icons | 1.11.3 | Ikon |
| Chart.js | 4.4.1 | Grafik dashboard |

Library disimpan di folder `assets/`, jadi aplikasi tetap bisa dipakai tanpa internet.

## Instalasi

1. Install XAMPP, lalu jalankan Apache dan MySQL dari XAMPP Control Panel.
2. Salin folder proyek ke `xampp/htdocs/kepegawaian`.
3. Buka http://localhost/phpmyadmin, pilih tab Import, pilih file `database.sql`, lalu klik Go.
4. Buka http://localhost/kepegawaian

Pengaturan koneksi database ada di `config.php` (user `root` tanpa password, database `db_kepegawaian`).

### Akun awal

| Role | Username | Password | Hak akses |
|---|---|---|---|
| Admin | admin | admin123 | Semua fitur, termasuk kelola pengguna |
| User | user | user123 | Hanya melihat dashboard dan data |

## Fitur

1. Login dengan role admin dan user.
2. Dashboard berisi ringkasan jumlah data dan grafik:
   - jumlah pegawai laki-laki dan perempuan
   - pendidikan terakhir (SD sampai S3)
   - kelompok usia (di bawah 25, 25-34, 35-44, 45 ke atas)
   - pegawai per departemen, pegawai masuk per bulan, dan status aktif/inaktif
3. CRUD pegawai: tambah, lihat daftar, cari berdasarkan nama/NIP, lihat detail, ubah, dan hapus.
4. CRUD departemen, jabatan, dan pengguna.
5. Data yang dihapus tidak hilang dari database, hanya kolom `aktif` diubah menjadi 0. Data inaktif bisa ditampilkan lagi dan dipulihkan.
6. Pegawai yang sudah inaktif bisa dihapus permanen.
7. Form edit dan halaman detail punya tombol Kembali.
8. Muncul pop-up konfirmasi sebelum data dihapus atau dipulihkan.

## Struktur Folder

```
kepegawaian/
├── assets/             Bootstrap, Bootstrap Icons, Chart.js
├── class/
│   └── Pegawai.php     Class Pegawai, berisi query dan logika data pegawai
├── database.sql        Struktur tabel dan data awal
├── config.php          Koneksi database, session, fungsi bantu
├── layout.php          Template halaman (topbar, sidebar, modal konfirmasi)
├── style.css           Tampilan
├── favicon.svg         Logo
├── login.php           Halaman login
├── logout.php          Logout
├── index.php           Dashboard
├── pegawai.php         Data pegawai
├── pegawai_detail.php  Detail pegawai
├── departemen.php      Data departemen
├── jabatan.php         Data jabatan
└── users.php           Data pengguna (khusus admin)
```

## Struktur Database

`pegawai`: id, nip (unik), nama, jenis_kelamin (L/P), tanggal_lahir, pendidikan (SD/SMP/SMA/D3/S1/S2/S3), alamat, no_hp, email, departemen_id, jabatan_id, tanggal_masuk, aktif

`departemen`: id, nama, keterangan, aktif

`jabatan`: id, nama, gaji_pokok, aktif

`users`: id, username (unik), password, nama, role (admin/user), aktif

Kolom `departemen_id` dan `jabatan_id` adalah foreign key ke tabel `departemen` dan `jabatan`.

## Dokumentasi Kode

### Alur halaman

Setiap halaman memakai pola yang sama:

1. Memanggil `config.php` untuk koneksi database dan session.
2. Memanggil `wajib_login()`. Jika belum login, pengguna diarahkan ke halaman login.
3. Jika ada request POST (tambah, ubah, hapus), cek hak akses dengan `wajib_admin_post()`, proses datanya, simpan pesan dengan `flash()`, lalu redirect dengan `kembali()`.
4. Mengambil data yang akan ditampilkan.
5. Menampilkan halaman dengan `layout_atas()`, isi halaman, lalu `layout_bawah()`.

### Class Pegawai (`class/Pegawai.php`)

Class ini menangani semua operasi tabel `pegawai`. Cara memakainya: `$pegawai = new Pegawai($db);`

| Konstanta | Isi |
|---|---|
| `KOLOM` | Kolom yang diisi dari form |
| `PENDIDIKAN` | Pilihan pendidikan terakhir |
| `KELOMPOK_USIA` | Kelompok usia dan batas bawahnya |

| Method | Parameter | Hasil | Fungsi |
|---|---|---|---|
| `__construct` | `mysqli $db` | - | Menyimpan koneksi database |
| `daftar` | `string $cari`, `bool $semua` | `mysqli_result` | Daftar pegawai beserta departemen dan jabatan. Jika `$semua` true, pegawai inaktif ikut ditampilkan |
| `detail` | `int $id` | `array` / `null` | Data lengkap satu pegawai, termasuk usia dan masa kerja |
| `cari` | `int $id` | `array` / `null` | Data satu pegawai untuk form edit |
| `kosong` | - | `array` | Data kosong untuk form tambah |
| `dariInput` | `array $input` | `array` | Mengambil isian form, isian kosong diubah menjadi NULL |
| `validasi` | `array $data` | `string` / `null` | Pesan error, atau null jika data valid |
| `simpan` | `array $data`, `int $id` | - | Menambah data jika `$id` = 0, mengubah data jika `$id` > 0 |
| `setAktif` | `int $id`, `bool $aktif` | - | Menonaktifkan atau memulihkan pegawai |
| `hapusPermanen` | `int $id` | `bool` | Menghapus pegawai dari database, hanya untuk pegawai inaktif |
| `jumlahGender` | - | `array` | Jumlah pegawai laki-laki dan perempuan |
| `jumlahPendidikan` | - | `array` | Jumlah pegawai per pendidikan |
| `kelompokUsia` | - | `array` | Jumlah pegawai per kelompok usia |

Grafik di dashboard hanya menghitung pegawai yang masih aktif.

### Fungsi bantu (`config.php`)

| Fungsi | Kegunaan |
|---|---|
| `e($s)` | Mengamankan teks sebelum ditampilkan (mencegah XSS) |
| `wajib_login()` | Mengarahkan ke halaman login jika belum login |
| `is_admin()` | Mengecek apakah pengguna adalah admin |
| `wajib_admin_post()` | Menolak aksi simpan/hapus jika bukan admin atau token CSRF tidak cocok |
| `csrf_field()` | Input tersembunyi berisi token CSRF |
| `flash($pesan, $tipe)` | Menyimpan pesan notifikasi. Tanpa parameter, menampilkan pesan lalu menghapusnya |
| `kembali($url)` | Redirect ke halaman lain |
| `tombol_status($id, $aktif)` | Tombol hapus atau pulihkan |
| `tombol_kembali($url)` | Tombol Kembali |
| `badge_status($aktif)` | Label Aktif / Inaktif |

### Fungsi tampilan (`layout.php`)

| Fungsi | Kegunaan |
|---|---|
| `layout_atas($judul, $menu)` | Bagian atas halaman: head, topbar, dan sidebar. `$menu` menentukan menu yang aktif |
| `layout_bawah($script)` | Bagian bawah halaman: modal konfirmasi, JavaScript, dan script tambahan |

### Konfirmasi hapus

Form yang butuh konfirmasi diberi atribut `data-confirm`, contohnya:

```html
<form method="post" data-confirm="Yakin hapus data ini?" data-btn="danger">
```

Saat form dikirim, JavaScript di `layout.php` menampilkan modal konfirmasi lebih dulu. Form baru dikirim setelah tombol "Ya, lanjutkan" diklik.

### Keamanan

- Password disimpan dalam bentuk hash dengan `password_hash()`.
- Query yang menerima input dari pengguna memakai prepared statement untuk mencegah SQL injection.
- Teks yang ditampilkan di-escape dengan `e()` untuk mencegah XSS.
- Aksi simpan dan hapus hanya bisa dilakukan admin dan dicek dengan token CSRF.
