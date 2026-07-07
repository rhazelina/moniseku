<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class AuditTrail extends BaseController
{
    public function index()
    {
        return view('admin/audit_trail');
    }
}