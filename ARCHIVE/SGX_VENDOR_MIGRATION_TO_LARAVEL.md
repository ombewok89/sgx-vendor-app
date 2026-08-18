# Rencana Migrasi SGX_VENDOR — Node.js/Express → Laravel + MySQL

**Keputusan:** Proyek independen (tidak digabung ke SIMONDOK), stack tujuan **Laravel + MySQL**, untuk shared hosting tanpa Node.js.
**Sumber:** `SGX_VENDOR.rar` (Express + SQLite + Vue 3), hasil audit sebelumnya (`SGX_VENDOR_AUDIT_FINDINGS.md`).
**Prinsip kerja:** Frontend Vue 3 (sudah ada) **tidak perlu ditulis ulang** — cukup diarahkan ke API Laravel yang baru. Hanya backend yang di-port total.

---

## 1. Pemetaan Arsitektur

| Node/Express (lama) | Laravel (baru) |
|---|---|
| `express` routes | `routes/api.php` + Controllers |
| Service layer manual (`*Service.js`) | `app/Services/*Service.php` (pola dipertahankan — arsitekturnya sudah bagus, tinggal port bahasa) |
| `jsonwebtoken` custom middleware | **Laravel Sanctum** (token-based, cocok untuk SPA + mobile webview) |
| RBAC manual (`requireRoles`) | **spatie/laravel-permission** (sudah jadi keputusan di SIMONDOK — pakai yang sama untuk konsistensi) |
| `bcryptjs` | `Hash::make()` / `Hash::check()` (bawaan Laravel) |
| `multer` (upload) | `Illuminate\Http\UploadedFile` + `Storage` facade |
| SQLite raw query (`db.get/all/run`) | Eloquent ORM / Query Builder |
| `sqlite3` file | MySQL via cPanel "MySQL Database Wizard" |
| Watermark/hash foto (manual) | **Intervention Image** (sudah dipakai di SIMONDOK — reuse keputusan yang sama) |
| BA Opname PDF (belum jelas engine-nya, cek `baService.js`) | `barryvdh/laravel-dompdf` atau `spatie/laravel-pdf` |
| Notifikasi Fonnte (manual `fetch`) | `Http::` facade Laravel (lebih rapi, built-in retry/timeout) |
| `cors` middleware `origin: '*'` | Laravel CORS config — **wajib dibatasi ke domain frontend**, jangan ulangi bug lama |

---

## 2. Pemetaan Skema Database (SQLite → MySQL)

21 tabel di skema lama. Semua bisa dipetakan langsung ke Eloquent model + migration. Catatan konversi tipe data:

