<?php
// ================================================================
// LOKASI : app/Controllers/Api/UserApi.php
// VERSI  : v1.3
//
// PERUBAHAN dari v1.2:
// - detail(): hapus kolom regu_id dari SELECT karena tidak ada
//   di skema DB tabel users
// - Semua referensi kolom sudah sesuai skema DB aktual:
//   kartu_id (PK kartu_rfid), bukan kartu_rfid_id
// - Tidak ada perubahan logika atau struktur endpoint
// ================================================================

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class UserApi extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // -----------------------------------------------------------------
    // GET /api/users/petugas
    // Mengembalikan daftar semua petugas aktif beserta info kartu RFID
    // -----------------------------------------------------------------
    public function petugas()
    {
        $data = $this->db->query("
            SELECT
                u.user_id,
                u.username,
                u.nama_lengkap,
                (
                    SELECT kr.uid_rfid
                      FROM kartu_rfid kr
                     WHERE kr.user_id      = u.user_id
                       AND kr.status_kartu = 'Aktif'
                     ORDER BY kr.kartu_id DESC
                     LIMIT 1
                )                                     AS uid_kartu,
                CASE
                    WHEN EXISTS (
                        SELECT 1 FROM kartu_rfid kr2
                         WHERE kr2.user_id      = u.user_id
                           AND kr2.status_kartu = 'Aktif'
                    ) THEN 1 ELSE 0
                END                                   AS punya_kartu
            FROM users u
            WHERE u.role_id      = 2
              AND u.status_aktif = 'Aktif'
            ORDER BY u.nama_lengkap ASC
        ")->getResultArray();

        foreach ($data as &$row) {
            $row['punya_kartu'] = (bool)(int)$row['punya_kartu'];
        }

        return $this->response->setJSON($data);
    }

    // -----------------------------------------------------------------
    // GET /api/users/{id}
    // Detail satu user beserta daftar kartu aktifnya
    // -----------------------------------------------------------------
    public function detail($id = null)
    {
        if (! $id) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'user_id diperlukan',
            ]);
        }

        // SELECT hanya kolom yang ada di tabel users sesuai skema DB
        $user = $this->db->table('users')
            ->where('user_id', (int)$id)
            ->select('user_id, username, nama_lengkap, role_id, status_aktif')
            ->get()->getRow();

        if (! $user) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => false,
                'message' => 'User tidak ditemukan',
            ]);
        }

        $kartu = $this->db->table('kartu_rfid')
            ->where('user_id', (int)$id)
            ->where('status_kartu', 'Aktif')
            ->select('kartu_id, uid_rfid, status_kartu')
            ->orderBy('kartu_id', 'DESC')
            ->get()->getResultArray();

        return $this->response->setJSON([
            'status' => true,
            'data'   => array_merge((array)$user, ['kartu_aktif' => $kartu]),
        ]);
    }

    // -----------------------------------------------------------------
    // GET /api/users/{id}/kartu
    // Semua kartu RFID milik user (semua status, terurut terbaru)
    // -----------------------------------------------------------------
    public function kartu($id = null)
    {
        if (! $id) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'user_id diperlukan',
            ]);
        }

        $kartu = $this->db->table('kartu_rfid')
            ->where('user_id', (int)$id)
            ->select('kartu_id, uid_rfid, status_kartu')
            ->orderBy('kartu_id', 'DESC')
            ->get()->getResultArray();

        return $this->response->setJSON([
            'status' => true,
            'total'  => count($kartu),
            'data'   => $kartu,
        ]);
    }
}