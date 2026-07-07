<?= $this->extend('layouts/adminkit_template') ?>
<?= $this->section('content') ?>

<?php
/* ── Badge status ─────────────────────────────────────────────── */
function rfBadge(string $status, string $jenis = 'Terdaftar'): string {
    if ($jenis === 'Asing') {
        return '<span class="lrf-badge lrf-b-asing"><i class="bi bi-person-slash"></i> Kartu Asing</span>';
    }
    return match($status) {
        'Sesuai'          => '<span class="lrf-badge lrf-b-sesuai"><i class="bi bi-check-circle"></i> Sesuai</span>',
        'Di Luar Jadwal'  => '<span class="lrf-badge lrf-b-luar"><i class="bi bi-clock-history"></i> Di Luar Jadwal</span>',
        'Tidak Terjadwal' => '<span class="lrf-badge lrf-b-luar"><i class="bi bi-calendar-x"></i> Tdk Terjadwal</span>',
        'Tidak Sesuai'    => '<span class="lrf-badge lrf-b-tidak"><i class="bi bi-x-circle"></i> Tidak Sesuai</span>',
        'Terlambat'       => '<span class="lrf-badge lrf-b-lambat"><i class="bi bi-alarm"></i> Terlambat</span>',
        default           => '<span class="lrf-badge lrf-b-unknown">'.esc($status).'</span>',
    };
}

function rfKeteranganRow(array $row): string {
    $jenis = $row['jenis_log'] ?? 'Terdaftar';
    if ($jenis === 'Asing') return 'Kartu tidak dikenali sistem';
    $sv = $row['status_validasi'] ?? '';
    if ($sv === 'Sesuai')
        return ($row['is_lcs_match'] ?? 0) ? 'Scan valid, rute benar' : 'Scan valid, rute kurang tepat';
    if (in_array($sv, ['Di Luar Jadwal','Tidak Terjadwal']))
        return !empty($row['jadwal_shift_id']) ? 'Scan di luar jam patroli' : 'Tidak ada jadwal aktif';
    if ($sv === 'Tidak Sesuai') return 'Scan tidak memenuhi kriteria';
    if ($sv === 'Terlambat')    return 'Scan melewati batas waktu';
    return '–';
}

/* Inisialisasi variabel dari controller */
$dateMode      ??= 'minggu_ini';
$tglAwal       ??= date('Y-m-d', strtotime('-6 days'));
$tglAkhir      ??= date('Y-m-d');
$tglAwalRaw    ??= '';
$tglAkhirRaw   ??= '';
$filterJenis   ??= 'semua';
$filterStatus  ??= '';
$filterUid     ??= '';
$filterAlat    ??= '';
$search        ??= '';
$perPage       ??= 50;
$page          ??= 1;
$totalPage     ??= 1;
$totalLog      ??= 0;
$logs          ??= [];
$perangkatOptions ??= [];

/* Label mode tanggal */
$dateModeLabels = [
    'hari_ini'  => 'Hari Ini',
    'kemarin'   => 'Kemarin',
    'minggu_ini'=> 'Minggu Ini',
    'bulan_ini' => 'Bulan Ini',
    'custom'    => 'Kustom',
];

/* URL dasar untuk export — bawa semua filter termasuk date_mode */
$exportParams = http_build_query([
    'date_mode' => $dateMode,
    'tgl_awal'  => $tglAwalRaw ?: $tglAwal,
    'tgl_akhir' => $tglAkhirRaw ?: $tglAkhir,
    'filter'    => $filterJenis,
    'status'    => $filterStatus,
    'uid'       => $filterUid,
    'alat_id'   => $filterAlat,
    'q'         => $search,
    'limit'     => $perPage,
]);
$exportBase = base_url('log-rfid/export') . '?' . $exportParams;
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

:root{
  --lrf-bg      : #f4f6f9;
  --lrf-white   : #ffffff;
  --lrf-border  : #e2e8f0;
  --lrf-border2 : #cbd5e1;
  --lrf-t1      : #0f172a;
  --lrf-t2      : #334155;
  --lrf-t3      : #64748b;
  --lrf-t4      : #94a3b8;
  --lrf-blue    : #2563eb;
  --lrf-blue-l  : #eff6ff;
  --lrf-blue-m  : #bfdbfe;
  --lrf-green   : #16a34a;
  --lrf-green-l : #f0fdf4;
  --lrf-yellow  : #b45309;
  --lrf-yellow-l: #fffbeb;
  --lrf-red     : #dc2626;
  --lrf-red-l   : #fef2f2;
  --lrf-orange  : #ea580c;
  --lrf-orange-l: #fff7ed;
  --lrf-purple  : #7c3aed;
  --lrf-purple-l: #f5f3ff;
  --lrf-mono    : 'DM Mono', monospace;
  --lrf-sans    : 'Plus Jakarta Sans', sans-serif;
  --lrf-r       : 10px;
  --lrf-shadow  : 0 1px 3px rgba(15,23,42,.06), 0 1px 2px rgba(15,23,42,.04);
  --lrf-shadow2 : 0 4px 12px rgba(15,23,42,.1);
}

