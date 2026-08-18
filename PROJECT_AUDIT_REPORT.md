# Audit Report — PROJECT_AUDIT_PACKAGE

**Metode:** SCAN → TRACE → VERIFY → REPORT. Dokumentasi dipakai hanya sebagai peta navigasi; temuan ini didasarkan pada source code yang disertakan.

## Ringkasan

Status: **tidak layak diproduksikan sebelum temuan kritis ditutup.**

| Severity | Jumlah |
|---|---:|
| Critical | 2 |
| High | 4 |
| Medium | 3 |

Paket berisi 116 file source dan 63 file PHP. Seluruh file PHP yang ada lolos `php -l`, tetapi paket tidak menyertakan `vendor/`, `artisan`, `bootstrap/app.php`, lockfile Composer, `node_modules`, atau lockfile NPM. Karena itu klaim build/integration/API test tidak dapat direproduksi dari artefak ini.

## Temuan

### C-01 — Diagnostic endpoint dapat mereset database tanpa autentikasi

`source/config/test.php` adalah halaman publik tanpa autentikasi. Ia membaca konfigurasi database, menampilkan daftar pengguna, dan bila dipanggil dengan `?action=migrate` menjalankan `migrate:fresh --seed --force`. Dampaknya adalah pengungkapan metadata/PII dan penghapusan total data produksi oleh pengguna anonim bila file ter-deploy di web root.

**Bukti:** `test.php:4-5`, `:63`, `:75-82`, `:224`.

**Perbaikan:** jangan deploy file ini; hapus dari web root dan rotasi kredensial bila pernah terekspos. Pindahkan tooling operasional ke CLI yang diautentikasi kuat, serta hilangkan seluruh aksi destruktif berbasis GET.

### C-02 — Streamer publik memungkinkan pembacaan file di luar storage (path traversal)

`GET /api/storage-stream/{path}` bersifat publik dan memasukkan `$clean` yang belum dinormalisasi/diizinkan ke sejumlah path filesystem lalu memanggil `file_get_contents`. Regex hanya menghapus prefix `storage/`, bukan segmen `..`. Contoh payload traversal yang perlu dipastikan/diblokir di lingkungan staging adalah segmen URL-encoded menuju `.env`. Risiko mencakup pengungkapan `.env`, kode, atau file aplikasi.

**Bukti:** `source/backend/routes/api.php:29-53,71`. Klaim dokumen bahwa traversal telah disanitasi tidak sesuai dengan implementasi ini.

**Perbaikan:** hapus endpoint berbasis path publik. Stream berdasarkan ID record yang sudah diautorisasi, gunakan `Storage` disk yang terikat root, canonical-path containment check, allowlist ekstensi/MIME, dan `response()->file/streamDownload`.

### H-01 — Isolasi BA lintas tenant tidak diterapkan untuk detail dan PDF

Daftar BA membatasi `VENDOR`/`CLIENT` hanya jika `vendor_id` ada, tetapi `show()` dan `downloadPdf()` mencari BA hanya berdasarkan ID/work-order ID tanpa pengguna atau scope. Semua pengguna terautentikasi dapat meminta BA tenant lain dengan ID yang dapat ditebak.

**Bukti:** `source/backend/app/Http/Controllers/Api/BaDocumentController.php:16-29,63-103`.

**Perbaikan:** satu policy/scoped query wajib dipakai pada index, show, dan download; untuk client/vendor tanpa `vendor_id`, kembalikan 403/empty, bukan seluruh data.

### H-02 — Perubahan password tidak aman dan kontrak UI/API rusak

UI mengirim `currentPassword` dan `newPassword`; API hanya membaca field `password`, tidak memvalidasi panjang/konfirmasi, dan tidak memverifikasi password lama. Dampaknya: perubahan melalui UI tidak terjadi; pemanggil API yang mengirim `password` dapat mengganti sandi tanpa re-authentication.

**Bukti:** `UserProfileModal.vue:329-331`; `AuthController.php:94-96`.

**Perbaikan:** validasi `current_password`, `new_password` (policy kekuatan) dan `new_password_confirmation`; verifikasi `Hash::check`, revoke token aktif bila sesuai kebijakan, dan samakan kontrak frontend.

### H-03 — Bukti foto bersifat publik dan upload tidak memvalidasi file

