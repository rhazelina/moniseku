<?php
// ================================================================
// LOKASI : app/Controllers/Dashboard.php
// VERSI  : v4.0 — Optimized Layout, data asli dari DB
// ================================================================

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
        $this->requireLogin();

        $role = session()->get('role');

        if ($role === 'Administrator') {
            $db   = \Config\Database::connect();
            $data = $this->buildDashboardData($db);
            return view('admin/dashboard', $data);
        }

        if ($role === 'Petugas') {
            return view('petugas/dashboard');
        }

        return redirect()->to('/login');
    }

    private function buildDashboardData($db): array
    {
        $today     = date('Y-m-d');
        $thisMonth = date('Y-m');
        $thisWeek  = [
            date('Y-m-d', strtotime('monday this week')),
            date('Y-m-d', strtotime('sunday this week')),
        ];
        $last30Start = date('Y-m-d', strtotime('-29 days'));

        // ── 1. STATISTIK UTAMA ────────────────────────────────────
        $totalUsers = (int) $db->query(
            "SELECT COUNT(*) AS c FROM users WHERE status_aktif = 'Aktif'"
        )->getRow()->c;

        $totalPetugas = (int) $db->query(
            "SELECT COUNT(*) AS c FROM users WHERE role_id = 2 AND status_aktif = 'Aktif'"
        )->getRow()->c;

        $totalPerangkat = (int) $db->query(
            "SELECT COUNT(*) AS c FROM perangkat_rfid"
        )->getRow()->c;

        $perangkatOnline = (int) $db->query(
            "SELECT COUNT(*) AS c FROM perangkat_rfid WHERE status_perangkat = 'Online'"
        )->getRow()->c;

        $perangkatOffline = (int) $db->query(
            "SELECT COUNT(*) AS c FROM perangkat_rfid WHERE status_perangkat = 'Offline'"
        )->getRow()->c;

        $perangkatMaintenance = (int) $db->query(
            "SELECT COUNT(*) AS c FROM perangkat_rfid WHERE status_perangkat = 'Maintenance'"
        )->getRow()->c;

        $totalKartu = (int) $db->query(
            "SELECT COUNT(*) AS c FROM kartu_rfid WHERE status_kartu = 'Aktif'"
        )->getRow()->c;

        $scanHariIni = (int) $db->query(
            "SELECT COUNT(*) AS c FROM rfid_tap WHERE DATE(waktu_tap) = ?",
            [$today]
        )->getRow()->c;

        $scanBulanIni = (int) $db->query(
            "SELECT COUNT(*) AS c FROM rfid_tap WHERE DATE_FORMAT(waktu_tap,'%Y-%m') = ?",
            [$thisMonth]
        )->getRow()->c;

        $kartuAsingHariIni = (int) $db->query(
            "SELECT COUNT(*) AS c FROM rfid_tap WHERE jenis = 'Asing' AND DATE(waktu_tap) = ?",
            [$today]
        )->getRow()->c;

        $totalKartuAsing = (int) $db->query(
            "SELECT COUNT(DISTINCT uid_rfid) AS c FROM rfid_tap WHERE jenis = 'Asing'"
        )->getRow()->c;

        $totalScanAll = (int) $db->query(
            "SELECT COUNT(*) AS c FROM rfid_tap"
        )->getRow()->c;

        $jadwalHariIni = (int) $db->query(
            "SELECT COUNT(*) AS c FROM jadwal_shift WHERE tanggal = ?",
            [$today]
        )->getRow()->c;

        $jadwalBerjalan = (int) $db->query(
            "SELECT COUNT(*) AS c FROM jadwal_shift WHERE status_shift = 'Berjalan'"
        )->getRow()->c;

        $jadwalMingguIni = (int) $db->query(
            "SELECT COUNT(*) AS c FROM jadwal_shift WHERE tanggal BETWEEN ? AND ?",
            [$thisWeek[0], $thisWeek[1]]
        )->getRow()->c;

        // ── 2. GRAFIK AKTIVITAS 30 HARI ───────────────────────────
        $chartRows = $db->query("
            SELECT DATE(waktu_tap) AS tgl,
                   SUM(jenis = 'Terdaftar') AS terdaftar,
                   SUM(jenis = 'Asing') AS asing,
                   COUNT(*) AS total
            FROM rfid_tap
            WHERE DATE(waktu_tap) >= ?
            GROUP BY DATE(waktu_tap)
            ORDER BY tgl ASC
        ", [$last30Start])->getResultArray();

        $chartMap = [];
        foreach ($chartRows as $r) { $chartMap[$r['tgl']] = $r; }

        $chartLabels = $chartTerdaftar = $chartAsing = $chartTotal = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $chartLabels[]    = date('d M', strtotime($d));
            $chartTerdaftar[] = (int)($chartMap[$d]['terdaftar'] ?? 0);
            $chartAsing[]     = (int)($chartMap[$d]['asing']     ?? 0);
            $chartTotal[]     = (int)($chartMap[$d]['total']     ?? 0);
        }

        // ── 3. DOUGHNUT PERANGKAT ────────────────────────────────
        $donutLabels = ['Online', 'Offline', 'Maintenance'];
        $donutData   = [$perangkatOnline, $perangkatOffline, $perangkatMaintenance];

        // ── 4. LIVE RFID LOG ────────────────────────────────────
        $liveLog = $db->query("
            SELECT t.tap_id, t.waktu_tap, t.uid_rfid, t.jenis,
                   t.status_validasi, t.is_lcs_match,
                   COALESCE(u.nama_lengkap,'Tidak Dikenal') AS nama_petugas,
                   COALESCE(p.kode_perangkat,'-') AS kode_perangkat,
                   COALESCE(r.kode_ruangan,'-')   AS kode_ruangan,
                   COALESCE(r.nama_ruangan,'-')   AS nama_ruangan
            FROM rfid_tap t
            LEFT JOIN users          u ON u.user_id    = t.user_id
            LEFT JOIN perangkat_rfid p ON p.alat_id    = t.alat_id
            LEFT JOIN ruangan        r ON r.ruangan_id = t.ruangan_id
            ORDER BY t.waktu_tap DESC
            LIMIT 15
        ")->getResultArray();

        // ── 5. DAFTAR PERANGKAT ──────────────────────────────────
        $perangkatList = $db->query("
            SELECT p.alat_id, p.kode_perangkat, p.tipe_perangkat,
                   p.status_perangkat, p.ip_address, p.last_online,
                   r.nama_ruangan, r.lokasi
            FROM perangkat_rfid p
            LEFT JOIN ruangan r ON r.ruangan_id = p.ruangan_id
            ORDER BY FIELD(p.status_perangkat,'Online','Maintenance','Offline'), p.alat_id ASC
        ")->getResultArray();

        // ── 6. JADWAL HARI INI ───────────────────────────────────
        $jadwalList = $db->query("
            SELECT js.jadwal_shift_id, js.tanggal, js.status_shift,
                   js.warna, js.catatan,
                   s.nama_shift, s.jam_mulai, s.jam_selesai,
                   u1.nama_lengkap AS petugas_1_nama,
                   u2.nama_lengkap AS petugas_2_nama
            FROM jadwal_shift js
            JOIN shift s ON s.shift_id = js.shift_id
            LEFT JOIN users u1 ON u1.user_id = js.petugas_1
            LEFT JOIN users u2 ON u2.user_id = js.petugas_2
            WHERE js.tanggal = ?
            ORDER BY s.shift_id ASC
        ", [$today])->getResultArray();

        // ── 7. RINGKASAN LAPORAN PATROLI ─────────────────────────
        $laporanHariIni = (int) $db->query(
            "SELECT COUNT(*) AS c FROM patroli_hasil WHERE tanggal = ?", [$today]
        )->getRow()->c;
        $laporanMingguIni = (int) $db->query(
            "SELECT COUNT(*) AS c FROM patroli_hasil WHERE tanggal BETWEEN ? AND ?",
            [$thisWeek[0], $thisWeek[1]]
        )->getRow()->c;
        $laporanBulanIni = (int) $db->query(
            "SELECT COUNT(*) AS c FROM patroli_hasil WHERE DATE_FORMAT(tanggal,'%Y-%m') = ?",
            [$thisMonth]
        )->getRow()->c;
        $laporanTotal = (int) $db->query(
            "SELECT COUNT(*) AS c FROM patroli_hasil"
        )->getRow()->c;

        $laporanStatusHariIni = $db->query("
            SELECT status_patroli, COUNT(*) AS c
            FROM patroli_hasil WHERE tanggal = ?
            GROUP BY status_patroli
        ", [$today])->getResultArray();

        $statusMap = ['Valid'=>0,'Normal'=>0,'Warning'=>0,'Tidak Lengkap'=>0];
        foreach ($laporanStatusHariIni as $row) {
            if (isset($statusMap[$row['status_patroli']])) {
                $statusMap[$row['status_patroli']] = (int)$row['c'];
            }
        }

        // ── 8. GRAFIK PATROLI 7 HARI ────────────────────────────
        $patrol7Days = $db->query("
            SELECT tanggal,
                   SUM(status_patroli IN ('Valid','Normal')) AS lengkap,
                   SUM(status_patroli IN ('Warning','Tidak Lengkap')) AS tidak_lengkap
            FROM patroli_hasil
            WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY tanggal ORDER BY tanggal ASC
        ")->getResultArray();

        $p7Map = [];
        foreach ($patrol7Days as $r) { $p7Map[$r['tanggal']] = $r; }
        $p7Labels = $p7Lengkap = $p7Tidak = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $p7Labels[]  = date('d/m', strtotime($d));
            $p7Lengkap[] = (int)($p7Map[$d]['lengkap']       ?? 0);
            $p7Tidak[]   = (int)($p7Map[$d]['tidak_lengkap'] ?? 0);
        }

        // ── 9. TOP PETUGAS ───────────────────────────────────────
        $topPetugas = $db->query("
            SELECT u.nama_lengkap, COUNT(*) AS total_scan
            FROM rfid_tap t
            JOIN users u ON u.user_id = t.user_id
            WHERE t.jenis = 'Terdaftar' AND DATE(t.waktu_tap) = ?
            GROUP BY t.user_id ORDER BY total_scan DESC LIMIT 5
        ", [$today])->getResultArray();

        // ── 10. SYSTEM INFO ──────────────────────────────────────
        $dbStatus = 'Connected';
        try { $db->query("SELECT 1"); } catch (\Exception $e) { $dbStatus = 'Error'; }

        return [
            'title'                => 'Dashboard Monitoring',
            'totalUsers'           => $totalUsers,
            'totalPetugas'         => $totalPetugas,
            'totalPerangkat'       => $totalPerangkat,
            'perangkatOnline'      => $perangkatOnline,
            'perangkatOffline'     => $perangkatOffline,
            'perangkatMaintenance' => $perangkatMaintenance,
            'totalKartu'           => $totalKartu,
            'scanHariIni'          => $scanHariIni,
            'scanBulanIni'         => $scanBulanIni,
            'kartuAsingHariIni'    => $kartuAsingHariIni,
            'totalKartuAsing'      => $totalKartuAsing,
            'totalScanAll'         => $totalScanAll,
            'jadwalHariIni'        => $jadwalHariIni,
            'jadwalBerjalan'       => $jadwalBerjalan,
            'jadwalMingguIni'      => $jadwalMingguIni,
            'chartLabels'          => json_encode($chartLabels),
            'chartTerdaftar'       => json_encode($chartTerdaftar),
            'chartAsing'           => json_encode($chartAsing),
            'chartTotal'           => json_encode($chartTotal),
            'donutLabels'          => json_encode($donutLabels),
            'donutData'            => json_encode($donutData),
            'p7Labels'             => json_encode($p7Labels),
            'p7Lengkap'            => json_encode($p7Lengkap),
            'p7Tidak'              => json_encode($p7Tidak),
            'liveLog'              => $liveLog,
            'perangkatList'        => $perangkatList,
            'jadwalList'           => $jadwalList,
            'topPetugas'           => $topPetugas,
            'laporanHariIni'       => $laporanHariIni,
            'laporanMingguIni'     => $laporanMingguIni,
            'laporanBulanIni'      => $laporanBulanIni,
            'laporanTotal'         => $laporanTotal,
            'statusMap'            => $statusMap,
            'serverTime'           => date('d M Y H:i:s'),
            'dbStatus'             => $dbStatus,
            'today'                => $today,
        ];
    }
}