<?= $this->extend('layouts/adminkit_template') ?>
<?= $this->section('content') ?>

<?php
/*
 * admin/dashboard.php — v4.0
 * Optimasi Total: Compact · Padat · Enterprise · Professional
 * Desain: White background + Dark Navy Blue accent
 * Font: Plus Jakarta Sans (heading) + IBM Plex Sans (body) + JetBrains Mono (data)
 */
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=IBM+Plex+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

<style>
/* ═══════════════════════════════════════
   TOKENS
═══════════════════════════════════════ */
:root{
  --fh:'Plus Jakarta Sans',sans-serif;
  --fb:'IBM Plex Sans',sans-serif;
  --fm:'JetBrains Mono',monospace;

  /* Navy palette */
  --n900:#0a1628;
  --n800:#0f2044;
  --n700:#1a3461;
  --n600:#1e3f7a;
  --n500:#1d4ed8;
  --n400:#3b82f6;
  --n300:#93c5fd;
  --n200:#dbeafe;
  --n100:#eff6ff;

  /* Surface */
  --bg:#f4f7fc;
  --surface:#ffffff;
  --border:#e2e8f2;
  --border2:#cdd5e0;

  /* Text */
  --t1:#0a1628;
  --t2:#1e3a5f;
  --t3:#4b5e7a;
  --t4:#8a9ab5;

  /* Semantic */
  --gn:#15803d;--gn2:#22c55e;--gn-bg:#dcfce7;
  --am:#b45309;--am2:#f59e0b;--am-bg:#fef3c7;
  --rd:#b91c1c;--rd2:#ef4444;--rd-bg:#fee2e2;
  --vi:#6d28d9;--vi2:#8b5cf6;--vi-bg:#ede9fe;
  --te:#0f766e;--te2:#14b8a6;--te-bg:#ccfbf1;
  --pk:#be185d;--pk2:#ec4899;--pk-bg:#fce7f3;

  --r:8px;--r2:12px;
  --sh:0 1px 3px rgba(10,22,40,.07),0 1px 2px rgba(10,22,40,.04);
  --sh2:0 4px 12px rgba(10,22,40,.1);
}

/* ═══════════════════════════════════════
   BASE RESET
═══════════════════════════════════════ */
.db{font-family:var(--fb);background:var(--bg);min-height:100vh;padding:12px 14px 20px;box-sizing:border-box;}
.db *{box-sizing:border-box;margin:0;padding:0;}
.db a{text-decoration:none;}

