<?= $this->extend('layouts/adminkit_template') ?>
<?= $this->section('content') ?>

<?php /* ══════════════════════════════════════════════════════════════
   laporan.php  —  v2.1
   Kompatibel:
   - rfid_tap  (key waktu_tap)
   - Tidak ada tabel regu / regu_id
   - hasilMap  dari patroli_hasil
   - scanMap   dari rfid_tap (jenis='Terdaftar')
   - LCSService v5 (mendukung key waktu_tap & waktu_kunjungan)
══════════════════════════════════════════════════════════════ */

/* ── View helpers ─────────────────────────────────────────── */
function lprBadge(string $s): string {
    $m = [
        'Valid'         => ['lpr-b-valid',   'Valid'],
        'Normal'        => ['lpr-b-normal',  'Normal'],
        'Warning'       => ['lpr-b-warn',    'Perlu Perhatian'],
        'Tidak Lengkap' => ['lpr-b-bad',     'Tidak Lengkap'],
    ];
    [$cls, $lbl] = $m[$s] ?? ['lpr-b-bad', esc($s)];
    return '<span class="lpr-badge '.$cls.'">'.$lbl.'</span>';
}

function lprBar(float $p): string {
    $w = min((int)round($p), 100);
    $c = $p >= 100 ? '#16a34a' : ($p >= 80 ? '#2563eb' : ($p >= 50 ? '#d97706' : '#dc2626'));
    return '<div class="lpr-bar">'
         . '<div class="lpr-bar-t"><div class="lpr-bar-f" style="width:'.$w.'%;background:'.$c.'"></div></div>'
         . '<span class="lpr-bar-v" style="color:'.$c.'">'.number_format($p,1).'%</span>'
         . '</div>';
}

function shiftCls(string $n): string {
    if (stripos($n,'pagi')  !== false) return 'lpr-sh-pagi';
    if (stripos($n,'siang') !== false) return 'lpr-sh-siang';
    if (stripos($n,'malam') !== false) return 'lpr-sh-malam';
    return 'lpr-sh-other';
}

/**
 * Ambil waktu dari row scan — kompatibel waktu_tap dan waktu_kunjungan
 */
function getScanWaktu(array $sc): string {
    return $sc['waktu_tap'] ?? $sc['waktu_kunjungan'] ?? '';
}
?>

<style>
/* ══════════════════════════════════════════════════════════════
   LAPORAN PATROLI  —  v2.1
   Font: Nunito (display) + JetBrains Mono (data)
══════════════════════════════════════════════════════════════ */
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600&display=swap');

:root {
    --lf:  'Nunito', sans-serif;
    --lm:  'JetBrains Mono', monospace;
    --s:   #ffffff;
    --bg:  #f0f4f9;
    --bd:  #e2e8f0;
    --t1:  #0f172a;
    --t2:  #334155;
    --t3:  #64748b;
    --nv:  #0d1b2e;
    --bl:  #2563eb;
    --gn:  #16a34a;
    --am:  #d97706;
    --rd:  #dc2626;
    --vi:  #7c3aed;
    --te:  #0d9488;
    --r:   10px;
}
.lpr * { font-family: var(--lf); box-sizing: border-box; }

/* PAGE HEADER */
.lpr-ph {
    display: flex; align-items: flex-start;
    justify-content: space-between; flex-wrap: wrap;
    gap: 12px; margin-bottom: 20px;
}
.lpr-ph h2 {
    font-size: 22px; font-weight: 900; color: var(--t1);
    margin: 0; letter-spacing: -.4px;
}
.lpr-ph p { font-size: 13px; color: var(--t3); margin: 3px 0 0; }

/* FILTER */
.lpr-filter {
    background: var(--s); border: 1px solid var(--bd);
    border-radius: var(--r); padding: 14px 16px;
    display: flex; gap: 10px; flex-wrap: wrap;
    align-items: flex-end; margin-bottom: 20px;
}
.lpr-fg   { display: flex; flex-direction: column; gap: 4px; }
.lpr-lbl  {
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .08em; color: var(--t3);
}
.lpr-ctrl {
    height: 36px; padding: 0 12px; border: 1.5px solid var(--bd);
    border-radius: 8px; font-size: 13px; color: var(--t1);
    background: var(--bg); outline: none; transition: border-color .18s;
    font-family: var(--lf); min-width: 130px;
}
.lpr-ctrl:focus { border-color: var(--bl); }
.lpr-pills { display: flex; gap: 5px; flex-wrap: wrap; }
.lpr-pill {
    height: 36px; padding: 0 14px; border-radius: 8px;
    font-size: 12px; font-weight: 600; cursor: pointer;
    border: 1.5px solid var(--bd); background: var(--s);
    color: var(--t2); transition: all .18s; font-family: var(--lf);
}
.lpr-pill:hover  { border-color: var(--bl); color: var(--bl); }
.lpr-pill.active { background: var(--bl); color: #fff; border-color: var(--bl); }

/* BUTTONS */
.lpr-btn {
    display: inline-flex; align-items: center; gap: 6px;
    height: 36px; padding: 0 15px; border-radius: 8px;
    font-size: 12px; font-weight: 600; cursor: pointer;
    border: none; text-decoration: none; transition: all .18s;
    font-family: var(--lf); white-space: nowrap;
}
.lpr-btn-p  { background: var(--bl);  color: #fff; }
.lpr-btn-p:hover  { background: #1d4ed8; color:#fff; }
.lpr-btn-g  { background: var(--gn);  color: #fff; }
.lpr-btn-g:hover  { background: #15803d; }
.lpr-btn-o  { background: var(--s); border: 1.5px solid var(--bd); color: var(--t2); }
.lpr-btn-o:hover { border-color: var(--bl); color: var(--bl); }
.lpr-btn-sm { height: 28px; padding: 0 10px; font-size: 11px; }

/* STAT CARDS */
.lpr-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(148px, 1fr));
    gap: 12px; margin-bottom: 20px;
}
.lpr-sc {
    background: var(--s); border: 1px solid var(--bd);
    border-radius: var(--r); padding: 15px;
    position: relative; overflow: hidden;
    transition: transform .2s, box-shadow .2s;
}
.lpr-sc::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0;
    height: 3px; background: var(--sc, var(--bl));
    border-radius: var(--r) var(--r) 0 0;
}
.lpr-sc:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.07); }
.lpr-sc-ic {
    width: 32px; height: 32px; border-radius: 8px;
    background: var(--si, rgba(37,99,235,.1));
    color: var(--sc, var(--bl));
    display: grid; place-items: center; font-size: 15px; margin-bottom: 9px;
}
.lpr-sc-v  { font-size: 24px; font-weight: 900; color: var(--t1); line-height: 1; font-family: var(--lm); }
.lpr-sc-l  { font-size: 11px; color: var(--t3); margin-top: 4px; font-weight: 500; }

