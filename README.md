# Sistem Monitoring Kunjungan Ruangan oleh Security Berbasis RFID untuk Pelaporan Terintegrasi

**Tugas Akhir — Program Studi Informatika**
Fakultas Sains dan Teknologi, Universitas Bhinneka Nusantara (UBHINUS)

**Penyusun:** Gabriel Patrick Wahyu Mazmur Ariaji (221111002)
**Dosen Pembimbing:** Jozua Ferjanus Palandi, S.Kom., M.Kom.
**Studi Kasus:** Gereja GKI Bromo, Malang

---

## 📌 Deskripsi Aplikasi

Pencatatan patroli keamanan yang masih dilakukan secara manual menyebabkan data kunjungan rentan terhadap kesalahan, manipulasi, dan sulit diverifikasi secara objektif. **RFID Patrol** adalah sistem monitoring kunjungan ruangan oleh petugas keamanan (security) berbasis **RFID** dan **mikrokontroler ESP32**, yang dirancang untuk merekam identitas petugas, waktu, dan lokasi kunjungan secara otomatis dan real-time, sekaligus memvalidasi kesesuaian jadwal shift serta urutan rute patroli.

Validasi urutan rute patroli dilakukan menggunakan algoritma **Longest Common Subsequence (LCS)**, yaitu dengan membandingkan urutan ruangan yang di-scan secara aktual terhadap urutan ideal yang telah ditetapkan, sehingga tingkat kepatuhan petugas terhadap rute dapat diukur secara kuantitatif dan objektif.

Hasil kunjungan disajikan melalui aplikasi web dengan dua peran pengguna:
- **Administrator** — memantau dan mengelola seluruh data patroli, pengguna, perangkat, jadwal, dan laporan evaluasi.
- **Petugas Security** — melihat jadwal dan riwayat kunjungannya sendiri melalui dashboard pribadi.

## ✨ Fitur Utama

Sistem terdiri atas delapan modul fungsional utama:

1. **Autentikasi** — login berbasis peran (Administrator / Petugas) dengan validasi kredensial.
2. **Dashboard Administrator** — statistik total pengguna aktif, total perangkat, jumlah scan hari ini, jumlah kartu asing terdeteksi, grafik aktivitas scan 30 hari terakhir, live log scan terbaru, status perangkat, dan jadwal shift hari ini.
3. **Manajemen Pengguna** — CRUD data pengguna beserta foto profil, peran, dan UID kartu RFID terhubung, dengan validasi duplikasi di sisi server.
4. **Manajemen Perangkat** — pemantauan status setiap unit ESP32 (Online/Offline/Maintenance), tipe perangkat, ruangan, alamat IP, dan waktu terakhir online.
5. **Log RFID** — riwayat seluruh aktivitas scan dari seluruh perangkat, dengan filter tanggal, jenis kartu, status validasi, dan ekspor ke CSV/Excel/PDF; diperbarui otomatis via live polling.
6. **Jadwal Patroli** — kalender bulanan interaktif untuk penjadwalan shift dan dua petugas bertugas, lengkap dengan ekspor jadwal ke Excel.
7. **Laporan & Evaluasi LCS** — evaluasi kepatuhan rute patroli per sesi shift (persentase coverage, nilai LCS, status Valid/Normal/Warning/Tidak Lengkap), dengan opsi evaluasi ulang manual dan ekspor Excel.
8. **Dashboard Petugas** — statistik pribadi (jadwal bulan ini, jadwal hari ini, scan berhasil, titik terlewat), kalender jadwal pribadi, riwayat aktivitas, dan unduh jadwal dinas dalam format Excel.

Pada sisi perangkat keras, setiap unit ESP32 memberikan umpan balik langsung kepada petugas melalui **LCD** dan **buzzer** (pola bunyi berbeda untuk status: sesuai jadwal, tidak sesuai urutan, tidak terjadwal, atau kartu tidak dikenali).

## 🛠️ Teknologi yang Digunakan

| Kategori | Teknologi |
|---|---|
| Mikrokontroler | ESP32 (varian 38-pin & 30-pin) |
| Modul RFID | MFRC522 (protokol SPI) |
| Firmware | Arduino C++ (Arduino IDE) |
| Backend & Web Dashboard | PHP 8, Framework **CodeIgniter 4** (REST API + MVC) |
| Basis Data | **MySQL** / MariaDB (relasional, 12 entitas) |
| Algoritma Evaluasi | Longest Common Subsequence (LCS) — Dynamic Programming |
| Kontainerisasi | **Docker** & Docker Compose |

