# Ide Fitur — Client Portal SGX_VENDOR

**Fokus:** Mempermudah client (pemberi tugas) melihat & memahami laporan pekerjaan tanpa perlu bantuan tim SGX.
**Basis data yang tersedia:** `target_lat`/`target_lng` per SPK, `latitude`/`longitude` per foto evidence, `area_id`, `job_type_id`, `audit_logs`, timestamp check-in & upload per stage (BEFORE/PROCESS/AFTER).
**Catatan:** Sebagian besar fitur di bawah bisa dibangun tanpa mengubah skema database besar-besaran — datanya sudah ada, tinggal divisualisasikan.

---

## 🟢 Prioritas 1 — Quick Win (data sudah ada, tinggal UI)

### 1. Peta sebaran pekerjaan (map view)
Kolom `target_lat`/`target_lng` per SPK dan koordinat per foto sudah tersimpan tapi tidak pernah divisualisasikan. Client dengan puluhan/ratusan cabang akan jauh lebih mudah membaca sebaran & status via peta (pin merah = belum mulai, kuning = proses, hijau = selesai) dibanding scroll list panjang. Bisa pakai Leaflet + OpenStreetMap (gratis, ringan, tidak butuh API key berbayar).

### 2. Export laporan multi-toko ke Excel/PDF
Saat ini client hanya bisa lihat BA satu-per-satu (print manual via `BaOpnameViewer.vue`). Tambahkan tombol "Export Rekap" yang generate satu file (xlsx) berisi status semua toko + tanggal check-in + tanggal selesai — berguna untuk client yang harus lapor ke atasan mereka sendiri tanpa harus screenshot satu-satu.

### 3. Filter berdasarkan Area/Region
Kolom `area_id` sudah ada di tabel `work_orders`, tapi halaman client belum punya filter berdasarkan wilayah. Client dengan cabang di banyak kota pasti ingin bisa lihat "khusus wilayah Sumatera" atau semacamnya.

### 4. Perbandingan foto Before/After berdampingan (slider)
Foto per stage saat ini kemungkinan ditampilkan terpisah di gallery. Slider before-after (drag untuk bandingkan) jauh lebih meyakinkan sebagai bukti visual dibanding melihat dua foto di posisi berbeda.

### 5. Progress bar visual per cabang dengan indikator SLA
`progress_percent` sudah ada di data SPK, tapi belum ada indikator "sisa berapa hari sebelum deadline" atau highlight otomatis untuk SPK yang sudah lewat `deadline` tapi belum selesai. Ini quick win karena kolom `deadline` sudah ada di tabel `work_orders` — tinggal hitung selisih tanggal di frontend.

---

## 🟡 Prioritas 2 — Value Tambahan (butuh sedikit kerja backend)

### 6. Notifikasi WhatsApp otomatis saat status berubah
Kirim notifikasi ke PIC client (via Fonnte, sudah menjadi tooling langganan di ekosistem ini) saat SPK naik status ke APPROVED atau BA terbit — client tidak perlu login setiap hari untuk tahu progres.

### 7. Timeline aktivitas yang mudah dibaca (bukan raw audit log)
`audit_logs` sudah mencatat semua histori tapi formatnya teknis/internal. Buat tampilan timeline sederhana untuk client: "12 Agu — Check-in teknisi di lokasi", "13 Agu — Foto BEFORE diunggah (3 foto)", "15 Agu — BA terbit". Storytelling progres jauh lebih mudah dicerna client non-teknis dibanding tabel status.

### 8. Kolom komentar/acknowledgment dari sisi client
Saat ini approval hanya dari Admin SGX (`reviewService.js`). Client sering ingin memberi tanda "sudah saya cek, oke" atau catatan singkat langsung di laporan tanpa harus koordinasi via WhatsApp terpisah. Bisa ditambahkan sebagai kolom `client_acknowledged_at` + catatan, tanpa mengubah alur approval internal yang sudah ada.

### 9. Grafik tren periode (mingguan/bulanan)
Grafik jumlah cabang selesai per minggu, rata-rata waktu pengerjaan per SPK, tren kendala lapangan dari waktu ke waktu — berguna untuk laporan berkala client ke manajemen mereka sendiri.

### 10. Badge verifikasi keaslian foto
Tampilkan indikator "Terverifikasi GPS + Timestamp" pada tiap foto evidence yang dilihat client — memanfaatkan data `latitude`/`longitude`/`server_timestamp`/`file_hash` yang sudah tersimpan tapi belum ditonjolkan sebagai elemen kepercayaan (trust signal) di sisi UI client.

### 11. Sertifikat/ringkasan satu halaman per cabang selesai
Untuk cabang yang sudah 100% selesai & BA terbit, generate satu halaman ringkas (logo + foto before-after utama + tanggal + status) yang mudah di-print atau dilampirkan ke laporan internal client — beda dari BA formal yang lebih panjang dan detail teknis.

---

## 🔵 Prioritas 3 — Nice to Have (lebih besar, high-impact untuk skala besar)

### 12. Digest email otomatis mingguan
Kirim ringkasan otomatis ke email PIC client tiap awal minggu: "5 cabang selesai minggu ini, 2 masih proses, 1 kendala". Cocok untuk client yang tidak sempat cek portal tiap hari.

### 13. Akses read-only via link publik per SPK (tanpa login)
Client sering forward laporan ke pihak lain (misal tim internal mereka yang tidak punya akun). Sediakan link publik dengan token unik per SPK/BA yang bisa dibuka tanpa login — cukup untuk lihat status + foto, tanpa expose data SPK client lain.

### 14. Multi-user per client dengan scope wilayah berbeda
Untuk client besar dengan struktur regional (misal PIC Sumatera vs PIC Jawa), sediakan beberapa akun client di bawah satu perusahaan, masing-masing hanya melihat cabang di wilayah tanggung jawabnya.

### 15. Survey kepuasan singkat per SPK selesai
Setelah BA terbit, tampilkan form rating singkat (1-5 bintang + catatan opsional) yang bisa diisi client — datanya berguna untuk evaluasi performa vendor/tim SGX ke depannya, dan bisa jadi nilai tambah kalau ditunjukkan ke calon client baru.

### 16. White-label branding portal per client
Untuk client besar/strategis, tampilkan logo perusahaan client di header portal mereka sendiri (bukan cuma logo SGX) — kesan lebih personal dan profesional, terutama kalau portal ini nantinya dijual sebagai value-add layanan ke client korporat.

### 17. Kalender jadwal pekerjaan
Tampilan kalender (bukan hanya list) untuk melihat kapan tiap cabang dijadwalkan mulai dan deadline-nya — membantu client merencanakan kapan mereka perlu standby di lokasi (misal untuk akses masuk toko, koordinasi security, dsb).

---

## Rekomendasi Titik Mulai

Kombinasi **#1 (peta)** + **#4 (before-after slider)** + **#7 (timeline readable)** paling disarankan untuk dikerjakan lebih dulu: ketiganya memakai data yang sudah ada di database, tidak butuh perubahan skema, dan paling langsung terasa dampaknya terhadap pengalaman client saat membaca laporan.

Setelah itu, **#6 (notifikasi WhatsApp)** dan **#8 (acknowledgment client)** adalah langkah berikutnya yang paling menutup gap komunikasi antara SGX dan client tanpa perlu infrastruktur baru yang besar.
