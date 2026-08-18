# Panduan Deployment & Pembaruan SGX VENDOR (Laravel Standalone di cPanel)

Aplikasi **SGX Vendor Work Evidence & Digital Reporting System** kini berjalan mandiri berbasis **Laravel + MySQL/SQLite Native** dan telah terhubung penuh dengan **Git Version Control di cPanel**.

---

## ⚡ CARA UPDATE TERCEPAT (INSTAN 2 DETIK — TANPA UPLOAD ZIP)

Setiap kali Antigravity selesai melakukan perbaikan atau penambahan fitur baru di komputer lokal, Anda **tidak perlu lagi mengunduh atau mengunggah file ZIP**.

Cukup buka menu **Terminal** di cPanel Anda dan jalankan perintah berikut:

```bash
cd /home/sinargra/vendor.sinargrafika.my.id && git pull
```

Jika ada migrasi skema database baru yang ditambahkan:
```bash
php artisan migrate --force
```

✨ **Selesai!** Seluruh sistem frontend, backend, dan konfigurasi akan langsung terperbarui ke versi paling mutakhir dalam hitungan detik.

---

## 🗄️ Konfigurasi Database di cPanel

### Menggunakan MySQL cPanel (Rekomendasi Produksi)
1. Buka cPanel → menu **MySQL Database Wizard**.
2. Buat database dan user database (contoh: `sinargra_sgx_vendor`).
3. Berikan hak akses **ALL PRIVILEGES**.
4. Buka file `.env` di File Manager cPanel dan sesuaikan:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sinargra_sgx_vendor
   DB_USERNAME=sinargra_sgx_user
   DB_PASSWORD=PasswordDatabaseAnda
   ```
5. Di Terminal cPanel, jalankan inisialisasi awal:
   ```bash
   php artisan migrate --seed --force
   ```

---

## 🔐 Akun Akses Awal Sistem (Default Demo)

| Role Pengguna | Email Login | Password Default |
| :--- | :--- | :--- |
| **Super Admin** | `superuser@sgx.com` | `admin123` |
| **Admin Operasional** | `admin@sgx.com` | `admin123` |
| **Teknisi Lapangan (PIC)** | `andi.lapangan@sgx.com` | `admin123` |
| **Mitra Vendor** | `vendor@sgx.com` | `admin123` |
| **Client QA (Indomaret/Alfamart)** | `client@sgx.com` | `admin123` |

---

## 📦 Alternatif: Paket ZIP Manual (Cadangan)
Jika sewaktu-waktu Anda tidak memiliki akses Terminal/Git:
- Paket ZIP mandiri offline tetap tersedia di:
  📁 `d:\ANTIGRAFYTI\SGX_VENDOR\DEPLOY_LARAVEL_VENDOR_SINARGRAFIKA.zip`
