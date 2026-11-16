<?php
// Class Pegawai
class Pegawai {
    const KOLOM = ['nip', 'nama', 'jenis_kelamin', 'tanggal_lahir', 'pendidikan', 'alamat', 'no_hp', 'email', 'departemen_id', 'jabatan_id', 'tanggal_masuk'];
    const PENDIDIKAN = ['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3'];
    const KELOMPOK_USIA = ['< 25' => 0, '25 - 34' => 25, '35 - 44' => 35, '>= 45' => 45];

    private mysqli $db;

    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    public function daftar(string $cari = '', bool $semua = false): mysqli_result {
        $st = $this->db->prepare('SELECT p.*, d.nama departemen, j.nama jabatan FROM pegawai p
            LEFT JOIN departemen d ON d.id = p.departemen_id
            LEFT JOIN jabatan j ON j.id = p.jabatan_id
            WHERE (p.nama LIKE ? OR p.nip LIKE ?) ' . ($semua ? '' : 'AND p.aktif = 1 ') . 'ORDER BY p.aktif DESC, p.nama');
        $like = "%$cari%";
        $st->execute([$like, $like]);
        return $st->get_result();
    }

    // Detail pegawai
    public function detail(int $id): ?array {
        $st = $this->db->prepare('SELECT p.*, d.nama departemen, j.nama jabatan, j.gaji_pokok,
                TIMESTAMPDIFF(YEAR, p.tanggal_lahir, CURDATE()) usia,
                TIMESTAMPDIFF(MONTH, p.tanggal_masuk, CURDATE()) masa_kerja_bulan
            FROM pegawai p
            LEFT JOIN departemen d ON d.id = p.departemen_id
            LEFT JOIN jabatan j ON j.id = p.jabatan_id
            WHERE p.id = ?');
        $st->execute([$id]);
        return $st->get_result()->fetch_assoc();
    }

    public function cari(int $id): ?array {
        $st = $this->db->prepare('SELECT * FROM pegawai WHERE id = ?');
        $st->execute([$id]);
        return $st->get_result()->fetch_assoc();
    }

    public function kosong(): array {
        return array_fill_keys(self::KOLOM, '') + ['id' => ''];
    }

    // Ambil input form
    public function dariInput(array $input): array {
        $data = [];
        foreach (self::KOLOM as $k) $data[$k] = trim($input[$k] ?? '') ?: null;
        return $data;
    }

    // Validasi
    public function validasi(array $data): ?string {
        if (!$data['nip'] || !$data['nama'] || !$data['tanggal_masuk']) return 'NIP, nama, dan tanggal masuk wajib diisi.';
        if (!in_array($data['jenis_kelamin'], ['L', 'P'])) return 'Jenis kelamin tidak valid.';
        if (!in_array($data['pendidikan'], self::PENDIDIKAN)) return 'Pendidikan terakhir wajib dipilih.';
        if ($data['email'] && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) return 'Format email tidak valid.';
        return null;
    }

    // Tambah / ubah
    public function simpan(array $data, int $id = 0): void {
        $param = array_values($data);
        if ($id) {
            $sql = 'UPDATE pegawai SET ' . implode(' = ?, ', self::KOLOM) . ' = ? WHERE id = ?';
            $param[] = $id;
        } else {
            $sql = 'INSERT INTO pegawai (' . implode(', ', self::KOLOM) . ') VALUES (' . rtrim(str_repeat('?, ', count(self::KOLOM)), ', ') . ')';
        }
        $this->db->prepare($sql)->execute($param);
    }

    // Hapus (inaktif) / pulihkan
    public function setAktif(int $id, bool $aktif): void {
        $this->db->prepare('UPDATE pegawai SET aktif = ? WHERE id = ?')->execute([(int)$aktif, $id]);
    }

    // Hapus permanen
    public function hapusPermanen(int $id): bool {
        $st = $this->db->prepare('DELETE FROM pegawai WHERE id = ? AND aktif = 0');
        $st->execute([$id]);
        return $st->affected_rows > 0;
    }

    // Statistik dashboard
    public function jumlahGender(): array {
        $r = $this->db->query("SELECT SUM(jenis_kelamin = 'L') l, SUM(jenis_kelamin = 'P') p FROM pegawai WHERE aktif = 1")->fetch_assoc();
        return ['Laki-laki' => (int)$r['l'], 'Perempuan' => (int)$r['p']];
    }

    public function jumlahPendidikan(): array {
        $hasil = array_fill_keys(self::PENDIDIKAN, 0);
        foreach ($this->db->query('SELECT pendidikan, COUNT(*) j FROM pegawai WHERE aktif = 1 AND pendidikan IS NOT NULL GROUP BY pendidikan') as $r)
            $hasil[$r['pendidikan']] = (int)$r['j'];
        return $hasil;
    }

    public function kelompokUsia(): array {
        $hasil = array_fill_keys(array_keys(self::KELOMPOK_USIA), 0);
        foreach ($this->db->query('SELECT TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) usia FROM pegawai WHERE aktif = 1 AND tanggal_lahir IS NOT NULL') as $r) {
            foreach (array_reverse(self::KELOMPOK_USIA) as $label => $batas) {
                if ($r['usia'] >= $batas) { $hasil[$label]++; break; }
            }
        }
        return $hasil;
    }
}
