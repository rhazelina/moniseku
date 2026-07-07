<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// =====================================================
// AUTH
// =====================================================

$routes->get('/',           'Login::index');
$routes->get('login',       'Login::index');
$routes->post('login/auth', 'Login::auth');
$routes->get('logout',      'Login::logout');

// =====================================================
// DASHBOARD ADMIN
// =====================================================

$routes->get('dashboard',       'Dashboard::index');
$routes->get('admin/dashboard', 'Dashboard::index');

// =====================================================
// DASHBOARD PETUGAS
// =====================================================

$routes->get('petugas/dashboard',       'PetugasDashboard::index');
$routes->get('petugas/tracking',        'PetugasDashboard::tracking');
$routes->get('petugas/analisis',        'PetugasDashboard::analisis');
$routes->get('petugas/kalender',        'PetugasDashboard::kalenderData');
$routes->get('petugas/download-jadwal', 'PetugasDashboard::downloadJadwal');
$routes->get('petugas/logout',          'PetugasDashboard::logout');  // ← 

// =====================================================
// USERS
// =====================================================

$routes->get('users',                'Users::index');
$routes->post('users/store',         'Users::store');
$routes->post('users/update/(:num)', 'Users::update/$1');
$routes->get('users/delete/(:num)',  'Users::delete/$1');

// =====================================================
// PERANGKAT RFID
// =====================================================

$routes->get('perangkat',                       'Perangkat::index');
$routes->post('perangkat/store',                'Perangkat::store');
$routes->post('perangkat/update/(:num)',         'Perangkat::update/$1');
$routes->get('perangkat/delete/(:num)',          'Perangkat::delete/$1');
$routes->post('perangkat/toggle-status/(:num)',  'Perangkat::toggleStatus/$1');
$routes->post('perangkat/store-ruangan',         'Perangkat::storeRuangan');

// =====================================================
// KEHADIRAN
// =====================================================

$routes->get('kehadiran', 'Kehadiran::index');

// =====================================================
// JADWAL SHIFT
// =====================================================

$routes->get('jadwal',              'JadwalController::index');
$routes->get('jadwal/getByDate',    'JadwalController::getByDate');
$routes->get('jadwal/getByMonth',   'JadwalController::getByMonth');
$routes->post('jadwal/save',        'JadwalController::save');
$routes->post('jadwal/delete',      'JadwalController::delete');
$routes->get('jadwal/export-pdf',   'JadwalController::exportPdf');
$routes->get('jadwal/export-excel', 'JadwalController::exportExcel');
$routes->get('jadwal/syncStatus',   'JadwalController::syncStatus');

// =====================================================
// LOG RFID
// =====================================================

$routes->get('log-rfid',               'LogRfid::index');
$routes->get('log-rfid/export',        'LogRfid::export');
$routes->get('log-rfid/live',          'LogRfid::live');
$routes->get('log-rfid/device-status', 'LogRfid::deviceStatus');

// =====================================================
// AUDIT TRAIL
// =====================================================

$routes->get('audit-trail', 'AuditTrail::index');

// =====================================================
// EVALUASI PATROLI (LCS)
// =====================================================

$routes->group('evaluasi', function ($routes) {
    $routes->get('/',                              'PatroliController::index');
    $routes->get('detail/(:num)',                  'PatroliController::detail/$1');
    $routes->post('shift/(:num)',                  'PatroliController::evaluasiShift/$1');
    $routes->post('petugas/(:num)/(:num)',         'PatroliController::evaluasiPetugas/$1/$2');
    $routes->get('api/coverage/(:num)/(:num)',     'PatroliController::hitungCoverage/$1/$2');
    $routes->get('api/lcs/(:num)/(:num)',          'PatroliController::hitungLCS/$1/$2');
    $routes->post('simpan',                        'PatroliController::simpanHasilEvaluasi');
});

// =====================================================
// LAPORAN PATROLI
// =====================================================

$routes->group('laporan', function ($routes) {
    $routes->get('/',              'LaporanController::index');
    $routes->get('detail/(:num)', 'LaporanController::detail/$1');
    $routes->post('evaluasi/(:num)', 'LaporanController::evaluasiShift/$1');
    $routes->get('export-pdf',     'LaporanController::exportPdf');
    $routes->get('export-excel',   'LaporanController::exportExcel');
});

// =====================================================
// API ESP32
// =====================================================

$routes->group('api', function ($routes) {

    // Device
    $routes->post('device/login',        'Api\DeviceApi::login');
    $routes->post('device/heartbeat',    'Api\DeviceApi::heartbeat');
    $routes->get('device/status/(:num)', 'Api\DeviceApi::status/$1');
    $routes->get('device/check-offline', 'Api\DeviceApi::checkOffline');

    // RFID
    $routes->post('rfid/scan',           'Api\RfidApi::scan');
    $routes->post('rfid/assign',         'Api\RfidApi::assign');

    // Users
    $routes->get('users/petugas',        'Api\UserApi::petugas');
});