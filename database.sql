-- Database Kepegawaian
-- Import lewat phpMyAdmin
-- Akun: admin/admin123, user/user123

DROP DATABASE IF EXISTS db_kepegawaian;
CREATE DATABASE db_kepegawaian CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE db_kepegawaian;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  nama VARCHAR(100) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  aktif TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE departemen (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  keterangan VARCHAR(255),
  aktif TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE jabatan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  gaji_pokok DECIMAL(12,2) NOT NULL DEFAULT 0,
  aktif TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE pegawai (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nip VARCHAR(30) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  jenis_kelamin ENUM('L','P') NOT NULL,
  tanggal_lahir DATE,
  pendidikan ENUM('SD','SMP','SMA','D3','S1','S2','S3'),
  alamat VARCHAR(255),
  no_hp VARCHAR(20),
  email VARCHAR(100),
  departemen_id INT,
  jabatan_id INT,
  tanggal_masuk DATE NOT NULL,
  aktif TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (departemen_id) REFERENCES departemen(id),
  FOREIGN KEY (jabatan_id) REFERENCES jabatan(id)
);

INSERT INTO users (username, password, nama, role) VALUES
('admin', '$2y$10$6RotxaXsFBSGY6Ie59rMz.nhkgKLEbhCnIR8B4p.KbvPIky99dWqG', 'Administrator', 'admin'),
('user',  '$2y$10$zwSKkTOk01zEiua9oD8Hn.11pS7KaOVDZfBX79mLCv.Z2dWL9AUna', 'Pengguna Biasa', 'user');

INSERT INTO departemen (nama, keterangan) VALUES
('IT', 'Teknologi Informasi'),
('Keuangan', 'Akuntansi dan keuangan'),
('HRD', 'Sumber daya manusia'),
('Pemasaran', 'Marketing dan penjualan');

INSERT INTO jabatan (nama, gaji_pokok) VALUES
('Manager', 15000000),
('Supervisor', 10000000),
('Staff', 6000000),
('Magang', 2500000);

INSERT INTO pegawai (nip, nama, jenis_kelamin, tanggal_lahir, pendidikan, alamat, no_hp, email, departemen_id, jabatan_id, tanggal_masuk, aktif) VALUES
('2026001', 'Budi Santoso',   'L', '1978-03-12', 'S2', 'Jl. Merdeka 1, Bandung',   '081234567001', 'budi@kantor.id',   1, 1, '2026-01-05', 1),
('2026002', 'Siti Aminah',    'P', '1993-07-21', 'S1', 'Jl. Asia Afrika 5, Bandung','081234567002', 'siti@kantor.id',   2, 2, '2026-02-10', 1),
('2026003', 'Andi Pratama',   'L', '1997-11-02', 'S1', 'Jl. Dago 12, Bandung',     '081234567003', 'andi@kantor.id',   1, 3, '2026-03-15', 1),
('2026004', 'Dewi Lestari',   'P', '1995-05-30', 'S1', 'Jl. Riau 8, Bandung',      '081234567004', 'dewi@kantor.id',   3, 2, '2026-03-20', 1),
('2026005', 'Rizky Maulana',  'L', '2000-01-17', 'D3', 'Jl. Braga 3, Bandung',     '081234567005', 'rizky@kantor.id',  4, 3, '2026-05-02', 1),
('2026006', 'Nur Aisyah',     'P', '2004-09-09', 'SMA', 'Jl. Cihampelas 7, Bandung','081234567006', 'nur@kantor.id',    4, 4, '2026-06-11', 1),
('2026007', 'Fajar Nugroho',  'L', '1984-12-25', 'S1', 'Jl. Setiabudi 20, Bandung','081234567007', 'fajar@kantor.id',  2, 3, '2026-07-01', 0),
('2026008', 'Maya Sari',      'P', '1998-04-14', 'D3', 'Jl. Buah Batu 9, Bandung', '081234567008', 'maya@kantor.id',   1, 3, '2026-08-19', 1);
