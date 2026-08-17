# Panduan Deployment SGX VENDOR (Laravel Native 100% Standalone di cPanel)

Aplikasi **SGX Vendor Work Evidence & Digital Reporting System** kini telah **100% berbasis Laravel + MySQL/SQLite Native**, sehingga dapat berjalan secara mandiri di cPanel Shared Hosting tanpa memerlukan Node.js atau Railway.

---

## 📁 Struktur File Deployment di cPanel

Ada **2 Cara Praktis** untuk meletakkan file di cPanel File Manager:

### OPSI 1: Cara Paling Cepat & Praktis (Folder Subdomain Langsung)
Letakkan seluruh isi folder project ke dalam folder subdomain `/home/username/vendor.sinargrafika.my.id/`.

Di dalam folder `vendor.sinargrafika.my.id/` buat file `.htaccess` utama di root folder untuk mengarahkan traffic ke subfolder `public/`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

---

### OPSI 2: Standar Keamanan Enterprise Laravel (Folder Terpisah)
1. Buat folder baru di luar `public_html`, misalnya `/home/username/sgx_vendor_core/`.
2. Upload seluruh isi folder backend Laravel (kecuali folder `public/`) ke `/home/username/sgx_vendor_core/`.
3. Upload seluruh isi folder `public/` ke folder subdomain `/home/username/vendor.sinargrafika.my.id/`.
4. Buka file `vendor.sinargrafika.my.id/index.php` dan sesuaikan 2 baris path:
   ```php
   require __DIR__.'/../sgx_vendor_core/vendor/autoload.php';
   $app = require_once __DIR__.'/../sgx_vendor_core/bootstrap/app.php';
   ```

---

## 🗄️ Pengaturan Database di cPanel

### A. Menggunakan MySQL (Disarankan untuk Produksi)
1. Buka cPanel → **MySQL Database Wizard**.
2. Buat database (contoh: `sinargra_sgx_vendor`), user (contoh: `sinargra_sgx_user`), dan password.
3. Berikan hak akses **ALL PRIVILEGES**.
4. Buka file `.env` di File Manager dan sesuaikan:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sinargra_sgx_vendor
   DB_USERNAME=sinargra_sgx_user
   DB_PASSWORD=PasswordDatabaseAnda
   ```
5. Buka cPanel **Terminal** (jika ada) dan jalankan:
   ```bash
   php artisan migrate --seed --force
   ```
   *(Atau jika tidak ada Terminal, import file `database_schema_and_seed.sql` langsung melalui **phpMyAdmin**)*.

### B. Menggunakan SQLite (Zero Setup - Langsung Jalan Tanpa MySQL Wizard)
Jika Anda tidak ingin membuat database MySQL manual di cPanel, aplikasi sudah dilengkapi SQLite bawaan:
```env
DB_CONNECTION=sqlite
DB_DATABASE=/home/username/vendor.sinargrafika.my.id/database/database.sqlite
```

---

## 🔑 Akun Bawaan (Default Credentials)

| Role | Email | Password | Deskripsi |
| :--- | :--- | :--- | :--- |
| **SUPERUSER** | `superuser@sgx.com` | `admin123` | Akses penuh sistem & audit log |
| **ADMIN** | `admin@sgx.com` | `admin123` | Penerbitan SPK, review evidensi, BA Opname |
| **FIELD_TEAM** | `andi.lapangan@sgx.com` | `admin123` | Presensi GPS Geofencing & upload foto |
| **VENDOR** | `vendor@sgx.com` | `admin123` | Monitoring SPK internal mitra vendor |
| **CLIENT** | `client@sgx.com` | `admin123` | QA Client & verifikasi hasil pekerjaan |
