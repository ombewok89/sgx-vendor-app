# TUGAS: AUDIT DAN PERBAIKAN TOTAL RBAC PERMISSION MATRIX

Anda sedang bekerja pada project:

**SGX Vendor Work Evidence & Management**

Repository referensi:

`https://github.com/rzaoesman-create/sgx-vendor-app`

Masalah utama yang harus diperbaiki:

> Menu **Matriks Hak Akses RBAC → Konfigurasi Hak Akses Role & Menu (CRUD)** tidak dapat menyimpan perubahan permission ketika tombol **Save / Simpan** ditekan.

Jangan langsung menebak penyebab dan jangan hanya memperbaiki UI.

Lakukan audit end-to-end terhadap seluruh alur Permission Matrix sampai akar masalah ditemukan dan diperbaiki.

---

# 1. TUJUAN UTAMA

Pastikan administrator/SUPERUSER dapat:

- memilih Role;
- melihat daftar seluruh menu/modul;
- melihat hak akses yang sudah tersimpan;
- mengaktifkan/menonaktifkan permission;
- mengatur hak:
  - View
  - Create
  - Update
  - Delete
- menekan tombol Save;
- backend menerima payload;
- permission tersimpan permanen ke database;
- matrix langsung menampilkan data terbaru;
- setelah browser refresh permission tetap tersimpan;
- perubahan permission benar-benar memengaruhi menu dan API aplikasi.

Permission Matrix **tidak boleh hanya bersifat dekoratif di frontend**.

---

# 2. JANGAN UBAH FITUR DI LUAR SCOPE

Jangan melakukan redesign besar aplikasi.

Jangan mengganti framework.

Jangan menghapus fungsi yang sudah berjalan.

Jangan mengubah struktur database tanpa alasan yang benar-benar diperlukan.

Jangan mengganti sistem autentikasi yang sudah ada.

Pertahankan:

- Laravel
- Laravel Sanctum
- Spatie Laravel Permission jika memang sedang digunakan
- struktur role aplikasi
- struktur menu aplikasi yang sudah ada.

Fokus pada:

**RBAC + Permission Matrix + sinkronisasi frontend/backend/database.**

---

# 3. MULAI DENGAN AUDIT, BUKAN CODING

Sebelum mengubah kode, lakukan repository scan.

Cari dan identifikasi semua file yang berhubungan dengan:

- Permission Matrix
- Role
- Permission
- Menu
- Sidebar
- Navigation
- Authorization
- Middleware
- Spatie Permission
- API Permission
- Auth
- User Role

Gunakan pencarian project seperti:

`PermissionController`

`updateMatrix`

`matrix`

`permissions`

`roles`

`syncPermissions`

`givePermissionTo`

`revokePermissionTo`

`hasPermissionTo`

`can`

`hasRole`

`role_has_permissions`

`model_has_roles`

`model_has_permissions`

`Permission::`

`Role::`

`sidebar`

`menu`

`navigation`

`RBAC`

Temukan implementasi aktual, bukan berdasarkan asumsi.

---

# 4. TRACE TOMBOL SAVE

Cari halaman:

**Matriks Hak Akses RBAC → Konfigurasi Hak Akses Role & Menu (CRUD)**

Identifikasi event handler tombol:

`Save`

atau:

`Simpan`

atau fungsi seperti:

`handleSave()`

`savePermissions()`

`updatePermissions()`

`submitMatrix()`

Pastikan event benar-benar dipanggil.

Trace:

UI

↓

state permission

↓

payload

↓

API client

↓

HTTP request

↓

Laravel route

↓

PermissionController

↓

validation

↓

Spatie Permission

↓

database

↓

response API

↓

update frontend state

↓

reload matrix

Jelaskan di laporan di titik mana alur tersebut saat ini gagal.

---

# 5. PERIKSA ROUTE BACKEND

Cari route terkait Permission Matrix.

Kemungkinan:

`GET /api/permissions/matrix`

dan

`POST /api/permissions/matrix`

atau route serupa.

Pastikan route Save benar-benar mengarah ke method yang tepat.

Contoh target:

