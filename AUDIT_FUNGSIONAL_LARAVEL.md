# Laporan Audit Ulang — DEPLOY_LARAVEL_VENDOR_SINARGRAFIKA

**Metode:** Review langsung source code Laravel (controllers, services, routes, migrations, seeder) + analisis **log runtime aktual** (`storage/logs/laravel.log`) + query langsung ke `database/database.sqlite` yang ikut terbundle di paket deploy ini.
**Konteks penting:** Paket ini bukan cuma source code — ada bekas hasil testing lokal developer ikut terbundle (log error asli, database SQLite hasil seed asli). Ini justru sangat membantu audit karena saya bisa lihat bug yang **benar-benar sudah terjadi**, bukan cuma potensi.

---

## Ringkasan Eksekutif

Kabar baik dulu: sebagian besar temuan kritis dari audit versi Node.js sebelumnya (auth bypass `quick-switch`, permission matrix dekoratif sebagian, IDOR foto evidence untuk FIELD_TEAM) **sudah diperbaiki** di versi Laravel ini. Arsitekturnya juga lebih rapi (Sanctum, spatie/laravel-permission, service layer terpisah).

Tapi ada **temuan baru yang levelnya sama seriusnya, bahkan lebih luas cakupannya**: banyak endpoint CRUD sensitif (master data, manajemen user, permission matrix, template BA) **sama sekali tidak punya pengecekan role** — hanya dilindungi status "sudah login", bukan "berhak melakukan ini". Ini ditemukan lewat pemetaan sistematis semua method di semua controller, bukan dugaan.

Ditambah ada 1 bug yang **terbukti sudah terjadi berulang kali** di log (18 kali dalam satu sesi testing) dan sepertinya belum diperbaiki.

---

## 🔴 KRITIS — Ditemukan dari Review Kode

### 1. Privilege escalation via endpoint manajemen user — TIDAK ADA proteksi role sama sekali
- **Lokasi:** `app/Http/Controllers/Api/AuthController.php` — method `storeUser()`, `updateUser()`, `deleteUser()`, `users()`, `roles()`
- **Route terkait:** `POST/PUT/DELETE /api/master/users/*`, `GET /api/users`, `GET /api/roles`
- **Masalah:** Saya cek satu-satu setiap method di controller ini — **tidak ada satu pun** `hasRole()`/`hasAnyRole()`/`authorize()`. Hanya dilindungi middleware `auth:sanctum` (artinya: syaratnya cuma "sudah login", role apa pun boleh).
- **Dampak:** User dengan role **FIELD_TEAM, VENDOR, atau CLIENT** bisa memanggil `PUT /api/master/users/{id_dirinya_sendiri}` dengan body `{"role": "SUPERUSER"}` — dan `updateUser()` akan menjalankan `$user->syncRoles([$request->role])` tanpa validasi apa pun. **Ini setara persis dengan bug `quick-switch` yang sudah diperbaiki di versi Node — hanya butuh satu langkah tambahan (login dengan akun rendah dulu), tapi hasil akhirnya sama: siapa pun bisa jadi Superuser.**
- **Fix:** Tambahkan `if (!$request->user()->hasAnyRole(['SUPERUSER','ADMIN'])) { return response()->json(['message'=>'Forbidden'], 403); }` di awal tiap method, atau lebih baik pakai Laravel Policy + `$this->authorize()` supaya tidak bisa kelupaan lagi di endpoint lain.

### 2. Permission Matrix — bisa diubah oleh siapa saja yang login
- **Lokasi:** `app/Http/Controllers/Api/PermissionController.php` — method `matrix()`, `updateMatrix()`
- **Route:** `GET/POST /api/permissions/matrix`
- **Masalah:** Sama seperti temuan #1 — nol pengecekan role. Endpoint yang seharusnya jadi fitur eksklusif Superuser untuk mengatur seluruh sistem izin, bisa diakses **dan diubah** oleh role mana pun yang sedang login.
- **Dampak:** Ini bahkan lebih parah dari #1 — kalau ini dieksploitasi, RBAC seluruh aplikasi bisa dikonfigurasi ulang oleh siapa saja.
- **Fix:** Wajib `hasRole('SUPERUSER')` di kedua method, tanpa terkecuali.

### 3. Master Data CRUD (Vendor, Area, Job Type, Field Team, System Settings) — tidak ada proteksi role
- **Lokasi:** `app/Http/Controllers/Api/MasterDataController.php` — **semua** method `store*`, `update*`, `delete*`, plus `updateSetting()`
- **Masalah:** Sama pola-nya — nol pengecekan role di semua method mutasi.
- **Dampak:** FIELD_TEAM atau CLIENT bisa menghapus data Vendor/Area/Job Type yang sedang dipakai vendor lain, atau mengubah System Settings (termasuk `fonnte_api_key`, `geofence_default_radius_meters`, dll — pengaturan inti sistem).
- **Fix:** Sama seperti #1, gate dengan role ADMIN/SUPERUSER.

