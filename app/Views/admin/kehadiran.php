<?= $this->include('layouts/header') ?>

<div class="main-wrapper">

    <!-- SIDEBAR -->
    <?= $this->include('layouts/sidebar') ?>

    <!-- MAIN CONTENT -->
    <div class="main-content">

        <!-- TOPBAR -->
        <div class="topbar">

            <div class="topbar-left">

                <div>
                    <h4><?= $title ?? 'Data Kehadiran'; ?></h4>
                    <p>Monitoring kehadiran RFID pengguna</p>
                </div>

            </div>

        </div>

        <!-- CONTENT -->
        <div class="content-wrapper">

            <!-- FILTER CARD -->
            <div class="card border-0 shadow-sm mb-3">

                <div class="card-body">

                    <div class="row g-2">

                        <div class="col-md-3">
                            <input type="date" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <select class="form-select">
                                <option>Semua Status</option>
                                <option>Hadir</option>
                                <option>Terlambat</option>
                                <option>Tidak Hadir</option>
                            </select>
                        </div>

                        <div class="col-md-6 text-end">
                            <button class="btn btn-primary">
                                <i class="bi bi-search me-1"></i> Filter
                            </button>

                            <button class="btn btn-success">
                                <i class="bi bi-download me-1"></i> Export
                            </button>
                        </div>

                    </div>

                </div>

            </div>

            <!-- TABLE CARD -->
            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white">

                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-check me-2"></i>
                        Data Kehadiran
                    </h5>

                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>RFID UID</th>
                                    <th>Waktu Masuk</th>
                                    <th>Waktu Keluar</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>

                                <!-- DUMMY DATA -->
                                <tr>
                                    <td>1</td>
                                    <td>Andi Wijaya</td>
                                    <td>RFID-001-A</td>
                                    <td>08:00:12</td>
                                    <td>17:00:05</td>
                                    <td>
                                        <span class="badge bg-success">Hadir</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td>2</td>
                                    <td>Budi Santoso</td>
                                    <td>RFID-002-B</td>
                                    <td>08:45:10</td>
                                    <td>-</td>
                                    <td>
                                        <span class="badge bg-warning text-dark">Terlambat</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td>3</td>
                                    <td>Citra Lestari</td>
                                    <td>RFID-003-C</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>
                                        <span class="badge bg-danger">Tidak Hadir</span>
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<?= $this->include('layouts/footer') ?>