<?php
// ================================================================
// LOKASI : app/Controllers/LogRfid.php
// VERSI  : v5.1 — Fix escapeStr() → pakai escape() CI4-compatible
// ================================================================

namespace App\Controllers;

class LogRfid extends BaseController
{
    // -----------------------------------------------------------------
    // Resolusi rentang tanggal dari mode filter
    // -----------------------------------------------------------------
    private function resolveDateRange(string $mode, string $tglAwal, string $tglAkhir): array
    {
        switch ($mode) {
            case 'hari_ini':
                $a = $b = date('Y-m-d');
                break;
            case 'kemarin':
                $a = $b = date('Y-m-d', strtotime('-1 day'));
                break;
            case 'minggu_ini':
                $dow = (int)date('N');
                $a   = date('Y-m-d', strtotime('-' . ($dow - 1) . ' days'));
                $b   = date('Y-m-d', strtotime('+' . (7 - $dow)  . ' days'));
                break;
            case 'bulan_ini':
                $a = date('Y-m-01');
                $b = date('Y-m-t');
                break;
            case 'custom':
            default:
                $a = $tglAwal  ?: date('Y-m-d', strtotime('-7 days'));
                $b = $tglAkhir ?: date('Y-m-d');
                if ($a > $b) [$a, $b] = [$b, $a];
                break;
        }
        return [$a, $b];
    }

    // -----------------------------------------------------------------
    // Helper: escape value untuk LIKE tanpa quote wrapper
    // CI4 MySQLi: escape() mengembalikan '...' — kita strip quote-nya
    // -----------------------------------------------------------------
    private function escLike($db, string $val): string
    {
        $escaped = $db->escape($val);                      // => 'nilai'
        return substr($escaped, 1, strlen($escaped) - 2);  // => nilai
    }

    // -----------------------------------------------------------------
    // Core query builder
    // -----------------------------------------------------------------
    private function buildQuery($db, array $params): array
    {
        $tglAwal      = $params['tglAwal'];
        $tglAkhir     = $params['tglAkhir'];
        $filterJenis  = $params['filterJenis'];
        $filterStatus = $params['filterStatus'];
        $filterAlat   = $params['filterAlat'];
        $filterUid    = $params['filterUid'];
        $search       = $params['search'];

        $where = [
            "DATE(t.waktu_tap) >= " . $db->escape($tglAwal),
            "DATE(t.waktu_tap) <= " . $db->escape($tglAkhir),
        ];

        if ($filterJenis === 'terdaftar') {
            $where[] = "t.jenis = 'Terdaftar'";
        } elseif ($filterJenis === 'asing') {
            $where[] = "t.jenis = 'Asing'";
        }

        if ($filterStatus !== '') {
            $where[] = "t.status_validasi = " . $db->escape($filterStatus);
        }

        if ($filterAlat !== '') {
            $where[] = "t.alat_id = " . (int)$filterAlat;
        }

        if ($filterUid !== '') {
            $fu      = $this->escLike($db, $filterUid);
            $where[] = "t.uid_rfid LIKE '%{$fu}%'";
        }

        if ($search !== '') {
            $sq      = $this->escLike($db, $search);
            $where[] = "(
                u.nama_lengkap   LIKE '%{$sq}%' OR
                u.username       LIKE '%{$sq}%' OR
                r.kode_ruangan   LIKE '%{$sq}%' OR
                r.nama_ruangan   LIKE '%{$sq}%' OR
                p.kode_perangkat LIKE '%{$sq}%' OR
                t.uid_rfid       LIKE '%{$sq}%'
            )";
        }

        $whereSQL = implode(' AND ', $where);

