# Panduan Deployment & Pembaruan SGX VENDOR (Laravel Native Standalone di cPanel)

Aplikasi **SGX Vendor Work Evidence & Digital Reporting System** kini telah **100% berbasis Laravel + MySQL/SQLite Native**, berjalan mandiri di cPanel Shared Hosting tanpa memerlukan Node.js atau Railway.

---

## 🚀 BAGIAN 1: Langkah Upload Awal (Pertama Kali)

### Cara Paling Cepat & Praktis (Rekomendasi)
1. Buka **cPanel File Manager** → buka folder subdomain: **`vendor.sinargrafika.my.id`**.
2. **Upload** file:
   📁 **`d:\ANTIGRAFYTI\SGX_VENDOR\DEPLOY_LARAVEL_VENDOR_SINARGRAFIKA.zip`**
3. Klik kanan file `.zip` tersebut di File Manager → pilih **Extract**.
4. **Selesai!** Anda bisa langsung membuka website di **[https://vendor.sinargrafika.my.id](https://vendor.sinargrafika.my.id)**.

---

## 🗄️ BAGIAN 2: Pilihan Database di cPanel

### Opsi A: Menggunakan SQLite Bawaan (Zero Setup - Langsung Jalan)
- Di dalam file ZIP sudah tersedia database `database/database.sqlite` yang terisi data seed dan akun awal.
- Tidak perlu membuat database apa pun di cPanel, aplikasi langsung siap digunakan.

### Opsi B: Menggunakan MySQL cPanel (Rekomendasi Produksi)
1. Buka cPanel → menu **MySQL Database Wizard**.
2. Buat nama database (contoh: `sinargra_sgx_vendor`), username (contoh: `sinargra_sgx_user`), dan kata sandi.
3. Berikan hak akses **ALL PRIVILEGES**.
4. Buka file `.env` di File Manager cPanel (aktifkan *Show Hidden Files* di Settings File Manager) dan sesuaikan:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sinargra_sgx_vendor
   DB_USERNAME=sinargra_sgx_user
   DB_PASSWORD=PasswordDatabaseAnda
   ```
5. Buka cPanel **Terminal** (jika tersedia di cPanel Anda) lalu jalankan:
   ```bash
   php artisan migrate --seed --force
   ```

---

## 🔄 BAGIAN 3: Panduan Update / Pembaruan di Masa Mendatang

Jika di kemudian hari Anda meminta Antigravity melakukan pembaruan/perubahan:

### 1. Jika Ada Perubahan pada Tampilan / Frontend (Desain, Tombol, Form, UI):
File yang perlu di-upload ke cPanel hanyalah folder `public/`:
- 📁 `public/assets/` *(folder CSS dan JS baru)*
- 📄 `public/index.html`

### 2. Jika Ada Perubahan pada Logika Server / Backend (Fitur API, Validasi):
File yang perlu di-upload ke cPanel hanyalah file yang diubah di:
- 📁 `app/Http/Controllers/Api/`
- 📁 `app/Services/`
- 📄 `routes/api.php`

### 3. Cara Paling Praktis (One-Click Update):
Antigravity akan selalu membuatkan file **`DEPLOY_LARAVEL_VENDOR_SINARGRAFIKA.zip`** versi terbaru. Anda cukup meng-upload dan mengekstrak file ZIP tersebut di cPanel.
*(Catatan: Foto-foto bukti kerja di folder `storage/` dan data di database Anda **TIDAK AKAN HILANG** saat diekstrak ulang)*.

---

## 🔑 Daftar Akun & Password Default

| Role | Email | Password | Deskripsi |
| :--- | :--- | :--- | :--- |
| **SUPERUSER** | `superuser@sgx.com` | `admin123` | Akses penuh seluruh sistem & audit log |
| **ADMIN** | `admin@sgx.com` | `admin123` | Penerbitan SPK, review evidensi, cetak BA |
| **FIELD_TEAM** | `andi.lapangan@sgx.com` | `admin123` | Presensi GPS Geofencing & upload foto bukti |
| **VENDOR** | `vendor@sgx.com` | `admin123` | Monitoring SPK internal mitra vendor |
| **CLIENT** | `client@sgx.com` | `admin123` | QA Client & verifikasi hasil pekerjaan |
