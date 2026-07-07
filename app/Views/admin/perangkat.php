<?= $this->extend('layouts/adminkit_template') ?>
<?= $this->section('content') ?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap');

    :root {
        --blue-950: #0a1628;
        --blue-900: #0d1f3c;
        --blue-800: #1a3560;
        --blue-700: #1e4080;
        --blue-600: #2563eb;
        --blue-500: #3b82f6;
        --blue-400: #60a5fa;
        --blue-100: #dbeafe;
        --blue-50:  #eff6ff;
        --white:    #ffffff;
        --slate-50: #f8fafc;
        --slate-100:#f1f5f9;
        --slate-200:#e2e8f0;
        --slate-300:#cbd5e1;
        --slate-400:#94a3b8;
        --slate-600:#475569;
        --slate-700:#334155;
        --slate-800:#1e293b;
        --green-500:#22c55e;
        --green-100:#dcfce7;
        --green-700:#15803d;
        --amber-500:#f59e0b;
        --amber-100:#fef3c7;
        --amber-800:#92400e;
        --red-500:  #ef4444;
        --red-100:  #fee2e2;
        --red-700:  #b91c1c;
        --radius-sm: 8px;
        --radius:    12px;
        --radius-lg: 16px;
        --shadow-sm: 0 1px 3px rgba(0,0,0,.08);
        --shadow-md: 0 4px 16px rgba(0,0,0,.10);
        --shadow-lg: 0 12px 40px rgba(0,0,0,.14);
        --font: 'Plus Jakarta Sans', sans-serif;
        --mono: 'JetBrains Mono', monospace;
    }

    * { box-sizing: border-box; }
    body, .content-wrapper { font-family: var(--font) !important; }

    /* ── PAGE HEADER ── */
    .page-header {
        background: linear-gradient(135deg, var(--blue-950) 0%, var(--blue-800) 60%, var(--blue-600) 100%);
        border-radius: var(--radius-lg);
        padding: 20px 28px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-lg);
    }
    .page-header::before {
        content: '';
        position: absolute; inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        pointer-events: none;
    }
    .page-header::after {
        content: '';
        position: absolute; right: -50px; top: -50px;
        width: 200px; height: 200px;
        background: radial-gradient(circle, rgba(255,255,255,.07) 0%, transparent 70%);
        pointer-events: none;
    }
    .ph-left { display: flex; align-items: center; gap: 14px; position: relative; z-index: 1; }
    .ph-icon {
        width: 44px; height: 44px;
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.2);
        border-radius: var(--radius);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem; color: #fff;
        flex-shrink: 0;
    }
    .ph-text h3  { font-size: 1.1rem; font-weight: 800; color: #fff; margin: 0 0 2px; letter-spacing: -.01em; }
    .ph-text p   { font-size: .78rem; color: rgba(255,255,255,.6); margin: 0; }
    .ph-right { position: relative; z-index: 1; }
    .btn-tambah {
        background: rgba(255,255,255,.95);
        color: var(--blue-800);
        border: none; border-radius: var(--radius);
        padding: 10px 20px;
        font-size: .83rem; font-weight: 700;
        font-family: var(--font);
        display: inline-flex; align-items: center; gap: 7px;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0,0,0,.18);
        transition: all .2s;
        white-space: nowrap;
    }
    .btn-tambah:hover { background: #fff; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(0,0,0,.22); }

    /* ── STAT ROW ── */
    .stat-row { display: grid; grid-template-columns: repeat(4,1fr); gap: 12px; margin-bottom: 20px; }
    @media (max-width: 900px) { .stat-row { grid-template-columns: repeat(2,1fr); } }
    @media (max-width: 480px) { .stat-row { grid-template-columns: 1fr; } }

    .stat-card {
        background: #fff;
        border-radius: var(--radius);
        border: 1px solid var(--slate-200);
        padding: 14px 18px;
        display: flex; align-items: center; gap: 12px;
        box-shadow: var(--shadow-sm);
        position: relative; overflow: hidden;
        transition: box-shadow .2s, transform .2s;
    }
    .stat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .stat-card::after {
        content: ''; position: absolute; right: 0; top: 0; bottom: 0;
        width: 3px; border-radius: 0 var(--radius) var(--radius) 0;
    }
    .sc-total::after  { background: var(--blue-600); }
    .sc-online::after { background: var(--green-500); }
    .sc-offline::after{ background: var(--slate-400); }
    .sc-maint::after  { background: var(--amber-500); }

    .stat-icon { width: 40px; height: 40px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .sc-total  .stat-icon { background: var(--blue-50);   color: var(--blue-600); }
    .sc-online .stat-icon { background: var(--green-100); color: var(--green-700); }
    .sc-offline .stat-icon{ background: var(--slate-100); color: var(--slate-400); }
    .sc-maint  .stat-icon { background: var(--amber-100); color: var(--amber-800); }

    .stat-body .s-label { font-size: .68rem; font-weight: 700; color: var(--slate-400); text-transform: uppercase; letter-spacing: .05em; }
    .stat-body .s-value { font-size: 1.45rem; font-weight: 800; color: var(--slate-800); line-height: 1.1; }
    .stat-body .s-sub   { font-size: .7rem; color: var(--slate-400); margin-top: 1px; }

    /* ── CARD PANEL ── */
    .card-panel { background: #fff; border-radius: var(--radius-lg); border: 1px solid var(--slate-200); box-shadow: var(--shadow-md); overflow: hidden; margin-bottom: 20px; }
    .card-panel-head {
        padding: 14px 20px;
        background: linear-gradient(135deg, var(--blue-950) 0%, var(--blue-800) 100%);
        display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;
    }
    .card-panel-head h5 { margin: 0; font-weight: 700; color: #fff; font-size: .9rem; display: flex; align-items: center; gap: 7px; }

    .toolbar { display: flex; align-items: center; gap: 7px; flex-wrap: wrap; }

    .search-box { position: relative; }
    .search-box input {
        padding: 7px 11px 7px 32px;
        border-radius: var(--radius-sm);
        border: 1px solid rgba(255,255,255,.25);
        background: rgba(255,255,255,.12);
        color: #fff; font-size: .8rem; font-family: var(--font);
        width: 210px; transition: all .2s;
    }
    .search-box input::placeholder { color: rgba(255,255,255,.45); }
    .search-box input:focus { outline: none; background: rgba(255,255,255,.2); border-color: rgba(255,255,255,.5); }
    .search-box i { position: absolute; left: 9px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,.55); font-size: .78rem; pointer-events: none; }

    .filter-sel {
        padding: 7px 26px 7px 10px;
        border-radius: var(--radius-sm);
        border: 1px solid rgba(255,255,255,.25);
        background: rgba(255,255,255,.12);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='white'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 7px center;
        color: #fff; font-size: .8rem; font-family: var(--font);
        cursor: pointer; appearance: none; -webkit-appearance: none;
        transition: all .2s;
    }
    .filter-sel option { color: var(--slate-800); background: #fff; }
    .filter-sel:focus { outline: none; background-color: rgba(255,255,255,.2); border-color: rgba(255,255,255,.5); }

    .per-page-sel {
        padding: 7px 22px 7px 10px; border-radius: var(--radius-sm);
        border: 1px solid rgba(255,255,255,.25);
        background: rgba(255,255,255,.12);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='white'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 6px center;
        color: #fff; font-size: .78rem; font-family: var(--font);
        cursor: pointer; appearance: none; -webkit-appearance: none;
    }
    .per-page-sel option { color: var(--slate-800); background: #fff; }
    .per-page-sel:focus { outline: none; }

    /* ── TABLE ── */
    .table-wrap { overflow-x: auto; }
    table.dt { width: 100%; border-collapse: collapse; }
    table.dt thead th {
        padding: 10px 14px;
        background: var(--slate-50);
        border-bottom: 2px solid var(--slate-200);
        color: var(--slate-600);
        font-size: .68rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .07em;
        white-space: nowrap; cursor: pointer; user-select: none;
    }
    table.dt thead th:hover { background: var(--slate-100); }
    table.dt thead th.sort-asc::after  { content: ' ▲'; color: var(--blue-500); }
    table.dt thead th.sort-desc::after { content: ' ▼'; color: var(--blue-500); }
    table.dt tbody td {
        padding: 11px 14px; vertical-align: middle;
        border-bottom: 1px solid var(--slate-100);
        font-size: .82rem; color: var(--slate-800);
    }
    table.dt tbody tr:last-child td { border-bottom: none; }
    table.dt tbody tr:hover td { background: #f0f6ff; }

    /* badges */
    .badge-status { display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: 20px; font-size: .7rem; font-weight: 700; white-space: nowrap; }
    .bs-online  { background: var(--green-100); color: var(--green-700); }
    .bs-offline { background: var(--slate-100); color: var(--slate-400); }
    .bs-maint   { background: var(--amber-100); color: var(--amber-800); }
    .status-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
    .sd-on  { background: var(--green-500); animation: pOn 2s infinite; }
    .sd-off { background: var(--slate-400); }
    .sd-mnt { background: var(--amber-500); animation: pOn 2s infinite; }
    @keyframes pOn { 0%,100%{ box-shadow: 0 0 0 3px rgba(34,197,94,.2); } 50%{ box-shadow: 0 0 0 6px rgba(34,197,94,0); } }

    .badge-tipe { font-family: var(--mono); font-size: .67rem; font-weight: 600; background: #ede9fe; color: #5b21b6; padding: 2px 7px; border-radius: 5px; }
    .badge-feat { font-size: .67rem; font-weight: 600; padding: 2px 7px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px; }
    .feat-on  { background: var(--green-100); color: var(--green-700); }
    .feat-off { background: var(--slate-100); color: var(--slate-400); }

    .ip-text  { font-family: var(--mono); font-size: .78rem; color: var(--slate-600); }
    .dev-code { font-family: var(--mono); font-size: .83rem; font-weight: 600; color: var(--blue-700); }
    .dev-id   { font-size: .68rem; color: var(--slate-400); }

    /* action buttons */
    .act-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 28px; height: 28px; border-radius: 6px;
        border: none; cursor: pointer; font-size: .8rem;
        transition: all .15s; text-decoration: none;
    }
    .ab-info   { background: var(--blue-50);   color: var(--blue-600); }
    .ab-info:hover   { background: var(--blue-100); }
    .ab-toggle { background: var(--slate-100); color: var(--slate-500); border: 1px solid var(--slate-200); }
    .ab-toggle:hover { background: var(--slate-200); }
    .ab-edit   { background: var(--amber-100); color: var(--amber-800); border: 1px solid #fde68a; }
    .ab-edit:hover   { background: #fde68a; }
    .ab-del    { background: var(--red-100);   color: var(--red-700);   border: 1px solid #fecaca; }
    .ab-del:hover    { background: #fecaca; }

    /* flash */
    .flash-msg { border-radius: 10px; padding: 12px 16px; display: flex; align-items: center; gap: 9px; font-size: .85rem; margin-bottom: 14px; animation: fadeIn .3s ease; }
    .flash-ok  { background: var(--green-100); color: var(--green-700); border: 1px solid #bbf7d0; }
    .flash-err { background: var(--red-100);   color: var(--red-700);   border: 1px solid #fecaca; }
    @keyframes fadeIn { from{opacity:0;transform:translateY(-5px)} to{opacity:1;transform:none} }

    /* empty */
    .empty-box { padding: 56px 20px; text-align: center; color: var(--slate-400); }
    .empty-box i { font-size: 2.8rem; opacity: .25; display: block; margin-bottom: 10px; }

    /* pagination */
    .pag-wrap { display: flex; align-items: center; justify-content: space-between; padding: 12px 18px; border-top: 1px solid var(--slate-100); flex-wrap: wrap; gap: 8px; }
    .pag-info { font-size: .76rem; color: var(--slate-400); }
    .pag-btns { display: flex; gap: 4px; }
    .pag-btn {
        width: 28px; height: 28px; border-radius: 6px; border: 1px solid var(--slate-200);
        background: #fff; color: var(--slate-600); font-size: .76rem; font-family: var(--font);
        cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .15s;
    }
    .pag-btn:hover  { background: var(--blue-50); border-color: var(--blue-200); color: var(--blue-600); }
    .pag-btn.active { background: var(--blue-600); border-color: var(--blue-600); color: #fff; font-weight: 700; }
    .pag-btn:disabled { opacity: .3; pointer-events: none; }

    /* modal */
    .modal-content { border-radius: var(--radius-lg); border: none; box-shadow: 0 24px 80px rgba(0,0,0,.22); overflow: hidden; }
    .modal-header  { background: linear-gradient(135deg, var(--blue-950), var(--blue-800)); border-bottom: none; padding: 18px 22px; }
    .modal-header .modal-title { color: #fff; font-weight: 700; font-size: .9rem; display: flex; align-items: center; gap: 7px; }
    .modal-header .btn-close    { filter: invert(1) brightness(2); opacity: .7; }
    .modal-body   { padding: 22px; background: var(--white); }
    .modal-footer { border-top: 1px solid var(--slate-100); padding: 14px 22px; background: var(--slate-50); }

    .form-label { font-size: .76rem; font-weight: 700; color: var(--slate-600); margin-bottom: 4px; }
    .form-control, .form-select {
        border-radius: var(--radius-sm); border: 1.5px solid var(--slate-200);
        font-size: .84rem; font-family: var(--font); padding: 8px 12px;
        color: var(--slate-800); transition: border-color .2s, box-shadow .2s;
        background: var(--white);
    }
    .form-control:focus, .form-select:focus { border-color: var(--blue-600); box-shadow: 0 0 0 3px rgba(37,99,235,.10); outline: none; }
    .input-mono { font-family: var(--mono) !important; }

    /* feature toggle */
    .add-ruangan-box {
        background: var(--slate-50);
        border: 1px dashed var(--slate-300);
        border-radius: var(--radius-sm);
        padding: 10px;
        margin-top: 8px;
    }
    .ruangan-row { display: flex; gap: 8px; }
    .ruangan-row .form-select { flex: 1; }
    .ruangan-row .btn-add-ruangan {
        flex-shrink: 0;
        width: 38px;
        display: flex; align-items: center; justify-content: center;
        border-radius: var(--radius-sm);
    }
    .feat-row { display: flex; gap: 8px; flex-wrap: wrap; }
    .feat-lbl {
        flex: 1; min-width: 120px;
        display: flex; align-items: center; gap: 7px;
        padding: 10px 13px;
        border: 1.5px solid var(--slate-200);
        border-radius: var(--radius-sm);
        background: var(--slate-50);
        cursor: pointer; transition: all .15s; user-select: none;
    }
    .feat-lbl:has(input:checked) { border-color: var(--blue-600); background: var(--blue-50); }
    .feat-lbl input { display: none; }
    .feat-lbl .fl-text  { font-size: .82rem; font-weight: 600; color: var(--slate-600); }
    .feat-lbl:has(input:checked) .fl-text { color: var(--blue-700); }

    /* detail grid */
    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    @media (max-width: 500px) { .detail-grid { grid-template-columns: 1fr; } }
    .detail-item { padding: 11px 13px; background: var(--slate-50); border-radius: var(--radius-sm); border: 1px solid var(--slate-200); }
    .detail-item .di-label { font-size: .66rem; font-weight: 700; color: var(--slate-400); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 3px; }
    .detail-item .di-value { font-size: .86rem; font-weight: 600; color: var(--slate-800); }

    /* spinner */
    .spin { animation: spin .7s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>

<!-- ═══════════ PAGE HEADER ═══════════ -->
<div class="page-header">
    <div class="ph-left">
        <div class="ph-icon"><i class="bi bi-hdd-network-fill"></i></div>
        <div class="ph-text">
            <h3>Manajemen Perangkat</h3>
            <p>Monitor & kelola perangkat RFID ESP32</p>
        </div>
    </div>
    <div class="ph-right">
        <button class="btn-tambah" id="btnTambah">
            <i class="bi bi-plus-circle-fill"></i> Tambah Perangkat
        </button>
    </div>
</div>

<!-- ═══════════ FLASH ═══════════ -->
<?php if (session()->getFlashdata('success')): ?>
<div class="flash-msg flash-ok"><i class="bi bi-check-circle-fill"></i><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
<div class="flash-msg flash-err"><i class="bi bi-exclamation-circle-fill"></i><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<!-- ═══════════ STAT CHIPS ═══════════ -->
<?php
    $total       = count($perangkat);
    $online      = count(array_filter($perangkat, fn($p) => $p['status_perangkat'] === 'Online'));
    $offline     = count(array_filter($perangkat, fn($p) => $p['status_perangkat'] === 'Offline'));
    $maintenance = count(array_filter($perangkat, fn($p) => $p['status_perangkat'] === 'Maintenance'));
    $pctOnline   = $total ? round($online / $total * 100) : 0;
?>
<div class="stat-row">
    <div class="stat-card sc-total">
        <div class="stat-icon"><i class="bi bi-hdd-stack-fill"></i></div>
        <div class="stat-body">
            <div class="s-label">Total Perangkat</div>
            <div class="s-value"><?= $total ?></div>
            <div class="s-sub"><?= $pctOnline ?>% aktif</div>
        </div>
    </div>
    <div class="stat-card sc-online">
        <div class="stat-icon"><i class="bi bi-wifi"></i></div>
        <div class="stat-body">
            <div class="s-label">Online</div>
            <div class="s-value"><?= $online ?></div>
            <div class="s-sub">Terhubung & aktif</div>
        </div>
    </div>
    <div class="stat-card sc-offline">
        <div class="stat-icon"><i class="bi bi-wifi-off"></i></div>
        <div class="stat-body">
            <div class="s-label">Offline</div>
            <div class="s-value"><?= $offline ?></div>
            <div class="s-sub">Tidak terhubung</div>
        </div>
    </div>
    <div class="stat-card sc-maint">
        <div class="stat-icon"><i class="bi bi-tools"></i></div>
        <div class="stat-body">
            <div class="s-label">Maintenance</div>
            <div class="s-value"><?= $maintenance ?></div>
            <div class="s-sub">Sedang diservis</div>
        </div>
    </div>
</div>

<!-- ═══════════ TABLE CARD ═══════════ -->
<div class="card-panel">
    <div class="card-panel-head">
        <h5><i class="bi bi-table"></i>Data Perangkat RFID</h5>
        <div class="toolbar">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" placeholder="Cari kode, lokasi, IP…">
            </div>
            <select class="filter-sel" id="filterStatus">
                <option value="">Semua Status</option>
                <option value="Online">Online</option>
                <option value="Offline">Offline</option>
                <option value="Maintenance">Maintenance</option>
            </select>
            <select class="filter-sel" id="filterTipe">
                <option value="">Semua Tipe</option>
                <option value="ESP32_30PIN">30-PIN</option>
                <option value="ESP32_38PIN">38-PIN</option>
            </select>
            <select class="per-page-sel" id="perPageSel">
                <option value="10">10/hal</option>
                <option value="25">25/hal</option>
                <option value="50">50/hal</option>
            </select>
        </div>
    </div>

    <div class="table-wrap">
        <table class="dt" id="mainTable">
            <thead>
                <tr>
                    <th data-col="0">#</th>
                    <th data-col="1">Kode Perangkat</th>
                    <th data-col="2">Ruangan / Lokasi</th>
                    <th data-col="3">Tipe</th>
                    <th data-col="4">IP Address</th>
                    <th data-col="5">Fitur</th>
                    <th data-col="6">Status</th>
                    <th data-col="7">Last Online</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody">
            <?php if (!empty($perangkat)): ?>
                <?php foreach ($perangkat as $p):
                    $st = $p['status_perangkat'];
                    [$bCls, $dCls] = match($st) {
                        'Online'      => ['bs-online','sd-on'],
                        'Maintenance' => ['bs-maint', 'sd-mnt'],
                        default       => ['bs-offline','sd-off'],
                    };
                ?>
                <tr data-status="<?= esc($st) ?>"
                    data-tipe="<?= esc($p['tipe_perangkat']) ?>"
                    data-search="<?= strtolower(esc($p['kode_perangkat'].' '.$p['ip_address'].' '.($p['nama_ruangan']??''))) ?>">
                    <td class="text-muted fw-semibold row-num"></td>
                    <td>
                        <div class="dev-code"><?= esc($p['kode_perangkat']) ?></div>
                        <div class="dev-id">ID: <?= $p['alat_id'] ?></div>
                    </td>
                    <td>
                        <?php if (!empty($p['nama_ruangan'])): ?>
                            <div class="fw-semibold" style="font-size:.83rem"><?= esc($p['nama_ruangan']) ?></div>
                            <div style="font-size:.7rem;color:var(--slate-400)"><?= esc($p['lokasi']??'') ?></div>
                        <?php else: ?><span style="color:var(--slate-300)">—</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge-tipe"><?= esc($p['tipe_perangkat']) ?></span></td>
                    <td>
                        <?php if (!empty($p['ip_address'])): ?>
                            <span class="ip-text"><i class="bi bi-ethernet me-1" style="color:var(--slate-400)"></i><?= esc($p['ip_address']) ?></span>
                        <?php else: ?><span style="color:var(--slate-300)">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:3px;flex-wrap:wrap">
                            <span class="badge-feat <?= $p['fitur_lcd']    ? 'feat-on':'feat-off' ?>"><i class="bi bi-display"></i>LCD</span>
                            <span class="badge-feat <?= $p['fitur_buzzer'] ? 'feat-on':'feat-off' ?>"><i class="bi bi-volume-up"></i>Buzzer</span>
                            <span class="badge-feat <?= ($p['fitur_rfid']??1) ? 'feat-on':'feat-off' ?>"><i class="bi bi-credit-card"></i>RFID</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge-status <?= $bCls ?>">
                            <span class="status-dot <?= $dCls ?>"></span><?= $st ?>
                        </span>
                    </td>
                    <td style="font-size:.76rem;color:var(--slate-400)">
                        <?= !empty($p['last_online']) ? date('d M Y H:i', strtotime($p['last_online'])) : '—' ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:3px">
                            <button class="act-btn ab-info" title="Detail"
                                onclick='openDetail(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)'>
                                <i class="bi bi-info-circle"></i>
                            </button>
                            <button class="act-btn ab-toggle" title="Toggle Status"
                                onclick="doToggle(<?= $p['alat_id'] ?>, this)">
                                <i class="bi bi-toggle-<?= $st==='Online' ? 'on text-success':'off' ?>"></i>
                            </button>
                            <button class="act-btn ab-edit" title="Edit"
                                onclick='openEdit(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)'>
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <a class="act-btn ab-del" title="Hapus"
                                href="<?= base_url('perangkat/delete/'.$p['alat_id']) ?>"
                                onclick="return confirm('Yakin hapus <?= esc($p['kode_perangkat']) ?>?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="9">
                    <div class="empty-box">
                        <i class="bi bi-hdd-network"></i>
                        <div class="fw-bold mb-1">Belum ada perangkat</div>
                        <div style="font-size:.8rem">Klik "Tambah Perangkat" untuk mendaftarkan ESP32 pertama</div>
                    </div>
                </td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pag-wrap">
        <div class="pag-info" id="pagInfo">Menampilkan 0–0 dari 0 data</div>
        <div class="pag-btns" id="pagBtns"></div>
    </div>
</div>


<!-- ═══════════ MODAL TAMBAH ═══════════ -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle-fill"></i>Tambah Perangkat Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('perangkat/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Kode Perangkat <span class="text-danger">*</span></label>
                            <input type="text" name="kode_perangkat" class="form-control input-mono" placeholder="DEVICE_07" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ruangan <span class="text-danger">*</span></label>
                            <div class="ruangan-row">
                                <select name="ruangan_id" id="t_ruangan" class="form-select" required>
                                    <option value="">— Pilih Ruangan —</option>
                                    <?php foreach ($ruangan as $r): ?>
                                        <option value="<?= $r['ruangan_id'] ?>"><?= esc($r['nama_ruangan']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-outline-primary btn-add-ruangan" title="Tambah ruangan baru" onclick="toggleAddRuangan('t')">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                            <div id="t_ruangan_box" class="add-ruangan-box" style="display:none">
                                <input type="text" id="t_ruangan_nama" class="form-control form-control-sm" placeholder="Nama ruangan baru, mis. Titik 13">
                                <input type="text" id="t_ruangan_lokasi" class="form-control form-control-sm mt-2" placeholder="Lokasi (opsional)">
                                <div class="mt-2 d-flex gap-2">
                                    <button type="button" class="btn btn-success btn-sm" onclick="simpanRuanganBaru('t')"><i class="bi bi-check-lg me-1"></i>Simpan Ruangan</button>
                                    <button type="button" class="btn btn-link btn-sm" onclick="toggleAddRuangan('t')">Batal</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username Login</label>
                            <input type="text" name="username_login" class="form-control" placeholder="username perangkat">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password Login</label>
                            <div class="input-group">
                                <input type="password" name="password_login" id="t_pass" class="form-control" placeholder="Password">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePass('t_pass',this)"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">IP Address</label>
                            <input type="text" name="ip_address" class="form-control input-mono" placeholder="192.168.4.xxx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipe Perangkat</label>
                            <select name="tipe_perangkat" class="form-select">
                                <option value="ESP32_30PIN">ESP32 30-PIN</option>
                                <option value="ESP32_38PIN">ESP32 38-PIN</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status Awal</label>
                            <select name="status_perangkat" class="form-select">
                                <option value="Offline" selected>Offline</option>
                                <option value="Online">Online</option>
                                <option value="Maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Fitur Perangkat</label>
                            <div class="feat-row">
                                <label class="feat-lbl">
                                    <input type="checkbox" name="fitur_lcd" value="1">
                                    <i class="bi bi-display text-primary"></i>
                                    <span class="fl-text">LCD Display</span>
                                </label>
                                <label class="feat-lbl">
                                    <input type="checkbox" name="fitur_buzzer" value="1" checked>
                                    <i class="bi bi-volume-up text-warning"></i>
                                    <span class="fl-text">Buzzer</span>
                                </label>
                                <label class="feat-lbl">
                                    <input type="checkbox" name="fitur_rfid" value="1" checked>
                                    <i class="bi bi-credit-card text-success"></i>
                                    <span class="fl-text">RFID Reader</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4"><i class="bi bi-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ═══════════ MODAL EDIT ═══════════ -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" id="formEdit">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square"></i>Edit Perangkat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Kode Perangkat <span class="text-danger">*</span></label>
                            <input type="text" name="kode_perangkat" id="e_kode" class="form-control input-mono" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ruangan <span class="text-danger">*</span></label>
                            <div class="ruangan-row">
                                <select name="ruangan_id" id="e_ruangan" class="form-select" required>
                                    <option value="">— Pilih Ruangan —</option>
                                    <?php foreach ($ruangan as $r): ?>
                                        <option value="<?= $r['ruangan_id'] ?>"><?= esc($r['nama_ruangan']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-outline-primary btn-add-ruangan" title="Tambah ruangan baru" onclick="toggleAddRuangan('e')">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                            <div id="e_ruangan_box" class="add-ruangan-box" style="display:none">
                                <input type="text" id="e_ruangan_nama" class="form-control form-control-sm" placeholder="Nama ruangan baru, mis. Titik 13">
                                <input type="text" id="e_ruangan_lokasi" class="form-control form-control-sm mt-2" placeholder="Lokasi (opsional)">
                                <div class="mt-2 d-flex gap-2">
                                    <button type="button" class="btn btn-success btn-sm" onclick="simpanRuanganBaru('e')"><i class="bi bi-check-lg me-1"></i>Simpan Ruangan</button>
                                    <button type="button" class="btn btn-link btn-sm" onclick="toggleAddRuangan('e')">Batal</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username Login</label>
                            <input type="text" name="username_login" id="e_username" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password Baru <small class="fw-normal text-muted">(kosongkan jika tidak diubah)</small></label>
                            <div class="input-group">
                                <input type="password" name="password_login" id="e_pass" class="form-control" placeholder="Password baru…">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePass('e_pass',this)"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">IP Address</label>
                            <input type="text" name="ip_address" id="e_ip" class="form-control input-mono">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipe Perangkat</label>
                            <select name="tipe_perangkat" id="e_tipe" class="form-select">
                                <option value="ESP32_30PIN">ESP32 30-PIN</option>
                                <option value="ESP32_38PIN">ESP32 38-PIN</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status_perangkat" id="e_status" class="form-select">
                                <option value="Online">Online</option>
                                <option value="Offline">Offline</option>
                                <option value="Maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Fitur Perangkat</label>
                            <div class="feat-row">
                                <label class="feat-lbl">
                                    <input type="checkbox" name="fitur_lcd" id="e_lcd" value="1">
                                    <i class="bi bi-display text-primary"></i>
                                    <span class="fl-text">LCD Display</span>
                                </label>
                                <label class="feat-lbl">
                                    <input type="checkbox" name="fitur_buzzer" id="e_buzzer" value="1">
                                    <i class="bi bi-volume-up text-warning"></i>
                                    <span class="fl-text">Buzzer</span>
                                </label>
                                <label class="feat-lbl">
                                    <input type="checkbox" name="fitur_rfid" id="e_rfid" value="1">
                                    <i class="bi bi-credit-card text-success"></i>
                                    <span class="fl-text">RFID Reader</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm px-4"><i class="bi bi-save me-1"></i>Update</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ═══════════ MODAL DETAIL ═══════════ -->
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-info-circle-fill"></i>Detail Perangkat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="detail-grid" id="detailGrid"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


<!-- ═══════════ SCRIPTS ═══════════ -->
<script>
(function () {
    'use strict';

    /* ── Table engine ── */
    var allRows = [], filteredRows = [];
    var sortCol = -1, sortDir = 1, page = 1, perPage = 10;

    function initTable() {
        allRows = Array.from(document.querySelectorAll('#tableBody tr[data-status]'));
        applyFilters();
    }

    function applyFilters() {
        var q  = document.getElementById('searchInput').value.toLowerCase();
        var st = document.getElementById('filterStatus').value;
        var tp = document.getElementById('filterTipe').value;
        filteredRows = allRows.filter(function(r) {
            if (q  && !r.dataset.search.includes(q)) return false;
            if (st && r.dataset.status !== st)        return false;
            if (tp && r.dataset.tipe   !== tp)        return false;
            return true;
        });
        page = 1;
        renderTable();
    }

    function renderTable() {
        var body  = document.getElementById('tableBody');
        var start = (page - 1) * perPage;
        var slice = filteredRows.slice(start, start + perPage);

        allRows.forEach(function(r) { r.style.display = 'none'; });
        var no = start + 1;
        slice.forEach(function(r) {
            r.style.display = '';
            var el = r.querySelector('.row-num');
            if (el) el.textContent = no++;
        });

        var empty = body.querySelector('.empty-placeholder');
        if (slice.length === 0) {
            if (!empty) {
                empty = document.createElement('tr');
                empty.className = 'empty-placeholder';
                empty.innerHTML = '<td colspan="9"><div class="empty-box"><i class="bi bi-search"></i><div class="fw-bold">Tidak ada data</div></div></td>';
                body.appendChild(empty);
            }
        } else {
            if (empty) empty.remove();
        }
        updatePag();
    }

    function updatePag() {
        var total = filteredRows.length;
        var pages = Math.max(1, Math.ceil(total / perPage));
        var start = total === 0 ? 0 : (page - 1) * perPage + 1;
        var end   = Math.min(page * perPage, total);
        document.getElementById('pagInfo').textContent = 'Menampilkan ' + start + '–' + end + ' dari ' + total + ' data';

        var wrap = document.getElementById('pagBtns');
        wrap.innerHTML = '';

        function mkBtn(label, pg, dis, active) {
            var b = document.createElement('button');
            b.className = 'pag-btn' + (active ? ' active' : '');
            b.innerHTML = label; b.disabled = !!dis;
            b.onclick = function() { page = pg; renderTable(); };
            wrap.appendChild(b);
        }

        mkBtn('<i class="bi bi-chevron-left"></i>', page - 1, page === 1, false);
        var maxShow = 5, half = Math.floor(maxShow / 2);
        var lo = Math.max(1, page - half), hi = Math.min(pages, lo + maxShow - 1);
        if (hi - lo < maxShow - 1) lo = Math.max(1, hi - maxShow + 1);
        if (lo > 1) { mkBtn('1', 1, false, false); if (lo > 2) mkBtn('…', lo - 1, false, false); }
        for (var i = lo; i <= hi; i++) mkBtn(i, i, false, i === page);
        if (hi < pages) { if (hi < pages - 1) mkBtn('…', hi + 1, false, false); mkBtn(pages, pages, false, false); }
        mkBtn('<i class="bi bi-chevron-right"></i>', page + 1, page === pages, false);
    }

    /* sort */
    document.querySelectorAll('#mainTable thead th[data-col]').forEach(function(th) {
        th.addEventListener('click', function() {
            var col = parseInt(th.dataset.col);
            if (sortCol === col) sortDir *= -1; else { sortCol = col; sortDir = 1; }
            document.querySelectorAll('#mainTable thead th').forEach(function(t) { t.classList.remove('sort-asc','sort-desc'); });
            th.classList.add(sortDir === 1 ? 'sort-asc' : 'sort-desc');
            filteredRows.sort(function(a, b) {
                var ca = (a.cells[col] ? a.cells[col].textContent.trim().toLowerCase() : '');
                var cb = (b.cells[col] ? b.cells[col].textContent.trim().toLowerCase() : '');
                return ca < cb ? -sortDir : ca > cb ? sortDir : 0;
            });
            page = 1; renderTable();
        });
    });

    document.getElementById('searchInput').addEventListener('input', applyFilters);
    document.getElementById('filterStatus').addEventListener('change', applyFilters);
    document.getElementById('filterTipe').addEventListener('change', applyFilters);
    document.getElementById('perPageSel').addEventListener('change', function() {
        perPage = parseInt(this.value); page = 1; renderTable();
    });

    initTable();

    /* ── Modals ── */
    var mTambah = null, mEdit = null, mDetail = null;
    function getMTambah() { return mTambah || (mTambah = new bootstrap.Modal(document.getElementById('modalTambah'))); }
    function getMEdit()   { return mEdit   || (mEdit   = new bootstrap.Modal(document.getElementById('modalEdit'))); }
    function getMDetail() { return mDetail || (mDetail = new bootstrap.Modal(document.getElementById('modalDetail'))); }

    document.getElementById('btnTambah').addEventListener('click', function() { getMTambah().show(); });

    window.openEdit = function(d) {
        document.getElementById('e_kode').value     = d.kode_perangkat  || '';
        document.getElementById('e_ruangan').value  = d.ruangan_id      || '';
        document.getElementById('e_username').value = d.username_login   || '';
        document.getElementById('e_ip').value       = d.ip_address       || '';
        document.getElementById('e_tipe').value     = d.tipe_perangkat   || 'ESP32_30PIN';
        document.getElementById('e_status').value   = d.status_perangkat || 'Offline';
        document.getElementById('e_pass').value     = '';
        document.getElementById('e_lcd').checked    = d.fitur_lcd    == 1;
        document.getElementById('e_buzzer').checked = d.fitur_buzzer == 1;
        document.getElementById('e_rfid').checked   = d.fitur_rfid   == 1;
        document.getElementById('formEdit').action  = '<?= base_url('perangkat/update/') ?>' + d.alat_id;
        getMEdit().show();
    };

    window.openDetail = function(d) {
        var st = d.status_perangkat;
        var stB = st === 'Online'
            ? '<span class="badge-status bs-online"><span class="status-dot sd-on"></span>Online</span>'
            : st === 'Maintenance'
            ? '<span class="badge-status bs-maint"><span class="status-dot sd-mnt"></span>Maintenance</span>'
            : '<span class="badge-status bs-offline"><span class="status-dot sd-off"></span>Offline</span>';
        var fL = d.fitur_lcd    == 1 ? '<span class="badge-feat feat-on">✓ LCD</span>'    : '<span class="badge-feat feat-off">✗ LCD</span>';
        var fB = d.fitur_buzzer == 1 ? '<span class="badge-feat feat-on">✓ Buzzer</span>' : '<span class="badge-feat feat-off">✗ Buzzer</span>';
        var fR = d.fitur_rfid   == 1 ? '<span class="badge-feat feat-on">✓ RFID</span>'   : '<span class="badge-feat feat-off">✗ RFID</span>';
        document.getElementById('detailGrid').innerHTML =
            '<div class="detail-item"><div class="di-label">Kode</div><div class="di-value" style="font-family:var(--mono)">' + d.kode_perangkat + '</div></div>' +
            '<div class="detail-item"><div class="di-label">ID</div><div class="di-value">#' + d.alat_id + '</div></div>' +
            '<div class="detail-item"><div class="di-label">Tipe</div><div class="di-value"><span class="badge-tipe">' + d.tipe_perangkat + '</span></div></div>' +
            '<div class="detail-item"><div class="di-label">Status</div><div class="di-value">' + stB + '</div></div>' +
            '<div class="detail-item"><div class="di-label">IP Address</div><div class="di-value" style="font-family:var(--mono)">' + (d.ip_address||'—') + '</div></div>' +
            '<div class="detail-item"><div class="di-label">Username</div><div class="di-value">' + (d.username_login||'—') + '</div></div>' +
            '<div class="detail-item"><div class="di-label">Last Online</div><div class="di-value" style="font-size:.8rem">' + (d.last_online||'—') + '</div></div>' +
            '<div class="detail-item"><div class="di-label">Ruangan ID</div><div class="di-value">' + d.ruangan_id + '</div></div>' +
            '<div class="detail-item" style="grid-column:1/-1"><div class="di-label">Fitur Aktif</div><div class="di-value" style="display:flex;gap:5px;margin-top:3px">' + fL + fB + fR + '</div></div>' +
            '<div class="detail-item"><div class="di-label">Dibuat</div><div class="di-value" style="font-size:.78rem">' + (d.created_at||'—') + '</div></div>' +
            '<div class="detail-item"><div class="di-label">Diupdate</div><div class="di-value" style="font-size:.78rem">' + (d.updated_at||'—') + '</div></div>';
        getMDetail().show();
    };

    /* ── Toggle status ── */
    window.doToggle = function(id, btn) {
        var icon = btn.querySelector('i');
        var prev = icon.className;
        icon.className = 'bi bi-arrow-repeat spin';
        btn.disabled = true;
        fetch('<?= base_url('perangkat/toggle-status/') ?>' + id, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
            body: JSON.stringify({ '<?= csrf_token() ?>': '<?= csrf_hash() ?>' })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) { location.reload(); }
            else { icon.className = prev; btn.disabled = false; alert('Gagal toggle status'); }
        })
        .catch(function() { icon.className = 'bi bi-exclamation-triangle text-danger'; btn.disabled = false; });
    };

}());

function togglePass(id, btn) {
    var inp = document.getElementById(id);
    var ico = btn.querySelector('i');
    if (inp.type === 'password') { inp.type = 'text'; ico.className = 'bi bi-eye-slash'; }
    else { inp.type = 'password'; ico.className = 'bi bi-eye'; }
}

/* ── Tambah Ruangan Baru (inline, dari popup Tambah/Edit Perangkat) ── */
function toggleAddRuangan(prefix) {
    var box = document.getElementById(prefix + '_ruangan_box');
    if (!box) return;
    box.style.display = (box.style.display === 'none' || box.style.display === '') ? 'block' : 'none';
}

function simpanRuanganBaru(prefix) {
    var namaEl   = document.getElementById(prefix + '_ruangan_nama');
    var lokasiEl = document.getElementById(prefix + '_ruangan_lokasi');
    var nama     = namaEl.value.trim();
    var lokasi   = lokasiEl.value.trim();

    if (!nama) {
        alert('Nama ruangan wajib diisi');
        namaEl.focus();
        return;
    }

    var body = new URLSearchParams();
    body.set('nama_ruangan', nama);
    body.set('lokasi', lokasi);
    body.set('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    fetch('<?= base_url('perangkat/store-ruangan') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: body
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            ['t_ruangan', 'e_ruangan'].forEach(function(selId) {
                var sel = document.getElementById(selId);
                if (!sel) return;
                var opt = document.createElement('option');
                opt.value = data.ruangan_id;
                opt.textContent = data.nama_ruangan;
                sel.appendChild(opt);
            });
            document.getElementById(prefix + '_ruangan').value = data.ruangan_id;
            namaEl.value = '';
            lokasiEl.value = '';
            toggleAddRuangan(prefix);
        } else {
            alert(data.message || 'Gagal menambah ruangan');
        }
    })
    .catch(function() {
        alert('Terjadi kesalahan jaringan, ruangan gagal ditambahkan');
    });
}
</script>

<?= $this->endSection() ?>