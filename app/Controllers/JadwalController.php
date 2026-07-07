<?php
// ================================================================
// LOKASI : app/Controllers/JadwalController.php
// VERSI  : v2.2 — Export Excel format Jadwal Dinas (matriks)
//
// PERUBAHAN dari v2.1:
// - exportExcel(): format tabel matriks seperti jadwal dinas
//   Baris = petugas, Kolom = tanggal (1–31)
//   Cell = kode shift (P/S/M) atau kosong jika tidak bertugas
//   Output: file .xlsx (Content-Type Excel) dengan encoding UTF-8
// - Tombol view: "Unduh Jadwal" (bukan "Unduh Laporan")
// - Tambah exportExcelXlsx() pakai output buffer HTML tabel
//   dengan BOM UTF-8 agar Excel tidak garbled
// ================================================================

namespace App\Controllers;

class JadwalController extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ─────────────────────────────────────────────────────────────
    // AUTO UPDATE STATUS SHIFT
    // ─────────────────────────────────────────────────────────────
    private function _autoUpdateStatusShift(): void
    {
        $tanggalHariIni = date('Y-m-d');
        $jamSekarang    = date('H:i:s');

        $this->db->query("
            UPDATE jadwal_shift js
            JOIN shift s ON js.shift_id = s.shift_id
            SET js.status_shift = 'Selesai'
            WHERE js.tanggal < ?
              AND js.status_shift != 'Selesai'
        ", [$tanggalHariIni]);

        $shiftsHariIni = $this->db->query("
            SELECT js.jadwal_shift_id, js.status_shift,
                   s.jam_mulai, s.jam_selesai
            FROM jadwal_shift js
            JOIN shift s ON js.shift_id = s.shift_id
            WHERE js.tanggal = ?
        ", [$tanggalHariIni])->getResultArray();

        foreach ($shiftsHariIni as $row) {
            $statusBaru = $this->_hitungStatusShift(
                $jamSekarang,
                $row['jam_mulai'],
                $row['jam_selesai']
            );
            if ($statusBaru !== $row['status_shift']) {
                $this->db->query(
                    "UPDATE jadwal_shift SET status_shift = ? WHERE jadwal_shift_id = ?",
                    [$statusBaru, $row['jadwal_shift_id']]
                );
            }
        }
    }

    private function _hitungStatusShift(string $jamNow, string $jamMulai, string $jamSelesai): string
    {
        $now     = $this->_jamKeMenit($jamNow);
        $mulai   = $this->_jamKeMenit($jamMulai);
        $selesai = $this->_jamKeMenit($jamSelesai);

        if ($mulai < $selesai) {
            if ($now < $mulai)   return 'Belum Mulai';
            if ($now < $selesai) return 'Berjalan';
            return 'Selesai';
        } else {
            if ($now >= $mulai || $now < $selesai) return 'Berjalan';
            return 'Belum Mulai';
        }
    }

    private function _jamKeMenit(string $jam): int
    {
        $parts = explode(':', $jam);
        return (int)($parts[0]) * 60 + (int)($parts[1] ?? 0);
    }

    // ─────────────────────────────────────────────────────────────
    // GET /jadwal
    // ─────────────────────────────────────────────────────────────
    public function index()
    {
        $this->_autoUpdateStatusShift();

        $users = $this->db->table('users')
            ->select('user_id, nama_lengkap, username')
            ->where('role_id', 2)
            ->where('status_aktif', 'Aktif')
            ->orderBy('nama_lengkap', 'ASC')
            ->get()->getResultArray();

        $shifts = $this->db->table('shift')
            ->get()->getResultArray();

        return view('admin/jadwal', [
            'title'  => 'Jadwal Patroli',
            'users'  => $users,
            'shifts' => $shifts,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // GET /jadwal/getByDate?date=2026-06-04
    // ─────────────────────────────────────────────────────────────
    public function getByDate()
    {
        $date = $this->request->getGet('date');
        if (!$date) return $this->response->setJSON([]);

        $this->_autoUpdateStatusShift();

        $rows = $this->db->table('jadwal_shift js')
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
            ->where('js.tanggal', $date)
            ->orderBy('s.shift_id', 'ASC')
            ->get()->getResultArray();

        return $this->response->setJSON($rows);
    }

    // ─────────────────────────────────────────────────────────────
    // GET /jadwal/getByMonth?month=2026-06
    // ─────────────────────────────────────────────────────────────
    public function getByMonth()
    {
        $month = $this->request->getGet('month');
        if (!$month) return $this->response->setJSON([]);

        $rows = $this->db->table('jadwal_shift js')
            ->select("js.tanggal, COUNT(*) as jumlah_shift, GROUP_CONCAT(js.warna ORDER BY js.shift_id) as warna_list")
            ->join('shift s', 's.shift_id = js.shift_id')
            ->where("DATE_FORMAT(js.tanggal, '%Y-%m')", $month)
            ->groupBy('js.tanggal')
            ->get()->getResultArray();

        $map = [];
        foreach ($rows as $r) {
            $map[$r['tanggal']] = [
                'jumlah' => (int)$r['jumlah_shift'],
                'warna'  => explode(',', $r['warna_list'] ?? '#3b82f6')[0],
            ];
        }
        return $this->response->setJSON($map);
    }

    // ─────────────────────────────────────────────────────────────
    // GET /jadwal/syncStatus
    // ─────────────────────────────────────────────────────────────
    public function syncStatus()
    {
        $this->_autoUpdateStatusShift();

        $rows = $this->db->query("
            SELECT js.jadwal_shift_id, js.status_shift, s.nama_shift
            FROM jadwal_shift js
            JOIN shift s ON js.shift_id = s.shift_id
            WHERE js.tanggal = CURDATE()
        ")->getResultArray();

        return $this->response->setJSON([
            'ok'     => true,
            'waktu'  => date('H:i:s'),
            'jadwal' => $rows,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // POST /jadwal/save
    // ─────────────────────────────────────────────────────────────
    public function save()
    {
        $tanggal   = $this->request->getPost('tanggal');
        $shift_id  = $this->request->getPost('shift_id');
        $petugas_1 = $this->request->getPost('petugas_1');
        $petugas_2 = $this->request->getPost('petugas_2');
        $warna     = $this->request->getPost('warna')   ?? '#3b82f6';
        $catatan   = $this->request->getPost('catatan') ?? '';

        if (!$tanggal || !$shift_id || !$petugas_1 || !$petugas_2) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Tanggal, shift, Petugas 1, dan Petugas 2 wajib diisi.',
            ]);
        }

        if ($petugas_1 == $petugas_2) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Petugas 1 dan Petugas 2 tidak boleh orang yang sama.',
            ]);
        }

        $existing = $this->db->table('jadwal_shift')
            ->where('tanggal',  $tanggal)
            ->where('shift_id', $shift_id)
            ->get()->getRow();

        $data = [
            'petugas_1' => $petugas_1,
            'petugas_2' => $petugas_2,
            'warna'     => $warna,
            'catatan'   => $catatan,
        ];

        if ($existing) {
            if (in_array($existing->status_shift, ['Berjalan', 'Selesai'])) {
                $this->db->table('jadwal_shift')
                    ->where('jadwal_shift_id', $existing->jadwal_shift_id)
                    ->update(['warna' => $warna, 'catatan' => $catatan]);
                return $this->response->setJSON([
                    'status'  => true,
                    'message' => 'Jadwal diperbarui (warna & catatan saja — shift sudah '
                        . $existing->status_shift . ').',
                ]);
            }
            $this->db->table('jadwal_shift')
                ->where('jadwal_shift_id', $existing->jadwal_shift_id)
                ->update($data);
            $msg = 'Jadwal shift berhasil diperbarui.';
        } else {
            $shiftRow = $this->db->table('shift')
                ->where('shift_id', $shift_id)
                ->get()->getRow();

            $statusAwal = 'Belum Mulai';
            if ($shiftRow && $tanggal === date('Y-m-d')) {
                $statusAwal = $this->_hitungStatusShift(
                    date('H:i:s'),
                    $shiftRow->jam_mulai,
                    $shiftRow->jam_selesai
                );
            }

            $data['tanggal']      = $tanggal;
            $data['shift_id']     = $shift_id;
            $data['status_shift'] = $statusAwal;
            $this->db->table('jadwal_shift')->insert($data);
            $msg = 'Jadwal shift berhasil disimpan (status: ' . $statusAwal . ').';
        }

        return $this->response->setJSON(['status' => true, 'message' => $msg]);
    }

    // ─────────────────────────────────────────────────────────────
    // POST /jadwal/delete
    // ─────────────────────────────────────────────────────────────
    public function delete()
    {
        $id = $this->request->getPost('jadwal_shift_id');
        if (!$id) {
            return $this->response->setJSON(['status' => false, 'message' => 'ID tidak valid.']);
        }

        $row = $this->db->table('jadwal_shift')
            ->where('jadwal_shift_id', $id)
            ->get()->getRow();

        if (!$row) {
            return $this->response->setJSON(['status' => false, 'message' => 'Jadwal tidak ditemukan.']);
        }

        if (in_array($row->status_shift, ['Berjalan', 'Selesai'])) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Jadwal tidak dapat dihapus — status sudah ' . $row->status_shift . '.',
            ]);
        }

        $this->db->table('jadwal_shift')->where('jadwal_shift_id', $id)->delete();
        return $this->response->setJSON(['status' => true, 'message' => 'Jadwal berhasil dihapus.']);
    }

    // ─────────────────────────────────────────────────────────────
    // GET /jadwal/export-excel?month=2026-06
    // Format: Jadwal Dinas — matriks petugas vs tanggal
    //
    // Struktur:
    //   Baris header : Nama Petugas | 1 | 2 | 3 | ... | 31 | Jml P | Jml S | Jml M
    //   Setiap baris : 1 petugas, cell = P / S / M (atau kosong)
    //   Kode shift   : P = Pagi, S = Siang, M = Malam
    //   Footer       : jumlah petugas per hari (baris total)
    //
    // Encoding: UTF-8 BOM agar Excel tidak garbled
    // ─────────────────────────────────────────────────────────────
    public function exportExcel()
    {
        $month = $this->request->getGet('month');
        if (!$month) { echo 'Parameter month diperlukan'; exit; }

        // ── Parse tahun & bulan ──
        [$yr, $mo] = explode('-', $month);
        $yr  = (int)$yr;
        $mo  = (int)$mo;
        $totalDays = (int)date('t', mktime(0,0,0,$mo,1,$yr));

        $namaBulan = [
            1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
            5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
            9=>'September',10=>'Oktober',11=>'November',12=>'Desember',
        ];
        $labelBulan = strtoupper($namaBulan[$mo] . ' ' . $yr);

        // ── Ambil semua petugas aktif (role=2) ──
        $users = $this->db->table('users')
            ->select('user_id, nama_lengkap')
            ->where('role_id', 2)
            ->where('status_aktif', 'Aktif')
            ->orderBy('nama_lengkap', 'ASC')
            ->get()->getResultArray();

        // ── Ambil semua jadwal bulan ini ──
        $jadwals = $this->db->table('jadwal_shift js')
            ->select('
                js.tanggal,
                js.shift_id,
                s.nama_shift,
                js.status_shift,
                u1.user_id AS petugas_1_id,
                u2.user_id AS petugas_2_id
            ')
            ->join('shift s',  's.shift_id = js.shift_id')
            ->join('users u1', 'u1.user_id = js.petugas_1', 'left')
            ->join('users u2', 'u2.user_id = js.petugas_2', 'left')
            ->where("DATE_FORMAT(js.tanggal, '%Y-%m')", $month)
            ->orderBy('js.tanggal', 'ASC')
            ->orderBy('js.shift_id', 'ASC')
            ->get()->getResultArray();

        // ── Kode shift ──
        $kodeShift = ['Pagi'=>'P', 'Siang'=>'S', 'Malam'=>'M'];

        // ── Bangun lookup: [user_id][tanggal] = kode shift (P/S/M) ──
        // Jika petugas bertugas di 2 shift dalam sehari, gabungkan (P+S)
        $lookup = [];
        foreach ($jadwals as $j) {
            $kode = $kodeShift[$j['nama_shift']] ?? substr($j['nama_shift'],0,1);
            $tgl  = (int)date('j', strtotime($j['tanggal']));
            foreach (['petugas_1_id','petugas_2_id'] as $f) {
                if (!empty($j[$f])) {
                    $uid = $j[$f];
                    if (!isset($lookup[$uid][$tgl])) {
                        $lookup[$uid][$tgl] = $kode;
                    } else {
                        // Sudah ada shift lain di hari yang sama → gabungkan
                        if (strpos($lookup[$uid][$tgl], $kode) === false) {
                            $lookup[$uid][$tgl] .= '+'.$kode;
                        }
                    }
                }
            }
        }

        // ── Bangun lookup status hari (untuk warna akhir pekan) ──
        // Hari minggu = 0, sabtu = 6
        $hariAkhirPekan = [];
        for ($d = 1; $d <= $totalDays; $d++) {
            $dow = (int)date('w', mktime(0,0,0,$mo,$d,$yr));
            $hariAkhirPekan[$d] = ($dow === 0 || $dow === 6);
        }

        // ── Hitung jumlah shift per petugas (P, S, M) ──
        $jumlahShift = []; // [user_id] => ['P'=>n,'S'=>n,'M'=>n,'total'=>n]
        foreach ($users as $u) {
            $uid = $u['user_id'];
            $jumlahShift[$uid] = ['P'=>0,'S'=>0,'M'=>0,'total'=>0];
            for ($d = 1; $d <= $totalDays; $d++) {
                $kode = $lookup[$uid][$d] ?? '';
                if ($kode) {
                    foreach (['P','S','M'] as $k) {
                        if (strpos($kode,$k) !== false) $jumlahShift[$uid][$k]++;
                    }
                    $jumlahShift[$uid]['total']++;
                }
            }
        }

        // ── Hitung jumlah petugas per hari & per shift (baris footer) ──
        $footerPagi  = []; // [hari] => jumlah petugas shift P
        $footerSiang = [];
        $footerMalam = [];
        $footerTotal = [];
        for ($d = 1; $d <= $totalDays; $d++) {
            $countP = $countS = $countM = 0;
            foreach ($users as $u) {
                $kode = $lookup[$u['user_id']][$d] ?? '';
                if (strpos($kode,'P')!==false) $countP++;
                if (strpos($kode,'S')!==false) $countS++;
                if (strpos($kode,'M')!==false) $countM++;
            }
            $footerPagi[$d]  = $countP ?: '';
            $footerSiang[$d] = $countS ?: '';
            $footerMalam[$d] = $countM ?: '';
            $footerTotal[$d] = ($countP+$countS+$countM) ?: '';
        }

        // ════════════════════════════════════════════════
        // OUTPUT HTML → Excel (dengan BOM UTF-8)
        // Menggunakan tabel HTML yang dirender Excel
        // ════════════════════════════════════════════════
        $filename = 'Jadwal_Patroli_' . $labelBulan . '.xls';

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // BOM UTF-8 wajib agar Excel tidak garbled
        echo "\xEF\xBB\xBF";

        // ── Styles inline ──
        $styleTitle   = 'font-family:Arial;font-size:13pt;font-weight:bold;text-align:center;';
        $styleSubtitle= 'font-family:Arial;font-size:11pt;font-weight:bold;text-align:center;';
        $styleTh      = 'font-family:Arial;font-size:9pt;font-weight:bold;background:#1e3a5f;color:#ffffff;text-align:center;border:1px solid #999;white-space:nowrap;padding:4px 3px;';
        $styleThLight = 'font-family:Arial;font-size:9pt;font-weight:bold;background:#2563eb;color:#ffffff;text-align:center;border:1px solid #999;white-space:nowrap;padding:4px 3px;';
        $styleTd      = 'font-family:Arial;font-size:9pt;text-align:center;border:1px solid #ccc;padding:3px 2px;';
        $styleTdLeft  = 'font-family:Arial;font-size:9pt;text-align:left;border:1px solid #ccc;padding:3px 6px;white-space:nowrap;';
        $styleWeekend = 'font-family:Arial;font-size:9pt;font-weight:bold;background:#fff1f2;color:#dc2626;text-align:center;border:1px solid #ccc;padding:3px 2px;';
        $stylePagi    = 'font-family:Arial;font-size:9pt;font-weight:bold;background:#fef9c3;color:#854d0e;text-align:center;border:1px solid #ccc;padding:3px 2px;';
        $styleSiang   = 'font-family:Arial;font-size:9pt;font-weight:bold;background:#dbeafe;color:#1e40af;text-align:center;border:1px solid #ccc;padding:3px 2px;';
        $styleMalam   = 'font-family:Arial;font-size:9pt;font-weight:bold;background:#ede9fe;color:#5b21b6;text-align:center;border:1px solid #ccc;padding:3px 2px;';
        $styleGanda   = 'font-family:Arial;font-size:8pt;font-weight:bold;background:#fce7f3;color:#831843;text-align:center;border:1px solid #ccc;padding:3px 2px;';
        $styleEmpty   = 'font-family:Arial;font-size:9pt;text-align:center;border:1px solid #ccc;padding:3px 2px;background:#f8fafc;';
        $styleFooter  = 'font-family:Arial;font-size:9pt;font-weight:bold;background:#f1f5f9;color:#334155;text-align:center;border:1px solid #999;padding:3px 2px;';
        $styleFooterL = 'font-family:Arial;font-size:9pt;font-weight:bold;background:#f1f5f9;color:#334155;text-align:left;border:1px solid #999;padding:3px 6px;';
        $styleSummTh  = 'font-family:Arial;font-size:9pt;font-weight:bold;background:#16a34a;color:#ffffff;text-align:center;border:1px solid #999;padding:4px 6px;';
        $styleSummTd  = 'font-family:Arial;font-size:9pt;font-weight:bold;text-align:center;border:1px solid #ccc;padding:3px 6px;';

        $colCount = 1 + $totalDays + 4; // No | Nama | hari1..N | jmlP | jmlS | jmlM | total

        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office"
               xmlns:x="urn:schemas-microsoft-com:office:excel"
               xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta charset="utf-8">
        <xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>
        <x:Name>Jadwal Patroli</x:Name>
        <x:WorksheetOptions><x:Print><x:FitWidth>1</x:FitWidth></x:Print></x:WorksheetOptions>
        </x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml>
        </head><body>';

        echo '<table border="0" cellspacing="0" cellpadding="0">';

        // ── Judul ──
        echo '<tr><td colspan="' . $colCount . '" style="' . $styleTitle . '">JADWAL DINAS SECURITY</td></tr>';
        echo '<tr><td colspan="' . $colCount . '" style="' . $styleSubtitle . '">BULAN : ' . $labelBulan . '</td></tr>';
        echo '<tr><td colspan="' . $colCount . '" style="height:8px;"></td></tr>';

        // ── Header Tabel ──
        // Baris 1: No | Nama Petugas | tanggal (1..31) | Jml Pagi | Jml Siang | Jml Malam | Total
        echo '<tr>';
        echo '<th rowspan="2" style="' . $styleTh . ';width:25px;">No</th>';
        echo '<th rowspan="2" style="' . $styleTh . ';min-width:120px;">Nama Petugas</th>';

        // Header tanggal
        for ($d = 1; $d <= $totalDays; $d++) {
            $dow   = (int)date('w', mktime(0,0,0,$mo,$d,$yr));
            $isWE  = ($dow===0 || $dow===6);
            $bgHdr = $isWE ? 'background:#dc2626;' : 'background:#1e3a5f;';
            echo '<th style="' . $styleTh . $bgHdr . 'width:22px;">' . $d . '</th>';
        }

        echo '<th rowspan="2" style="' . $styleSummTh . ';background:#854d0e;width:35px;">Jml<br>P</th>';
        echo '<th rowspan="2" style="' . $styleSummTh . ';background:#1e40af;width:35px;">Jml<br>S</th>';
        echo '<th rowspan="2" style="' . $styleSummTh . ';background:#5b21b6;width:35px;">Jml<br>M</th>';
        echo '<th rowspan="2" style="' . $styleSummTh . ';background:#16a34a;width:40px;">Total<br>Shift</th>';
        echo '</tr>';

        // Baris 2: sub-header hari (Sen/Sel/...)
        $singkatHari = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
        echo '<tr>';
        for ($d = 1; $d <= $totalDays; $d++) {
            $dow  = (int)date('w', mktime(0,0,0,$mo,$d,$yr));
            $isWE = ($dow===0 || $dow===6);
            $bgHdr = $isWE ? 'background:#dc2626;' : 'background:#334155;';
            echo '<th style="' . $styleTh . $bgHdr . 'font-size:7pt;">' . $singkatHari[$dow] . '</th>';
        }
        echo '</tr>';

        // ── Baris Data Petugas ──
        $no = 1;
        foreach ($users as $u) {
            $uid = $u['user_id'];
            $js  = $jumlahShift[$uid];

            // Warna baris alternating
            $rowBg = ($no % 2 === 0) ? 'background:#f8fafc;' : 'background:#ffffff;';

            echo '<tr>';
            echo '<td style="' . $styleTd . $rowBg . 'text-align:center;">' . $no . '</td>';
            echo '<td style="' . $styleTdLeft . $rowBg . '">' . htmlspecialchars($u['nama_lengkap'], ENT_QUOTES, 'UTF-8') . '</td>';

            for ($d = 1; $d <= $totalDays; $d++) {
                $kode = $lookup[$uid][$d] ?? '';
                $dow  = (int)date('w', mktime(0,0,0,$mo,$d,$yr));
                $isWE = ($dow===0 || $dow===6);

                if ($kode === '') {
                    $cs = $isWE ? 'background:#fff1f2;border:1px solid #ccc;' : '';
                    echo '<td style="' . $styleEmpty . $cs . '">-</td>';
                } elseif ($kode === 'P') {
                    echo '<td style="' . $stylePagi . '">P</td>';
                } elseif ($kode === 'S') {
                    echo '<td style="' . $styleSiang . '">S</td>';
                } elseif ($kode === 'M') {
                    echo '<td style="' . $styleMalam . '">M</td>';
                } else {
                    // Shift ganda (misal P+S)
                    echo '<td style="' . $styleGanda . '">' . htmlspecialchars($kode, ENT_QUOTES, 'UTF-8') . '</td>';
                }
            }

            echo '<td style="' . $styleSummTd . 'background:#fef9c3;color:#854d0e;">' . ($js['P'] ?: '-') . '</td>';
            echo '<td style="' . $styleSummTd . 'background:#dbeafe;color:#1e40af;">' . ($js['S'] ?: '-') . '</td>';
            echo '<td style="' . $styleSummTd . 'background:#ede9fe;color:#5b21b6;">' . ($js['M'] ?: '-') . '</td>';
            echo '<td style="' . $styleSummTd . 'background:#dcfce7;color:#15803d;">' . ($js['total'] ?: '-') . '</td>';
            echo '</tr>';

            $no++;
        }

        // ── Baris Footer: Jumlah Petugas per Hari ──
        // Footer Pagi
        echo '<tr>';
        echo '<td colspan="2" style="' . $styleFooterL . '">Jumlah Petugas Pagi</td>';
        for ($d = 1; $d <= $totalDays; $d++) {
            echo '<td style="' . $styleFooter . 'background:#fef9c3;color:#854d0e;">' . ($footerPagi[$d] ?: '-') . '</td>';
        }
        echo '<td colspan="4" style="' . $styleFooter . '"></td>';
        echo '</tr>';

        // Footer Siang
        echo '<tr>';
        echo '<td colspan="2" style="' . $styleFooterL . '">Jumlah Petugas Siang</td>';
        for ($d = 1; $d <= $totalDays; $d++) {
            echo '<td style="' . $styleFooter . 'background:#dbeafe;color:#1e40af;">' . ($footerSiang[$d] ?: '-') . '</td>';
        }
        echo '<td colspan="4" style="' . $styleFooter . '"></td>';
        echo '</tr>';

        // Footer Malam
        echo '<tr>';
        echo '<td colspan="2" style="' . $styleFooterL . '">Jumlah Petugas Malam</td>';
        for ($d = 1; $d <= $totalDays; $d++) {
            echo '<td style="' . $styleFooter . 'background:#ede9fe;color:#5b21b6;">' . ($footerMalam[$d] ?: '-') . '</td>';
        }
        echo '<td colspan="4" style="' . $styleFooter . '"></td>';
        echo '</tr>';

        // ── Baris Kosong ──
        echo '<tr><td colspan="' . $colCount . '" style="height:16px;"></td></tr>';

        // ── Keterangan ──
        echo '<tr><td colspan="' . $colCount . '" style="font-family:Arial;font-size:9pt;font-weight:bold;">KETERANGAN :</td></tr>';
        echo '<tr><td colspan="' . $colCount . '" style="font-family:Arial;font-size:9pt;">P = Pagi (06:00 – 14:00)</td></tr>';
        echo '<tr><td colspan="' . $colCount . '" style="font-family:Arial;font-size:9pt;">S = Siang (14:00 – 22:00)</td></tr>';
        echo '<tr><td colspan="' . $colCount . '" style="font-family:Arial;font-size:9pt;">M = Malam (22:00 – 06:00)</td></tr>';
        echo '<tr><td colspan="' . $colCount . '" style="font-family:Arial;font-size:9pt;">- = Tidak Bertugas / Libur</td></tr>';
        echo '<tr><td colspan="' . $colCount . '" style="font-family:Arial;font-size:8pt;color:#dc2626;">* Kolom merah = Hari Minggu / Sabtu</td></tr>';

        // ── Baris Kosong ──
        echo '<tr><td colspan="' . $colCount . '" style="height:20px;"></td></tr>';

        // ── TTD / Tanda Tangan ──
        echo '<tr>';
        echo '<td colspan="' . intdiv($colCount, 2) . '" style="font-family:Arial;font-size:9pt;text-align:center;font-weight:bold;">Mengetahui,<br>Kepala Keamanan</td>';
        echo '<td colspan="' . ($colCount - intdiv($colCount, 2)) . '" style="font-family:Arial;font-size:9pt;text-align:center;font-weight:bold;">Dibuat oleh,<br>Koordinator Shift</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td colspan="' . intdiv($colCount, 2) . '" style="font-family:Arial;font-size:9pt;text-align:center;height:50px;">&nbsp;</td>';
        echo '<td colspan="' . ($colCount - intdiv($colCount, 2)) . '" style="font-family:Arial;font-size:9pt;text-align:center;">&nbsp;</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td colspan="' . intdiv($colCount, 2) . '" style="font-family:Arial;font-size:9pt;text-align:center;">(________________________)</td>';
        echo '<td colspan="' . ($colCount - intdiv($colCount, 2)) . '" style="font-family:Arial;font-size:9pt;text-align:center;">(________________________)</td>';
        echo '</tr>';

        echo '</table></body></html>';
        exit;
    }

}