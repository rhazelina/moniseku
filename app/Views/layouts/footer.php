<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
/**
 * GKI Bromo — RFID Monitoring
 * Sidebar Controller v5
 * ─────────────────────────────────────────────────────────────────
 *
 * PERBAIKAN UTAMA v5:
 *
 * 1. CSS tablet TIDAK lagi punya default `width: var(--sb-collapsed)`.
 *    State collapsed tablet kini dikontrol via class `.is-tablet-collapsed`
 *    yang ditambahkan JS — bukan CSS media query.
 *    Ini menghilangkan race condition: dulu CSS collapse dulu, JS expand
 *    belakangan → flash/flicker dan sidebar stuck collapsed.
 *
 * 2. State tablet disimpan di localStorage (key: rfid_sb_tablet).
 *    Default: expanded (true). Tidak direset saat navigasi antar halaman.
 *
 * 3. resize handler tidak lagi reset tabletExpanded ke false.
 *    State tetap terjaga saat resize window.
 *
 * 4. margin-left mainArea dikontrol 100% oleh JS, bukan CSS.
 *
 * Breakpoints:
 *   ≥1200px  Desktop : collapse/expand via hamburger (persisted)
 *   768–1199 Tablet  : default expanded, hamburger toggle (persisted)
 *   <768     Mobile  : off-canvas drawer + overlay
 */
