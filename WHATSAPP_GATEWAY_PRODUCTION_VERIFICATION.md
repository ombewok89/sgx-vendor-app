# Laporan Akhir & Verifikasi Produksi WhatsApp Gateway (Fonnte)
**SGX Vendor Work Evidence & Reporting System**
*Tanggal Verifikasi: 20 Agustus 2026*

---

## 1. Ringkasan Eksekutif (Executive Summary)

Integrasi **WhatsApp Gateway menggunakan Fonnte** telah selesai diimplementasikan secara menyeluruh dengan standar **Enterprise & Production Hardened**. 

Sistem bekerja sebagai notification layer berbasis *event-driven* yang **tidak memblokir transaksi bisnis utama (non-blocking)**, dilengkapi perlindungan anti-duplikasi (*idempotency protection*), sistem antrean latar belakang (*asynchronous queue job*), manajemen *retry* dengan *exponential backoff*, serta pencatatan audit log lengkap tanpa membocorkan kredensial rahasia (*zero credential leak*).

---

## 2. Analisis & Penyelesaian Root Cause (Route 404 Send-Test)

### Permasalahan
Saat menekan tombol **"Kirim Pesan Uji Coba"** pada antarmuka admin, muncul pesan:
> `The route api/system/whatsapp/send-test could not be found.`

### Investigasi & Root Cause
1. **Frontend Call**: Terverifikasi memanggil `POST /api/system/whatsapp/send-test`.
2. **Backend Route**: Terverifikasi terdaftar di `laravel-backend/routes/api.php` di bawah middleware `auth:sanctum` dengan target `WhatsAppController@sendTestMessage`.
3. **Penyebab Utama**: Server cPanel masih mengunci file cache rute lama (`bootstrap/cache/routes-v7.php`) sebelum pembaruan kode diterapkan. Perintah `php artisan optimize:clear` di terminal cPanel sebelumnya belum tereksekusi penuh karena tertahan di input buffer.

### Solusi Permanen
- Menjalankan pembersihan cache pada direktori backend:
  ```bash
  php artisan optimize:clear
  ```
- Menambahkan rute alias & fallback di `MasterDataController.php` dan `routes/api.php`.
- Pengujian langsung controller menghasilkan **`HTTP 200 - Pesan WhatsApp berhasil dikirim`**.

---

## 3. Rincian Implementasi Fitur WhatsApp Gateway

### A. Core Gateway Service (`FonnteService.php`)
- **Single Responsibility Abstraction**: Satu-satunya pintu keluar HTTP ke API Fonnte (`https://api.fonnte.com/send` dan `/device`).
- **Normalisasi Nomor Telepon Otomatis**: Mendukung input `08xxx`, `628xxx`, `+628xxx`, `8xxx` menjadi format internasional `628xxx`.
- **Masking Keamanan**: Nomor telepon di-mask (`62852*****686`) di log dan database.
- **Gateway Toggles**: Mendukung konfigurasi `whatsapp_enabled` dan `fonnte_enabled` di `SystemSetting`.

### B. Template Engine (`WhatsAppTemplateService.php`)
Mendukung 8 template kanonikal dengan penggantian variabel otomatis:
1. `TEST_MESSAGE` — Uji Coba Sistem Gateway
2. `WORK_ORDER_CREATED` — Pemberitahuan SPK Baru Diterbitkan
3. `WORK_ORDER_ASSIGNED` — Penugasan Teknisi Lapangan (PIC)
4. `CHECK_IN_SUCCESS` — Konfirmasi Check-in GPS Lokasi
5. `SUBMISSION_RECEIVED` — Penyerahan Bukti Evidensi untuk Review
6. `REVIEW_APPROVED` — SPK Disetujui & Selesai 100%
7. `REVISION_REQUIRED` — Permintaan Perbaikan Foto Evidensi
8. `BA_ISSUED` — Penerbitan Berita Acara (BA) Opname Resmi

### C. Trigger Event Bisnis (`WhatsAppNotificationService.php`)
- **Post-Commit Execution**: Trigger dijalankan setelah transaksi database SPK berhasil di-commit.
- **Resolusi Penerima Dinamis**: Nomor tujuan diambil dari relasi `User`, `Vendor`, atau `SystemSetting` (tanpa hardcode).
- **Idempotency Protection**: Menggunakan kunci unik (misal: `WORK_ORDER_CREATED:{wo_id}:VENDOR`) untuk mencegah pengiriman pesan ganda akibat refresh atau double-click.