```php
Route::get('/permissions/matrix', [PermissionController::class, 'matrix']);

Route::post('/permissions/matrix', [PermissionController::class, 'updateMatrix']);
```

Jangan memaksakan contoh tersebut apabila struktur project menggunakan route lain.

Gunakan route aktual project.

Pastikan tidak terjadi kesalahan seperti:

- frontend melakukan PUT tetapi backend POST;
- URL frontend berbeda;
- route parameter salah;
- route tidak berada dalam middleware Sanctum;
- CSRF/API configuration salah;
- method controller berbeda.

---

# 6. AUDIT PermissionController

Prioritaskan pemeriksaan:

`app/Http/Controllers/Api/PermissionController.php`

khususnya method seperti:

`matrix()`

dan:

`updateMatrix()`

Verifikasi:

1. request tervalidasi;
2. role benar-benar ditemukan;
3. permission ditemukan/dibuat;
4. permission disinkronkan ke role;
5. transaction berhasil commit;
6. response mengembalikan matrix terbaru.

Cari kemungkinan bug:

- role ID dibandingkan dengan role name;
- permission ID dibandingkan dengan permission name;
- request menggunakan `roleId`, backend mengharapkan `role_id`;
- request menggunakan `permissions`, backend mengharapkan `matrix`;
- key CRUD tidak sesuai;
- array nested salah;
- permission tidak pernah dibuat;
- `syncPermissions()` mendapatkan format yang salah;
- guard Spatie berbeda;
- exception ditelan;
- database transaction rollback;
- response frontend dianggap gagal.

---

# 7. WAJIB PERIKSA FORMAT PAYLOAD

Capture payload aktual yang dikirim frontend.

Contoh kemungkinan format:

```json
{
  "role_id": 2,
  "permissions": {
    "dashboard": {
      "view": true,
      "create": false,
      "update": false,
      "delete": false
    }
  }
}
```

atau:

```json
{
  "role": "ADMIN",
  "permissions": [
    "dashboard.view",
    "work_orders.view",
    "work_orders.create",
    "work_orders.update"
  ]
}
```

Gunakan format yang paling sesuai dengan arsitektur project.

Jangan mempertahankan dua format berbeda antara frontend dan backend.

Buat **satu contract API yang konsisten**.

---

# 8. NORMALISASI PENAMAAN PERMISSION

Periksa sistem penamaan permission.

Gunakan pola yang konsisten seperti:

`dashboard.view`

`work_orders.view`

`work_orders.create`

`work_orders.update`

`work_orders.delete`

`vendors.view`

`vendors.create`

`vendors.update`

`vendors.delete`

dan seterusnya.

Jangan biarkan campuran seperti:

`view_work_order`

`work-order-create`

`work_orders.edit`

`WORKORDER_DELETE`

dalam sistem yang sama.

Bila project sudah memiliki naming convention resmi, pertahankan convention tersebut.

Jangan melakukan migration nama massal apabila tidak diperlukan.

---

# 9. PERBAIKI SECURITY Permission Matrix

Ini wajib.

Permission Matrix merupakan konfigurasi keamanan paling sensitif.

Endpoint membaca dan mengubah Permission Matrix hanya boleh diakses oleh:

`SUPERUSER`

Tambahkan authorization pada backend.

Contoh:

```php
if (!$request->user()->hasRole('SUPERUSER')) {
    return response()->json([
        'message' => 'Forbidden'
    ], 403);
}
```

Lebih baik apabila project sudah memiliki Policy/Gate/Middleware resmi, gunakan mekanisme tersebut.

Authorization **WAJIB dilakukan di backend**.

Jangan mengandalkan:

- menu disembunyikan;
- tombol Save disembunyikan;
- frontend role checking.

Frontend bukan security boundary.

---

# 10. JANGAN SAMPAI SUPERUSER TERKUNCI

Tambahkan perlindungan terhadap konfigurasi yang menyebabkan tidak ada lagi SUPERUSER yang mampu mengelola RBAC.

SUPERUSER harus selalu mempunyai hak minimum untuk:

- membuka pengaturan RBAC;
- membaca Permission Matrix;
- mengubah Permission Matrix;
- mengelola role/permission yang diperlukan.

