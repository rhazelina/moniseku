<?php
// ================================================================
// LOKASI : app/Views/petugas/dashboard.php
// VERSI  : v5.0 — Download jadwal reuse JadwalController::exportExcel
// Standalone view — header & footer sendiri, tanpa sidebar
// ================================================================

$profil            ??= [];
$fotoProfilUrl     ??= null;
$jadwalHariIni     ??= [];
$jadwalBerikutnya  ??= [];
$aktivitasTerakhir ??= [];
$kalenderBulanIni  ??= [];
$bulanAktif        ??= date('Y-m');
$namaBulan         ??= 'Bulan Ini';
$totalJadwalBulan  ??= 0;
$totalScanBulan    ??= 0;
$totalBerhasil     ??= 0;
$totalTerlewat     ??= 0;

$namaPetugas = esc($profil['nama_lengkap'] ?? session()->get('nama_lengkap') ?? 'Petugas');
$username    = esc($profil['username']     ?? session()->get('username')     ?? '-');
$uidRfid     = esc($profil['uid_rfid']     ?? '-');
$statusAktif = $profil['status_aktif']     ?? 'Aktif';

// Inisial avatar
$initials = strtoupper(mb_substr($profil['nama_lengkap'] ?? 'P', 0, 1));
$parts    = explode(' ', trim($profil['nama_lengkap'] ?? ''));
if (count($parts) >= 2) {
    $initials = strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1));
}
$escInitials = esc($initials);

$jam   = (int) date('H');
$salam = $jam < 12 ? 'Selamat Pagi' : ($jam < 15 ? 'Selamat Siang' : ($jam < 18 ? 'Selamat Sore' : 'Selamat Malam'));

$hariID    = ['Sunday'=>'Min','Monday'=>'Sen','Tuesday'=>'Sel',
              'Wednesday'=>'Rab','Thursday'=>'Kam','Friday'=>'Jum','Saturday'=>'Sab'];
$bulanShort= ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei',
              '06'=>'Jun','07'=>'Jul','08'=>'Agu','09'=>'Sep','10'=>'Okt',
              '11'=>'Nov','12'=>'Des'];
$namaBulanArr = [
    '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
    '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
    '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember',
];
[$bY, $bM] = explode('-', $bulanAktif);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Petugas — GKI Bromo</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=DM+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
/* ═══════════════════════════════════════════════════════════
   Dashboard Petugas v5.0
   Font: Plus Jakarta Sans + DM Mono
═══════════════════════════════════════════════════════════ */
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --f:'Plus Jakarta Sans',sans-serif;
  --m:'DM Mono',monospace;
  --bg:#f0f4f9;
  --s1:#ffffff;
  --s2:#f8fafc;
  --bd:#e2e8f0;
  --bd2:#cbd5e1;
  --t1:#0f172a;
  --t2:#334155;
  --t3:#64748b;
  --t4:#94a3b8;
  --blue:#2563eb;
  --blue-d:#1d4ed8;
  --blue-l:#eff6ff;
  --blue-m:#bfdbfe;
  --green:#16a34a;
  --green-l:#f0fdf4;
  --green-m:#bbf7d0;
  --red:#dc2626;
  --red-l:#fef2f2;
  --red-m:#fecaca;
  --amber:#d97706;
  --amber-l:#fffbeb;
  --amber-m:#fde68a;
  --purple:#7c3aed;
  --purple-l:#f5f3ff;
  --navy:#0d1b2e;
  --r:12px;
  --sh:0 1px 3px rgba(15,23,42,.08),0 1px 2px rgba(15,23,42,.05);
  --shm:0 4px 16px rgba(15,23,42,.1);
}
body{font-family:var(--f);background:var(--bg);color:var(--t1);min-height:100vh;font-size:14px;-webkit-font-smoothing:antialiased}
a{text-decoration:none;color:inherit}

