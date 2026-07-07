<?php
// ================================================================
// LOKASI: app/Controllers/LaporanController.php
// VERSI : v2.1 — Tambah filter petugas
//
// PERUBAHAN DARI v2.0:
// 1. index()            → tambah parameter $petugasId dari GET
//                         tambah WHERE petugas_1 / petugas_2 jika dipilih
//                         pass $allPetugas & $filterPetugas ke view
// 2. buildTabelExport() → tambah filter petugas_id opsional
//
// TIDAK ADA perubahan lain — semua fungsi lama tetap utuh.
// ================================================================

namespace App\Controllers;

use App\Libraries\LCSService;

class LaporanController extends BaseController
{
    protected LCSService $lcs;

    public function __construct()
    {
        $this->lcs = new LCSService();
    }

    // ════════════════════════════════════════════════════════════
    // 1. DASHBOARD LAPORAN
    // ════════════════════════════════════════════════════════════
    public function index()
    {
        $db = \Config\Database::connect();

        $this->repairStatusValidasi($db);
        $this->autoEvaluasiPending($db);

        $periode   = $this->request->getGet('periode')    ?? 'hari_ini';
        $shiftId   = $this->request->getGet('shift_id')   ?? '';
        $tglAwal   = $this->request->getGet('tgl_awal')   ?? '';
        $tglAkhir  = $this->request->getGet('tgl_akhir')  ?? '';
        // ── TAMBAHAN: filter petugas ─────────────────────────
        $petugasId = $this->request->getGet('petugas_id') ?? '';

        [$tglAwal, $tglAkhir] = $this->resolvePeriode($periode, $tglAwal, $tglAkhir);

        // ── Query jadwal (tanpa regu) ────────────────────────────
        $jadwalQuery = $db->table('jadwal_shift js')
            ->select('
                js.jadwal_shift_id,
                js.tanggal,
                js.status_shift,
                js.warna,
                js.catatan,
                s.shift_id,
                s.nama_shift,
                s.jam_mulai,
                s.jam_selesai,
                u1.user_id      AS petugas_1_id,
                u1.nama_lengkap AS petugas_1_nama,
                u2.user_id      AS petugas_2_id,
                u2.nama_lengkap AS petugas_2_nama
            ')
            ->join('shift s',  's.shift_id = js.shift_id')
            ->join('users u1', 'u1.user_id = js.petugas_1', 'left')
            ->join('users u2', 'u2.user_id = js.petugas_2', 'left')
            ->where('js.tanggal >=', $tglAwal)
            ->where('js.tanggal <=', $tglAkhir)
            ->orderBy('js.tanggal', 'ASC')
            ->orderBy('s.shift_id', 'ASC');

        if ($shiftId) {
            $jadwalQuery->where('s.shift_id', $shiftId);
        }

        // ── TAMBAHAN: filter jadwal berdasarkan petugas ──────────
        if ($petugasId) {
            $jadwalQuery->groupStart()
                ->where('js.petugas_1', (int) $petugasId)
                ->orWhere('js.petugas_2', (int) $petugasId)
            ->groupEnd();
        }

        $jadwalList = $jadwalQuery->get()->getResultArray();
        $jadwalIds  = array_column($jadwalList, 'jadwal_shift_id');

        $hasilMap = [];
        $scanMap  = [];

        if (!empty($jadwalIds)) {
            // ── Hasil patroli ────────────────────────────────────
            $hasilQuery = $db->table('patroli_hasil ph')
                ->select('ph.*, u.nama_lengkap,
                          pll.urutan_ideal, pll.urutan_aktual, pll.nilai_lcs')
                ->join('users u',             'u.user_id            = ph.petugas_id')
                ->join('patroli_lcs_log pll', 'pll.patroli_hasil_id = ph.id', 'left')
                ->whereIn('ph.jadwal_shift_id', $jadwalIds)
                ->orderBy('ph.patroli_ke', 'ASC');

            // ── TAMBAHAN: filter hasil hanya untuk petugas terpilih
            if ($petugasId) {
                $hasilQuery->where('ph.petugas_id', (int) $petugasId);
            }

            $hasilRows = $hasilQuery->get()->getResultArray();

            foreach ($hasilRows as $h) {
                $hasilMap[$h['jadwal_shift_id']][$h['petugas_id']][] = $h;
            }

            // ── Scan dari rfid_tap (hanya Terdaftar) ─────────────
            $tapQuery = $db->table('rfid_tap rt')
                ->select('
                    rt.tap_id,
                    rt.user_id,
                    rt.jadwal_shift_id,
                    rt.waktu_tap,
                    rt.is_lcs_match,
                    rt.status_validasi,
                    r.kode_ruangan,
                    r.nama_ruangan,
                    u.nama_lengkap
                ')
                ->join('ruangan r', 'r.ruangan_id = rt.ruangan_id')
                ->join('users u',   'u.user_id    = rt.user_id')
                ->whereIn('rt.jadwal_shift_id', $jadwalIds)
                ->where('rt.jenis', 'Terdaftar')
                ->orderBy('rt.waktu_tap', 'ASC');

            // ── TAMBAHAN: filter scan hanya untuk petugas terpilih
            if ($petugasId) {
                $tapQuery->where('rt.user_id', (int) $petugasId);
            }

            $tapRows = $tapQuery->get()->getResultArray();

            foreach ($tapRows as $t) {
                $scanMap[$t['jadwal_shift_id']][$t['user_id']][] = $t;
            }
        }

        $allShift  = $db->table('shift')->orderBy('shift_id', 'ASC')->get()->getResultArray();
        $idealKode = $this->getUrutanIdeal($db);

        // ── TAMBAHAN: ambil daftar petugas (role_id=2 = Petugas) ─
        $allPetugas = $db->table('users')
            ->select('user_id, nama_lengkap')
            ->where('role_id', 2)
            ->where('status_aktif', 'Aktif')
            ->orderBy('nama_lengkap', 'ASC')
            ->get()->getResultArray();

        return view('admin/laporan', [
            'title'        => 'Laporan Patroli',
            'periode'      => $periode,
            'tglAwal'      => $tglAwal,
            'tglAkhir'     => $tglAkhir,
            'filterShift'  => $shiftId,
            'allShift'     => $allShift,
            'jadwalList'   => $jadwalList,
            'hasilMap'     => $hasilMap,
            'scanMap'      => $scanMap,
            'idealKode'    => $idealKode,
            // ── TAMBAHAN ─────────────────────────────────────────
            'allPetugas'   => $allPetugas,
            'filterPetugas'=> $petugasId,
        ]);
    }

    // ════════════════════════════════════════════════════════════
    // 2. EVALUASI MANUAL / AJAX
    // ════════════════════════════════════════════════════════════
    public function evaluasiShift($jadwalShiftId)
    {
        $db     = \Config\Database::connect();
        $result = $this->prosesEvaluasi($db, (int) $jadwalShiftId);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON($result);
        }
        return $result['success']
            ? redirect()->to(base_url('laporan'))->with('success', 'Evaluasi berhasil.')
            : redirect()->back()->with('error', $result['message'] ?? 'Terjadi kesalahan.');
    }

    public function evaluasiSemua()
    {
        $db     = \Config\Database::connect();
        $shifts = $db->table('jadwal_shift')->get()->getResultArray();
        $done   = 0;
        foreach ($shifts as $s) {
            $r = $this->prosesEvaluasi($db, (int) $s['jadwal_shift_id']);
            if ($r['success'] && !empty($r['data'])) $done++;
        }
        return $this->response->setJSON([
            'status'   => true,
            'diproses' => $done,
            'total'    => count($shifts),
        ]);
    }

    // ════════════════════════════════════════════════════════════
    // 3. REPAIR STATUS — GET /laporan/repairStatus
    // ════════════════════════════════════════════════════════════
    public function repairStatus()
    {
        $db    = \Config\Database::connect();
        $fixed = $this->repairStatusValidasi($db);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'  => true,
                'message' => "Diperbaiki: {$fixed['diperbaiki']} record",
                'detail'  => $fixed,
            ]);
        }