Jangan izinkan sistem menyebabkan seluruh administrator kehilangan akses.

Jika diperlukan, implementasikan rule:

**SUPERUSER memiliki bypass permission.**

Misalnya melalui Gate before atau mekanisme setara yang sudah digunakan project.

Namun jangan membuat duplicate authorization mechanism.

---

# 11. DATABASE SPATIE PERMISSION

Jika project menggunakan Spatie Laravel Permission, audit:

`roles`

`permissions`

`role_has_permissions`

`model_has_roles`

`model_has_permissions`

Pastikan:

- role tersedia;
- permission tersedia;
- pivot tersimpan;
- `guard_name` konsisten;
- tidak ada duplicate permission;
- tidak ada orphan permission.

Periksa khusus:

`guard_name`

Misalnya seluruh API menggunakan:

`web`

tetapi permission dibuat menggunakan:

`api`

Ini dapat menyebabkan permission tampak tersimpan tetapi Laravel menganggap user tidak memiliki permission.

Gunakan guard yang konsisten dengan konfigurasi aplikasi.

---

# 12. CACHE SPATIE

Periksa cache permission.

Setelah perubahan RBAC berhasil disimpan, pastikan permission cache di-reset dengan cara resmi Spatie.

Gunakan service/package API yang benar.

Jangan membuat mekanisme cache manual jika tidak perlu.

Masalah yang harus dicegah:

Save berhasil

↓

database berubah

↓

tetapi user tetap memakai permission lama karena cache.

---

# 13. GUNAKAN DATABASE TRANSACTION

Update Permission Matrix harus atomic.

Gunakan pola:

```php
DB::transaction(function () {
    // validate role
    // resolve permissions
    // sync role permissions
});
```

Jika satu bagian gagal:

ROLLBACK seluruh perubahan.

Jangan menyimpan setengah matrix.

---

# 14. ERROR HANDLING

Jangan menelan exception.

Jika Save gagal:

backend harus memberikan response JSON jelas.

Contoh:

```json
{
  "success": false,
  "message": "Failed to update permission matrix",
  "error": "..."
}
```

Untuk production jangan membocorkan stack trace.

Log detail error ke Laravel log.

Frontend harus menampilkan feedback:

- berhasil disimpan;
- gagal disimpan;
- forbidden;
- validation error;
- network error.

Tombol Save tidak boleh gagal diam-diam.

---

# 15. FRONTEND STATE

Periksa apakah checkbox CRUD memakai state yang benar.

Cari bug seperti:

- shallow copy state;
- mutation langsung object;
- stale React state;
- checkbox tidak masuk payload;
- role berubah tetapi state sebelumnya terbawa;
- state di-reset sebelum request selesai.

Pastikan setiap perubahan menghasilkan state:

```text
role
 └ menu
     ├ view
     ├ create
     ├ update
     └ delete
```

atau struktur resmi project.

---

# 16. IMPLEMENTASI UNSAVED CHANGES

Tambahkan state:

`isDirty`

Jika pengguna mengubah permission:

`isDirty = true`

Setelah berhasil Save:

`isDirty = false`

Tombol Save sebaiknya:

Disabled jika:

- belum ada perubahan;
- request sedang berjalan;
- role belum dipilih.

Saat Save berjalan:

`Saving...`

Setelah berhasil:

`Hak akses berhasil disimpan.`

---

# 17. REFRESH SETELAH SAVE

Setelah Save berhasil:

JANGAN hanya mempertahankan state lokal.

Fetch kembali matrix dari backend.

Flow:

POST updateMatrix

↓

response success

↓

GET matrix untuk role aktif

↓

replace frontend state

Ini memastikan UI benar-benar mencerminkan database.

---

# 18. MENU VISIBILITY HARUS TERHUBUNG KE RBAC

Audit Sidebar/Navigation.

Permission Matrix harus memengaruhi menu.

Contoh:

Jika:

`work_orders.view = false`

maka menu Work Order tidak muncul.

Tetapi tetap pastikan endpoint API memiliki authorization backend sendiri.

Implementasikan konsep:

Frontend permission = UX

Backend permission = Security

---

# 19. CRUD SEMANTIC

