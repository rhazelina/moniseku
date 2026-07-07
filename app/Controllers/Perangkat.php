<?php

namespace App\Controllers;

class Perangkat extends BaseController
{
    private function getDb()
    {
        return \Config\Database::connect();
    }

    private function getRuangan()
    {
        return $this->getDb()->table('ruangan')->get()->getResultArray();
    }

    // =========================
    // LIST PERANGKAT
    // =========================
    public function index()
    {
        $db = $this->getDb();

        $perangkat = $db->query("
            SELECT
                p.*,
                r.nama_ruangan,
                r.lokasi
            FROM perangkat_rfid p
            LEFT JOIN ruangan r ON p.ruangan_id = r.ruangan_id
            ORDER BY p.alat_id ASC
        ")->getResultArray();

        $data = [
            'title'     => 'Manajemen Perangkat',
            'perangkat' => $perangkat,
            'ruangan'   => $this->getRuangan(),
        ];

        return view('admin/perangkat', $data);
    }

    // =========================
    // STORE PERANGKAT
    // =========================
    public function store()
    {
        $db   = $this->getDb();
        $kode = $this->request->getPost('kode_perangkat');

        // Cek duplikat kode
        $cek = $db->table('perangkat_rfid')->where('kode_perangkat', $kode)->get()->getRowArray();
        if ($cek) {
            return redirect()->to('/perangkat')->with('error', 'Kode perangkat sudah digunakan');
        }

        $password = $this->request->getPost('password_login');

        $db->table('perangkat_rfid')->insert([
            'ruangan_id'       => $this->request->getPost('ruangan_id'),
            'kode_perangkat'   => $kode,
            'username_login'   => $this->request->getPost('username_login'),
            'password_login'   => ! empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null,
            'ip_address'       => $this->request->getPost('ip_address') ?: null,
            'status_perangkat' => $this->request->getPost('status_perangkat') ?? 'Offline',
            'tipe_perangkat'   => $this->request->getPost('tipe_perangkat')   ?? 'ESP32_30PIN',
            'fitur_lcd'        => $this->request->getPost('fitur_lcd')    ? 1 : 0,
            'fitur_buzzer'     => $this->request->getPost('fitur_buzzer') ? 1 : 0,
            'fitur_rfid'       => $this->request->getPost('fitur_rfid')   ? 1 : 0,
        ]);

        return redirect()->to('/perangkat')->with('success', 'Perangkat berhasil ditambahkan');
    }

    // =========================
    // UPDATE PERANGKAT
    // =========================
    public function update($id)
    {
        $db = $this->getDb();

        $perangkat = $db->table('perangkat_rfid')->where('alat_id', $id)->get()->getRowArray();
        if (! $perangkat) {
            return redirect()->to('/perangkat')->with('error', 'Perangkat tidak ditemukan');
        }

        $kode = $this->request->getPost('kode_perangkat');

        // Cek duplikat kode (selain diri sendiri)
        $cek = $db->table('perangkat_rfid')
            ->where('kode_perangkat', $kode)
            ->where('alat_id !=', $id)
            ->get()->getRowArray();
        if ($cek) {
            return redirect()->to('/perangkat')->with('error', 'Kode perangkat sudah digunakan perangkat lain');
        }

        $data = [
            'ruangan_id'       => $this->request->getPost('ruangan_id'),
            'kode_perangkat'   => $kode,
            'username_login'   => $this->request->getPost('username_login'),
            'ip_address'       => $this->request->getPost('ip_address') ?: null,
            'status_perangkat' => $this->request->getPost('status_perangkat'),
            'tipe_perangkat'   => $this->request->getPost('tipe_perangkat'),
            'fitur_lcd'        => $this->request->getPost('fitur_lcd')    ? 1 : 0,
            'fitur_buzzer'     => $this->request->getPost('fitur_buzzer') ? 1 : 0,
            'fitur_rfid'       => $this->request->getPost('fitur_rfid')   ? 1 : 0,
        ];

        // Password opsional
        $password = $this->request->getPost('password_login');
        if (! empty($password)) {
            $data['password_login'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $db->table('perangkat_rfid')->where('alat_id', $id)->update($data);

        return redirect()->to('/perangkat')->with('success', 'Perangkat berhasil diupdate');
    }

    // =========================
    // DELETE PERANGKAT
    // =========================
    public function delete($id)
    {
        $db = $this->getDb();
        $db->table('perangkat_rfid')->where('alat_id', $id)->delete();
        return redirect()->to('/perangkat')->with('success', 'Perangkat berhasil dihapus');
    }

    // =========================
    // TAMBAH RUANGAN BARU (AJAX, dipanggil dari popup Tambah/Edit Perangkat)
    // =========================
    public function storeRuangan()
    {
        $db = $this->getDb();

        $nama   = trim((string) $this->request->getPost('nama_ruangan'));
        $lokasi = trim((string) $this->request->getPost('lokasi'));

        if ($nama === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'Nama ruangan wajib diisi']);
        }

        // Cek nama ruangan sudah ada atau belum (hindari duplikat)
        $cekNama = $db->table('ruangan')->where('nama_ruangan', $nama)->get()->getRowArray();
        if ($cekNama) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nama ruangan sudah ada']);
        }

        // urutan_patroli & kode_ruangan dibuat otomatis melanjutkan urutan terakhir
        $last   = $db->table('ruangan')->selectMax('urutan_patroli', 'max_urutan')->get()->getRowArray();
        $urutan = (int) ($last['max_urutan'] ?? 0) + 1;
        $kode   = 'T' . str_pad((string) $urutan, 2, '0', STR_PAD_LEFT);

        // Jaga-jaga jika kode_ruangan ternyata sudah dipakai (gap data / input manual sebelumnya)
        $cekKode = $db->table('ruangan')->where('kode_ruangan', $kode)->get()->getRowArray();
        if ($cekKode) {
            $kode = $kode . '-' . time();
        }

        $db->table('ruangan')->insert([
            'kode_ruangan'   => $kode,
            'nama_ruangan'   => $nama,
            'lokasi'         => $lokasi !== '' ? $lokasi : null,
            'urutan_patroli' => $urutan,
            'aktif'          => 1,
        ]);

        $id = $db->insertID();

        return $this->response->setJSON([
            'success'      => true,
            'ruangan_id'   => $id,
            'nama_ruangan' => $nama,
            'kode_ruangan' => $kode,
        ]);
    }

    // =========================
    // TOGGLE STATUS (AJAX)
    // =========================
    public function toggleStatus($id)
    {
        $db        = $this->getDb();
        $perangkat = $db->table('perangkat_rfid')->where('alat_id', $id)->get()->getRowArray();

        if (! $perangkat) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ditemukan']);
        }

        $newStatus = $perangkat['status_perangkat'] === 'Online' ? 'Offline' : 'Online';

        $db->table('perangkat_rfid')->where('alat_id', $id)->update([
            'status_perangkat' => $newStatus,
            'last_online'      => $newStatus === 'Online' ? date('Y-m-d H:i:s') : $perangkat['last_online'],
        ]);

        return $this->response->setJSON(['success' => true, 'status' => $newStatus]);
    }
}