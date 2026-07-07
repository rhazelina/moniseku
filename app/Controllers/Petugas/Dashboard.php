<?php

namespace App\Controllers\Petugas;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        $this->requireLogin();
        $this->requireRole('Petugas');

        return view('petugas/dashboard');
    }
}