Gunakan arti permission:

### VIEW
- melihat halaman
- membaca list
- membaca detail

### CREATE
- membuat data baru

### UPDATE
- mengubah data

### DELETE
- menghapus data

Jangan gunakan Create sebagai syarat membuka halaman.

Jangan gunakan View untuk mengizinkan Delete.

---

# 20. PARENT MENU DAN CHILD MENU

Jika aplikasi memiliki struktur:

Master Data

├ Vendor

├ Area

├ Job Type

└ Field Team

Parent menu harus tampil jika minimal satu child dapat diakses.

Contoh:

Vendor.view = false

Area.view = true

Maka:

Master Data tetap tampil

tetapi hanya submenu Area yang tampil.

---

# 21. SELECT ALL

Jika UI memiliki:

`Select All`

`Full Access`

atau checkbox parent,

pastikan implementasi konsisten.

Contoh Full CRUD:

View = true

Create = true

Update = true

Delete = true

Jika Create/Update/Delete aktif tetapi View false, tentukan rule.

Rekomendasi:

Create/Update/Delete → otomatis membutuhkan View.

Namun jangan ubah behavior apabila project sudah memiliki business rule berbeda.

---

# 22. ROLE SUPERUSER

SUPERUSER harus diperlakukan khusus.

Rekomendasi:

SUPERUSER memiliki semua permission.

Permission SUPERUSER dapat:

A. dibuat read-only di matrix,

atau

B. selalu memiliki backend bypass.

Gunakan solusi yang paling konsisten dengan arsitektur project.

Jangan biarkan SUPERUSER kehilangan akses ke Permission Matrix.

---

# 23. TEST WAJIB

Buat automated test bila infrastructure test tersedia.

Minimal test:

## TEST 1 — Load Matrix

Login SUPERUSER.

GET Permission Matrix.

Expected:

`200`

dan matrix valid.

---

## TEST 2 — Save Matrix

Ubah permission ADMIN:

`work_orders.create = true`

Save.

Expected:

`200`

Database memiliki permission tersebut.

---

## TEST 3 — Persistence

Reload matrix.

Expected:

permission tetap true.

---

## TEST 4 — Remove Permission

Set:

`work_orders.delete = false`

Save.

Expected:

pivot permission terhapus.

---

## TEST 5 — Unauthorized

Login FIELD_TEAM.

POST Permission Matrix.

Expected:

`403 Forbidden`

---

## TEST 6 — VENDOR

Login VENDOR.

POST Permission Matrix.

Expected:

`403`.

---

## TEST 7 — CLIENT

Login CLIENT.

POST Permission Matrix.

Expected:

`403`.

---

## TEST 8 — Invalid Role

Kirim role tidak dikenal.

Expected:

`422` atau `404`.

Tidak boleh `500`.

---

## TEST 9 — Invalid Permission

Kirim permission tidak dikenal.

Pastikan behavior sesuai design:

reject atau ignore dengan deterministic behavior.

Tidak boleh silent corruption.

---

## TEST 10 — UI Persistence

SUPERUSER:

ubah matrix

↓

Save

↓

refresh browser

Expected:

nilai checkbox sama dengan database.

---

# 24. TEST API SECARA LANGSUNG

Selain test UI, test endpoint langsung.

Gunakan:

PHPUnit/Pest

atau HTTP client yang tersedia.

Tujuannya membedakan:

Frontend bug

vs

Backend bug.

---

# 25. CHECK LOG

Periksa:

`storage/logs/laravel.log`

ketika Save dilakukan.

Cari:

- SQL error
- validation error
- guard mismatch
- permission not found
- role not found
- integrity constraint
- authentication error
- authorization error
- TypeError
- null property
- malformed payload.

Jangan menyimpulkan bug selesai hanya karena halaman tidak error.

---

# 26. SECURITY AUDIT TAMBAHAN TERKAIT

Tanpa memperluas scope terlalu jauh, periksa endpoint:

User Management

Role Management

Permission Management

karena ketiganya berkaitan langsung dengan privilege escalation.

Pastikan user biasa tidak dapat:

