# Dokumentasi Teknis Endpoint Webhook Payment Gateway

Dokumen ini memuat spesifikasi teknis integrasi webhook antara Payment Gateway pihak ketiga dengan platform penagihan UKT SEVIMA.

---

## 1. Spesifikasi Endpoint

- **URL:** `POST /api/v1/webhook/payment` (Alias: `POST /webhook/payment`)
- **Content-Type:** `application/json`
- **Autentikasi & Keamanan:** HMAC-SHA256 via Header `X-Signature`

---

## 2. Header Wajib

| Header | Format | Keterangan |
| :--- | :--- | :--- |
| `Content-Type` | `application/json` | Format request body |
| `Accept` | `application/json` | Ekspektasi format balasan |
| `X-Signature` | String (Hex 64 Karakter) | Signature HMAC-SHA256 dihitung dari raw JSON body menggunakan secret HMAC institusi tujuan. |

### Formula Kalkulasi Signature HMAC
```php
$signature = hash_hmac('sha256', $rawRequestBody, $institutionSecret);
```

---

## 3. Format Payload Notifikasi (Request Body)

```json
{
  "event": "payment.success",
  "institution_code": "UNIV-A",
  "bill_number": "TAG-2024-001",
  "amount": 4500000.00,
  "transaction_reference": "GW-TRX-20261029-0091",
  "payment_time": "2026-10-29T10:15:00Z"
}
```

### Penjelasan Field:
| Field | Tipe | Keterangan |
| :--- | :--- | :--- |
| `event` | String | Jenis event notifikasi: `payment.success` atau `payment.expired`. |
| `institution_code` | String | Kode unik institusi/kampus tujuan (misal: `UNIV-A`, `UNIV-B`, `UNIV-C`). |
| `bill_number` | String | Nomor tagihan UKT mahasiswa (unik di dalam institusi tersebut). |
| `amount` | Decimal / Float | Nominal yang dibayarkan oleh mahasiswa dalam mata uang IDR. |
| `transaction_reference` | String | Nomor referensi unik transaksi dari Payment Gateway untuk audit & idempotency. |
| `payment_time` | ISO-8601 String | Waktu transaksi diselesaikan di payment gateway. |

---

## 4. Jenis Event yang Didukung

### A. Event: `payment.success` (Pembayaran Berhasil)
- Memperbarui status tagihan dari `belum_bayar` menjadi **`lunas`**.
- Mencatat record baru ke tabel `riwayat_transaksis` (ledger transaksi).
- Secara otomatis membuka hak akses pengisian **Kartu Rencana Studi (KRS)** bagi mahasiswa terkait.
- **Idempotency:** Jika event ini dikirim berulang dengan referensi yang sama, sistem mengembalikan status sukses (200 OK) tanpa menggandakan pencatatan.

### B. Event: `payment.expired` (Masa Berlaku Tagihan Habis)
- Memperbarui status tagihan dari `belum_bayar` menjadi **`kedaluwarsa`**.
- Mencatat riwayat kedaluwarsa di log transaksi.
- **Guard:** Jika tagihan sudah berstatus `lunas`, event `payment.expired` yang datang terlambat akan **diabaikan** secara aman demi integritas data.

---

## 5. Format Respon Server

### A. Respon Sukses (200 OK)
```json
{
  "status": "success",
  "message": "Pembayaran berhasil diverifikasi. Status tagihan diubah menjadi Lunas.",
  "data": {
    "nomor_tagihan": "TAG-2024-001",
    "status": "lunas",
    "nominal": 4500000
  }
}
```

### B. Respon Idempotent Duplikat (200 OK)
```json
{
  "status": "ignored",
  "message": "Idempotent: Tagihan TAG-2024-001 sudah berstatus lunas.",
  "data": {
    "nomor_tagihan": "TAG-2024-001",
    "status": "lunas",
    "nominal": 4500000
  }
}
```

### C. Respon Signature Tidak Valid (401 Unauthorized)
```json
{
  "status": "error",
  "message": "Signature HMAC tidak valid."
}
```

### D. Respon Institusi Tidak Ditemukan (404 Not Found)
```json
{
  "status": "error",
  "message": "Institusi dengan kode 'UNIV-UNKNOWN' tidak ditemukan."
}
```

---

## 6. Contoh Eksekusi Manual via cURL

```bash
# 1. Siapkan JSON payload
PAYLOAD='{"event":"payment.success","institution_code":"UNIV-A","bill_number":"TAG-2024-001","amount":4500000,"transaction_reference":"REF-MANUAL-01","payment_time":"2026-10-29T10:00:00Z"}'

# 2. Hitung HMAC-SHA256 dengan secret UNIV-A (secret_univ_a_98fbc12a)
# Di Linux/macOS:
SIGNATURE=$(echo -n "$PAYLOAD" | openssl dgst -sha256 -hmac "secret_univ_a_98fbc12a" | sed 's/^.* //')

# 3. Kirim Webhook
curl -X POST "http://localhost:8000/api/v1/webhook/payment" \
     -H "Content-Type: application/json" \
     -H "X-Signature: $SIGNATURE" \
     -d "$PAYLOAD"
```

---

## 7. Menggunakan Mock Payment Gateway Simulator (UI)

Telah disediakan antarmuka visual interaktif untuk demonstrasi langsung tanpa perlu cURL manual:
1. Buka browser pada URL: `http://localhost:8000/simulator`.
2. Pilih institusi (Univ A, B, atau C).
3. Pilih tagihan target.
4. Pilih event (`payment.success` atau `payment.expired`).
5. Klik tombol **"Hitung HMAC & Kirim Webhook Notifikasi"**.
6. Inspektur respon dan tabel audit log di halaman tersebut akan menampilkan hasil verifikasi secara real-time.