| Tabel SQLite | Model Laravel | Catatan Migrasi |
|---|---|---|
| `roles` | `Role` | Pertimbangkan **ganti total** ke tabel `roles`/`permissions` bawaan spatie/laravel-permission, jangan pertahankan tabel custom ini |
| `vendors` | `Vendor` | `is_active INTEGER` → `boolean` |
| `areas` | `Area` | Langsung, tanpa perubahan berarti |
| `job_types` | `JobType` | `doc_mode TEXT` → pertimbangkan `enum` MySQL: `BEFORE_PROCESS_AFTER`, dst |
| `users` | `User` (extend model bawaan Laravel) | `password_hash` → `password` (konvensi Laravel), `role TEXT` → **hapus kolom ini**, gantikan sepenuhnya dengan relasi spatie/laravel-permission |
| `field_teams` | `FieldTeam` | Langsung |
| `field_team_members` | pivot table `field_team_user` | Konversi ke pivot standar Laravel (`belongsToMany`) |
| `work_orders` | `WorkOrder` | `target_lat/target_lng REAL` → `decimal(10,7)` (presisi GPS harus dijaga, jangan pakai `float`) |
| `work_order_assignments` | pivot `work_order_user` | `belongsToMany` dengan kolom tambahan (`role_in_team`) via `withPivot()` |
| `work_order_items` | `WorkOrderItem` | Langsung |
| `check_ins` | `CheckIn` | `latitude/longitude/accuracy REAL` → `decimal` |
| `evidence_photos` | `EvidencePhoto` | `file_hash` tetap dipertahankan (integritas foto) — **pastikan ini benar2 dihitung ulang di PHP** (`hash_file('sha256', ...)`) |
| `issues` | `Issue` | Langsung |
| `reviews` | `Review` | Langsung |
| `revisions` | `Revision` | Langsung |
| `document_templates` | `DocumentTemplate` | `header_html/footer_html/body_template TEXT` → `longText` di MySQL |
| `ba_documents` | `BaDocument` | `content_json TEXT` → gunakan `casts => ['content_json' => 'array']` di Eloquent |
| `notifications` | `Notification` | Pertimbangkan pakai **Laravel Notification system bawaan** (custom channel untuk Fonnte) daripada tabel manual |
| `audit_logs` | `AuditLog` | Pertimbangkan package `spatie/laravel-activitylog` daripada tulis manual — lebih matang & auto-capture model changes |
| `system_settings` | `SystemSetting` | Langsung, key-value |
| `notifications_feed` | `NotificationFeed` | Langsung |
| `user_read_notifications` | pivot `notification_feed_user_read` | `belongsToMany` dengan `read_at` di pivot |

**Konversi tipe umum SQLite → MySQL:**
- `INTEGER PRIMARY KEY AUTOINCREMENT` → `bigIncrements('id')` (default Laravel)
- `TEXT` (untuk tanggal ISO string) → `timestamp` / `datetime` (jangan simpan tanggal sebagai string lagi — manfaatkan Carbon)
- `INTEGER DEFAULT 1` (boolean semu) → `boolean('is_active')->default(true)`
- `REAL` → `decimal(10,7)` untuk koordinat GPS, `decimal(8,2)` untuk angka lain

---

## 3. Perbaikan Sekaligus Saat Migrasi (jangan bawa bug lama)

Karena ini rewrite total, ini kesempatan sekaligus menutup semua temuan dari audit sebelumnya — **jangan port bug-nya, port fungsinya saja:**

| Bug lama | Solusi di Laravel |
|---|---|
| `quick-switch` auth bypass | **Jangan buat endpoint ini sama sekali.** Login wajib email+password dari awal. |
| Permission Matrix dekoratif | spatie/laravel-permission otomatis menegakkan lewat middleware `can:` — tidak ada celah "UI vs backend beda" lagi |
| IDOR evidence photo (field team lintas vendor) | Gunakan **Laravel Policy** (`EvidencePhotoPolicy`) yang dicek otomatis via `$this->authorize()` di setiap controller method — satu tempat, tidak bisa lupa seperti kemarin |
| JWT_SECRET hardcoded fallback | `.env` wajib, Laravel akan error di boot kalau `APP_KEY`/config penting kosong (built-in fail-fast) |
| CORS `origin: '*'` | Set eksplisit di `config/cors.php` |
| `/uploads` publik tanpa auth | Simpan foto di `storage/app/private`, sajikan lewat route terautentikasi + `Storage::download()` atau signed URL (`URL::temporarySignedRoute`) |
| Geofencing tidak divalidasi | Implementasikan di `CheckInService` pakai formula haversine (bisa pakai package `spatie/laravel-google-geocoder` kalau butuh reverse geocode juga) |

---

## 4. Struktur Folder Laravel yang Disarankan

```
app/
  Http/Controllers/Api/
    AuthController.php
    WorkOrderController.php
    CheckInController.php
    EvidenceController.php
    ReviewController.php
    BaDocumentController.php
    MasterDataController.php
    NotificationController.php
  Services/
    WorkOrderService.php
    CheckInService.php
    EvidenceService.php
    BaDocumentService.php
    NotificationService.php   (integrasi Fonnte)
    PermissionService.php     (kalau masih perlu custom logic di luar spatie)
  Policies/
    WorkOrderPolicy.php
    EvidencePhotoPolicy.php
  Models/
    (semua 21 tabel di atas)
database/migrations/
  (satu file per tabel, urutan sesuai foreign key)
routes/api.php
```

