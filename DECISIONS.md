# Architecture Decision Records (DECISIONS.md)

Dokumen ini mencatat keputusan-keputusan teknis penting, alternatif yang dipertimbangkan, dan konsekuensinya dalam pengembangan sistem SEVIMA UKT Payment Webhook Gateway.

---

## 1. Strategi Isolasi Multi-Tenant: Single Database dengan Row-Level Scoping vs Schema-per-Tenant

**Konteks :**  
Sistem harus melayani banyak perguruan tinggi dalam satu platform dengan isolasi data yang mutlak antar-kampus. Pengguna hanya boleh mengakses data milik institusinya sendiri, dan nomor tagihan bisa sama antar institusi yang berbeda.

**Opsi :**  
1. *Schema-per-tenant*: Setiap institusi memiliki schema PostgreSQL terpisah (`tenant_univa`, `tenant_univb`).
2. *Database-per-tenant*: Setiap institusi memiliki database terpisah.
3. *Single-database dengan Row-Level Scoping*: Satu database bersama, setiap baris data diberi kolom `institusi_id` dengan composite index dan Eloquent Global Scope otomatis.

**Keputusan :**  
Memilih **Opsi 3 (Single-database dengan Row-Level Scoping)** menggunakan trait `BelongsToInstitution`. Trait ini secara otomatis menambahkan klausa `WHERE institusi_id = ?` ke seluruh query Eloquent dan meng-inject `institusi_id` pada saat *insert*. Keunikan nomor tagihan dijamin oleh constraint komposit PostgreSQL: `UNIQUE(institusi_id, nomor_tagihan)`.

**Konsekuensi :**  
- *Positif:* Manajemen migrasi database jauh lebih sederhana dan cepat (1 kali migrasi untuk semua kampus), backup terpusat, konsumsi resource database hemat, dan sangat scalable untuk ratusan perguruan tinggi.
- *Trade-off:* Memerlukan kewaspadaan tinggi agar tidak ada raw query yang membypass `institusi_id`. Oleh karena itu, dibuat *automated feature test* khusus (`MultiTenantIsolationTest`) untuk membuktikan kebocoran data antar-institusi mustahil terjadi.

---

## 2. Arsitektur Primary Key: PostgreSQL Native UUID (UUIDv7) vs Auto-Increment Integer vs CUID

**Konteks :**  
Pada sistem finansial multi-tenant, ID entitas (User, Tagihan, Transaksi) sering terpapar di URL atau API. Penggunaan integer auto-increment (`1, 2, 3`) sangat rentan terhadap serangan enumerasi IDOR (*Insecure Direct Object Reference*). Di sisi lain, random UUIDv4 konvensional menyebabkan fragmentasi index B-tree pada PostgreSQL.

**Opsi :**  
1. *Auto-Increment BigInteger (1, 2, 3)*: Cepat, tapi rawan ditebak (IDOR vulnerability).
2. *CUID / CUID2*: Collision-resistant, namun disimpan sebagai `VARCHAR(24)` / `TEXT` di PostgreSQL dan membutuhkan library pihak ketiga di PHP.
3. *UUIDv4 (Random)*: Aman dari IDOR, didukung native oleh PostgreSQL, namun acak total sehingga memicu fragmentasi index B-tree saat volume data membesar.
4. *UUIDv7 (Time-Ordered RFC 9562)*: Menggabungkan timestamp milidetik (48-bit) dengan entropy acak, disimpan dalam tipe native 16-byte `UUID` PostgreSQL.

**Keputusan :**  
Memilih **Opsi 4 (PostgreSQL Native UUID dengan generator UUIDv7)** melalui trait `HasUuidV7`.

**Konsekuensi :**  
- *Positif:* Nilai ID bersifat sequential/time-ordered sehingga data baru selalu di-insert di bagian akhir index B-Tree (performa penulisan setara auto-increment). Menghemat ruang disk dan RAM karena hanya memakan 16 byte binary storage (dibandingkan VARCHAR 24+ byte pada CUID). Menutup celah enumerasi IDOR sepenuhnya.
- *Trade-off:* Ukuran primary key 16 byte sedikit lebih besar dibandingkan BigInteger (8 byte), namun trade-off ini sangat sepadan dengan keamanan multi-tenant yang didapatkan.

---

## 3. Concurrency & Idempotency Webhook: Pessimistic Row Locking (`lockForUpdate()`)

**Konteks :**  
Di dunia nyata, Payment Gateway sering mengirimkan webhook berulang secara simultan (*concurrent retry*) akibat latency jaringan, atau mahasiswa melakukan pembayaran ganda pada jeda waktu sepersekian detik. Sistem tidak boleh mengalami *race condition* atau mencatat *double-credit*.

**Opsi :**  
1. *Optimistic Locking*: Menggunakan kolom `version` pada tabel tagihan.
2. *Application-level Lock (Cache lock / Redis lock)*: Mengunci key `lock:tagihan:{id}` sebelum memproses.
3. *Pessimistic Row Locking di Database (`SELECT ... FOR UPDATE`)*: Mengunci baris record tagihan di level engine database PostgreSQL selama transaksi berjalan.

