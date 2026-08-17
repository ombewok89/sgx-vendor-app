# 🚀 PANDUAN LENGKAP DEPLOYMENT & AUTO-UPDATE cPANEL (SUBDOMAIN: `vendor.sinargrafika.my.id`)

Dokumen ini berisi panduan lengkap deployment aplikasi **SGX Vendor Work Evidence** pada subdomain **`vendor.sinargrafika.my.id`**, baik menggunakan metode otomatis (Git / Railway / cPanel Git) maupun metode cepat (Upload Manual File ZIP).

---

## 📌 DAFTAR ISI
1. [Target Domain & Arsitektur Direktori](#1-target-domain--arsitektur-direktori)
2. [Tahap 1: Membuat Subdomain di cPanel](#tahap-1-membuat-subdomain-di-cpanel)
3. [Tahap 2: Push Kode dari Laptop ke GitHub](#tahap-2-push-kode-dari-laptop-ke-github)
4. [Tahap 3: Membuat Personal Access Token (PAT) GitHub](#tahap-3-membuat-personal-access-token-pat-github)
5. [Tahap 4: Clone Repository di cPanel Git™ Version Control](#tahap-4-clone-repository-di-cpanel-git-version-control)
6. [Tahap 5: Setup Backend Node.js di cPanel / Railway.app](#tahap-5-setup-backend-nodejs-di-cpanel--railwayapp)
7. [Tahap 6: Pasang Frontend di Folder Subdomain](#tahap-6-pasang-frontend-di-folder-subdomain)
8. [Tahap 7: Alur Update Otomatis di Masa Depan (Git Push)](#tahap-7-alur-update-otomatis-di-masa-depan-git-push)
9. [Catatan Keamanan Database & Uploads](#9-catatan-keamanan-database--uploads)
10. [📦 METODE CEPAT & LANGSUNG: UPLOAD MANUAL FILE ZIP VIA cPANEL FILE MANAGER](#-metode-cepat--langsung-upload-manual-file-zip-via-cpanel-file-manager)

---

## 1. Target Domain & Arsitektur Direktori

- **Target URL Aplikasi**: `https://vendor.sinargrafika.my.id`
- **Target API Backend**: `https://vendor.sinargrafika.my.id/api` (atau URL Railway)

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
5. ⚠️ **JANGAN CENTANG** kotak *"Share document root with sinargrafika.my.id"*.
6. Pastikan **Document Root** diarahkan ke: `vendor.sinargrafika.my.id`.
7. Klik **Submit**.
8. *(Opsional tapi disarankan)*: Buka menu **SSL/TLS Status** di cPanel, lalu klik **"Run AutoSSL"** agar subdomain `vendor.sinargrafika.my.id` memiliki sertifikat HTTPS (gembok hijau) gratis.

---

## Tahap 2: Push Kode dari Laptop ke GitHub

### 2.1 Buat Repository Baru di GitHub
1. Buka [https://github.com](https://github.com) dan login.
2. Klik tombol **`+`** (kanan atas) → pilih **New repository**.
3. **Repository name**: `sgx-vendor-app`.
4. **Visibility**: Pilih **`Private`** (atau Public jika ingin langsung clone tanpa SSH).
5. Klik tombol hijau **"Create repository"**.
6. Salin link HTTPS repo Anda: `https://github.com/rzaoesman-create/sgx-vendor-app.git`.

### 2.2 Jalankan Perintah Push di Terminal Laptop
Buka terminal (PowerShell) di folder proyek `d:\ANTIGRAFYTI\SGX_VENDOR`, lalu ketik:

```bash
git init
git add .
git commit -m "Deploy SGX Vendor ke vendor.sinargrafika.my.id"
git branch -M main
git remote add origin https://github.com/rzaoesman-create/sgx-vendor-app.git
git push -u origin main
```

---

## Tahap 3: Membuat Personal Access Token (PAT) GitHub

Jika repository Anda disetel Private:

1. Di GitHub, klik foto profil Anda (kanan atas) → klik **Settings**.
2. Di menu sebelah kiri paling bawah, klik **Developer settings**.
3. Pilih **Personal access tokens** → pilih **Tokens (classic)**.
4. Klik **Generate new token** → pilih **Generate new token (classic)**.
5. Isi:
   - **Note**: `cPanel vendor.sinargrafika.my.id`
   - **Expiration**: `No expiration` (atau 90 days)
   - **Select scopes**: Centang kotak **`repo`** (Full control of private repositories).
6. Klik tombol hijau **Generate token**.
7. Salin kode token yang muncul.

---

## Tahap 4: Clone Repository di cPanel Git™ Version Control

1. Di cPanel, buka menu **Git™ Version Control**.
2. Klik tombol biru **"Create"** (kanan atas).
3. Isi data form:
   - **Clone URL**: `https://github.com/rzaoesman-create/sgx-vendor-app.git` (jika repo Public) atau via SSH `git@github.com:rzaoesman-create/sgx-vendor-app.git`.
   - **Repository Path**: `sgx_vendor_app`
   - **Repository Name**: `sgx_vendor_app`
4. Klik tombol **"Create"**.

---

## Tahap 5: Setup Backend Node.js di cPanel / Railway.app

### Opsi A: Jika cPanel Mendukung Node.js ("Setup Node.js App")
1. Buka menu **"Setup Node.js App"** di cPanel.
2. Klik **"Create Application"**.
3. Isi:
   - **Node.js version**: `18.x` atau `20.x`
   - **Application mode**: `Production`
   - **Application root**: `sgx_vendor_app`
   - **Application startup file**: `server/src/index.js`
4. Tambahkan Environment Variables:
   - `NODE_ENV` = `production`
   - `PORT` = `5000`
   - `JWT_SECRET` = `sgx_sinargrafika_sec_98234789123891238912389`
   - `CORS_ORIGIN` = `https://vendor.sinargrafika.my.id`
5. Klik **"Create"** → klik **"Run NPM Install"** → klik **"Start"**.

### Opsi B: Jika cPanel Tidak Ada Node.js (Gunakan Railway.app Gratis)
1. Buka **[Railway.app](https://railway.app)** → Login with GitHub.
2. Klik **"+ New Project"** → **"Deploy from GitHub repo"** → pilih `rzaoesman-create/sgx-vendor-app`.
3. Di tab *Settings*: set Root Directory ke `/server` dan Start Command ke `node src/index.js`.
4. Di tab *Variables*: tambahkan `NODE_ENV`, `JWT_SECRET`, `CORS_ORIGIN`, dan `PORT`.
5. Di tab *Networking*: klik **"Generate Domain"** (salin URL backend yang didapat).

---

## Tahap 6: Pasang Frontend di Folder Subdomain

1. Di laptop Anda, jalankan build frontend:
   ```bash
   cd d:\ANTIGRAFYTI\SGX_VENDOR\client
   npm run build
   ```
2. Buka folder `client/dist/` di laptop Anda.
3. Kompres seluruh isi folder `dist/` ke file ZIP (`dist.zip`).
4. Buka **cPanel File Manager** → buka folder **`vendor.sinargrafika.my.id`**.
5. Upload file `dist.zip` ke folder tersebut dan lakukan **Extract**.
6. Buka browser: **`https://vendor.sinargrafika.my.id`**.

---

## Tahap 7: Alur Update Otomatis di Masa Depan (Git Push)

1. **Di Laptop**:
   ```bash
   git add .
   git commit -m "Update fitur"
   git push origin main
   ```
2. **Di cPanel / Railway**:
   - Jika Railway: Ter-update otomatis 100% (*Auto-Deploy*).
   - Jika cPanel Git: Buka menu Git™ Version Control → klik *Manage* → klik *Update from Remote*.

---

## 9. Catatan Keamanan Database & Uploads

- **Database SQLite**: Terletak di `/home/username/sgx_vendor_app/server/data/sgx_vendor.sqlite` (di luar jangkauan publik), sehingga data SPK, user, dan Berita Acara aman dan tidak akan hilang saat update.
- **Foto Bukti Lapangan**: Tersimpan di folder `uploads/` dan terlindungi otentikasi token.
- **Akun Login Bawaan**:
  - Super Admin: `superuser@sgx.com` | `admin123`
  - Admin: `admin@sgx.com` | `admin123`
  - Lapangan: `andi.lapangan@sgx.com` | `admin123`
  - Klien: `reza.indomarco@sgx-partner.com` | `admin123`

---
---

## 📦 METODE CEPAT & LANGSUNG: UPLOAD MANUAL FILE ZIP VIA cPANEL FILE MANAGER

*Gunakan metode ini jika Anda ingin langsung meng-upload aplikasi ke cPanel tanpa repot konfigurasi Git atau platform lain.*

Saya sudah membuatkan paket siap upload berupa file ZIP di laptop Anda:
📁 **`d:\ANTIGRAFYTI\SGX_VENDOR\DEPLOY_VENDOR_SINARGRAFIKA.zip`**

### Langkah 1: Buat Subdomain di cPanel
1. Login ke **cPanel** hosting Anda.
2. Buka menu **Domains** (atau **Subdomains**).
3. Klik **"Create A New Domain"**.
4. Masukkan nama domain: **`vendor.sinargrafika.my.id`**
5. ⚠️ **JANGAN CENTANG** kotak *"Share document root with sinargrafika.my.id"*.
6. Klik **Submit**. cPanel akan membuat folder: `/home/username/vendor.sinargrafika.my.id`.

### Langkah 2: Upload File ZIP ke File Manager cPanel
1. Di cPanel, buka menu **File Manager**.
2. Masuk ke folder subdomain Anda: **`vendor.sinargrafika.my.id`**.
3. Klik tombol **Upload** (di bar atas).
4. Pilih file: **`DEPLOY_VENDOR_SINARGRAFIKA.zip`** dari laptop Anda.
5. Tunggu proses upload hingga selesai (100% warna hijau).

### Langkah 3: Ekstrak File ZIP
1. Kembali ke File Manager, klik kanan pada file **`DEPLOY_VENDOR_SINARGRAFIKA.zip`** → pilih **Extract**.
2. Klik **Extract Files**.
3. Buka browser: **`https://vendor.sinargrafika.my.id`**
   🎉 **Aplikasi langsung aktif dan siap digunakan!**

### 🔄 Cara Update di Masa Depan dengan Metode Ini:
Setiap kali kita melakukan perbaikan fitur di masa depan:
1. Saya akan membuatkan file build terbaru dan menginfokan persis nama file yang berubah.
2. Anda cukup meng-upload file tersebut ke File Manager cPanel untuk menimpa file lamanya.
