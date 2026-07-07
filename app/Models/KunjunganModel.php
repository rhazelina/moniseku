<?php

namespace App\Models;

use CodeIgniter\Model;

class KunjunganModel extends Model
{
    protected $table = 'kunjungan';
    protected $primaryKey = 'kunjungan_id';

    protected $allowedFields = [
        'user_id',
        'ruangan_id',
        'alat_id',
        'waktu_kunjungan',
        'status_validasi',
        'jadwal_shift_id',
        'urutan_aktual',
        'urutan_seharusnya',
        'is_lcs_match'
    ];
}