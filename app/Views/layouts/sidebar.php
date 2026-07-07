<!-- OVERLAY (mobile) -->
<div class="sb-overlay" id="sbOverlay" aria-hidden="true"></div>


<!-- SIDEBAR -->
<aside class="sidebar" id="mainSidebar" role="navigation" aria-label="Navigasi Utama">


    <!-- ══ 1. HAMBURGER ROW ══ -->
    <div class="sb-hamburger-row">
        <span class="sb-app-name">RFID Monitor</span>
        <button class="sb-hamburger" id="sbHamburger"
                aria-label="Toggle navigasi"
                aria-expanded="false"
                aria-controls="mainSidebar">
            <i class="bi bi-list"></i>
        </button>
    </div>


    <!-- ══ 2. PROFILE ══ -->
    <div class="sb-profile">
        <img
            src="<?= base_url('uploads/profile/' . (session()->get('foto_profile') ?? 'user.png')) ?>"
            class="sb-profile-avatar"
            alt="Foto Profil"
            onerror="this.src='<?= base_url('img/user.png') ?>'">
        <div class="sb-profile-info">
            <div class="sb-profile-name"><?= esc(session()->get('username')); ?></div>
            <div class="sb-profile-role"><?= esc(session()->get('role')); ?></div>
        </div>
    </div>


    <!-- ══ 3. NAVIGATION ══ -->
    <nav class="sb-nav" aria-label="Menu Utama">


        <p class="sb-section-label">Main</p>
        <ul>
            <li class="<?= uri_string() == 'dashboard' ? 'active' : ''; ?>">
                <a href="<?= base_url('dashboard'); ?>">
                    <span class="nav-icon"><i class="bi bi-grid-1x2"></i></span>
                    <span class="nav-label">Dashboard</span>
                    <span class="nav-tip">Dashboard</span>
                </a>
            </li>
            <li class="<?= uri_string() == 'users' ? 'active' : ''; ?>">
                <a href="<?= base_url('users'); ?>">
                    <span class="nav-icon"><i class="bi bi-people"></i></span>
                    <span class="nav-label">Manajemen Pengguna</span>
                    <span class="nav-tip">Manajemen Pengguna</span>
                </a>
            </li>
        </ul>


        <p class="sb-section-label">Monitoring</p>
        <ul>
            <li class="<?= uri_string() == 'perangkat' ? 'active' : ''; ?>">
                <a href="<?= base_url('perangkat'); ?>">
                    <span class="nav-icon"><i class="bi bi-hdd-network"></i></span>
                    <span class="nav-label">Data Perangkat</span>
                    <span class="nav-tip">Data Perangkat</span>
                </a>
            </li>
            <li class="<?= uri_string() == 'log-rfid' ? 'active' : ''; ?>">
                <a href="<?= base_url('log-rfid'); ?>">
                    <span class="nav-icon"><i class="bi bi-credit-card-2-front"></i></span>
                    <span class="nav-label">Log RFID</span>
                    <span class="nav-tip">Log RFID</span>
                </a>
            </li>
            <li class="<?= uri_string() == 'jadwal' ? 'active' : ''; ?>">
                <a href="<?= base_url('jadwal'); ?>">
                    <span class="nav-icon"><i class="bi bi-calendar-week"></i></span>
                    <span class="nav-label">Jadwal Patroli</span>
                    <span class="nav-tip">Jadwal Patroli</span>
                </a>
            </li>
        </ul>


        <p class="sb-section-label">Reports</p>
        <ul>
            <li class="<?= uri_string() == 'laporan' ? 'active' : ''; ?>">
                <a href="<?= base_url('laporan'); ?>">
                    <span class="nav-icon"><i class="bi bi-file-earmark-bar-graph"></i></span>
                    <span class="nav-label">Laporan</span>
                    <span class="nav-tip">Laporan</span>
                </a>
            </li>
        </ul>


    </nav>


    <!-- ══ 4. LOGOUT ══ -->
    <div class="sb-footer">
        <a href="<?= base_url('logout'); ?>">
            <span class="nav-icon"><i class="bi bi-box-arrow-right"></i></span>
            <span class="nav-label">Logout</span>
            <span class="nav-tip">Logout</span>
        </a>
    </div>


</aside>