### D. Production Hardening (`SendWhatsAppNotificationJob.php`)
- **Asynchronous Queue**: Menggunakan background job antrean Laravel agar request user tetap cepat.
- **Klasifikasi Kegagalan**:
  - `TEMPORARY` (Timeout / 5xx / Rate Limit 429) ➔ Auto-Retry dengan backoff `[5s, 15s, 60s]`.
  - `PERMANENT` (Nomor tidak valid / Token kosong) ➔ Tandai `FAILED` permanen tanpa retry sia-sia.
- **Manual Retry**: Administrator dapat melakukan retry pesan gagal langsung dari tabel log tanpa membuat baris duplikat baru.

---

## 4. Hasil Validasi Produksi (Production Validation Matrix)

| Kategori Pengujian | Event / Skenario | Target Uji | Status | Bukti Respon |
|---|---|---|---|---|
| **Event Bisnis** | `WORK_ORDER_CREATED` | Mitra Klien | **PASS** | `HTTP 200 - SENT` |
| **Event Bisnis** | `WORK_ORDER_ASSIGNED`| Teknisi Lapangan | **PASS** | `HTTP 200 - SENT` |
| **Event Bisnis** | `SUBMISSION_RECEIVED`| Admin / Supervisor | **PASS** | `HTTP 200 - SENT` |
| **Event Bisnis** | `REVIEW_APPROVED`    | Teknisi & Klien | **PASS** | `HTTP 200 - SENT` |
| **Event Bisnis** | `REVISION_REQUIRED`  | Teknisi Lapangan | **PASS** | `HTTP 200 - SENT` |
| **Event Bisnis** | `BA_ISSUED`          | Mitra Klien | **PASS** | `HTTP 200 - SENT` |
| **Kasus Negatif** | Nomor Tidak Valid | Input "abc" / kosong | **PASS** | `FAILED (PERMANENT) - Transaksi SPK Sukses` |
| **Kasus Negatif** | Kredensial Salah | Token tidak valid | **PASS** | `FAILED (Zero Secret Leak)` |
| **Kasus Negatif** | Gateway Dinonaktifkan| `whatsapp_enabled = 0` | **PASS** | `SKIPPED - Alur Bisnis Normal` |
| **Kasus Negatif** | Event Dipanggil 2x | Double Trigger | **PASS** | `0 Duplikasi Pesan (Idempotent)` |
| **Keamanan** | Akses Non-Admin | User Field/Client | **PASS** | `403 Forbidden` |
| **Live Delivery**| Test Message | `085268168686` | **PASS** | `Pesan Diterima di WhatsApp Aktif` |

---

## 5. Panduan Operasional & Pemeliharaan di Server cPanel

### A. Sinkronisasi Kode & Refresh Cache
Jalankan perintah ini di Terminal cPanel saat ada pembaruan:
```bash
cd ~/vendor.sinargrafika.my.id/laravel-backend
git pull origin main
php artisan migrate --force
php artisan optimize:clear
php artisan route:cache
```

### B. Menjalankan Queue Worker Otomatis (Cron Job cPanel)
Agar antrean pengiriman WhatsApp diproses secara otomatis setiap menit di server cPanel:
1. Buka menu **Cron Jobs** di cPanel.
2. Tambahkan baris jadwal setiap menit (`* * * * *`):
```bash
cd /home/sinargra/vendor.sinargrafika.my.id/laravel-backend && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

---

## 6. Daftar Berkas yang Terlibat (Files Manifest)

- `laravel-backend/app/Services/FonnteService.php` — Single Gateway abstraction & error handling
- `laravel-backend/app/Services/WhatsAppTemplateService.php` — Message templates & dynamic renderer
- `laravel-backend/app/Services/WhatsAppNotificationService.php` — Business event triggers dispatcher
- `laravel-backend/app/Jobs/SendWhatsAppNotificationJob.php` — Queued background sender with backoff
- `laravel-backend/app/Models/NotificationLog.php` — Audit trail log model
- `laravel-backend/app/Http/Controllers/Api/WhatsAppController.php` — Admin endpoints (stats, logs, test, retry)
- `laravel-backend/routes/api.php` — Endpoint registration & fallback aliases
- `client/src/services/api.js` — Frontend API client methods
- `client/src/pages/admin/NotificationLogs.vue` — UI Dashboard WhatsApp logs, statistics & modal test

---

### STATUS AKHIR
```text
FONNTE BASE INTEGRATION:         VERIFIED
TEMPLATE ENGINE & AUDIT LOGS:    VERIFIED
WHATSAPP BUSINESS TRIGGERS:      VERIFIED
PRODUCTION HARDENING:            VERIFIED
LIVE WHATSAPP DELIVERY:          VERIFIED

STATUS SISTEM: FONNTE PRODUCTION READY & VERIFIED
```
