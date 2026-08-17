# PROMPT: Audit Fungsional Menyeluruh — SGX_VENDOR (Laravel + Vue 3)

Gunakan prompt ini langsung ke Claude Code di root project (folder yang berisi `laravel-backend/` dan `client/`).

---

## Konteks Project (jangan diasumsikan ulang, sudah pasti)

- **Backend:** Laravel 13 (`laravel-backend/`), Sanctum untuk auth, `spatie/laravel-permission` untuk RBAC, `intervention/image` untuk foto, `barryvdh/laravel-dompdf` untuk BA Opname PDF.
- **Frontend:** Vue 3 SPA (`client/`), tidak pakai vue-router (navigasi berbasis `activeTab` state di `App.vue`), token disimpan di `localStorage` key `sgx_token`.
- **Role aplikasi:** SUPERUSER, ADMIN, VENDOR, FIELD_TEAM, CLIENT — masing-masing punya halaman/menu berbeda di `client/src/pages/{admin,vendor,field,client,superuser}/`.
- **Ada folder legacy** di repo yang sama: `server/` (versi Node.js lama) dan `php-backend/` (percobaan PHP native yang tampaknya ditinggalkan). **Ini bukan bagian dari aplikasi yang diaudit** — laporkan sebagai temuan housekeeping (berpotensi bikin bingung/tertinggal saat deploy), tapi jangan dianalisis fungsinya.

---

## Tujuan

Pastikan **setiap tombol, menu, form, dan alur kerja benar-benar berfungsi end-to-end** — bukan hanya lolos compile/lint. Temukan:
1. Tombol/menu yang tidak melakukan apa-apa (handler kosong, `TODO`, `console.log` placeholder, atau tidak terhubung sama sekali)
2. Pemanggilan API dari frontend yang tidak punya route backend yang cocok (atau sebaliknya — route backend yang tidak pernah dipanggil frontend, indikasi fitur setengah jadi)
3. Error runtime (500, exception PHP, error JS di console) saat alur normal dijalankan
4. Broken state: loading yang tidak pernah selesai, error yang tidak ditampilkan ke user, form yang submit tapi tidak memberi feedback
5. Inkonsistensi RBAC (satu endpoint terverifikasi tapi endpoint serupa lain tidak — ini pernah jadi bug nyata di versi Node sebelumnya, cek betul konsistensinya di versi Laravel ini)

---

## Metodologi (jalankan berurutan, laporkan tiap langkah sebelum lanjut ke berikutnya)

### Langkah 1 — Setup & jalankan aplikasi
- `cd laravel-backend && composer install && php artisan migrate:fresh --seed && php artisan serve`
- `cd client && npm install && npm run dev`
- Konfirmasi kedua server jalan tanpa error sebelum lanjut. Kalau migration/seed gagal, itu sudah bug prioritas tinggi — laporkan dulu sebelum lanjut audit lainnya.

### Langkah 2 — Audit statis: pemetaan Route ↔ Frontend Call
- List semua route di `laravel-backend/routes/api.php`.
- Grep semua pemanggilan API di `client/src/**/*.vue` dan `client/src/services/api.js`.
- Buat tabel silang: route yang **tidak pernah dipanggil frontend** (dead endpoint) vs. pemanggilan frontend yang **tidak match route manapun** (akan selalu 404).
- Cek juga method HTTP-nya cocok (POST vs GET vs DELETE) — mismatch method jadi silent bug.

### Langkah 3 — Audit statis: setiap tombol & menu per halaman
Untuk **setiap file** di `client/src/pages/*/*.vue` dan komponen shared (`Navbar.vue`, `Sidebar.vue`, `NotificationDrawer.vue`, dll):
- List semua elemen `<button>`, `@click`, `router-link`/`activeTab =`, dan elemen dengan handler event lain.
- Untuk tiap satu: telusuri ke method di `<script setup>` — pastikan method itu **benar-benar ada**, tidak kosong, tidak sekadar `// TODO`, dan benar-benar memanggil API atau mengubah state yang berarti.
- Tandai kalau ada tombol yang terlihat aktif secara visual (tidak disabled) tapi ternyata tidak terhubung ke handler apa pun.

### Langkah 4 — Audit alur kerja utama end-to-end (functional walkthrough)
Jalankan tiap alur berikut secara langsung di browser (pakai akun sesuai role dari seeder), catat setiap step yang gagal/error/hasilnya tidak sesuai:

