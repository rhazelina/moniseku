<?php
// LOKASI: app/Controllers/BaseController.php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $session;

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);

        $this->session = session();

        $uri      = service('uri');
        $segment1 = $uri->getSegment(1);

        // ==========================
        // 🔥 SKIP RULES
        // ==========================
        $isLoginPage = ($segment1 === 'login');
        $isAPI       = ($segment1 === 'api');

        // ==========================
        // 🔵 API (ESP32 / MOBILE) → NO SESSION CHECK
        // ==========================
        if ($isAPI) {
            return;
        }

        // ==========================
        // 🔵 LOGIN PAGE → FREE ACCESS
        // ==========================
        if ($isLoginPage) {
            return;
        }

        // ==========================
        // 🔐 WEB ADMIN PROTECTION
        // ==========================
        if (!$this->session->get('user_id')) {
            return redirect()->to(base_url('/login'));
        }

        // ==========================
        // 🔐 IDLE TIMEOUT (30 MENIT)
        // Diubah dari 3 menit (180 detik) → 30 menit (1800 detik)
        // agar evaluasi LCS yang berjalan di background tidak
        // memutus sesi admin di tengah proses.
        // ==========================
        $timeout = 1800;
        $last    = $this->session->get('last_activity');

        if ($last && (time() - $last > $timeout)) {
            $this->session->destroy();
            return redirect()->to(base_url('/login'));
        }

        // Update timestamp aktivitas terakhir
        $this->session->set('last_activity', time());
    }

    // ==========================
    // 🔐 MANUAL PROTECTION (WEB ONLY)
    // ==========================
    protected function requireLogin(): void
    {
        if (!$this->session->get('user_id')) {
            header('Location: ' . base_url('/login'));
            exit;
        }
    }

    protected function requireRole(string $role): void
    {
        if ($this->session->get('role') !== $role) {
            header('Location: ' . base_url('/login'));
            exit;
        }
    }
}