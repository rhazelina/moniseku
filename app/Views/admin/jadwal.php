<?= $this->extend('layouts/adminkit_template') ?>
<?= $this->section('content') ?>

<style>
/* ══════════════════════════════════════════════════════════
   JADWAL PATROLI  —  v2.2  (no regu, 2 petugas per shift)
   Font: Outfit (display) + JetBrains Mono (data)
══════════════════════════════════════════════════════════ */
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap');

:root {
    --jf: 'Outfit', sans-serif;
    --jm: 'JetBrains Mono', monospace;
    --surface: #ffffff;
    --bg: #f0f4f9;
    --border: #e2e8f0;
    --text-1: #0f172a;
    --text-2: #334155;
    --text-3: #64748b;
    --accent: #2563eb;
    --accent-d: #1d4ed8;
    --green: #16a34a;
    --amber: #d97706;
    --red: #dc2626;
    --purple: #7c3aed;
    --navy: #0d1b2e;
    --r: 10px;
}
.jdw * { font-family: var(--jf); box-sizing: border-box; }

/* ── PAGE HEADER ── */
.jdw-ph {
    display: flex; align-items: flex-start;
    justify-content: space-between; gap: 12px;
    margin-bottom: 22px; flex-wrap: wrap;
}
.jdw-ph h2 { font-size: 22px; font-weight: 800; color: var(--text-1); margin: 0; letter-spacing: -.4px; }
.jdw-ph p  { font-size: 13px; color: var(--text-3); margin: 3px 0 0; }

/* ── GRID LAYOUT ── */
.jdw-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 20px;
    align-items: start;
}
@media (max-width: 1100px) { .jdw-grid { grid-template-columns: 1fr; } }

/* ── CARD ── */
.jdw-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
    margin-bottom: 16px;
}
.jdw-card-hd {
    display: flex; align-items: center;
    justify-content: space-between;
    padding: 13px 16px;
    border-bottom: 1px solid var(--border);
    font-size: 13px; font-weight: 700; color: var(--text-1);
    flex-wrap: wrap; gap: 10px;
}
.jdw-card-bd { padding: 16px; }

/* ── MONTH NAV ── */
.jdw-month-nav {
    display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
}
.jdw-month-nav input[type=month] {
    border: 1px solid var(--border); border-radius: 8px;
    padding: 7px 12px; font-size: 13px;
    font-family: var(--jm); color: var(--text-1);
    background: var(--bg); outline: none;
    transition: border-color .18s;
}
.jdw-month-nav input[type=month]:focus { border-color: var(--accent); }

