<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Sistem Monitoring RFID GKI Bromo</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --blue:      #2563EB;
            --blue-h:    #1D4ED8;
            --white:     #FFFFFF;
            --border:    #E5E7EB;
            --input-bg:  #F9FAFB;
            --text-dark: #111827;
            --text-mid:  #374151;
            --text-mute: #6B7280;
            --text-lite: #9CA3AF;
        }

        html, body { height: 100%; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
            position: relative;
            overflow: hidden;
            background: #060b18;
        }

        /* ===== DARK BACKGROUND ===== */
        .bg-layer {
            position: fixed;
            inset: 0;
            z-index: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 10%,  rgba(37,99,235,0.30) 0%, transparent 55%),
                radial-gradient(ellipse 60% 50% at 80% 90%,  rgba(99,37,235,0.22) 0%, transparent 55%),
                linear-gradient(160deg, #060c1f 0%, #0a0f2c 40%, #070c20 70%, #060b18 100%);
        }

        /* Moving aurora blobs */
        .aurora {
            position: fixed;
            border-radius: 50%;
            filter: blur(90px);
            pointer-events: none;
            z-index: 0;
            animation: auroraFloat 14s ease-in-out infinite;
        }
        .aurora-1 {
            width: 640px; height: 400px;
            background: radial-gradient(ellipse, rgba(37,99,235,0.45) 0%, transparent 70%);
            top: -120px; left: -160px;
            animation-delay: 0s;
        }
        .aurora-2 {
            width: 500px; height: 500px;
            background: radial-gradient(ellipse, rgba(120,50,255,0.28) 0%, transparent 70%);
            bottom: -150px; right: -100px;
            animation-delay: -5s;
        }
        .aurora-3 {
            width: 350px; height: 350px;
            background: radial-gradient(ellipse, rgba(37,170,235,0.20) 0%, transparent 70%);
            top: 40%; left: 55%;
            animation-delay: -9s;
            animation-duration: 18s;
        }

        @keyframes auroraFloat {
            0%   { opacity: 0.4; transform: translate(0,   0)    scale(1); }
            33%  { opacity: 0.7; transform: translate(40px, 20px) scale(1.05); }
            66%  { opacity: 0.5; transform: translate(-20px,35px) scale(0.97); }
            100% { opacity: 0.4; transform: translate(0,   0)    scale(1); }
        }

        .dot-grid {
            position: fixed;
            inset: 0;
            z-index: 0;
            background-image: radial-gradient(circle, rgba(255,255,255,0.05) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
        }

        /* ===== MAIN CARD ===== */
        .login-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 1100px;
            display: flex;
            border-radius: 24px;
            box-shadow: 0 24px 70px rgba(0,0,0,0.60), 0 0 0 1px rgba(255,255,255,0.08);
            overflow: hidden;
            min-height: 580px;
        }

        /* ===== LEFT PANEL ===== */
        .panel-left {
            width: 45%;
            flex-shrink: 0;
            position: relative;
            overflow: hidden;
            border-radius: 24px 0 0 24px;
        }

        .panel-left-img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .panel-left-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                180deg,
                rgba(6,11,30,0.30) 0%,
                rgba(6,11,30,0.52) 55%,
                rgba(4,8,25,0.80) 100%
            );
        }

        .panel-shimmer {
            position: absolute;
            top: 0; left: 0;
            width: 60%; height: 100%;
            background: linear-gradient(120deg, rgba(255,255,255,0.06) 0%, transparent 60%);
            pointer-events: none;
            z-index: 1;
        }

        .panel-left-content {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 48px 36px;
            z-index: 2;
        }

        .panel-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.18);
            color: rgba(255,255,255,0.88);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            padding: 7px 18px;
            border-radius: 100px;
            backdrop-filter: blur(10px);
            margin-bottom: 28px;
        }

        .panel-title {
            font-family: 'Sora', sans-serif;
            font-size: 26px;
            font-weight: 700;
            color: #FFFFFF;
            line-height: 1.25;
            margin-bottom: 14px;
            letter-spacing: -0.3px;
            text-shadow: 0 2px 20px rgba(0,0,0,0.40);
        }

        .panel-subtitle {
            font-size: 14px;
            font-weight: 400;
            color: rgba(255,255,255,0.70);
            line-height: 1.65;
            max-width: 280px;
        }

        .panel-dots {
            position: absolute;
            bottom: 28px;
            left: 28px;
            display: grid;
            grid-template-columns: repeat(5, 6px);
            gap: 6px;
            z-index: 2;
        }
        .panel-dots span {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: rgba(255,255,255,0.25);
            display: block;
        }

        /* ===== RIGHT PANEL — PURE WHITE ===== */
        .panel-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 52px 52px;
            background: #FFFFFF;
            border-radius: 0 24px 24px 0;
        }

        /* Logo */
        .logo-area {
            text-align: center;
            margin-bottom: 22px;
        }

        .logo-area img {
            height: 110px;
            width: auto;
            object-fit: contain;
            display: inline-block;
        }

        /* Form heading */
        .form-heading {
            text-align: center;
            margin-bottom: 32px;
        }

        .form-heading h1 {
            font-family: 'Sora', sans-serif;
            font-size: 17px;
            font-weight: 700;
            color: var(--text-dark);
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 0;
            line-height: 1.35;
        }

        .heading-divider {
            width: 36px;
            height: 3px;
            background: var(--blue);
            border-radius: 4px;
            margin: 12px auto 0;
        }

        /* Flash alerts */
        .alert {
            border-radius: 10px;
            padding: 11px 16px;
            font-size: 13px;
            margin-bottom: 18px;
            font-weight: 500;
            border: none;
        }
        .alert-danger  { background: #FEF2F2; color: #B91C1C; border-left: 3px solid #EF4444; }
        .alert-success { background: #F0FDF4; color: #15803D; border-left: 3px solid #22C55E; }

        /* ===== FORM FIELDS ===== */
        .field-group { margin-bottom: 18px; }

        .field-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-mid);
            margin-bottom: 8px;
        }

        .field-wrap { position: relative; }

        .field-icon-left {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            color: var(--text-lite);
            pointer-events: none;
            z-index: 2;
            transition: color 0.2s;
        }

        .field-control {
            width: 100%;
            height: 50px;
            padding: 0 48px 0 44px;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            background: var(--input-bg);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            color: var(--text-dark);
            outline: none;
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
            -webkit-appearance: none;
        }

        .field-control::placeholder { color: var(--text-lite); }

        .field-control:focus {
            border-color: var(--blue);
            background: #FFFFFF;
            box-shadow: 0 0 0 4px rgba(37,99,235,0.08);
        }

        .field-wrap:focus-within .field-icon-left { color: var(--blue); }

        .toggle-pw {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            color: var(--text-lite);
            font-size: 14px;
            transition: color 0.2s;
            z-index: 2;
        }
        .toggle-pw:hover { color: var(--text-mid); }

        /* ===== SUBMIT BUTTON ===== */
        .btn-login {
            display: block;
            width: 100%;
            height: 50px;
            margin-top: 24px;
            border: none;
            border-radius: 12px;
            background: var(--blue);
            color: #FFFFFF;
            font-family: 'Sora', sans-serif;
            font-size: 13.5px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.22s, transform 0.18s, box-shadow 0.22s;
            box-shadow: 0 6px 20px rgba(37,99,235,0.30);
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(255,255,255,0.12) 0%, transparent 55%);
            pointer-events: none;
        }

        .btn-login:hover {
            background: var(--blue-h);
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(37,99,235,0.40);
        }

        .btn-login:active {
            transform: translateY(0);
            box-shadow: 0 4px 12px rgba(37,99,235,0.22);
        }

        /* ===== WATERMARK ===== */
        .watermark {
            text-align: center;
            margin-top: 28px;
            padding-top: 18px;
            border-top: 1px solid var(--border);
        }

        .watermark p {
            font-size: 11.5px;
            color: var(--text-lite);
            line-height: 1.8;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 900px) {
            .panel-right { padding: 44px 36px; }
        }

        @media (max-width: 768px) {
            body { align-items: flex-start; padding: 16px 12px; }

            .login-card {
                flex-direction: column;
                min-height: auto;
                max-width: 480px;
                margin: 0 auto;
            }

            .panel-left {
                width: 100%;
                height: 210px;
                border-radius: 24px 24px 0 0;
                flex-shrink: 0;
            }

            .panel-left-img { border-radius: 24px 24px 0 0; }
            .panel-title { font-size: 20px; }
            .panel-badge { margin-bottom: 14px; }
            .panel-dots { display: none; }

            .panel-right {
                padding: 32px 24px;
                border-radius: 0 0 24px 24px;
            }

            .logo-area img { height: 80px; }
            .form-heading h1 { font-size: 15px; }
        }

        @media (max-width: 480px) {
            .panel-left { height: 190px; }
            .panel-right { padding: 24px 18px; }
            .logo-area img { height: 70px; }
        }
    </style>
