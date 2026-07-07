<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class RfidController extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function scan()
    {
        // ✅ JSON INPUT dari ESP32
        $data = $this->request->getJSON(true);

        $uid = $data['uid_rfid'] ?? null;
        $alat_id = $data['alat_id'] ?? null;

        if (!$uid || !$alat_id) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Data tidak lengkap'
            ]);
        }

        // 🔵 1. cek device
        $device = $this->db->table('perangkat_rfid')
            ->where('alat_id', $alat_id)
            ->get()->getRow();

        if (!$device) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Device tidak valid'
            ]);
        }

        // 🟢 2. cek kartu RFID
        $kartu = $this->db->table('kartu_rfid')
            ->where('uid_rfid', $uid)
            ->get()->getRow();

        if (!$kartu) {
            $this->db->table('kartu_tidak_terdaftar')->insert([
                'uid_rfid' => $uid,
                'alat_id' => $alat_id,
                'waktu_tapping' => date('Y-m-d H:i:s')
            ]);

            return $this->response->setJSON([
                'status' => false,
                'message' => 'Kartu tidak terdaftar'
            ]);
        }

        // 🟡 3. ambil user
        $user = $this->db->table('users')
            ->where('user_id', $kartu->user_id)
            ->get()->getRow();

        // 🔴 4. simpan kunjungan
        $this->db->table('kunjungan')->insert([
            'user_id' => $user->user_id,
            'alat_id' => $alat_id,
            'ruangan_id' => $device->ruangan_id,
            'waktu_kunjungan' => date('Y-m-d H:i:s'),
            'status_validasi' => 'Sesuai'
        ]);

        // 🟣 5. response ke ESP32
        return $this->response->setJSON([
            'status' => true,
            'message' => 'OK',
            'nama' => $user->nama_lengkap,
            'username' => $user->username,
            'ruangan_id' => $device->ruangan_id,
            'device' => $device->kode_perangkat
        ]);
    }
}