<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Users extends BaseController
{
    public function index()
    {
        $this->requireLogin();
        $this->requireRole('Administrator');

        $db = \Config\Database::connect();

        $data['users'] = $db->query("
            SELECT users.*, roles.nama_role
            FROM users
            JOIN roles ON users.role_id = roles.role_id
        ")->getResultArray();

        $data['roles'] = $db->table('roles')->get()->getResultArray();

        return view('admin/users', $data);
    }
}