- membuat SUPERUSER;
- mengubah dirinya menjadi SUPERUSER;
- mengubah Permission Matrix;
- memberikan permission kepada dirinya sendiri.

Laporkan jika ada vulnerability.

Jangan memperbaiki modul lain yang tidak berhubungan kecuali diperlukan untuk menutup vulnerability RBAC.

---

# 27. ACCEPTANCE CRITERIA

Task dianggap selesai hanya jika seluruh kondisi berikut terpenuhi:

- Permission Matrix dapat dibuka.
- Role dapat dipilih.
- Matrix tampil benar.
- Checkbox dapat diubah.
- Save mengirim request.
- Backend menerima request.
- Tidak ada error 500.
- Database berubah.
- Refresh browser mempertahankan perubahan.
- Spatie permission membaca perubahan.
- User dengan permission baru mendapatkan akses.
- User tanpa permission ditolak.
- Sidebar mengikuti permission.
- API mengikuti permission.
- hanya SUPERUSER dapat mengubah RBAC.
- role rendah mendapat 403.
- SUPERUSER tidak dapat terkunci dari RBAC.
- tidak ada regression pada authentication.

---

# 28. JANGAN BERHENTI SETELAH MENEMUKAN SATU BUG

Jika menemukan misalnya:

payload frontend salah,

jangan langsung berhenti.

Setelah memperbaikinya, lanjutkan trace sampai database.

Kemudian test kembali:

Frontend

→ API

→ Controller

→ Permission

→ Database

→ Cache

→ Authorization

→ UI.

Tujuan akhir bukan membuat tombol Save terlihat bekerja.

Tujuan akhir adalah:

**RBAC BENAR-BENAR BERFUNGSI.**

---

# 29. OUTPUT YANG SAYA INGINKAN

Setelah selesai, berikan laporan:

## A. ROOT CAUSE

Jelaskan penyebab asli Save tidak berfungsi.

Format:

`ROOT CAUSE #1`

File:

Function:

Problem:

Impact:

Fix:

Lakukan untuk setiap penyebab yang ditemukan.

---

## B. FILE YANG DIUBAH

Contoh:

`PermissionController.php`

`api.php`

`PermissionMatrix.tsx`

`api.ts`

`Sidebar.tsx`

dll.

Gunakan file aktual project.

---

## C. PERUBAHAN

Jelaskan singkat perubahan setiap file.

---

## D. DATABASE

Jelaskan apakah ada perubahan database.

Jika tidak diperlukan, tulis:

`Tidak ada perubahan schema database.`

---

## E. TEST RESULTS

Laporkan:

`PASS`

atau

`FAIL`

untuk setiap test.

---

## F. SECURITY RESULTS

Laporkan:

SUPERUSER update matrix:

PASS/FAIL

ADMIN update matrix:

PASS/FAIL

VENDOR update matrix:

PASS/FAIL

FIELD_TEAM update matrix:

PASS/FAIL

CLIENT update matrix:

PASS/FAIL

---

# 30. MODE KERJA

Gunakan metode:

SCAN

↓

TRACE

↓

VERIFY

↓

FIX

↓

TEST

↓

REGRESSION TEST

↓

REPORT

Jangan melakukan refactor besar sebelum root cause diketahui.

Jangan membuat asumsi berdasarkan nama file saja.

Baca implementasi aktual.

Jika struktur project berbeda dari asumsi prompt ini, ikuti struktur aktual project.

---

# HASIL AKHIR YANG DIHARAPKAN

Permission Matrix menjadi:

**Role → Menu → CRUD Permission → Database → Backend Authorization → Frontend Visibility**

dengan alur:

```text
SUPERUSER memilih role
        ↓
matrix di-load dari server
        ↓
SUPERUSER mengubah permission
        ↓
frontend membentuk payload
        ↓
POST update permission matrix
        ↓
backend authorization
        ↓
validation
        ↓
database transaction
        ↓
Spatie syncPermissions
        ↓
reset permission cache
        ↓
response success
        ↓
frontend fetch matrix terbaru
        ↓
UI diperbarui
        ↓
permission langsung berlaku
```

Jangan nyatakan pekerjaan selesai sebelum flow di atas sudah diverifikasi melalui test nyata.