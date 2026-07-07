<?php
// ================================================================
// LOKASI : app/Controllers/Api/RfidApi.php
// VERSI  : v5.0 — MIGRASI ke rfid_tap
//
// PERUBAHAN DARI v4.0:
// 1. Semua data tapping disimpan ke tabel `rfid_tap` (bukan kunjungan)
// 2. Kartu tidak terdaftar → insert rfid_tap dengan jenis='Asing',
//    user_id/jadwal_shift_id/status_validasi = NULL
// 3. Kartu aktif terdaftar → insert rfid_tap dengan jenis='Terdaftar'
//    beserta status_validasi, urutan_aktual, urutan_seharusnya, is_lcs_match
// 4. Duplicate tap prevention memakai rfid_tap
// 5. _evaluasiLCS() membaca dari rfid_tap bukan kunjungan,
//    dan update is_lcs_match di rfid_tap
// 6. Kolom yang dipakai di rfid_tap sesuai skema DB:
//    tap_id, waktu_tap, uid_rfid, alat_id, user_id, ruangan_id,
//    jadwal_shift_id, jenis, status_validasi,
//    urutan_aktual, urutan_seharusnya, is_lcs_match, is_offline_tap
// ================================================================

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\LCSService;

class RfidApi extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ─────────────────────────────────────────────────────────────
    // RFID SCAN
    // POST /api/rfid/scan
    // Body JSON:
    //   uid_rfid        : string  (wajib)
    //   kode_perangkat  : string  (wajib jika alat_id kosong)
    //   alat_id         : int     (opsional, prioritas di atas kode_perangkat)
    //   offline_tap     : bool    (opsional, default false)
    // ─────────────────────────────────────────────────────────────
    public function scan()
    {
        $data = $this->request->getJSON(true);

        $uid            = strtoupper(trim($data['uid_rfid']       ?? ''));
        $kode_perangkat = trim($data['kode_perangkat'] ?? '');
        $alat_id_input  = $data['alat_id']    ?? null;
        $isOfflineTap   = (bool)($data['offline_tap'] ?? false);

        log_message('debug', '[RFID SCAN] uid=' . $uid
            . ' kode=' . $kode_perangkat
            . ' offline=' . ($isOfflineTap ? 'yes' : 'no'));

        // ── VALIDASI INPUT ──────────────────────────────────────
        if (empty($uid)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'uid_rfid wajib diisi',
            ]);
        }

        if (empty($kode_perangkat) && empty($alat_id_input)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'kode_perangkat atau alat_id wajib diisi',
            ]);
        }

        // ── TEMUKAN DEVICE ──────────────────────────────────────
        if (!empty($alat_id_input) && (int)$alat_id_input > 0) {
            $device = $this->db->table('perangkat_rfid')
                ->where('alat_id', (int)$alat_id_input)
                ->get()->getRow();
        } else {
            $device = $this->db->table('perangkat_rfid')
                ->where('kode_perangkat', $kode_perangkat)
                ->get()->getRow();
        }

        if (!$device) {
            log_message('warning', '[RFID SCAN] Perangkat tidak ditemukan: kode='
                . $kode_perangkat . ' alat_id=' . ($alat_id_input ?? '-'));
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => false,
                'message' => 'Perangkat tidak ditemukan',
            ]);
        }

        // ── UPDATE DEVICE ONLINE ────────────────────────────────
        $now = date('Y-m-d H:i:s');
        $this->db->table('perangkat_rfid')
            ->where('alat_id', $device->alat_id)
            ->update([
                'status_perangkat' => 'Online',
                'last_online'      => $now,
                'ip_address'       => $this->request->getIPAddress(),
            ]);

        // ── CEK KARTU RFID ──────────────────────────────────────
        // Cari kartu berdasarkan UID — tidak perlu filter status dulu,
        // dicek lebih lanjut setelah kartu ditemukan
        $kartu = $this->db->table('kartu_rfid')
            ->where('uid_rfid', $uid)
            ->get()->getRow();

        // ── KARTU TIDAK TERDAFTAR (ASING) ──────────────────────
        if (!$kartu) {
            log_message('info', '[RFID SCAN] Kartu tidak terdaftar (Asing): uid=' . $uid);

            // Duplicate tap prevention untuk kartu asing (< 3 detik)
            $dupAsingCheck = $this->db->table('rfid_tap')
                ->where('uid_rfid', $uid)
                ->where('alat_id', $device->alat_id)
                ->where('jenis', 'Asing')
                ->where('waktu_tap >=', date('Y-m-d H:i:s', time() - 3))
                ->get()->getRow();

            if ($dupAsingCheck) {
                log_message('info', '[RFID SCAN] Duplicate tap asing diabaikan: uid=' . $uid);
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Kartu tidak terdaftar',
                    'uid'     => $uid,
                ]);
            }

            // Insert ke rfid_tap sebagai Asing
            $this->db->table('rfid_tap')->insert([
                'waktu_tap'  => $now,
                'uid_rfid'   => $uid,
                'alat_id'    => $device->alat_id,
                'ruangan_id' => $device->ruangan_id,
                'jenis'      => 'Asing',
                // user_id, jadwal_shift_id, status_validasi, urutan_aktual,
                // urutan_seharusnya, is_lcs_match → NULL (default DB)
                'is_offline_tap' => $isOfflineTap ? 1 : 0,
            ]);

            $tapId = (int)$this->db->insertID();
            log_message('info', '[RFID SCAN] Tap Asing tersimpan: tap_id=' . $tapId
                . ' uid=' . $uid . ' alat=' . $device->alat_id);

            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Kartu tidak terdaftar',
                'uid'     => $uid,
                'tap_id'  => $tapId,
            ]);
        }

        // ── CEK STATUS KARTU ────────────────────────────────────
        if (strtolower($kartu->status_kartu) !== 'aktif') {
            log_message('info', '[RFID SCAN] Kartu tidak aktif: uid=' . $uid
                . ' status=' . $kartu->status_kartu);
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Kartu tidak aktif (' . $kartu->status_kartu . ')',
                'uid'     => $uid,
            ]);
        }

        // ── CEK USER ────────────────────────────────────────────
        $user = $this->db->query(
            "SELECT * FROM users
             WHERE user_id = ?
               AND LOWER(status_aktif) = 'aktif'
             LIMIT 1",
            [$kartu->user_id]
        )->getRow();

        if (!$user) {
            $userRaw = $this->db->table('users')
                ->where('user_id', $kartu->user_id)
                ->get()->getRow();
            log_message('error', '[RFID SCAN] User id=' . $kartu->user_id
                . ($userRaw
                    ? ' ditemukan tapi status_aktif=[' . $userRaw->status_aktif . ']'
                    : ' TIDAK ADA di tabel users'));
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'User tidak ditemukan atau nonaktif',
                'uid'     => $uid,
            ]);
        }

        log_message('debug', '[RFID SCAN] User OK: id=' . $user->user_id
            . ' nama=' . $user->nama_lengkap);

        // ── DUPLICATE TAP PREVENTION untuk Terdaftar (< 3 detik) ─
        $dupCheck = $this->db->table('rfid_tap')
            ->where('uid_rfid', $uid)
            ->where('alat_id', $device->alat_id)
            ->where('jenis', 'Terdaftar')
            ->where('waktu_tap >=', date('Y-m-d H:i:s', time() - 3))
            ->get()->getRow();

        if ($dupCheck) {
            log_message('info', '[RFID SCAN] Duplicate tap diabaikan: user='
                . $user->user_id . ' alat=' . $device->alat_id);
            return $this->response->setJSON([
                'status'          => true,
                'message'         => 'Tap sudah tercatat',
                'status_validasi' => 'Duplikat',
                'nama_lengkap'    => $user->nama_lengkap,
                'nama'            => $user->nama_lengkap,
                'waktu'           => date('H:i:s'),
                'jam'             => date('H:i:s'),
                'uid'             => $uid,
                'user_id'         => (int) $user->user_id,
                'ruangan_id'      => (int) $device->ruangan_id,
                'tap_id'          => (int) $dupCheck->tap_id,
            ]);
        }

        // ── CARI JADWAL SHIFT HARI INI ──────────────────────────
        // Prioritas 1: Shift yang jam-nya mencakup waktu sekarang
        $tanggal        = date('Y-m-d');
        $jamSekarang    = date('H:i:s');
        $jadwalShiftId  = null;
        $statusValidasi = 'Tidak Terjadwal';

        $jadwalShift = $this->db->query("
            SELECT js.jadwal_shift_id,
                   js.shift_id,
                   js.petugas_1,
                   js.petugas_2,
                   js.status_shift,
                   s.jam_mulai,
                   s.jam_selesai,
                   s.nama_shift
            FROM jadwal_shift js
            JOIN shift s ON js.shift_id = s.shift_id
            WHERE js.tanggal = ?
              AND (
                (s.jam_mulai <= s.jam_selesai AND ? BETWEEN s.jam_mulai AND s.jam_selesai)
                OR
                (s.jam_mulai > s.jam_selesai AND (? >= s.jam_mulai OR ? <= s.jam_selesai))
              )
            ORDER BY
              CASE js.status_shift
                WHEN 'Berjalan'    THEN 0
                WHEN 'Belum Mulai' THEN 1
                ELSE 2
              END,
              s.jam_mulai ASC
            LIMIT 1
        ", [$tanggal, $jamSekarang, $jamSekarang, $jamSekarang])->getRow();

        // Fallback Prioritas 2: Ambil jadwal hari ini apapun statusnya
        if (!$jadwalShift) {
            $jadwalShift = $this->db->query("
                SELECT js.jadwal_shift_id,
                       js.shift_id,
                       js.petugas_1,
                       js.petugas_2,
                       js.status_shift,
                       s.jam_mulai,
                       s.jam_selesai,
                       s.nama_shift
                FROM jadwal_shift js
                JOIN shift s ON js.shift_id = s.shift_id
                WHERE js.tanggal = ?
                ORDER BY
                  CASE js.status_shift
                    WHEN 'Berjalan'    THEN 0
                    WHEN 'Belum Mulai' THEN 1
                    ELSE 2
                  END,
                  s.jam_mulai DESC
                LIMIT 1
            ", [$tanggal])->getRow();
        }

        if ($jadwalShift) {
            $jadwalShiftId = (int)$jadwalShift->jadwal_shift_id;

            log_message('debug', '[RFID SCAN] Jadwal ditemukan: id=' . $jadwalShiftId
                . ' status=' . $jadwalShift->status_shift
                . ' petugas_1=' . ($jadwalShift->petugas_1 ?? 'null')
                . ' petugas_2=' . ($jadwalShift->petugas_2 ?? 'null'));

            // Cek apakah user terdaftar di shift ini
            // Prioritas 1: via kolom petugas_1 / petugas_2
            $petugasLangsung = (
                (!empty($jadwalShift->petugas_1) && (int)$jadwalShift->petugas_1 === (int)$user->user_id) ||
                (!empty($jadwalShift->petugas_2) && (int)$jadwalShift->petugas_2 === (int)$user->user_id)
            );

            // Prioritas 2: via tabel pivot jadwal_shift_petugas (jika ada)
            $petugasPivot = false;
            try {
                $pivotRow = $this->db->table('jadwal_shift_petugas')
                    ->where('jadwal_shift_id', $jadwalShiftId)
                    ->where('user_id', $user->user_id)
                    ->get()->getRow();
                $petugasPivot = ($pivotRow !== null);
            } catch (\Throwable $e) {
                // Tabel jadwal_shift_petugas mungkin tidak ada — abaikan
                log_message('debug', '[RFID SCAN] jadwal_shift_petugas tidak ada atau error: '
                    . $e->getMessage());
            }

            $statusValidasi = ($petugasLangsung || $petugasPivot)
                ? 'Sesuai'
                : 'Tidak Sesuai';

            log_message('debug', '[RFID SCAN] status_validasi=' . $statusValidasi
                . ' via=' . ($petugasLangsung ? 'kolom_langsung' : ($petugasPivot ? 'pivot' : 'tidak_terdaftar')));
        } else {
            log_message('info', '[RFID SCAN] Tidak ada jadwal hari ini: tanggal=' . $tanggal);
        }

        // ── HITUNG URUTAN AKTUAL ────────────────────────────────
        // urutan_aktual = jumlah tap Terdaftar user ini di shift ini + 1
        $urutan = null;
        $urutanSeharusnya = null;

        if ($jadwalShiftId !== null) {
            $jumlahTapSebelumnya = $this->db->table('rfid_tap')
                ->where('user_id', $user->user_id)
                ->where('jadwal_shift_id', $jadwalShiftId)
                ->where('jenis', 'Terdaftar')
                ->countAllResults();
            $urutan = $jumlahTapSebelumnya + 1;

            // urutan_seharusnya: ambil urutan_patroli ruangan ini
            $ruanganInfo = $this->db->table('ruangan')
                ->where('ruangan_id', $device->ruangan_id)
                ->select('urutan_patroli')
                ->get()->getRow();
            $urutanSeharusnya = $ruanganInfo ? (int)$ruanganInfo->urutan_patroli : null;
        }

        // ── INSERT KE rfid_tap ──────────────────────────────────
        try {
            $insertData = [
                'waktu_tap'        => $now,
                'uid_rfid'         => $uid,
                'alat_id'          => $device->alat_id,
                'user_id'          => $user->user_id,
                'ruangan_id'       => $device->ruangan_id,
                'jadwal_shift_id'  => $jadwalShiftId,
                'jenis'            => 'Terdaftar',
                'status_validasi'  => $statusValidasi,
                'urutan_aktual'    => $urutan,
                'urutan_seharusnya'=> $urutanSeharusnya,
                'is_lcs_match'     => 0,
                'is_offline_tap'   => $isOfflineTap ? 1 : 0,
            ];

            log_message('debug', '[RFID SCAN] Insert rfid_tap: ' . json_encode($insertData));

            $inserted = $this->db->table('rfid_tap')->insert($insertData);

            if (!$inserted) {
                $dbError = $this->db->error();
                log_message('error', '[RFID SCAN] Insert rfid_tap GAGAL: '
                    . ($dbError['message'] ?? '-'));
                return $this->response->setStatusCode(500)->setJSON([
                    'status'  => false,
                    'message' => 'Gagal menyimpan data tap',
                    'debug'   => $dbError['message'] ?? '-',
                ]);
            }

            $tapId = (int)$this->db->insertID();

            if ($tapId === 0) {
                log_message('error', '[RFID SCAN] insertID()=0 | user=' . $user->user_id);
                return $this->response->setStatusCode(500)->setJSON([
                    'status'  => false,
                    'message' => 'Gagal mendapatkan ID tap',
                ]);
            }

            log_message('info', '[RFID SCAN] BERHASIL: tap_id=' . $tapId
                . ' user=' . $user->nama_lengkap
                . ' ruangan=' . $device->ruangan_id
                . ' status=' . $statusValidasi
                . ' shift=' . ($jadwalShiftId ?? 'NULL'));

        } catch (\Throwable $e) {
            log_message('error', '[RFID SCAN] Exception insert rfid_tap: ' . $e->getMessage()
                . ' | user=' . $user->user_id);
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => 'Database error saat menyimpan tap',
                'debug'   => $e->getMessage(),
            ]);
        }

        // ── TRIGGER EVALUASI LCS ────────────────────────────────
        // Hanya untuk tap 'Sesuai' (petugas terdaftar di shift ini)
        if ($jadwalShiftId && $statusValidasi === 'Sesuai') {
            $this->_evaluasiLCS($user->user_id, $jadwalShiftId, $tanggal);
        }

        // ── RESPONSE KE ESP32 ───────────────────────────────────
        $pesanMap = [
            'Sesuai'          => 'Tap berhasil',
            'Tidak Sesuai'    => 'Tap dicatat (bukan petugas shift ini)',
            'Tidak Terjadwal' => 'Tap dicatat (tidak ada jadwal hari ini)',
        ];

        return $this->response->setJSON([
            'status'          => true,
            'message'         => $pesanMap[$statusValidasi] ?? 'Tap berhasil',
            'status_validasi' => $statusValidasi,
            'nama_lengkap'    => $user->nama_lengkap,
            'nama'            => $user->nama_lengkap,
            'waktu'           => date('H:i:s'),
            'jam'             => date('H:i:s'),
            'uid'             => $uid,
            'user_id'         => (int) $user->user_id,
            'ruangan_id'      => (int) $device->ruangan_id,
            'tap_id'          => $tapId,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // ASSIGN RFID
    // POST /api/rfid/assign
    // Body: { "uid_rfid": "AABBCCDD", "user_id": 9 }
    // ─────────────────────────────────────────────────────────────
    public function assign()
    {
        $data = $this->request->getJSON(true);

        $uid     = strtoupper(trim($data['uid_rfid'] ?? ''));
        $user_id = (int)($data['user_id'] ?? 0);

        if (empty($uid) || $user_id === 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'UID atau user_id kosong',
            ]);
        }

        $user = $this->db->query(
            "SELECT * FROM users WHERE user_id = ? AND LOWER(status_aktif) = 'aktif' LIMIT 1",
            [$user_id]
        )->getRow();

        if (!$user) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'User tidak ditemukan atau nonaktif',
            ]);
        }

        // Cek apakah UID sudah ada di database
        $cekUid = $this->db->table('kartu_rfid')
            ->where('uid_rfid', $uid)
            ->get()->getRow();

        if ($cekUid) {
            if ((int)$cekUid->user_id === $user_id) {
                // UID milik user yang sama → aktifkan kembali
                $this->db->table('kartu_rfid')
                    ->where('kartu_id', $cekUid->kartu_id)
                    ->update(['status_kartu' => 'Aktif']);
                return $this->response->setJSON([
                    'status'  => true,
                    'message' => 'RFID sudah terdaftar — status diperbarui ke Aktif',
                    'uid'     => $uid,
                ]);
            }
            // UID sudah dipakai user lain
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'UID sudah digunakan oleh user lain',
            ]);
        }

        // Cek kartu aktif milik user ini
        $cekUser = $this->db->table('kartu_rfid')
            ->where('user_id', $user_id)
            ->where('status_kartu', 'Aktif')
            ->get()->getRow();

        if ($cekUser) {
            // Update UID pada kartu aktif yang sudah ada
            $this->db->table('kartu_rfid')
                ->where('kartu_id', $cekUser->kartu_id)
                ->update(['uid_rfid' => $uid, 'status_kartu' => 'Aktif']);
        } else {
            // Buat kartu baru
            $this->db->table('kartu_rfid')->insert([
                'uid_rfid'     => $uid,
                'user_id'      => $user_id,
                'status_kartu' => 'Aktif',
            ]);
        }

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'RFID berhasil disimpan',
            'uid'     => $uid,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // EVALUASI LCS (PRIVATE)
    // Dipanggil setiap tap 'Sesuai' berhasil tersimpan ke rfid_tap
    //
    // Membaca data tap dari rfid_tap (bukan kunjungan),
    // menyimpan hasil ke patroli_hasil, patroli_lcs_log,
    // patroli_titik_terlewat, dan update is_lcs_match di rfid_tap.
    // ─────────────────────────────────────────────────────────────
    private function _evaluasiLCS(int $userId, int $jadwalShiftId, string $tanggal): void
    {
        try {
            // Ambil urutan ruangan ideal (SOP)
            $ruanganList = $this->db->table('ruangan')
                ->where('aktif', 1)
                ->orderBy('urutan_patroli', 'ASC')
                ->select('kode_ruangan')
                ->get()->getResultArray();

            if (empty($ruanganList)) {
                log_message('warning', '[LCS] Tidak ada ruangan aktif — dilewati');
                return;
            }

            $idealKode = array_column($ruanganList, 'kode_ruangan');

            // Ambil semua tap 'Sesuai' user ini di shift ini dari rfid_tap
            $tapRows = $this->db->query("
                SELECT rt.tap_id,
                       rt.waktu_tap,
                       r.kode_ruangan
                FROM rfid_tap rt
                JOIN ruangan r ON rt.ruangan_id = r.ruangan_id
                WHERE rt.user_id         = ?
                  AND rt.jadwal_shift_id = ?
                  AND rt.jenis           = 'Terdaftar'
                  AND rt.status_validasi = 'Sesuai'
                ORDER BY rt.waktu_tap ASC
            ", [$userId, $jadwalShiftId])->getResultArray();

            if (empty($tapRows)) {
                log_message('info', '[LCS] Belum ada tap Sesuai: user='
                    . $userId . ' shift=' . $jadwalShiftId);
                return;
            }

            // Sesuaikan nama kolom agar kompatibel dengan LCSService
            // (LCSService mungkin mengharapkan 'waktu_kunjungan' — sesuaikan jika perlu)
            $tapRowsForLcs = array_map(function ($row) {
                return [
                    'tap_id'           => $row['tap_id'],
                    'waktu_kunjungan'  => $row['waktu_tap'],   // alias agar LCSService tidak perlu diubah
                    'kode_ruangan'     => $row['kode_ruangan'],
                ];
            }, $tapRows);

            // Evaluasi LCS — patroli_ke mulai dari 1 per petugas
            $lcs       = new LCSService();
            $hasilList = $lcs->evaluasiPetugas(
                $tapRowsForLcs,
                $idealKode,
                $jadwalShiftId,
                $userId,
                $tanggal,
                1,   // offset mulai dari 1
                1    // step 1 per sesi
            );

            foreach ($hasilList as $idx => $hasil) {
                $dbData = $hasil['db'];

                $this->db->transStart();

                // ── Upsert patroli_hasil ────────────────────────
                $existing = $this->db->table('patroli_hasil')
                    ->where('jadwal_shift_id', $dbData['jadwal_shift_id'])
                    ->where('petugas_id',      $dbData['petugas_id'])
                    ->where('tanggal',         $dbData['tanggal'])
                    ->where('patroli_ke',      $dbData['patroli_ke'])
                    ->get()->getRow();

                if ($existing) {
                    $this->db->table('patroli_hasil')
                        ->where('id', $existing->id)
                        ->update([
                            'coverage_persen' => $dbData['coverage_persen'],
                            'lcs_persen'      => $dbData['lcs_persen'],
                            'jumlah_titik'    => $dbData['jumlah_titik'],
                            'jumlah_scan'     => $dbData['jumlah_scan'],
                            'status_patroli'  => $dbData['status_patroli'],
                        ]);
                    $hasilId = (int)$existing->id;
                } else {
                    $this->db->table('patroli_hasil')->insert($dbData);
                    $hasilId = (int)$this->db->insertID();
                }

                if ($hasilId === 0) {
                    $this->db->transRollback();
                    log_message('error', '[LCS] hasilId=0, rollback | user=' . $userId);
                    continue;
                }

                // ── Hapus + insert patroli_lcs_log ──────────────
                $this->db->table('patroli_lcs_log')
                    ->where('patroli_hasil_id', $hasilId)->delete();
                $this->db->table('patroli_lcs_log')->insert([
                    'patroli_hasil_id' => $hasilId,
                    'urutan_ideal'     => implode(',', $hasil['lcs']['urutan_ideal']),
                    'urutan_aktual'    => implode(',', $hasil['lcs']['urutan_aktual']),
                    'nilai_lcs'        => $hasil['lcs']['persentase'],
                ]);

                // ── Hapus + insert patroli_titik_terlewat ────────
                $this->db->table('patroli_titik_terlewat')
                    ->where('patroli_hasil_id', $hasilId)->delete();
                foreach ($hasil['lcs']['titik_terlewat'] as $kodeRuangan) {
                    $ruangan = $this->db->table('ruangan')
                        ->where('kode_ruangan', $kodeRuangan)->get()->getRow();
                    if ($ruangan) {
                        $this->db->table('patroli_titik_terlewat')->insert([
                            'patroli_hasil_id' => $hasilId,
                            'ruangan_id'       => $ruangan->ruangan_id,
                            'keterangan'       => 'Belum dikunjungi',
                        ]);
                    }
                }

                // ── Update is_lcs_match di rfid_tap ─────────────
                $lcsSequence = $hasil['lcs']['lcs_sequence'];
                $seenKode    = [];
                foreach ($hasil['sesi'] as $sesiRow) {
                    // Cocokkan baris sesi ke tap_id di rfid_tap
                    $matchTapId = null;
                    foreach ($tapRows as $tr) {
                        if ($tr['waktu_tap']    === $sesiRow['waktu_kunjungan']
                            && $tr['kode_ruangan'] === $sesiRow['kode_ruangan']) {
                            $matchTapId = (int)$tr['tap_id'];
                            break;
                        }
                    }
                    if (!$matchTapId) continue;

                    $kode = $sesiRow['kode_ruangan'];
                    if (!isset($seenKode[$kode])) $seenKode[$kode] = 0;

                    $occurrenceInLcs = count(array_keys($lcsSequence, $kode));
                    $matchVal = ($seenKode[$kode] < $occurrenceInLcs) ? 1 : 0;
                    if ($matchVal) $seenKode[$kode]++;

                    $this->db->table('rfid_tap')
                        ->where('tap_id', $matchTapId)
                        ->update(['is_lcs_match' => $matchVal]);
                }

                $this->db->transComplete();

                if ($this->db->transStatus() === false) {
                    log_message('error', '[LCS] Transaksi gagal | user=' . $userId
                        . ' patroli_ke=' . $dbData['patroli_ke']);
                }
            }

            log_message('info', '[LCS] Evaluasi selesai: user=' . $userId
                . ' shift=' . $jadwalShiftId . ' sesi=' . count($hasilList));

        } catch (\Throwable $e) {
            log_message('error', '[LCS] Error: ' . $e->getMessage()
                . ' | user=' . $userId . ' shift=' . $jadwalShiftId);
        }
    }
}