<?php
// ================================================================
// LOKASI: app/Controllers/Api/DeviceApi.php
// VERSI  : v3.3 (tidak ada perubahan dari v3.3 asli)
//
// File ini tidak menyentuh tabel kunjungan maupun rfid_tap
// sehingga tidak memerlukan perubahan untuk migrasi ke rfid_tap.
//
// CATATAN v3.3 (tetap berlaku):
// - checkOffline(): fix where('last_online IS NOT NULL') → (null, false)
// - checkOffline(): ambil daftar perangkat SEBELUM update agar akurat
// ================================================================

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class DeviceApi extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ─────────────────────────────────────────────────────────────
    // LOGIN DEVICE
    // POST /api/device/login
    // Body: { "username": "mntt06", "password": "Gkibrm01", "kode_perangkat": "DEVICE_06" }
    // ─────────────────────────────────────────────────────────────
    public function login()
    {
        $data = $this->request->getJSON(true);

        $username       = trim($data['username']       ?? '');
        $password       = trim($data['password']       ?? '');
        $kode_perangkat = trim($data['kode_perangkat'] ?? '');

        if (empty($username) || empty($password)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Username dan password wajib diisi',
            ]);
        }

        try {
            $builder = $this->db->table('perangkat_rfid')
                ->where('username_login', $username);

            if (!empty($kode_perangkat)) {
                $builder->where('kode_perangkat', $kode_perangkat);
            }

            $device = $builder->get()->getRow();
        } catch (\Throwable $e) {
            log_message('error', '[DEVICE LOGIN] DB error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => 'Database error',
            ]);
        }

        if (!$device) {
            return $this->response->setStatusCode(401)->setJSON([
                'status'  => false,
                'message' => 'Login gagal — username atau kode perangkat salah',
            ]);
        }

        // Cek password (bcrypt atau plaintext)
        $storedPass = $device->password_login ?? '';
        $passwordOk = false;

        if (!empty($storedPass) && str_starts_with($storedPass, '$2y$')) {
            $passwordOk = password_verify($password, $storedPass);
        } else {
            $passwordOk = ($password === $storedPass);
        }

        if (!$passwordOk) {
            return $this->response->setStatusCode(401)->setJSON([
                'status'  => false,
                'message' => 'Login gagal — password salah',
            ]);
        }

        // Tandai Online
        $now = date('Y-m-d H:i:s');
        try {
            $this->db->table('perangkat_rfid')
                ->where('alat_id', $device->alat_id)
                ->update([
                    'status_perangkat' => 'Online',
                    'last_online'      => $now,
                    'ip_address'       => $this->request->getIPAddress(),
                ]);
        } catch (\Throwable $e) {
            log_message('error', '[DEVICE LOGIN] Update status gagal: ' . $e->getMessage());
        }

        log_message('info', '[DEVICE LOGIN] Berhasil: alat_id=' . $device->alat_id
            . ' kode=' . $device->kode_perangkat);

        return $this->response->setJSON([
            'status'         => true,
            'alat_id'        => (int) $device->alat_id,
            'ruangan_id'     => (int) $device->ruangan_id,
            'kode_perangkat' => $device->kode_perangkat,
            'message'        => 'Login berhasil',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // HEARTBEAT — ESP32 kirim setiap 60 detik
    // POST /api/device/heartbeat
    // Body: { "kode_perangkat": "DEVICE_06", "username": "mntt06", "alat_id": 6 }
    // ─────────────────────────────────────────────────────────────
    public function heartbeat()
    {
        $data = $this->request->getJSON(true);

        $kode_perangkat = trim($data['kode_perangkat'] ?? '');
        $alat_id_input  = $data['alat_id'] ?? null;

        try {
            if (!empty($alat_id_input) && (int)$alat_id_input > 0) {
                $device = $this->db->table('perangkat_rfid')
                    ->where('alat_id', (int)$alat_id_input)
                    ->get()->getRow();
            } elseif (!empty($kode_perangkat)) {
                $device = $this->db->table('perangkat_rfid')
                    ->where('kode_perangkat', $kode_perangkat)
                    ->get()->getRow();
            } else {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => false,
                    'message' => 'kode_perangkat atau alat_id wajib diisi',
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', '[DEVICE HEARTBEAT] DB error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => 'Database error',
            ]);
        }

        if (!$device) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => false,
                'message' => 'Perangkat tidak ditemukan',
            ]);
        }

        $now = date('Y-m-d H:i:s');
        try {
            $this->db->table('perangkat_rfid')
                ->where('alat_id', $device->alat_id)
                ->update([
                    'status_perangkat' => 'Online',
                    'last_online'      => $now,
                    'ip_address'       => $this->request->getIPAddress(),
                ]);
        } catch (\Throwable $e) {
            log_message('error', '[DEVICE HEARTBEAT] Update gagal: ' . $e->getMessage());
        }

        return $this->response->setJSON([
            'status'  => true,
            'alat_id' => (int) $device->alat_id,
            'time'    => $now,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // PING
    // GET /api/device/ping
    // ─────────────────────────────────────────────────────────────
    public function ping()
    {
        return $this->response->setJSON([
            'status' => true,
            'time'   => date('Y-m-d H:i:s'),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // STATUS — Ambil status 1 device
    // GET /api/device/status/{kode_atau_id}
    // ─────────────────────────────────────────────────────────────
    public function status($identifier = null)
    {
        if (!$identifier) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Identifier perangkat diperlukan',
            ]);
        }

        try {
            $device = $this->db->table('perangkat_rfid')
                ->where('kode_perangkat', $identifier)
                ->get()->getRow();

            if (!$device && is_numeric($identifier)) {
                $device = $this->db->table('perangkat_rfid')
                    ->where('alat_id', (int) $identifier)
                    ->get()->getRow();
            }
        } catch (\Throwable $e) {
            log_message('error', '[DEVICE STATUS] DB error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => 'Database error',
            ]);
        }

        if (!$device) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => false,
                'message' => 'Perangkat tidak ditemukan',
            ]);
        }

        return $this->response->setJSON([
            'status'         => true,
            'alat_id'        => (int) $device->alat_id,
            'kode_perangkat' => $device->kode_perangkat,
            'ruangan_id'     => (int) $device->ruangan_id,
            'device_status'  => $device->status_perangkat,
            'last_online'    => $device->last_online,
            'ip_address'     => $device->ip_address,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // CHECK OFFLINE — Tandai device Offline jika tidak heartbeat > 3 menit
    // GET /api/device/check-offline
    // ─────────────────────────────────────────────────────────────
    public function checkOffline()
    {
        $threshold = 180; // 3 menit dalam detik
        $cutoff    = date('Y-m-d H:i:s', time() - $threshold);

        // ── AMBIL DAFTAR SEBELUM DI-UPDATE ──────────────────────
        try {
            $akanOffline = $this->db->table('perangkat_rfid')
                ->select('alat_id, kode_perangkat, last_online, ip_address')
                ->where('status_perangkat', 'Online')
                ->where('last_online <', $cutoff)
                ->where('last_online IS NOT NULL', null, false) // CI4: hindari escape NULL check
                ->get()->getResult();
        } catch (\Throwable $e) {
            log_message('error', '[CHECK-OFFLINE] Gagal ambil daftar: ' . $e->getMessage());
            $akanOffline = [];
        }

        // ── UPDATE STATUS KE OFFLINE ─────────────────────────────
        try {
            $this->db->table('perangkat_rfid')
                ->where('status_perangkat', 'Online')
                ->where('last_online <', $cutoff)
                ->where('last_online IS NOT NULL', null, false)
                ->update(['status_perangkat' => 'Offline']);

            $affected = $this->db->affectedRows();
        } catch (\Throwable $e) {
            log_message('error', '[CHECK-OFFLINE] Update gagal: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => 'Gagal update status offline',
            ]);
        }

        if ($affected > 0) {
            log_message('info', '[CHECK-OFFLINE] ' . $affected
                . ' perangkat ditandai Offline | cutoff=' . $cutoff);
        }

        // ── AMBIL SEMUA STATUS UNTUK RESPONSE ───────────────────
        try {
            $devices = $this->db->table('perangkat_rfid')
                ->select('alat_id, kode_perangkat, status_perangkat, last_online, ip_address')
                ->orderBy('kode_perangkat', 'ASC')
                ->get()->getResult();
        } catch (\Throwable $e) {
            log_message('error', '[CHECK-OFFLINE] Gagal ambil semua device: ' . $e->getMessage());
            $devices = [];
        }

        return $this->response->setJSON([
            'status'         => true,
            'marked_offline' => (int) $affected,
            'threshold_sec'  => $threshold,
            'cutoff_time'    => $cutoff,
            'checked_at'     => date('Y-m-d H:i:s'),
            'newly_offline'  => $akanOffline,
            'devices'        => $devices,
        ]);
    }
}