/* ── TOPBAR ─────────────────────────────────────────────── */
.db-topbar{
  position:sticky;top:0;z-index:100;
  background:var(--navy);height:56px;
  display:flex;align-items:center;
  padding:0 20px;gap:12px;
  border-bottom:1px solid rgba(255,255,255,.07);
  box-shadow:0 2px 8px rgba(0,0,0,.3);
}
.db-topbar-brand{
  display:flex;align-items:center;gap:9px;flex-shrink:0;
}
.db-topbar-brand img{height:28px;width:auto;object-fit:contain;border-radius:4px}
.db-topbar-brand-txt{font-size:12px;font-weight:800;color:rgba(255,255,255,.7);letter-spacing:.07em;text-transform:uppercase;white-space:nowrap}
.db-topbar-sep{width:1px;height:22px;background:rgba(255,255,255,.1);flex-shrink:0}
.db-topbar-title{font-size:13px;font-weight:700;color:rgba(255,255,255,.85);flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.db-topbar-right{display:flex;align-items:center;gap:8px;flex-shrink:0}
.db-pulse{display:inline-flex;align-items:center;gap:5px;font-size:10px;font-weight:700;color:#4ade80;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.2);padding:3px 9px;border-radius:99px;white-space:nowrap}
.db-pulse span{width:6px;height:6px;border-radius:50%;background:#22c55e;animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.35}}
.db-topbar-clock{font-family:var(--m);font-size:11px;font-weight:500;color:rgba(255,255,255,.45);white-space:nowrap}
.db-logout{display:inline-flex;align-items:center;gap:5px;height:30px;padding:0 12px;border-radius:7px;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.22);color:#fca5a5;font-size:11px;font-weight:700;cursor:pointer;font-family:var(--f);text-decoration:none;transition:all .15s;white-space:nowrap}
.db-logout:hover{background:rgba(239,68,68,.2);color:#fecaca}

/* ── HERO ───────────────────────────────────────────────── */
.db-hero{
  background:var(--navy);
  background-image:
    radial-gradient(ellipse 60% 55% at 80% -5%,rgba(37,99,235,.32) 0%,transparent 60%),
    radial-gradient(ellipse 45% 65% at 5% 110%,rgba(124,58,237,.18) 0%,transparent 60%);
  padding:22px 20px 68px;
  position:relative;overflow:hidden;
}
.db-hero::after{
  content:'';position:absolute;inset:0;pointer-events:none;opacity:.35;
  background:url("data:image/svg+xml,%3Csvg width='48' height='48' viewBox='0 0 48 48' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%234f6791' fill-opacity='0.06'%3E%3Ccircle cx='24' cy='24' r='1'/%3E%3C/g%3E%3C/svg%3E");
}
.db-hero-inner{position:relative;z-index:1;display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;max-width:1200px;margin:0 auto}
.db-hero-left{display:flex;align-items:center;gap:14px;flex:1;min-width:220px}
.db-av-wrap{position:relative;flex-shrink:0}
.db-av{
  width:64px;height:64px;border-radius:50%;
  background:linear-gradient(135deg,var(--blue),var(--purple));
  border:3px solid rgba(255,255,255,.18);
  display:flex;align-items:center;justify-content:center;
  font-size:22px;font-weight:800;color:#fff;
  overflow:hidden;box-shadow:0 4px 18px rgba(37,99,235,.35);
}
.db-av img{width:100%;height:100%;object-fit:cover;display:block}
.db-av-dot{position:absolute;bottom:1px;right:1px;width:14px;height:14px;border-radius:50%;background:#22c55e;border:3px solid var(--navy)}
.db-hero-greeting{font-size:10px;font-weight:700;color:rgba(255,255,255,.42);letter-spacing:.1em;text-transform:uppercase;margin-bottom:3px}
.db-hero-name{font-size:19px;font-weight:900;color:#fff;letter-spacing:-.3px;line-height:1.15;margin-bottom:8px}
.db-chips{display:flex;flex-wrap:wrap;gap:5px}
.db-chip{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:10px;font-weight:700;letter-spacing:.03em;white-space:nowrap}
.db-chip-uid{background:rgba(37,99,235,.22);color:#93c5fd;border:1px solid rgba(37,99,235,.28);font-family:var(--m)}
.db-chip-role{background:rgba(255,255,255,.09);color:rgba(255,255,255,.65);border:1px solid rgba(255,255,255,.1)}
.db-chip-aktif{background:rgba(34,197,94,.16);color:#86efac;border:1px solid rgba(34,197,94,.2)}
.db-hero-right{text-align:right;flex-shrink:0}
.db-hero-time{font-family:var(--m);font-size:30px;font-weight:600;color:#fff;line-height:1;letter-spacing:-.02em}
.db-hero-date{font-size:11px;color:rgba(255,255,255,.45);margin-top:4px}

/* ── BODY ───────────────────────────────────────────────── */
.db-body{
  margin-top:-50px;position:relative;z-index:2;
  padding:0 16px 56px;
  max-width:1240px;margin-left:auto;margin-right:auto;
}
@media(min-width:768px){.db-body{padding:0 24px 60px;margin-top:-50px}}

/* ── STAT CARDS ─────────────────────────────────────────── */
.db-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px}
@media(max-width:880px){.db-stats{grid-template-columns:repeat(2,1fr)}}
@media(max-width:420px) {.db-stats{grid-template-columns:1fr 1fr;gap:8px}}
.db-sc{
  background:var(--s1);border:1px solid var(--bd);border-radius:var(--r);
  box-shadow:var(--sh);padding:15px 14px;
  display:flex;align-items:flex-start;gap:11px;
  transition:transform .18s,box-shadow .18s;
}
.db-sc:hover{transform:translateY(-2px);box-shadow:var(--shm)}
.db-sc-ico{width:40px;height:40px;border-radius:10px;display:grid;place-items:center;font-size:17px;flex-shrink:0}
.db-sc-ico.blue  {background:var(--blue-l);color:var(--blue)}
.db-sc-ico.green {background:var(--green-l);color:var(--green)}
.db-sc-ico.red   {background:var(--red-l);color:var(--red)}
.db-sc-ico.amber {background:var(--amber-l);color:var(--amber)}
.db-sc-val{font-family:var(--m);font-size:24px;font-weight:700;color:var(--t1);line-height:1}
.db-sc-lbl{font-size:11px;color:var(--t3);margin-top:3px;font-weight:600}
.db-sc-sub{font-size:10px;color:var(--t4);margin-top:2px}

/* ── DOWNLOAD BANNER ─────────────────────────────────────── */
.db-dl-banner{
  background:linear-gradient(135deg,#0d2744 0%,#1e3a5f 60%,#163460 100%);
  border:1px solid rgba(96,165,250,.18);
  border-radius:var(--r);
  padding:15px 20px;
  margin-bottom:18px;
  display:flex;align-items:center;gap:16px;flex-wrap:wrap;
  box-shadow:0 2px 10px rgba(37,99,235,.18);
  position:relative;overflow:hidden;
}
.db-dl-banner::before{
  content:'';position:absolute;right:-30px;top:-30px;
  width:120px;height:120px;border-radius:50%;
  background:rgba(37,99,235,.08);pointer-events:none;
}
.db-dl-banner-ico{
  width:42px;height:42px;border-radius:11px;
  background:rgba(37,99,235,.18);border:1px solid rgba(96,165,250,.25);
  display:grid;place-items:center;color:#60a5fa;font-size:18px;flex-shrink:0;
}
.db-dl-banner-text{flex:1;min-width:160px}
.db-dl-banner-title{font-size:13px;font-weight:800;color:#e2e8f0;margin-bottom:2px;display:flex;align-items:center;gap:7px}
.db-dl-banner-sub{font-size:10px;color:rgba(255,255,255,.38);font-weight:500}
.db-dl-banner-sub b{color:rgba(255,255,255,.55)}
.db-dl-banner-btns{display:flex;align-items:center;gap:8px;flex-wrap:wrap;flex-shrink:0}
/* hanya satu tombol: Excel (.xls) */
.db-dl-xls{
  display:inline-flex;align-items:center;gap:7px;
  height:36px;padding:0 18px;border-radius:8px;
  background:#16a34a;color:#fff;border:none;
  font-size:12px;font-weight:800;cursor:pointer;
  font-family:var(--f);white-space:nowrap;
  transition:background .15s;
}
.db-dl-xls:hover{background:#15803d}
.db-dl-bulan-sel{
  height:36px;padding:0 10px;border-radius:8px;
  border:1px solid rgba(96,165,250,.25);
  background:rgba(255,255,255,.07);
  color:#e2e8f0;font-family:var(--m);font-size:12px;
  outline:none;cursor:pointer;
  transition:border-color .15s;
}
.db-dl-bulan-sel:focus{border-color:rgba(96,165,250,.5)}
.db-dl-bulan-sel option{background:#1e3a5f;color:#e2e8f0}

/* ── MAIN GRID ──────────────────────────────────────────── */
.db-grid{display:grid;grid-template-columns:1fr 350px;gap:18px;align-items:start}
@media(max-width:1024px){.db-grid{grid-template-columns:1fr}}

/* ── CARD ───────────────────────────────────────────────── */
.db-card{background:var(--s1);border:1px solid var(--bd);border-radius:var(--r);box-shadow:var(--sh);overflow:hidden;margin-bottom:14px}
.db-card:last-child{margin-bottom:0}
.db-card-hd{display:flex;align-items:center;justify-content:space-between;padding:13px 16px;border-bottom:1px solid var(--bd);gap:10px;flex-wrap:wrap}
.db-card-title{font-size:13px;font-weight:800;color:var(--t1);display:flex;align-items:center;gap:8px}
.db-card-ico{width:26px;height:26px;border-radius:7px;background:var(--blue-l);color:var(--blue);display:grid;place-items:center;font-size:12px;flex-shrink:0}
.db-card-bd{padding:14px 16px}

/* ── TAB NAV ────────────────────────────────────────────── */
.db-tabs{display:flex;gap:3px;background:var(--s2);border-radius:10px;padding:3px;margin-bottom:14px;flex-wrap:nowrap;overflow-x:auto}
.db-tab{flex:1;padding:7px 10px;border:none;border-radius:8px;font-size:11px;font-weight:700;color:var(--t3);background:transparent;cursor:pointer;font-family:var(--f);transition:all .15s;white-space:nowrap;text-align:center;min-width:90px}
.db-tab:hover{color:var(--blue)}
.db-tab.active{background:var(--s1);color:var(--blue);box-shadow:var(--sh)}
.db-pane{display:none}
.db-pane.active{display:block}

/* ── KALENDER ───────────────────────────────────────────── */
.cal-nav{display:flex;align-items:center;gap:6px}
.cal-nav-btn{width:26px;height:26px;border:1px solid var(--bd2);border-radius:6px;background:var(--s2);cursor:pointer;font-size:12px;color:var(--t3);display:grid;place-items:center;transition:all .15s;font-family:var(--f)}
.cal-nav-btn:hover{border-color:var(--blue);color:var(--blue)}
.cal-month-lbl{font-size:12px;font-weight:800;color:var(--t1);min-width:108px;text-align:center}
.cal-wd-row{display:grid;grid-template-columns:repeat(7,1fr);gap:3px;margin-bottom:4px}
.cal-wd-cell{text-align:center;font-size:9px;font-weight:800;letter-spacing:.07em;text-transform:uppercase;color:var(--t4);padding:4px 0}
.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:3px}
.cal-cell{
  border-radius:9px;min-height:46px;
  display:flex;flex-direction:column;align-items:center;
  padding:6px 3px 5px;cursor:default;
  position:relative;transition:background .15s;background:#f8fafc;
  border:1.5px solid transparent;
}
.cal-cell.empty{background:transparent;border-color:transparent}
.cal-cell.weekend{background:#fff8f8}
.cal-cell.today{background:var(--blue-l);border-color:var(--blue)}
.cal-cell.hasjd{
  background:var(--green-l);border-color:var(--green-m);cursor:pointer;
}
.cal-cell.hasjd:hover{background:#dcfce7}
.cal-cell.nojd{background:#fef2f2;border-color:#fecaca}
.cal-cell.today.hasjd{background:#d1fae5;border-color:var(--green)}
.cal-num{font-size:12px;font-weight:700;color:var(--t1);line-height:1}
.cal-cell.weekend .cal-num{color:var(--red)}
.cal-cell.today   .cal-num{color:var(--blue)}
.cal-cell.hasjd   .cal-num{color:var(--green)}
.cal-dot{width:5px;height:5px;border-radius:50%;background:var(--green);margin-top:4px;flex-shrink:0}
.cal-cell.nojd .cal-dot{background:var(--red)}
.cal-detail{
  margin-top:12px;padding:11px 13px;
  background:var(--green-l);border:1px solid var(--green-m);
  border-radius:10px;display:none;
}
.cal-detail-title{font-size:11px;font-weight:800;color:var(--green);margin-bottom:8px;display:flex;align-items:center;gap:6px}
.cal-legend{display:flex;gap:12px;font-size:10px;color:var(--t3);font-weight:600;margin-bottom:10px;flex-wrap:wrap}
.cal-legend span{display:flex;align-items:center;gap:5px}
.cal-legend .dot{width:9px;height:9px;border-radius:2px;flex-shrink:0}

/* ── JADWAL CARD ────────────────────────────────────────── */
.db-jc{border:1.5px solid var(--bd);border-radius:10px;padding:12px 14px;margin-bottom:10px;position:relative;overflow:hidden;transition:box-shadow .18s}
.db-jc:hover{box-shadow:var(--shm)}
.db-jc::before{content:'';position:absolute;left:0;top:0;bottom:0;width:4px}
.db-jc.pagi  {border-color:#fde68a}.db-jc.pagi::before{background:#f59e0b}
.db-jc.siang {border-color:var(--blue-m)}.db-jc.siang::before{background:var(--blue)}
.db-jc.malam {border-color:#ddd6fe}.db-jc.malam::before{background:var(--purple)}
.db-jc-name{font-size:13px;font-weight:800;color:var(--t1);margin-bottom:4px;display:flex;align-items:center;gap:6px}
.db-jc-jam{font-family:var(--m);font-size:12px;color:var(--t3);margin-bottom:2px}
.db-jc-cat{font-size:10px;color:var(--t4)}
.db-jc-status{position:absolute;top:11px;right:13px;font-size:9px;font-weight:700;padding:2px 8px;border-radius:10px;letter-spacing:.04em}
.ss-belum   {background:#f1f5f9;color:var(--t3)}
.ss-berjalan{background:var(--green-l);color:var(--green)}
.ss-selesai {background:#e0e7ff;color:#4338ca}

/* ── AKTIVITAS ──────────────────────────────────────────── */
.db-akt{display:flex;align-items:center;gap:11px;padding:9px 0;border-bottom:1px solid var(--bd)}
.db-akt:last-child{border-bottom:none}
.db-akt-ico{width:34px;height:34px;border-radius:9px;display:grid;place-items:center;font-size:15px;flex-shrink:0}
.akt-sesuai{background:var(--green-l);color:var(--green)}
.akt-diluar{background:var(--amber-l);color:var(--amber)}
.akt-tidak {background:var(--red-l);color:var(--red)}
.db-akt-info{flex:1;min-width:0}
.db-akt-lok{font-size:12px;font-weight:700;color:var(--t1)}
.db-akt-meta{font-size:10px;color:var(--t4);margin-top:1px;display:flex;gap:8px;flex-wrap:wrap}
.db-akt-time{font-family:var(--m);font-size:11px;font-weight:600;color:var(--t3);white-space:nowrap;text-align:right;align-self:flex-start}
.db-badge{display:inline-flex;align-items:center;gap:3px;padding:2px 7px;border-radius:4px;font-size:9px;font-weight:700;font-family:var(--m);letter-spacing:.03em;white-space:nowrap}
.bdg-sesuai{background:var(--green-l);color:var(--green);border:1px solid var(--green-m)}
.bdg-diluar{background:var(--amber-l);color:var(--amber);border:1px solid var(--amber-m)}
.bdg-tidak {background:var(--red-l);color:var(--red);border:1px solid var(--red-m)}

/* ── JADWAL BERIKUTNYA ──────────────────────────────────── */
.db-next{display:flex;align-items:center;gap:11px;padding:8px 0;border-bottom:1px solid var(--bd)}
.db-next:last-child{border-bottom:none}
.db-next-date{min-width:44px;height:44px;border-radius:10px;background:var(--blue-l);border:1px solid var(--blue-m);display:flex;flex-direction:column;align-items:center;justify-content:center;flex-shrink:0}
.db-next-dd{font-size:15px;font-weight:900;color:var(--blue);line-height:1}
.db-next-mm{font-size:9px;font-weight:700;color:var(--t3);text-transform:uppercase;letter-spacing:.04em}
.db-next-shift{font-size:12px;font-weight:800;color:var(--t1)}
.db-next-jam  {font-family:var(--m);font-size:10px;color:var(--t4);margin-top:2px}
.db-next-hari {font-size:9px;color:var(--t3);font-weight:600;text-transform:uppercase;letter-spacing:.05em}

/* ── PROFIL CARD ────────────────────────────────────────── */
.db-profil-av{width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,var(--blue),var(--purple));display:flex;align-items:center;justify-content:center;font-size:17px;font-weight:800;color:#fff;overflow:hidden;flex-shrink:0;border:2px solid var(--blue-m)}
.db-profil-av img{width:100%;height:100%;object-fit:cover;display:block}
.db-info-row{display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid var(--bd);font-size:11px}
.db-info-row:last-child{border-bottom:none}
.db-info-k{color:var(--t3);font-weight:600;min-width:70px;flex-shrink:0}
.db-info-v{color:var(--t1);font-weight:700;word-break:break-all}

/* ── ANALISIS PROGRESS ──────────────────────────────────── */
.db-prog{margin-bottom:13px}
.db-prog-lbl{display:flex;justify-content:space-between;font-size:11px;font-weight:700;color:var(--t2);margin-bottom:5px}
.db-prog-lbl span{color:var(--t3);font-weight:400}
.db-prog-bar{height:8px;border-radius:99px;background:var(--bd);overflow:hidden}
.db-prog-fill{height:100%;border-radius:99px;transition:width .6s ease}
.pf-green{background:linear-gradient(90deg,var(--green),#4ade80)}
.pf-blue {background:linear-gradient(90deg,var(--blue),#60a5fa)}

/* ── EMPTY ──────────────────────────────────────────────── */
.db-empty{text-align:center;padding:28px 16px;color:var(--t4);font-size:12px}
.db-empty i{font-size:30px;display:block;margin-bottom:8px;opacity:.3}

/* ── BTN ────────────────────────────────────────────────── */
.db-btn{display:inline-flex;align-items:center;gap:6px;height:34px;padding:0 14px;border-radius:8px;border:none;font-size:12px;font-weight:700;cursor:pointer;font-family:var(--f);text-decoration:none;white-space:nowrap;transition:all .15s}
.db-btn-red{background:#fff;color:var(--red);border:1.5px solid var(--red-m)}.db-btn-red:hover{background:var(--red-l)}

/* ── FOOTER ─────────────────────────────────────────────── */
.db-footer{background:var(--s1);border-top:1px solid var(--bd);height:40px;display:flex;align-items:center;padding:0 24px;font-size:10px;color:var(--t4);flex-wrap:wrap;gap:8px}
.db-footer-dot{width:5px;height:5px;border-radius:50%;background:#22c55e;margin-left:auto;flex-shrink:0}

/* ── RESPONSIVE ─────────────────────────────────────────── */
@media(max-width:640px){
  .db-hero-time{font-size:22px}
  .db-hero-name{font-size:16px}
  .db-av{width:52px;height:52px;font-size:18px}
  .db-hero-right{display:none}
  .db-topbar-clock{display:none}
  .db-topbar-brand-txt{display:none}
  .db-dl-banner{flex-direction:column;align-items:flex-start}
  .db-dl-banner-btns{width:100%}
  .db-dl-xls{flex:1;justify-content:center}
}
</style>
</head>
<body>

<!-- ══ TOPBAR ════════════════════════════════════════════ -->
<header class="db-topbar">
  <div class="db-topbar-brand">
    <img src="<?= base_url('img/logogki.png') ?>" alt="GKI"
         onerror="this.style.display='none'">
    <div class="db-topbar-brand-txt">GKI Bromo</div>
  </div>
  <div class="db-topbar-sep"></div>
  <div class="db-topbar-title">
    <i class="bi bi-shield-shaded" style="color:#60a5fa;margin-right:6px"></i>
    Dashboard Petugas Patroli
  </div>
  <div class="db-topbar-right">
    <div class="db-pulse"><span></span>Online</div>
    <div class="db-topbar-clock" id="dbTopClock">--:--:--</div>
    <a href="<?= base_url('petugas/logout') ?>" class="db-logout">
      <i class="bi bi-box-arrow-right"></i><span>Logout</span>
    </a>
  </div>
</header>

<!-- ══ HERO ══════════════════════════════════════════════ -->
<div class="db-hero">
  <div class="db-hero-inner">
    <div class="db-hero-left">
      <div class="db-av-wrap">
        <div class="db-av">
          <?php if ($fotoProfilUrl): ?>
            <img src="<?= esc($fotoProfilUrl) ?>"
                 alt="Foto"
                 loading="lazy"
                 onerror="this.parentElement.innerHTML='<?= $escInitials ?>'">
          <?php else: ?>
            <?= $initials ?>
          <?php endif; ?>
        </div>
        <div class="db-av-dot" title="Akun Aktif"></div>
      </div>
      <div>
        <div class="db-hero-greeting"><?= $salam ?>, Petugas</div>
        <div class="db-hero-name"><?= $namaPetugas ?></div>
        <div class="db-chips">
          <?php if ($uidRfid !== '-'): ?>
          <span class="db-chip db-chip-uid">
            <i class="bi bi-credit-card-2-front"></i><?= $uidRfid ?>
          </span>
          <?php endif; ?>
          <span class="db-chip db-chip-role">
            <i class="bi bi-person-badge"></i>Petugas Patroli
          </span>
          <span class="db-chip db-chip-aktif">
            <i class="bi bi-circle-fill" style="font-size:5px"></i><?= esc($statusAktif) ?>
          </span>
        </div>
      </div>
    </div>
    <div class="db-hero-right">
      <div class="db-hero-time" id="dbHeroClock">--:--:--</div>
      <div class="db-hero-date"><?= date('l, d F Y') ?></div>
    </div>
  </div>
</div>

<!-- ══ BODY ═══════════════════════════════════════════════ -->
<div class="db-body">

  <!-- ── STAT CARDS ────────────────────────────────────── -->
  <div class="db-stats">
    <div class="db-sc">
      <div class="db-sc-ico blue"><i class="bi bi-calendar-check"></i></div>
      <div>
        <div class="db-sc-val"><?= $totalJadwalBulan ?></div>
        <div class="db-sc-lbl">Jadwal Bulan Ini</div>
        <div class="db-sc-sub"><?= $namaBulan ?></div>
      </div>
    </div>
    <div class="db-sc">
      <div class="db-sc-ico amber"><i class="bi bi-calendar-day"></i></div>
      <div>
        <div class="db-sc-val"><?= count($jadwalHariIni) ?></div>
        <div class="db-sc-lbl">Jadwal Hari Ini</div>
        <div class="db-sc-sub"><?= date('d F Y') ?></div>
      </div>
    </div>
    <div class="db-sc">
      <div class="db-sc-ico green"><i class="bi bi-check2-circle"></i></div>
      <div>
        <div class="db-sc-val"><?= $totalBerhasil ?></div>
        <div class="db-sc-lbl">Scan Berhasil</div>
        <div class="db-sc-sub">dari <?= $totalScanBulan ?> total</div>
      </div>
    </div>
    <div class="db-sc">
      <div class="db-sc-ico red"><i class="bi bi-exclamation-triangle"></i></div>
      <div>
        <div class="db-sc-val"><?= $totalTerlewat ?></div>
        <div class="db-sc-lbl">Titik Terlewat</div>
        <div class="db-sc-sub">bulan ini</div>
      </div>
    </div>
  </div>

  <!-- ── DOWNLOAD BANNER ───────────────────────────────── -->
  <div class="db-dl-banner">
    <div class="db-dl-banner-ico">
      <i class="bi bi-file-earmark-excel"></i>
    </div>
    <div class="db-dl-banner-text">
      <div class="db-dl-banner-title">
        <i class="bi bi-download"></i>
        Download Jadwal Dinas Security
      </div>
      <div class="db-dl-banner-sub">
        Format Excel matriks — <b>Petugas × Tanggal</b> dengan kode shift
        <b>P</b>(Pagi) / <b>S</b>(Siang) / <b>M</b>(Malam) — identik dengan halaman Jadwal
      </div>
    </div>
    <div class="db-dl-banner-btns">
      <!-- Pilih bulan langsung di banner -->
      <select class="db-dl-bulan-sel" id="dlBulanSelect">
        <?php
        // Tampilkan 12 bulan: 6 bulan lalu s/d 5 bulan ke depan
        for ($i = -6; $i <= 5; $i++) {
            $ts  = strtotime("$i months", mktime(0,0,0,(int)$bM,1,(int)$bY));
            $val = date('Y-m', $ts);
            $mn  = $namaBulanArr[date('m', $ts)] ?? date('M', $ts);
            $yr2 = date('Y', $ts);
            $sel = ($val === $bulanAktif) ? 'selected' : '';
            echo "<option value=\"$val\" $sel>$mn $yr2</option>";
        }
        ?>
      </select>
      <button class="db-dl-xls" onclick="doDownloadExcel()">
        <i class="bi bi-file-earmark-spreadsheet"></i>
        Unduh Excel (.xls)
      </button>
    </div>
  </div>

  <!-- ── MAIN GRID ─────────────────────────────────────── -->
  <div class="db-grid">

    <!-- ─── KOLOM KIRI ──────────────────────────────────── -->
    <div>
      <div class="db-tabs">
        <button class="db-tab active" onclick="dbTab(event,'tabKal')">
          <i class="bi bi-calendar3"></i> Kalender
        </button>
        <button class="db-tab" onclick="dbTab(event,'tabAkt')">
          <i class="bi bi-clock-history"></i> Aktivitas
        </button>
        <button class="db-tab" onclick="dbTab(event,'tabAnal')">
          <i class="bi bi-graph-up"></i> Analisis
        </button>
      </div>

      <!-- TAB KALENDER -->
      <div id="tabKal" class="db-pane active">
        <div class="db-card">
          <div class="db-card-hd">
            <div class="db-card-title">
              <div class="db-card-ico"><i class="bi bi-calendar3"></i></div>
              Kalender Jadwal Bulanan
            </div>
            <div class="cal-nav">
              <button class="cal-nav-btn" onclick="calPrev()">
                <i class="bi bi-chevron-left"></i>
              </button>
              <div class="cal-month-lbl" id="calLabel"><?= $namaBulan ?></div>
              <button class="cal-nav-btn" onclick="calNext()">
                <i class="bi bi-chevron-right"></i>
              </button>
            </div>
          </div>
          <div class="db-card-bd">
            <!-- Legenda -->
            <div class="cal-legend">
              <span><span class="dot" style="background:var(--green)"></span>Ada Jadwal</span>
              <span><span class="dot" style="background:var(--red)"></span>Tidak Ada</span>
              <span><span class="dot" style="background:var(--blue)"></span>Hari Ini</span>
              <span><span class="dot" style="background:#fca5a5"></span>Akhir Pekan</span>
            </div>
            <!-- Header hari -->
            <div class="cal-wd-row">
              <?php foreach (['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $wd): ?>
              <div class="cal-wd-cell"><?= $wd ?></div>
              <?php endforeach; ?>
            </div>
            <!-- Grid kalender -->
            <div class="cal-grid" id="calGrid"></div>
            <!-- Detail hari dipilih -->
            <div class="cal-detail" id="calDetail">
              <div class="cal-detail-title">
                <i class="bi bi-calendar-check"></i>
                <span id="calDetailDate">—</span>
              </div>
              <div id="calDetailContent"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB AKTIVITAS -->
      <div id="tabAkt" class="db-pane">
        <div class="db-card">
          <div class="db-card-hd">
            <div class="db-card-title">
              <div class="db-card-ico" style="background:var(--amber-l);color:var(--amber)">
                <i class="bi bi-clock-history"></i>
              </div>
              Kunjungan Terbaru
            </div>
            <span style="font-size:10px;color:var(--t4);font-weight:600">8 terakhir</span>
          </div>
          <div class="db-card-bd" style="padding-bottom:8px">
            <?php if (empty($aktivitasTerakhir)): ?>
            <div class="db-empty">
              <i class="bi bi-geo"></i>Belum ada data kunjungan
            </div>
            <?php else: foreach ($aktivitasTerakhir as $a):
              $sv     = $a['status_validasi'] ?? '';
              $icCls  = ($sv === 'Sesuai') ? 'akt-sesuai' : (in_array($sv,['Di Luar Jadwal','Tidak Terjadwal']) ? 'akt-diluar' : 'akt-tidak');
              $ico    = ($sv === 'Sesuai') ? 'bi-check2-circle' : (in_array($sv,['Di Luar Jadwal','Tidak Terjadwal']) ? 'bi-clock-history' : 'bi-x-circle');
              $bdgCls = ($sv === 'Sesuai') ? 'bdg-sesuai' : (in_array($sv,['Di Luar Jadwal','Tidak Terjadwal']) ? 'bdg-diluar' : 'bdg-tidak');
            ?>
            <div class="db-akt">
              <div class="db-akt-ico <?= $icCls ?>"><i class="bi <?= $ico ?>"></i></div>
              <div class="db-akt-info">
                <div class="db-akt-lok">
                  <?= esc($a['nama_ruangan'] ?? '—') ?>
                  <small style="font-family:var(--m);font-size:9px;color:var(--t4);margin-left:5px"><?= esc($a['kode_ruangan'] ?? '') ?></small>
                </div>
                <div class="db-akt-meta">
                  <span><i class="bi bi-router" style="font-size:9px"></i> <?= esc($a['kode_perangkat'] ?? '—') ?></span>
                  <span class="db-badge <?= $bdgCls ?>"><?= esc($sv ?: '—') ?></span>
                </div>
              </div>
              <div class="db-akt-time">
                <?= date('H:i', strtotime($a['waktu_tap'])) ?><br>
                <span style="font-size:9px;color:var(--t4)"><?= date('d/m', strtotime($a['waktu_tap'])) ?></span>
              </div>
            </div>
            <?php endforeach; endif; ?>
          </div>
        </div>
      </div>

      <!-- TAB ANALISIS -->
      <div id="tabAnal" class="db-pane">
        <?php
        $pctBerhasil = $totalScanBulan > 0 ? round(($totalBerhasil / $totalScanBulan) * 100) : 0;
        $th          = $totalBerhasil + $totalTerlewat;
        $pctCov      = $th > 0 ? round(($totalBerhasil / $th) * 100, 1) : 0;
        ?>
        <div class="db-card">
          <div class="db-card-hd">
            <div class="db-card-title">
              <div class="db-card-ico" style="background:var(--purple-l);color:var(--purple)">
                <i class="bi bi-graph-up"></i>
              </div>
              Analisis Patroli — <?= $namaBulan ?>
            </div>
          </div>
          <div class="db-card-bd">
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:16px">
              <div style="text-align:center;padding:12px 6px;background:var(--green-l);border:1px solid var(--green-m);border-radius:10px">
                <div style="font-size:22px;font-weight:900;color:var(--green);font-family:var(--m)"><?= $totalBerhasil ?></div>
                <div style="font-size:10px;color:var(--t3);font-weight:700;margin-top:2px">Berhasil</div>
              </div>
              <div style="text-align:center;padding:12px 6px;background:var(--red-l);border:1px solid var(--red-m);border-radius:10px">
                <div style="font-size:22px;font-weight:900;color:var(--red);font-family:var(--m)"><?= $totalTerlewat ?></div>
                <div style="font-size:10px;color:var(--t3);font-weight:700;margin-top:2px">Terlewat</div>
              </div>
              <div style="text-align:center;padding:12px 6px;background:var(--blue-l);border:1px solid var(--blue-m);border-radius:10px">
                <div style="font-size:22px;font-weight:900;color:var(--blue);font-family:var(--m)"><?= $totalScanBulan ?></div>
                <div style="font-size:10px;color:var(--t3);font-weight:700;margin-top:2px">Total Scan</div>
              </div>
            </div>
            <div class="db-prog">
              <div class="db-prog-lbl">Penyelesaian Patroli <span><?= $pctCov ?>%</span></div>
              <div class="db-prog-bar"><div class="db-prog-fill pf-green" style="width:<?= min($pctCov,100) ?>%"></div></div>
            </div>
            <div class="db-prog">
              <div class="db-prog-lbl">Scan Valid dari Total <span><?= $pctBerhasil ?>%</span></div>
              <div class="db-prog-bar"><div class="db-prog-fill pf-blue" style="width:<?= min($pctBerhasil,100) ?>%"></div></div>
            </div>
            <?php if ($totalTerlewat > 0): ?>
            <div style="font-size:11px;color:var(--red);font-weight:700;padding:8px 11px;background:var(--red-l);border:1px solid var(--red-m);border-radius:8px;margin-top:4px">
              <i class="bi bi-exclamation-triangle" style="margin-right:6px"></i>
              Anda melewatkan <strong><?= $totalTerlewat ?></strong> titik patroli bulan ini.
            </div>
            <?php else: ?>
            <div style="font-size:11px;color:var(--green);font-weight:700;padding:8px 11px;background:var(--green-l);border:1px solid var(--green-m);border-radius:8px;margin-top:4px">
              <i class="bi bi-check-circle" style="margin-right:6px"></i>
              Semua titik patroli berhasil dikunjungi bulan ini!
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div><!-- /kiri -->

    <!-- ─── KOLOM KANAN ──────────────────────────────────── -->
    <div>

      <!-- Jadwal Hari Ini -->
      <div class="db-card">
        <div class="db-card-hd">
          <div class="db-card-title">
            <div class="db-card-ico" style="background:var(--amber-l);color:var(--amber)">
              <i class="bi bi-calendar-day"></i>
            </div>
            Jadwal Hari Ini
          </div>
          <span style="font-size:10px;font-weight:700;color:var(--t4)"><?= date('d F Y') ?></span>
        </div>
        <div class="db-card-bd" style="padding-bottom:6px">
          <?php if (empty($jadwalHariIni)): ?>
          <div class="db-empty"><i class="bi bi-calendar-x"></i>Tidak ada jadwal hari ini</div>
          <?php else: foreach ($jadwalHariIni as $j):
            $sk    = strtolower($j['nama_shift'] ?? 'pagi');
            $em    = match($sk) {'pagi'=>'🌅','siang'=>'🌇','malam'=>'🌙',default=>'⏰'};
            $spCls = match($j['status_shift'] ?? '') {'Berjalan'=>'ss-berjalan','Selesai'=>'ss-selesai',default=>'ss-belum'};
          ?>
          <div class="db-jc <?= $sk ?>">
            <div class="db-jc-name"><?= $em ?> <?= esc($j['nama_shift']) ?></div>
            <div class="db-jc-jam">
              <i class="bi bi-clock" style="font-size:10px"></i>
              <?= substr($j['jam_mulai'],0,5) ?> – <?= substr($j['jam_selesai'],0,5) ?>
            </div>
            <?php if (!empty($j['catatan'])): ?>
            <div class="db-jc-cat"><i class="bi bi-sticky" style="font-size:9px"></i> <?= esc($j['catatan']) ?></div>
            <?php endif; ?>
            <span class="db-jc-status <?= $spCls ?>"><?= esc($j['status_shift'] ?? 'Belum Mulai') ?></span>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <!-- Jadwal Berikutnya -->
      <div class="db-card">
        <div class="db-card-hd">
          <div class="db-card-title">
            <div class="db-card-ico"><i class="bi bi-calendar-forward"></i></div>
            Jadwal Berikutnya
          </div>
          <span style="font-size:10px;color:var(--t4);font-weight:600">7 hari ke depan</span>
        </div>
        <div class="db-card-bd" style="padding-bottom:8px">
          <?php if (empty($jadwalBerikutnya)): ?>
          <div class="db-empty"><i class="bi bi-calendar2"></i>Tidak ada jadwal berikutnya</div>
          <?php else: foreach ($jadwalBerikutnya as $j):
            $hs = $hariID[date('l', strtotime($j['tanggal']))] ?? '—';
            $bl = $bulanShort[date('m', strtotime($j['tanggal']))] ?? date('M', strtotime($j['tanggal']));
            $em = match(strtolower($j['nama_shift'] ?? '')) {'pagi'=>'🌅','siang'=>'🌇','malam'=>'🌙',default=>'⏰'};
          ?>
          <div class="db-next">
            <div class="db-next-date">
              <div class="db-next-dd"><?= date('d', strtotime($j['tanggal'])) ?></div>
              <div class="db-next-mm"><?= $bl ?></div>
            </div>
            <div>
              <div class="db-next-hari"><?= $hs ?></div>
              <div class="db-next-shift"><?= $em ?> <?= esc($j['nama_shift']) ?></div>
              <div class="db-next-jam"><?= substr($j['jam_mulai'],0,5) ?> – <?= substr($j['jam_selesai'],0,5) ?></div>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <!-- Profil -->
      <div class="db-card">
        <div class="db-card-hd">
          <div class="db-card-title">
            <div class="db-card-ico" style="background:var(--purple-l);color:var(--purple)">
              <i class="bi bi-person-circle"></i>
            </div>
            Profil Saya
          </div>
        </div>
        <div class="db-card-bd">
          <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
            <div class="db-profil-av">
              <?php if ($fotoProfilUrl): ?>
                <img src="<?= esc($fotoProfilUrl) ?>"
                     alt="Foto"
                     loading="lazy"
                     onerror="this.parentElement.innerHTML='<?= $escInitials ?>'">
              <?php else: ?>
                <?= $initials ?>
              <?php endif; ?>
            </div>
            <div>
              <div style="font-size:14px;font-weight:800;color:var(--t1)"><?= $namaPetugas ?></div>
              <div style="font-size:11px;color:var(--t3)">@<?= $username ?></div>
            </div>
          </div>
          <div class="db-info-row">
            <i class="bi bi-credit-card-2-front" style="color:var(--blue);font-size:13px;flex-shrink:0"></i>
            <span class="db-info-k">UID RFID</span>
            <span class="db-info-v" style="font-family:var(--m);font-size:11px"><?= $uidRfid ?></span>
          </div>
          <div class="db-info-row">
            <i class="bi bi-shield-check" style="color:var(--blue);font-size:13px;flex-shrink:0"></i>
            <span class="db-info-k">Status</span>
            <span class="db-info-v"><?= esc($statusAktif) ?></span>
          </div>
          <div style="margin-top:12px">
            <a href="<?= base_url('petugas/logout') ?>" class="db-btn db-btn-red" style="width:100%;justify-content:center">
              <i class="bi bi-box-arrow-right"></i> Logout
            </a>
          </div>
        </div>
      </div>

    </div><!-- /kanan -->
  </div><!-- /.db-grid -->
</div><!-- /.db-body -->

<!-- ══ FOOTER ════════════════════════════════════════════ -->
<footer class="db-footer">
  <span>© <?= date('Y') ?> GKI Bromo Malang — Sistem Monitoring RFID Patroli</span>
  <div class="db-footer-dot"></div>
  <span>All systems operational</span>
</footer>

<script>
/* ── CONFIG ─────────────────────────────────────────────── */
var CAL_DATA  = <?= json_encode($kalenderBulanIni, JSON_UNESCAPED_UNICODE) ?>;
var CAL_TODAY = '<?= date('Y-m-d') ?>';
var CAL_BASE  = '<?= base_url() ?>';
var calY = <?= (int)$bY ?>;
var calM = <?= (int)$bM ?>;
var calKd = CAL_DATA;

var BULAN_NAMA = {
  1:'Januari',2:'Februari',3:'Maret',4:'April',
  5:'Mei',6:'Juni',7:'Juli',8:'Agustus',
  9:'September',10:'Oktober',11:'November',12:'Desember'
};
var HARI_NAMA = {0:'Minggu',1:'Senin',2:'Selasa',3:'Rabu',4:'Kamis',5:'Jumat',6:'Sabtu'};

function pad2(n){ return String(n).padStart(2,'0'); }

/* ── LIVE CLOCK ──────────────────────────────────────────── */
(function(){
  function tick(){
    var d = new Date();
    var t = [d.getHours(),d.getMinutes(),d.getSeconds()].map(function(n){ return pad2(n); }).join(':');
    var h = document.getElementById('dbHeroClock');
    var c = document.getElementById('dbTopClock');
    if(h) h.textContent = t;
    if(c) c.textContent = t;
  }
  tick(); setInterval(tick, 1000);
})();

/* ── TAB ────────────────────────────────────────────────── */
function dbTab(ev, id){
  document.querySelectorAll('.db-tab').forEach(function(t){ t.classList.remove('active'); });
  document.querySelectorAll('.db-pane').forEach(function(p){ p.classList.remove('active'); });
  ev.currentTarget.classList.add('active');
  document.getElementById(id).classList.add('active');
}

/* ── DOWNLOAD EXCEL ──────────────────────────────────────── */
// Pakai route yang sama dengan JadwalController::exportExcel
// GET /jadwal/export-excel?month=YYYY-MM
function doDownloadExcel(){
  var sel   = document.getElementById('dlBulanSelect');
  var month = sel ? sel.value : (calY + '-' + pad2(calM));
  if(!month){ alert('Pilih bulan terlebih dahulu.'); return; }
  // Langsung download — buka di tab baru tidak diperlukan untuk .xls
  var url = CAL_BASE + 'jadwal/export-excel?month=' + month;
  var a   = document.createElement('a');
  a.href  = url;
  a.download = '';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
}

/* ── KALENDER ────────────────────────────────────────────── */
function renderCal(){
  var lbl  = document.getElementById('calLabel');
  var grid = document.getElementById('calGrid');
  lbl.textContent = BULAN_NAMA[calM] + ' ' + calY;

  var mm       = pad2(calM);
  var days     = new Date(calY, calM, 0).getDate();
  var firstDow = new Date(calY, calM - 1, 1).getDay();

  var html = '';
  for(var i=0; i<firstDow; i++) html += '<div class="cal-cell empty"></div>';
  for(var d=1; d<=days; d++){
    var ds  = calY + '-' + mm + '-' + pad2(d);
    var dow = new Date(calY, calM-1, d).getDay();
    var isW = (dow===0||dow===6);
    var isT = (ds===CAL_TODAY);
    var hasJ = !!calKd[ds];
    var cls = 'cal-cell'
      + (isW  ? ' weekend' : '')
      + (isT  ? ' today'   : '')
      + (hasJ ? ' hasjd'   : ' nojd');
    var onclick = hasJ ? ' onclick="calSelect(\''+ds+'\')"' : '';
    html += '<div class="'+cls+'"'+onclick+'>'
          + '<div class="cal-num">'+d+'</div>'
          + '<div class="cal-dot"></div>'
          + '</div>';
  }
  grid.innerHTML = html;
  document.getElementById('calDetail').style.display = 'none';
}

function calSelect(ds){
  var det = document.getElementById('calDetail');
  var dtl = document.getElementById('calDetailDate');
  var con = document.getElementById('calDetailContent');
  var dObj = new Date(ds+'T00:00:00');
  dtl.textContent = HARI_NAMA[dObj.getDay()]+', '
    + dObj.getDate()+' '+BULAN_NAMA[dObj.getMonth()+1]+' '+dObj.getFullYear();

  var data = calKd[ds];
  if(data){
    var statArr  = (data.status_list     || '').split(',');
    var shiftArr = (data.nama_shift_list || '').split(', ');
    var rows = shiftArr.map(function(sh,i){
      var st  = statArr[i] || '—';
      var col = st==='Selesai'  ? 'color:#16a34a;font-weight:700'
              : st==='Berjalan' ? 'color:#2563eb;font-weight:700'
              : 'color:#94a3b8';
      return '<div style="display:flex;align-items:center;gap:8px;padding:5px 0;border-bottom:1px solid #bbf7d0">'
           + '<i class="bi bi-clock" style="font-size:10px;color:#16a34a"></i>'
           + '<span style="font-size:12px;font-weight:700;color:#0f172a">'+sh+'</span>'
           + '<span style="font-size:10px;'+col+';margin-left:auto">'+st+'</span>'
           + '</div>';
    }).join('');
    con.innerHTML = rows;
    det.style.background  = 'var(--green-l)';
    det.style.borderColor = 'var(--green-m)';
  } else {
    con.innerHTML = '<div style="font-size:12px;color:var(--red);font-weight:600">'
      + '<i class="bi bi-x-circle" style="margin-right:6px"></i>'
      + 'Tidak ada jadwal pada tanggal ini.</div>';
    det.style.background  = 'var(--red-l)';
    det.style.borderColor = 'var(--red-m)';
  }
  det.style.display = 'block';
}

function calPrev(){
  calM--;
  if(calM<1){ calM=12; calY--; }
  loadCalData();
}
function calNext(){
  calM++;
  if(calM>12){ calM=1; calY++; }
  loadCalData();
}
function loadCalData(){
  var bs = calY+'-'+pad2(calM);
  fetch(CAL_BASE+'petugas/kalender?bulan='+bs,
    {headers:{'X-Requested-With':'XMLHttpRequest'}})
  .then(function(r){ return r.json(); })
  .then(function(d){ calKd = d; renderCal(); })
  .catch(function(){ calKd = {}; renderCal(); });
}

renderCal();
</script>
</body>
</html>