---

## 5. Fase Eksekusi (disarankan test-gated per fase, commit per fase)

1. **Fase 0 — Setup:** `composer create-project laravel/laravel`, install `spatie/laravel-permission`, `laravel/sanctum`, `intervention/image`, `barryvdh/laravel-dompdf`. Setup `.env` untuk MySQL lokal dulu (bukan langsung di shared hosting).
2. **Fase 1 — Database:** Buat semua migration + model + relasi sesuai tabel §2. Seed roles/permissions via spatie.
3. **Fase 2 — Auth & RBAC:** Login/logout via Sanctum, middleware `can:` per route, **tanpa** quick-switch.
4. **Fase 3 — Master Data:** Vendor, Area, JobType, FieldTeam CRUD — modul paling sederhana, bagus untuk validasi pola kerja dulu.
5. **Fase 4 — Work Order + Assignment:** Termasuk isolasi vendor (port logic dari `workOrderService.js` yang sudah benar).
6. **Fase 5 — Check-in + Evidence Photo:** Termasuk fix IDOR (Policy) dan geofencing.
7. **Fase 6 — Review + Revision + BA Opname PDF:** Termasuk watermark foto (Intervention Image) & generate PDF.
8. **Fase 7 — Notifikasi (Fonnte) + Audit Log.**
9. **Fase 8 — Deploy ke shared hosting:** Build asset (kalau ada Blade+Vite campur) atau full API mode dengan Vue dipisah sebagai static build.

---

## 6. Catatan Deployment Shared Hosting (khusus PHP-only)

- Root domain harus diarahkan ke folder `public/` Laravel, bukan root project — di shared hosting cPanel biasanya perlu **symlink** atau pindahkan isi `public/` ke `public_html` + edit path `index.php` (`require __DIR__.'/../vendor/autoload.php'` disesuaikan).
- Queue: karena tidak ada Node/worker process panjang, pakai **database queue driver** + **cPanel Cron Job** tiap menit menjalankan `php artisan schedule:run` (pola ini konsisten dengan keputusan yang sudah diambil untuk SIMONDOK).
- Storage foto: pastikan `storage/app` writable, dan jalankan `php artisan storage:link` (atau alternatif manual kalau hosting tidak izinkan symlink — banyak shared hosting Indonesia block symlink, cek dulu).

---

## 7. Estimasi Kompleksitas per Modul (kasar, untuk perencanaan waktu)

| Modul | Kompleksitas | Alasan |
|---|---|---|
| Master data (Vendor/Area/JobType) | Rendah | CRUD standar |
| Auth + RBAC | Sedang | Perlu setup spatie permission + seeding role/permission matrix baru |
| Work Order + Assignment | Sedang | Ada logic isolasi vendor yang perlu dijaga persis |
| Check-in + Geofencing | Sedang | Fitur geofencing ini baru (belum pernah jalan di versi lama), perlu testing lapangan |
| Evidence Photo (upload+hash+watermark) | Tinggi | Banyak edge case: kompresi, watermark, hash integrity, policy akses |
| BA Opname PDF | Tinggi | Tergantung kompleksitas template BA yang sudah dirancang — perlu cek `document_templates`/`baService.js` lebih detail sebelum estimasi final |
| Notifikasi Fonnte | Rendah | Sudah ada pola serupa dari proyek lain (reuse) |

---

## Langkah Berikutnya

Modul mana yang ingin dikerjakan lebih dulu sebagai prompt artifact detail ke Claude Code? Saran saya urutan **Fase 0-3** dulu (setup + auth + master data) supaya fondasi RBAC-nya benar sebelum masuk ke modul yang lebih kompleks (evidence & BA PDF).
