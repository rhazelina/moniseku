<?php

namespace App\Controllers;

class Kehadiran extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Data Kehadiran'
        ];

        return view('admin/kehadiran', $data);
    }
}