### 4. Template BA Opname — tidak ada proteksi role
- **Lokasi:** `app/Http/Controllers/Api/BaDocumentController.php` — `storeTemplate()`, `updateTemplate()`, `setDefaultTemplate()`, `deleteTemplate()`
- **Masalah & Fix:** Pola identik dengan temuan di atas.

### 5. CLIENT bisa melihat data SPK & foto evidence milik SEMUA vendor/klien lain
- **Lokasi:** `app/Services/WorkOrderService.php::getScopedQuery()` dan `app/Http/Controllers/Api/EvidenceController.php::gallery()`
- **Masalah:** Fungsi scoping data di kedua tempat ini hanya punya cabang kondisi untuk role `VENDOR` dan `FIELD_TEAM`. **Tidak ada cabang untuk role `CLIENT`.** Akibatnya, user CLIENT yang memanggil `GET /api/work-orders` atau `GET /api/evidence/photos` mendapat query **tanpa filter sama sekali** — sama seperti yang dilihat Superuser: seluruh data SPK dan foto bukti dari **semua vendor dan semua klien lain**, bukan cuma miliknya sendiri.
- **Ini kebocoran data lintas-tenant yang cukup serius** — mengingat tujuan awal Client Portal justru supaya tiap klien hanya melihat laporan pekerjaan miliknya sendiri (lihat pembahasan fitur client portal sebelumnya).
- **Catatan:** `BaDocumentController::index()` juga punya gap yang sama (tidak ada scoping untuk CLIENT).
- **Fix:** Tambahkan kolom penanda "milik klien mana" yang jelas di `work_orders` (kemungkinan perlu kolom `client_id` baru kalau belum ada — perlu dicek skema), lalu tambahkan cabang `elseif ($user->hasRole('CLIENT')) { $query->where('client_id', $user->client_id); }` di ketiga tempat ini (WorkOrderService, EvidenceController, BaDocumentController).

---

## 🟠 TINGGI — Ditemukan dari Log Runtime Aktual (sudah benar-benar terjadi)

### 6. Request tanpa token yang valid menghasilkan error 500, bukan 401 — terjadi 18x dalam satu sesi testing, dan masih terjadi di baris terakhir log
- **Bukti:** `storage/logs/laravel.log` mencatat `Route [login] not defined` sebanyak **18 kali**, tersebar dari pukul 08:05 sampai **08:23:00 — yaitu baris paling akhir di log**, artinya bug ini masih terjadi sampai sesi testing berakhir, belum diperbaiki.
- **Akar masalah:** `bootstrap/app.php` memang sudah mengatur `shouldRenderJsonWhen()` supaya response error di-render sebagai JSON untuk request `api/*` — **tapi ini cuma mengatur format response-nya**. Masalah sebenarnya terjadi lebih awal: middleware `Authenticate` bawaan Laravel, saat mendeteksi request tidak terautentikasi dan **tidak** mengirim header `Accept: application/json`, akan mencoba redirect ke route bernama `login` — dan karena ini API murni tanpa halaman login berbasis Blade, route itu tidak pernah didefinisikan → exception → user (atau notification polling) mendapat error 500 mentah, bukan 401 yang rapi.
- **Dampak:** Pola waktu di log (muncul berkelompok tiap ~1 menit) mengindikasikan ini kemungkinan besar dipicu oleh **fitur polling notifikasi** yang jalan di background dengan token kosong/kedaluwarsa — artinya ini bisa terjadi berulang terus-menerus di production tanpa disadari user (karena mungkin tertangkap diam-diam di try-catch frontend), memenuhi log server dengan noise dan berpotensi membingungkan saat debugging masalah lain nantinya.
- **Fix:** Di `bootstrap/app.php`, tambahkan konfigurasi supaya guest tidak pernah diarahkan ke route bernama `login` untuk request API:
  ```php
  ->withMiddleware(function (Middleware $middleware): void {
      $middleware->redirectGuestsTo(fn ($request) => null); // selalu lempar 401 JSON, jangan pernah redirect
  })
  ```

---

## 🟡 SEDANG — Kebersihan Deployment (bukan bug fungsional, tapi risiko nyata)