</head>

<body>

<div class="bg-layer"></div>
<div class="aurora aurora-1"></div>
<div class="aurora aurora-2"></div>
<div class="aurora aurora-3"></div>
<div class="dot-grid"></div>

<div class="login-card">

    <!-- LEFT PANEL -->
    <div class="panel-left">
        <img src="<?= base_url('img/gmbrkiri.png'); ?>" alt="GKI Bromo" class="panel-left-img">
        <div class="panel-left-overlay"></div>
        <div class="panel-shimmer"></div>

        <div class="panel-left-content">
            <div class="panel-badge">
                <i class="fa-solid fa-wifi"></i>
                RFID Monitoring
            </div>
            <div class="panel-title">
                SISTEM MONITORING RFID<br>GKI BROMO
            </div>
            <p class="panel-subtitle">
                Monitoring Kunjungan Ruangan Secara Real-Time
            </p>
        </div>

        <div class="panel-dots">
            <?php for ($i = 0; $i < 15; $i++): ?>
                <span></span>
            <?php endfor; ?>
        </div>
    </div>

    <!-- RIGHT PANEL — WHITE -->
    <div class="panel-right">

        <div class="logo-area">
            <img src="<?= base_url('img/logogki.png'); ?>" alt="Logo GKI Bromo">
        </div>

        <div class="form-heading">
            <h1>Sistem Monitoring Kunjungan Ruangan<br>oleh Security Berbasis RFID</h1>
            <div class="heading-divider"></div>
        </div>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check me-2"></i>
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('login/auth'); ?>" method="post">

            <?= csrf_field() ?>

            <div class="field-group">
                <label class="field-label" for="username">Nama Pengguna</label>
                <div class="field-wrap">
                    <i class="fa-regular fa-user field-icon-left"></i>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="field-control"
                        placeholder="Masukkan username"
                        autocomplete="username"
                        required>
                </div>
            </div>

            <div class="field-group">
                <label class="field-label" for="password">Kata Sandi</label>
                <div class="field-wrap">
                    <i class="fa-solid fa-lock field-icon-left"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="field-control"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required>
                    <button type="button" class="toggle-pw" id="togglePw" aria-label="Toggle password">
                        <i class="fa-regular fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="fa-solid fa-arrow-right-to-bracket me-2"></i>MASUK
            </button>

        </form>

        <div class="watermark">
            <p>&copy; 2026 GKI Bromo &mdash; Sistem Monitoring Kunjungan RFID</p>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const togglePw   = document.getElementById('togglePw');
    const pwField    = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');

    togglePw.addEventListener('click', function () {
        const isHidden = pwField.type === 'password';
        pwField.type   = isHidden ? 'text' : 'password';
        toggleIcon.className = isHidden ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
    });
</script>

</body>
</html>