/* CHARTS GRID */
.lpr-cg {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 12px; margin-bottom: 22px;
}
@media (max-width: 1100px) { .lpr-cg { grid-template-columns: 1fr 1fr; } }
@media (max-width: 680px)  { .lpr-cg { grid-template-columns: 1fr; } }
.lpr-cc {
    background: var(--s); border: 1px solid var(--bd);
    border-radius: var(--r); padding: 16px;
}
.lpr-cct {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: var(--t3); margin-bottom: 12px;
    display: flex; align-items: center; gap: 6px;
}
.lpr-cw { position: relative; height: 180px; }

/* SECTION */
.lpr-sec {
    display: flex; align-items: center;
    justify-content: space-between; margin-bottom: 12px;
    flex-wrap: wrap; gap: 8px;
}
.lpr-sec-t {
    font-size: 15px; font-weight: 800; color: var(--t1);
    display: flex; align-items: center; gap: 8px;
}
.lpr-sec-t::before {
    content: ''; display: inline-block; width: 3px; height: 18px;
    background: var(--bl); border-radius: 99px;
}

/* TABLE CARD */
.lpr-tc {
    background: var(--s); border: 1px solid var(--bd);
    border-radius: var(--r); overflow: hidden; margin-bottom: 20px;
}
.lpr-toolbar {
    display: flex; align-items: center;
    justify-content: space-between;
    padding: 12px 16px; border-bottom: 1px solid var(--bd);
    flex-wrap: wrap; gap: 10px;
}
.lpr-sw       { position: relative; }
.lpr-sw i     { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--t3); font-size: 12px; }
.lpr-search   {
    padding: 6px 12px 6px 30px; border: 1.5px solid var(--bd);
    border-radius: 8px; font-size: 13px; width: 210px; outline: none;
    background: var(--bg); color: var(--t1); font-family: var(--lf);
    transition: border-color .18s;
}
.lpr-search:focus { border-color: var(--bl); }

/* MAIN TABLE */
.lpr-tbl { width: 100%; border-collapse: collapse; }
.lpr-tbl thead th {
    background: var(--bg); font-size: 10px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .07em; color: var(--t3);
    padding: 9px 14px; border-bottom: 1px solid var(--bd); white-space: nowrap;
}
.lpr-tbl thead th.tc { text-align: center; }
.lpr-tbl tbody td   {
    padding: 11px 14px; border-bottom: 1px solid var(--bd);
    font-size: 13px; color: var(--t2); vertical-align: middle;
}
.lpr-tbl tbody tr:last-child td { border-bottom: none; }
.lpr-tbl tbody tr:hover td      { background: rgba(37,99,235,.025); }
.lpr-tbl .date-row td {
    background: #f8fafc; font-size: 11px; font-weight: 700;
    color: var(--t3); letter-spacing: .05em; padding: 7px 14px;
    border-bottom: 1px solid var(--bd); border-top: 2px solid var(--bd);
}

