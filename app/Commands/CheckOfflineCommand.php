<?php
// ================================================================
// LOKASI: /home/gkibrmpa/sistem_monitoring_rfid/app/Commands/CheckOfflineCommand.php
// VERSI  : v1.2
//
// PERBAIKAN:
// - Fix bug where('last_online IS NOT NULL') → where(..., null, false)
// - Ambil list perangkat SEBELUM di-update agar log akurat
// - Tampilkan ringkasan semua perangkat dengan warna
//
// CARA PASANG CRON (WAJIB agar status real-time):
//   crontab -e
//   Tambahkan baris:
//   * * * * * cd /home/gkibrmpa/sistem_monitoring_rfid && php spark offline:check >> /tmp/rfid_offline.log 2>&1
//
// Test manual:
//   php spark offline:check
// ================================================================

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CheckOfflineCommand extends BaseCommand
{
    protected $group       = 'RFID';
    protected $name        = 'offline:check';
    protected $description = 'Tandai perangkat RFID sebagai Offline jika tidak mengirim heartbeat dalam 3 menit.';

    public function run(array $params)
    {
        $db        = \Config\Database::connect();
        $threshold = 180; // 3 menit = 3x heartbeat interval 60 detik
        $cutoff    = date('Y-m-d H:i:s', time() - $threshold);

        // ── AMBIL DAFTAR SEBELUM DI-UPDATE ──────────────────────
        // Harus diambil SEBELUM update agar bisa ditampilkan di log
        $akanOffline = $db->table('perangkat_rfid')
            ->select('kode_perangkat, last_online, ip_address')
            ->where('status_perangkat', 'Online')
            ->where('last_online <', $cutoff)
            ->where('last_online IS NOT NULL', null, false) // ← FIX: parameter ke-3 false
            ->get()->getResultArray();

        // ── UPDATE STATUS KE OFFLINE ─────────────────────────────
        $db->table('perangkat_rfid')
            ->where('status_perangkat', 'Online')
            ->where('last_online <', $cutoff)
            ->where('last_online IS NOT NULL', null, false) // ← FIX
            ->update(['status_perangkat' => 'Offline']);

        $affected = $db->affectedRows();

        // ── LOG HASIL ────────────────────────────────────────────
        CLI::write(
            '[' . date('Y-m-d H:i:s') . '] offline:check'
            . ' | cutoff: ' . $cutoff
            . ' | ditandai offline: ' . $affected . ' perangkat',
            $affected > 0 ? 'yellow' : 'green'
        );

        foreach ($akanOffline as $dev) {
            CLI::write(
                '  ✗ ' . str_pad($dev['kode_perangkat'], 14)
                . ' last_online: ' . ($dev['last_online'] ?? 'NULL')
                . '  ip: ' . ($dev['ip_address'] ?? '-'),
                'yellow'
            );
        }

        // ── RINGKASAN SEMUA PERANGKAT ────────────────────────────
        $semua = $db->table('perangkat_rfid')
            ->select('kode_perangkat, status_perangkat, last_online, ip_address')
            ->orderBy('kode_perangkat', 'ASC')
            ->get()->getResultArray();

        CLI::newLine();
        CLI::write('Ringkasan status semua perangkat:', 'cyan');
        CLI::write(str_pad('Kode', 16) . str_pad('Status', 14) . 'Last Online');
        CLI::write(str_repeat('-', 55));

        foreach ($semua as $dev) {
            $warna = match($dev['status_perangkat']) {
                'Online'      => 'green',
                'Maintenance' => 'yellow',
                default       => 'red',
            };
            CLI::write(
                str_pad($dev['kode_perangkat'], 16)
                . str_pad($dev['status_perangkat'], 14)
                . ($dev['last_online'] ?? 'Belum pernah online'),
                $warna
            );
        }
    }
}