.lrf *{ font-family: var(--lrf-sans); box-sizing: border-box; }
.lrf{ padding: 0 0 56px; background: var(--lrf-bg); min-height: 100vh; }

/* ── PAGE HEADER ── */
.lrf-header{
  padding: 22px 0 18px;
  border-bottom: 1px solid var(--lrf-border);
  margin-bottom: 22px;
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 12px;
}
.lrf-header-title{
  font-size: 18px; font-weight: 800; color: var(--lrf-t1);
  margin: 0 0 3px; display: flex; align-items: center; gap: 9px;
  letter-spacing: -.3px;
}
.lrf-header-title .lrf-icon-wrap{
  width: 34px; height: 34px; border-radius: 9px;
  background: var(--lrf-blue); display: grid; place-items: center;
  color: #fff; font-size: 15px; flex-shrink: 0;
}
.lrf-header-sub{ font-size: 12px; color: var(--lrf-t3); margin: 0; }
.lrf-header-actions{ display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }

/* ── BUTTONS ── */
.lrf-btn{
  display: inline-flex; align-items: center; gap: 6px;
  height: 36px; padding: 0 16px; border-radius: 8px; border: none;
  font-size: 12px; font-weight: 600; cursor: pointer; white-space: nowrap;
  font-family: var(--lrf-sans); text-decoration: none;
  transition: all .15s ease;
}
.lrf-btn-primary  { background: var(--lrf-blue); color: #fff; }
.lrf-btn-primary:hover{ background: #1d4ed8; color: #fff; }
.lrf-btn-outline  { background: var(--lrf-white); color: var(--lrf-t2); border: 1px solid var(--lrf-border2); box-shadow: var(--lrf-shadow); }
.lrf-btn-outline:hover{ border-color: var(--lrf-blue); color: var(--lrf-blue); }
.lrf-btn-ghost    { background: transparent; color: var(--lrf-t3); border: 1px solid var(--lrf-border); }
.lrf-btn-ghost:hover{ border-color: var(--lrf-blue); color: var(--lrf-blue); background: var(--lrf-blue-l); }
.lrf-btn-sm{ height: 30px; padding: 0 12px; font-size: 11px; }

/* ── DATE QUICK TABS ── */
.lrf-date-tabs{
  display: flex; gap: 0; background: var(--lrf-bg);
  border: 1px solid var(--lrf-border2); border-radius: 9px;
  padding: 3px; overflow: hidden; flex-wrap: nowrap;
}
.lrf-date-tab{
  flex: 1; height: 32px; padding: 0 14px;
  border: none; border-radius: 7px; cursor: pointer;
  font-size: 11px; font-weight: 600; color: var(--lrf-t3);
  background: transparent; font-family: var(--lrf-sans);
  transition: all .15s ease; white-space: nowrap;
}
.lrf-date-tab:hover{ color: var(--lrf-blue); background: rgba(37,99,235,.06); }
.lrf-date-tab.active{
  background: var(--lrf-white); color: var(--lrf-blue);
  box-shadow: var(--lrf-shadow2);
}

/* ── CUSTOM DATE PANEL ── */
.lrf-custom-panel{
  display: none; margin-top: 10px;
  padding: 12px 14px; background: var(--lrf-blue-l);
  border: 1px solid var(--lrf-blue-m); border-radius: 8px;
  align-items: center; gap: 10px; flex-wrap: wrap;
}
.lrf-custom-panel.show{ display: flex; }
.lrf-custom-label{ font-size: 11px; font-weight: 700; color: var(--lrf-blue); white-space: nowrap; }
.lrf-custom-sep{ font-size: 11px; color: var(--lrf-t3); }

/* ── FILTER CARD ── */
.lrf-filter-card{
  background: var(--lrf-white); border: 1px solid var(--lrf-border);
  border-radius: var(--lrf-r); box-shadow: var(--lrf-shadow);
  padding: 16px 18px; margin-bottom: 16px;
}
.lrf-filter-title{
  font-size: 11px; font-weight: 700; text-transform: uppercase;
  letter-spacing: .08em; color: var(--lrf-t3);
  margin-bottom: 14px; display: flex; align-items: center; gap: 6px;
}
.lrf-filter-title::before{
  content: ''; display: block;
  width: 3px; height: 12px; background: var(--lrf-blue); border-radius: 99px;
}
.lrf-filter-section{ margin-bottom: 14px; }
.lrf-filter-section-label{
  font-size: 10px; font-weight: 700; text-transform: uppercase;
  letter-spacing: .07em; color: var(--lrf-t4); margin-bottom: 8px;
}
.lrf-filter-row{ display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; }
.lrf-fg{ display: flex; flex-direction: column; gap: 4px; }
.lrf-flbl{ font-size: 10px; font-weight: 600; color: var(--lrf-t3); text-transform: uppercase; letter-spacing: .06em; }
.lrf-ctrl{
  height: 36px; padding: 0 10px;
  background: var(--lrf-bg); border: 1px solid var(--lrf-border2);
  border-radius: 7px; color: var(--lrf-t1); font-size: 12px;
  font-family: var(--lrf-sans); outline: none; min-width: 130px;
  transition: border-color .15s, box-shadow .15s;
}
.lrf-ctrl:focus{ border-color: var(--lrf-blue); box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
.lrf-ctrl-uid{ min-width: 150px; font-family: var(--lrf-mono); font-size: 11px; }
.lrf-ctrl-search{ min-width: 180px; }
.lrf-divider{ border: none; border-top: 1px solid var(--lrf-border); margin: 14px 0; }
.lrf-filter-actions{
  display: flex; gap: 8px; flex-wrap: wrap;
  align-items: center;
}
.lrf-export-group{ display: flex; gap: 6px; flex-wrap: wrap; margin-left: auto; }
.lrf-export-label{ font-size: 10px; color: var(--lrf-t4); align-self: center; font-weight: 600; letter-spacing: .05em; }
.lrf-limit-wrap{ display: flex; align-items: center; gap: 6px; }
.lrf-limit-lbl{ font-size: 11px; color: var(--lrf-t3); white-space: nowrap; }
.lrf-ctrl-limit{ min-width: 80px; }

/* ── PERIOD BADGE ── */
.lrf-period-badge{
  display: inline-flex; align-items: center; gap: 6px;
  padding: 4px 12px 4px 8px; border-radius: 6px;
  background: var(--lrf-blue-l); border: 1px solid var(--lrf-blue-m);
  font-size: 11px; font-weight: 600; color: var(--lrf-blue);
}
.lrf-period-badge i{ font-size: 12px; }

/* ── TABLE AREA ── */
.lrf-table-card{
  background: var(--lrf-white); border: 1px solid var(--lrf-border);
  border-radius: var(--lrf-r); box-shadow: var(--lrf-shadow); overflow: hidden;
}
.lrf-table-hdr{
  display: flex; align-items: center; justify-content: space-between;
  padding: 12px 18px; border-bottom: 1px solid var(--lrf-border);
  flex-wrap: wrap; gap: 10px;
}
.lrf-table-title{ font-size: 13px; font-weight: 700; color: var(--lrf-t1); display: flex; align-items: center; gap: 8px; }
.lrf-count-pill{
  font-family: var(--lrf-mono); font-size: 11px; color: var(--lrf-blue);
  background: var(--lrf-blue-l); border: 1px solid var(--lrf-blue-m);
  padding: 2px 9px; border-radius: 99px; font-weight: 500;
}
.lrf-tbl-wrap{ overflow-x: auto; }
.lrf-tbl{ width: 100%; border-collapse: collapse; }
.lrf-tbl thead th{
  background: #f8fafc;
  font-size: 10px; font-weight: 700; text-transform: uppercase;
  letter-spacing: .07em; color: var(--lrf-t3);
  padding: 10px 14px; border-bottom: 1px solid var(--lrf-border);
  white-space: nowrap; text-align: left;
}
.lrf-tbl thead th.tc{ text-align: center; }
.lrf-tbl tbody td{
  padding: 11px 14px; border-bottom: 1px solid var(--lrf-border);
  font-size: 12px; color: var(--lrf-t2); vertical-align: middle;
}
.lrf-tbl tbody tr:last-child td{ border-bottom: none; }
.lrf-tbl tbody tr:hover td{ background: #f8fafc; }

/* date separator */
.lrf-daterow td{
  background: #f1f5f9 !important;
  font-size: 10px; font-weight: 700; letter-spacing: .08em;
  color: var(--lrf-t3); padding: 6px 14px !important;
  border-top: 1px solid var(--lrf-border) !important;
  border-bottom: 1px solid var(--lrf-border) !important;
  text-transform: uppercase; font-family: var(--lrf-mono);
}
.lrf-daterow-hari{
  display: inline-block; background: var(--lrf-blue); color: #fff;
  font-size: 9px; font-weight: 700; padding: 1px 6px;
  border-radius: 3px; margin-left: 6px; font-family: var(--lrf-mono);
}

/* ── CELLS ── */
.lrf-no{ font-family: var(--lrf-mono); font-size: 11px; color: var(--lrf-t4); }
.lrf-time-big{ font-family: var(--lrf-mono); font-size: 13px; font-weight: 500; color: var(--lrf-t1); white-space: nowrap; }
.lrf-time-date{ font-size: 10px; color: var(--lrf-t4); margin-top: 2px; }
.lrf-uid{
  font-family: var(--lrf-mono); font-size: 11px; color: #0369a1;
  background: #e0f2fe; border: 1px solid #bae6fd;
  padding: 3px 8px; border-radius: 5px; display: inline-block; letter-spacing: .04em;
}
.lrf-uid-unknown{
  font-family: var(--lrf-mono); font-size: 11px; color: var(--lrf-purple);
  background: var(--lrf-purple-l); border: 1px solid #ddd6fe;
  padding: 3px 8px; border-radius: 5px; display: inline-block;
}
.lrf-officer{ display: flex; align-items: center; gap: 8px; }
.lrf-av{
  width: 30px; height: 30px; border-radius: 8px; flex-shrink: 0;
  display: grid; place-items: center; font-size: 12px; font-weight: 700;
  color: #fff; background: var(--lrf-blue);
}
.lrf-av-gray{ background: #94a3b8; }
.lrf-oname{ font-size: 12px; font-weight: 600; color: var(--lrf-t1); }
.lrf-ouname{ font-size: 10px; color: var(--lrf-t4); margin-top: 1px; }
.lrf-devname{ font-family: var(--lrf-mono); font-size: 12px; font-weight: 500; color: var(--lrf-t1); }
.lrf-devtipe{ font-size: 10px; color: var(--lrf-t4); margin-top: 2px; }
.lrf-loknama{ font-size: 12px; color: var(--lrf-t2); }
.lrf-lokkode{ font-family: var(--lrf-mono); font-size: 10px; color: var(--lrf-t4); margin-top: 2px; }
.lrf-jenis{
  font-family: var(--lrf-mono); font-size: 9px; font-weight: 700;
  padding: 2px 7px; border-radius: 4px; text-transform: uppercase; letter-spacing: .06em;
}
.lrf-j-terdaftar{ background: var(--lrf-blue-l); color: var(--lrf-blue); border: 1px solid var(--lrf-blue-m); }
.lrf-j-asing{ background: var(--lrf-purple-l); color: var(--lrf-purple); border: 1px solid #ddd6fe; }

/* ── BADGES ── */
.lrf-badge{
  display: inline-flex; align-items: center; gap: 4px;
  padding: 3px 9px; border-radius: 5px; white-space: nowrap;
  font-size: 10px; font-weight: 700; font-family: var(--lrf-mono); letter-spacing: .03em;
}
.lrf-badge i{ font-size: 10px; }
.lrf-b-sesuai { background: var(--lrf-green-l); color: var(--lrf-green); border: 1px solid #bbf7d0; }
.lrf-b-luar   { background: var(--lrf-yellow-l); color: var(--lrf-yellow); border: 1px solid #fde68a; }
.lrf-b-tidak  { background: var(--lrf-red-l); color: var(--lrf-red); border: 1px solid #fecaca; }
.lrf-b-lambat { background: var(--lrf-orange-l); color: var(--lrf-orange); border: 1px solid #fed7aa; }
.lrf-b-asing  { background: var(--lrf-purple-l); color: var(--lrf-purple); border: 1px solid #ddd6fe; }
.lrf-b-unknown{ background: #f1f5f9; color: var(--lrf-t3); border: 1px solid var(--lrf-border); }
.lrf-ket{ font-size: 11px; color: var(--lrf-t3); line-height: 1.5; }
.lrf-shift-mini{ font-family: var(--lrf-mono); font-size: 9px; color: var(--lrf-t4); margin-top: 2px; }

/* ── EMPTY ── */
.lrf-empty{ text-align: center; padding: 60px 20px; }
.lrf-empty-ico{ font-size: 36px; color: var(--lrf-border2); display: block; margin-bottom: 12px; }
.lrf-empty-t{ font-size: 14px; font-weight: 600; color: var(--lrf-t3); }
.lrf-empty-s{ font-size: 12px; color: var(--lrf-t4); margin-top: 4px; }

/* ── PAGINATION ── */
.lrf-pager{
  display: flex; align-items: center; justify-content: space-between;
  padding: 12px 18px; border-top: 1px solid var(--lrf-border);
  flex-wrap: wrap; gap: 10px; background: #f8fafc;
}
.lrf-pager-info{ font-size: 11px; color: var(--lrf-t3); }
.lrf-pager-info span{ font-family: var(--lrf-mono); font-weight: 500; color: var(--lrf-t1); }
.lrf-pager-btns{ display: flex; gap: 4px; }
.lrf-pg{
  width: 30px; height: 30px; border-radius: 6px; display: grid; place-items: center;
  font-size: 11px; font-weight: 600; font-family: var(--lrf-mono);
  border: 1px solid var(--lrf-border); background: var(--lrf-white);
  color: var(--lrf-t3); text-decoration: none; transition: all .14s;
}
.lrf-pg.act{ background: var(--lrf-blue); color: #fff; border-color: var(--lrf-blue); }
.lrf-pg:hover:not(.act){ border-color: var(--lrf-blue); color: var(--lrf-blue); }

/* ── LEGEND ── */
.lrf-legend{
  display: flex; flex-wrap: wrap; gap: 0; margin-top: 16px;
  background: var(--lrf-white); border: 1px solid var(--lrf-border);
  border-radius: var(--lrf-r); box-shadow: var(--lrf-shadow); overflow: hidden;
}
.lrf-legend-item{
  display: flex; align-items: center; gap: 10px;
  padding: 10px 16px; border-right: 1px solid var(--lrf-border);
  flex: 1; min-width: 200px;
}
.lrf-legend-item:last-child{ border-right: none; }
.lrf-legend-desc{ font-size: 11px; color: var(--lrf-t3); line-height: 1.4; }

/* responsive */
@media(max-width:900px){
  .lrf-filter-row{ flex-direction: column; }
  .lrf-ctrl{ min-width: 100%; }
  .lrf-export-group{ margin-left: 0; }
  .lrf-date-tabs{ flex-wrap: wrap; }
  .lrf-tbl thead th:nth-child(6),
  .lrf-tbl tbody td:nth-child(6){ display:none; }
}
</style>

<div class="lrf">

<!-- ── HEADER ── -->
<div class="lrf-header">
  <div>
    <h2 class="lrf-header-title">
      <span class="lrf-icon-wrap"><i class="bi bi-broadcast-pin"></i></span>
      Log RFID
    </h2>
    <p class="lrf-header-sub">Histori seluruh tapping kartu RFID — terdaftar &amp; kartu asing</p>
  </div>
  <div class="lrf-header-actions">
    <span class="lrf-period-badge">
      <i class="bi bi-calendar-range"></i>
      <?= esc($dateModeLabels[$dateMode] ?? 'Custom') ?>:
      <?= date('d M Y', strtotime($tglAwal)) ?>
      <?php if ($tglAwal !== $tglAkhir): ?>
        &ndash; <?= date('d M Y', strtotime($tglAkhir)) ?>
      <?php endif; ?>
    </span>
    <button class="lrf-btn lrf-btn-ghost" onclick="window.location.reload()">
      <i class="bi bi-arrow-clockwise"></i> Refresh
    </button>
  </div>
</div>

<!-- ── FILTER CARD ── -->
<div class="lrf-filter-card">
  <div class="lrf-filter-title"><i class="bi bi-funnel"></i> Filter &amp; Periode</div>

  <form method="GET" action="<?= base_url('log-rfid') ?>" id="lrfForm">
    <input type="hidden" name="date_mode" id="hidDateMode" value="<?= esc($dateMode) ?>">

    <!-- PERIODE CEPAT -->
    <div class="lrf-filter-section">
      <div class="lrf-filter-section-label"><i class="bi bi-calendar3"></i> Pilih Periode</div>
      <div class="lrf-date-tabs" id="dateTabs">
        <?php foreach ($dateModeLabels as $modeKey => $modeLabel): ?>
        <button type="button"
                class="lrf-date-tab <?= $dateMode === $modeKey ? 'active' : '' ?>"
                data-mode="<?= $modeKey ?>"
                onclick="setDateMode('<?= $modeKey ?>')">
          <?= $modeLabel ?>
        </button>
        <?php endforeach; ?>
      </div>

      <!-- Panel custom date -->
      <div class="lrf-custom-panel <?= $dateMode === 'custom' ? 'show' : '' ?>" id="customPanel">
        <span class="lrf-custom-label"><i class="bi bi-sliders"></i> Rentang Kustom</span>
        <div class="lrf-fg">
          <label class="lrf-flbl">Dari Tanggal</label>
          <input type="date" name="tgl_awal" id="inputTglAwal" class="lrf-ctrl"
                 value="<?= esc($dateMode === 'custom' ? ($tglAwalRaw ?: $tglAwal) : '') ?>"
                 style="min-width:150px">
        </div>
        <span class="lrf-custom-sep">—</span>
        <div class="lrf-fg">
          <label class="lrf-flbl">Hingga Tanggal</label>
          <input type="date" name="tgl_akhir" id="inputTglAkhir" class="lrf-ctrl"
                 value="<?= esc($dateMode === 'custom' ? ($tglAkhirRaw ?: $tglAkhir) : '') ?>"
                 style="min-width:150px">
        </div>
      </div>
    </div>

    <hr class="lrf-divider">

    <!-- FILTER DATA -->
    <div class="lrf-filter-section">
      <div class="lrf-filter-section-label"><i class="bi bi-filter"></i> Filter Data</div>
      <div class="lrf-filter-row">

        <div class="lrf-fg">
          <label class="lrf-flbl">Tipe Kartu</label>
          <select name="filter" class="lrf-ctrl">
            <option value="semua"     <?= $filterJenis==='semua'     ?'selected':'' ?>>Semua Tipe</option>
            <option value="terdaftar" <?= $filterJenis==='terdaftar' ?'selected':'' ?>>Terdaftar</option>
            <option value="asing"     <?= $filterJenis==='asing'     ?'selected':'' ?>>Kartu Asing</option>
          </select>
        </div>

        <div class="lrf-fg">
          <label class="lrf-flbl">Status Validasi</label>
          <select name="status" class="lrf-ctrl">
            <option value="">Semua Status</option>
            <?php foreach (['Sesuai','Di Luar Jadwal','Tidak Terjadwal','Tidak Sesuai','Terlambat'] as $st): ?>
            <option value="<?= $st ?>" <?= $filterStatus===$st?'selected':'' ?>><?= $st ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="lrf-fg">
          <label class="lrf-flbl">Perangkat</label>
          <select name="alat_id" class="lrf-ctrl">
            <option value="">Semua Perangkat</option>
            <?php foreach ($perangkatOptions as $opt): ?>
            <option value="<?= $opt['alat_id'] ?>" <?= $filterAlat==$opt['alat_id']?'selected':'' ?>>
              <?= esc($opt['kode_perangkat']) ?> — <?= esc($opt['tipe_perangkat']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="lrf-fg">
          <label class="lrf-flbl">UID Kartu</label>
          <input type="text" name="uid" class="lrf-ctrl lrf-ctrl-uid"
                 placeholder="Contoh: A1B2C3D4"
                 value="<?= esc($filterUid) ?>">
        </div>

        <div class="lrf-fg">
          <label class="lrf-flbl">Cari</label>
          <input type="text" name="q" class="lrf-ctrl lrf-ctrl-search"
                 placeholder="Nama, lokasi, perangkat…"
                 value="<?= esc($search) ?>">
        </div>

      </div>
    </div>

    <hr class="lrf-divider">

    <!-- AKSI -->
    <div class="lrf-filter-actions">
      <div class="lrf-limit-wrap">
        <span class="lrf-limit-lbl">Tampilkan</span>
        <select name="limit" class="lrf-ctrl lrf-ctrl-limit">
          <?php foreach ([25,50,100,200,500] as $lim): ?>
          <option value="<?= $lim ?>" <?= $perPage==$lim?'selected':'' ?>><?= $lim ?></option>
          <?php endforeach; ?>
        </select>
        <span class="lrf-limit-lbl">data</span>
      </div>

      <button type="submit" class="lrf-btn lrf-btn-primary">
        <i class="bi bi-search"></i> Terapkan Filter
      </button>
      <a href="<?= base_url('log-rfid') ?>" class="lrf-btn lrf-btn-outline">
        <i class="bi bi-x-circle"></i> Reset
      </a>

      <!-- EXPORT mengikuti filter aktif -->
      <div class="lrf-export-group">
        <span class="lrf-export-label">EXPORT:</span>
        <a href="<?= $exportBase ?>&format=csv" class="lrf-btn lrf-btn-outline lrf-btn-sm">
          <i class="bi bi-filetype-csv" style="color:#16a34a"></i> CSV
        </a>
        <a href="<?= $exportBase ?>&format=excel" class="lrf-btn lrf-btn-outline lrf-btn-sm">
          <i class="bi bi-file-earmark-spreadsheet" style="color:#1d4ed8"></i> Excel
        </a>
        <a href="<?= $exportBase ?>&format=pdf" target="_blank" class="lrf-btn lrf-btn-outline lrf-btn-sm">
          <i class="bi bi-file-earmark-pdf" style="color:#dc2626"></i> PDF
        </a>
      </div>
    </div>

    <input type="hidden" name="page" value="1">
  </form>
</div>

<!-- ── TABEL LOG ── -->
<div class="lrf-table-card">
  <div class="lrf-table-hdr">
    <div class="lrf-table-title">
      <i class="bi bi-table" style="color:var(--lrf-blue)"></i>
      Rekaman Tapping RFID
      <span class="lrf-count-pill"><?= number_format($totalLog) ?> entri</span>
    </div>
    <div style="font-size:11px;color:var(--lrf-t3)">
      <?= esc($dateModeLabels[$dateMode] ?? 'Periode') ?>:
      <strong><?= date('d M Y', strtotime($tglAwal)) ?></strong>
      <?php if ($tglAwal !== $tglAkhir): ?>
        s/d <strong><?= date('d M Y', strtotime($tglAkhir)) ?></strong>
      <?php endif; ?>
    </div>
  </div>

  <?php if (empty($logs)): ?>
  <div class="lrf-empty">
    <i class="bi bi-inbox lrf-empty-ico"></i>
    <div class="lrf-empty-t">Tidak ada data log</div>
    <div class="lrf-empty-s">Coba ubah periode atau filter yang aktif</div>
  </div>
  <?php else: ?>

  <div class="lrf-tbl-wrap">
    <table class="lrf-tbl">
      <thead>
        <tr>
          <th style="width:42px">#</th>
          <th>WAKTU SCAN</th>
          <th>UID KARTU</th>
          <th>PETUGAS</th>
          <th>PERANGKAT</th>
          <th>LOKASI</th>
          <th class="tc">TIPE</th>
          <th class="tc">STATUS</th>
          <th>KETERANGAN</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $prevDate = null;
      $no = ($page - 1) * $perPage;
      $hariID  = ['Sunday'=>'MINGGU','Monday'=>'SENIN','Tuesday'=>'SELASA',
                  'Wednesday'=>'RABU','Thursday'=>'KAMIS','Friday'=>'JUMAT','Saturday'=>'SABTU'];
      $bulanID = ['01'=>'JAN','02'=>'FEB','03'=>'MAR','04'=>'APR','05'=>'MEI',
                  '06'=>'JUN','07'=>'JUL','08'=>'AGU','09'=>'SEP','10'=>'OKT',
                  '11'=>'NOV','12'=>'DES'];

      foreach ($logs as $row):
        $no++;
        $rowDate = date('Y-m-d', strtotime($row['waktu_kunjungan']));
        $isAsing = ($row['jenis_log'] ?? 'Terdaftar') === 'Asing';
        $isToday = ($rowDate === date('Y-m-d'));

        if ($rowDate !== $prevDate):
          $prevDate = $rowDate;
          $dn = $hariID[date('l', strtotime($rowDate))] ?? date('l', strtotime($rowDate));
          $bn = $bulanID[date('m', strtotime($rowDate))] ?? date('M', strtotime($rowDate));
      ?>
      <tr class="lrf-daterow">
        <td colspan="9">
          <?= $dn ?> &middot; <?= date('d', strtotime($rowDate)) ?> <?= $bn ?> <?= date('Y', strtotime($rowDate)) ?>
          <?php if ($isToday): ?>
            <span class="lrf-daterow-hari">HARI INI</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endif; ?>

      <tr>
        <td class="lrf-no"><?= $no ?></td>

        <td>
          <div class="lrf-time-big"><?= date('H:i:s', strtotime($row['waktu_kunjungan'])) ?></div>
          <div class="lrf-time-date"><?= date('d/m/Y', strtotime($row['waktu_kunjungan'])) ?></div>
        </td>

        <td>
          <?php if ($isAsing): ?>
            <span class="lrf-uid-unknown"><?= esc($row['uid_kartu']) ?></span>
          <?php else: ?>
            <span class="lrf-uid"><?= esc($row['uid_kartu'] ?? '–') ?></span>
          <?php endif; ?>
        </td>

        <td>
          <div class="lrf-officer">
            <div class="lrf-av <?= $isAsing ? 'lrf-av-gray' : '' ?>">
              <?php if ($isAsing): ?>
                <i class="bi bi-person-slash" style="font-size:12px"></i>
              <?php else: ?>
                <?= strtoupper(mb_substr($row['nama_petugas'] ?? '?', 0, 1)) ?>
              <?php endif; ?>
            </div>
            <div>
              <div class="lrf-oname">
                <?= esc($isAsing ? 'Tidak Dikenal' : ($row['nama_petugas'] ?? '–')) ?>
              </div>
              <div class="lrf-ouname">
                <?php if ($isAsing): ?>
                  kartu asing
                <?php else: ?>
                  <?= !empty($row['username']) ? '@'.esc($row['username']) : '–' ?>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </td>

        <td>
          <div class="lrf-devname"><?= esc($row['kode_perangkat'] ?? '–') ?></div>
          <div class="lrf-devtipe"><?= esc($row['tipe_perangkat'] ?? '–') ?></div>
        </td>

        <td>
          <div class="lrf-loknama"><?= esc($row['nama_ruangan'] ?? '–') ?></div>
          <div class="lrf-lokkode">
            <?= esc($row['kode_ruangan'] ?? '–') ?>
            <?= !empty($row['lokasi_ruangan']) && $row['lokasi_ruangan'] !== '-' ? ' &middot; '.esc($row['lokasi_ruangan']) : '' ?>
          </div>
        </td>

        <td style="text-align:center">
          <span class="lrf-jenis <?= $isAsing ? 'lrf-j-asing' : 'lrf-j-terdaftar' ?>">
            <?= $isAsing ? 'Asing' : 'Terdaftar' ?>
          </span>
        </td>

        <td style="text-align:center">
          <?= rfBadge($row['status_validasi'] ?? '', $row['jenis_log'] ?? 'Terdaftar') ?>
        </td>

        <td class="lrf-ket">
          <?= rfKeteranganRow($row) ?>
          <?php if (!$isAsing && !empty($row['nama_shift'])): ?>
            <div class="lrf-shift-mini">
              <?= esc($row['nama_shift']) ?>
              &middot; <?= substr($row['jam_mulai'] ?? '00:00', 0, 5) ?>–<?= substr($row['jam_selesai'] ?? '00:00', 0, 5) ?>
            </div>
          <?php endif; ?>
        </td>
      </tr>

      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- ── PAGINATION ── -->
  <div class="lrf-pager">
    <span class="lrf-pager-info">
      Halaman <span><?= $page ?></span> dari <span><?= $totalPage ?></span>
      &nbsp;&middot;&nbsp; Total <span><?= number_format($totalLog) ?></span> entri
      &nbsp;&middot;&nbsp; Tampil <span><?= number_format(count($logs)) ?></span>
    </span>
    <div class="lrf-pager-btns">
      <?php
      $qBase = http_build_query([
          'date_mode' => $dateMode,
          'tgl_awal'  => $tglAwalRaw ?: $tglAwal,
          'tgl_akhir' => $tglAkhirRaw ?: $tglAkhir,
          'filter'    => $filterJenis,
          'status'    => $filterStatus,
          'uid'       => $filterUid,
          'alat_id'   => $filterAlat,
          'q'         => $search,
          'limit'     => $perPage,
      ]);
      $base  = base_url('log-rfid') . '?' . $qBase . '&page=';
      $start = max(1, $page - 2);
      $end   = min($totalPage, $page + 2);

      if ($page > 1): ?>
        <a href="<?= $base.($page-1) ?>" class="lrf-pg" title="Sebelumnya">
          <i class="bi bi-chevron-left"></i>
        </a>
      <?php endif;
      for ($i = $start; $i <= $end; $i++): ?>
        <a href="<?= $base.$i ?>" class="lrf-pg <?= $i===$page?'act':'' ?>"><?= $i ?></a>
      <?php endfor;
      if ($page < $totalPage): ?>
        <a href="<?= $base.($page+1) ?>" class="lrf-pg" title="Berikutnya">
          <i class="bi bi-chevron-right"></i>
        </a>
      <?php endif; ?>
    </div>
  </div>

  <?php endif; ?>
</div>

<!-- ── LEGENDA STATUS ── -->
<div class="lrf-legend">
  <?php
  $legends = [
    ['lrf-b-sesuai','bi-check-circle', 'Sesuai Jadwal',   'Tap dalam jam shift yang benar & rute valid'],
    ['lrf-b-luar',  'bi-calendar-x',   'Di Luar Jadwal',  'Tap di luar rentang waktu shift terdaftar'],
    ['lrf-b-tidak', 'bi-x-circle',     'Tidak Sesuai',    'Tidak memenuhi kriteria evaluasi LCS patroli'],
    ['lrf-b-lambat','bi-alarm',        'Terlambat',       'Tap melewati batas toleransi waktu shift'],
    ['lrf-b-asing', 'bi-person-slash', 'Kartu Asing',     'UID tidak terdaftar — akses ditolak sistem'],
  ];
  foreach ($legends as [$bc,$ic,$lbl,$desc]): ?>
  <div class="lrf-legend-item">
    <span class="lrf-badge <?= $bc ?>" style="flex-shrink:0">
      <i class="bi <?= $ic ?>"></i> <?= $lbl ?>
    </span>
    <span class="lrf-legend-desc"><?= $desc ?></span>
  </div>
  <?php endforeach; ?>
</div>

</div><!-- /.lrf -->

<script>
(function () {
  /* ── Date mode switcher ── */
  function setDateMode(mode) {
    document.getElementById('hidDateMode').value = mode;

    // update tab styling
    document.querySelectorAll('.lrf-date-tab').forEach(function(btn) {
      btn.classList.toggle('active', btn.dataset.mode === mode);
    });

    var panel = document.getElementById('customPanel');
    if (mode === 'custom') {
      panel.classList.add('show');
    } else {
      panel.classList.remove('show');
      // kosongkan input custom agar tidak dikirim
      document.getElementById('inputTglAwal').value  = '';
      document.getElementById('inputTglAkhir').value = '';
    }
  }

  // Expose ke onclick global
  window.setDateMode = setDateMode;

  /* ── Validasi custom date sebelum submit ── */
  document.getElementById('lrfForm').addEventListener('submit', function(e) {
    var mode = document.getElementById('hidDateMode').value;
    if (mode === 'custom') {
      var a = document.getElementById('inputTglAwal').value;
      var b = document.getElementById('inputTglAkhir').value;
      if (!a || !b) {
        e.preventDefault();
        alert('Harap isi Tanggal Mulai dan Tanggal Selesai untuk periode Kustom.');
        return;
      }
      if (a > b) {
        e.preventDefault();
        alert('Tanggal Mulai tidak boleh melebihi Tanggal Selesai.');
        return;
      }
    }
  });

  /* ── Live polling setiap 30 detik ── */
  var latestOnLoad = '<?= addslashes($latestWaktu ?? '') ?>';

  setInterval(function () {
    fetch('<?= base_url('log-rfid/live') ?>', {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function (r) { return r.json(); })
    .then(function (d) {
      if (!d.ok || !d.latest_waktu) return;
      if (latestOnLoad === '' || d.latest_waktu > latestOnLoad) {
        window.location.reload();
      }
    })
    .catch(function () {});
  }, 30000);
}());
</script>

<?= $this->endSection() ?>