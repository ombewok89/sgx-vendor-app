# Audit Temuan — SGX_VENDOR (Work Evidence & Management App)

**Stack terdeteksi:** Express.js + SQLite (backend) · Vue 3 + Vite + Tailwind (frontend)
**Metode audit:** Pembacaan langsung source code (routes, services, middleware, schema, frontend api/auth layer). Bukan asumsi dari dokumentasi.
**Tujuan dokumen:** Bisa langsung dipakai sebagai prompt artifact ke Claude Code, satu issue = satu commit, test-gated.

---

## 🔴 PRIORITAS 1 — Kritis (Auth & Access Control)

### 1.1 [SUDAH DI PERBAIKI] Auth bypass total via `POST /api/auth/quick-switch`
- **Status:** ✅ **SUDAH DI PERBAIKI** (Endpoint `POST /api/auth/quick-switch` dan UI switcher telah dihapus total. Form login formal dengan verifikasi kata sandi aktif penuh).
- **File Diperbaiki:** `server/src/routes/authRoutes.js`, `server/src/services/authService.js`, `client/src/pages/auth/LoginPage.vue`, `client/src/components/Navbar.vue`, `client/src/composables/useAuth.js`.

### 1.2 [SUDAH DI PERBAIKI] Permission Matrix tidak menegakkan apa pun di backend
- **Status:** ✅ **SUDAH DI PERBAIKI** (Middleware `checkPermission(moduleId, action)` telah diintegrasikan dan ditegakkan secara aktif pada seluruh rute Work Orders, Master Data Vendors/Areas/Teams/JobTypes/Templates, Review/Approval, dan BA Opname).
- **File Diperbaiki:** `server/src/routes/workOrderRoutes.js`, `server/src/routes/reviewRoutes.js`, `server/src/routes/baRoutes.js`, `server/src/routes/masterDataRoutes.js`, `server/src/routes/evidenceRoutes.js`.

### 1.3 [SUDAH DI PERBAIKI] vendorIsolation middleware dead code + isolasi tidak konsisten
- **Status:** ✅ **SUDAH DI PERBAIKI** (Isolasi data antar-vendor telah diterapkan secara ketat dan konsisten pada seluruh query dan mutasi data: `getEvidencePhotos`, `getFieldIssues`, `resolveIssue`, `getBaDocuments`, `getBaDocumentById`, dan KPI Dashboard).
- **File Diperbaiki:** `server/src/services/evidenceService.js`, `server/src/services/baService.js`, `server/src/routes/baRoutes.js`.

### 1.4 [SUDAH DI PERBAIKI] IDOR — Field team bisa upload/hapus foto bukti di SPK vendor lain
- **Status:** ✅ **SUDAH DI PERBAIKI** (Pengecekan otorisasi penugasan tim lapangan / PIC telah dipasang pada `uploadPhotoEvidence()`, `reportIssue()`, dan `deletePhotoEvidence()`. Field team yang tidak ditugaskan ke SPK tertentu otomatis ditolak).
- **File Diperbaiki:** `server/src/services/evidenceService.js`.

---

## 🟠 PRIORITAS 2 — Konfigurasi & Infrastruktur

### 2.1 [SUDAH DI PERBAIKI] JWT_SECRET hardcoded fallback, tidak ada `.env`
- **Status:** ✅ **SUDAH DI PERBAIKI** (Fallback hardcoded dihilangkan, sistem fail-fast menolak start jika JWT_SECRET kosong. File `.env` dan `.env.example` telah dibuat untuk deployment hosting).
- **File Diperbaiki:** `server/src/middleware/auth.js`, `server/src/services/authService.js`, `server/src/index.js`, `.env`, `.env.example`.

### 2.2 [SUDAH DI PERBAIKI] CORS terlalu longgar
- **Status:** ✅ **SUDAH DI PERBAIKI** (CORS telah dikonfigurasi menggunakan whitelist berbasis environment variable `CORS_ORIGIN`, siap multi-domain hosting maupun reverse-proxy).
- **File Diperbaiki:** `server/src/index.js`, `.env`, `.env.example`.

### 2.3 [SUDAH DI PERBAIKI] Folder `/uploads` disajikan tanpa autentikasi
- **Status:** ✅ **SUDAH DI PERBAIKI** (Akses langsung tanpa token ke `/uploads` diblokir dengan status 401. Disediakan middleware validasi token Bearer/Query string serta rute streaming terproteksi `GET /api/evidence/photos/:id/file` dengan pengecekan isolasi vendor).
- **File Diperbaiki:** `server/src/index.js`, `server/src/routes/evidenceRoutes.js`, `server/src/services/evidenceService.js`.

### 2.4 Password seed default `admin123`
- **File:** `server/src/config/database.js`
- **Fix:** Pastikan ada langkah wajib ganti password di first-run production, atau generate random password saat seeding lalu print sekali ke console.

---

## 🟡 PRIORITAS 3 — Fungsional / Desain

### 3.1 [SUDAH DI PERBAIKI] Geofencing check-in tidak pernah divalidasi
- **Status:** ✅ **SUDAH DI PERBAIKI** (Perhitungan jarak Haversine diintegrasikan pada `performCheckIn()`. Ditambahkan fitur checklist pada form SPK: `require_geofence` dan pemilihan radius toleransi 100m–1000m. Status kecocokan dicatat otomatis: `VERIFIED_MATCH` vs `OUT_OF_RANGE`).
- **File Diperbaiki:** `server/src/services/checkInService.js`, `server/src/services/workOrderService.js`, `server/src/config/database.js`, `client/src/pages/admin/WorkOrderCreateModal.vue`.

### 3.2 [SUDAH DI PERBAIKI] Master data list terbuka untuk semua role tanpa filter
- **Status:** ✅ **SUDAH DI PERBAIKI** (Endpoint `GET /api/master/vendors` telah dibatasi secara ketat berdasarkan role pengguna: akun role VENDOR hanya dapat melihat data profil vendor miliknya sendiri, mencegah kebocoran data kompetitor).
- **File Diperbaiki:** `server/src/routes/masterDataRoutes.js`, `server/src/services/masterDataService.js`.

### 3.3 [SUDAH DI PERBAIKI] Test suite hanya menutup happy-path
- **Status:** ✅ **SUDAH DI PERBAIKI** (Test suite keamanan otomatis `server/tests/security-tests.js` telah dibuat dan sukses menguji 5 skenario batas keamanan: penolakan password salah, proteksi IDOR penugasan, isolasi data multi-vendor, dan validasi radius geofencing).
- **File Diperbaiki:** `server/tests/security-tests.js`, `server/tests/run-tests.js`.

---

## Urutan Eksekusi yang Disarankan
1. **1.1** (auth bypass) — paling berbahaya, kerjakan lebih dulu.
2. **1.4** (IDOR evidence) — pola fix sudah ada contohnya di `checkInService.js`, cepat dikerjakan.
3. **1.2 & 1.3** (permission matrix & vendor isolation) — perlu keputusan desain dulu: mau diaktifkan penuh atau disederhanakan.
4. **2.1–2.4** — konfigurasi, bisa dikerjakan paralel, low-risk.
5. **3.1–3.3** — functional improvement, tidak urgent tapi berdampak ke kualitas data bukti kerja.
