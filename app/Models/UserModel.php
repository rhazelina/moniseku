<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';

    protected $allowedFields = [
        'username',
        'nama_lengkap',
        'role_id',
        'status_aktif',
        'rfid_uid'
    ];

    public function getPetugas()
    {
        return $this->where('role_id', 2)
                    ->where('status_aktif', 'Aktif')
                    ->findAll();
    }
}