<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Monitoring RFID'; ?> — GKI Bromo</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/style.css'); ?>">

    <style>
        /* ============================================================
           DESIGN TOKENS
        ============================================================ */
        :root {
            --sb-bg:          #0d1b2e;
            --sb-border:      rgba(255,255,255,0.07);
            --sb-text:        #7e99b8;
            --sb-text-hover:  #ddeaf6;
            --sb-active-bg:   rgba(59,130,246,0.15);
            --sb-active-text: #60a5fa;
            --sb-active-bar:  #3b82f6;
            --sb-width:       252px;
            --sb-collapsed:   64px;

            --bg:       #f0f4f8;
            --surface:  #ffffff;
            --border:   #e2e8f0;
            --text-1:   #0f172a;
            --text-2:   #475569;
            --text-3:   #94a3b8;
            --accent:   #2563eb;

            --tb-h:     58px;
            --tb-bg:    #ffffff;
            --tb-bdr:   #e8edf3;

            --cr: 8px;
            --cs: 0 1px 3px rgba(0,0,0,.06), 0 4px 18px rgba(0,0,0,.05);

            --ease: cubic-bezier(.4,0,.2,1);
            --dur:  0.26s;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            font-family: 'IBM Plex Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            font-size: 14px;
            color: var(--text-1);
            background: var(--bg);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        a { text-decoration: none; color: inherit; }
        img { max-width: 100%; display: block; }

        /* ============================================================
           APP SHELL
        ============================================================ */
        .app-shell {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* ============================================================
           SIDEBAR
           FIX: Tidak ada CSS default collapse untuk tablet.
                Semua state dikontrol penuh oleh JS via class.
                Ini menghilangkan race condition antara CSS dan JS.
        ============================================================ */
        .sidebar {
            width: var(--sb-width);
            height: 100vh;
            background: var(--sb-bg);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1040;
            /* FIX: overflow-x hidden saja, bukan overflow hidden penuh.
               overflow hidden memotong tooltip dan active bar ::before */
            overflow-x: hidden;
            overflow-y: hidden;
            transition: width var(--dur) var(--ease), transform var(--dur) var(--ease);
            will-change: width, transform;
        }

        /* State: desktop collapsed */
        .sidebar.is-collapsed {
            width: var(--sb-collapsed);
        }

        /* State: tablet collapsed (diset JS, bukan CSS media query default) */
        .sidebar.is-tablet-collapsed {
            width: var(--sb-collapsed);
        }

        /* State: tablet expanded */
        .sidebar.tablet-expanded {
            width: var(--sb-width);
        }

        /* Mobile: off-canvas */
        @media (max-width: 767.98px) {
            .sidebar {
                transform: translateX(-100%);
                width: var(--sb-width) !important;
            }
            .sidebar.is-open {
                transform: translateX(0);
            }
        }

        /* ============================================================
           HAMBURGER ROW
        ============================================================ */
        .sb-hamburger-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 12px;
            border-bottom: 1px solid var(--sb-border);
            flex-shrink: 0;
            height: var(--tb-h);
            /* FIX: hapus overflow hidden agar tidak clip konten */
        }

        .sidebar.is-collapsed .sb-hamburger-row,
        .sidebar.is-tablet-collapsed .sb-hamburger-row {
            justify-content: center;
        }

        .sb-app-name {
            font-size: 11px;
            font-weight: 700;
            color: rgba(221,234,246,0.55);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            max-width: 150px;
            opacity: 1;
            transition: opacity var(--dur) var(--ease), max-width var(--dur) var(--ease);
        }

        .sidebar.is-collapsed .sb-app-name,
        .sidebar.is-tablet-collapsed .sb-app-name {
            opacity: 0;
            max-width: 0;
        }

        .sb-hamburger {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: rgba(255,255,255,0.06);
            border: 1px solid var(--sb-border);
            color: var(--sb-text);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
            flex-shrink: 0;
            transition: background var(--dur) var(--ease), color var(--dur) var(--ease);
            outline: none;
        }

        .sb-hamburger:hover {
            background: rgba(255,255,255,0.1);
            color: var(--sb-text-hover);
        }

        /* ============================================================
           PROFILE ROW
        ============================================================ */
        .sb-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border-bottom: 1px solid var(--sb-border);
            flex-shrink: 0;
            /* FIX: hapus overflow hidden */
            transition: padding var(--dur) var(--ease);
        }

        .sidebar.is-collapsed .sb-profile,
        .sidebar.is-tablet-collapsed .sb-profile {
            padding: 12px 0;
            justify-content: center;
        }

        .sb-profile-avatar {
            width: 34px;
            height: 34px;
            min-width: 34px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(96,165,250,0.4);
            flex-shrink: 0;
        }

        .sb-profile-info {
            overflow: hidden;
            opacity: 1;
            max-width: 160px;
            white-space: nowrap;
            transition: opacity var(--dur) var(--ease), max-width var(--dur) var(--ease);
        }

        .sidebar.is-collapsed .sb-profile-info,
        .sidebar.is-tablet-collapsed .sb-profile-info {
            opacity: 0;
            max-width: 0;
        }

        .sb-profile-name {
            font-size: 12.5px;
            font-weight: 700;
            color: #ddeaf6;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.25;
        }

        .sb-profile-role {
            font-size: 10px;
            font-weight: 500;
            color: var(--sb-text);
            letter-spacing: 0.06em;
            text-transform: uppercase;
            line-height: 1.3;
        }

        /* ============================================================
           NAV
           FIX: min-height: 0 agar flex shrink bekerja benar.
                Tanpa ini, sb-nav meluap ke bawah dan menimpa footer,
                membuat area klik Monitoring/Reports tidak bisa diklik.
        ============================================================ */
        .sb-nav {
            flex: 1;
            min-height: 0;
            padding: 8px 0;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.07) transparent;
        }

        .sb-nav::-webkit-scrollbar { width: 3px; }
        .sb-nav::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.08);
            border-radius: 2px;
        }

        /* Section label */
        .sb-section-label {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgba(126,153,184,0.4);
            padding: 12px 18px 4px;
            white-space: nowrap;
            overflow: hidden;
            height: 28px;
            opacity: 1;
            transition: opacity var(--dur) var(--ease),
                        height var(--dur) var(--ease),
                        padding var(--dur) var(--ease);
        }

        .sidebar.is-collapsed .sb-section-label,
        .sidebar.is-tablet-collapsed .sb-section-label {
            opacity: 0;
            height: 0;
            padding: 0;
        }

        .sb-nav ul { list-style: none; padding: 0; }

        /* Nav link — base state (expanded) */
        .sb-nav ul li a {
            display: flex;
            align-items: center;
            height: 44px;
            padding: 0 8px 0 10px;
            margin: 1px 8px;
            border-radius: 8px;
            color: var(--sb-text);
            font-size: 13px;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            position: relative;
            transition: background var(--dur) var(--ease), color var(--dur) var(--ease);
        }

        .sb-nav ul li a:hover {
            background: rgba(255,255,255,0.05);
            color: var(--sb-text-hover);
        }

        /* Active state */
        .sb-nav ul li.active a {
            background: var(--sb-active-bg);
            color: var(--sb-active-text);
            font-weight: 600;
        }

        /* FIX: z-index pada ::before agar tidak overlap area klik */
        .sb-nav ul li.active a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 20%;
            height: 60%;
            width: 3px;
            border-radius: 0 2px 2px 0;
            background: var(--sb-active-bar);
            z-index: 0;
            pointer-events: none;
        }

        /* ============================================================
           ICON
           FIX: icon selalu centered dengan ukuran fixed 40px.
                Tidak diubah saat collapsed agar tidak terpotong.
        ============================================================ */
        .nav-icon {
            font-size: 16px;
            flex-shrink: 0;
            width: 40px;
            min-width: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 1;
        }

        /* ============================================================
           NAV LABEL
        ============================================================ */
        .nav-label {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            opacity: 1;
            max-width: 160px;
            transition: opacity var(--dur) var(--ease),
                        max-width var(--dur) var(--ease),
                        width var(--dur) var(--ease);
        }

        /* ============================================================
           COLLAPSED NAV LINK STATE
           FIX: gunakan padding:0 dan justify-content:center.
                width dikembalikan ke auto agar tidak clip icon.
                margin tetap 1px 8px sehingga icon terlihat penuh.
        ============================================================ */
        .sidebar.is-collapsed .sb-nav ul li a,
        .sidebar.is-tablet-collapsed .sb-nav ul li a {
            padding: 0;
            justify-content: center;
            overflow: visible; /* agar tooltip tidak terpotong */
        }

        .sidebar.is-collapsed .sb-nav ul li a,
        .sidebar.is-tablet-collapsed .sb-nav ul li a {
            /* FIX: width auto, bukan calc — supaya tidak terpotong */
            width: auto;
        }

        .sidebar.is-collapsed .nav-label,
        .sidebar.is-tablet-collapsed .nav-label {
            opacity: 0;
            max-width: 0;
            width: 0;
            flex: 0;
            overflow: hidden;
        }

        /* Footer link collapsed */
        .sidebar.is-collapsed .sb-footer a,
        .sidebar.is-tablet-collapsed .sb-footer a {
            padding: 0;
            justify-content: center;
            overflow: visible;
        }

        /* ============================================================
           TOOLTIP
        ============================================================ */
        .nav-tip {
            position: absolute;
            left: calc(100% + 12px);
            top: 50%;
            transform: translateY(-50%);
            background: #1e3a5f;
            color: #ddeaf6;
            font-size: 11.5px;
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 6px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            z-index: 9999;
            box-shadow: 0 4px 14px rgba(0,0,0,.35);
            transition: opacity .15s ease;
        }

        .nav-tip::before {
            content: '';
            position: absolute;
            right: 100%;
            top: 50%;
            transform: translateY(-50%);
            border: 5px solid transparent;
            border-right-color: #1e3a5f;
        }

        /* Tooltip hanya muncul saat collapsed */
        .sidebar.is-collapsed .sb-nav ul li a:hover .nav-tip,
        .sidebar.is-collapsed .sb-footer a:hover .nav-tip,
        .sidebar.is-tablet-collapsed .sb-nav ul li a:hover .nav-tip,
        .sidebar.is-tablet-collapsed .sb-footer a:hover .nav-tip {
            opacity: 1;
        }

        /* ============================================================
           SIDEBAR FOOTER — logout
        ============================================================ */
        .sb-footer {
            border-top: 1px solid var(--sb-border);
            padding: 6px 0;
            flex-shrink: 0;
        }

        .sb-footer a {
            display: flex;
            align-items: center;
            height: 44px;
            padding: 0 8px 0 10px;
            margin: 1px 8px;
            border-radius: 8px;
            color: #f87171;
            font-size: 13px;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            position: relative;
            transition: background var(--dur) var(--ease), color var(--dur) var(--ease);
        }

        .sb-footer a:hover {
            background: rgba(248,113,113,0.09);
            color: #fca5a5;
        }

        /* ============================================================
           OVERLAY — mobile
        ============================================================ */
        .sb-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            z-index: 1039;
        }

        .sb-overlay.active {
            display: block;
            animation: fadeIn .2s ease;
        }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        /* ============================================================
           MAIN AREA — margin dikontrol JS
        ============================================================ */
        .main-area {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            transition: margin-left var(--dur) var(--ease);
            min-height: 100vh;
        }

        /* ============================================================
           TOPBAR
        ============================================================ */
        .topbar {
            height: var(--tb-h);
            background: var(--tb-bg);
            border-bottom: 1px solid var(--tb-bdr);
            display: flex;
            align-items: center;
            padding: 0 20px;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 1030;
            flex-shrink: 0;
        }

        .tb-page-info {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            flex: 1;
            min-width: 0;
        }

        .tb-page-info h5 {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-1);
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.25;
        }

        .tb-page-info span {
            font-size: 10.5px;
            color: var(--text-3);
            white-space: nowrap;
        }

        .tb-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .tb-status {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(22,163,74,0.09);
            border: 1px solid rgba(22,163,74,0.25);
            border-radius: 20px;
            padding: 4px 11px 4px 8px;
            white-space: nowrap;
        }

        .tb-status .pulse {
            width: 7px;
            height: 7px;
            min-width: 7px;
            border-radius: 50%;
            background: #22c55e;
            animation: pulseAnim 2.2s ease-in-out infinite;
        }

        @keyframes pulseAnim {
            0%,100% { box-shadow: 0 0 0 0 rgba(34,197,94,.45); }
            50%      { box-shadow: 0 0 0 5px rgba(34,197,94,0); }
        }

        .tb-status-text {
            font-size: 11px;
            font-weight: 600;
            color: #15803d;
            letter-spacing: 0.03em;
        }

        .tb-datetime {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 11px;
            font-weight: 500;
            color: var(--text-3);
            white-space: nowrap;
        }

        .tb-divider {
            width: 1px;
            height: 24px;
            background: var(--border);
            flex-shrink: 0;
        }

        .tb-logo {
            height: 32px;
            width: auto;
            max-width: 110px;
            object-fit: contain;
            flex-shrink: 0;
        }

        @media (max-width: 767.98px) {
            .topbar { padding: 0 14px; gap: 8px; }
            .tb-datetime { display: none; }
            .tb-divider { display: none; }
        }

        @media (max-width: 575.98px) {
            .tb-status-text { display: none; }
            .tb-status { padding: 6px 7px; border-radius: 50%; }
        }

        /* ============================================================
           CONTENT & FOOTER
        ============================================================ */
        .content-wrapper {
            flex: 1;
            padding: 24px;
        }

        @media (max-width: 767.98px) {
            .content-wrapper { padding: 16px 14px; }
        }

        .footer-bar {
            height: 42px;
            background: var(--surface);
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 24px;
            font-size: 11px;
            color: var(--text-3);
            flex-shrink: 0;
        }

        .footer-bar .fr {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .footer-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #22c55e;
        }

        @media (max-width: 767.98px) {
            .footer-bar { padding: 0 14px; }
        }

        /* ============================================================
           CARD / TABLE
        ============================================================ */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--cr);
            box-shadow: var(--cs);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border);
            padding: 15px 20px;
            font-weight: 600;
            font-size: 13px;
        }

        .card-body { padding: 20px; }

        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table { font-size: 13px; color: var(--text-1); margin: 0; }

        .table th {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-3);
            border-bottom: 1px solid var(--border);
            padding: 10px 14px;
            white-space: nowrap;
        }

        .table td {
            padding: 10px 14px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .table tbody tr:last-child td { border-bottom: 0; }
        .table tbody tr:hover td { background: var(--bg); }
    </style>
</head>

<body>