(function () {
    'use strict';

    var sidebar   = document.getElementById('mainSidebar');
    var mainArea  = document.getElementById('mainArea');
    var overlay   = document.getElementById('sbOverlay');
    var hamburger = document.getElementById('sbHamburger');

    if (!sidebar || !mainArea || !hamburger) return;

    var BP_TABLET  = 768;
    var BP_DESKTOP = 1200;

    var DESKTOP_KEY = 'rfid_sb_collapsed';
    var TABLET_KEY  = 'rfid_sb_tablet';

    /* Baca CSS variable */
    var rootStyle = getComputedStyle(document.documentElement);
    var SB_WIDTH  = rootStyle.getPropertyValue('--sb-width').trim();     /* "252px" */
    var SB_COLL   = rootStyle.getPropertyValue('--sb-collapsed').trim(); /* "64px"  */

    /* ── Zone helper ─────────────────────────────────── */
    function zone() {
        var w = window.innerWidth;
        if (w < BP_TABLET)  return 'mobile';
        if (w < BP_DESKTOP) return 'tablet';
        return 'desktop';
    }

    /* ── Set margin-left mainArea ────────────────────── */
    function setMargin(ml) {
        mainArea.style.marginLeft = ml;
    }

    /* ── Bersihkan semua class state sidebar ─────────── */
    function clearAllStates() {
        sidebar.classList.remove(
            'is-collapsed',
            'is-tablet-collapsed',
            'tablet-expanded',
            'is-open'
        );
    }

    /* ════════════════════════════════════════════════════
       DESKTOP
    ════════════════════════════════════════════════════ */
    var storedDesktop    = localStorage.getItem(DESKTOP_KEY);
    var desktopCollapsed = storedDesktop === null ? false : storedDesktop === 'true';

    function applyDesktop() {
        clearAllStates();
        if (overlay) {
            overlay.classList.remove('active');
            overlay.setAttribute('aria-hidden', 'true');
        }
        document.body.style.overflow = '';

        if (desktopCollapsed) {
            sidebar.classList.add('is-collapsed');
            setMargin(SB_COLL);
        } else {
            setMargin(SB_WIDTH);
        }

        hamburger.setAttribute('aria-expanded', String(!desktopCollapsed));
    }

    function toggleDesktop() {
        desktopCollapsed = !desktopCollapsed;
        localStorage.setItem(DESKTOP_KEY, String(desktopCollapsed));
        applyDesktop();
    }

    /* ════════════════════════════════════════════════════
       TABLET
       FIX: default expanded, state persisted di localStorage.
            Collapsed pakai class `is-tablet-collapsed` (bukan
            CSS media query default) — tidak ada race condition.
    ════════════════════════════════════════════════════ */
    var storedTablet  = localStorage.getItem(TABLET_KEY);
    var tabletExpanded = storedTablet === null ? true : storedTablet !== 'false';

    function applyTablet() {
        clearAllStates();
        if (overlay) {
            overlay.classList.remove('active');
            overlay.setAttribute('aria-hidden', 'true');
        }
        document.body.style.overflow = '';

        if (tabletExpanded) {
            sidebar.classList.add('tablet-expanded');
            setMargin(SB_WIDTH);
        } else {
            sidebar.classList.add('is-tablet-collapsed');
            setMargin(SB_COLL);
        }

        hamburger.setAttribute('aria-expanded', String(tabletExpanded));
    }

    function toggleTablet() {
        tabletExpanded = !tabletExpanded;
        localStorage.setItem(TABLET_KEY, String(tabletExpanded));
        applyTablet();
    }

    /* ════════════════════════════════════════════════════
       MOBILE
    ════════════════════════════════════════════════════ */
    function openMobile() {
        clearAllStates();
        sidebar.classList.add('is-open');
        if (overlay) {
            overlay.classList.add('active');
            overlay.removeAttribute('aria-hidden');
        }
        document.body.style.overflow = 'hidden';
        setMargin('0px');
        hamburger.setAttribute('aria-expanded', 'true');
    }

    function closeMobile() {
        sidebar.classList.remove('is-open');
        if (overlay) {
            overlay.classList.remove('active');
            overlay.setAttribute('aria-hidden', 'true');
        }
        document.body.style.overflow = '';
        setMargin('0px');
        hamburger.setAttribute('aria-expanded', 'false');
    }

    function toggleMobile() {
        sidebar.classList.contains('is-open') ? closeMobile() : openMobile();
    }

    /* ════════════════════════════════════════════════════
       EVENT LISTENERS
    ════════════════════════════════════════════════════ */

    /* Hamburger */
    hamburger.addEventListener('click', function () {
        var z = zone();
        if      (z === 'mobile')  toggleMobile();
        else if (z === 'tablet')  toggleTablet();
        else                      toggleDesktop();
    });

    /* Overlay click → tutup */
    if (overlay) {
        overlay.addEventListener('click', function () {
            if (zone() === 'mobile') closeMobile();
        });
    }

    /* ESC → tutup */
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        var z = zone();
        if (z === 'mobile') {
            closeMobile();
        } else if (z === 'tablet' && tabletExpanded) {
            tabletExpanded = false;
            localStorage.setItem(TABLET_KEY, 'false');
            applyTablet();
        }
    });

    /* Resize → re-apply state zona yang sesuai.
       FIX: tidak reset tabletExpanded, state tetap terjaga. */
    var resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            var z = zone();
            document.body.style.overflow = '';

            if (z === 'desktop') {
                applyDesktop();
            } else if (z === 'tablet') {
                applyTablet();
            } else {
                /* mobile */
                clearAllStates();
                setMargin('0px');
            }
        }, 80);
    });

    /* ════════════════════════════════════════════════════
       INIT — jalankan sekali saat halaman dimuat
    ════════════════════════════════════════════════════ */
    (function init() {
        var z = zone();
        if (z === 'desktop') {
            applyDesktop();
        } else if (z === 'tablet') {
            applyTablet();
        } else {
            clearAllStates();
            setMargin('0px');
        }
    })();

    /* ════════════════════════════════════════════════════
       LIVE CLOCK
    ════════════════════════════════════════════════════ */
    var clockEl = document.getElementById('liveClock');

    function tickClock() {
        if (!clockEl) return;
        var now  = new Date();
        var hh   = String(now.getHours()).padStart(2, '0');
        var mm   = String(now.getMinutes()).padStart(2, '0');
        var ss   = String(now.getSeconds()).padStart(2, '0');
        var day  = now.toLocaleDateString('id-ID', { weekday: 'long' });
        var date = now.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        clockEl.textContent = day + ', ' + date + '  ' + hh + ':' + mm + ':' + ss;
    }

    tickClock();
    setInterval(tickClock, 1000);

}());
</script>

<!-- Footer bar -->
<footer class="footer-bar" role="contentinfo">
    <span>© <?= date('Y') ?> GKI Bromo &mdash; RFID Monitoring System</span>
    <div class="fr">
        <div class="footer-dot"></div>
        <span>All systems operational</span>
    </div>
</footer>

</body>
</html>