# 🚀 PANDUAN DEPLOYMENT & AUTO-UPDATE cPANEL (SUBDOMAIN: `vendor.sinargrafika.my.id`)

Dokumen ini adalah panduan langkah demi langkah (*step-by-step*) khusus untuk deployment aplikasi **SGX Vendor Work Evidence** pada subdomain **`vendor.sinargrafika.my.id`** menggunakan **GitHub** dan fitur **Git™ Version Control** di cPanel.

---

## 📌 DAFTAR ISI
1. [Target Domain & Arsitektur Direktori](#1-target-domain--arsitektur-direktori)
2. [Tahap 1: Membuat Subdomain di cPanel](#tahap-1-membuat-subdomain-di-cpanel)
3. [Tahap 2: Push Kode dari Laptop ke GitHub](#tahap-2-push-kode-dari-laptop-ke-github)
4. [Tahap 3: Membuat Personal Access Token (PAT) GitHub](#tahap-3-membuat-personal-access-token-pat-github)
5. [Tahap 4: Clone Repository di cPanel Git™ Version Control](#tahap-4-clone-repository-di-cpanel-git-version-control)
6. [Tahap 5: Setup Backend Node.js di cPanel ("Setup Node.js App")](#tahap-5-setup-backend-nodejs-di-cpanel-setup-nodejs-app)
7. [Tahap 6: Pasang Frontend di Folder Subdomain](#tahap-6-pasang-frontend-di-folder-subdomain)
8. [Tahap 7: Alur Update Cepat di Masa Mendatang (Hanya 1-Klik)](#tahap-7-alur-update-cepat-di-masa-mendatang-hanya-1-klik)
9. [Catatan Keamanan Database & Uploads](#9-catatan-keamanan-database--uploads)

---

## 1. Target Domain & Arsitektur Direktori

- **Target URL Aplikasi**: `https://vendor.sinargrafika.my.id`
- **Target API Backend**: `https://vendor.sinargrafika.my.id/api` (atau port Node.js)

### Struktur Folder di Server Hosting:
```text
/home/username_cpanel/
│
├── vendor.sinargrafika.my.id/       <-- [DOCUMENT ROOT SUBDOMAIN - FRONTEND]
│   ├── assets/                      (File Javascript & CSS)
│   ├── index.html                   (Single Page Application)
│   └── .htaccess                    (Routing SPA Vue + Proxy ke Backend)
│
└── sgx_vendor_app/                  <-- [BACKEND REPOSITORY NODE.JS]
    ├── .env                         (Konfigurasi rahasia)
    ├── package.json
    ├── server/
    │   ├── src/                     (Express API Server)
    │   └── data/sgx_vendor.sqlite   (Database SQLite Transaksi Asli - PERSISTEN)
    └── uploads/                     (Folder Foto Bukti Pekerjaan - PERSISTEN)
```

---

## Tahap 1: Membuat Subdomain di cPanel

1. Login ke dashboard **cPanel** hosting Anda.
2. Cari dan klik menu **Domains** (atau **Subdomains**).
3. Klik tombol **"Create A New Domain"**.
4. Masukkan nama subdomain: **`vendor.sinargrafika.my.id`**
5. Pastikan **Document Root** diarahkan ke: `vendor.sinargrafika.my.id` (atau centang pilihan agar tidak masuk ke `public_html`).
6. Klik **Submit**.
7. *(Opsional tapi disarankan)*: Buka menu **SSL/TLS Status** di cPanel, lalu klik **"Run AutoSSL"** agar subdomain `vendor.sinargrafika.my.id` memiliki sertifikat HTTPS (gembok hijau) gratis.

---

## Tahap 2: Push Kode dari Laptop ke GitHub

### 2.1 Buat Repository Baru di GitHub
1. Buka [https://github.com](https://github.com) dan login.
2. Klik tombol **`+`** (kanan atas) → pilih **New repository**.
3. **Repository name**: `sgx-vendor-app` (bebas).
4. **Visibility**: Pilih **`Private`** (wajib agar kode aman dari publik).
5. Klik tombol hijau **"Create repository"**.
6. Salin link HTTPS repo Anda, misal: `https://github.com/username-anda/sgx-vendor-app.git`.

### 2.2 Jalankan Perintah Push di Terminal Laptop
Buka terminal (PowerShell / Command Prompt) di folder proyek `d:\ANTIGRAFYTI\SGX_VENDOR`, lalu ketik:

```bash
# 1. Inisialisasi Git
git init

# 2. Masukkan semua perubahan file
git add .

# 3. Buat commit pertama
git commit -m "Deploy SGX Vendor ke vendor.sinargrafika.my.id"

# 4. Arahkan branch ke main
git branch -M main

# 5. Hubungkan ke GitHub Anda (Ganti dengan link GitHub Anda)
git remote add origin https://github.com/username-anda/sgx-vendor-app.git

# 6. Kirim kode ke GitHub
git push -u origin main
```

---

## Tahap 3: Membuat Personal Access Token (PAT) GitHub

Agar cPanel bisa mengunduh repository Private Anda secara otomatis:

1. Di GitHub, klik foto profil Anda (kanan atas) → klik **Settings**.
2. Di menu sebelah kiri paling bawah, klik **Developer settings**.
3. Pilih **Personal access tokens** → pilih **Tokens (classic)**.
4. Klik **Generate new token** → pilih **Generate new token (classic)**.
5. Isi data:
   - **Note**: `cPanel vendor.sinargrafika.my.id`
   - **Expiration**: `No expiration` (atau 90 days)
   - **Select scopes**: Centang kotak **`repo`** (Full control of private repositories).
6. Klik tombol hijau **Generate token**.
7. ⚠️ **Salin dan simpan kode token** (contoh: `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`).

---

## Tahap 4: Clone Repository di cPanel Git™ Version Control

1. Di cPanel, buka menu **Git™ Version Control**.
2. Klik tombol biru **"Create"** (kanan atas).
3. Isi data form:
   - **Clone URL**: Masukkan link GitHub yang disisipkan token Anda:
     ```text
     https://username-anda:TOKEN_ANDA@github.com/username-anda/sgx-vendor-app.git
     https://rzaoesman-create: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@github.com/rzaoesman-create/sgx-vendor-app.git
     ```
     
     ```
     *(Ganti `username-anda` dan `TOKEN_ANDA` dengan username & token GitHub Anda).*
   - **Repository Path**: `sgx_vendor_app`
   - **Repository Name**: `sgx_vendor_app`
4. Klik tombol **"Create"**. cPanel akan meng-clone kode secara otomatis dalam 5–10 detik.

---

## Tahap 5: Setup Backend Node.js di cPanel ("Setup Node.js App")

1. Di cPanel, cari dan buka menu **"Setup Node.js App"**.
2. Klik tombol **"Create Application"**.
3. Isi konfigurasi khusus untuk subdomain Anda:
   - **Node.js version**: Pilih versi **`18.x`** atau **`20.x`**.
   - **Application mode**: Pilih **`Production`**.
   - **Application root**: `sgx_vendor_app`
   - **Application URL**: Pilih `vendor.sinargrafika.my.id/api` (atau subdomain yang Anda inginkan).
   - **Application startup file**: `server/src/index.js`
4. Di bagian **Environment Variables** (klik *Add Variable*), masukkan:
   - `NODE_ENV` = `production`
   - `PORT` = `5000`
   - `JWT_SECRET` = `sgx_sinargrafika_sec_98234789123891238912389` *(Gunakan teks acak panjang)*
   - `CORS_ORIGIN` = `https://vendor.sinargrafika.my.id`
5. Klik tombol **"Create"** di kanan atas.
6. Setelah selesai, klik tombol **"Run NPM Install"** pada halaman Node.js app tersebut.
7. Klik tombol **"Start / Restart"**. Backend API server kini telah berjalan!

---

## Tahap 6: Pasang Frontend di Folder Subdomain

1. Di laptop Anda, jalankan build frontend:
   ```bash
   npm run build
   ```
2. Buka folder `client/dist/` di laptop Anda.
3. Kompres seluruh isi folder `dist/` tersebut ke file ZIP (beri nama `dist.zip`).
4. Buka **cPanel File Manager** → buka folder **`vendor.sinargrafika.my.id`**.
5. Upload file `dist.zip` ke folder `vendor.sinargrafika.my.id`.
6. Klik kanan pada `dist.zip` → pilih **Extract**.
7. Buka browser dan akses: **`https://vendor.sinargrafika.my.id`**
   🎉 **Aplikasi SGX Vendor Work Evidence sekarang sudah LIVE dan siap beroperasi!**

---

## Tahap 7: Alur Update Cepat di Masa Mendatang (Hanya 1-Klik)

Jika sewaktu-waktu Anda menambah fitur atau memperbaiki sesuatu:

1. **Di Laptop**:
   ```bash
   git add .
   git commit -m "Update perbaikan"
   git push origin main
   ```
2. **Di cPanel**:
   - Buka menu **Git™ Version Control** → klik **"Manage"** pada `sgx_vendor_app`.
   - Di tab **"Pull or Deploy"**, klik tombol **"Update from Remote"** (1-klik).
   - Buka menu **"Setup Node.js App"** → klik **"Restart"**.
3. **Jika ada update tampilan**: Jalankan `npm run build` di lokal dan extract `dist.zip` baru ke folder `vendor.sinargrafika.my.id`.

---

## 9. Catatan Keamanan Database & Uploads

- **Database SQLite**: Terletak di `/home/username/sgx_vendor_app/server/data/sgx_vendor.sqlite` (di luar jangkauan publik web), sehingga data SPK, user, dan Berita Acara aman dan tidak akan hilang saat Git Pull.
- **Foto Bukti Lapangan**: Tersimpan di `/home/username/sgx_vendor_app/uploads/` dan dilindungi oleh otentikasi login token.
- **Akun Login Bawaan**: Gunakan salah satu email yang ada di [`CATATAN.TXT`](file:///d:/ANTIGRAFYTI/SGX_VENDOR/CATATAN.TXT) dengan kata sandi awal `admin123`.
