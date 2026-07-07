<?php
// ================================================================
// LOKASI : app/Controllers/PetugasDashboard.php
// VERSI  : v5.0 — Download jadwal reuse output dari JadwalController
//
// PERUBAHAN dari v4.x:
// - Hapus PDF/XLSX custom, download-jadwal sekarang pakai format
//   Excel matriks (.xls) yang sama persis dengan JadwalController
//   exportExcel() — cukup redirect ke route tersebut
// - Hapus dependency XlsxWriter library
// - downloadJadwal() → redirect ke /jadwal/export-excel?month=YYYY-MM
// - Semua guard, kalender, statistik tetap sama
// ================================================================

namespace App\Controllers;

class PetugasDashboard extends BaseController
{
    protected $db;
    protected int $userId;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ── Guard: hanya role Petugas ─────────────────────────────
    private function _guard(): mixed
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }
        $role = strtolower((string)(session()->get('role') ?? ''));
        if (!in_array($role, ['petugas', '2'])) {
            return redirect()->to(base_url('dashboard'));
        }
        $this->userId = (int) session()->get('user_id');
        return null;
    }

    // ─────────────────────────────────────────────────────────
    // GET /petugas/dashboard
    // ─────────────────────────────────────────────────────────
    public function index(): mixed
    {
        $guard = $this->_guard();
        if ($guard) return $guard;

        $uid   = $this->userId;
        $today = date('Y-m-d');
        $month = date('Y-m');

        // ── Profil lengkap + uid RFID aktif ──────────────────
        $profil = $this->db->query("
            SELECT u.user_id,
                   u.nama_lengkap,
                   u.username,
                   u.foto_profile,
                   u.status_aktif,
                   u.last_login,
                   (
                       SELECT kr.uid_rfid
                         FROM kartu_rfid kr
                        WHERE kr.user_id      = u.user_id
                          AND kr.status_kartu = 'Aktif'
                        ORDER BY kr.kartu_id DESC
                        LIMIT 1
                   ) AS uid_rfid
            FROM users u
            WHERE u.user_id = ?
            LIMIT 1
        ", [$uid])->getRowArray() ?? [];

        // ── Resolusi URL foto profil ──────────────────────────
        $fotoProfilUrl = null;
        $fotoFile      = $profil['foto_profile'] ?? null;
        if ($fotoFile) {
            $fotoPath = FCPATH . 'uploads/profile/' . $fotoFile;
            if (file_exists($fotoPath)) {
                $fotoProfilUrl = base_url('uploads/profile/' . $fotoFile);
            }
        }

        // ── Jadwal hari ini ───────────────────────────────────
        $jadwalHariIni = $this->db->query("
            SELECT js.jadwal_shift_id,
                   js.status_shift,
                   js.catatan,
                   s.nama_shift,
                   s.jam_mulai,
                   s.jam_selesai
            FROM jadwal_shift js
            JOIN shift s ON s.shift_id = js.shift_id
            WHERE js.tanggal = ?
              AND (js.petugas_1 = ? OR js.petugas_2 = ?)
            ORDER BY s.shift_id ASC
        ", [$today, $uid, $uid])->getResultArray();

        // ── Jadwal berikutnya (7 hari ke depan) ──────────────
        $jadwalBerikutnya = $this->db->query("
            SELECT js.tanggal,
                   js.status_shift,
                   s.nama_shift,
                   s.jam_mulai,
                   s.jam_selesai
            FROM jadwal_shift js
            JOIN shift s ON s.shift_id = js.shift_id
            WHERE js.tanggal >  ?
              AND js.tanggal <= ?
              AND (js.petugas_1 = ? OR js.petugas_2 = ?)
            ORDER BY js.tanggal ASC, s.shift_id ASC
            LIMIT 5
        ", [$today, date('Y-m-d', strtotime('+7 days')), $uid, $uid])
          ->getResultArray();

        // ── Statistik bulan ini ───────────────────────────────
        $totalJadwalBulan = (int) $this->db->query("
            SELECT COUNT(*) AS n
            FROM jadwal_shift
            WHERE DATE_FORMAT(tanggal,'%Y-%m') = ?
              AND (petugas_1 = ? OR petugas_2 = ?)
        ", [$month, $uid, $uid])->getRow()->n;

        $statScan = $this->db->query("
            SELECT
                COUNT(*)                                AS total,
                SUM(status_validasi = 'Sesuai')         AS berhasil
            FROM rfid_tap
            WHERE user_id = ?
              AND jenis    = 'Terdaftar'
              AND DATE_FORMAT(waktu_tap,'%Y-%m') = ?
        ", [$uid, $month])->getRowArray();

        $totalScanBulan = (int) ($statScan['total']    ?? 0);
        $totalBerhasil  = (int) ($statScan['berhasil'] ?? 0);

        $totalTerlewat = (int) $this->db->query("
            SELECT COALESCE(SUM(GREATEST(jumlah_titik - jumlah_scan, 0)), 0) AS n
            FROM patroli_hasil
            WHERE petugas_id = ?
              AND DATE_FORMAT(tanggal,'%Y-%m') = ?
        ", [$uid, $month])->getRow()->n;

        // ── 8 aktivitas terbaru dari rfid_tap ────────────────
        $aktivitasTerakhir = $this->db->query("
            SELECT t.tap_id                                 AS kunjungan_id,
                   t.waktu_tap,
                   t.uid_rfid                               AS uid_kartu,
                   t.jenis,
                   t.status_validasi,
                   COALESCE(r.nama_ruangan, 'Ruangan N/A')  AS nama_ruangan,
                   COALESCE(r.kode_ruangan, '-')             AS kode_ruangan,
                   COALESCE(p.kode_perangkat, '-')           AS kode_perangkat,
                   COALESCE(p.tipe_perangkat, '-')           AS tipe_perangkat
            FROM rfid_tap t
            LEFT JOIN ruangan r        ON r.ruangan_id = t.ruangan_id
            LEFT JOIN perangkat_rfid p ON p.alat_id    = t.alat_id
            WHERE t.user_id = ?
              AND t.jenis   = 'Terdaftar'
            ORDER BY t.waktu_tap DESC
            LIMIT 8
        ", [$uid])->getResultArray();

        // ── Kalender bulan ini ────────────────────────────────
        $kalenderBulanIni = $this->_kalenderData($uid, $month);

        $namaBulanArr = [
            '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
            '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
            '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember',
        ];
        [$bY, $bM]  = explode('-', $month);
        $namaBulan   = ($namaBulanArr[$bM] ?? $bM) . ' ' . $bY;

        return view('petugas/dashboard', [
            'title'             => 'Dashboard Petugas',
            'profil'            => $profil,
            'fotoProfil'        => $fotoFile,
            'fotoProfilUrl'     => $fotoProfilUrl,
            'jadwalHariIni'     => $jadwalHariIni,
            'jadwalBerikutnya'  => $jadwalBerikutnya,
            'totalJadwalBulan'  => $totalJadwalBulan,
            'totalScanBulan'    => $totalScanBulan,
            'totalBerhasil'     => $totalBerhasil,
            'totalTerlewat'     => $totalTerlewat,
            'aktivitasTerakhir' => $aktivitasTerakhir,
            'kalenderBulanIni'  => $kalenderBulanIni,
            'bulanAktif'        => $month,
            'namaBulan'         => $namaBulan,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // AJAX: GET /petugas/kalender?bulan=2026-06
    // ─────────────────────────────────────────────────────────
    public function kalenderData(): mixed
    {
        $guard = $this->_guard();
        if ($guard) return $guard;

        $bulan = $this->request->getGet('bulan') ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            $bulan = date('Y-m');
        }

        return $this->response->setJSON(
            $this->_kalenderData($this->userId, $bulan)
        );
    }

    // ─────────────────────────────────────────────────────────
    // GET /petugas/logout
    // ─────────────────────────────────────────────────────────
    public function logout(): mixed
    {
        session()->destroy();
        return redirect()->to(base_url('login'));
    }

    // ─────────────────────────────────────────────────────────
    // Helper: data kalender per tanggal (dipakai index + AJAX)
    // ─────────────────────────────────────────────────────────
    private function _kalenderData(int $uid, string $bulan): array
    {
        $rows = $this->db->query("
            SELECT js.tanggal,
                   COUNT(*)                                               AS jumlah_jadwal,
                   GROUP_CONCAT(s.nama_shift  ORDER BY s.shift_id SEPARATOR ', ') AS nama_shift_list,
                   GROUP_CONCAT(js.status_shift ORDER BY s.shift_id SEPARATOR ',') AS status_list,
                   MIN(s.jam_mulai)   AS jam_mulai,
                   MAX(s.jam_selesai) AS jam_selesai
            FROM jadwal_shift js
            JOIN shift s ON s.shift_id = js.shift_id
            WHERE DATE_FORMAT(js.tanggal,'%Y-%m') = ?
              AND (js.petugas_1 = ? OR js.petugas_2 = ?)
            GROUP BY js.tanggal
        ", [$bulan, $uid, $uid])->getResultArray();

        $map = [];
        foreach ($rows as $r) {
            $map[$r['tanggal']] = $r;
        }
        return $map;
    }

    // placeholder route handlers
    public function tracking(): mixed
    {
        $guard = $this->_guard();
        if ($guard) return $guard;
        return redirect()->to(base_url('petugas/dashboard'));
    }

    public function analisis(): mixed
    {
        $guard = $this->_guard();
        if ($guard) return $guard;
        return redirect()->to(base_url('petugas/dashboard'));
    }
}