### 7. File `.env` asli ikut terbundle di paket deploy — dan `APP_DEBUG=true`
- **Bukti:** File `.env` ada di root paket ini dengan `APP_KEY` asli terisi dan **`APP_DEBUG=true`**.
- **Risiko:** Kalau paket ini di-upload apa adanya ke shared hosting, setiap error (termasuk bug #6 di atas) akan menampilkan **stack trace lengkap ke publik** — termasuk path server, query SQL, dan potongan kode. Ini bukan cuma bug tampilan, tapi kebocoran informasi yang bisa dimanfaatkan orang lain untuk memetakan kelemahan sistem.
- **Fix:** `.env` **tidak boleh** ikut di-zip untuk deploy — buat `.env` baru langsung di server dengan `APP_DEBUG=false`, `APP_ENV=production`, dan `APP_KEY` baru (`php artisan key:generate`).

### 8. Database SQLite hasil testing developer ikut terbundle
- **Bukti:** `database/database.sqlite` ikut ter-zip, saya query langsung — berisi 7 user asli dengan password ter-hash (password default `admin123` untuk semua), audit log asli, dan 2 SPK contoh.
- **Risiko:** Kalau ini yang dipakai langsung di production, semua akun demo dengan password `admin123` yang sudah publik (pernah dibahas di audit sebelumnya) ikut aktif di server sungguhan.
- **Fix:** Jangan bundle file `.sqlite` di paket deploy. Jalankan `php artisan migrate --seed` langsung di server dengan data seed yang sudah disesuaikan (ganti password default, atau generate random password sekali print di awal).

### 9. CORS tidak dikonfigurasi eksplisit — masih pakai default Laravel (`allowed_origins => ['*']`)
- **Bukti:** Tidak ditemukan `config/cors.php` yang di-publish di project ini, artinya aplikasi memakai default bawaan framework yang **mengizinkan origin apa pun**.
- **Catatan:** Ini persis temuan yang sama dari audit versi Node.js sebelumnya — perlu dipastikan benar-benar dibatasi sebelum go-live, bukan cuma dilupakan lagi di rewrite ini.
- **Fix:** `php artisan config:publish cors` lalu set `allowed_origins` ke domain frontend spesifik.

### 10. Audit log menelan semua error secara diam-diam
- **Lokasi:** `app/Services/AuditService.php` — `catch (\Throwable $e) { /* Silently ignore */ }`
- **Catatan:** Bug array-to-string yang dulu ada di log **sudah diperbaiki** (sekarang pakai `json_encode` untuk value non-string) — bagus. Tapi try-catch yang menelan semua error tanpa mencatat apa pun berarti kalau audit logging gagal lagi karena sebab lain di masa depan, tidak akan ada jejak sama sekali untuk debug — padahal tabel ini justru dimaksudkan sebagai jejak kepatuhan/keamanan.
- **Saran:** Minimal catat ke `Log::warning()` saat audit logging gagal, jangan dibiarkan sunyi total.

---

## ✅ Status Temuan Lama (dari audit versi Node.js sebelumnya)

| Temuan lama | Status di versi Laravel ini |
|---|---|
| `quick-switch` auth bypass tanpa password | ✅ Sudah dihapus total |
| IDOR foto evidence (FIELD_TEAM lintas vendor) | ✅ Sudah diperbaiki (`EvidenceService` cek assignment) |
| Geofencing tidak divalidasi | ✅ Ada `require_strict_gps` & `geofence_default_radius_meters` di system settings — perlu diverifikasi implementasinya benar-benar dipakai di `CheckInService`, belum saya cek detail |
| Permission Matrix dekoratif | ⚠️ Sebagian — sekarang endpoint-nya ADA, tapi endpoint itu sendiri **tidak dilindungi** (lihat temuan #2) — jadi masalahnya bergeser, bukan hilang |
| `vendor_isolation` middleware dead code | ⚠️ Isolasi vendor untuk WorkOrder & Evidence sekarang benar via service, TAPI isolasi CLIENT tidak pernah diimplementasikan sama sekali (temuan #5 — gap baru) |

---

## Urutan Prioritas Perbaikan

1. **Temuan #1 dan #2** (privilege escalation via user management + permission matrix tanpa proteksi) — ini paling berbahaya, harus ditutup sebelum go-live dalam bentuk apa pun.
2. **Temuan #5** (kebocoran data lintas-tenant untuk CLIENT) — kritis karena bertentangan langsung dengan tujuan awal Client Portal.
3. **Temuan #3 dan #4** (Master Data & Template CRUD tanpa proteksi) — pola perbaikannya sama seperti #1, bisa dikerjakan sekaligus dalam satu commit karena solusinya identik di semua controller.
4. **Temuan #6** (error 500 saat unauthenticated) — sudah terbukti terjadi berulang di log, quick fix satu baris di `bootstrap/app.php`.
5. **Temuan #7, #8, #9** — bukan bug kode, tapi **wajib** dibereskan sebelum upload ke shared hosting (checklist deployment, bukan checklist coding).
6. **Temuan #10** — perbaikan kecil, bisa disatukan dengan pekerjaan lain.

---

## Catatan Metodologi

Saya **tidak** menjalankan aplikasi secara live (tidak ada `composer install` penuh di lingkungan audit ini karena dibatasi akses jaringan ke Packagist), jadi temuan di atas murni dari:
1. Pembacaan source code langsung (paling dominan, dan paling bisa diandalkan untuk gap RBAC)
2. Log error asli yang ikut terbundle (bukti kejadian nyata, bukan dugaan)
3. Query langsung ke database SQLite yang ikut terbundle (untuk verifikasi skema & data aktual)

Frontend (`client/src`) **tidak ikut** di paket ini — hanya build hasil kompilasi (`public/assets/index-*.js` yang sudah di-minify). Jadi audit tombol/menu per halaman seperti yang saya lakukan untuk versi Node dulu **belum bisa dilakukan** untuk paket ini. Kalau mau audit level itu juga, saya perlu source `client/src` terbaru (atau paket ini dijalankan live dan saya diberi hasil klik-per-klik).