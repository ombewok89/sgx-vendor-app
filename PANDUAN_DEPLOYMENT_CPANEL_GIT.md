# 🚀 PANDUAN LENGKAP DEPLOYMENT & AUTO-UPDATE SHARED HOSTING (cPANEL + GITHUB)

Dokumen ini berisi panduan langkah demi langkah (*step-by-step*) mulai dari nol setelah pendaftaran akun GitHub dan cPanel, hingga aplikasi **SGX Vendor Work Evidence** online dan siap di-update kapan saja hanya dengan 1-klik.

---

## 📌 DAFTAR ISI
1. [Struktur Arsitektur di Shared Hosting](#1-struktur-arsitektur-di-shared-hosting)
2. [Tahap 1: Menghubungkan Laptop ke GitHub](#tahap-1-menghubungkan-laptop-ke-github)
3. [Tahap 2: Membuat Token Akses GitHub (Personal Access Token)](#tahap-2-membuat-token-akses-github-personal-access-token)
4. [Tahap 3: Clone Repository di cPanel Git™ Version Control](#tahap-3-clone-repository-di-cpanel-git-version-control)
5. [Tahap 4: Menjalankan Backend di cPanel "Setup Node.js App"](#tahap-4-menjalankan-backend-di-cpanel-setup-nodejs-app)
6. [Tahap 5: Menayangkan Frontend ke Website (`public_html`)](#tahap-5-menayangkan-frontend-ke-website-public_html)
7. [Tahap 6: Cara Melakukan Update Aplikasi di Masa Depan (Hanya 1-Klik)](#tahap-6-cara-melakukan-update-aplikasi-di-masa-depan-hanya-1-klik)
8. [Tips Penting Keamanan Data Transaksi & Foto](#8-tips-penting-keamanan-data-transaksi--foto)

---

## 1. Struktur Arsitektur di Shared Hosting

Pada cPanel shared hosting, sistem dibagi menjadi 2 bagian terisolasi:

```text
/home/username_cpanel/
│
├── public_html/                     <-- [FRONTEND WEBSITE]
│   ├── assets/                      (File Javascript & CSS hasil build)
│   ├── index.html                   (Halaman utama aplikasi)
│   └── .htaccess                    (Wajib: Pengatur rute SPA Vue Router)
│
└── sgx_vendor_app/                  <-- [BACKEND REPOSITORY GIT]
    ├── .env                         (Konfigurasi kunci rahasia hosting)
    ├── package.json
    ├── server/
    │   ├── src/                     (Kode server API Express.js)
    │   └── data/sgx_vendor.sqlite   (Database transaksi asli - AMAN & PERSISTEN)
    └── uploads/                     (Folder foto bukti teknisi - AMAN & PERSISTEN)
```

---

## Tahap 1: Menghubungkan Laptop ke GitHub

### 1.1 Buat Repository Baru di Website GitHub
1. Buka [https://github.com](https://github.com) dan login.
2. Di pojok kanan atas, klik tanda **`+`** → pilih **New repository**.
3. Isi kolom:
   - **Repository name**: `sgx-vendor-app` (atau nama pilihan Anda).
   - **Visibility**: Pilih **`Private`** (agar kode sumber aman dan tidak dapat dilihat publik).
   - Jangan centang "Add a README file" (karena kita sudah punya kode lokal).
4. Klik tombol hijau **"Create repository"**.
5. Salin link HTTPS repository Anda, misalnya: `https://github.com/username-anda/sgx-vendor-app.git`.

### 1.2 Push (Kirim) Seluruh Kode dari Laptop ke GitHub
Buka terminal PowerShell di folder proyek Anda (`d:\ANTIGRAFYTI\SGX_VENDOR`), lalu jalankan perintah berikut secara berurutan:

```bash
# 1. Inisialisasi Git di komputer lokal (jika belum)
git init

# 2. Tambahkan semua file
git add .

# 3. Buat commit pertama
git commit -m "Initial commit - SGX Vendor Production Ready"

# 4. Arahkan branch utama ke main
git branch -M main

# 5. Hubungkan ke GitHub Anda (Ganti dengan URL repo GitHub Anda)
git remote add origin https://github.com/username-anda/sgx-vendor-app.git

# 6. Kirim kode ke GitHub
git push -u origin main
```

---

## Tahap 2: Membuat Token Akses GitHub (Personal Access Token)

Karena repository Anda disetel **Private**, cPanel memerlukan token izin akses untuk mengunduh kode.

1. Di GitHub, klik foto profil Anda di pojok kanan atas → pilih **Settings**.
2. Scroll ke bagian paling bawah di menu kiri → klik **Developer settings**.
3. Pilih **Personal access tokens** → klik **Tokens (classic)**.
4. Klik **Generate new token** → pilih **Generate new token (classic)**.
5. Isi:
   - **Note**: `cPanel Shared Hosting`
   - **Expiration**: Pilih `No expiration` (atau 90 days).
   - **Select scopes**: Centang kotak **`repo`** (Full control of private repositories).
6. Scroll ke bawah dan klik **Generate token**.
7. ⚠️ **PENTING**: Salin kode token yang muncul (contoh: `ghp_xxxxxxxxxxxxxxxxxxxx`). Simpan di catatan Anda, karena kode ini hanya muncul sekali.

---

## Tahap 3: Clone Repository di cPanel Git™ Version Control

1. Buka dashboard **cPanel** hosting Anda.
2. Cari dan klik menu **Git™ Version Control**.
3. Di pojok kanan atas, klik tombol biru **"Create"**.
4. Isi data form sebagai berikut:
   - **Clone URL**: Masukkan URL GitHub yang disisipkan token Anda:
     ```text
     https://username-anda:TOKEN_ANDA@github.com/username-anda/sgx-vendor-app.git
     ```
     *(Ganti `username-anda` dan `TOKEN_ANDA` dengan username & token dari Tahap 2).*
   - **Repository Path**: `sgx_vendor_app` *(Folder ini akan dibuat otomatis di cPanel)*.
   - **Repository Name**: `sgx_vendor_app`.
5. Klik tombol **"Create"** di bagian bawah.
6. Tunggu beberapa detik hingga proses clone selesai dan status repository menjadi aktif.

---

## Tahap 4: Menjalankan Backend di cPanel "Setup Node.js App"

1. Di dashboard cPanel, cari menu **"Setup Node.js App"** (atau *Node.js Selector*).
2. Klik tombol **"Create Application"**.
3. Isi konfigurasi berikut:
   - **Node.js version**: Pilih versi **`18.x`** atau **`20.x`** (direkomendasikan versi LTS terbaru).
   - **Application mode**: Pilih **`Production`**.
   - **Application root**: `sgx_vendor_app` *(sesuaikan dengan nama folder di Tahap 3)*.
   - **Application URL**: Pilih subdomain atau domain API Anda (misal: `api.domainanda.com` atau `domainanda.com/api`).
   - **Application startup file**: `server/src/index.js`
4. Di bagian **Environment Variables** (klik *Add Variable*), tambahkan variabel berikut:
   - `NODE_ENV` = `production`
   - `PORT` = `5000` (atau biarkan default cPanel)
   - `JWT_SECRET` = `sgx_prod_key_98234789123891238912389` *(Gunakan string acak panjang)*
   - `CORS_ORIGIN` = `https://domainanda.com,https://www.domainanda.com`
5. Klik tombol **"Create"** di kanan atas.
6. Setelah aplikasi terbuat, klik tombol **"Run NPM Install"** pada halaman Node.js app tersebut untuk menginstal library server.
7. Klik tombol **"Start / Restart"**. Backend API server Anda kini telah aktif!

---

## Tahap 5: Menayangkan Frontend ke Website (`public_html`)

1. Di komputer lokal Anda, lakukan build produksi untuk frontend:
   ```bash
   npm run build
   ```
2. Buka folder `client/dist/` di komputer lokal Anda.
3. Di dalam folder `dist/`, Anda akan melihat:
   - Folder `assets/`
   - File `index.html`
   - File `.htaccess`
4. Pilih semua file di dalam folder `client/dist/` tersebut, lalu kompres ke format **ZIP** (beri nama `frontend.zip`).
5. Buka **cPanel File Manager** → buka folder **`public_html`**.
6. Upload file `frontend.zip` ke dalam `public_html`.
7. Klik kanan pada `frontend.zip` di cPanel File Manager → pilih **Extract**.
8. Buka domain Anda di browser: `https://domainanda.com`.
   🎉 **Selamat! Aplikasi SGX Vendor Work Evidence kini telah live online!**

---

## Tahap 6: Cara Melakukan Update Aplikasi di Masa Depan (Hanya 1-Klik)

Setelah deployment pertama selesai, alur kerja update di masa depan sangatlah mudah:

```mermaid
graph LR
    A["1. Coding di Laptop"] --> B["2. git push ke GitHub"]
    B --> C["3. Klik 'Pull' di cPanel Git"]
    C --> D["4. Klik 'Restart' di Node.js App"]
    D --> E["Selesai & Terupdate!"]
```

### Langkah Update Praktis:
1. **Di Laptop Anda**: Setelah selesai mengubah kode atau menambah fitur:
   ```bash
   git add .
   git commit -m "Update fitur baru"
   git push origin main
   ```
2. **Di cPanel Hosting**:
   - Buka menu **Git™ Version Control**.
   - Klik **"Manage"** di samping repo Anda.
   - Masuk ke tab **"Pull or Deploy"** → klik tombol **"Update from Remote"**.
   - Buka menu **"Setup Node.js App"** → klik tombol **"Restart"**.
3. **Jika Ada Perubahan Tampilan (UI)**:
   - Jalankan `npm run build` di lokal, upload isi folder `dist` baru ke `public_html`.

---

## 8. Tips Penting Keamanan Data Transaksi & Foto

1. **Database SQLite (`server/data/sgx_vendor.sqlite`)**:
   - Terletak di dalam folder backend di luar `public_html`, sehingga tidak dapat diakses langsung oleh publik.
   - Saat Anda melakukan Git Pull / Update, file database ini **tidak akan tertimpa atau ter-reset**, seluruh data transaksi SPK aman.
2. **Folder Foto Bukti (`uploads/`)**:
   - Foto-foto evidensi hasil pekerjaan teknisi tersimpan di folder ini.
   - Saat update sistem, folder `uploads/` tetap utuh.
3. **Backup Berkala**:
   - Anda cukup mengunduh berkas `sgx_vendor.sqlite` melalui File Manager cPanel seminggu sekali untuk arsip cadangan.

---

> [!TIP]
> Dokumen panduan ini telah tersimpan di root proyek Anda: [`PANDUAN_DEPLOYMENT_CPANEL_GIT.md`](file:///d:/ANTIGRAFYTI/SGX_VENDOR/PANDUAN_DEPLOYMENT_CPANEL_GIT.md). Anda dapat membukanya kapan saja saat proses deployment ke cPanel berlangsung!
