<?php

namespace App\Models;

use CodeIgniter\Model;

class RfidModel extends Model
{
    protected $table = 'kartu_rfid';
    protected $primaryKey = 'kartu_id';

    protected $allowedFields = [
        'uid_rfid',
        'user_id',
        'alat_id',
        'status',
        'created_at'
    ];
}