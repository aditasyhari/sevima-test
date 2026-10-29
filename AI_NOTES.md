# Refleksi Bekerja Bersama AI (AI Pairing Notes)

## Bagian yang dikerjakan bersama AI
- **Arsitektur Multi-Tenancy & Trait Isolation:** Bersama AI merancang trait `BelongsToInstitution` yang secara otomatis meng-inject global scope `WHERE institusi_id = ?` ke seluruh query Eloquent dan meng-assign `institusi_id` pada event `creating`.
- **Implementasi Verifikasi HMAC-SHA256:** Berkolaborasi menyusun `VerifyHmacSignatureAction` yang memastikan pembacaan body dilakukan secara *raw* (`$request->getContent()`) dan perbandingan string dilakukan dengan `hash_equals()` untuk mencegah *timing attack*.
- **Penyusunan State Machine & Idempotency Webhook:** Menyusun logika `ProcessPaymentWebhookAction` dengan transaksi database dan pessimistic locking (`lockForUpdate()`) agar terlindung dari *duplicate webhook* dan *out-of-order delivery*.
- **Pembuatan Komponen Atomic Design (Vue 3):** Memecah UI ke dalam Atoms (`RetroButton`, `RetroBadge`, `RetroInput`), Molecules (`StatusFilterPills`, `StatCard`, `KrsStatusBadgeCard`), dan Organisms (`TagihanTable`, `StudentHeroBillCard`, `CreateTagihanModal`).
- **Penyusunan Test Suite Pest:** Menghasilkan 33 skenario automated tests untuk isolasi tenant, validasi HMAC, penolakan signature palsu, dan penanganan idempotency.

## Satu contoh saran AI yang saya tolak/koreksi, dan alasannya
- **Saran Awal AI:** Pada saat awal perancangan basis data, AI sempat mempertimbangkan penggunaan library pihak ketiga untuk **CUID** sebagai primary key.
- **Koreksi / Penolakan Saya:** Saya menolak opsi CUID dan menginstruksikan penggunaan **PostgreSQL Native `UUID` dengan generator `UUIDv7`**.
- **Alasannya:** PostgreSQL memiliki tipe data native `UUID` (16 byte binary storage yang sangat efisien dalam memori dan index B-Tree). Jika menggunakan CUID, kolom harus disimpan sebagai `VARCHAR(24)` / `TEXT` yang lebih boros penyimpanan dan membutuhkan library pihak ketiga di PHP. Selain itu, **UUIDv7** (RFC 9562) sudah bersifat *time-ordered* (sequential), sehingga sepenuhnya memecahkan masalah fragmentasi index B-Tree tanpa mengorbankan keamanan anti-IDOR.

## Bagian yang saya kerjakan tanpa AI, dan alasannya
- **Analisis Kritis Domain Kampus & Kebijakan KRS:** Mengidentifikasi secara independen bahwa inti dari sistem UKT di perguruan tinggi adalah sebagai *gating access* pengisian KRS (Kartu Rencana Studi). Keputusan visualisasi status KRS di level antarmuka mahasiswa dan API murni diputuskan dari pemahaman mendalam terhadap alur bisnis universitas.
- **Pemisahan Elemen Tidak Relevan dari Referensi Desain:** Saat menganalisis gambar referensi dashboard, secara sadar membuang elemen marketplace kursus e-learning (harga dollar, tombol upsell "Upgrade to PRO", modul belajar) agar sistem berfokus murni pada administrasi keuangan akademik.
- **Konfigurasi Lingkungan Keamanan Docker (Mitigasi RCE):** Menentukan hardening konfigurasi PHP-FPM di Docker (`disable_functions = exec,passthru,shell_exec,system`, `expose_php = Off`, user non-root) untuk memastikan runtime kebal dari ancaman RCE (seperti PHP2Shell CVE-2026-109618/109619).

## Kalau mengulang dari awal, apa yang akan saya ubah
- **Asynchronous Webhook Queue:** Pada implementasi saat ini, notifikasi webhook diproses secara sinkron langsung di dalam request handler HTTP untuk kemudahan demonstrasi lokal. Jika diulang dalam skala beban jutaan transaksi kampus, saya akan langsung memasukkan webhook ke antrian Redis/RabbitMQ (`DispatchWebhookJob`) dengan *dead-letter queue* (DLQ) dan *exponential backoff retry*.
- **Audit Diffing Snapshot:** Menambahkan kolom `diff_changes` di tabel `webhook_audit_logs` untuk mencatat perbandingan nilai kolom *before* dan *after* status tagihan diubah, sehingga tim audit keuangan dapat melihat rekaman riwayat perubahan secara granular.