/* ── BUTTONS ── */
.jbtn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 14px; border-radius: 8px; border: 1px solid var(--border);
    font-size: 12px; font-weight: 600; cursor: pointer;
    transition: all .18s; background: var(--surface);
    color: var(--text-2); font-family: var(--jf);
    white-space: nowrap; text-decoration: none;
}
.jbtn:hover          { border-color: var(--accent); color: var(--accent); }
.jbtn.primary        { background: var(--accent); color: #fff; border-color: var(--accent); }
.jbtn.primary:hover  { background: var(--accent-d); color: #fff; }
.jbtn.success        { background: var(--green); color: #fff; border-color: var(--green); }
.jbtn.success:hover  { background: #15803d; }
.jbtn.danger         { background: var(--red); color: #fff; border-color: var(--red); }
.jbtn.danger:hover   { background: #b91c1c; }
.jbtn.warning        { background: var(--amber); color: #fff; border-color: var(--amber); }
.jbtn.warning:hover  { background: #b45309; }
.jbtn:disabled       { opacity: .5; cursor: not-allowed; pointer-events: none; }
.jbtn.sm             { padding: 5px 10px; font-size: 11px; }

/* ── KALENDER ── */
.jcal-wd-row {
    display: grid; grid-template-columns: repeat(7, 1fr);
    gap: 3px; margin-bottom: 4px;
}
.jcal-wd {
    text-align: center; font-size: 10px; font-weight: 700;
    letter-spacing: .07em; text-transform: uppercase;
    color: var(--text-3); padding: 4px 0;
}
.jcal-grid {
    display: grid; grid-template-columns: repeat(7, 1fr); gap: 3px;
}
.jcal-cell {
    border-radius: 8px; border: 1.5px solid transparent;
    background: var(--bg); min-height: 60px;
    display: flex; flex-direction: column; overflow: hidden;
    transition: border-color .15s, background .15s;
}
.jcal-cell.empty    { background: transparent; border-color: transparent; }
.jcal-cell.weekend  { background: #fff1f2; }
.jcal-cell.today    { border-color: var(--accent); }
.jcal-cell.selected {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(37,99,235,.15);
}
.jcal-top {
    display: flex; align-items: center;
    justify-content: space-between; padding: 4px 5px 2px;
}
.jcal-num { font-size: 12px; font-weight: 700; color: var(--text-1); line-height: 1; }
.jcal-cell.weekend .jcal-num { color: var(--red); }
.jcal-cell.today   .jcal-num { color: var(--accent); }
.jcal-btn {
    font-size: 9px; font-weight: 700; padding: 2px 5px;
    border-radius: 4px; border: none; cursor: pointer;
    font-family: var(--jf);
    background: rgba(37,99,235,.12); color: var(--accent);
    transition: background .15s; white-space: nowrap; line-height: 1.5;
}
.jcal-btn:hover { background: rgba(37,99,235,.25); }
.jcal-shifts { display: flex; flex-direction: column; gap: 2px; padding: 0 4px 5px; flex: 1; }
.jcal-dot    { height: 5px; border-radius: 3px; width: 100%; opacity: .85; }

/* ── DETAIL SHIFT TABS ── */
.jdtabs { display: flex; gap: 6px; flex-wrap: wrap; }
.jdtab {
    padding: 6px 16px; border-radius: 20px; border: 1.5px solid var(--border);
    font-size: 12px; font-weight: 600; cursor: pointer;
    font-family: var(--jf); background: var(--surface);
    color: var(--text-2); transition: all .18s;
}
.jdtab.active { border-color: var(--accent); background: var(--accent); color: #fff; }

/* ── SHIFT DETAIL CARD ── */
.jsdc {
    border: 2px solid var(--border); border-radius: 10px;
    overflow: hidden; background: var(--surface); margin-bottom: 12px;
}
.jsdc-hd {
    display: flex; align-items: center;
    justify-content: space-between; padding: 10px 14px;
    border-bottom: 1px solid var(--border); flex-wrap: wrap; gap: 8px;
}
.jsh-badge {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 12px; font-weight: 700; padding: 3px 10px; border-radius: 20px;
}
.jsh-pagi   { background: #fef9c3; color: #854d0e; }
.jsh-siang  { background: #dbeafe; color: #1e40af; }
.jsh-malam  { background: #ede9fe; color: #5b21b6; }
.jstatus-pill {
    font-size: 10px; font-weight: 700; padding: 2px 8px;
    border-radius: 10px; letter-spacing: .04em;
}
.jsp-belum  { background: #f1f5f9; color: #64748b; }
.jsp-jalan  { background: #dcfce7; color: #15803d; }
.jsp-selesai{ background: #e0e7ff; color: #4338ca; }
.jsdc-bd    { padding: 12px 14px; }
.jpetugas-row {
    display: flex; align-items: center; gap: 10px;
    padding: 7px 0; border-bottom: 1px solid var(--border); font-size: 13px;
}
.jpetugas-row:last-of-type { border-bottom: none; }
.javatar {
    width: 32px; height: 32px; min-width: 32px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; color: #fff; flex-shrink: 0;
}
.jcatatan {
    background: var(--bg); border-radius: 6px; padding: 8px 10px;
    font-size: 12px; color: var(--text-2); margin-top: 10px;
    border-left: 3px solid var(--accent);
}
.jwarna-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; flex-shrink: 0; }

/* ── AKTIVITAS TABLE ── */
.jakt-tbl { width: 100%; border-collapse: collapse; font-size: 12px; }
.jakt-tbl th {
    background: var(--bg); font-size: 10px; font-weight: 700;
    letter-spacing: .06em; text-transform: uppercase;
    color: var(--text-3); padding: 6px 8px;
    border: 1px solid var(--border); white-space: nowrap;
}
.jakt-tbl td   { padding: 6px 8px; border: 1px solid var(--border); vertical-align: middle; }
.jakt-tbl tr:hover td { background: rgba(37,99,235,.03); }
.jakt-tbl .row-patroli td { background: rgba(22,163,74,.04); }
.jab { padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600; display: inline-block; }
.jab-patroli  { background: #dcfce7; color: #15803d; }
.jab-pos      { background: #dbeafe; color: #1e40af; }
.jab-istirahat{ background: #fef9c3; color: #854d0e; }
.jab-netral   { background: #f1f5f9; color: #475569; }
.jab-monitor  { background: #ede9fe; color: #5b21b6; }

/* ── FORM ── */
.jform-lbl {
    display: block; font-size: 11px; font-weight: 700;
    letter-spacing: .07em; text-transform: uppercase;
    color: var(--text-3); margin-bottom: 5px;
}
.jform-ctrl {
    width: 100%; padding: 9px 12px; border: 1px solid var(--border);
    border-radius: 8px; font-size: 13px; color: var(--text-1);
    background: var(--surface); font-family: var(--jf); outline: none;
    transition: border-color .2s; box-sizing: border-box;
}
.jform-ctrl:focus    { border-color: var(--accent); }
.jform-ctrl:disabled { opacity: .6; cursor: not-allowed; }
.jform-ctrl.err      { border-color: var(--red); }

/* Shift radio cards */
.jshift-radios { display: flex; gap: 8px; }
.jshift-radios input[type=radio] { display: none; }
.jshift-radios label {
    flex: 1; display: flex; flex-direction: column; align-items: center;
    padding: 9px 4px; border: 1.5px solid var(--border); border-radius: 9px;
    cursor: pointer; font-size: 12px; font-weight: 600; color: var(--text-2);
    transition: all .18s; text-align: center;
}
.jshift-radios label small {
    font-size: 10px; color: var(--text-3); margin-top: 2px;
    font-weight: 400; font-family: var(--jm);
}
.jshift-radios input:checked + label {
    border-color: var(--accent); background: rgba(37,99,235,.07); color: var(--accent);
}
.jshift-radios input:checked + label small { color: var(--accent); }

/* Color swatches */
.jcolor-swatches { display: flex; flex-wrap: wrap; gap: 6px; }
.jcolor-swatch {
    width: 26px; height: 26px; border-radius: 50%; cursor: pointer;
    border: 2px solid transparent; transition: all .15s; flex-shrink: 0;
}
.jcolor-swatch.active { border-color: var(--text-1); transform: scale(1.25); }
.jcolor-custom-row { display: flex; align-items: center; gap: 8px; margin-top: 8px; }
.jcolor-preview {
    width: 26px; height: 26px; border-radius: 50%;
    border: 2px solid var(--border); flex-shrink: 0;
}

.jbtn-submit {
    width: 100%; padding: 10px; background: var(--accent); color: #fff;
    border: none; border-radius: 8px; font-size: 13px; font-weight: 700;
    cursor: pointer; font-family: var(--jf); transition: background .2s;
    display: flex; align-items: center; justify-content: center; gap: 6px;
}
.jbtn-submit:hover    { background: var(--accent-d); }
.jbtn-submit:disabled { background: #93c5fd; cursor: not-allowed; }
.jbtn-cancel {
    width: 100%; padding: 8px; background: var(--bg); color: var(--text-2);
    border: 1px solid var(--border); border-radius: 8px; font-size: 13px;
    font-weight: 600; cursor: pointer; font-family: var(--jf);
    margin-top: 8px; transition: all .18s;
}
.jbtn-cancel:hover { border-color: var(--accent); color: var(--accent); }

/* Toast */
.jtoast {
    padding: 10px 14px; border-radius: 8px; font-size: 12px;
    font-weight: 600; margin-bottom: 12px; display: none;
    align-items: center; gap: 8px;
}
.jtoast.ok   { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.jtoast.err  { background: #fef2f2; color: var(--red); border: 1px solid #fecaca; }
.jtoast.warn { background: #fef9c3; color: #854d0e; border: 1px solid #fde68a; }

/* Edit mode bar */
.jedit-bar {
    background: rgba(245,158,11,.1); border: 1px solid #f59e0b;
    border-radius: 8px; padding: 8px 12px; font-size: 12px;
    font-weight: 600; color: #92400e;
    display: flex; align-items: center; gap: 6px; margin-bottom: 12px;
}

/* Date selected indicator */
.jdate-ind {
    display: flex; align-items: center; gap: 8px;
    background: rgba(37,99,235,.06); border: 1px solid rgba(37,99,235,.2);
    border-radius: 8px; padding: 8px 12px; margin-bottom: 14px;
    font-size: 12px; font-weight: 700; color: var(--accent);
}

/* No data */
.jno-data {
    text-align: center; padding: 32px 20px; color: var(--text-3); font-size: 13px;
}
.jno-data i { font-size: 34px; display: block; margin-bottom: 10px; opacity: .35; }

/* Modal */
.jmodal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.45); z-index: 2000;
    align-items: center; justify-content: center;
}
.jmodal-overlay.show { display: flex; }
.jmodal-box {
    background: var(--surface); border-radius: 12px; padding: 24px;
    width: 400px; max-width: 95vw;
    box-shadow: 0 20px 60px rgba(0,0,0,.22);
}
.jmodal-title { font-size: 15px; font-weight: 700; color: var(--text-1); margin-bottom: 16px; }

/* Lock note */
.jlock-note {
    font-size: 11px; color: var(--amber); margin-top: 5px;
    display: flex; align-items: center; gap: 4px;
}
.jdup-note {
    font-size: 11px; color: var(--red); margin-top: 4px;
    display: flex; align-items: center; gap: 4px;
}

/* Export preview info */
.jexport-info {
    background: var(--bg); border-radius: 8px; padding: 12px;
    font-size: 12px; color: var(--text-2); margin-bottom: 14px;
    border: 1px solid var(--border);
}
.jexport-info ul { margin: 6px 0 0 16px; padding: 0; }
.jexport-info li { margin-bottom: 3px; }
.jexport-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 8px; border-radius: 10px; font-size: 11px;
    font-weight: 700; margin-right: 4px;
}
.jeb-p { background: #fef9c3; color: #854d0e; }
.jeb-s { background: #dbeafe; color: #1e40af; }
.jeb-m { background: #ede9fe; color: #5b21b6; }
</style>

<!-- ═══ PAGE HEADER ════════════════════════════════════════ -->
<div class="jdw">
<div class="jdw-ph">
    <div>
        <h2><i class="bi bi-calendar-week me-2 text-primary"></i>Jadwal Patroli</h2>
        <p>Kelola jadwal shift harian &mdash; klik <strong>Detail</strong> pada tanggal untuk melihat atau mengedit</p>
    </div>
    <button class="jbtn success" onclick="showExportModal()">
        <i class="bi bi-file-earmark-spreadsheet"></i> Unduh Jadwal
    </button>
</div>

<div class="jdw-grid">

    <!-- ═══ KIRI: Kalender + Detail ═══════════════════════ -->
    <div>

        <!-- Kalender -->
        <div class="jdw-card">
            <div class="jdw-card-hd">
                <span><i class="bi bi-calendar3 me-2"></i>Kalender Jadwal</span>
                <div class="jdw-month-nav">
                    <button class="jbtn" onclick="prevMonth()"><i class="bi bi-chevron-left"></i></button>
                    <input type="month" id="monthPicker" onchange="loadCalendar()">
                    <button class="jbtn" onclick="nextMonth()"><i class="bi bi-chevron-right"></i></button>
                </div>
            </div>
            <div class="jdw-card-bd">
                <div class="jcal-wd-row">
                    <?php foreach (['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $d): ?>
                    <div class="jcal-wd"><?= $d ?></div>
                    <?php endforeach; ?>
                </div>
                <div class="jcal-grid" id="calGrid"></div>
            </div>
        </div>

        <!-- Detail Shift -->
        <div class="jdw-card" id="detailCard">
            <div class="jdw-card-hd">
                <div id="detailDateLabel" style="font-size:13px;font-weight:600;color:var(--text-1);">
                    <i class="bi bi-calendar-event me-2 text-primary"></i>Pilih tanggal di kalender
                </div>
                <div class="jdtabs" id="shiftTabs"></div>
            </div>
            <div class="jdw-card-bd" id="shiftDetailArea">
                <div class="jno-data">
                    <i class="bi bi-calendar2-x"></i>
                    Klik tombol <strong>Detail</strong> pada tanggal di kalender
                </div>
            </div>
        </div>

    </div><!-- /kiri -->

    <!-- ═══ KANAN: Form ═══════════════════════════════════ -->
    <div>
        <div class="jdw-card">
            <div class="jdw-card-hd">
                <span id="jFormTitle"><i class="bi bi-plus-circle me-2"></i>Tambah Jadwal</span>
            </div>
            <div class="jdw-card-bd">

                <!-- Toast -->
                <div class="jtoast ok"   id="toastOk"></div>
                <div class="jtoast err"  id="toastErr"></div>
                <div class="jtoast warn" id="toastWarn"></div>

                <!-- Edit mode indicator -->
                <div class="jedit-bar" id="jEditBar" style="display:none;">
                    <i class="bi bi-pencil-square"></i>
                    <span>Mode Edit — memperbarui shift yang sudah dipilih</span>
                </div>

                <!-- Tanggal terpilih -->
                <div class="jdate-ind">
                    <i class="bi bi-calendar-event"></i>
                    <span id="jFormDateText">Belum ada tanggal dipilih — klik kalender</span>
                </div>
                <input type="hidden" id="jTanggal">
                <input type="hidden" id="jJadwalId">

                <!-- Pilih Shift -->
                <div class="mb-3">
                    <label class="jform-lbl">
                        Pilih Shift <span style="color:var(--red)">*</span>
                    </label>
                    <div class="jshift-radios">
                        <?php foreach ($shifts as $s): ?>
                        <div>
                            <input type="radio" name="shift_id"
                                   id="jsr_<?= $s['shift_id'] ?>"
                                   value="<?= $s['shift_id'] ?>">
                            <label for="jsr_<?= $s['shift_id'] ?>">
                                <?= $s['nama_shift'] === 'Pagi' ? '🌅' : ($s['nama_shift'] === 'Siang' ? '🌇' : '🌙') ?>
                                <?= esc($s['nama_shift']) ?>
                                <small><?= substr($s['jam_mulai'],0,5) ?>–<?= substr($s['jam_selesai'],0,5) ?></small>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div id="jShiftLockNote" class="jlock-note" style="display:none;">
                        <i class="bi bi-lock"></i> Shift dikunci — sedang Berjalan / Selesai
                    </div>
                </div>

                <!-- Petugas 1 -->
                <div class="mb-3">
                    <label class="jform-lbl" for="jP1">
                        Petugas 1 <span style="color:var(--red)">*</span>
                        <span style="font-weight:400;font-size:10px;text-transform:none;color:var(--text-3);">
                            (patroli ronde ke-1 & 3)
                        </span>
                    </label>
                    <select class="jform-ctrl" id="jP1" onchange="checkDuplikat()">
                        <option value="">-- Pilih Petugas 1 --</option>
                        <?php foreach ($users as $u): ?>
                        <option value="<?= $u['user_id'] ?>"><?= esc($u['nama_lengkap']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Petugas 2 -->
                <div class="mb-3">
                    <label class="jform-lbl" for="jP2">
                        Petugas 2 <span style="color:var(--red)">*</span>
                        <span style="font-weight:400;font-size:10px;text-transform:none;color:var(--text-3);">
                            (patroli ronde ke-2 & 4)
                        </span>
                    </label>
                    <select class="jform-ctrl" id="jP2" onchange="checkDuplikat()">
                        <option value="">-- Pilih Petugas 2 --</option>
                        <?php foreach ($users as $u): ?>
                        <option value="<?= $u['user_id'] ?>"><?= esc($u['nama_lengkap']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div id="jDupNote" class="jdup-note" style="display:none;">
                        <i class="bi bi-exclamation-triangle"></i> Petugas 1 dan 2 tidak boleh sama
                    </div>
                </div>

                <!-- Warna -->
                <div class="mb-3">
                    <label class="jform-lbl">Warna Jadwal</label>
                    <div class="jcolor-swatches" id="jColorSwatches">
                        <?php
                        $swatches = [
                            '#3b82f6','#22c55e','#f59e0b','#ef4444',
                            '#8b5cf6','#f97316','#06b6d4','#64748b',
                            '#ec4899','#14b8a6',
                        ];
                        foreach ($swatches as $c):
                        ?>
                        <div class="jcolor-swatch <?= $c==='#3b82f6' ? 'active' : '' ?>"
                             style="background:<?= $c ?>;"
                             data-color="<?= $c ?>"
                             onclick="selectColor('<?= $c ?>',this)"
                             title="<?= $c ?>"></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="jcolor-custom-row">
                        <div class="jcolor-preview" id="jColorPreview" style="background:#3b82f6;"></div>
                        <input type="color" id="jColorPicker" value="#3b82f6"
                               style="width:36px;height:28px;border:1px solid var(--border);border-radius:6px;cursor:pointer;padding:2px;"
                               oninput="onColorInput(this.value)">
                        <span style="font-size:11px;color:var(--text-3);">Warna kustom</span>
                    </div>
                    <input type="hidden" id="jSelectedColor" value="#3b82f6">
                </div>

                <!-- Catatan -->
                <div class="mb-3">
                    <label class="jform-lbl" for="jCatatan">
                        Catatan
                        <span style="font-weight:400;font-size:10px;text-transform:none;color:var(--text-3);">(opsional)</span>
                    </label>
                    <textarea class="jform-ctrl" id="jCatatan" rows="2"
                        placeholder="Contoh: Ibadah Minggu Raya, Patroli Khusus..."></textarea>
                </div>

                <!-- Tombol -->
                <button class="jbtn-submit" id="jBtnSimpan" onclick="simpanJadwal()">
                    <i class="bi bi-check2-circle"></i> Simpan Jadwal
                </button>
                <button class="jbtn-cancel" id="jBtnBatal" onclick="resetForm()" style="display:none;">
                    <i class="bi bi-x-circle me-1"></i>Batal Edit
                </button>

            </div>
        </div>

        <!-- Legenda -->
        <div class="jdw-card">
            <div class="jdw-card-bd" style="padding:14px 16px;">
                <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3);margin-bottom:10px;">
                    Legenda
                </p>
                <div style="display:flex;flex-direction:column;gap:8px;font-size:12px;color:var(--text-2);">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="width:12px;height:12px;border-radius:2px;border:1.5px solid var(--accent);background:rgba(37,99,235,.06);display:inline-block;flex-shrink:0;"></span>
                        Hari ini
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="width:12px;height:12px;border-radius:2px;box-shadow:0 0 0 3px rgba(37,99,235,.15);border:1.5px solid var(--accent);display:inline-block;flex-shrink:0;"></span>
                        Dipilih
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="width:12px;height:12px;border-radius:2px;background:#fff1f2;border:1px solid #fecaca;display:inline-block;flex-shrink:0;"></span>
                        Akhir pekan
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="width:12px;height:4px;border-radius:2px;background:#3b82f6;display:inline-block;flex-shrink:0;"></span>
                        Bar warna = jadwal aktif
                    </div>
                    <hr style="margin:4px 0;border-color:var(--border);">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <span class="jab jab-patroli">🚶 Patroli</span> Petugas keliling semua titik
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <span class="jab jab-pos">🏠 Jaga Pos</span> Petugas di pos jaga
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <span class="jab jab-istirahat">☕ Istirahat</span> Waktu istirahat
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /kanan -->
</div><!-- /.jdw-grid -->
</div><!-- /.jdw -->

<!-- ═══ MODAL UNDUH JADWAL ════════════════════════════════ -->
<div id="jExportModal" class="jmodal-overlay">
    <div class="jmodal-box">
        <div class="jmodal-title">
            <i class="bi bi-file-earmark-spreadsheet me-2 text-success"></i>Unduh Jadwal Bulanan
        </div>

        <!-- Info format -->
        <div class="jexport-info">
            <strong style="font-size:12px;">Format Excel — Jadwal Dinas Security</strong>
            <ul>
                <li>Tabel matriks: <strong>Petugas × Tanggal (1–<?php echo date('t'); ?>)</strong></li>
                <li>
                    Kode shift:
                    <span class="jexport-badge jeb-p">P = Pagi</span>
                    <span class="jexport-badge jeb-s">S = Siang</span>
                    <span class="jexport-badge jeb-m">M = Malam</span>
                </li>
                <li>Kolom merah = Sabtu / Minggu</li>
                <li>Baris bawah = Jumlah petugas per hari</li>
                <li>Kolom kanan = Rekapitulasi shift per petugas</li>
            </ul>
        </div>

        <div class="mb-3">
            <label class="jform-lbl">Pilih Bulan</label>
            <input type="month" id="jExportMonth" class="jform-ctrl">
        </div>
        <div style="display:flex;gap:8px;margin-top:16px;flex-wrap:wrap;">
            <button class="jbtn success" style="flex:1;" onclick="doExport('excel')">
                <i class="bi bi-file-earmark-excel"></i> Unduh Excel (.xls)
            </button>
            <button class="jbtn" onclick="closeExportModal()">
                <i class="bi bi-x"></i> Batal
            </button>
        </div>
    </div>
</div>

<script>
/* ============================================================
   JADWAL PATROLI — v2.2 JS Controller
   Skema DB: jadwal_shift memiliki petugas_1, petugas_2 (tanpa regu)
   Setiap shift = 2 petugas, masing-masing patroli 2× per shift
============================================================ */
const BASE      = '<?= base_url() ?>';
const TODAY_STR = '<?= date('Y-m-d') ?>';

let selectedDate  = '';
let jadwalMap     = {};
let activeShiftId = null;
let shiftCache    = [];
let isEditMode    = false;

/* ── INIT ──────────────────────────────────────────────── */
(function(){
    const d   = new Date();
    const mm  = String(d.getMonth()+1).padStart(2,'0');
    const val = d.getFullYear() + '-' + mm;
    document.getElementById('monthPicker').value   = val;
    document.getElementById('jExportMonth').value  = val;
    loadCalendar();
})();

/* ── NAVIGASI BULAN ─────────────────────────────────────── */
function prevMonth(){
    const p = document.getElementById('monthPicker').value.split('-');
    const d = new Date(+p[0], +p[1]-2, 1);
    document.getElementById('monthPicker').value = d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0');
    loadCalendar();
}
function nextMonth(){
    const p = document.getElementById('monthPicker').value.split('-');
    const d = new Date(+p[0], +p[1], 1);
    document.getElementById('monthPicker').value = d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0');
    loadCalendar();
}

/* ── LOAD KALENDER ─────────────────────────────────────── */
function loadCalendar(){
    const month = document.getElementById('monthPicker').value;
    if (!month) return;
    fetch(BASE+'jadwal/getByMonth?month='+month)
        .then(r => r.json())
        .then(data => { jadwalMap = data; renderCalendar(month); })
        .catch(()  => renderCalendar(month));
}

/* ── RENDER KALENDER ───────────────────────────────────── */
function renderCalendar(month){
    const [yr, mo] = month.split('-').map(Number);
    const days  = new Date(yr, mo, 0).getDate();
    const first = new Date(yr, mo-1, 1).getDay();
    const grid  = document.getElementById('calGrid');
    let html    = '';

    for (let i=0; i<first; i++) html += '<div class="jcal-cell empty"></div>';

    for (let d=1; d<=days; d++){
        const ds  = yr+'-'+String(mo).padStart(2,'0')+'-'+String(d).padStart(2,'0');
        const dow = new Date(yr, mo-1, d).getDay();
        const isW = (dow===0||dow===6);
        const cls = 'jcal-cell'
            + (isW     ? ' weekend'  : '')
            + (ds===TODAY_STR  ? ' today'    : '')
            + (ds===selectedDate ? ' selected' : '');

        const jd  = jadwalMap[ds];
        let bars  = '';
        if (jd && jd.jumlah > 0){
            bars = '<div class="jcal-shifts">';
            for (let si=0; si<Math.min(jd.jumlah,3); si++){
                bars += `<div class="jcal-dot" style="background:${jd.warna||'#3b82f6'}"></div>`;
            }
            bars += '</div>';
        }

        html += `<div class="${cls}">
            <div class="jcal-top">
                <span class="jcal-num">${d}</span>
                <button class="jcal-btn" onclick="selectDate('${ds}')">Detail</button>
            </div>
            ${bars}
        </div>`;
    }
    grid.innerHTML = html;
}

/* ── PILIH TANGGAL ─────────────────────────────────────── */
function selectDate(ds){
    selectedDate  = ds;
    isEditMode    = false;
    activeShiftId = null;

    document.getElementById('jTanggal').value   = ds;
    document.getElementById('jJadwalId').value  = '';
    const d    = new Date(ds+'T00:00:00');
    const lbl  = d.toLocaleDateString('id-ID',{weekday:'long',day:'numeric',month:'long',year:'numeric'});
    document.getElementById('jFormDateText').textContent  = lbl;
    document.getElementById('detailDateLabel').innerHTML  =
        '<i class="bi bi-calendar-event me-2 text-primary"></i>' + lbl;

    resetFormFields();
    hideEditBar();
    renderCalendar(document.getElementById('monthPicker').value);
    loadShiftDetail(ds);
}

/* ── LOAD DETAIL SHIFT ─────────────────────────────────── */
function loadShiftDetail(ds){
    const area = document.getElementById('shiftDetailArea');
    const tabs = document.getElementById('shiftTabs');
    area.innerHTML = '<div class="jno-data"><i class="bi bi-hourglass-split"></i>Memuat data...</div>';
    tabs.innerHTML = '';

    fetch(BASE+'jadwal/getByDate?date='+ds)
        .then(r => r.json())
        .then(rows => {
            shiftCache = rows;
            if (!rows.length){
                area.innerHTML = '<div class="jno-data"><i class="bi bi-calendar-x"></i>Belum ada jadwal untuk tanggal ini.<br><small>Gunakan form di kanan untuk menambahkan.</small></div>';
                return;
            }
            const META = {1:{ico:'🌅',lbl:'Pagi'},2:{ico:'🌇',lbl:'Siang'},3:{ico:'🌙',lbl:'Malam'}};
            tabs.innerHTML = rows.map(r=>{
                const m = META[r.shift_id]||{ico:'⏰',lbl:r.nama_shift};
                return `<button class="jdtab" id="jtab_${r.shift_id}"
                         onclick="switchTab(${r.shift_id})">${m.ico} ${m.lbl}</button>`;
            }).join('');
            switchTab(rows[0].shift_id);
        })
        .catch(()=>{
            area.innerHTML = '<div class="jno-data"><i class="bi bi-wifi-off"></i>Gagal memuat data.</div>';
        });
}

/* ── GANTI TAB ──────────────────────────────────────────── */
function switchTab(shiftId){
    activeShiftId = shiftId;
    document.querySelectorAll('.jdtab').forEach(t=>t.classList.remove('active'));
    const tab = document.getElementById('jtab_'+shiftId);
    if (tab) tab.classList.add('active');
    const row = shiftCache.find(r=>r.shift_id==shiftId);
    if (row) renderShiftDetail(row);
}

/* ── RENDER DETAIL SHIFT ────────────────────────────────── */
function renderShiftDetail(row){
    const area  = document.getElementById('shiftDetailArea');
    const color = row.warna || '#3b82f6';
    const bcls  = {1:'jsh-pagi',2:'jsh-siang',3:'jsh-malam'}[row.shift_id]||'jsh-pagi';
    const ico   = {1:'🌅',2:'🌇',3:'🌙'}[row.shift_id]||'⏰';
    const jam   = row.jam_mulai.substring(0,5)+'–'+row.jam_selesai.substring(0,5);
    const ss    = row.status_shift||'Belum Mulai';
    const spCls = ss==='Berjalan'?'jsp-jalan':ss==='Selesai'?'jsp-selesai':'jsp-belum';

    const canEdit   = ss==='Belum Mulai';
    const editBtn   = canEdit
        ? `<button class="jbtn sm" onclick="startEdit(${row.shift_id})"><i class="bi bi-pencil"></i> Edit</button>`
        : `<button class="jbtn sm warning" onclick="startEditPartial(${row.shift_id})"><i class="bi bi-pencil"></i> Edit Catatan</button>`;
    const delBtn  = canEdit
        ? `<button class="jbtn sm danger" onclick="hapusJadwal(${row.jadwal_shift_id})"><i class="bi bi-trash"></i></button>`
        : '';

    let html = `<div class="jsdc" style="border-color:${color};">
        <div class="jsdc-hd">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <div class="jwarna-dot" style="background:${color}"></div>
                <span class="jsh-badge ${bcls}">${ico} ${escH(row.nama_shift)}</span>
                <span style="font-size:11px;color:var(--text-3);font-family:var(--jm);">${jam}</span>
                <span class="jstatus-pill ${spCls}">${ss}</span>
            </div>
            <div style="display:flex;gap:6px;">${editBtn}${delBtn}</div>
        </div>
        <div class="jsdc-bd">`;

    if (row.petugas_1_nama){
        html += `<div class="jpetugas-row">
            <div class="javatar" style="background:${color};">${ini(row.petugas_1_nama)}</div>
            <div>
                <div style="font-weight:600;">${escH(row.petugas_1_nama)}</div>
                <div style="font-size:11px;color:var(--text-3);">Petugas 1 — Ronde 1 &amp; 3</div>
            </div>
        </div>`;
    }
    if (row.petugas_2_nama){
        html += `<div class="jpetugas-row">
            <div class="javatar" style="background:#16a34a;">${ini(row.petugas_2_nama)}</div>
            <div>
                <div style="font-weight:600;">${escH(row.petugas_2_nama)}</div>
                <div style="font-size:11px;color:var(--text-3);">Petugas 2 — Ronde 2 &amp; 4</div>
            </div>
        </div>`;
    }
    if (row.catatan){
        html += `<div class="jcatatan"><i class="bi bi-sticky me-2"></i>${escH(row.catatan)}</div>`;
    }

    html += buildAktTabel(row);
    html += '</div></div>';
    area.innerHTML = html;
}

/* ── TABEL AKTIVITAS ────────────────────────────────────── */
function buildAktTabel(row){
    const p1  = row.petugas_1_nama ? row.petugas_1_nama.split(' ')[0] : 'P1';
    const p2  = row.petugas_2_nama ? row.petugas_2_nama.split(' ')[0] : 'P2';
    const jm  = row.jam_mulai ? row.jam_mulai.substring(0,5) : '06:00';

    function addMin(base, mins){
        const [h,m] = base.split(':').map(Number);
        const t = h*60 + m + mins;
        return String(Math.floor(t/60)%24).padStart(2,'0')+':'+String(t%60).padStart(2,'0');
    }

    const rows = [
        [addMin(jm,  0), addMin(jm, 30),  'Briefing',
            ab('Hadir','netral'),         ab('Hadir','netral'),
            'Arahan &amp; persiapan sebelum mulai shift', false],
        [addMin(jm, 30), addMin(jm, 90),  `Ronde 1 — ${p1}`,
            ab('🚶 Patroli','patroli'),   ab('🏠 Jaga Pos','pos'),
            `Keliling T01–T06 | ${p2} jaga pos`, true],
        [addMin(jm, 90), addMin(jm,150),  `Ronde 2 — ${p2}`,
            ab('🏠 Jaga Pos','pos'),       ab('🚶 Patroli','patroli'),
            `Keliling T01–T06 | ${p1} jaga pos`, true],
        [addMin(jm,150), addMin(jm,180),  'Istirahat',
            ab('☕ Istirahat','istirahat'), ab('☕ Istirahat','istirahat'),
            'Istirahat bergantian singkat', false],
        [addMin(jm,180), addMin(jm,240),  `Ronde 3 — ${p1}`,
            ab('🚶 Patroli','patroli'),   ab('🏠 Jaga Pos','pos'),
            `Keliling T01–T06 | ${p2} jaga pos`, true],
        [addMin(jm,240), addMin(jm,300),  `Ronde 4 — ${p2}`,
            ab('🏠 Jaga Pos','pos'),       ab('🚶 Patroli','patroli'),
            `Keliling T01–T06 | ${p1} jaga pos`, true],
        [addMin(jm,300), addMin(jm,450),  'Jaga Pos Bersama',
            ab('🖥 Monitor','monitor'),    ab('🖥 Monitor','monitor'),
            'Pantau CCTV &amp; lingkungan sekitar', false],
        [addMin(jm,450), addMin(jm,480),  'Serah Terima',
            ab('📋 Laporan','netral'),     ab('📋 Laporan','netral'),
            'Dokumentasi &amp; serah tugas ke shift berikutnya', false],
    ];

    let h = `<div style="overflow-x:auto;margin-top:12px;">
        <p style="font-size:11px;color:var(--text-3);margin-bottom:7px;">
            📋 Setiap petugas patroli <strong>2× per shift</strong> — bergantian cover pos.
        </p>
        <table class="jakt-tbl"><thead><tr>
            <th>Mulai</th><th>Selesai</th><th>Sesi Kegiatan</th>
            <th>${escH(p1)}</th><th>${escH(p2)}</th><th>Keterangan</th>
        </tr></thead><tbody>`;

    rows.forEach(r=>{
        const rc = r[6] ? 'row-patroli' : '';
        h += `<tr class="${rc}">
            <td style="font-family:var(--jm);font-size:12px;white-space:nowrap;">${r[0]}</td>
            <td style="font-family:var(--jm);font-size:12px;white-space:nowrap;">${r[1]}</td>
            <td><strong>${r[2]}</strong></td>
            <td>${r[3]}</td><td>${r[4]}</td>
            <td style="color:var(--text-3);font-size:11px;">${r[5]}</td>
        </tr>`;
    });
    h += '</tbody></table></div>';
    return h;
}
function ab(text, type){
    const cls = {patroli:'jab jab-patroli',pos:'jab jab-pos',istirahat:'jab jab-istirahat',
                 netral:'jab jab-netral',monitor:'jab jab-monitor'}[type]||'jab jab-netral';
    return `<span class="${cls}">${text}</span>`;
}

/* ── MODE EDIT PENUH ────────────────────────────────────── */
function startEdit(shiftId){
    const row = shiftCache.find(r=>r.shift_id==shiftId);
    if (!row) return;
    isEditMode = true;
    document.getElementById('jJadwalId').value = row.jadwal_shift_id;
    const sr = document.querySelector(`input[name=shift_id][value="${shiftId}"]`);
    if (sr){ sr.checked=true; sr.disabled=false; }
    document.querySelectorAll('input[name=shift_id]').forEach(r=>r.disabled=false);
    document.getElementById('jP1').value       = row.petugas_1_id||'';
    document.getElementById('jP2').value       = row.petugas_2_id||'';
    document.getElementById('jP1').disabled    = false;
    document.getElementById('jP2').disabled    = false;
    document.getElementById('jCatatan').value  = row.catatan||'';
    selectColor(row.warna||'#3b82f6', null);
    document.getElementById('jColorPicker').value = row.warna||'#3b82f6';
    document.getElementById('jShiftLockNote').style.display = 'none';
    showEditBar();
    scrollToForm();
}

/* ── MODE EDIT PARSIAL ──────────────────────────────────── */
function startEditPartial(shiftId){
    const row = shiftCache.find(r=>r.shift_id==shiftId);
    if (!row) return;
    isEditMode = true;
    document.getElementById('jJadwalId').value = row.jadwal_shift_id;
    document.querySelectorAll('input[name=shift_id]').forEach(r=>{
        r.checked  = (r.value==shiftId);
        r.disabled = true;
    });
    document.getElementById('jP1').value    = row.petugas_1_id||'';
    document.getElementById('jP2').value    = row.petugas_2_id||'';
    document.getElementById('jP1').disabled = true;
    document.getElementById('jP2').disabled = true;
    document.getElementById('jCatatan').value = row.catatan||'';
    selectColor(row.warna||'#3b82f6', null);
    document.getElementById('jColorPicker').value = row.warna||'#3b82f6';
    document.getElementById('jShiftLockNote').style.display = 'flex';
    showEditBar();
    scrollToForm();
}

/* ── SIMPAN JADWAL ──────────────────────────────────────── */
function simpanJadwal(){
    hideAllToast();
    const tanggal  = document.getElementById('jTanggal').value;
    const shiftEl  = document.querySelector('input[name=shift_id]:checked');
    const p1       = document.getElementById('jP1').value;
    const p2       = document.getElementById('jP2').value;
    const warna    = document.getElementById('jSelectedColor').value;
    const catatan  = document.getElementById('jCatatan').value.trim();

    if (!tanggal){ showToast('err','Klik tanggal di kalender terlebih dahulu.'); return; }
    if (!shiftEl){ showToast('err','Pilih shift (Pagi / Siang / Malam).'); return; }
    if (!p1)     { showToast('err','Petugas 1 wajib dipilih.'); return; }
    if (!p2)     { showToast('err','Petugas 2 wajib dipilih.'); return; }
    if (p1===p2) { showToast('err','Petugas 1 dan 2 tidak boleh orang yang sama.'); return; }

    const btn = document.getElementById('jBtnSimpan');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan…';

    const body = new URLSearchParams({
        tanggal, shift_id: shiftEl.value,
        petugas_1: p1, petugas_2: p2,
        warna, catatan
    });

    fetch(BASE+'jadwal/save', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: body.toString()
    })
    .then(r=>r.json())
    .then(res=>{
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-circle"></i> Simpan Jadwal';
        if (res.status){
            showToast('ok', res.message);
            isEditMode = false;
            resetFormFields();
            hideEditBar();
            loadCalendar();
            if (selectedDate) loadShiftDetail(selectedDate);
        } else {
            showToast('err', res.message||'Gagal menyimpan.');
        }
    })
    .catch(()=>{
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-circle"></i> Simpan Jadwal';
        showToast('err','Koneksi gagal. Coba lagi.');
    });
}

/* ── HAPUS JADWAL ───────────────────────────────────────── */
function hapusJadwal(id){
    if (!confirm('Yakin hapus jadwal ini?\nShift Berjalan/Selesai tidak dapat dihapus.')) return;
    fetch(BASE+'jadwal/delete',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'jadwal_shift_id='+id
    })
    .then(r=>r.json())
    .then(res=>{
        if (res.status){
            showToast('ok',res.message);
            isEditMode=false; activeShiftId=null;
            resetFormFields(); hideEditBar();
            loadCalendar();
            if (selectedDate) loadShiftDetail(selectedDate);
        } else {
            showToast('err',res.message);
        }
    })
    .catch(()=>showToast('err','Koneksi gagal.'));
}

/* ── CEK DUPLIKAT ───────────────────────────────────────── */
function checkDuplikat(){
    const p1 = document.getElementById('jP1').value;
    const p2 = document.getElementById('jP2').value;
    document.getElementById('jDupNote').style.display =
        (p1&&p2&&p1===p2) ? 'flex' : 'none';
}

/* ── WARNA ──────────────────────────────────────────────── */
function selectColor(hex, el){
    document.getElementById('jSelectedColor').value           = hex;
    document.getElementById('jColorPreview').style.background = hex;
    document.querySelectorAll('.jcolor-swatch').forEach(s=>s.classList.remove('active'));
    if (el) el.classList.add('active');
    else {
        const found = document.querySelector(`.jcolor-swatch[data-color="${hex}"]`);
        if (found) found.classList.add('active');
    }
}
function onColorInput(hex){
    document.getElementById('jSelectedColor').value           = hex;
    document.getElementById('jColorPreview').style.background = hex;
    document.querySelectorAll('.jcolor-swatch').forEach(s=>s.classList.remove('active'));
}

/* ── RESET FORM ─────────────────────────────────────────── */
function resetForm(){
    isEditMode = false;
    resetFormFields();
    hideEditBar();
}
function resetFormFields(){
    document.querySelectorAll('input[name=shift_id]').forEach(r=>{ r.checked=false; r.disabled=false; });
    document.getElementById('jP1').value    = '';
    document.getElementById('jP2').value    = '';
    document.getElementById('jP1').disabled = false;
    document.getElementById('jP2').disabled = false;
    document.getElementById('jCatatan').value = '';
    document.getElementById('jJadwalId').value = '';
    document.getElementById('jShiftLockNote').style.display = 'none';
    document.getElementById('jDupNote').style.display       = 'none';
    selectColor('#3b82f6', document.querySelector('.jcolor-swatch[data-color="#3b82f6"]'));
    document.getElementById('jColorPicker').value = '#3b82f6';
    document.getElementById('jBtnBatal').style.display = 'none';
    document.getElementById('jFormTitle').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Tambah Jadwal';
}
function showEditBar(){
    document.getElementById('jEditBar').style.display  = 'flex';
    document.getElementById('jBtnBatal').style.display = 'block';
    document.getElementById('jFormTitle').innerHTML =
        '<i class="bi bi-pencil-square me-2 text-warning"></i>Edit Jadwal';
}
function hideEditBar(){
    document.getElementById('jEditBar').style.display  = 'none';
    document.getElementById('jBtnBatal').style.display = 'none';
    document.getElementById('jFormTitle').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Tambah Jadwal';
}

/* ── EXPORT MODAL ───────────────────────────────────────── */
function showExportModal(){
    // Sinkronkan bulan modal dengan kalender aktif
    const mp = document.getElementById('monthPicker').value;
    if (mp) document.getElementById('jExportMonth').value = mp;
    document.getElementById('jExportModal').classList.add('show');
}
function closeExportModal(){ document.getElementById('jExportModal').classList.remove('show'); }
function doExport(fmt){
    const month = document.getElementById('jExportMonth').value;
    if (!month){ alert('Pilih bulan terlebih dahulu.'); return; }
    window.open(BASE+'jadwal/export-'+fmt+'?month='+month, '_blank');
    closeExportModal();
}
document.getElementById('jExportModal').addEventListener('click', function(e){
    if (e.target===this) closeExportModal();
});

/* ── TOAST ──────────────────────────────────────────────── */
function showToast(type, msg){
    hideAllToast();
    const idMap = {ok:'toastOk',err:'toastErr',warn:'toastWarn'};
    const icoMap= {ok:'<i class="bi bi-check-circle me-2"></i>',
                   err:'<i class="bi bi-x-circle me-2"></i>',
                   warn:'<i class="bi bi-exclamation-triangle me-2"></i>'};
    const el = document.getElementById(idMap[type]||'toastErr');
    el.innerHTML = (icoMap[type]||'') + msg;
    el.style.display = 'flex';
    setTimeout(()=>el.style.display='none', 5000);
}
function hideAllToast(){
    ['toastOk','toastErr','toastWarn'].forEach(id=>{
        document.getElementById(id).style.display='none';
    });
}

/* ── HELPERS ────────────────────────────────────────────── */
function scrollToForm(){
    document.querySelector('.jdw-grid > div:last-child')
        .scrollIntoView({behavior:'smooth', block:'start'});
}
function ini(nama){ return nama ? nama.charAt(0).toUpperCase() : '?'; }
function escH(s){
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>

<?= $this->endSection() ?>