<?php
// ================================================================
// LOKASI : app/Controllers/Login.php
// VERSI  : v2.0 — Redirect berdasarkan role (admin / petugas)
// ================================================================

namespace App\Controllers;

use CodeIgniter\Controller;

class Login extends Controller
{
    public function index()
    {
        // Jika sudah login, redirect ke dashboard sesuai role
        if (session()->get('logged_in')) {
            return $this->_redirectByRole(session()->get('role'));
        }
        return view('login');
    }

    public function auth()
    {
        $db = \Config\Database::connect();

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // ── Validasi input ────────────────────────────────────
        if (empty($username) || empty($password)) {
            return redirect()->to('/login')
                ->with('error', 'Username dan password tidak boleh kosong.');
        }

        // ── Ambil user + role ─────────────────────────────────
        $user = $db->table('users')
            ->select('users.*, roles.nama_role')
            ->join('roles', 'roles.role_id = users.role_id')
            ->where('users.username', $username)
            ->get()
            ->getRowArray();

        // ── User tidak ditemukan ──────────────────────────────
        if (!$user) {
            return redirect()->to('/login')
                ->with('error', 'Username tidak ditemukan.');
        }

        // ── Akun tidak aktif ──────────────────────────────────
        if (isset($user['status_aktif']) && $user['status_aktif'] !== 'Aktif') {
            return redirect()->to('/login')
                ->with('error', 'Akun Anda tidak aktif. Hubungi administrator.');
        }

        // ── Password salah ────────────────────────────────────
        if (!password_verify($password, $user['password'])) {
            return redirect()->to('/login')
                ->with('error', 'Password yang Anda masukkan salah.');
        }

        // ── Set session lengkap ───────────────────────────────
        session()->set([
            'user_id'       => $user['user_id'],
            'username'      => $user['username'],
            'nama_lengkap'  => $user['nama_lengkap'],
            'role'          => $user['nama_role'],
            'role_id'       => $user['role_id'],
            'foto_profile'  => $user['foto_profile'] ?? null,
            'uid_rfid'      => $user['uid_rfid']     ?? null,
            'jabatan'       => $user['jabatan']      ?? null,
            'status_aktif'  => $user['status_aktif'] ?? 'Aktif',
            'logged_in'     => true,
            'last_activity' => time(),
        ]);

        // ── Update last login ─────────────────────────────────
        $db->table('users')
            ->where('user_id', $user['user_id'])
            ->update(['last_login' => date('Y-m-d H:i:s')]);

        // ── Redirect berdasarkan role ─────────────────────────
        return $this->_redirectByRole($user['nama_role']);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }

    // ─────────────────────────────────────────────────────────
    // Helper: redirect ke dashboard sesuai role
    // ─────────────────────────────────────────────────────────
    private function _redirectByRole(string $role)
    {
        $roleNorm = strtolower(trim($role));

        if ($roleNorm === 'administrator' || $roleNorm === 'admin') {
            return redirect()->to('/admin/dashboard');
        }

        if ($roleNorm === 'petugas') {
            return redirect()->to('/petugas/dashboard');
        }

        // Role tidak dikenal — fallback ke dashboard admin
        return redirect()->to('/admin/dashboard');
    }
}