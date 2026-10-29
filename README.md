# SEVIMA UKT Payment Webhook Gateway (Project M2)

Sistem Penagihan UKT (*Uang Kuliah Tunggal*) Multi-Kampus berbasis **Laravel 13, Inertia.js (Vue 3), dan PostgreSQL** dengan integrasi notifikasi pembayaran Payment Gateway via webhook aman berbasis HMAC-SHA256, penentuan hak akses KRS mahasiswa, dan audit trail lengkap.

Project ini dibuat untuk memenuhi **Tes Teknis Software Engineer SEVIMA (Jalur Middle/Senior - Project M2)**.

---

## 🚀 Cara Menjalankan Aplikasi (Docker 1-Command)

Aplikasi telah dikonfigurasi penuh dengan Docker Compose multi-stage build. Reviewer cukup menjalankan **satu perintah** dari direktori root project:

```bash
docker compose up --build
```

Setelah proses build selesai, buka browser Anda:
- **Aplikasi Web:** [http://localhost:8000](http://localhost:8000)
- **Mock Payment Gateway Simulator:** [http://localhost:8000/simulator](http://localhost:8000/simulator)
- **API Endpoint Mahasiswa:** `GET http://localhost:8000/api/v1/students/{studentId}/bills`
- **Webhook Receiver Endpoint:** `POST http://localhost:8000/api/v1/webhook/payment`

> **Otomatisasi Startup Container:** Container `app` akan otomatis menunggu database PostgreSQL siap, menjalankan migrasi database, men-seed 3 institusi dan akun demo, mengompilasi asset Vue 3 Vite, mengoptimalkan konfigurasi cache, dan menyalakan server Nginx + PHP-FPM.

---

## 💻 Alternatif: Menjalankan di Lingkungan Lokal (Tanpa Docker)

Jika Anda ingin menjalankan secara native pada mesin host yang sudah memiliki PHP 8.3+, Composer, Node.js, dan PostgreSQL:

```bash
# 1. Install dependensi backend & frontend
composer install
npm install

# 2. Setup environment & database
cp .env.example .env
php artisan key:generate

# Konfigurasi koneksi database PostgreSQL di file .env Anda:
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=sevima_ukt
# DB_USERNAME=postgres
# DB_PASSWORD=your_password

# 3. Jalankan migrasi dan seeder data
php artisan migrate:fresh --seed

# 4. Build asset frontend & jalankan server
npm run build
php artisan serve
```

---

## 👥 Matriks Akun Demo & Institusi

Database telah di-seed dengan **3 institusi berbeda**, lengkap dengan akun demo untuk masing-masing role:

> **Fitur Quick Demo Login:** Pada halaman login ([http://localhost:8000/login](http://localhost:8000/login)), telah disediakan tombol **1-Klik Login** untuk setiap akun di bawah ini tanpa perlu mengetik email & password secara manual.

| Institusi | Kode | Peran (Role) | Nama Pengguna | Email | Password |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Univ. Negeri Gadjah Sakti** | `UNIV-A` | Admin Keuangan | Admin Gadjah Sakti | `admin.univa@sevima.test` | `password` |
| | | Mahasiswa (Aktif) | Budi Santoso (NIM: 2024010001) | `budi.univa@sevima.test` | `password` |
| | | Mahasiswa (Lunas) | Siti Nurhaliza (NIM: 2024010002) | `siti.univa@sevima.test` | `password` |
| **Inst. Teknologi Nusantara** | `UNIV-B` | Admin Keuangan | Admin Keuangan ITN | `admin.univb@sevima.test` | `password` |
| | | Mahasiswa | Andi Pratama (NIM: 2024020001) | `andi.univb@sevima.test` | `password` |
| **Poltek Harapan Bangsa** | `UNIV-C` | Admin Keuangan | Admin Poltek Harapan | `admin.univc@sevima.test` | `password` |
| | | Mahasiswa | Maya Anggraini (NIM: 2024030001) | `maya.univc@sevima.test` | `password` |

### 🔐 Manajemen Kepatuhan Secret HMAC (Sesuai Ketentuan Soal)

> ⚠️ **Catatan Kepatuhan Evaluasi:**  
> Sesuai instruksi soal tes: *"Secret HMAC mohon dikirim terpisah melalui email. Secret tidak boleh tertulis di repositori."*  
> Oleh karena itu:
> 1. Secret diinjeksi secara aman melalui Environment Variable (`HMAC_SECRET_UNIV_A`, `HMAC_SECRET_UNIV_B`, `HMAC_SECRET_UNIV_C`).
> 2. Pada antarmuka visual Simulator, nilai secret dimasking secara default (`••••••••`) dengan tombol *toggle* mata 👁️ untuk kepatuhan *clean desk* saat *screen sharing* wawancara Tahap 2.
> 3. Penguji dapat menggunakan secret default lokal untuk keperluan evaluasi atau mengubahnya melalui file `.env`.
> 4. Daftar teks secret HMAC resmi untuk ketiga institusi dikirimkan terpisah melalui email submission resmi (lihat template di bawah).

---

## 📧 Draf Email Pengumpulan Tes (Submission Template)

Reviewer dapat menyalin format email di bawah ini untuk pengiriman resmi sebelum tenggat waktu:

```text
Kepada: nur.arifah@sevima.com, careers@sevima.com, alfian@sevima.com, mahendri@sevima.com
Subjek: [Nama Anda] TaskSE- SEVIMA

Yth. Tim Rekrutmen & Penilai Teknis SEVIMA,

Berikut saya sampaikan tautan repositori penyelesaian Tes Teknis Software Engineer (Project M2: Integrasi Pembayaran UKT via Webhook):

- Tautan Repositori: [URL Repositori GitHub/GitLab Anda]
- Tautan Mock Gateway Simulator: http://localhost:8000/simulator
- Dokumentasi Endpoint Webhook: http://localhost:8000/simulator (In-App) atau file WEBHOOK_DOCS.md

Sesuai instruksi soal ("Secret HMAC mohon dikirim terpisah melalui email dan tidak boleh tertulis di repositori"), berikut adalah kredensial secret HMAC untuk 3 institusi pengujian:
1. Universitas Negeri Gadjah Sakti (UNIV-A) : secret_univ_a_98fbc12a
2. Institut Teknologi Nusantara (UNIV-B)     : secret_univ_b_77ea410d
3. Politeknik Harapan Bangsa (UNIV-C)        : secret_univ_c_13cd99fa

Aplikasi dapat dijalankan secara instan dengan 1 perintah:
$ docker compose up --build

Akun demo untuk masing-masing role (Admin Keuangan & Mahasiswa) telah terpasang dengan fitur 1-Klik Quick Login pada http://localhost:8000/login.

Terima kasih atas kesempatan yang diberikan.

Salam hormat,
[Nama Anda]
```

---

## 🧪 Menjalankan Automated Test Suite

Aplikasi dilengkapi dengan **36 unit dan feature tests** yang mencakup logika bisnis inti, isolasi multi-tenant, dan format payload bilingual:

```bash
php artisan test
```

### Lingkup Test Otomatis:
1. **`MultiTenantIsolationTest.php`:**
   - Memastikan Admin Institusi A **tidak dapat melihat** tagihan milik Institusi B.
   - Memastikan nomor tagihan yang sama (`TAG-IDENTIK-01`) **dapat ada bersamaan** di Institusi A dan B (pembuktian *composite unique index* `[institusi_id, nomor_tagihan]`).
   - Memastikan Admin Institusi A **gagal menerbitkan tagihan** untuk mahasiswa Institusi B.
2. **`WebhookPaymentTest.php`:**
   - Notifikasi valid dengan HMAC-SHA256 yang benar berhasil mengubah status menjadi `lunas`, mencatat riwayat transaksi, dan menuliskan audit log.
   - Notifikasi dengan signature yang dimanipulasi (*tampered*) ditolak dengan HTTP 401 Unauthorized dan kegagalan dicatat di audit log.
   - Pengujian **Idempotency**: pengiriman event `payment.success` berulang tidak menyebabkan *double-credit* atau duplikasi ledger.
   - Pengujian **Out-of-Order**: event `payment.expired` yang datang terlambat diabaikan jika tagihan sudah berstatus `lunas`.
3. **`StudentBillingApiTest.php`:**
   - Memverifikasi endpoint status tagihan, status kelayakan KRS, dan riwayat transaksi mahasiswa.

---

## 🛠️ Mock Payment Gateway Simulator

Sesuai ketentuan soal, kami menyediakan modul Mock Payment Gateway interaktif di URL `/simulator`:
1. Buka [http://localhost:8000/simulator](http://localhost:8000/simulator).
2. Pilih institusi kampus, pilih tagihan mahasiswa, dan pilih event (`payment.success` atau `payment.expired`).
3. Opsi **"Uji Keamanan (Tamper Signature)"** dapat dicentang untuk menguji penolakan signature palsu.
4. Klik tombol kirim untuk melihat inspeksi payload, kalkulasi `X-Signature`, dan live response secara seketika.
5. Tabel **Audit Log Real-Time** di bagian bawah secara otomatis merekam setiap transaksi yang masuk.

Dokumentasi lengkap format payload, event, dan contoh cURL dapat dibaca di **[WEBHOOK_DOCS.md](./WEBHOOK_DOCS.md)**.

---

## 📑 Asumsi Bisnis & Teknis

1. **Kebijakan Pembayaran Kedaluwarsa (Forgiving Late Settlement):** Jika mahasiswa melakukan pembayaran dan payment gateway berhasil mendebit dana saat tagihan sudah berstatus `kedaluwarsa`, sistem tetap menerima notifikasi `payment.success` dan mengubah status menjadi `lunas` dengan catatan audit khusus, agar hak akademik mahasiswa (KRS) tidak dirugikan sepihak.
2. **Single Database Row-Level Multi-Tenancy:** Dipilih model arsitektur *Single Database dengan Row-Level Scoping* (`institusi_id`) karena paling hemat resource, mudah di-maintenance, dan efisien untuk melayani ratusan institusi perguruan tinggi.
3. **Primary Key UUIDv7:** Seluruh primary key menggunakan PostgreSQL native `UUID` dengan generator `UUIDv7` (RFC 9562) untuk mencegah kebocoran IDOR dan menjaga performa index B-tree tetap optimal setara auto-increment integer.

---

## 📂 Artefak Wajib Project

- **[ANTIGRAVITY.md](./ANTIGRAVITY.md)** / **[CLAUDE.md](./CLAUDE.md)**: Konteks project, aturan bisnis multi-institusi, konvensi kode, dan batasan AI.
- **[AI_NOTES.md](./AI_NOTES.md)**: Refleksi pengerjaan pairing bersama AI assistant (hal yang dikerjakan bersama AI, saran AI yang dikoreksi/ditolak, dan evaluasi).
- **[DECISIONS.md](./DECISIONS.md)**: 5 Catatan Keputusan Arsitektur (*Architectural Decision Records / ADRs*).
- **[WEBHOOK_DOCS.md](./WEBHOOK_DOCS.md)**: Dokumentasi format payload, signature, event, dan respon webhook gateway.
