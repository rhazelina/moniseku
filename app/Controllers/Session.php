<?php

namespace App\Controllers;

class Session extends BaseController
{
    // EXTEND SESSION MANUAL
    public function extend()
    {
        session()->set('last_activity', time());

        return $this->response->setJSON([
            'status' => 'ok',
            'message' => 'Session extended'
        ]);
    }

    // KEEP ALIVE (PING SERVER)
    public function ping()
    {
        session()->set('last_activity', time());

        return $this->response->setJSON([
            'alive' => true
        ]);
    }
}