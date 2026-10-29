# Project: SEVIMA UKT Payment Webhook Gateway (Project M2)

## Deskripsi singkat
Aplikasi web fullstack multi-tenant untuk pengelolaan tagihan Uang Kuliah Tunggal (UKT) antar-kampus dengan integrasi penerimaan notifikasi pembayaran dari payment gateway melalui webhook aman berbasis HMAC-SHA256, verifikasi status kelayakan KRS mahasiswa, dan audit trail lengkap.

## Stack dan cara menjalankan
- **Backend:** Laravel 13 (PHP 8.3+)
- **Frontend:** Inertia.js (Vue 3, Composition API `<script setup>`) dengan Atomic Design dan tema Retro-Pop
- **Database:** PostgreSQL 16 (Wajib) dengan tipe data native `UUID` (UUIDv7)
- **Container:** Docker & Docker Compose (Nginx + PHP-FPM + PostgreSQL)
- **Testing:** Pest PHP (33 unit & feature test cases)

### Cara Menjalankan dengan Docker (1 Perintah)
```bash
docker compose up --build
```
Aplikasi langsung dapat diakses di: `http://localhost:8000` (database migrasi dan seeder otomatis berjalan).

### Cara Menjalankan Lokal (Tanpa Docker)
```bash
composer install
npm install
npm run build
php artisan migrate:fresh --seed
php artisan serve
```

## Arsitektur singkat
Aplikasi mengadopsi **Clean Architecture & Action/Service Pattern**:
- `app/Models/Concerns/BelongsToInstitution.php`: Global scope multi-tenant otomatis (`WHERE institusi_id = ?`).
- `app/Models/Concerns/HasUuidV7.php`: Generator primary key time-ordered UUIDv7 untuk optimasi B-tree index PostgreSQL.
- `app/Actions/Webhook/VerifyHmacSignatureAction.php`: Verifikasi raw-body payload dengan `hash_hmac('sha256', ...)` dan `hash_equals()` anti timing-attack.
- `app/Actions/Webhook/ProcessPaymentWebhookAction.php`: State machine tagihan dengan database transaction dan pessimistic locking (`lockForUpdate()`) untuk idempotency & anti race-condition.
- `app/Actions/Webhook/RecordWebhookAuditAction.php`: Audit logger independen yang mencatat semua payload & header masuk.
- `app/Services/PaymentGateway/MockPaymentGatewayService.php`: Mock Payment Gateway engine untuk kalkulasi signature dan simulasi webhook.

## Aturan bisnis penting (termasuk multi-institusi)
1. **Isolasi Data Antar-Institusi (Multi-Tenancy):**
   - Single database melayani banyak kampus.
   - Semua entitas domain (`tagihans`, `users`, `riwayat_transaksis`) terikat pada `institusi_id`.
   - User Admin/Mahasiswa Kampus A **mustahil** dapat membaca, mengubah, atau menerbitkan tagihan untuk Kampus B (IDOR strictly prohibited).
2. **Keunikan Nomor Tagihan:**
   - Nomor tagihan unik **di dalam institusi yang sama**, namun dapat bernilai sama antar-institusi berbeda (Composite unique index `[institusi_id, nomor_tagihan]`).
3. **Secret HMAC Multi-Tenant:**
   - Setiap institusi memiliki `webhook_secret` unik masing-masing.
   - Signature dihitung dari secret kampus terkait dan wajib dicocokkan dengan header `X-Signature`.
4. **Idempotency & State Consistency:**
   - Notifikasi sukses duplikat tidak boleh memicu double-credit atau corrupt ledger.
   - Tagihan yang sudah berstatus `lunas` tidak boleh diubah menjadi `kedaluwarsa` oleh webhook `payment.expired` yang datang terlambat (*out-of-order event*).
5. **KRS Gating:**
   - Status kelulusan pembayaran UKT semester aktif menjadi syarat mutlak terbukanya hak mahasiswa mengisi KRS (Kartu Rencana Studi).

## Konvensi kode
- Ikuti standar PSR-12 dan Laravel Clean Code conventions.
- Gunakan explicit type-hinting dan return types pada seluruh method PHP 8.
- Pisahkan validasi ke dalam `FormRequest` class (misal `StoreTagihanRequest`).
- Hindari fat controller; delegasikan proses bisnis ke Action / Service class.
- Komponen Vue 3 wajib menggunakan `<script setup>` dengan pendekatan Atomic Design (Atoms, Molecules, Organisms).

## Hal yang tidak boleh dilakukan AI
1. DILARANG menghapus atau membypass isolasi multi-tenant `BelongsToInstitution` pada endpoint publik/authenticated.
2. DILARANG menggunakan Livewire (sesuai larangan eksplisit pada spesifikasi soal SEVIMA).
3. DILARANG menyimpan atau men-commit secret HMAC, credential database, atau API key ke repositori git (seluruh secret wajib dikonfigurasi lewat `.env` atau seeder).
4. DILARANG mengabaikan verifikasi signature HMAC pada endpoint webhook `/api/v1/webhook/payment`.
5. DILARANG mengubah tagihan yang sudah `lunas` menjadi `kedaluwarsa` saat menerima event `payment.expired`.