### Struktur Basis Data (12 Entitas)

`users`, `roles`, `kartu_rfid`, `perangkat_rfid`, `ruangan`, `shift`, `jadwal_shift`, `kunjungan`, `patroli_hasil`, `patroli_lcs_log`, `patroli_titik_terlewat`, `log_sistem`.

## 📂 Struktur Repository

```
sistem_monitoring_rfid/
├── app/                     # Source code aplikasi CodeIgniter 4
├── firmware/                # Kode firmware ESP32 (Arduino C++)
├── gkibrmpa_rfidpatrol.sql  # Dump struktur & data awal basis data
├── Dockerfile               # Image aplikasi (PHP + CodeIgniter 4)
├── docker-compose.yml       # Orkestrasi service app & database
├── .env.example             # Contoh konfigurasi environment
└── README.md
```

## 🚀 Instalasi & Menjalankan Aplikasi (via Docker)

### Prasyarat
- [Docker](https://www.docker.com/) & Docker Compose sudah terpasang
- Git

### Langkah-langkah

1. **Clone repository**
   ```bash
   git clone https://github.com/Mazmurgthski/sistem-monitoring-kunjungan-rfid.git
   cd sistem-monitoring-kunjungan-rfid
   ```

2. **Salin file environment**
   ```bash
   cp .env.example .env
   ```
   Sesuaikan variabel berikut sesuai kebutuhan (harus konsisten dengan `docker-compose.yml`):
   ```
   DB_HOST=db
   DB_DATABASE=rfid_patroli_local
   DB_USERNAME=rfid_dev
   DB_PASSWORD=dev12345
   ```

3. **Build & jalankan container**
   ```bash
   docker compose up -d --build
   ```
   Perintah ini akan menyiapkan dua container:
   - `rfid_patroli_app` — aplikasi web (CodeIgniter 4), diakses pada `http://localhost:8080`
   - `rfid_patroli_db` — basis data MySQL, expose pada port `3307`

4. **Import struktur & data awal basis data**

   Linux/macOS/CMD:
   ```bash
   docker exec -i rfid_patroli_db mysql -u rfid_dev -pdev12345 rfid_patroli_local < gkibrmpa_rfidpatrol.sql
   ```
   PowerShell (Windows):
   ```powershell
   Get-Content gkibrmpa_rfidpatrol.sql | docker exec -i rfid_patroli_db mysql -u rfid_dev -pdev12345 rfid_patroli_local
   ```

5. **Verifikasi import berhasil**
   ```bash
   docker exec -i rfid_patroli_db mysql -u rfid_dev -pdev12345 rfid_patroli_local -e "SHOW TABLES;"
   ```
   Pastikan 12 tabel (users, kartu_rfid, kunjungan, dll.) muncul.

6. **Akses aplikasi**

   Buka browser dan kunjungi:
   ```
   http://localhost:8080
   ```

### Menghentikan aplikasi
```bash
docker compose down
```

## 📡 Konfigurasi Firmware ESP32

Firmware diunggah melalui Arduino IDE dan memerlukan konfigurasi berikut pada masing-masing perangkat:

- SSID & password WiFi lokal
- `serverUrl` — mengarah ke alamat host tempat aplikasi web dijalankan (mis. `http://<IP-server>:8080`)
- `alatId` — kode unik perangkat (mis. `DEVICE_01`)

Endpoint yang digunakan perangkat untuk mengirim hasil scan:
```
POST /api/rfid/scan
Content-Type: application/json

{ "uid_rfid": "<UID_HEX>", "alat_id": "<ID_PERANGKAT>" }
```

## 👥 Aktor Sistem

| Aktor | Deskripsi |
|---|---|
| **Petugas Keamanan** | Melakukan tapping kartu RFID di setiap titik ruangan sesuai jadwal patroli; memantau jadwal dan riwayat aktivitas pribadi. |
| **Administrator** | Mengelola data pengguna, perangkat, dan jadwal patroli; memantau aktivitas scan real-time; mengevaluasi kepatuhan rute via laporan LCS. |

## 📄 Lisensi & Sitasi

Proyek ini merupakan bagian dari Tugas Akhir pada Program Studi Informatika, Fakultas Sains dan Teknologi, Universitas Bhinneka Nusantara, 2026. Dikembangkan sesuai **Buku Panduan Pengajuan Seminar Akhir TA** (Nomor Dokumen: 03/SAINTEK.PANDUAN/UBHINUS/IV/2026), termasuk ketentuan standarisasi teknis Bab III mengenai penggunaan repository GitHub dan Docker.