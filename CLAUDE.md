# Project: SEVIMA UKT Payment Webhook Gateway (Project M2)

> Catatan: Project ini dikerjakan bersama Antigravity AI Assistant. Dokumentasi konteks AI, aturan bisnis multi-institusi, arsitektur, dan batasan kode lengkap dapat dilihat pada **[ANTIGRAVITY.md](./ANTIGRAVITY.md)**.

## Deskripsi singkat
Aplikasi web fullstack multi-tenant untuk pengelolaan tagihan Uang Kuliah Tunggal (UKT) antar-kampus dengan integrasi penerimaan notifikasi pembayaran dari payment gateway melalui webhook aman berbasis HMAC-SHA256, verifikasi status kelayakan KRS mahasiswa, dan audit trail lengkap.

## Stack dan cara menjalankan
- Laravel 13 (PHP 8.3+) + PostgreSQL 16 + Inertia.js (Vue 3)
- Jalankan via Docker: `docker compose up --build`

Lihat rincian lengkap pada file **[ANTIGRAVITY.md](./ANTIGRAVITY.md)**.
