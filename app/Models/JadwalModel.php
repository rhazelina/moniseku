<?php

namespace App\Models;

use CodeIgniter\Model;

class JadwalModel extends Model
{
    protected $table = 'jadwal_patroli';
    protected $primaryKey = 'jadwal_id';

    protected $allowedFields = [
        'user_id',
        'ruangan_id',
        'tanggal',
        'urutan_kunjungan',
        'waktu_mulai',
        'waktu_selesai'
    ];
}