1. **Login** tiap role (SUPERUSER, ADMIN, VENDOR, FIELD_TEAM, CLIENT) → pastikan diarahkan ke dashboard yang benar sesuai role.
2. **Admin: buat SPK baru** → isi semua field wajib → assign field team → simpan → cek muncul di list.
3. **Field Team: check-in** ke SPK yang di-assign → cek lokasi tersimpan, status SPK berubah.
4. **Field Team: upload foto bukti** tiap stage (BEFORE/PROCESS/AFTER) → cek watermark, cek foto tersimpan & tampil di gallery admin.
5. **Field Team: laporkan issue/kendala** → cek muncul di Field Issues Manager admin.
6. **Admin: review & approve SPK** → cek status berubah, cek notifikasi terkirim (in-app minimal).
7. **Admin: request revisi** → cek field team menerima info revisi, bisa upload ulang.
8. **Admin: generate BA Opname PDF** → cek PDF ter-generate benar (tidak corrupt, data lengkap, foto muncul).
9. **Vendor: lihat dashboard vendor** → pastikan hanya melihat data SPK milik vendor sendiri (bukan vendor lain — ini poin RBAC kritis).
10. **Client: lihat task list & BA list** → pastikan hanya melihat data miliknya.
11. **Superuser: buka Permission Matrix** → ubah satu permission → simpan → **verifikasi benar-benar berefek** (coba akses fitur itu dengan role yang izinnya baru dicabut, harus benar-benar tertolak, bukan cuma UI matrix-nya yang berubah).
12. **Master Data (semua role admin/superuser)**: tambah/edit Vendor, Area, Job Type, Field Team → cek tersimpan & muncul di dropdown terkait.
13. **Notifikasi**: cek badge unread, cek mark-as-read benar-benar mengubah status.
14. **Logout** → cek token benar-benar invalid setelahnya (coba panggil API lain pakai token lama, harus 401).

Untuk tiap langkah, buka Developer Console browser dan catat **semua error JS/network 4xx-5xx** yang muncul, walaupun UI terlihat "baik-baik saja" (banyak bug tersembunyi di balik try-catch yang menelan error diam-diam).

### Langkah 5 — Audit RBAC konsisten (fokus khusus, karena ini riwayat bug nyata di versi sebelumnya)
- Cek **setiap** endpoint yang memodifikasi data foto evidence (`upload`, `deletePhoto`, `reportIssue`, `resolveIssue`) — pastikan **semua** memvalidasi bahwa FIELD_TEAM hanya bisa beraksi pada SPK yang di-assign ke dirinya, bukan hanya sebagian endpoint.
- Cek isolasi VENDOR konsisten di semua endpoint yang mengembalikan data SPK/evidence/BA — bukan cuma di satu controller.
- Cek endpoint yang seharusnya read-only untuk CLIENT benar-benar menolak write action kalau dicoba paksa lewat API langsung (bukan cuma disembunyikan di UI).

### Langkah 6 — Audit form & edge case
- Submit form dengan field kosong/invalid → pastikan validasi jelas (bukan cuma gagal senyap atau error 500 mentah ditampilkan ke user).
- Upload file dengan format/ukuran salah → pastikan pesan error jelas, bukan crash.
- Cek state kosong (empty state) — misal SPK baru tanpa foto sama sekali, vendor tanpa SPK — pastikan halaman tidak crash/blank.
- Cek loading state tidak infinite kalau API gagal (harus ada timeout/error handling, bukan spinner selamanya).

---

## Format Laporan yang Diinginkan

Untuk setiap temuan bug, gunakan format ini (bukan narasi panjang):

```
### [SEVERITY] Judul singkat bug
- **Lokasi:** file:baris (frontend & backend kalau relevan keduanya)
- **Langkah reproduksi:** 1... 2... 3...
- **Yang terjadi:** ...
- **Yang seharusnya terjadi:** ...
- **Saran fix:** ...
```

Severity: **Kritis** (data hilang/salah, RBAC bocor, crash total) / **Tinggi** (fitur utama tidak jalan) / **Sedang** (fitur jalan tapi UX buruk/pesan error tidak jelas) / **Rendah** (kosmetik, typo, dead code).

---

## Aturan Kerja
- **Jangan langsung fix sambil audit.** Kumpulkan dulu semua temuan Langkah 1-6 jadi satu laporan lengkap.
- Setelah laporan lengkap diberikan dan direview, baru masuk mode fix: **satu bug = satu commit**, tiap fix harus ada test (atau minimal langkah verifikasi manual yang jelas) sebelum lanjut ke bug berikutnya — ikuti pola kerja test-gated yang biasa dipakai di project ini.
- Kalau menemukan folder `server/` (Node) atau `php-backend/` masih di-reference di suatu tempat (build script, .env, dokumentasi), laporkan sebagai temuan housekeeping terpisah — jangan dihapus tanpa konfirmasi.
