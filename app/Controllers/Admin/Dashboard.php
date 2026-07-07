<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        $this->requireLogin();
        $this->requireRole('Administrator');

        return view('admin/dashboard');
    }
}