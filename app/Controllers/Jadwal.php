<?php

namespace App\Controllers;

use App\Models\JadwalModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class Jadwal extends Controller
{
    public function index()
    {
        $db = \Config\Database::connect();

        $users = $db->table('users')
            ->select('user_id, nama_lengkap')
            ->where('role_id', 2)
            ->get()
            ->getResultArray();

        return view('admin/jadwal', [
            'users' => $users
        ]);
    }

    public function getByDate()
    {
        $date = $this->request->getGet('date');

        $db = \Config\Database::connect();

        $data = $db->table('jadwal_patroli j')
            ->select('j.*, u.nama_lengkap')
            ->join('users u', 'u.user_id = j.user_id')
            ->where('j.tanggal', $date)
            ->orderBy('j.waktu_mulai', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($data as &$d) {

            if ($d['waktu_mulai'] < '14:00:00') {
                $d['shift'] = 'pagi';
            } elseif ($d['waktu_mulai'] < '22:00:00') {
                $d['shift'] = 'siang';
            } else {
                $d['shift'] = 'malam';
            }
        }

        return $this->response->setJSON($data);
    }

    public function download()
    {
        $user = $this->request->getGet('user');
        $month = $this->request->getGet('month');

        $db = \Config\Database::connect();

        $data = $db->table('jadwal_patroli j')
            ->select('j.*, u.nama_lengkap')
            ->join('users u', 'u.user_id = j.user_id')
            ->where('j.user_id', $user)
            ->like('j.tanggal', $month)
            ->get()
            ->getResultArray();

        return $this->response->setJSON($data);
    }

    /* =========================
    AUTO GENERATE FULL BULAN
    ========================= */
    public function generateAuto()
    {
        $db = \Config\Database::connect();

        $users = $db->table('users')
            ->where('role_id', 2)
            ->where('status_aktif', 'Aktif')
            ->get()
            ->getResultArray();

        if (count($users) < 3) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Minimal 3 satpam untuk 3 shift'
            ]);
        }

        $month = $this->request->getGet('month');

        if (!$month) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Month wajib diisi'
            ]);
        }

        [$year, $m] = explode('-', $month);
        $days = cal_days_in_month(CAL_GREGORIAN, $m, $year);

        $jadwalModel = new JadwalModel();

        /* histori bulan sebelumnya */
        $prevMonth = date('Y-m', strtotime("$month -1 month"));

        $history = $db->table('jadwal_patroli')
            ->select('user_id, COUNT(*) as total')
            ->like('tanggal', $prevMonth)
            ->groupBy('user_id')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($history as $h) {
            $map[$h['user_id']] = $h['total'];
        }

        /* fairness sorting */
        usort($users, function ($a, $b) use ($map) {
            return ($map[$a['user_id']] ?? 0) <=> ($map[$b['user_id']] ?? 0);
        });

        $index = 0;

        $shifts = [
            'pagi' => ['08:00:00', '16:00:00'],
            'siang' => ['16:00:00', '00:00:00'],
            'malam' => ['00:00:00', '08:00:00'],
        ];

        for ($day = 1; $day <= $days; $day++) {

            $date = sprintf('%s-%02d-%02d', $year, $m, $day);

            foreach ($shifts as $shift => $time) {

                $user = $users[$index % count($users)];
                $index++;

                $jadwalModel->insert([
                    'user_id' => $user['user_id'],
                    'ruangan_id' => 1,
                    'tanggal' => $date,
                    'urutan_kunjungan' => 1,
                    'waktu_mulai' => $time[0],
                    'waktu_selesai' => $time[1],
                ]);
            }
        }

        return $this->response->setJSON([
            'status' => true,
            'message' => "Jadwal berhasil dibuat untuk $month"
        ]);
    }
}