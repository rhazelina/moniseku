<?php

namespace App\Controllers;

use App\Models\DeviceModel;
use App\Models\KunjunganModel;
use CodeIgniter\RESTful\ResourceController;

class DeviceApi extends ResourceController
{
    // =========================
    // 1. LOGIN DEVICE ESP32
    // =========================
    public function login()
    {
        $deviceModel = new DeviceModel();

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $device = $deviceModel
            ->where('username_login', $username)
            ->first();

        if (!$device) {
            return $this->respond([
                'status' => false,
                'message' => 'Device tidak ditemukan'
            ]);
        }

        if ($device['password_login'] !== $password) {
            return $this->respond([
                'status' => false,
                'message' => 'Password salah'
            ]);
        }

        // update status online
        $deviceModel->update($device['alat_id'], [
            'status_perangkat' => 'Online',
            'last_online' => date('Y-m-d H:i:s')
        ]);

        return $this->respond([
            'status' => true,
            'message' => 'LOGIN OK',
            'alat_id' => $device['alat_id'],
            'ruangan_id' => $device['ruangan_id'],
            'kode_perangkat' => $device['kode_perangkat']
        ]);
    }

    // =========================
    // 2. RFID TAP MASUK
    // =========================
    public function rfidTap()
    {
        $kunjunganModel = new KunjunganModel();
        $deviceModel = new DeviceModel();

        $uid = $this->request->getPost('uid');
        $alat_id = $this->request->getPost('alat_id');

        // ambil device
        $device = $deviceModel->find($alat_id);

        if (!$device) {
            return $this->respond([
                'status' => false,
                'message' => 'Device tidak valid'
            ]);
        }

        // cari user dari kartu RFID
        $db = \Config\Database::connect();

        $user = $db->table('kartu_rfid')
            ->where('uid_rfid', $uid)
            ->get()
            ->getRowArray();

        if (!$user) {
            return $this->respond([
                'status' => false,
                'message' => 'RFID tidak terdaftar'
            ]);
        }

        // ambil user_id dari kartu
        $user_id = $user['user_id'];

        // insert kunjungan
        $kunjunganModel->insert([
            'user_id' => $user_id,
            'ruangan_id' => $device['ruangan_id'],
            'alat_id' => $alat_id,
            'waktu_kunjungan' => date('Y-m-d H:i:s'),
            'status_validasi' => 'Tidak Terjadwal',
            'is_lcs_match' => 0
        ]);

        return $this->respond([
            'status' => true,
            'message' => 'TAP SUCCESS',
            'user_id' => $user_id,
            'ruangan_id' => $device['ruangan_id']
        ]);
    }
}