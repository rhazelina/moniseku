<?= $this->extend('layouts/adminkit_template') ?>

<?= $this->section('content') ?>

<!-- PAGE HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h3 class="fw-bold mb-1">
            Audit Trail
        </h3>

        <p class="text-muted mb-0">
            Monitoring aktivitas sistem dan riwayat akses pengguna.
        </p>
    </div>

    <button class="btn btn-primary">
        <i class="bi bi-download me-2"></i>
        Export Log
    </button>

</div>

<!-- FILTER -->
<div class="card border-0 shadow-sm rounded-4 mb-4">

    <div class="card-body">

        <div class="row g-3">

            <div class="col-md-4">

                <label class="form-label fw-semibold">
                    Cari Pengguna
                </label>

                <input
                    type="text"
                    class="form-control"
                    placeholder="Masukkan nama pengguna"
                >

            </div>

            <div class="col-md-3">

                <label class="form-label fw-semibold">
                    Tanggal
                </label>

                <input
                    type="date"
                    class="form-control"
                >

            </div>

            <div class="col-md-3">

                <label class="form-label fw-semibold">
                    Jenis Aktivitas
                </label>

                <select class="form-select">

                    <option selected>
                        Semua Aktivitas
                    </option>

                    <option>
                        Login
                    </option>

                    <option>
                        Logout
                    </option>

                    <option>
                        Tambah Jadwal
                    </option>

                    <option>
                        Export Laporan
                    </option>

                </select>

            </div>

            <div class="col-md-2 d-flex align-items-end">

                <button class="btn btn-primary w-100">

                    <i class="bi bi-search me-2"></i>
                    Filter

                </button>

            </div>

        </div>

    </div>

</div>

<!-- TABLE -->
<div class="card border-0 shadow-sm rounded-4">

    <div class="card-body">

        <div class="table-responsive">

            <table class="table align-middle">

                <thead>

                    <tr>

                        <th>No</th>
                        <th>Pengguna</th>
                        <th>Aktivitas</th>
                        <th>Detail</th>
                        <th>IP Address</th>
                        <th>Waktu</th>
                        <th>Status</th>

                    </tr>

                </thead>

                <tbody>

                    <tr>

                        <td>1</td>

                        <td>

                            <div class="d-flex align-items-center gap-3">

                                <img
                                    src="<?= base_url('img/user.png'); ?>"
                                    width="42"
                                    height="42"
                                    class="rounded-circle border"
                                >

                                <div>

                                    <div class="fw-semibold">
                                        Gabriel Patrick
                                    </div>

                                    <small class="text-muted">
                                        Administrator
                                    </small>

                                </div>

                            </div>

                        </td>

                        <td>

                            <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">

                                Login Sistem

                            </span>

                        </td>

                        <td>

                            Berhasil login ke dashboard admin

                        </td>

                        <td>

                            192.168.1.20

                        </td>

                        <td>

                            14 Mei 2026
                            <br>

                            <small class="text-muted">
                                08:21 WIB
                            </small>

                        </td>

                        <td>

                            <span class="badge bg-success px-3 py-2 rounded-pill">
                                Berhasil
                            </span>

                        </td>

                    </tr>

                    <tr>

                        <td>2</td>

                        <td>

                            <div class="d-flex align-items-center gap-3">

                                <img
                                    src="<?= base_url('img/user.png'); ?>"
                                    width="42"
                                    height="42"
                                    class="rounded-circle border"
                                >

                                <div>

                                    <div class="fw-semibold">
                                        Admin Utama
                                    </div>

                                    <small class="text-muted">
                                        Super Admin
                                    </small>

                                </div>

                            </div>

                        </td>

                        <td>

                            <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill">

                                Export Data

                            </span>

                        </td>

                        <td>

                            Mengunduh laporan kunjungan patroli

                        </td>

                        <td>

                            192.168.1.12

                        </td>

                        <td>

                            14 Mei 2026
                            <br>

                            <small class="text-muted">
                                10:14 WIB
                            </small>

                        </td>

                        <td>

                            <span class="badge bg-success px-3 py-2 rounded-pill">
                                Berhasil
                            </span>

                        </td>

                    </tr>

                    <tr>

                        <td>3</td>

                        <td>

                            <div class="d-flex align-items-center gap-3">

                                <img
                                    src="<?= base_url('img/user.png'); ?>"
                                    width="42"
                                    height="42"
                                    class="rounded-circle border"
                                >

                                <div>

                                    <div class="fw-semibold">
                                        Petugas Malam
                                    </div>

                                    <small class="text-muted">
                                        Petugas
                                    </small>

                                </div>

                            </div>

                        </td>

                        <td>

                            <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill">

                                Gagal Login

                            </span>

                        </td>

                        <td>

                            Password salah saat login sistem

                        </td>

                        <td>

                            192.168.1.35

                        </td>

                        <td>

                            14 Mei 2026
                            <br>

                            <small class="text-muted">
                                11:32 WIB
                            </small>

                        </td>

                        <td>

                            <span class="badge bg-danger px-3 py-2 rounded-pill">
                                Gagal
                            </span>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?= $this->endSection() ?>