        return redirect()->to(base_url('laporan'))
            ->with('success', "Status berhasil diperbaiki: {$fixed['diperbaiki']} record. "
                . "(dari hasil: {$fixed['dari_hasil']}, dari jadwal: {$fixed['dari_jadwal']})");
    }

    // ════════════════════════════════════════════════════════════
    // 4. EXPORT EXCEL
    // ════════════════════════════════════════════════════════════
    public function exportExcel()
    {
        $db = \Config\Database::connect();
        [$a, $b] = $this->resolvePeriode(
            $this->request->getGet('periode')   ?? 'bulan_ini',
            $this->request->getGet('tgl_awal')  ?? '',
            $this->request->getGet('tgl_akhir') ?? ''
        );
        $sid      = $this->request->getGet('shift_id')   ?? '';
        // ── TAMBAHAN: terima filter petugas untuk export ─────────
        $pidExport = $this->request->getGet('petugas_id') ?? '';

        $tabel = $this->buildTabelExport($db, $a, $b, $sid, $pidExport);

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="Laporan_Patroli_'
            . date('Ymd_His') . '.xls"');

        $bgs = [
            'Valid'         => '#d1fae5',
            'Normal'        => '#dbeafe',
            'Warning'       => '#fef3c7',
            'Tidak Lengkap' => '#fee2e2',
        ];

        echo '<html><head><meta charset="utf-8">
        <style>
        th{background:#1e3a5f;color:#fff;padding:5px 8px;font-size:11px}
        td{padding:4px 8px;font-size:10px;border:1px solid #d1d5db}
        h2{font-size:12px;color:#1e3a5f;margin:14px 0 4px}
        .t{font-size:14px;font-weight:bold;color:#1e3a5f}
        </style></head><body>';

        echo '<p class="t">LAPORAN PATROLI RFID — GKI BROMO MALANG</p>';
        echo '<p style="font-size:10px;color:#6b7280">Periode: '
             . date('d M Y', strtotime($a)) . ' – ' . date('d M Y', strtotime($b))
             . ' | Dicetak: ' . date('d M Y H:i') . '</p>';

        echo '<h2>Detail Patroli per Petugas</h2>';
        echo '<table border="1" cellspacing="0">
        <tr>
          <th>#</th><th>Tanggal</th><th>Shift</th><th>Jam Mulai</th><th>Jam Selesai</th>
          <th>Petugas</th><th>Patroli ke-</th><th>Scan</th><th>Titik</th>
          <th>Kelengkapan</th><th>Kepatuhan Rute</th><th>Status</th>
          <th>Scan Pertama</th><th>Scan Terakhir</th>
        </tr>';

        foreach ($tabel as $i => $r) {
            $bg = $bgs[$r['status_patroli'] ?? ''] ?? '#fff';
            echo '<tr>'
               . '<td align="center">' . ($i + 1) . '</td>'
               . '<td>' . date('d/m/Y', strtotime($r['tanggal'])) . '</td>'
               . '<td>' . htmlspecialchars($r['nama_shift'],   ENT_QUOTES) . '</td>'
               . '<td>' . substr($r['jam_mulai'],   0, 5) . '</td>'
               . '<td>' . substr($r['jam_selesai'], 0, 5) . '</td>'
               . '<td>' . htmlspecialchars($r['nama_lengkap'], ENT_QUOTES) . '</td>'
               . '<td align="center">' . $r['patroli_ke'] . '</td>'
               . '<td align="center">' . $r['jumlah_scan'] . '</td>'
               . '<td align="center">' . $r['jumlah_titik'] . '</td>'
               . '<td align="center">' . number_format((float) $r['coverage_persen'], 1) . '%</td>'
               . '<td align="center">' . number_format((float) $r['lcs_persen'],      1) . '%</td>'
               . '<td align="center" style="background:' . $bg . ';font-weight:bold">'
                   . htmlspecialchars($r['status_patroli'] ?? '-', ENT_QUOTES) . '</td>'
               . '<td>' . ($r['waktu_pertama']  ? date('H:i:s', strtotime($r['waktu_pertama']))  : '-') . '</td>'
               . '<td>' . ($r['waktu_terakhir'] ? date('H:i:s', strtotime($r['waktu_terakhir'])) : '-') . '</td>'
               . '</tr>';
        }
        echo '</table></body></html>';
        exit;
    }

    // ════════════════════════════════════════════════════════════
    // PRIVATE: REPAIR STATUS VALIDASI
    //
    // Memperbaiki rfid_tap yang sudah punya jadwal_shift_id
    // tetapi status_validasi masih 'Tidak Terjadwal'.
    //
    // Hanya bekerja pada tap jenis='Terdaftar'.
    // ════════════════════════════════════════════════════════════
    private function repairStatusValidasi($db): array
    {
        // ── Case 1: Sinkronkan dari patroli_hasil ──────────────
        $db->query("
            UPDATE rfid_tap rt
            JOIN patroli_hasil ph
                ON  ph.jadwal_shift_id = rt.jadwal_shift_id
                AND ph.petugas_id      = rt.user_id
            SET rt.status_validasi = CASE
                WHEN ph.status_patroli IN ('Valid', 'Normal', 'Warning') THEN 'Sesuai'
                WHEN ph.status_patroli = 'Tidak Lengkap'                 THEN 'Di Luar Jadwal'
                ELSE rt.status_validasi
            END
            WHERE rt.jadwal_shift_id IS NOT NULL
              AND rt.jenis           = 'Terdaftar'
              AND rt.status_validasi = 'Tidak Terjadwal'
        ");
        $case1 = $db->affectedRows();

        // ── Case 2: Jadwal sudah di-link tapi belum ada patroli_hasil ──
        $db->query("
            UPDATE rfid_tap
            SET status_validasi = 'Sesuai'
            WHERE jadwal_shift_id IS NOT NULL
              AND jenis           = 'Terdaftar'
              AND status_validasi = 'Tidak Terjadwal'
        ");
        $case2 = $db->affectedRows();

        return [
            'diperbaiki'  => $case1 + $case2,
            'dari_hasil'  => $case1,
            'dari_jadwal' => $case2,
        ];
    }

    // ════════════════════════════════════════════════════════════
    // PRIVATE: AUTO-EVALUASI PENDING
    // ════════════════════════════════════════════════════════════
    private function autoEvaluasiPending($db): void
    {
        // 1. Evaluasi jadwal yang sudah punya tap tapi belum ada patroli_hasil
        $rows = $db->query("
            SELECT DISTINCT rt.jadwal_shift_id
            FROM rfid_tap rt
            WHERE rt.jadwal_shift_id IS NOT NULL
              AND rt.jenis = 'Terdaftar'
              AND NOT EXISTS (
                  SELECT 1 FROM patroli_hasil ph
                  WHERE ph.jadwal_shift_id = rt.jadwal_shift_id
              )
        ")->getResultArray();

        foreach ($rows as $row) {
            $this->prosesEvaluasi($db, (int) $row['jadwal_shift_id']);
        }

        // 2. Coba cocokkan tap tanpa jadwal_shift_id ke jadwal yang ada
        $unlinked = $db->query("
            SELECT DISTINCT rt.user_id, DATE(rt.waktu_tap) AS tgl
            FROM rfid_tap rt
            WHERE rt.jadwal_shift_id IS NULL
              AND rt.jenis           = 'Terdaftar'
              AND rt.user_id         IS NOT NULL
        ")->getResultArray();

        foreach ($unlinked as $u) {
            $uid = (int) $u['user_id'];
            $tgl = $u['tgl'];

            $jadwals = $db->table('jadwal_shift js')
                ->join('shift s', 's.shift_id = js.shift_id')
                ->where('js.tanggal', $tgl)
                ->groupStart()
                    ->where('js.petugas_1', $uid)
                    ->orWhere('js.petugas_2', $uid)
                ->groupEnd()
                ->get()->getResultArray();

            foreach ($jadwals as $j) {
                $jm   = $j['jam_mulai'];
                $js   = $j['jam_selesai'];
                $jsId = (int) $j['jadwal_shift_id'];

                if ($js > $jm) {
                    // Shift normal
                    $db->query("
                        UPDATE rfid_tap
                        SET jadwal_shift_id = ?
                        WHERE user_id       = ?
                          AND jenis         = 'Terdaftar'
                          AND jadwal_shift_id IS NULL
                          AND DATE(waktu_tap) = ?
                          AND TIME(waktu_tap) >= ?
                          AND TIME(waktu_tap) <  ?
                    ", [$jsId, $uid, $tgl, $jm, $js]);
                } else {
                    // Shift malam (lintas tengah malam)
                    $besok = date('Y-m-d', strtotime($tgl . ' +1 day'));
                    $db->query("
                        UPDATE rfid_tap
                        SET jadwal_shift_id = ?
                        WHERE user_id       = ?
                          AND jenis         = 'Terdaftar'
                          AND jadwal_shift_id IS NULL
                          AND (
                            (DATE(waktu_tap) = ? AND TIME(waktu_tap) >= ?)
                            OR
                            (DATE(waktu_tap) = ? AND TIME(waktu_tap) <  ?)
                          )
                    ", [$jsId, $uid, $tgl, $jm, $besok, $js]);
                }
            }
        }

        // 3. Evaluasi ulang jadwal yang tapnya baru di-link
        $newLinked = $db->query("
            SELECT DISTINCT rt.jadwal_shift_id
            FROM rfid_tap rt
            WHERE rt.jadwal_shift_id IS NOT NULL
              AND rt.jenis = 'Terdaftar'
              AND NOT EXISTS (
                  SELECT 1 FROM patroli_hasil ph
                  WHERE ph.jadwal_shift_id = rt.jadwal_shift_id
              )
        ")->getResultArray();

        foreach ($newLinked as $row) {
            $this->prosesEvaluasi($db, (int) $row['jadwal_shift_id']);
        }

        // 4. Repair status yang masih salah setelah evaluasi
        $this->repairStatusValidasi($db);
    }

    // ════════════════════════════════════════════════════════════
    // PRIVATE: INTI EVALUASI LCS PER SHIFT
    // Membaca dari rfid_tap, filter jenis='Terdaftar' + 'Sesuai'
    // ════════════════════════════════════════════════════════════
    private function prosesEvaluasi($db, int $jadwalShiftId): array
    {
        $jadwal = $db->table('jadwal_shift js')
            ->select('js.*, s.jam_mulai, s.jam_selesai, s.nama_shift')
            ->join('shift s', 's.shift_id = js.shift_id')
            ->where('js.jadwal_shift_id', $jadwalShiftId)
            ->get()->getRowArray();

        if (!$jadwal) {
            return ['success' => false, 'message' => 'Shift tidak ditemukan.'];
        }

        $idealKode = $this->getUrutanIdeal($db);
        $p1        = (int) ($jadwal['petugas_1'] ?? 0);
        $p2        = (int) ($jadwal['petugas_2'] ?? 0);
        $hasilAll  = [];

        foreach (array_filter([$p1, $p2]) as $uid) {
            $uid = (int) $uid;

            // ── Ambil tap dari rfid_tap ──────────────────────────
            $tapRows = $db->table('rfid_tap rt')
                ->select('rt.tap_id, rt.waktu_tap, r.kode_ruangan, r.nama_ruangan')
                ->join('ruangan r', 'r.ruangan_id = rt.ruangan_id')
                ->where('rt.user_id',         $uid)
                ->where('rt.jadwal_shift_id', $jadwalShiftId)
                ->where('rt.jenis',           'Terdaftar')
                ->where('rt.status_validasi', 'Sesuai')
                ->orderBy('rt.waktu_tap', 'ASC')
                ->get()->getResultArray();

            // ── Fallback: cocokkan berdasarkan jam shift ─────────
            if (empty($tapRows)) {
                $tapRows = $this->fetchTapByJam($db, $uid, $jadwal);
                if (!empty($tapRows)) {
                    $ids = array_column($tapRows, 'tap_id');
                    $db->table('rfid_tap')
                       ->whereIn('tap_id', $ids)
                       ->update([
                           'jadwal_shift_id' => $jadwalShiftId,
                           'status_validasi' => 'Sesuai',
                       ]);
                }
            }

            if (empty($tapRows)) continue;

            // Alias 'waktu_tap' → 'waktu_kunjungan' agar LCSService
            // kompatibel (LCSService v5 sudah bisa auto-detect kedua key)
            $tapForLcs = array_map(function ($row) {
                $row['waktu_kunjungan'] = $row['waktu_tap'];
                return $row;
            }, $tapRows);

            $offset = ($uid === $p1) ? 1 : 2;

            $hasilList = $this->lcs->evaluasiPetugas(
                $tapForLcs,
                $idealKode,
                $jadwalShiftId,
                $uid,
                $jadwal['tanggal'],
                $offset,
                2
            );

            foreach ($hasilList as $item) {
                $hasilId = $this->upsertHasil($db, $item['db']);

                // ── LCS log ──────────────────────────────────────
                $db->table('patroli_lcs_log')
                   ->where('patroli_hasil_id', $hasilId)->delete();
                $db->table('patroli_lcs_log')->insert([
                    'patroli_hasil_id' => $hasilId,
                    'urutan_ideal'     => implode(',', $item['lcs']['urutan_ideal']),
                    'urutan_aktual'    => implode(',', $item['lcs']['urutan_aktual']),
                    'nilai_lcs'        => $item['lcs']['persentase'],
                ]);

                // ── Titik terlewat ────────────────────────────────
                $db->table('patroli_titik_terlewat')
                   ->where('patroli_hasil_id', $hasilId)->delete();
                $this->simpanTitikTerlewat($db, $hasilId, $item['lcs']['titik_terlewat']);

                // ── Update is_lcs_match & status di rfid_tap ─────
                $this->updateTapRfid(
                    $db,
                    $item['sesi'],
                    $jadwalShiftId,
                    $item['lcs'],
                    $idealKode,
                    $item['status'],
                    $tapRows   // untuk mapping waktu_tap → tap_id
                );

                $hasilAll[] = $item['db'];
            }
        }

        // Update status shift → Selesai jika ada hasil
        if (!empty($hasilAll)) {
            $db->table('jadwal_shift')
               ->where('jadwal_shift_id', $jadwalShiftId)
               ->update(['status_shift' => 'Selesai']);
        }

        return ['success' => true, 'data' => $hasilAll];
    }

    // ════════════════════════════════════════════════════════════
    // PRIVATE: DB HELPERS
    // ════════════════════════════════════════════════════════════

    private function getUrutanIdeal($db): array
    {
        return array_column(
            $db->table('ruangan')
               ->where('aktif', 1)
               ->orderBy('urutan_patroli', 'ASC')
               ->get()->getResultArray(),
            'kode_ruangan'
        );
    }

    /**
     * Fallback: ambil tap dari rfid_tap berdasarkan rentang jam shift
     * untuk tap yang belum punya jadwal_shift_id.
     */
    private function fetchTapByJam($db, int $uid, array $jadwal): array
    {
        $tgl = $jadwal['tanggal'];
        $jm  = $jadwal['jam_mulai'];
        $js  = $jadwal['jam_selesai'];

        $base = $db->table('rfid_tap rt')
            ->select('rt.tap_id, rt.waktu_tap, r.kode_ruangan, r.nama_ruangan')
            ->join('ruangan r', 'r.ruangan_id = rt.ruangan_id')
            ->where('rt.user_id', $uid)
            ->where('rt.jenis',   'Terdaftar')
            ->where('rt.jadwal_shift_id IS NULL', null, false);

        if ($js > $jm) {
            return $base
                ->where("DATE(rt.waktu_tap)", $tgl)
                ->where("TIME(rt.waktu_tap) >=", $jm)
                ->where("TIME(rt.waktu_tap) <",  $js)
                ->orderBy('rt.waktu_tap', 'ASC')
                ->get()->getResultArray();
        }

        $besok = date('Y-m-d', strtotime($tgl . ' +1 day'));
        return $base->groupStart()
            ->where("DATE(rt.waktu_tap) = '$tgl' AND TIME(rt.waktu_tap) >= '$jm'")
            ->orWhere("DATE(rt.waktu_tap) = '$besok' AND TIME(rt.waktu_tap) < '$js'")
            ->groupEnd()
            ->orderBy('rt.waktu_tap', 'ASC')
            ->get()->getResultArray();
    }

    private function upsertHasil($db, array $data): int
    {
        $row = $db->table('patroli_hasil')
            ->where('jadwal_shift_id', $data['jadwal_shift_id'])
            ->where('petugas_id',      $data['petugas_id'])
            ->where('patroli_ke',      $data['patroli_ke'])
            ->get()->getRowArray();

        if ($row) {
            $db->table('patroli_hasil')->where('id', $row['id'])->update($data);
            return (int) $row['id'];
        }
        $db->table('patroli_hasil')->insert($data);
        return (int) $db->insertID();
    }

    private function simpanTitikTerlewat($db, int $hasilId, array $terlewat): void
    {
        if (empty($terlewat)) return;
        $mapKode = [];
        foreach ($db->table('ruangan')->get()->getResultArray() as $r) {
            $mapKode[$r['kode_ruangan']] = $r['ruangan_id'];
        }
        foreach ($terlewat as $kode) {
            if (isset($mapKode[$kode])) {
                $db->table('patroli_titik_terlewat')->insert([
                    'patroli_hasil_id' => $hasilId,
                    'ruangan_id'       => $mapKode[$kode],
                    'keterangan'       => 'Tidak dikunjungi pada sesi ini',
                ]);
            }
        }
    }

    /**
     * Update kolom rfid_tap setelah evaluasi LCS selesai.
     * Menggantikan updateKunjungan() versi lama.
     *
     * - status_validasi : 'Sesuai' (Valid/Normal/Warning) atau 'Di Luar Jadwal'
     * - is_lcs_match    : 1 jika titik ini termasuk dalam LCS sequence
     *
     * Identifikasi baris via tap_id (bukan kunjungan_id).
     */
    private function updateTapRfid(
        $db,
        array  $sesi,           // baris dari LCSService (punya key waktu_kunjungan & kode_ruangan)
        int    $jsId,
        array  $hasilLCS,
        array  $idealKode,
        string $statusPatroli,  // 'Valid' | 'Normal' | 'Warning' | 'Tidak Lengkap'
        array  $tapRows         // baris asli rfid_tap (punya tap_id & waktu_tap)
    ): void {
        $urutanMap = array_flip($idealKode);
        $lcsSet    = array_flip($hasilLCS['lcs_sequence'] ?? []);

        $statusValidasi = in_array($statusPatroli, ['Valid', 'Normal', 'Warning'])
            ? 'Sesuai'
            : 'Di Luar Jadwal';

        // Buat lookup waktu_tap → tap_id dari baris asli rfid_tap
        $tapLookup = [];
        foreach ($tapRows as $tr) {
            // Gunakan waktu_tap sebagai key; jika ada duplikat waktu, ambil yang pertama
            if (!isset($tapLookup[$tr['waktu_tap'] . '|' . $tr['kode_ruangan']])) {
                $tapLookup[$tr['waktu_tap'] . '|' . $tr['kode_ruangan']] = (int) $tr['tap_id'];
            }
        }

        foreach ($sesi as $idx => $k) {
            $kode = $k['kode_ruangan'];
            // waktu_kunjungan di sesi = alias dari waktu_tap (diset di prosesEvaluasi)
            $waktu  = $k['waktu_kunjungan'] ?? $k['waktu_tap'] ?? null;
            $tapKey = $waktu . '|' . $kode;
            $tapId  = $tapLookup[$tapKey] ?? null;

            if (!$tapId) continue;

            $db->table('rfid_tap')
               ->where('tap_id', $tapId)
               ->update([
                   'jadwal_shift_id'   => $jsId,
                   'urutan_aktual'     => $idx + 1,
                   'urutan_seharusnya' => isset($urutanMap[$kode]) ? $urutanMap[$kode] + 1 : null,
                   'is_lcs_match'      => isset($lcsSet[$kode]) ? 1 : 0,
                   'status_validasi'   => $statusValidasi,
               ]);
        }
    }

    // ════════════════════════════════════════════════════════════
    // PRIVATE: QUERY BUILDERS
    // ════════════════════════════════════════════════════════════

    private function resolvePeriode(string $p, string $a, string $b): array
    {
        $today = date('Y-m-d');
        return match ($p) {
            'hari_ini'   => [$today, $today],
            'minggu_ini' => [date('Y-m-d', strtotime('monday this week')),
                             date('Y-m-d', strtotime('sunday this week'))],
            'bulan_ini'  => [date('Y-m-01'), date('Y-m-t')],
            'tahun_ini'  => [date('Y-01-01'), date('Y-12-31')],
            'custom'     => [$a ?: date('Y-m-01'), $b ?: $today],
            default      => [$today, $today],
        };
    }

    /**
     * Build data untuk export Excel.
     * JOIN ke rfid_tap untuk waktu pertama/terakhir tap.
     *
     * TAMBAHAN v2.1: parameter $pidExport opsional untuk filter petugas.
     */
    private function buildTabelExport($db, string $a, string $b, string $sid, string $pid = ''): array
    {
        $q = $db->table('patroli_hasil ph')
            ->select('
                ph.tanggal,
                ph.patroli_ke,
                ph.jumlah_scan,
                ph.jumlah_titik,
                ph.coverage_persen,
                ph.lcs_persen,
                ph.status_patroli,
                u.nama_lengkap,
                s.nama_shift,
                s.jam_mulai,
                s.jam_selesai,
                MIN(rt.waktu_tap) AS waktu_pertama,
                MAX(rt.waktu_tap) AS waktu_terakhir
            ')
            ->join('users u',         'u.user_id          = ph.petugas_id')
            ->join('jadwal_shift js', 'js.jadwal_shift_id = ph.jadwal_shift_id')
            ->join('shift s',         's.shift_id         = js.shift_id')
            ->join('rfid_tap rt',     'rt.jadwal_shift_id = ph.jadwal_shift_id
                                       AND rt.user_id     = ph.petugas_id
                                       AND rt.jenis       = \'Terdaftar\'', 'left')
            ->where('ph.tanggal >=', $a)
            ->where('ph.tanggal <=', $b)
            ->groupBy('ph.id')
            ->orderBy('ph.tanggal', 'DESC')
            ->orderBy('ph.patroli_ke', 'ASC');

        if ($sid) $q->where('js.shift_id', $sid);

        // ── TAMBAHAN: filter petugas pada export ─────────────────
        if ($pid) $q->where('ph.petugas_id', (int) $pid);

        return $q->get()->getResultArray();
    }
}