        $sql = "
            SELECT
                t.tap_id                                        AS kunjungan_id,
                t.waktu_tap                                     AS waktu_kunjungan,
                t.uid_rfid                                      AS uid_kartu,
                t.jenis                                         AS jenis_log,
                t.status_validasi,
                COALESCE(t.is_lcs_match, 0)                     AS is_lcs_match,
                t.jadwal_shift_id,
                t.alat_id,
                COALESCE(u.nama_lengkap, 'Tidak Dikenal')       AS nama_petugas,
                COALESCE(u.username, '')                        AS username,
                COALESCE(p.kode_perangkat, '-')                 AS kode_perangkat,
                COALESCE(p.tipe_perangkat, '-')                 AS tipe_perangkat,
                COALESCE(r.kode_ruangan, '-')                   AS kode_ruangan,
                COALESCE(r.nama_ruangan, 'Ruangan N/A')         AS nama_ruangan,
                COALESCE(r.lokasi, '')                          AS lokasi_ruangan,
                s.nama_shift,
                s.jam_mulai,
                s.jam_selesai
            FROM rfid_tap t
            LEFT JOIN users          u  ON u.user_id          = t.user_id
            LEFT JOIN perangkat_rfid p  ON p.alat_id          = t.alat_id
            LEFT JOIN ruangan        r  ON r.ruangan_id        = t.ruangan_id
            LEFT JOIN jadwal_shift   js ON js.jadwal_shift_id  = t.jadwal_shift_id
            LEFT JOIN shift          s  ON s.shift_id          = js.shift_id
            WHERE {$whereSQL}
            ORDER BY t.waktu_tap DESC
        ";

