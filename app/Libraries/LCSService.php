<?php
// LOKASI: app/Libraries/LCSService.php
// VERSI : v5.0 — Kompatibel rfid_tap
//
// PERUBAHAN dari v4:
// - pecahSesi() sekarang membaca key 'waktu_tap' ATAU 'waktu_kunjungan'
//   (alias apapun yang dikirim, dicek secara otomatis)
// - evaluasiPetugas() sama persis, tidak ada perubahan interface
// - Semua metode lain tidak berubah

namespace App\Libraries;

class LCSService
{
    /**
     * Jeda (detik) antar scan yang dianggap pergantian ronde patroli.
     * 60 menit.
     */
    const JEDA_SESI = 3600;

    // ═══════════════════════════════════════════════════════════════
    // 1. ALGORITMA LCS UTAMA
    // ═══════════════════════════════════════════════════════════════

    /**
     * Hitung LCS + Coverage antara urutan ideal dan aktual.
     *
     * @param  string[] $ideal   ['T01','T02','T03','T04','T05','T06']
     * @param  string[] $actual  Urutan scan mentah (boleh duplikat)
     * @return array
     */
    public function calculate(array $ideal, array $actual): array
    {
        $n = count($ideal);
        $m = count($actual);

        if ($n === 0) {
            return $this->emptyResult($ideal, $actual);
        }

        // ── DP table O(n×m) ──────────────────────────────────────
        $dp = array_fill(0, $n + 1, array_fill(0, $m + 1, 0));
        for ($i = 1; $i <= $n; $i++) {
            for ($j = 1; $j <= $m; $j++) {
                $dp[$i][$j] = $ideal[$i - 1] === $actual[$j - 1]
                    ? $dp[$i - 1][$j - 1] + 1
                    : max($dp[$i - 1][$j], $dp[$i][$j - 1]);
            }
        }
        $panjangLCS = $dp[$n][$m];

        // ── Traceback ─────────────────────────────────────────────
        $seq = []; $i = $n; $j = $m;
        while ($i > 0 && $j > 0) {
            if ($ideal[$i - 1] === $actual[$j - 1]) {
                array_unshift($seq, $ideal[$i - 1]);
                $i--; $j--;
            } elseif ($dp[$i - 1][$j] > $dp[$i][$j - 1]) {
                $i--;
            } else {
                $j--;
            }
        }

        // ── Coverage (unik aktual ∩ ideal) ────────────────────────
        $unik     = array_unique($actual);
        $visited  = array_values(array_intersect($ideal, $unik));
        $terlewat = array_values(array_diff($ideal, $unik));
        $coverage = round(count($visited) / $n * 100, 2);
        $lcsPct   = round($panjangLCS / $n * 100, 2);

        return [
            'panjang_lcs'    => $panjangLCS,
            'persentase'     => $lcsPct,
            'coverage'       => $coverage,
            'titik_terlewat' => $terlewat,
            'lcs_sequence'   => $seq,
            'urutan_ideal'   => $ideal,
            'urutan_aktual'  => $actual,
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    // 2. PECAH SESI PATROLI
    // ═══════════════════════════════════════════════════════════════

    /**
     * Pecah array tap/kunjungan menjadi sesi berdasarkan jeda waktu.
     *
     * Kompatibel dengan dua sumber:
     *   - rfid_tap  : key 'waktu_tap'
     *   - (legacy)  : key 'waktu_kunjungan'
     *
     * @param  array  $rows  Baris terurut ASC, tiap baris punya
     *                       key 'waktu_tap' atau 'waktu_kunjungan'
     * @return array  Array of array (tiap sub-array = 1 sesi patroli)
     */
    public function pecahSesi(array $rows): array
    {
        if (empty($rows)) return [];

        // Tentukan key waktu sekali saja di awal
        $waktuKey = isset($rows[0]['waktu_tap']) ? 'waktu_tap' : 'waktu_kunjungan';

        $sesiList = [];
        $sesi     = [$rows[0]];

        for ($i = 1, $total = count($rows); $i < $total; $i++) {
            $gap = strtotime($rows[$i][$waktuKey])
                 - strtotime($rows[$i - 1][$waktuKey]);

            if (abs($gap) > self::JEDA_SESI) {
                $sesiList[] = $sesi;
                $sesi       = [];
            }
            $sesi[] = $rows[$i];
        }
        $sesiList[] = $sesi;
        return array_values(array_filter($sesiList));
    }

    // ═══════════════════════════════════════════════════════════════
    // 3. STATUS OTOMATIS
    // ═══════════════════════════════════════════════════════════════

    public function tentukanStatus(float $coverage, float $lcs): string
    {
        if ($coverage < 50)                 return 'Tidak Lengkap';
        if ($coverage >= 100 && $lcs >= 90) return 'Valid';
        if ($coverage >= 80  && $lcs >= 60) return 'Normal';
        return 'Warning';
    }

    // ═══════════════════════════════════════════════════════════════
    // 4. EVALUASI SATU PETUGAS — helper utama
    // ═══════════════════════════════════════════════════════════════

    /**
     * Evaluasi lengkap satu petugas dalam satu shift.
     *
     * @param  array  $rows          Baris terurut waktu, butuh key
     *                               'kode_ruangan' dan
     *                               'waktu_tap' ATAU 'waktu_kunjungan'
     * @param  array  $idealKode     ['T01','T02',...]
     * @param  int    $jadwalShiftId
     * @param  int    $userId
     * @param  string $tanggal       Y-m-d
     * @param  int    $patroliKeOffset
     * @param  int    $patroliKeStep
     * @return array
     */
    public function evaluasiPetugas(
        array  $rows,
        array  $idealKode,
        int    $jadwalShiftId,
        int    $userId,
        string $tanggal,
        int    $patroliKeOffset = 1,
        int    $patroliKeStep   = 2
    ): array {
        $sesiList  = $this->pecahSesi($rows);
        $hasilList = [];

        foreach ($sesiList as $idx => $sesi) {
            $aktualKode = array_column($sesi, 'kode_ruangan');
            $hasilLCS   = $this->calculate($idealKode, $aktualKode);
            $status     = $this->tentukanStatus($hasilLCS['coverage'], $hasilLCS['persentase']);
            $patroliKe  = $patroliKeOffset + ($idx * $patroliKeStep);

            $hasilList[] = [
                'db' => [
                    'jadwal_shift_id' => $jadwalShiftId,
                    'petugas_id'      => $userId,
                    'tanggal'         => $tanggal,
                    'patroli_ke'      => $patroliKe,
                    'coverage_persen' => $hasilLCS['coverage'],
                    'lcs_persen'      => $hasilLCS['persentase'],
                    'jumlah_titik'    => count($idealKode),
                    'jumlah_scan'     => count($sesi),
                    'status_patroli'  => $status,
                ],
                'lcs'        => $hasilLCS,
                'sesi'       => $sesi,
                'patroli_ke' => $patroliKe,
                'status'     => $status,
            ];
        }

        return $hasilList;
    }

    // ─────────────────────────────────────────────────────────────
    private function emptyResult(array $ideal, array $actual): array
    {
        return [
            'panjang_lcs'    => 0,
            'persentase'     => 0.0,
            'coverage'       => 0.0,
            'titik_terlewat' => $ideal,
            'lcs_sequence'   => [],
            'urutan_ideal'   => $ideal,
            'urutan_aktual'  => $actual,
        ];
    }
}