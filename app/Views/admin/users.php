<?= $this->extend('layouts/adminkit_template') ?>
<?= $this->section('content') ?>

<style>
    :root {
        --brand: #2563eb;
        --brand-light: #eff6ff;
        --brand-dark: #1d4ed8;
        --success: #16a34a;
        --danger: #dc2626;
        --warning: #d97706;
        --neutral-50: #f8fafc;
        --neutral-100: #f1f5f9;
        --neutral-200: #e2e8f0;
        --neutral-400: #94a3b8;
        --neutral-600: #475569;
        --neutral-800: #1e293b;
        --radius: 12px;
        --shadow-sm: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
        --shadow-md: 0 4px 12px rgba(0,0,0,.08), 0 2px 6px rgba(0,0,0,.04);
    }

    /* ── PAGE HEADER ── */
    .page-header {
        background: linear-gradient(135deg, #1e293b 0%, #2563eb 100%);
        border-radius: var(--radius);
        padding: 28px 32px;
        margin-bottom: 24px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .page-header::after {
        content: '';
        position: absolute;
        right: -30px; top: -30px;
        width: 180px; height: 180px;
        background: rgba(255,255,255,.06);
        border-radius: 50%;
    }
    .page-header h3 { font-size: 1.5rem; font-weight: 700; margin: 0 0 4px; }
    .page-header p  { margin: 0; opacity: .75; font-size: .9rem; }

    /* ── CARD ── */
    .card-modern {
        background: #fff;
        border-radius: var(--radius);
        box-shadow: var(--shadow-md);
        border: 1px solid var(--neutral-200);
        overflow: hidden;
    }
    .card-modern .card-head {
        padding: 18px 24px;
        border-bottom: 1px solid var(--neutral-100);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .card-modern .card-head h5 { margin: 0; font-weight: 700; color: var(--neutral-800); }

    /* ── SEARCH ── */
    .search-wrap { position: relative; }
    .search-wrap input {
        padding-left: 36px;
        border-radius: 8px;
        border: 1px solid var(--neutral-200);
        font-size: .875rem;
        width: 240px;
        transition: border-color .2s, box-shadow .2s;
    }
    .search-wrap input:focus {
        border-color: var(--brand);
        box-shadow: 0 0 0 3px rgba(37,99,235,.12);
        outline: none;
    }
    .search-wrap .bi-search {
        position: absolute;
        left: 10px; top: 50%;
        transform: translateY(-50%);
        color: var(--neutral-400);
        font-size: .85rem;
        pointer-events: none;
    }

    /* ── TABLE ── */
    .table-modern { margin: 0; }
    .table-modern thead th {
        background: var(--neutral-50);
        border-bottom: 2px solid var(--neutral-200);
        color: var(--neutral-600);
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        padding: 12px 16px;
        white-space: nowrap;
    }
    .table-modern tbody td {
        padding: 13px 16px;
        vertical-align: middle;
        border-bottom: 1px solid var(--neutral-100);
        font-size: .875rem;
        color: var(--neutral-800);
    }
    .table-modern tbody tr:last-child td { border-bottom: none; }
    .table-modern tbody tr:hover td { background: var(--brand-light); }

    /* ── AVATAR ── */
    .avatar {
        width: 40px; height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--neutral-200);
        flex-shrink: 0;
    }
    .user-info .fw-semibold { font-size: .875rem; line-height: 1.3; }
    .user-info .text-muted  { font-size: .75rem; }

    /* ── BADGES ── */
    .badge-rfid   { background: #dbeafe; color: #1d4ed8; font-weight: 600; font-size: .72rem; padding: 4px 8px; border-radius: 6px; font-family: monospace; }
    .badge-noreg  { background: var(--neutral-100); color: var(--neutral-400); font-size: .72rem; padding: 4px 8px; border-radius: 6px; }
    .badge-admin  { background: #fee2e2; color: #b91c1c; font-size: .72rem; font-weight: 600; padding: 4px 8px; border-radius: 6px; }
    .badge-staff  { background: #dcfce7; color: #15803d; font-size: .72rem; font-weight: 600; padding: 4px 8px; border-radius: 6px; }
    .badge-aktif  { background: #dcfce7; color: #15803d; font-size: .72rem; font-weight: 600; padding: 4px 8px; border-radius: 6px; }
    .badge-nonaktif { background: var(--neutral-100); color: var(--neutral-400); font-size: .72rem; font-weight: 600; padding: 4px 8px; border-radius: 6px; }

    /* ── ACTION BUTTONS ── */
    .btn-edit {
        background: #fef9c3; color: #854d0e;
        border: 1px solid #fde68a;
        border-radius: 7px; padding: 5px 10px;
        font-size: .8rem; transition: all .15s;
    }
    .btn-edit:hover { background: #fde68a; color: #713f12; }
    .btn-del {
        background: #fee2e2; color: #b91c1c;
        border: 1px solid #fecaca;
        border-radius: 7px; padding: 5px 10px;
        font-size: .8rem; transition: all .15s;
    }
    .btn-del:hover { background: #fecaca; color: #991b1b; }

    /* ── MODAL ── */
    .modal-content { border-radius: var(--radius); border: none; box-shadow: 0 20px 60px rgba(0,0,0,.18); }
    .modal-header  { border-bottom: 1px solid var(--neutral-100); padding: 20px 24px; }
    .modal-header .modal-title { font-weight: 700; color: var(--neutral-800); }
    .modal-body    { padding: 24px; }
    .modal-footer  { border-top: 1px solid var(--neutral-100); padding: 16px 24px; }

    /* ── FORM ── */
    .form-label { font-size: .8rem; font-weight: 600; color: var(--neutral-600); margin-bottom: 5px; }
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid var(--neutral-200);
        font-size: .875rem;
        padding: 9px 13px;
        transition: border-color .2s, box-shadow .2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--brand);
        box-shadow: 0 0 0 3px rgba(37,99,235,.12);
    }

    /* ── AVATAR PREVIEW ── */
    .avatar-preview-wrap { position: relative; display: inline-block; }
    .avatar-preview {
        width: 80px; height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--neutral-200);
    }
    .avatar-change-btn {
        position: absolute; bottom: 0; right: 0;
        background: var(--brand); color: #fff;
        border-radius: 50%; width: 26px; height: 26px;
        display: flex; align-items: center; justify-content: center;
        font-size: .75rem; cursor: pointer; border: 2px solid #fff;
    }

    /* ── ALERT ── */
    .alert-modern {
        border-radius: 10px;
        border: none;
        font-size: .875rem;
        padding: 14px 18px;
        display: flex; align-items: center; gap: 10px;
    }
    .alert-success-modern { background: #dcfce7; color: #15803d; }
    .alert-danger-modern  { background: #fee2e2; color: #b91c1c; }

    /* ── EMPTY STATE ── */
    .empty-state { padding: 60px 20px; text-align: center; color: var(--neutral-400); }
    .empty-state i { font-size: 2.5rem; margin-bottom: 12px; display: block; opacity: .5; }

    /* ── STAT CHIPS ── */
    .stat-chips { display: flex; gap: 10px; margin-bottom: 24px; flex-wrap: wrap; }
    .stat-chip {
        background: #fff;
        border: 1px solid var(--neutral-200);
        border-radius: 10px;
        padding: 12px 20px;
        display: flex; align-items: center; gap: 10px;
        box-shadow: var(--shadow-sm);
        flex: 1; min-width: 140px;
    }
    .stat-chip .icon { width: 36px; height: 36px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
    .stat-chip .label { font-size: .72rem; color: var(--neutral-400); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; }
    .stat-chip .value { font-size: 1.2rem; font-weight: 700; color: var(--neutral-800); line-height: 1; }

    /* responsive table */
    @media (max-width: 768px) {
        .page-header { padding: 20px; }
        .card-modern .card-head { gap: 8px; }
        .search-wrap input { width: 100%; }
    }
</style>

<!-- PAGE HEADER -->
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3><i class="bi bi-people-fill me-2"></i>Manajemen Pengguna</h3>
            <p>Kelola data administrator dan petugas patroli RFID</p>
        </div>
        <!-- UBAH: hapus data-bs-* , pakai id saja -->
        <button type="button" class="btn btn-light fw-semibold" id="btnTambahUser">
            <i class="bi bi-plus-circle me-1"></i> Tambah User
        </button>
    </div>
</div>

<!-- FLASH MESSAGES -->
<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert-modern alert-success-modern mb-4">
        <i class="bi bi-check-circle-fill"></i>
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert-modern alert-danger-modern mb-4">
        <i class="bi bi-exclamation-circle-fill"></i>
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<!-- STAT CHIPS -->
<?php
    $total    = count($users);
    $aktif    = count(array_filter($users, fn($u) => $u['status_aktif'] === 'Aktif'));
    $hasRfid  = count(array_filter($users, fn($u) => !empty($u['uid_rfid'])));
?>
<div class="stat-chips">
    <div class="stat-chip">
        <div class="icon" style="background:#eff6ff;color:#2563eb"><i class="bi bi-people-fill"></i></div>
        <div><div class="label">Total User</div><div class="value"><?= $total ?></div></div>
    </div>
    <div class="stat-chip">
        <div class="icon" style="background:#dcfce7;color:#16a34a"><i class="bi bi-person-check-fill"></i></div>
        <div><div class="label">Aktif</div><div class="value"><?= $aktif ?></div></div>
    </div>
    <div class="stat-chip">
        <div class="icon" style="background:#fef9c3;color:#854d0e"><i class="bi bi-credit-card-fill"></i></div>
        <div><div class="label">Punya RFID</div><div class="value"><?= $hasRfid ?></div></div>
    </div>
    <div class="stat-chip">
        <div class="icon" style="background:#fee2e2;color:#b91c1c"><i class="bi bi-person-x-fill"></i></div>
        <div><div class="label">Nonaktif</div><div class="value"><?= $total - $aktif ?></div></div>
    </div>
</div>

<!-- TABLE CARD -->
<div class="card-modern">
    <div class="card-head">
        <h5><i class="bi bi-table me-2 text-primary"></i>Data Pengguna</h5>
        <div class="search-wrap">
            <i class="bi bi-search"></i>
            <input type="text" id="searchInput" class="form-control" placeholder="Cari nama, username, RFID…">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-modern" id="usersTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Pengguna</th>
                    <th>Username</th>
                    <th>UID RFID</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (! empty($users)) : ?>
                <?php $no = 1; ?>
                <?php foreach ($users as $user) : ?>
                    <?php
                        $foto = ! empty($user['foto_profile'])
                            ? 'uploads/profile/' . $user['foto_profile']
                            : 'uploads/profile/default.png';
                        $isAdmin = $user['nama_role'] === 'Administrator';
                    ?>
                    <tr>
                        <td class="text-muted fw-semibold"><?= $no++ ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2 user-info">
                                <img src="<?= base_url($foto) ?>" class="avatar" alt="">
                                <div>
                                    <div class="fw-semibold"><?= esc($user['nama_lengkap']) ?></div>
                                    <div class="text-muted">ID: <?= esc($user['user_id']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td><span class="text-muted">@</span><?= esc($user['username']) ?></td>
                        <td>
                            <?php if (! empty($user['uid_rfid'])) : ?>
                                <span class="badge-rfid"><i class="bi bi-credit-card me-1"></i><?= esc($user['uid_rfid']) ?></span>
                            <?php else : ?>
                                <span class="badge-noreg">Belum Terdaftar</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="<?= $isAdmin ? 'badge-admin' : 'badge-staff' ?>">
                                <?= esc($user['nama_role']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="<?= $user['status_aktif'] === 'Aktif' ? 'badge-aktif' : 'badge-nonaktif' ?>">
                                <?= esc($user['status_aktif']) ?>
                            </span>
                        </td>
                        <td class="text-muted" style="font-size:.8rem">
                            <?= ! empty($user['last_login'])
                                ? date('d M Y H:i', strtotime($user['last_login']))
                                : '—'
                            ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn-edit"
                                    onclick='editUser(<?= htmlspecialchars(json_encode($user), ENT_QUOTES, "UTF-8") ?>)'>
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <a href="<?= base_url('users/delete/' . $user['user_id']) ?>"
                                   class="btn-del"
                                   onclick="return confirm('Yakin hapus user <?= esc($user['nama_lengkap']) ?>?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <i class="bi bi-people"></i>
                            <div class="fw-semibold mb-1">Belum ada data pengguna</div>
                            <div class="text-muted" style="font-size:.85rem">Tambahkan user pertama dengan klik tombol di atas</div>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<!-- MODAL TAMBAH USER -->
<div class="modal fade" id="modalTambahUser" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahLabel"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Tambah User Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form action="<?= base_url('users/store') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama lengkap" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" placeholder="username" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password" id="tambah_pass" class="form-control" placeholder="Min. 6 karakter" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePass('tambah_pass', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">UID RFID</label>
                            <input type="text" name="uid_rfid" class="form-control" placeholder="Opsional — scan kartu RFID">
                            <div class="form-text">Kosongkan jika kartu belum disiapkan</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role_id" class="form-select" required>
                                <option value="">— Pilih Role —</option>
                                <?php foreach ($roles as $role) : ?>
                                    <option value="<?= $role['role_id'] ?>"><?= esc($role['nama_role']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status Akun</label>
                            <select name="status_aktif" class="form-select">
                                <option value="Aktif">Aktif</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Foto Profile</label>
                            <input type="file" name="foto_profile" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- MODAL EDIT USER -->
<div class="modal fade" id="modalEditUser" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" enctype="multipart/form-data" id="formEditUser">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditLabel"><i class="bi bi-pencil-square me-2 text-warning"></i>Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="avatar-preview-wrap">
                            <img id="preview_foto"
                                 src="<?= base_url('uploads/profile/default.png') ?>"
                                 class="avatar-preview" alt="foto">
                            <label for="edit_foto_file" class="avatar-change-btn" title="Ganti foto">
                                <i class="bi bi-camera-fill"></i>
                            </label>
                        </div>
                        <input type="file" id="edit_foto_file" name="foto_profile" class="d-none" accept="image/*"
                               onchange="previewFoto(this)">
                        <div class="mt-2 text-muted" style="font-size:.75rem">Klik ikon kamera untuk ganti foto</div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" id="edit_username" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" id="edit_nama" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password Baru</label>
                            <div class="input-group">
                                <input type="password" name="password" id="edit_pass" class="form-control" placeholder="Kosongkan jika tidak diubah">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePass('edit_pass', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">UID RFID</label>
                            <input type="text" name="uid_rfid" id="edit_rfid" class="form-control" placeholder="Kosongkan untuk melepas kartu">
                            <div class="form-text">Kosongkan untuk melepaskan kartu dari user ini</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <select name="role_id" id="edit_role" class="form-select" required>
                                <?php foreach ($roles as $role) : ?>
                                    <option value="<?= $role['role_id'] ?>"><?= esc($role['nama_role']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status Akun</label>
                            <select name="status_aktif" id="edit_status" class="form-select" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning px-4"><i class="bi bi-save me-1"></i>Update</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- JAVASCRIPT -->
<script>
(function () {
    // Inisialisasi modal secara manual setelah DOM + Bootstrap siap
    // Ini fix untuk kasus Bootstrap JS di-load di footer (setelah konten)
    var _modalTambah = null;
    var _modalEdit   = null;

    function getModalTambah() {
        if (!_modalTambah) {
            _modalTambah = new bootstrap.Modal(document.getElementById('modalTambahUser'));
        }
        return _modalTambah;
    }

    function getModalEdit() {
        if (!_modalEdit) {
            _modalEdit = new bootstrap.Modal(document.getElementById('modalEditUser'));
        }
        return _modalEdit;
    }

    // Tombol Tambah User
    document.getElementById('btnTambahUser').addEventListener('click', function () {
        getModalTambah().show();
    });

    // Edit modal — dipanggil dari onclick di baris tabel
    window.editUser = function (data) {
        document.getElementById('edit_username').value = data.username     || '';
        document.getElementById('edit_nama').value     = data.nama_lengkap || '';
        document.getElementById('edit_rfid').value     = data.uid_rfid     || '';
        document.getElementById('edit_role').value     = data.role_id      || '';
        document.getElementById('edit_status').value   = data.status_aktif || 'Aktif';
        document.getElementById('edit_pass').value     = '';

        var foto = data.foto_profile
            ? '<?= base_url('uploads/profile/') ?>' + data.foto_profile
            : '<?= base_url('uploads/profile/default.png') ?>';
        document.getElementById('preview_foto').src = foto;

        document.getElementById('formEditUser').action =
            '<?= base_url('users/update/') ?>' + data.user_id;

        getModalEdit().show();
    };

    // Live search
    document.getElementById('searchInput').addEventListener('input', function () {
        var q    = this.value.toLowerCase();
        var rows = document.querySelectorAll('#usersTable tbody tr');
        rows.forEach(function (row) {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
}());

// Preview foto sebelum upload
function previewFoto(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('preview_foto').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Toggle show/hide password
function togglePass(inputId, btn) {
    var input = document.getElementById(inputId);
    var icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>

<?= $this->endSection() ?>