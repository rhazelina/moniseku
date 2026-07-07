<?= $this->include('layouts/header') ?>


<div class="app-shell">


    <?= $this->include('layouts/sidebar') ?>


    <!-- MAIN AREA — id="mainArea" dipakai JS untuk set margin-left -->
    <div class="main-area" id="mainArea">


        <!-- TOPBAR: judul | status · jam · logo -->
        <header class="topbar" role="banner">


            <div class="tb-page-info">
                <h5><?= $title ?? 'Dashboard'; ?></h5>
                <span>Sistem Monitoring Kunjungan RFID</span>
            </div>


            <div class="tb-right">
                <div class="tb-status">
                    <span class="pulse"></span>
                    <span class="tb-status-text">Online</span>
                </div>


                <span class="tb-datetime" id="liveClock"></span>


                <div class="tb-divider"></div>


                <img
                    src="<?= base_url('img/logogki.png'); ?>"
                    alt="GKI Bromo"
                    class="tb-logo"
                    onerror="this.style.display='none'">
            </div>


        </header>


        <!-- PAGE CONTENT -->
        <main class="content-wrapper" id="pageContent">
            <?= $this->renderSection('content') ?>
        </main>


    </div><!-- /.main-area -->


</div><!-- /.app-shell -->


<?= $this->include('layouts/footer') ?>