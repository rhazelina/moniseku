<?php

namespace App\Models;

use CodeIgniter\Model;

class DeviceModel extends Model
{
    protected $table = 'perangkat_rfid';
    protected $primaryKey = 'alat_id';

    protected $allowedFields = [
        'ruangan_id',
        'kode_perangkat',
        'username_login',
        'password_login',
        'ip_address',
        'status_perangkat',
        'last_online'
    ];
}