**Keputusan :**  
Memilih **Opsi 3 (Pessimistic Row Locking)** dengan `Tagihan::where(...)->lockForUpdate()->first()` yang dibungkus dalam `DB::transaction()`. Sebelum status diubah, sistem mengecek apakah status sudah `lunas` atau apakah `referensi_gateway` sudah tercatat di tabel riwayat transaksi.

**Konsekuensi :**  
- *Positif:* Jaminan konsistensi ACID 100% pada level database engine. Jika ada dua webhook untuk tagihan yang sama masuk pada milidetik yang identik, webhook kedua akan tertahan menunggu transaksi pertama selesai, lalu membaca status terkini (`lunas`) dan mengabaikan proses tanpa corrupt state (idempotent).
- *Trade-off:* Transaksi yang terkunci menahan baris data selama beberapa milidetik. Hal ini dioptimalkan dengan menjaga agar blok transaksi hanya memproses query database esensial tanpa memanggil external network I/O di dalam lock.

---

## 4. Penanganan Pembayaran Tagihan yang Sudah Kedaluwarsa (Late Settlement Policy)

**Konteks :**  
Di perguruan tinggi, mahasiswa terkadang sudah membuka transfer pembayaran di ATM/m-banking pada menit-menit akhir masa berlaku, namun notifikasi sukses dari gateway baru tiba setelah tanggal kedaluwarsa sistem terlewati. Bagaimana sistem bersikap terhadap event `payment.success` pada tagihan yang berstatus `kedaluwarsa`?

**Opsi :**  
1. *Tolak Pembayaran (Strict Reject)*: Mengembalikan error ke gateway dan membiarkan uang mahasiswa tertahan/menggantung.
2. *Terima dan Luluskan (Forgiving Settlement)*: Tetap memperbarui status tagihan menjadi `lunas`, mencatat riwayat transaksi, dan memberikan catatan audit bahwa pembayaran diselesaikan setelah tanggal kedaluwarsa.
3. *Status Khusus (Dispute/Review)*: Mengubah status menjadi `membutuhkan_verifikasi_manual`.

**Keputusan :**  
Memilih **Opsi 2 (Forgiving Settlement)**. Uang mahasiswa sudah terpotong di payment gateway, sehingga menolak notifikasi sukses secara sepihak akan merugikan mahasiswa dan menimbulkan friksi birokrasi di kampus. Sistem mengubah status tagihan menjadi `lunas`, mencatat nominal bayar di ledger, dan menuliskan catatan audit: *"Pembayaran berhasil diterima untuk tagihan yang sebelumnya kedaluwarsa. Status diubah menjadi Lunas."*

**Konsekuensi :**  
- *Positif:* Mahasiswa tidak dirugikan, hak akses KRS tetap dapat terbuka secara otomatis tanpa harus antre di loket keuangan kampus, dan gateway menerima respon 200 OK yang bersih.
- *Trade-off:* Admin keuangan perlu memiliki visibilitas atas tagihan-tagihan yang dibayar pasca-jatuh tempo, yang telah difasilitasi melalui catatan audit di log transaksi.

---

## 5. Audit Trail Independen untuk Notifikasi Webhook Gagal & Tidak Sah

**Konteks :**  
Soal mewajibkan: *"Seluruh notifikasi yang diterima dicatat untuk keperluan audit."* Ini mencakup notifikasi dengan signature palsu (serangan tampering), notifikasi untuk institusi yang tidak terdaftar, maupun payload malformed. Jika proses pencatatan audit ikut ter-rollback saat error terjadi, jejak audit akan hilang.

**Opsi :**  
1. *Menyimpan log hanya jika verifikasi HMAC berhasil*.
2. *Menyimpan log di file teks (`storage/logs/laravel.log`)*.
3. *Menyimpan log audit ke tabel dedicated PostgreSQL (`webhook_audit_logs`) secara independen dari transaksi bisnis*.

**Keputusan :**  
Memilih **Opsi 3 (Dedicated Table `webhook_audit_logs`)**. Tindakan pencatatan audit dipisahkan ke dalam `RecordWebhookAuditAction` yang dijalankan di luar database transaction tagihan. Setiap request masuk dicatat secara utuh (IP address, raw headers, payload JSON, status verifikasi signature, dan HTTP response code).

**Konsekuensi :**  
- *Positif:* Tersedianya jejak forensik lengkap untuk kebutuhan analisis insiden di **Wawancara Tahap 2**. Tim penilai dapat langsung memeriksa log serangan/kegagalan signature di tabel audit atau melalui antarmuka **Mock Gateway Simulator**.
- *Trade-off:* Ukuran tabel `webhook_audit_logs` akan terus bertambah. Di lingkungan production jangka panjang, tabel ini disarankan memiliki kebijakan partitioning bulanan atau auto-archiving.