Rute view/file foto ditempatkan sebelum middleware Sanctum; endpoint streaming memberi CORS `*`. Upload hanya memvalidasi SPK dan tahap, tanpa rule `file`, MIME/ekstensi, atau ukuran. Service memakai ekstensi yang dikirim klien serta permission `0777` untuk direktori/file. Hash SHA-256 hanya mencatat bytes saat unggah; tidak mencegah pemalsuan metadata GPS atau menjamin sumber gambar.

**Bukti:** `routes/api.php:73-75`; `EvidenceController.php:14-34`; `EvidenceService.php:28-59,87-93`.

**Perbaikan:** autentikasi dan policy pada view/download; validasi server-side (image/MIME, ukuran, decode/re-encode), simpan di storage privat, gunakan 0640/0750, dan nyatakan GPS/watermark sebagai data yang tidak tepercaya tanpa attestation perangkat.

### H-04 — LEWATKAN

### M-01 — Work-order dapat disubmit tanpa bukti minimum atau transisi status tervalidasi

`submit()` langsung mengubah status ke `REVIEW`; tidak ada pemeriksaan kelengkapan BEFORE/PROCESS/AFTER atau `min_photos_per_stage`, meski properti itu tersedia pada master data. Workflows frontend juga merujuk `SUBMITTED`, `UNDER_REVIEW`, `BA_OPNAME`, dan `COMPLETED`, sementara API memakai `REVIEW` dan `APPROVED`.

**Bukti:** `WorkOrderController.php:296-312`; `MasterDataController.php:142,158`; `ReviewController.php:33-38`.

**Perbaikan:** buat state machine tunggal di backend dengan guard bukti/check-in/revisi dan uji transisi yang dilarang.

### M-02 — Endpoint complete yang dipakai UI tidak ada di backend

Frontend memanggil `POST /ba/complete/{id}`, tetapi tidak ada rute backend terkait. Aksi penandaan selesai akan menghasilkan 404.

**Bukti:** `frontend/src/services/api.js:86`; pencarian seluruh `backend/routes` tidak menemukan rute tersebut.

### M-03 — Notifikasi tidak ter-scope ketika ditandai dibaca

`markAsRead()` menerima ID lalu menandai notifikasi tanpa memastikan ia terlihat oleh pengguna; `markAllAsRead()` mengambil seluruh ID. Ini merusak integritas status notifikasi lintas peran/tenant.

**Bukti:** `NotificationController.php:52-73`.

## Klaim dokumentasi yang tidak terverifikasi/bertentangan

* Dokumentasi menyebut Laravel 11/PHP 8.2; `composer.json` menyatakan Laravel `^13.17` dan PHP `^8.3`.
* Dokumentasi menyebut route guard `router/index.js`; file itu tidak ada dalam paket (navigasi ada di `App.vue`).
* Dokumentasi menyatakan streaming mencegah traversal; endpoint API tidak melakukannya.
* Laporan build/test tidak dapat direproduksi karena seluruh runtime/dependency dan test suite tidak disertakan. Yang dapat diverifikasi hanya syntax: 63/63 file PHP lolos `php -l`.
* Dokumentasi menyebut alur berakhir `COMPLETED → VERIFIED`; source tidak memiliki transisi `VERIFIED` dan persetujuan mengubah status ke `APPROVED`.

## Prioritas remediasi

1. Segera hapus/deny `test.php`, `stream.php`, dan public path-streaming dari deployment; rotasi rahasia serta lakukan incident review bila sudah internet-facing.
2. Terapkan policy dan scoped query di seluruh akses objek (BA, foto, issues, notifikasi), bukan hanya endpoint daftar.
3. Perbaiki alur password dan upload; ubah storage menjadi privat dengan izin least-privilege.
4. Satukan kontrak route/status API-frontend dan tambahkan test otorisasi lintas tenant, upload, state transition, serta negative tests.
5. Baru lakukan build dan integration test dari artefak lengkap dengan dependency lockfiles dan test suite.

## Batas verifikasi

Audit ini adalah static-source audit atas ZIP, bukan pentest terhadap host produksi. Eksploitasi HTTP dan operasi destruktif tidak dijalankan. Namun C-01 adalah jalur destruktif langsung dalam source; C-02 harus diperlakukan sebagai critical sampai diuji dan ditutup pada staging.
