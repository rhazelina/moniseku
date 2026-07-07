<?php

namespace App\Controllers;

class Users extends BaseController
{
    // =========================
    // HELPER: GET ROLES
    // =========================
    private function getRoles()
    {
        return \Config\Database::connect()
            ->table('roles')
            ->get()
            ->getResultArray();
    }

    // =========================
    // LIST USERS
    // =========================
    public function index()
    {
        $db = \Config\Database::connect();

        $users = $db->query("
            SELECT
                users.*,
                roles.nama_role,
                kartu_rfid.uid_rfid,
                kartu_rfid.status_kartu
            FROM users
            JOIN roles ON users.role_id = roles.role_id
            LEFT JOIN kartu_rfid ON users.user_id = kartu_rfid.user_id
            ORDER BY users.user_id DESC
        ")->getResultArray();

        return view('admin/users', [
            'users' => $users,
            'roles' => $this->getRoles(),
        ]);
    }

    // =========================
    // STORE USER
    // =========================
    public function store()
    {
        $db = \Config\Database::connect();

        // Handle foto upload
        $file     = $this->request->getFile('foto_profile');
        $fotoName = null;

        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $fotoName = $file->getRandomName();
            $file->move('uploads/profile', $fotoName);
        }

        // Insert ke tabel users (tanpa uid_rfid)
        $db->table('users')->insert([
            'username'     => $this->request->getPost('username'),
            'password'     => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'role_id'      => $this->request->getPost('role_id'),
            'status_aktif' => $this->request->getPost('status_aktif') ?? 'Aktif',
            'foto_profile' => $fotoName,
        ]);

        $newUserId = $db->insertID();

        // Simpan uid_rfid ke kartu_rfid jika diisi
        $uidRfid = $this->request->getPost('uid_rfid');
        if (! empty($uidRfid)) {
            // Cek apakah uid_rfid sudah terdaftar di kartu lain
            $existing = $db->table('kartu_rfid')
                ->where('uid_rfid', $uidRfid)
                ->get()
                ->getRowArray();

            if ($existing) {
                // Pindahkan kepemilikan kartu ke user baru
                $db->table('kartu_rfid')
                    ->where('uid_rfid', $uidRfid)
                    ->update([
                        'user_id'     => $newUserId,
                        'status_kartu' => 'Aktif',
                    ]);
            } else {
                // Buat entri kartu baru
                $db->table('kartu_rfid')->insert([
                    'uid_rfid'    => $uidRfid,
                    'user_id'     => $newUserId,
                    'status_kartu' => 'Aktif',
                ]);
            }
        }

        return redirect()->to('/users')->with('success', 'User berhasil ditambahkan');
    }

    // =========================
    // UPDATE USER
    // =========================
    public function update($id)
    {
        $db = \Config\Database::connect();

        $user = $db->table('users')
            ->where('user_id', $id)
            ->get()
            ->getRowArray();

        if (! $user) {
            return redirect()->to('/users')->with('error', 'User tidak ditemukan');
        }

        // Handle foto upload
        $file     = $this->request->getFile('foto_profile');
        $fotoName = $user['foto_profile'];

        if ($file && $file->isValid() && ! $file->hasMoved()) {
            // Hapus foto lama
            if (! empty($fotoName) && file_exists(FCPATH . 'uploads/profile/' . $fotoName)) {
                unlink(FCPATH . 'uploads/profile/' . $fotoName);
            }
            $fotoName = $file->getRandomName();
            $file->move('uploads/profile', $fotoName);
        }

        // Data update untuk tabel users (tanpa uid_rfid!)
        $data = [
            'username'     => $this->request->getPost('username'),
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'role_id'      => $this->request->getPost('role_id'),
            'status_aktif' => $this->request->getPost('status_aktif'),
            'foto_profile' => $fotoName,
        ];

        // Password opsional
        if ($this->request->getPost('password')) {
            $data['password'] = password_hash(
                $this->request->getPost('password'),
                PASSWORD_DEFAULT
            );
        }

        // Update tabel users
        $db->table('users')
            ->where('user_id', $id)
            ->update($data);

        // ---- Handle uid_rfid di tabel kartu_rfid ----
        $uidRfid = $this->request->getPost('uid_rfid');

        // Ambil kartu yang saat ini dimiliki user ini
        $kartuLama = $db->table('kartu_rfid')
            ->where('user_id', $id)
            ->get()
            ->getRowArray();

        if (! empty($uidRfid)) {
            // Cek apakah uid_rfid sudah dimiliki user lain
            $kartuExisting = $db->table('kartu_rfid')
                ->where('uid_rfid', $uidRfid)
                ->where('user_id !=', $id)
                ->get()
                ->getRowArray();

            if ($kartuExisting) {
                // uid_rfid dipakai user lain — tolak, beri warning
                return redirect()->to('/users')->with('error', 'UID RFID sudah digunakan oleh user lain');
            }

            if ($kartuLama) {
                if ($kartuLama['uid_rfid'] !== $uidRfid) {
                    // Update uid_rfid kartu lama
                    $db->table('kartu_rfid')
                        ->where('user_id', $id)
                        ->update(['uid_rfid' => $uidRfid, 'status_kartu' => 'Aktif']);
                }
                // Kalau sama, tidak perlu update
            } else {
                // Belum punya kartu, buat baru
                $db->table('kartu_rfid')->insert([
                    'uid_rfid'    => $uidRfid,
                    'user_id'     => $id,
                    'status_kartu' => 'Aktif',
                ]);
            }
        } else {
            // uid_rfid dikosongkan → hapus/lepas kartu dari user ini
            if ($kartuLama) {
                $db->table('kartu_rfid')
                    ->where('user_id', $id)
                    ->update(['user_id' => null, 'status_kartu' => 'Nonaktif']);
            }
        }

        return redirect()->to('/users')->with('success', 'User berhasil diupdate');
    }

    // =========================
    // DELETE USER
    // =========================
    public function delete($id)
    {
        $db = \Config\Database::connect();

        $user = $db->table('users')
            ->where('user_id', $id)
            ->get()
            ->getRowArray();

        // Hapus foto
        if (! empty($user['foto_profile'])) {
            $path = FCPATH . 'uploads/profile/' . $user['foto_profile'];
            if (file_exists($path)) {
                unlink($path);
            }
        }

        // Lepaskan kartu RFID dari user ini (jangan hapus kartu, hanya unlink)
        $db->table('kartu_rfid')
            ->where('user_id', $id)
            ->update(['user_id' => null, 'status_kartu' => 'Nonaktif']);

        // Hapus user
        $db->table('users')->where('user_id', $id)->delete();

        return redirect()->to('/users')->with('success', 'User berhasil dihapus');
    }
}