        return $db->query($sql)->getResultArray();
    }

    // -----------------------------------------------------------------
    // Keterangan baris
    // -----------------------------------------------------------------
    private function makeKeterangan(array $row): string
    {
        if (($row['jenis_log'] ?? '') === 'Asing') return 'Kartu tidak dikenali sistem';
        $sv = $row['status_validasi'] ?? '';
        if ($sv === 'Sesuai')
            return ($row['is_lcs_match'] ?? 0) ? 'Scan valid, rute benar' : 'Scan valid, rute kurang tepat';
        if (in_array($sv, ['Di Luar Jadwal', 'Tidak Terjadwal']))
            return !empty($row['jadwal_shift_id']) ? 'Scan di luar jam patroli' : 'Tidak ada jadwal aktif';
        if ($sv === 'Tidak Sesuai') return 'Scan tidak memenuhi kriteria';
        if ($sv === 'Terlambat')    return 'Scan melewati batas waktu';
        return '-';
    }

    // -----------------------------------------------------------------
    // INDEX
    // -----------------------------------------------------------------
    public function index()
    {
        $db = \Config\Database::connect();

        $dateMode     = $this->request->getGet('date_mode')  ?? 'minggu_ini';
        $tglAwalRaw   = $this->request->getGet('tgl_awal')   ?? '';
        $tglAkhirRaw  = $this->request->getGet('tgl_akhir')  ?? '';
        $filterJenis  = $this->request->getGet('filter')     ?? 'semua';
        $filterStatus = $this->request->getGet('status')     ?? '';
        $filterUid    = $this->request->getGet('uid')        ?? '';
        $filterAlat   = $this->request->getGet('alat_id')    ?? '';
        $search       = $this->request->getGet('q')          ?? '';
        $perPage      = max(10, min(1000, (int)($this->request->getGet('limit') ?? 50)));
        $page         = max(1, (int)($this->request->getGet('page') ?? 1));

        [$tglAwal, $tglAkhir] = $this->resolveDateRange($dateMode, $tglAwalRaw, $tglAkhirRaw);

        $params  = compact(
            'tglAwal', 'tglAkhir',
            'filterJenis', 'filterStatus', 'filterAlat', 'filterUid', 'search'
        );
        $allLogs = $this->buildQuery($db, $params);

        $totalLog  = count($allLogs);
        $totalPage = max(1, (int)ceil($totalLog / $perPage));
        $page      = min($page, $totalPage);
        $logs      = array_slice($allLogs, ($page - 1) * $perPage, $perPage);

        $latestWaktu = !empty($allLogs) ? $allLogs[0]['waktu_kunjungan'] : '';

        $perangkatOptions = $db->query(
            "SELECT alat_id, kode_perangkat, tipe_perangkat
               FROM perangkat_rfid ORDER BY kode_perangkat ASC"
        )->getResultArray();

        return view('admin/log_rfid', [
            'logs'             => $logs,
            'totalLog'         => $totalLog,
            'latestWaktu'      => $latestWaktu,
            'perangkatOptions' => $perangkatOptions,
            'dateMode'         => $dateMode,
            'tglAwal'          => $tglAwal,
            'tglAkhir'         => $tglAkhir,
            'tglAwalRaw'       => $tglAwalRaw,
            'tglAkhirRaw'      => $tglAkhirRaw,
            'filterJenis'      => $filterJenis,
            'filterStatus'     => $filterStatus,
            'filterUid'        => $filterUid,
            'filterAlat'       => $filterAlat,
            'search'           => $search,
            'perPage'          => $perPage,
            'page'             => $page,
            'totalPage'        => $totalPage,
        ]);
    }

    // -----------------------------------------------------------------
    // EXPORT
    // -----------------------------------------------------------------
    public function export()
    {
        $db = \Config\Database::connect();

        $format       = $this->request->getGet('format')    ?? 'csv';
        $dateMode     = $this->request->getGet('date_mode') ?? 'custom';
        $tglAwalRaw   = $this->request->getGet('tgl_awal')  ?? '';
        $tglAkhirRaw  = $this->request->getGet('tgl_akhir') ?? '';
        $filterJenis  = $this->request->getGet('filter')    ?? 'semua';
        $filterStatus = $this->request->getGet('status')    ?? '';
        $filterUid    = $this->request->getGet('uid')       ?? '';
        $filterAlat   = $this->request->getGet('alat_id')   ?? '';
        $search       = $this->request->getGet('q')         ?? '';
        $perPage      = max(0, (int)($this->request->getGet('limit') ?? 0));

        [$tglAwal, $tglAkhir] = $this->resolveDateRange($dateMode, $tglAwalRaw, $tglAkhirRaw);

        $params  = compact(
            'tglAwal', 'tglAkhir',
            'filterJenis', 'filterStatus', 'filterAlat', 'filterUid', 'search'
        );
        $allLogs = $this->buildQuery($db, $params);
        $rows    = ($perPage > 0) ? array_slice($allLogs, 0, $perPage) : $allLogs;

        $filename = 'log_rfid_' . $tglAwal . '_sd_' . $tglAkhir . '_' . date('His');
        $headers  = [
            'No', 'Waktu Scan', 'UID Kartu', 'Nama Petugas', 'Username',
            'Perangkat', 'Tipe Perangkat', 'Ruangan', 'Kode Ruangan', 'Lokasi',
            'Tipe Kartu', 'Status Validasi', 'Keterangan',
        ];

        $data = [];
        foreach ($rows as $i => $row) {
            $isAsing = ($row['jenis_log'] ?? '') === 'Asing';
            $data[]  = [
                $i + 1,
                date('d/m/Y H:i:s', strtotime($row['waktu_kunjungan'])),
                $row['uid_kartu']      ?? '-',
                $isAsing ? 'Tidak Dikenal' : ($row['nama_petugas'] ?? '-'),
                $isAsing ? '-'             : ('@' . ($row['username'] ?? '-')),
                $row['kode_perangkat'] ?? '-',
                $row['tipe_perangkat'] ?? '-',
                $row['nama_ruangan']   ?? '-',
                $row['kode_ruangan']   ?? '-',
                $row['lokasi_ruangan'] ?? '-',
                $row['jenis_log']      ?? '-',
                $isAsing ? 'Kartu Asing' : ($row['status_validasi'] ?? '-'),
                $this->makeKeterangan($row),
            ];
        }

        if ($format === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers);
            foreach ($data as $r) fputcsv($out, $r);
            fclose($out);
            exit;
        }

        if ($format === 'excel') {
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
            echo "\xEF\xBB\xBF<html><head><meta charset='UTF-8'><style>
              body{font-family:Arial,sans-serif}
              th{background:#1e40af;color:#fff;font-weight:bold;padding:6px 10px;border:1px solid #1e3a8a;text-align:left}
              td{padding:5px 10px;border:1px solid #d1d5db;font-size:11px;vertical-align:top}
              tr:nth-child(even) td{background:#f0f4ff}
              caption{font-size:14px;font-weight:bold;margin-bottom:8px;text-align:left}
            </style></head><body>
            <caption>Log RFID &mdash; " . htmlspecialchars($tglAwal) . " s/d " . htmlspecialchars($tglAkhir) . " (" . count($rows) . " entri)</caption>
            <table><tr>";
            foreach ($headers as $h) echo '<th>' . htmlspecialchars($h) . '</th>';
            echo '</tr>';
            foreach ($data as $r) {
                echo '<tr>';
                foreach ($r as $c) echo '<td>' . htmlspecialchars((string)$c) . '</td>';
                echo '</tr>';
            }
            echo '</table></body></html>';
            exit;
        }

        if ($format === 'pdf') {
            $info = "Periode: {$tglAwal} s/d {$tglAkhir}";
            if ($filterJenis  !== 'semua') $info .= " | Tipe: {$filterJenis}";
            if ($filterStatus !== '')      $info .= " | Status: {$filterStatus}";
            if ($filterAlat   !== '')      $info .= " | Alat: #{$filterAlat}";
            if ($filterUid    !== '')      $info .= " | UID: {$filterUid}";
            if ($search       !== '')      $info .= " | Cari: {$search}";
            $info .= " | " . count($rows) . " entri";

            header('Content-Type: text/html; charset=utf-8');
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8">
            <title>Log RFID ' . htmlspecialchars($tglAwal) . ' sd ' . htmlspecialchars($tglAkhir) . '</title>
            <style>
              *{font-family:Arial,sans-serif;font-size:10px;box-sizing:border-box}
              body{margin:16px;color:#0f172a}
              h2{font-size:15px;margin:0 0 4px}
              .info{color:#64748b;font-size:9px;margin-bottom:12px}
              .print-btn{display:inline-block;margin-bottom:10px;padding:6px 18px;
                background:#1e40af;color:#fff;border:none;border-radius:5px;cursor:pointer;font-size:12px}
              table{width:100%;border-collapse:collapse;page-break-inside:auto}
              thead{display:table-header-group}
              tr{page-break-inside:avoid;page-break-after:auto}
              th{background:#1e40af;color:#fff;padding:5px 7px;border:1px solid #1e3a8a;text-align:left}
              td{padding:4px 7px;border:1px solid #d1d5db;vertical-align:top}
              tr:nth-child(even) td{background:#f0f4ff}
              @media print{.print-btn{display:none}body{margin:8px}}
            </style></head><body>
            <h2>Log RFID &mdash; Sistem Monitoring Patroli</h2>
            <div class="info">' . htmlspecialchars($info) . ' &nbsp;|&nbsp; Dicetak: ' . date('d/m/Y H:i') . '</div>
            <button class="print-btn" onclick="window.print()">&#128438; Print / Simpan PDF</button>
            <table><thead><tr>';
            foreach ($headers as $h) echo '<th>' . htmlspecialchars($h) . '</th>';
            echo '</tr></thead><tbody>';
            foreach ($data as $r) {
                echo '<tr>';
                foreach ($r as $c) echo '<td>' . nl2br(htmlspecialchars((string)$c)) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table></body></html>';
            exit;
        }

        return redirect()->to(base_url('log-rfid'));
    }

    // -----------------------------------------------------------------
    // LIVE — AJAX polling setiap 30 detik
    // -----------------------------------------------------------------
    public function live()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $db = \Config\Database::connect();

        $rows = $db->query("
            SELECT
                t.tap_id                                       AS kunjungan_id,
                t.waktu_tap                                    AS waktu_kunjungan,
                t.uid_rfid                                     AS uid_kartu,
                t.jenis                                        AS jenis_log,
                t.status_validasi,
                COALESCE(u.nama_lengkap, 'Tidak Dikenal')     AS nama_petugas,
                COALESCE(p.kode_perangkat, '-')               AS kode_perangkat,
                COALESCE(r.kode_ruangan, '-')                  AS kode_ruangan,
                COALESCE(r.nama_ruangan, 'Ruangan N/A')       AS nama_ruangan
            FROM rfid_tap t
            LEFT JOIN users          u ON u.user_id    = t.user_id
            LEFT JOIN perangkat_rfid p ON p.alat_id    = t.alat_id
            LEFT JOIN ruangan        r ON r.ruangan_id = t.ruangan_id
            ORDER BY t.waktu_tap DESC
            LIMIT 30
        ")->getResultArray();

        return $this->response->setJSON([
            'ok'           => true,
            'data'         => $rows,
            'latest_waktu' => !empty($rows) ? $rows[0]['waktu_kunjungan'] : null,
        ]);
    }
}