/* SHIFT BADGE */
.lpr-sh {
    display: inline-block; padding: 3px 10px; border-radius: 6px;
    font-size: 11px; font-weight: 700; white-space: nowrap;
}
.lpr-sh-pagi  { background: #fef9c3; color: #854d0e; }
.lpr-sh-siang { background: #dbeafe; color: #1e40af; }
.lpr-sh-malam { background: #ede9fe; color: #5b21b6; }
.lpr-sh-other { background: #f1f5f9; color: #475569; }

/* STATUS BADGES */
.lpr-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 9px; border-radius: 99px; font-size: 10px;
    font-weight: 700; text-transform: uppercase;
    letter-spacing: .04em; white-space: nowrap;
}
.lpr-badge::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
.lpr-b-valid  { background: rgba(22,163,74,.1);   color: var(--gn); }
.lpr-b-normal { background: rgba(37,99,235,.1);   color: var(--bl); }
.lpr-b-warn   { background: rgba(215,119,6,.1);   color: var(--am); }
.lpr-b-bad    { background: rgba(220,38,38,.1);   color: var(--rd); }
.lpr-b-pending{ background: #f1f5f9; color: var(--t3); }

/* STATUS SHIFT */
.lpr-ss { display: inline-block; padding: 2px 8px; border-radius: 5px; font-size: 11px; font-weight: 700; }
.lpr-ss-done { background: #d1fae5; color: var(--gn); }
.lpr-ss-run  { background: #dbeafe; color: var(--bl); }
.lpr-ss-pend { background: #f1f5f9; color: var(--t3); }

/* MINI BAR */
.lpr-bar     { display: flex; align-items: center; gap: 7px; min-width: 90px; }
.lpr-bar-t   { flex: 1; height: 5px; background: var(--bd); border-radius: 99px; overflow: hidden; }
.lpr-bar-f   { height: 100%; border-radius: 99px; }
.lpr-bar-v   { font-size: 11px; font-weight: 700; min-width: 36px; text-align: right; font-family: var(--lm); }

/* PETUGAS COL */
.lpr-ptg     { display: flex; flex-direction: column; gap: 3px; }
.lpr-ptg-item{ display: flex; align-items: center; gap: 6px; font-size: 12px; }
.lpr-avc     {
    width: 26px; height: 26px; border-radius: 50%; background: var(--nv);
    color: #fff; font-size: 9px; font-weight: 700;
    display: grid; place-items: center; flex-shrink: 0;
}

/* MONO */
.lmono { font-family: var(--lm); font-size: 12px; }

/* INFO BOX */
.lpr-info {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 14px; border-radius: 8px;
    font-size: 13px; margin-bottom: 14px;
}
.lpr-info-b { background: rgba(37,99,235,.07); border: 1px solid rgba(37,99,235,.2); color: var(--bl); }
.lpr-info-a { background: rgba(215,119,6,.07);  border: 1px solid rgba(215,119,6,.2);  color: var(--am); }
.lpr-info-g { background: rgba(22,163,74,.07);  border: 1px solid rgba(22,163,74,.2);  color: var(--gn); }

/* EMPTY */
.lpr-empty { text-align: center; padding: 40px 20px; color: var(--t3); }
.lpr-empty i { font-size: 36px; display: block; margin-bottom: 10px; opacity: .25; }
.lpr-empty p { font-size: 14px; }

/* FOOTER */
.lpr-foot {
    padding: 9px 14px; border-top: 1px solid var(--bd);
    display: flex; justify-content: space-between;
    align-items: center; flex-wrap: wrap; gap: 8px;
}
.lpr-fi { font-size: 11px; color: var(--t3); font-family: var(--lm); }

/* ── MODAL ─────────────────────────────────────────────── */
.lpr-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.5); z-index: 3000;
    align-items: center; justify-content: center; padding: 20px;
}
.lpr-overlay.open { display: flex; }
.lpr-modal {
    background: var(--s); border-radius: var(--r);
    width: 100%; max-width: 840px; max-height: 90vh;
    overflow-y: auto; box-shadow: 0 28px 70px rgba(0,0,0,.22);
    animation: lprPop .22s ease;
}
@keyframes lprPop {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
.lpr-mhdr {
    position: sticky; top: 0; background: var(--nv);
    padding: 16px 20px; z-index: 1;
    display: flex; align-items: center;
    justify-content: space-between; gap: 12px;
}
.lpr-mhdr h4  { font-size: 15px; font-weight: 700; color: #f0f6ff; margin: 0; }
.lpr-mhdr p   { font-size: 11px; color: #7e99b8; margin: 2px 0 0; }
.lpr-mclose   {
    width: 30px; height: 30px; border-radius: 50%;
    border: 1px solid rgba(255,255,255,.2);
    background: rgba(255,255,255,.08); color: #fff;
    cursor: pointer; font-size: 14px; display: grid; place-items: center;
}
.lpr-mbody    { padding: 20px; }
.lpr-msec     { margin-bottom: 18px; }
.lpr-msec-t   {
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .1em; color: var(--t3);
    margin-bottom: 8px; padding-bottom: 5px; border-bottom: 1px solid var(--bd);
}

/* Ronde card modal */
.lpr-mrc     { border: 1px solid var(--bd); border-radius: 8px; overflow: hidden; margin-bottom: 10px; }
.lpr-mrc-hd  {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 12px; background: #f8fafc;
    border-bottom: 1px solid var(--bd); flex-wrap: wrap;
}
.lpr-mrc-ke  { font-size: 12px; font-weight: 700; color: var(--t2); }
.lpr-mrc-met {
    display: grid; grid-template-columns: repeat(3,1fr);
    gap: 8px; padding: 10px 12px;
}
@media (max-width: 480px) { .lpr-mrc-met { grid-template-columns: repeat(2,1fr); } }
.lpr-mrc-mb  { background: var(--bg); border-radius: 7px; padding: 8px; text-align: center; }
.lpr-mrc-mbl { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--t3); margin-bottom: 3px; }
.lpr-mrc-mbv { font-size: 17px; font-weight: 900; font-family: var(--lm); line-height: 1; }
.lpr-cg2 { color: var(--gn); }
.lpr-cb2 { color: var(--bl); }
.lpr-ca2 { color: var(--am); }
.lpr-cr2 { color: var(--rd); }
.lpr-cy2 { color: var(--t2); }

/* Rute ideal */
.lpr-ideal { display: flex; flex-wrap: wrap; align-items: center; gap: 3px; padding: 4px 0 8px; }

/* Chips */
.chips   { display: flex; flex-wrap: wrap; align-items: center; gap: 4px; padding: 4px 12px 10px; }
.chip    { font-size: 10px; font-weight: 600; border-radius: 4px; padding: 2px 7px; line-height: 1.7; }
.chip.ok   { background: #f0fdf4; color: var(--gn); border: 1px solid #bbf7d0; }
.chip.miss { background: #f8fafc; color: var(--t3); border: 1px solid var(--bd); }
.chip.skip { background: #fff5f5; color: var(--rd); border: 1px solid #fecaca; }
.chip-arr  { width: 9px; height: 9px; flex-shrink: 0; color: #cbd5e1; }
.all-ok    {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11px; color: var(--gn); padding: 4px 12px 10px;
}

/* Scan table modal */
.lpr-stbl  { width: 100%; border-collapse: collapse; font-size: 12px; }
.lpr-stbl thead th {
    padding: 7px 12px; font-size: 9px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .07em; color: var(--t3);
    background: #f8fafc; border-bottom: 1px solid var(--bd); white-space: nowrap;
}
.lpr-stbl thead th.tc { text-align: center; }
.lpr-stbl tbody td {
    padding: 7px 12px; border-bottom: 1px solid #f1f5f9;
    color: var(--t2); vertical-align: middle;
}
.lpr-stbl tbody tr:last-child td { border-bottom: none; }
.lpr-stbl tbody tr:hover td      { background: #f8fafc; }
.dot-ok {
    display: inline-flex; align-items: center; justify-content: center;
    width: 18px; height: 18px; background: #dcfce7; border-radius: 50%;
}
.dot-ok svg { width: 10px; height: 10px; color: var(--gn); }
.dot-no {
    display: inline-flex; align-items: center; justify-content: center;
    width: 18px; height: 18px; background: #f1f5f9; border-radius: 50%;
}
.dot-no svg { width: 10px; height: 10px; color: var(--t3); }

/* Scroll */
::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: #f1f5f9; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }

@media (max-width: 768px) {
    .lpr-stats   { grid-template-columns: repeat(2,1fr); }
    .lpr-filter, .lpr-ph { flex-direction: column; }
    .lpr-search  { width: 100%; }
    .lpr-toolbar { flex-direction: column; }
}
</style>

<?php
/* ── Hitung ringkasan ─────────────────────────────────────── */
$allHasil = [];
$allScans  = [];
foreach ($hasilMap as $jId => $byPetugas) {
    foreach ($byPetugas as $uid => $rondes) {
        foreach ($rondes as $rr) { $allHasil[] = $rr; }
    }
}
foreach ($scanMap as $jId => $byUser) {
    foreach ($byUser as $uid => $ss) {
        foreach ($ss as $sc) { $allScans[] = $sc; }
    }
}
$totalScan           = count($allScans);
$totalPatroli        = count($allHasil);
$totalShift          = count($jadwalList);
$patroliLengkap      = count(array_filter($allHasil,
    fn($r) => in_array($r['status_patroli'],['Valid','Normal'])));
$patroliTidakLengkap = $totalPatroli - $patroliLengkap;
$petugasAktif        = count(array_unique(array_column($allScans,'user_id')));

/* ── Grafik harian ─────────────────────────────────────────── */
$hMap = [];
foreach ($allScans as $sc) {
    $waktu = getScanWaktu($sc);
    if ($waktu) {
        $d = date('Y-m-d', strtotime($waktu));
        $hMap[$d] = ($hMap[$d] ?? 0) + 1;
    }
}
$hLabels = $hValues = [];
for ($cur = strtotime($tglAwal); $cur <= strtotime($tglAkhir); $cur = strtotime('+1 day', $cur)) {
    $d        = date('Y-m-d', $cur);
    $hLabels[] = date('d M', $cur);
    $hValues[] = $hMap[$d] ?? 0;
}

/* ── Grafik kunjungan per titik ────────────────────────────── */
$tMap = [];
foreach ($allScans as $sc) {
    $k = $sc['kode_ruangan'] ?? '?';
    $tMap[$k] = ($tMap[$k] ?? 0) + 1;
}
ksort($tMap);

/* ── Grafik status patroli ─────────────────────────────────── */
$sMap = ['Valid'=>0,'Normal'=>0,'Warning'=>0,'Tidak Lengkap'=>0];
foreach ($allHasil as $h) {
    $st = $h['status_patroli'] ?? 'Tidak Lengkap';
    if (isset($sMap[$st])) $sMap[$st]++;
}
?>

<div class="lpr">

<!-- ═══ PAGE HEADER ══════════════════════════════════════════ -->
<div class="lpr-ph">
    <div>
        <h2>Laporan Patroli</h2>
        <p>
            <?= date('d M Y', strtotime($tglAwal)) ?>
            <?= $tglAwal !== $tglAkhir ? ' &mdash; '.date('d M Y', strtotime($tglAkhir)) : '' ?>
        </p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        <button onclick="downloadData()" class="lpr-btn lpr-btn-g">
            <i class="bi bi-download"></i> Download Data
        </button>
        <button onclick="lprEvalAll()" class="lpr-btn lpr-btn-o" id="btnEval">
            <i class="bi bi-lightning-charge"></i> Evaluasi Ulang
        </button>
    </div>
</div>

<!-- ═══ FILTER ═══════════════════════════════════════════════ -->
<form method="GET" action="<?= base_url('laporan') ?>" id="lprForm" class="lpr-filter">
    <input type="hidden" name="periode" id="periodeHidden" value="<?= esc($periode) ?>">

    <div class="lpr-fg">
        <span class="lpr-lbl">Periode</span>
        <div class="lpr-pills">
            <?php foreach ([
                'hari_ini'   => 'Hari Ini',
                'minggu_ini' => 'Minggu Ini',
                'bulan_ini'  => 'Bulan Ini',
                'tahun_ini'  => 'Tahun Ini',
                'custom'     => 'Custom',
            ] as $k => $v): ?>
            <button type="button" class="lpr-pill <?= $periode===$k?'active':'' ?>"
                    onclick="setPeriode('<?= $k ?>')"><?= $v ?></button>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="lpr-fg" id="crA" style="<?= $periode!=='custom'?'display:none':'' ?>">
        <span class="lpr-lbl">Dari</span>
        <input type="date" name="tgl_awal" class="lpr-ctrl" value="<?= esc($tglAwal) ?>">
    </div>
    <div class="lpr-fg" id="crB" style="<?= $periode!=='custom'?'display:none':'' ?>">
        <span class="lpr-lbl">Sampai</span>
        <input type="date" name="tgl_akhir" class="lpr-ctrl" value="<?= esc($tglAkhir) ?>">
    </div>

    <div class="lpr-fg">
        <span class="lpr-lbl">Shift</span>
        <select name="shift_id" class="lpr-ctrl" onchange="this.form.submit()">
            <option value="">Semua Shift</option>
            <?php foreach ($allShift as $sh): ?>
            <option value="<?= $sh['shift_id'] ?>" <?= $filterShift==$sh['shift_id']?'selected':'' ?>>
                <?= esc($sh['nama_shift']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- ── TAMBAHAN: Filter Petugas ── -->
    <div class="lpr-fg">
        <span class="lpr-lbl">Petugas</span>
        <select name="petugas_id" class="lpr-ctrl" onchange="this.form.submit()">
            <option value="">Semua Petugas</option>
            <?php foreach ($allPetugas as $pt): ?>
            <option value="<?= $pt['user_id'] ?>" <?= $filterPetugas == $pt['user_id'] ? 'selected' : '' ?>>
                <?= esc($pt['nama_lengkap']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <!-- ── AKHIR TAMBAHAN ── -->

    <div class="lpr-fg" id="crBtn" style="<?= $periode!=='custom'?'display:none':'' ?>">
        <span class="lpr-lbl">&nbsp;</span>
        <button type="submit" class="lpr-btn lpr-btn-p">
            <i class="bi bi-search"></i> Tampilkan
        </button>
    </div>
</form>

<!-- ═══ INFO BOX ══════════════════════════════════════════════ -->
<?php if ($totalPatroli===0 && $totalScan===0): ?>
<div class="lpr-info lpr-info-b">
    <i class="bi bi-info-circle-fill"></i>
    <span>Belum ada data scan RFID pada periode ini.</span>
</div>
<?php elseif ($patroliTidakLengkap===0 && $totalPatroli>0): ?>
<div class="lpr-info lpr-info-g">
    <i class="bi bi-patch-check-fill"></i>
    <span>Semua <strong><?= $totalPatroli ?></strong> patroli pada periode ini berhasil lengkap.</span>
</div>
<?php elseif ($patroliTidakLengkap>0): ?>
<div class="lpr-info lpr-info-a">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <span>Terdapat <strong><?= $patroliTidakLengkap ?></strong> patroli tidak lengkap — periksa tabel di bawah.</span>
</div>
<?php endif; ?>

<!-- ═══ STAT CARDS ════════════════════════════════════════════ -->
<div class="lpr-stats">
<?php $cards = [
    ['bi-upc-scan',          $totalScan,           'Total Scan RFID',  'var(--bl)',  'rgba(37,99,235,.1)'],
    ['bi-shield-shaded',     $totalPatroli,        'Total Patroli',    'var(--vi)',  'rgba(124,58,237,.1)'],
    ['bi-clock-history',     $totalShift,          'Total Shift',      'var(--te)',  'rgba(13,148,136,.1)'],
    ['bi-check-circle-fill', $patroliLengkap,      'Patroli Lengkap',  'var(--gn)',  'rgba(22,163,74,.1)'],
    ['bi-x-circle-fill',     $patroliTidakLengkap, 'Tidak Lengkap',    'var(--rd)',  'rgba(220,38,38,.1)'],
    ['bi-people-fill',       $petugasAktif,        'Petugas Aktif',    'var(--am)',  'rgba(215,119,6,.1)'],
];
foreach ($cards as [$ic,$val,$lbl,$sc,$si]): ?>
<div class="lpr-sc" style="--sc:<?= $sc ?>;--si:<?= $si ?>">
    <div class="lpr-sc-ic"><i class="bi <?= $ic ?>"></i></div>
    <div class="lpr-sc-v"><?= number_format($val) ?></div>
    <div class="lpr-sc-l"><?= $lbl ?></div>
</div>
<?php endforeach; ?>
</div>

<!-- ═══ GRAFIK ═══════════════════════════════════════════════ -->
<div class="lpr-cg">
    <div class="lpr-cc">
        <div class="lpr-cct"><i class="bi bi-bar-chart-line" style="color:var(--bl)"></i> Aktivitas Patroli Harian</div>
        <div class="lpr-cw"><canvas id="cHarian"></canvas></div>
    </div>
    <div class="lpr-cc">
        <div class="lpr-cct"><i class="bi bi-geo-alt-fill" style="color:var(--gn)"></i> Kunjungan per Titik</div>
        <div class="lpr-cw"><canvas id="cTitik"></canvas></div>
    </div>
    <div class="lpr-cc">
        <div class="lpr-cct"><i class="bi bi-pie-chart-fill" style="color:var(--vi)"></i> Status Patroli</div>
        <div class="lpr-cw" id="cStatusWrap"><canvas id="cStatus"></canvas></div>
    </div>
</div>

<!-- ═══ TABEL LAPORAN ════════════════════════════════════════ -->
<div class="lpr-sec">
    <div class="lpr-sec-t">
        Rekap Shift &amp; Patroli
        <span style="font-size:11px;font-weight:400;color:var(--t3);"><?= count($jadwalList) ?> jadwal</span>
    </div>
</div>

<?php if (empty($jadwalList)): ?>
<div class="lpr-tc">
    <div class="lpr-empty">
        <i class="bi bi-calendar-x"></i>
        <p>Tidak ada jadwal pada periode ini.</p>
    </div>
</div>
<?php else: ?>

<div class="lpr-tc">
    <div class="lpr-toolbar">
        <div class="lpr-sw">
            <i class="bi bi-search"></i>
            <input type="text" class="lpr-search" id="lprSearch"
                   placeholder="Cari petugas, shift, status…">
        </div>
        <div style="display:flex;gap:6px;">
            <button class="lpr-btn lpr-btn-o lpr-btn-sm" onclick="downloadCSV()">
                <i class="bi bi-filetype-csv"></i> CSV
            </button>
            <button class="lpr-btn lpr-btn-o lpr-btn-sm" onclick="downloadExcel()">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </button>
        </div>
    </div>

    <div style="overflow-x:auto;">
    <table class="lpr-tbl" id="lprTable">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Shift</th>
                <th>Jam</th>
                <th>Petugas Bertugas</th>
                <th class="tc">Ronde</th>
                <th class="tc">Scan</th>
                <th>Kelengkapan</th>
                <th>Kepatuhan Rute</th>
                <th>Status</th>
                <th>Titik Terlewat</th>
                <th class="tc">Aksi</th>
            </tr>
        </thead>
        <tbody id="lprTbody">
        <?php
        /* Kelompokkan per tanggal */
        $byDate = [];
        foreach ($jadwalList as $jdw) {
            $byDate[$jdw['tanggal']][] = $jdw;
        }

        foreach ($byDate as $tgl => $jdwList):
            $isToday = ($tgl === date('Y-m-d'));
        ?>

        <!-- Date group row -->
        <tr class="date-row">
            <td colspan="11">
                <span style="font-family:var(--lm)"><?= date('l, d F Y', strtotime($tgl)) ?></span>
                <?php if ($isToday): ?>
                <span style="background:var(--bl);color:#fff;font-size:9px;font-weight:700;
                             padding:1px 7px;border-radius:99px;margin-left:6px;">HARI INI</span>
                <?php endif; ?>
            </td>
        </tr>

        <?php foreach ($jdwList as $jdw):
            $jsId   = $jdw['jadwal_shift_id'];
            $hasil  = $hasilMap[$jsId] ?? [];
            $scansJ = $scanMap[$jsId]  ?? [];

            /* Kumpulkan semua ronde */
            $allRondes  = [];
            $totalScanJ = 0;
            foreach ($hasil  as $uid => $rl) { foreach ($rl as $r) $allRondes[] = $r; }
            foreach ($scansJ as $uid => $ss) { $totalScanJ += count($ss); }

            /* Hitung agregat */
            $avgCov = $avgLcs = 0.0;
            $statusWorst = 'Valid';
            $terlewatSet = [];

            if (!empty($allRondes)) {
                $sumCov = $sumLcs = 0.0;
                $rank   = ['Tidak Lengkap'=>0,'Warning'=>1,'Normal'=>2,'Valid'=>3];
                foreach ($allRondes as $rr) {
                    $sumCov += (float)$rr['coverage_persen'];
                    $sumLcs += (float)($rr['nilai_lcs'] ?? $rr['lcs_persen'] ?? 0);
                    if (($rank[$rr['status_patroli']] ?? 0) < ($rank[$statusWorst] ?? 3)) {
                        $statusWorst = $rr['status_patroli'];
                    }
                    /* Titik terlewat dari urutan_aktual JSON/CSV */
                    $aktual = [];
                    if (!empty($rr['urutan_aktual'])) {
                        $raw = trim($rr['urutan_aktual']);
                        if ($raw[0] === '[') {
                            $aktual = json_decode($raw, true) ?? [];
                        } else {
                            $aktual = array_map('trim', explode(',', $raw));
                        }
                        $aktual = array_unique($aktual);
                    }
                    foreach ($idealKode as $kd) {
                        if (!in_array(trim($kd), $aktual)) {
                            $terlewatSet[$kd] = true;
                        }
                    }
                }
                $cnt    = count($allRondes);
                $avgCov = $sumCov / $cnt;
                $avgLcs = $sumLcs / $cnt;
            }
            $terlewatList = array_keys($terlewatSet);

            /* Search string */
            $srch = strtolower(
                $tgl.' '.$jdw['nama_shift'].' '.
                ($jdw['petugas_1_nama']??'').' '.
                ($jdw['petugas_2_nama']??'').' '.
                $statusWorst
            );

            /* Status shift badge */
            $ss  = $jdw['status_shift'] ?? 'Belum Mulai';
            $ssc = match($ss) {
                'Selesai'   => 'lpr-ss-done',
                'Berjalan'  => 'lpr-ss-run',
                default     => 'lpr-ss-pend',
            };
        ?>

        <tr data-s="<?= esc($srch) ?>" data-jsid="<?= $jsId ?>">
            <!-- Tanggal (kosong, ditampilkan di group row) -->
            <td></td>

            <!-- Shift + Status -->
            <td>
                <span class="lpr-sh <?= shiftCls($jdw['nama_shift']) ?>">
                    <?= esc($jdw['nama_shift']) ?>
                </span><br>
                <span class="lpr-ss <?= $ssc ?>" style="margin-top:4px;display:inline-block;">
                    <?= esc($ss) ?>
                </span>
            </td>

            <!-- Jam -->
            <td class="lmono" style="white-space:nowrap;">
                <?= substr($jdw['jam_mulai'],0,5) ?> – <?= substr($jdw['jam_selesai'],0,5) ?>
            </td>

            <!-- Petugas (tanpa regu) -->
            <td>
                <div class="lpr-ptg">
                    <?php foreach ([
                        ['P1', $jdw['petugas_1_nama'] ?? ''],
                        ['P2', $jdw['petugas_2_nama'] ?? ''],
                    ] as [$role, $nama]):
                        if (!$nama) continue;
                    ?>
                    <div class="lpr-ptg-item">
                        <div class="lpr-avc"><?= strtoupper(mb_substr($nama,0,1)) ?></div>
                        <span style="font-size:12px;"><?= esc($nama) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </td>

            <!-- Ronde -->
            <td style="text-align:center;" class="lmono"><?= count($allRondes) ?></td>

            <!-- Scan -->
            <td style="text-align:center;" class="lmono"><?= $totalScanJ ?></td>

            <!-- Kelengkapan -->
            <td>
                <?php if (!empty($allRondes)): echo lprBar($avgCov);
                else: ?><span style="font-size:11px;color:var(--t3);">–</span><?php endif; ?>
            </td>

            <!-- Kepatuhan Rute (LCS) -->
            <td>
                <?php if (!empty($allRondes)): echo lprBar($avgLcs);
                else: ?><span style="font-size:11px;color:var(--t3);">–</span><?php endif; ?>
            </td>

            <!-- Status -->
            <td>
                <?php if (!empty($allRondes)): echo lprBadge($statusWorst);
                else: ?><span class="lpr-badge lpr-b-pending">Belum Ada Data</span><?php endif; ?>
            </td>

            <!-- Titik Terlewat -->
            <td>
                <?php if (empty($allRondes)): ?>
                    <span style="font-size:11px;color:var(--t3);">–</span>
                <?php elseif (empty($terlewatList)): ?>
                    <span style="font-size:11px;color:var(--gn);display:flex;align-items:center;gap:4px;">
                        <i class="bi bi-check-circle-fill"></i> 100% Terkunjungi
                    </span>
                <?php else: ?>
                    <div style="display:flex;flex-wrap:wrap;gap:3px;">
                        <?php foreach (array_slice($terlewatList,0,4) as $kd): ?>
                        <span style="background:#fff5f5;color:var(--rd);border:1px solid #fecaca;
                                     border-radius:4px;font-size:10px;font-weight:600;padding:1px 6px;">
                            <?= esc($kd) ?>
                        </span>
                        <?php endforeach; ?>
                        <?php if (count($terlewatList)>4): ?>
                        <span style="font-size:10px;color:var(--t3);">+<?= count($terlewatList)-4 ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </td>

            <!-- Aksi -->
            <td style="text-align:center;">
                <button class="lpr-btn lpr-btn-o lpr-btn-sm" onclick="openDetail(<?= $jsId ?>)">
                    <i class="bi bi-eye"></i> Detail
                </button>
            </td>
        </tr>

        <?php endforeach; // jdwList ?>
        <?php endforeach; // byDate ?>
        </tbody>
    </table>
    </div>

    <div class="lpr-foot">
        <span class="lpr-fi" id="lprInfo">Menampilkan <?= count($jadwalList) ?> jadwal</span>
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
            <?= lprBadge('Valid') ?><?= lprBadge('Normal') ?>
            <?= lprBadge('Warning') ?><?= lprBadge('Tidak Lengkap') ?>
        </div>
    </div>
</div>
<?php endif; ?>

</div><!-- /.lpr -->

<!-- ══════════════════════════════════════════════════════════
     MODAL DETAIL SHIFT
══════════════════════════════════════════════════════════════ -->
<div class="lpr-overlay" id="detailModal">
    <div class="lpr-modal" id="detailModalBox">
        <div class="lpr-mhdr">
            <div>
                <h4 id="modalTitle">Detail Shift</h4>
                <p id="modalSub"></p>
            </div>
            <button class="lpr-mclose" onclick="closeModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="lpr-mbody" id="modalBody">
            <div style="text-align:center;padding:40px;color:var(--t3);">
                <i class="bi bi-arrow-repeat" style="font-size:28px;animation:spin 1s linear infinite;display:block;margin-bottom:8px;"></i>
                Memuat data…
            </div>
        </div>
    </div>
</div>

<style>@keyframes spin { to { transform: rotate(360deg); } }</style>

<!-- ══════════════════════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
/* ── Chart data dari PHP ────────────────────────────────── */
const _H = { labels: <?= json_encode($hLabels) ?>, values: <?= json_encode($hValues) ?> };
const _T = { labels: <?= json_encode(array_keys($tMap)) ?>, values: <?= json_encode(array_values($tMap)) ?> };
const _S = <?= json_encode($sMap) ?>;

Chart.defaults.font.family = "'Nunito', sans-serif";
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#64748b';
const TT = {
    backgroundColor: '#1e293b', borderColor: '#334155', borderWidth: 1,
    titleColor: '#f1f5f9', bodyColor: '#94a3b8', padding: 10, cornerRadius: 8
};

/* Grafik Harian */
(function(){
    const ctx = document.getElementById('cHarian'); if (!ctx) return;
    const g   = ctx.getContext('2d').createLinearGradient(0,0,0,180);
    g.addColorStop(0,'rgba(37,99,235,.28)'); g.addColorStop(1,'rgba(37,99,235,.02)');
    new Chart(ctx,{
        type: 'line',
        data: { labels: _H.labels, datasets: [{
            label: 'Scan', data: _H.values,
            borderColor: '#2563eb', backgroundColor: g,
            borderWidth: 2, fill: true, tension: .38,
            pointBackgroundColor: '#2563eb',
            pointRadius: _H.labels.length > 25 ? 1 : 3, pointHoverRadius: 5
        }]},
        options: { responsive: true, maintainAspectRatio: false,
            plugins: { legend: {display:false}, tooltip: TT },
            scales: {
                x: { grid:{color:'rgba(0,0,0,.05)'}, ticks:{maxTicksLimit:10,color:'#94a3b8'} },
                y: { beginAtZero:true, grid:{color:'rgba(0,0,0,.05)'}, ticks:{precision:0,color:'#94a3b8'} }
            }
        }
    });
})();

/* Grafik Titik */
(function(){
    const ctx = document.getElementById('cTitik'); if (!ctx || !_T.labels.length) return;
    const pal = ['#2563eb','#16a34a','#d97706','#dc2626','#7c3aed','#0d9488'];
    new Chart(ctx,{
        type: 'bar',
        data: { labels: _T.labels, datasets: [{
            label: 'Kunjungan', data: _T.values,
            backgroundColor: _T.labels.map((_,i)=>pal[i%pal.length]+'33'),
            borderColor:     _T.labels.map((_,i)=>pal[i%pal.length]),
            borderWidth: 1.5, borderRadius: 6
        }]},
        options: { responsive: true, maintainAspectRatio: false,
            plugins: { legend:{display:false}, tooltip: TT },
            scales: {
                x: { grid:{display:false}, ticks:{color:'#64748b',font:{weight:'600'}} },
                y: { beginAtZero:true, grid:{color:'rgba(0,0,0,.05)'}, ticks:{precision:0,color:'#94a3b8'} }
            }
        }
    });
})();

/* Grafik Status */
(function(){
    const ctx = document.getElementById('cStatus'); if (!ctx) return;
    const labels = Object.keys(_S);
    const values = Object.values(_S);
    const total  = values.reduce((a,b)=>a+b,0);
    if (total===0){
        const w = document.getElementById('cStatusWrap');
        if (w) w.innerHTML = '<div style="text-align:center;padding:30px;color:#94a3b8;font-size:12px;">Belum ada data</div>';
        return;
    }
    const cm = {'Valid':'#16a34a','Normal':'#2563eb','Warning':'#d97706','Tidak Lengkap':'#dc2626'};
    new Chart(ctx,{
        type: 'doughnut',
        data: { labels, datasets: [{
            data: values,
            backgroundColor: labels.map(l=>(cm[l]||'#94a3b8')+'33'),
            borderColor:     labels.map(l=>cm[l]||'#94a3b8'),
            borderWidth: 2, hoverOffset: 8
        }]},
        options: { responsive: true, maintainAspectRatio: false, cutout: '65%',
            plugins: {
                legend: { position:'bottom', labels:{padding:12,usePointStyle:true,pointStyleWidth:8,font:{size:11}} },
                tooltip: { ...TT, callbacks:{ label: c=>' '+c.raw+' ('+Math.round(c.raw/total*100)+'%)' } }
            }
        }
    });
})();

/* ── Filter periode ─────────────────────────────────────── */
function setPeriode(val){
    document.getElementById('periodeHidden').value = val;
    document.querySelectorAll('.lpr-pill').forEach(el=>el.classList.remove('active'));
    event.target.classList.add('active');
    const c = val==='custom';
    ['crA','crB','crBtn'].forEach(id=>{
        const el = document.getElementById(id);
        if (el) el.style.display = c ? '' : 'none';
    });
    if (!c) document.getElementById('lprForm').submit();
}

/* ── Search ─────────────────────────────────────────────── */
document.getElementById('lprSearch').addEventListener('input', function(){
    const q = this.value.toLowerCase().trim();
    let n = 0;
    document.querySelectorAll('#lprTbody tr:not(.date-row)').forEach(tr=>{
        const show = !q || (tr.getAttribute('data-s')||'').includes(q);
        tr.style.display = show ? '' : 'none';
        if (show) n++;
    });
    /* Sembunyikan date-row jika semua anaknya tersembunyi */
    document.querySelectorAll('#lprTbody tr.date-row').forEach(dr=>{
        let nx = dr.nextElementSibling;
        let anyVis = false;
        while (nx && !nx.classList.contains('date-row')){
            if (nx.style.display!=='none') anyVis = true;
            nx = nx.nextElementSibling;
        }
        dr.style.display = anyVis ? '' : 'none';
    });
    const info = document.getElementById('lprInfo');
    if (info) info.textContent = q ? 'Menampilkan '+n+' baris' : 'Menampilkan <?= count($jadwalList) ?> jadwal';
});

/* ── Evaluasi ulang semua ───────────────────────────────── */
function lprEvalAll(){
    const btn = document.getElementById('btnEval');
    btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Memproses…';
    btn.disabled  = true;
    fetch('<?= base_url('laporan/evaluasi-semua') ?>', {
        headers: {'X-Requested-With':'XMLHttpRequest'}
    })
    .then(r=>r.json())
    .then(d=>{
        alert('Selesai: '+d.diproses+' dari '+d.total+' shift dievaluasi.');
        location.reload();
    })
    .catch(()=>{
        btn.innerHTML = '<i class="bi bi-lightning-charge"></i> Evaluasi Ulang';
        btn.disabled  = false;
    });
}

/* ── Download CSV dari tabel ────────────────────────────── */
function collectRows(){
    const rows = [['Tanggal','Shift','Jam','Petugas 1','Petugas 2','Ronde',
                   'Scan','Kelengkapan (%)','Kepatuhan (%)','Status','Titik Terlewat']];
    document.querySelectorAll('#lprTbody tr:not(.date-row)').forEach(tr=>{
        if (tr.style.display==='none') return;
        const tds = tr.querySelectorAll('td');
        if (tds.length < 11) return;
        const t = i => (tds[i]?.innerText||'').replace(/\n/g,' ').trim();
        const p = tr.querySelectorAll('.lpr-ptg-item');
        rows.push([t(0),t(1),t(2),
            p[0]?.innerText.trim()||'',
            p[1]?.innerText.trim()||'',
            t(4),t(5),t(6),t(7),t(8),t(9)]);
    });
    return rows;
}
function downloadCSV(){
    const rows = collectRows();
    const csv  = rows.map(r=>r.map(v=>'"'+String(v).replace(/"/g,'""')+'"').join(',')).join('\n');
    const a    = document.createElement('a');
    a.href     = URL.createObjectURL(new Blob(['\uFEFF'+csv],{type:'text/csv;charset=utf-8'}));
    a.download = 'laporan_patroli_<?= date('Ymd') ?>.csv';
    a.click();
}
function downloadExcel(){
    window.location.href = '<?= base_url('laporan/export-excel') ?>?'
        + new URLSearchParams({
            periode:    document.getElementById('periodeHidden').value,
            shift_id:   '<?= esc($filterShift) ?>',
            tgl_awal:   '<?= esc($tglAwal) ?>',
            tgl_akhir:  '<?= esc($tglAkhir) ?>',
            petugas_id: '<?= esc($filterPetugas) ?>',
        }).toString();
}
function downloadData(){
    const m = document.createElement('div');
    m.style = 'position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:4000;display:flex;align-items:center;justify-content:center;';
    m.innerHTML = `<div style="background:#fff;border-radius:12px;padding:24px;width:290px;box-shadow:0 20px 50px rgba(0,0,0,.2);font-family:'Nunito',sans-serif;">
        <p style="font-size:15px;font-weight:800;color:#0f172a;margin:0 0 10px;">Download Data</p>
        <p style="font-size:12px;color:#64748b;margin:0 0 16px;">Periode: <?= date('d M Y',strtotime($tglAwal)) ?><?= $tglAwal!==$tglAkhir?' – '.date('d M Y',strtotime($tglAkhir)):'' ?></p>
        <button onclick="downloadCSV();this.closest('div[style]').remove()"
            style="width:100%;padding:10px;margin-bottom:8px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;">
            <i class="bi bi-filetype-csv"></i> Download CSV
        </button>
        <button onclick="downloadExcel();this.closest('div[style]').remove()"
            style="width:100%;padding:10px;margin-bottom:8px;background:#16a34a;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;">
            <i class="bi bi-file-earmark-excel"></i> Download Excel
        </button>
        <button onclick="this.closest('div[style]').remove()"
            style="width:100%;padding:9px;background:#f1f5f9;color:#475569;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;">
            Batal
        </button>
    </div>`;
    document.body.appendChild(m);
    m.addEventListener('click', e=>{ if(e.target===m) m.remove(); });
}

/* ── MODAL DETAIL ───────────────────────────────────────── */
/*
 * Data jadwal, hasil, scan di-embed dari PHP sebagai JSON.
 * scanMap: key waktu_tap (dari rfid_tap) — dirender via getScanWaktuJS()
 */
const _DATA = {
    jadwalList: <?= json_encode($jadwalList) ?>,
    hasilMap:   <?= json_encode($hasilMap)   ?>,
    scanMap:    <?= json_encode($scanMap)    ?>,
    idealKode:  <?= json_encode($idealKode)  ?>,
};

/**
 * Kompatibel waktu_tap (rfid_tap) dan waktu_kunjungan (legacy).
 */
function getScanWaktuJS(sc){
    return sc.waktu_tap || sc.waktu_kunjungan || '';
}

function openDetail(jsId){
    document.getElementById('detailModal').classList.add('open');
    renderModal(jsId);
}
function closeModal(){
    document.getElementById('detailModal').classList.remove('open');
}
document.getElementById('detailModal').addEventListener('click', function(e){
    if (e.target===this) closeModal();
});

function renderModal(jsId){
    const jdw = _DATA.jadwalList.find(j=>j.jadwal_shift_id==jsId);
    if (!jdw){
        document.getElementById('modalBody').innerHTML =
            '<p style="padding:20px;color:#64748b;">Data tidak ditemukan.</p>';
        return;
    }

    const hasil   = _DATA.hasilMap[jsId] || {};
    const scans   = _DATA.scanMap[jsId]  || {};
    const ideal   = _DATA.idealKode;

    document.getElementById('modalTitle').textContent = 'Shift ' + jdw.nama_shift;
    document.getElementById('modalSub').textContent   =
        fmtDate(jdw.tanggal) + ' · ' +
        jdw.jam_mulai.slice(0,5) + ' – ' + jdw.jam_selesai.slice(0,5);

    let html = '';

    /* ── Rute ideal SOP ── */
    html += `<div class="lpr-msec">
        <div class="lpr-msec-t">Rute SOP Patroli</div>
        <div class="lpr-ideal">`;
    ideal.forEach((k,i)=>{
        html += `<span class="chip" style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;">${esc(k)}</span>`;
        if (i < ideal.length-1)
            html += `<svg class="chip-arr" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>`;
    });
    html += `</div></div>`;

    /* ── Per petugas (tanpa regu) ── */
    const petugas = [];
    if (jdw.petugas_1_id) petugas.push({id:jdw.petugas_1_id, nama:jdw.petugas_1_nama||'–', role:'Petugas 1'});
    if (jdw.petugas_2_id) petugas.push({id:jdw.petugas_2_id, nama:jdw.petugas_2_nama||'–', role:'Petugas 2'});

    petugas.forEach(p=>{
        const rondes = hasil[p.id] || [];
        const pScans = scans[p.id] || [];

        html += `<div class="lpr-msec">
            <div class="lpr-msec-t">${esc(p.role)} — ${esc(p.nama)}</div>`;

        if (!rondes.length && !pScans.length){
            html += `<div style="padding:12px;font-size:13px;color:#64748b;background:#f8fafc;border-radius:8px;">
                Belum ada data scan untuk petugas ini.</div>`;
        } else {
            /* Ronde-ronde */
            rondes.forEach(r=>{
                const cov = parseFloat(r.coverage_persen || 0);
                const lcs = parseFloat(r.nilai_lcs || r.lcs_persen || 0);
                const st  = r.status_patroli || '–';
                const spill = {'Valid':'lpr-b-valid','Normal':'lpr-b-normal',
                               'Warning':'lpr-b-warn','Tidak Lengkap':'lpr-b-bad'}[st] || 'lpr-b-pending';

                /* Parse urutan_aktual: bisa JSON array atau CSV string */
                let aktual = [];
                if (r.urutan_aktual){
                    const raw = String(r.urutan_aktual).trim();
                    if (raw[0]==='[') {
                        try { aktual = JSON.parse(raw); } catch(e) {}
                    } else {
                        aktual = raw.split(',').map(s=>s.trim());
                    }
                    aktual = [...new Set(aktual)];
                }
                const idealSet = {};
                ideal.forEach(k=>idealSet[k]=true);
                const terlewat = ideal.filter(k=>!aktual.includes(k));

                html += `<div class="lpr-mrc">
                    <div class="lpr-mrc-hd">
                        <span class="lpr-mrc-ke">Ronde ke-${r.patroli_ke}</span>
                        <span class="lpr-badge ${spill}">${esc(st)}</span>
                        <span style="margin-left:auto;font-size:11px;color:#94a3b8;">${r.jumlah_scan} scan</span>
                    </div>
                    <div class="lpr-mrc-met">
                        <div class="lpr-mrc-mb">
                            <div class="lpr-mrc-mbl">Kelengkapan</div>
                            <div class="lpr-mrc-mbv ${cov>=80?'lpr-cg2':cov>=50?'lpr-ca2':'lpr-cr2'}">${cov.toFixed(1)}%</div>
                        </div>
                        <div class="lpr-mrc-mb">
                            <div class="lpr-mrc-mbl">Kepatuhan Rute</div>
                            <div class="lpr-mrc-mbv ${lcs>=80?'lpr-cb2':lcs>=60?'lpr-cy2':'lpr-cr2'}">${lcs.toFixed(1)}%</div>
                        </div>
                        <div class="lpr-mrc-mb">
                            <div class="lpr-mrc-mbl">Titik Dikunjungi</div>
                            <div class="lpr-mrc-mbv lpr-cy2">
                                ${ideal.filter(k=>aktual.includes(k)).length}
                                <span style="font-size:12px;font-weight:500;">/${ideal.length}</span>
                            </div>
                        </div>
                    </div>`;

                /* Urutan scan */
                if (aktual.length){
                    html += `<div style="padding:4px 12px 0;">
                        <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-bottom:5px;">Urutan Scan</div>
                        <div class="chips">`;
                    aktual.forEach((k,i)=>{
                        html += `<span class="chip ${idealSet[k]?'ok':'miss'}">${esc(k)}</span>`;
                        if (i<aktual.length-1)
                            html += `<svg class="chip-arr" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>`;
                    });
                    html += `</div></div>`;
                }

                /* Titik terlewat */
                html += `<div style="padding:4px 12px 10px;">`;
                if (terlewat.length){
                    html += `<div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#ef4444;margin-bottom:5px;">Titik Terlewat</div>
                        <div class="chips">`;
                    terlewat.forEach(k=>{ html += `<span class="chip skip">${esc(k)}</span>`; });
                    html += `</div>`;
                } else {
                    html += `<div class="all-ok" style="padding:0;">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Semua titik berhasil dikunjungi.
                    </div>`;
                }
                html += `</div></div>`; // /lpr-mrc
            });

            /* Riwayat scan — kompatibel waktu_tap & waktu_kunjungan */
            if (pScans.length){
                html += `<div style="margin-top:10px;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
                    <div style="padding:8px 12px;background:#f8fafc;border-bottom:1px solid #e2e8f0;
                                font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.07em;">
                        Riwayat Scan (${pScans.length} record)
                    </div>
                    <div style="overflow-x:auto;max-height:220px;overflow-y:auto;">
                    <table class="lpr-stbl">
                        <thead><tr>
                            <th>Waktu Scan</th><th>Kode</th>
                            <th>Lokasi</th><th class="tc">Sesuai Rute</th>
                        </tr></thead>
                        <tbody>`;
                pScans.forEach(sc=>{
                    const waktu = getScanWaktuJS(sc);
                    const t = waktu
                        ? new Date(waktu).toLocaleTimeString('id-ID',
                            {hour:'2-digit',minute:'2-digit',second:'2-digit'})
                        : '–';
                    html += `<tr>
                        <td class="lmono">${t}</td>
                        <td style="font-weight:700;color:#1e293b;">${esc(sc.kode_ruangan||'–')}</td>
                        <td>${esc(sc.nama_ruangan||'–')}</td>
                        <td class="tc">${sc.is_lcs_match
                            ? `<span class="dot-ok"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></span>`
                            : `<span class="dot-no"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"/></svg></span>`
                        }</td>
                    </tr>`;
                });
                html += `</tbody></table></div></div>`;
            }
        }
        html += `</div>`; // /lpr-msec
    });

    document.getElementById('modalBody').innerHTML = html;
}

/* ── Helpers JS ─────────────────────────────────────────── */
function esc(s){
    return String(s||'')
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function fmtDate(s){
    const d = new Date(s);
    return d.toLocaleDateString('id-ID',{weekday:'long',day:'numeric',month:'long',year:'numeric'});
}
</script>

<?= $this->endSection() ?>