/* ═══════════════════════════════════════
   TOPBAR — Header compact
═══════════════════════════════════════ */
.db-top{
  background:var(--n900);
  border-radius:var(--r2);
  padding:10px 16px;
  margin-bottom:8px;
  display:flex;align-items:center;justify-content:space-between;
  flex-wrap:wrap;gap:8px;
  position:relative;overflow:hidden;
}
.db-top::before{
  content:'';position:absolute;inset:0;
  background:repeating-linear-gradient(90deg,rgba(59,130,246,.06) 0,rgba(59,130,246,.06) 1px,transparent 1px,transparent 40px),
             repeating-linear-gradient(rgba(59,130,246,.06) 0,rgba(59,130,246,.06) 1px,transparent 1px,transparent 40px);
  pointer-events:none;
}
.db-top::after{
  content:'';position:absolute;right:-60px;top:-60px;
  width:180px;height:180px;
  background:radial-gradient(circle,rgba(59,130,246,.18) 0,transparent 65%);
  pointer-events:none;
}
.db-top-l{display:flex;align-items:center;gap:10px;position:relative;z-index:1;}
.db-top-ico{
  width:34px;height:34px;border-radius:8px;
  background:rgba(59,130,246,.2);border:1px solid rgba(59,130,246,.35);
  display:grid;place-items:center;font-size:.9rem;color:var(--n300);flex-shrink:0;
}
.db-top-title{font-family:var(--fh);font-size:1rem;font-weight:800;color:#fff;letter-spacing:.01em;line-height:1.1;}
.db-top-sub{font-size:.68rem;color:rgba(255,255,255,.45);margin-top:1px;}
.db-top-r{display:flex;align-items:center;gap:6px;flex-wrap:wrap;position:relative;z-index:1;}
.chip{
  display:inline-flex;align-items:center;gap:4px;
  padding:4px 9px;border-radius:20px;
  font-size:.67rem;font-weight:600;
  background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.75);
}
.chip-live{background:rgba(21,128,61,.2);border-color:rgba(34,197,94,.35);color:#86efac;}
.chip .dot{width:5px;height:5px;border-radius:50%;background:#22c55e;animation:blink 1.4s ease-in-out infinite;}
#db-clock{font-family:var(--fm);font-size:.72rem;color:rgba(255,255,255,.65);}
@keyframes blink{0%,100%{opacity:1;box-shadow:0 0 5px #22c55e}50%{opacity:.3;box-shadow:none}}

/* ═══════════════════════════════════════
   KPI ROW — 8 card 4-col
═══════════════════════════════════════ */
.kpi-grid{
  display:grid;
  grid-template-columns:repeat(8,1fr);
  gap:8px;margin-bottom:10px;
}
@media(max-width:1400px){.kpi-grid{grid-template-columns:repeat(4,1fr);}}
@media(max-width:900px) {.kpi-grid{grid-template-columns:repeat(2,1fr);}}
@media(max-width:480px) {.kpi-grid{grid-template-columns:repeat(2,1fr);}}

.kpi{
  background:var(--surface);border:1px solid var(--border);
  border-radius:var(--r);padding:10px 12px;
  position:relative;overflow:hidden;
  box-shadow:var(--sh);transition:box-shadow .2s,transform .15s;
}
.kpi:hover{box-shadow:var(--sh2);transform:translateY(-1px);}
.kpi::after{
  content:'';position:absolute;top:0;left:0;right:0;height:2px;
  background:var(--kc,var(--n500));border-radius:var(--r) var(--r) 0 0;
}
.kpi-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;}
.kpi-ico{
  width:28px;height:28px;border-radius:6px;
  background:var(--kb,rgba(29,78,216,.1));
  color:var(--kc,var(--n500));
  display:grid;place-items:center;font-size:.8rem;
}
.kpi-badge{font-size:.6rem;font-weight:700;padding:1px 6px;border-radius:99px;}
.kpi-badge-up{background:var(--gn-bg);color:var(--gn);}
.kpi-badge-dn{background:var(--rd-bg);color:var(--rd);}
.kpi-badge-nt{background:var(--n100);color:var(--t3);}
.kpi-val{font-family:var(--fm);font-size:1.35rem;font-weight:600;color:var(--t1);line-height:1;letter-spacing:-.02em;}
.kpi-lbl{font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--t4);margin-top:2px;}
.kpi-sub{font-size:.62rem;color:var(--t3);margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}

/* ═══════════════════════════════════════
   LAYOUT ROWS
═══════════════════════════════════════ */
.row-auto{display:grid;gap:8px;margin-bottom:8px;}
.row-7-3{grid-template-columns:7fr 3fr;}
.row-2{grid-template-columns:1fr 1fr;}
.row-3{grid-template-columns:1fr 1fr 1fr;}
.row-4{grid-template-columns:1fr 1fr 1fr 1fr;}
@media(max-width:1200px){
  .row-7-3{grid-template-columns:1fr 1fr;}
  .row-3,.row-4{grid-template-columns:1fr 1fr;}
}
@media(max-width:768px){
  .row-7-3,.row-2,.row-3,.row-4{grid-template-columns:1fr;}
}

/* ═══════════════════════════════════════
   CARD
═══════════════════════════════════════ */
.card{
  background:var(--surface);border:1px solid var(--border);
  border-radius:var(--r);overflow:hidden;box-shadow:var(--sh);
}
.card-hd{
  padding:8px 12px;border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;gap:6px;
}
.card-hd-t{
  font-family:var(--fh);font-size:.75rem;font-weight:700;color:var(--t1);
  display:flex;align-items:center;gap:6px;
}
.card-hd-t i{color:var(--n500);font-size:.8rem;}
.card-meta{font-size:.62rem;color:var(--t4);font-weight:500;}
.card-bd{padding:10px 12px;}
.card-bd-sm{padding:8px 10px;}

/* Link */
.db-link{font-size:.65rem;font-weight:700;color:var(--n500);display:flex;align-items:center;gap:3px;transition:gap .12s;}
.db-link:hover{gap:6px;color:var(--n400);}

/* Section bar */
.sec-bar{display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;flex-wrap:wrap;gap:4px;}
.sec-title{
  font-family:var(--fh);font-size:.75rem;font-weight:700;color:var(--t2);
  display:flex;align-items:center;gap:6px;
}
.sec-title::before{content:'';width:2px;height:12px;background:var(--n500);border-radius:99px;display:inline-block;}

/* ═══════════════════════════════════════
   TABLE
═══════════════════════════════════════ */
.tbl{width:100%;border-collapse:collapse;font-size:.75rem;}
.tbl thead th{
  background:var(--n100);padding:6px 10px;
  font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;
  color:var(--t4);border-bottom:1px solid var(--border);white-space:nowrap;
  position:sticky;top:0;z-index:1;
}
.tbl tbody td{
  padding:6px 10px;border-bottom:1px solid #f1f5fb;
  color:var(--t2);vertical-align:middle;
}
.tbl tbody tr:last-child td{border-bottom:none;}
.tbl tbody tr:hover td{background:#f8faff;}
.tbl-scroll{overflow-x:auto;overflow-y:auto;max-height:220px;}

/* Status badges */
.stb{display:inline-flex;align-items:center;gap:3px;padding:2px 7px;border-radius:99px;font-size:.6rem;font-weight:700;white-space:nowrap;}
.stb::before{content:'';width:4px;height:4px;border-radius:50%;background:currentColor;}
.stb-ok  {background:var(--gn-bg);color:var(--gn);}
.stb-rd  {background:var(--rd-bg);color:var(--rd);}
.stb-am  {background:var(--am-bg);color:var(--am);}
.stb-vi  {background:var(--vi-bg);color:var(--vi);}
.stb-nt  {background:var(--n100);color:var(--t3);}
.mono{font-family:var(--fm);font-size:.72rem;}

/* ═══════════════════════════════════════
   DEVICE LIST
═══════════════════════════════════════ */
.dev-list{overflow-y:auto;max-height:200px;}
.dev-row{display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid var(--border);}
.dev-row:last-child{border-bottom:none;}
.dev-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0;}
.dev-dot-on {background:var(--gn2);box-shadow:0 0 5px rgba(34,197,94,.5);animation:blink 2s infinite;}
.dev-dot-off{background:var(--t4);}
.dev-dot-mnt{background:var(--am2);}
.dev-info{flex:1;min-width:0;}
.dev-name{font-size:.73rem;font-weight:600;color:var(--t1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.dev-loc {font-size:.62rem;color:var(--t4);}
.dev-b{display:inline-block;padding:1px 6px;border-radius:4px;font-size:.6rem;font-weight:700;white-space:nowrap;}
.dev-b-on {background:var(--gn-bg);color:var(--gn);}
.dev-b-off{background:var(--n100);color:var(--t4);}
.dev-b-mnt{background:var(--am-bg);color:var(--am);}
.dev-time{font-family:var(--fm);font-size:.62rem;color:var(--t4);margin-left:auto;white-space:nowrap;}

/* ═══════════════════════════════════════
   JADWAL
═══════════════════════════════════════ */
.jdw-item{border:1px solid var(--border);border-radius:7px;overflow:hidden;margin-bottom:6px;}
.jdw-item:last-child{margin-bottom:0;}
.jdw-hd{
  padding:6px 10px;border-bottom:1px solid var(--border);
  display:flex;align-items:center;gap:6px;flex-wrap:wrap;background:var(--n100);
}
.jdw-bd{padding:7px 10px;font-size:.73rem;}
.jdw-tag{font-family:var(--fh);font-size:.67rem;font-weight:700;padding:2px 8px;border-radius:4px;}
.jdw-pagi {background:#fef9c3;color:#854d0e;}
.jdw-siang{background:#dbeafe;color:#1e40af;}
.jdw-malam{background:#ede9fe;color:#5b21b6;}
.jdw-time{font-family:var(--fm);font-size:.67rem;color:var(--t3);}
.jdw-st{font-size:.6rem;font-weight:700;padding:1px 7px;border-radius:99px;margin-left:auto;}
.jdw-st-b{background:var(--gn-bg);color:var(--gn);}
.jdw-st-r{background:var(--am-bg);color:var(--am);}
.jdw-st-d{background:var(--n100);color:var(--t4);}
.jdw-p{display:flex;align-items:center;gap:5px;padding:3px 0;}
.jdw-av{width:20px;height:20px;border-radius:50%;display:grid;place-items:center;font-size:.6rem;font-weight:700;color:#fff;flex-shrink:0;}

/* ═══════════════════════════════════════
   LAPORAN TILES
═══════════════════════════════════════ */
.lpr-grid{display:grid;grid-template-columns:1fr 1fr;gap:6px;}
.lpr-tile{background:var(--n100);border:1px solid var(--border);border-radius:7px;padding:8px;text-align:center;transition:background .12s;}
.lpr-tile:hover{background:var(--n200);}
.lpr-v{font-family:var(--fm);font-size:1.2rem;font-weight:700;color:var(--t1);line-height:1;}
.lpr-l{font-size:.58rem;font-weight:700;color:var(--t4);text-transform:uppercase;letter-spacing:.06em;margin-top:2px;}

/* ═══════════════════════════════════════
   STATUS BAR
═══════════════════════════════════════ */
.sbar{display:flex;flex-direction:column;gap:5px;}
.sbar-row{display:flex;align-items:center;gap:6px;}
.sbar-lbl{font-size:.62rem;color:var(--t3);font-weight:600;width:80px;flex-shrink:0;white-space:nowrap;}
.sbar-wrap{flex:1;height:5px;background:var(--border);border-radius:99px;overflow:hidden;}
.sbar-fill{height:100%;border-radius:99px;transition:width .4s ease;}
.sbar-num{font-family:var(--fm);font-size:.65rem;font-weight:700;color:var(--t2);min-width:18px;text-align:right;}



/* ═══════════════════════════════════════
   SYSTEM INFO
═══════════════════════════════════════ */
.sys-grid{display:grid;grid-template-columns:1fr 1fr;gap:5px;}
.sys-cell{background:var(--n100);border:1px solid var(--border);border-radius:6px;padding:7px 9px;}
.sys-cell.full{grid-column:1/-1;}
.sys-lbl{font-size:.58rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--t4);margin-bottom:2px;}
.sys-val{font-family:var(--fm);font-size:.72rem;font-weight:600;color:var(--t1);}
.sys-ok .sys-val{color:var(--gn);}
.sys-err .sys-val{color:var(--rd);}
.sys-ok{border-color:rgba(21,128,61,.25);background:rgba(21,128,61,.05);}
.sys-err{border-color:rgba(185,28,28,.25);background:rgba(185,28,28,.05);}

/* ═══════════════════════════════════════
   CHART CONTAINERS
═══════════════════════════════════════ */
.ch{position:relative;}
.ch-170{height:170px;}
.ch-150{height:150px;}
.ch-130{height:130px;}
.ch-120{height:120px;}

/* ═══════════════════════════════════════
   EMPTY
═══════════════════════════════════════ */
.empty{text-align:center;padding:18px 12px;color:var(--t4);font-size:.75rem;}
.empty i{font-size:1.4rem;opacity:.2;display:block;margin-bottom:5px;}

/* ═══════════════════════════════════════
   ANIMATE
═══════════════════════════════════════ */
@keyframes fadeUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
.db-top   {animation:fadeUp .25s .00s both;}
.kpi-grid {animation:fadeUp .25s .05s both;}
.card     {animation:fadeUp .25s .08s both;}

/* ═══════════════════════════════════════
   SCROLLBAR
═══════════════════════════════════════ */
::-webkit-scrollbar{width:3px;height:3px;}
::-webkit-scrollbar-track{background:var(--n100);}
::-webkit-scrollbar-thumb{background:var(--border2);border-radius:99px;}

/* ═══════════════════════════════════════
   RESPONSIVE GUARDS
═══════════════════════════════════════ */
.db{max-width:100%;overflow-x:hidden;}
img,canvas,table{max-width:100%;}
@media(max-width:480px){
  .db-top-title{font-size:.85rem;}
  .kpi-val{font-size:1.1rem;}
}
</style>

<div class="db">

<!-- ══════════════════ TOPBAR ══════════════════ -->
<div class="db-top">
  <div class="db-top-l">
    <div class="db-top-ico"><i class="bi bi-shield-fill-check"></i></div>
    <div>
      <div class="db-top-title">RFID Security Monitoring</div>
      <div class="db-top-sub">GKI Bromo Malang — Patrol Management System v4.0</div>
    </div>
  </div>
  <div class="db-top-r">
    <div class="chip chip-live"><span class="dot"></span>Live</div>
    <div class="chip"><i class="bi bi-calendar3" style="font-size:.65rem"></i><?= date('d M Y') ?></div>
    <div class="chip"><i class="bi bi-clock" style="font-size:.65rem"></i><span id="db-clock"><?= date('H:i:s') ?></span></div>
    <?php if ($jadwalBerjalan > 0): ?>
    <div class="chip" style="background:rgba(185,28,28,.2);border-color:rgba(239,68,68,.35);color:#fca5a5;">
      <i class="bi bi-activity" style="font-size:.65rem"></i><?= $jadwalBerjalan ?> Shift
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ══════════════════ KPI CARDS ══════════════════ -->
<div class="kpi-grid">

  <!-- Scan Hari Ini -->
  <div class="kpi" style="--kc:var(--n500);--kb:rgba(29,78,216,.08)">
    <div class="kpi-top">
      <div class="kpi-ico"><i class="bi bi-upc-scan"></i></div>
      <?php if($scanHariIni>0): ?><span class="kpi-badge kpi-badge-up">+<?= $scanHariIni ?></span><?php else: ?><span class="kpi-badge kpi-badge-nt">0</span><?php endif; ?>
    </div>
    <div class="kpi-val"><?= number_format($scanHariIni) ?></div>
    <div class="kpi-lbl">Scan Hari Ini</div>
    <div class="kpi-sub">Semua jenis kartu</div>
  </div>

  <!-- Scan Bulan -->
  <div class="kpi" style="--kc:var(--te);--kb:var(--te-bg)">
    <div class="kpi-top">
      <div class="kpi-ico"><i class="bi bi-graph-up-arrow"></i></div>
    </div>
    <div class="kpi-val"><?= number_format($scanBulanIni) ?></div>
    <div class="kpi-lbl">Scan Bulan Ini</div>
    <div class="kpi-sub"><?= date('M Y') ?></div>
  </div>

  <!-- Asing Hari Ini -->
  <div class="kpi" style="--kc:var(--rd);--kb:var(--rd-bg)">
    <div class="kpi-top">
      <div class="kpi-ico"><i class="bi bi-shield-exclamation"></i></div>
      <?php if($kartuAsingHariIni>0): ?><span class="kpi-badge kpi-badge-dn">!</span><?php endif; ?>
    </div>
    <div class="kpi-val"><?= $kartuAsingHariIni ?></div>
    <div class="kpi-lbl">Asing Hari Ini</div>
    <div class="kpi-sub">Unique: <?= $totalKartuAsing ?></div>
  </div>

  <!-- Total Scan -->
  <div class="kpi" style="--kc:var(--vi);--kb:var(--vi-bg)">
    <div class="kpi-top">
      <div class="kpi-ico"><i class="bi bi-database-fill-check"></i></div>
    </div>
    <div class="kpi-val"><?= number_format($totalScanAll) ?></div>
    <div class="kpi-lbl">Total Scan</div>
    <div class="kpi-sub">Sepanjang waktu</div>
  </div>

  <!-- Perangkat -->
  <div class="kpi" style="--kc:var(--te2);--kb:var(--te-bg)">
    <div class="kpi-top">
      <div class="kpi-ico"><i class="bi bi-hdd-network-fill"></i></div>
    </div>
    <div class="kpi-val"><?= $totalPerangkat ?></div>
    <div class="kpi-lbl">Perangkat</div>
    <div class="kpi-sub"><span style="color:var(--gn);font-weight:700"><?= $perangkatOnline ?>↑</span> <?= $perangkatOffline ?>↓</div>
  </div>

  <!-- Petugas -->
  <div class="kpi" style="--kc:var(--am);--kb:var(--am-bg)">
    <div class="kpi-top">
      <div class="kpi-ico"><i class="bi bi-person-badge-fill"></i></div>
    </div>
    <div class="kpi-val"><?= $totalPetugas ?></div>
    <div class="kpi-lbl">Petugas Aktif</div>
    <div class="kpi-sub">Total: <?= $totalUsers ?> user</div>
  </div>

  <!-- Kartu RFID -->
  <div class="kpi" style="--kc:var(--pk);--kb:var(--pk-bg)">
    <div class="kpi-top">
      <div class="kpi-ico"><i class="bi bi-credit-card-2-front-fill"></i></div>
    </div>
    <div class="kpi-val"><?= $totalKartu ?></div>
    <div class="kpi-lbl">Kartu RFID</div>
    <div class="kpi-sub">Status aktif</div>
  </div>

  <!-- Jadwal -->
  <div class="kpi" style="--kc:var(--gn);--kb:var(--gn-bg)">
    <div class="kpi-top">
      <div class="kpi-ico"><i class="bi bi-calendar-check-fill"></i></div>
      <?php if($jadwalBerjalan>0): ?><span class="kpi-badge kpi-badge-up"><?= $jadwalBerjalan ?></span><?php endif; ?>
    </div>
    <div class="kpi-val"><?= $jadwalHariIni ?></div>
    <div class="kpi-lbl">Jadwal Hari Ini</div>
    <div class="kpi-sub">Minggu: <?= $jadwalMingguIni ?></div>
  </div>

</div><!-- /.kpi-grid -->

<!-- ══════════════════ ROW 1: CHART AKTIVITAS + DOUGHNUT ══════════════════ -->
<div class="row-auto row-7-3" style="margin-bottom:10px;">

  <!-- Chart Aktivitas 30 Hari -->
  <div>
    <div class="sec-bar">
      <div class="sec-title">Aktivitas RFID — 30 Hari</div>
      <a href="<?= base_url('log-rfid') ?>" class="db-link">Lihat Log <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="card">
      <div class="card-hd">
        <div class="card-hd-t"><i class="bi bi-activity"></i>Scan Harian</div>
        <div style="display:flex;gap:10px;align-items:center;">
          <span style="display:flex;align-items:center;gap:3px;font-size:.62rem;color:var(--t3);"><span style="width:8px;height:2px;background:var(--n500);border-radius:2px;display:inline-block"></span>Terdaftar</span>
          <span style="display:flex;align-items:center;gap:3px;font-size:.62rem;color:var(--t3);"><span style="width:8px;height:2px;background:var(--rd2);border-radius:2px;display:inline-block"></span>Asing</span>
        </div>
      </div>
      <div class="card-bd" style="padding:8px 12px;">
        <div class="ch ch-170"><canvas id="cAktivitas"></canvas></div>
      </div>
    </div>
  </div>

  <!-- Doughnut Perangkat -->
  <div>
    <div class="sec-bar">
      <div class="sec-title">Status Perangkat</div>
      <a href="<?= base_url('perangkat') ?>" class="db-link">Kelola <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="card">
      <div class="card-hd">
        <div class="card-hd-t"><i class="bi bi-hdd-stack"></i>Distribusi</div>
        <span class="card-meta"><?= $totalPerangkat ?> unit</span>
      </div>
      <div class="card-bd" style="padding:8px 10px;">
        <div class="ch ch-120"><canvas id="cDonut"></canvas></div>
        <div class="sbar" style="margin-top:8px;">
          <div class="sbar-row">
            <span class="sbar-lbl">🟢 Online</span>
            <div class="sbar-wrap"><div class="sbar-fill" style="background:var(--gn2);width:<?= $totalPerangkat?round($perangkatOnline/$totalPerangkat*100):0 ?>%"></div></div>
            <span class="sbar-num"><?= $perangkatOnline ?></span>
          </div>
          <div class="sbar-row">
            <span class="sbar-lbl">🔴 Offline</span>
            <div class="sbar-wrap"><div class="sbar-fill" style="background:var(--rd2);width:<?= $totalPerangkat?round($perangkatOffline/$totalPerangkat*100):0 ?>%"></div></div>
            <span class="sbar-num"><?= $perangkatOffline ?></span>
          </div>
          <div class="sbar-row">
            <span class="sbar-lbl">🟡 Maintenance</span>
            <div class="sbar-wrap"><div class="sbar-fill" style="background:var(--am2);width:<?= $totalPerangkat?round($perangkatMaintenance/$totalPerangkat*100):0 ?>%"></div></div>
            <span class="sbar-num"><?= $perangkatMaintenance ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>

</div><!-- /.row-7-3 -->

<!-- ══════════════════ ROW 2: LIVE LOG ══════════════════ -->
<div style="margin-bottom:10px;">
  <div class="sec-bar">
    <div class="sec-title">Live RFID Log — 15 Terbaru</div>
    <div style="display:flex;align-items:center;gap:10px;">
      <span id="liveRefreshInfo" style="font-size:.62rem;color:var(--t4);">Auto-refresh 30 dtk</span>
      <a href="<?= base_url('log-rfid') ?>" class="db-link">Semua <i class="bi bi-arrow-right"></i></a>
    </div>
  </div>
  <div class="card">
    <div class="card-hd">
      <div class="card-hd-t"><i class="bi bi-broadcast" style="color:var(--rd2)"></i>Aktivitas Real-Time</div>
      <span class="card-meta" style="display:flex;align-items:center;gap:4px;"><span class="dot" style="background:#22c55e;width:5px;height:5px;border-radius:50%;display:inline-block;animation:blink 1.4s infinite;"></span>Live</span>
    </div>
    <div class="tbl-scroll">
      <table class="tbl" id="liveLogTable">
        <thead>
          <tr>
            <th>#</th><th>Waktu</th><th>UID Kartu</th><th>Petugas</th>
            <th>Perangkat</th><th>Lokasi</th><th>Status</th>
          </tr>
        </thead>
        <tbody id="liveLogBody">
        <?php if(!empty($liveLog)):?>
          <?php foreach($liveLog as $i=>$row):
            $isAsing=($row['jenis']??'')==='Asing';$sv=$row['status_validasi']??'';
            if($isAsing){$sc='stb-rd';$st='Asing';}
            elseif($sv==='Sesuai'){$sc='stb-ok';$st='Sesuai';}
            elseif(in_array($sv,['Di Luar Jadwal','Tidak Terjadwal'])){$sc='stb-vi';$st='Di Luar Jadwal';}
            elseif($sv==='Terlambat'){$sc='stb-am';$st='Terlambat';}
            elseif($sv==='Tidak Sesuai'){$sc='stb-rd';$st='Tidak Sesuai';}
            else{$sc='stb-nt';$st=$sv?:'–';}
          ?>
          <tr>
            <td style="color:var(--t4);font-size:.65rem;"><?= $i+1 ?></td>
            <td class="mono"><?= date('H:i:s',strtotime($row['waktu_tap']??$row['waktu_kunjungan'])) ?></td>
            <td class="mono" style="font-weight:600;color:var(--t1);"><?= esc($row['uid_rfid']??$row['uid_kartu']??'–') ?></td>
            <td style="font-size:.72rem;font-weight:600;"><?= $isAsing?'<span style="color:var(--t4)">–</span>':esc($row['nama_petugas']) ?></td>
            <td class="mono" style="color:var(--t3);"><?= esc($row['kode_perangkat']) ?></td>
            <td>
              <span style="font-size:.7rem;font-weight:600;color:var(--n700);"><?= esc($row['kode_ruangan']) ?></span>
              <span style="font-size:.65rem;color:var(--t4);"> <?= esc($row['nama_ruangan']) ?></span>
            </td>
            <td><span class="stb <?= $sc ?>"><?= $st ?></span></td>
          </tr>
          <?php endforeach;?>
        <?php else:?>
          <tr><td colspan="7"><div class="empty"><i class="bi bi-inbox"></i>Belum ada data</div></td></tr>
        <?php endif;?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ══════════════════ ROW 3: PERANGKAT + JADWAL ══════════════════ -->
<div class="row-auto row-2" style="margin-bottom:10px;">

  <!-- Perangkat -->
  <div>
    <div class="sec-bar">
      <div class="sec-title">Monitoring Perangkat</div>
      <a href="<?= base_url('perangkat') ?>" class="db-link">Kelola <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="card">
      <div class="card-hd">
        <div class="card-hd-t"><i class="bi bi-cpu"></i>Daftar ESP32</div>
        <span class="card-meta"><?= $totalPerangkat ?> unit</span>
      </div>
      <div class="card-bd-sm dev-list">
        <?php if(!empty($perangkatList)):?>
          <?php foreach($perangkatList as $pdev):
            $st=$pdev['status_perangkat'];
            $dc=$st==='Online'?'dev-dot-on':($st==='Maintenance'?'dev-dot-mnt':'dev-dot-off');
            $bc=$st==='Online'?'dev-b-on':($st==='Maintenance'?'dev-b-mnt':'dev-b-off');
            $ls=!empty($pdev['last_online'])?date('d/m H:i',strtotime($pdev['last_online'])):'–';
          ?>
          <div class="dev-row">
            <div class="dev-dot <?= $dc ?>"></div>
            <div class="dev-info">
              <div class="dev-name"><?= esc($pdev['kode_perangkat']) ?></div>
              <div class="dev-loc"><?= esc($pdev['nama_ruangan']??'–') ?><?= !empty($pdev['lokasi'])?' · '.esc($pdev['lokasi']):'' ?></div>
            </div>
            <span class="dev-b <?= $bc ?>"><?= $st ?></span>
            <div class="dev-time"><?= $ls ?></div>
          </div>
          <?php endforeach;?>
        <?php else:?>
          <div class="empty"><i class="bi bi-hdd-network"></i>Belum ada perangkat</div>
        <?php endif;?>
      </div>
    </div>
  </div>

  <!-- Jadwal Hari Ini -->
  <div>
    <div class="sec-bar">
      <div class="sec-title">Jadwal Hari Ini</div>
      <a href="<?= base_url('jadwal') ?>" class="db-link">Kalender <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="card">
      <div class="card-hd">
        <div class="card-hd-t"><i class="bi bi-calendar-event" style="color:var(--am)"></i><?= date('l, d F') ?></div>
        <span class="card-meta"><?= count($jadwalList) ?> shift</span>
      </div>
      <div class="card-bd-sm" style="padding:8px 10px;">
        <?php if(!empty($jadwalList)):?>
          <?php foreach($jadwalList as $jdw):
            $ss=$jdw['status_shift'];
            $sc=$ss==='Berjalan'?'jdw-st-b':($ss==='Belum Mulai'?'jdw-st-r':'jdw-st-d');
            $snm=$jdw['nama_shift'];
            $shCls='jdw-'.strtolower($snm);
          ?>
          <div class="jdw-item">
            <div class="jdw-hd">
              <span class="jdw-tag <?= $shCls ?>"><?= esc($snm) ?></span>
              <span class="jdw-time"><?= substr($jdw['jam_mulai'],0,5) ?> – <?= substr($jdw['jam_selesai'],0,5) ?></span>
              <span class="jdw-st <?= $sc ?>"><?= $ss ?></span>
            </div>
            <div class="jdw-bd">
              <?php if(!empty($jdw['petugas_1_nama'])):?>
              <div class="jdw-p">
                <div class="jdw-av" style="background:var(--n500);"><?= strtoupper(mb_substr($jdw['petugas_1_nama'],0,1)) ?></div>
                <span style="font-size:.72rem;font-weight:600;"><?= esc($jdw['petugas_1_nama']) ?></span>
                <span style="font-size:.6rem;color:var(--t4);">P1</span>
              </div>
              <?php endif;?>
              <?php if(!empty($jdw['petugas_2_nama'])):?>
              <div class="jdw-p">
                <div class="jdw-av" style="background:var(--gn);"><?= strtoupper(mb_substr($jdw['petugas_2_nama'],0,1)) ?></div>
                <span style="font-size:.72rem;font-weight:600;"><?= esc($jdw['petugas_2_nama']) ?></span>
                <span style="font-size:.6rem;color:var(--t4);">P2</span>
              </div>
              <?php endif;?>
              <?php if(!empty($jdw['catatan'])):?>
              <div style="font-size:.65rem;color:var(--t3);margin-top:4px;border-left:2px solid var(--n400);padding-left:6px;"><?= esc($jdw['catatan']) ?></div>
              <?php endif;?>
            </div>
          </div>
          <?php endforeach;?>
        <?php else:?>
          <div class="empty"><i class="bi bi-calendar-x"></i>Belum ada jadwal hari ini</div>
        <?php endif;?>
      </div>
    </div>
  </div>

</div><!-- /.row-2 -->

<!-- ══════════════════ ROW 4: LAPORAN + TOP PETUGAS + PATROLI 7H + SYS ══════════════════ -->
<div class="row-auto row-4" style="margin-bottom:4px;">

  <!-- Ringkasan Laporan -->
  <div>
    <div class="sec-bar">
      <div class="sec-title">Ringkasan Laporan</div>
      <a href="<?= base_url('laporan') ?>" class="db-link">Detail <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="card">
      <div class="card-hd">
        <div class="card-hd-t"><i class="bi bi-clipboard-data" style="color:var(--vi)"></i>Patroli</div>
      </div>
      <div class="card-bd-sm">
        <div class="lpr-grid">
          <div class="lpr-tile"><div class="lpr-v" style="color:var(--n500)"><?= $laporanHariIni ?></div><div class="lpr-l">Hari Ini</div></div>
          <div class="lpr-tile"><div class="lpr-v" style="color:var(--te)"><?= $laporanMingguIni ?></div><div class="lpr-l">Minggu</div></div>
          <div class="lpr-tile"><div class="lpr-v" style="color:var(--am)"><?= $laporanBulanIni ?></div><div class="lpr-l">Bulan</div></div>
          <div class="lpr-tile"><div class="lpr-v" style="color:var(--vi)"><?= $laporanTotal ?></div><div class="lpr-l">Total</div></div>
        </div>
        <?php $totalSH=array_sum($statusMap);?>
        <?php if($totalSH>0):?>
        <div class="sbar" style="margin-top:8px;">
          <?php $smaps=[['Valid','#15803d',$statusMap['Valid']],['Normal','#1d4ed8',$statusMap['Normal']],['Warning','#b45309',$statusMap['Warning']],['Tdk Lengkap','#b91c1c',$statusMap['Tidak Lengkap']]];?>
          <?php foreach($smaps as[$lbl,$col,$cnt]):$pct=$totalSH?round($cnt/$totalSH*100):0;?>
          <div class="sbar-row">
            <span class="sbar-lbl"><?= $lbl ?></span>
            <div class="sbar-wrap"><div class="sbar-fill" style="background:<?= $col ?>;width:<?= $pct ?>%"></div></div>
            <span class="sbar-num"><?= $cnt ?></span>
          </div>
          <?php endforeach;?>
        </div>
        <?php else:?>
          <div style="font-size:.67rem;color:var(--t4);text-align:center;padding:6px 0;">Belum ada patroli hari ini</div>
        <?php endif;?>
      </div>
    </div>
  </div>

  <!-- Top Petugas -->
  <div>
    <div class="sec-bar">
      <div class="sec-title">Top Scan Hari Ini</div>
    </div>
    <div class="card">
      <div class="card-hd">
        <div class="card-hd-t"><i class="bi bi-trophy" style="color:var(--am2)"></i>Peringkat</div>
      </div>
      <div class="card-bd-sm">
        <?php if(!empty($topPetugas)):?>
          <?php foreach($topPetugas as $ri=>$tp):$rCls=['r1','r2','r3'][$ri]??'';?>
          <div class="tp-row">
            <div class="tp-rank <?= $rCls ?>"><?= $ri+1 ?></div>
            <div class="tp-av"><?= strtoupper(mb_substr($tp['nama_lengkap'],0,1)) ?></div>
            <div class="tp-name"><?= esc($tp['nama_lengkap']) ?></div>
            <div class="tp-scan"><?= $tp['total_scan'] ?><span style="font-size:.58rem;font-weight:400;color:var(--t4)"> scan</span></div>
          </div>
          <?php endforeach;?>
        <?php else:?>
          <div class="empty" style="padding:14px 0;"><i class="bi bi-trophy"></i>Belum ada scan</div>
        <?php endif;?>
      </div>
    </div>
  </div>

  <!-- Patroli 7 Hari -->
  <div>
    <div class="sec-bar">
      <div class="sec-title">Patroli 7 Hari</div>
      <a href="<?= base_url('laporan') ?>" class="db-link">Detail <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="card">
      <div class="card-hd">
        <div class="card-hd-t"><i class="bi bi-bar-chart-steps" style="color:var(--gn)"></i>Lengkap vs Tidak</div>
      </div>
      <div class="card-bd" style="padding:8px 10px;">
        <div class="ch ch-150"><canvas id="cPatroli7"></canvas></div>
      </div>
    </div>
  </div>

  <!-- System Info -->
  <div>
    <div class="sec-bar">
      <div class="sec-title">Info Sistem</div>
    </div>
    <div class="card">
      <div class="card-hd">
        <div class="card-hd-t"><i class="bi bi-gear-wide-connected"></i>Quick Info</div>
      </div>
      <div class="card-bd-sm">
        <div class="sys-grid">
          <div class="sys-cell">
            <div class="sys-lbl">Sistem</div>
            <div class="sys-val" style="font-size:.67rem;">RFID v4.0</div>
          </div>
          <div class="sys-cell <?= $dbStatus==='Connected'?'sys-ok':'sys-err' ?>">
            <div class="sys-lbl">Database</div>
            <div class="sys-val"><?= $dbStatus==='Connected'?'● OK':'● Error' ?></div>
          </div>
          <div class="sys-cell">
            <div class="sys-lbl">User Aktif</div>
            <div class="sys-val"><?= $totalUsers ?></div>
          </div>
          <div class="sys-cell">
            <div class="sys-lbl">Online</div>
            <div class="sys-val" style="color:var(--gn)"><?= $perangkatOnline ?>/<?= $totalPerangkat ?></div>
          </div>
          <div class="sys-cell full">
            <div class="sys-lbl">Waktu Server</div>
            <div class="sys-val" id="serverTime" style="font-size:.67rem;"><?= $serverTime ?></div>
          </div>
          <div class="sys-cell">
            <div class="sys-lbl">Kartu RFID</div>
            <div class="sys-val"><?= $totalKartu ?></div>
          </div>
          <div class="sys-cell">
            <div class="sys-lbl">Titik Patroli</div>
            <div class="sys-val">6 titik</div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div><!-- /.row-4 -->

</div><!-- /.db -->

<!-- ═════════ CHART.JS ═════════ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const _ACT={labels:<?= $chartLabels ?>,terdaftar:<?= $chartTerdaftar ?>,asing:<?= $chartAsing ?>};
const _DON={labels:<?= $donutLabels ?>,data:<?= $donutData ?>};
const _P7={labels:<?= $p7Labels ?>,lengkap:<?= $p7Lengkap ?>,tidak:<?= $p7Tidak ?>};

Chart.defaults.font.family="'IBM Plex Sans',sans-serif";
Chart.defaults.font.size=10;
Chart.defaults.color='#6b7c99';

const TT={backgroundColor:'#0a1628',borderColor:'#1e3a5f',borderWidth:1,titleColor:'#f1f5f9',bodyColor:'#8a9ab5',padding:8,cornerRadius:6};

/* Chart 1: Aktivitas */
(function(){
  const ctx=document.getElementById('cAktivitas');if(!ctx)return;
  const c=ctx.getContext('2d');
  const gB=c.createLinearGradient(0,0,0,170);gB.addColorStop(0,'rgba(29,78,216,.2)');gB.addColorStop(1,'rgba(29,78,216,.01)');
  const gR=c.createLinearGradient(0,0,0,170);gR.addColorStop(0,'rgba(239,68,68,.15)');gR.addColorStop(1,'rgba(239,68,68,.01)');
  new Chart(ctx,{
    type:'line',
    data:{labels:_ACT.labels,datasets:[
      {label:'Terdaftar',data:_ACT.terdaftar,borderColor:'#1d4ed8',backgroundColor:gB,borderWidth:2,fill:true,tension:.4,pointRadius:_ACT.labels.length>15?0:2,pointHoverRadius:4,pointBackgroundColor:'#1d4ed8'},
      {label:'Asing',data:_ACT.asing,borderColor:'#ef4444',backgroundColor:gR,borderWidth:1.5,fill:true,tension:.4,borderDash:[4,3],pointRadius:_ACT.labels.length>15?0:2,pointHoverRadius:4,pointBackgroundColor:'#ef4444'},
    ]},
    options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},
      plugins:{legend:{display:false},tooltip:TT},
      scales:{
        x:{grid:{color:'rgba(0,0,0,.04)'},ticks:{maxTicksLimit:10,color:'#8a9ab5',font:{size:9}}},
        y:{beginAtZero:true,grid:{color:'rgba(0,0,0,.04)'},ticks:{precision:0,color:'#8a9ab5',font:{size:9}}}
      }
    }
  });
})();

/* Chart 2: Doughnut */
(function(){
  const ctx=document.getElementById('cDonut');if(!ctx)return;
  const total=_DON.data.reduce((a,b)=>a+b,0);
  if(!total){ctx.parentElement.innerHTML='<div class="empty"><i class="bi bi-hdd-network"></i>Belum ada</div>';return;}
  new Chart(ctx,{
    type:'doughnut',
    data:{labels:_DON.labels,datasets:[{data:_DON.data,
      backgroundColor:['rgba(34,197,94,.2)','rgba(148,163,184,.2)','rgba(245,158,11,.2)'],
      borderColor:['#22c55e','#94a3b8','#f59e0b'],borderWidth:2,hoverOffset:6}]},
    options:{responsive:true,maintainAspectRatio:false,cutout:'62%',
      plugins:{legend:{position:'bottom',labels:{padding:8,usePointStyle:true,pointStyleWidth:7,font:{size:9}}},
        tooltip:{...TT,callbacks:{label:c=>` ${c.raw} (${Math.round(c.raw/total*100)}%)`}}
      }
    }
  });
})();

/* Chart 3: Patroli 7 Hari */
(function(){
  const ctx=document.getElementById('cPatroli7');if(!ctx)return;
  new Chart(ctx,{
    type:'bar',
    data:{labels:_P7.labels,datasets:[
      {label:'Lengkap',data:_P7.lengkap,backgroundColor:'rgba(21,128,61,.8)',borderColor:'#15803d',borderWidth:0,borderRadius:3},
      {label:'Tidak Lengkap',data:_P7.tidak,backgroundColor:'rgba(185,28,28,.7)',borderColor:'#b91c1c',borderWidth:0,borderRadius:3},
    ]},
    options:{responsive:true,maintainAspectRatio:false,
      plugins:{legend:{position:'bottom',labels:{padding:7,usePointStyle:true,pointStyleWidth:7,font:{size:9}}},tooltip:TT},
      scales:{
        x:{grid:{display:false},stacked:true,ticks:{color:'#8a9ab5',font:{size:9}}},
        y:{beginAtZero:true,stacked:true,grid:{color:'rgba(0,0,0,.04)'},ticks:{precision:0,color:'#8a9ab5',font:{size:9}}}
      }
    }
  });
})();

/* Clock */
function tickClock(){
  const el=document.getElementById('db-clock');
  const st=document.getElementById('serverTime');
  const n=new Date();
  const t=n.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
  const dt=n.toLocaleString('id-ID',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit'});
  if(el)el.textContent=t;if(st)st.textContent=dt;
}
setInterval(tickClock,1000);tickClock();

/* Live Log Auto Refresh */
let cd=30;
function updateCD(){
  const el=document.getElementById('liveRefreshInfo');
  if(el)el.textContent='Refresh '+cd+' dtk';
  cd--;
  if(cd<0){cd=30;fetchLiveLog();}
}
setInterval(updateCD,1000);

function fetchLiveLog(){
  fetch('<?= base_url('log-rfid/live') ?>',{headers:{'X-Requested-With':'XMLHttpRequest'}})
  .then(r=>r.json()).then(d=>{
    if(!d.ok||!d.data)return;
    const tbody=document.getElementById('liveLogBody');if(!tbody)return;
    let h='';
    d.data.forEach((row,i)=>{
      const isAsing=row.jenis_log==='Asing';const sv=row.status_validasi||'';
      let sc,st;
      if(isAsing){sc='stb-rd';st='Asing';}
      else if(sv==='Sesuai'){sc='stb-ok';st='Sesuai';}
      else if(['Di Luar Jadwal','Tidak Terjadwal'].includes(sv)){sc='stb-vi';st='Di Luar Jadwal';}
      else if(sv==='Terlambat'){sc='stb-am';st='Terlambat';}
      else if(sv==='Tidak Sesuai'){sc='stb-rd';st='Tidak Sesuai';}
      else{sc='stb-nt';st=sv||'–';}
      const w=row.waktu_kunjungan||'';
      const t=w?new Date(w).toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit',second:'2-digit'}):'–';
      h+=`<tr>
        <td style="color:var(--t4);font-size:.65rem;">${i+1}</td>
        <td class="mono">${t}</td>
        <td class="mono" style="font-weight:600;color:var(--t1);">${esc(row.uid_kartu||'–')}</td>
        <td style="font-size:.72rem;font-weight:600;">${isAsing?'<span style="color:var(--t4)">–</span>':esc(row.nama_petugas||'–')}</td>
        <td class="mono" style="color:var(--t3);">${esc(row.kode_perangkat||'–')}</td>
        <td><span style="font-size:.7rem;font-weight:600;color:var(--n700);">${esc(row.kode_ruangan||'–')}</span> <span style="font-size:.65rem;color:var(--t4);">${esc(row.nama_ruangan||'–')}</span></td>
        <td><span class="stb ${sc}">${st}</span></td>
      </tr>`;
    });
    if(h)tbody.innerHTML=h;
  }).catch(()=>{});
}

function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
</script>

<?= $this->endSection() ?>