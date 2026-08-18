# PRODUCT BLUEPRINT — SGX VENDOR WORK EVIDENCE

**Dokumen:** Product Blueprint
**Nama konsep:** SGX Vendor Work Evidence
**Jenis aplikasi:** Sistem Pengendalian dan Bukti Pekerjaan Vendor
**Status:** Blueprint awal untuk rebuild dari nol
**Tujuan:** Menjadi sumber kebenaran awal sebelum pembuatan arsitektur, database, UI/UX, rule AI, dan coding.

---

# 1. TUJUAN APLIKASI

Aplikasi ini digunakan untuk mengelola pekerjaan vendor dari awal sampai selesai, terutama untuk memastikan:
- pekerjaan vendor tercatat dengan jelas;
- tim lapangan mendapatkan penugasan yang benar;
- lokasi pekerjaan dapat diverifikasi melalui GPS;
- dokumentasi foto memiliki konteks pekerjaan;
- foto Before / Process / After dikelola secara terstruktur;
- kendala teknis dapat dicatat;
- vendor dapat memantau progres pekerjaan;
- admin dapat melakukan review dan meminta revisi;
- pekerjaan yang telah disetujui dapat diajukan menjadi BA Opname;
- laporan dapat dibuat menggunakan template kop perusahaan;
- aktivitas penting memiliki audit trail;
- notifikasi dapat dikirim melalui WhatsApp Gateway Fonnte.

Aplikasi bukan sekadar aplikasi upload foto.

> **Digital Evidence & Reporting System**

Nilai utama aplikasi adalah membuktikan bahwa pekerjaan vendor dilakukan di lokasi yang benar, oleh tim yang benar, pada waktu yang benar, dengan dokumentasi yang lengkap, lalu mengubah bukti tersebut menjadi laporan yang dapat dipertanggungjawabkan.

---

# 2. MASALAH YANG HARUS DISELESAIKAN

Aplikasi baru harus mengurangi:
1. aturan bisnis yang terlalu banyak dan saling bertabrakan;
2. status pekerjaan yang sulit dipahami;
3. dokumentasi foto yang tidak terstruktur;
4. kesulitan mengetahui siapa yang melakukan pekerjaan;
5. kesulitan memverifikasi lokasi pekerjaan;
6. kesulitan vendor melihat perkembangan;
7. kesulitan admin memeriksa kelengkapan;
8. revisi tanpa alasan yang jelas;
9. tidak adanya riwayat perubahan yang jelas;
10. pembuatan laporan/BA yang terlalu manual;
11. ketergantungan berlebihan pada WhatsApp provider;
12. kompleksitas yang membuat sistem sulit dikembangkan.

---

# 3. PRINSIP DESAIN UTAMA

## 3.1 Satu pekerjaan = satu objek utama

Objek bisnis utama adalah **WORK ORDER / SPK**.

Semua data berhubungan dengan pekerjaan tersebut:

SPK → Vendor → Lokasi → Penanggung Jawab → Tim → Check-in → Evidence → Kendala → Progress → Review → Revision → BA Opname → Audit.

Jangan membuat banyak objek bisnis yang memiliki fungsi sama atau tumpang tindih.

## 3.2 Status harus sederhana

Status awal:

```text
DRAFT
↓
READY
↓
ASSIGNED
↓
CHECKED_IN
↓
IN_PROGRESS
↓
SUBMITTED
↓
UNDER_REVIEW
↓
REVISION
↓
APPROVED
↓
BA_OPNAME
↓
COMPLETED
```

Status hanya boleh berubah melalui workflow yang ditentukan. User tidak boleh mengubah status secara bebas.

## 3.3 Foto adalah Evidence

Foto bukan sekadar attachment.

Setiap evidence harus memiliki konteks:
- SPK / Work Order
- tahap dokumentasi
- user
- server timestamp
- GPS
- GPS accuracy
- urutan foto
- catatan bila diperlukan

Struktur:

```text
Evidence
├── BEFORE
│   ├── Photo 01
│   ├── Photo 02
│   └── Photo 03
├── PROCESS
│   ├── Photo 01
│   ├── Photo 02
│   └── Photo 03
└── AFTER
    ├── Photo 01
    ├── Photo 02
    └── Photo 03
```

Mode dokumentasi:
```text
AFTER_ONLY
BEFORE_PROCESS_AFTER
```

## 3.4 Server adalah sumber kebenaran

Server menjadi sumber kebenaran untuk:
- authorization;
- status;
- progress;
- timestamp resmi;
- GPS resmi;
- kelengkapan evidence;
- assignment;
- approval;
- revision;
- audit.

## 3.5 Perubahan harus minimal

Gunakan prinsip **Smallest Safe Change**:
- jangan melakukan refactoring besar yang tidak berhubungan dengan task;
- jangan mengubah file unrelated;
- jangan mengubah arsitektur tanpa alasan.

## 3.6 Core workflow harus sederhana

Core:
```text
SPK → Assignment → Check-in → Evidence → Submit → Review → Revision → Approval → BA
```

Supporting:
```text
WhatsApp, Reports, Dashboard, Notifications, Audit, Template, Export
```

Fitur lanjutan tidak boleh mengganggu core workflow.

---

# 4. ROLE APLIKASI

Empat role:
1. SUPERUSER
2. ADMIN
3. FIELD_TEAM / TIM LAPANGAN
4. VENDOR

---

# 5. SUPERUSER

Superuser adalah administrator sistem, bukan operator pekerjaan sehari-hari.

Menu:
```text
Dashboard
Users
Roles & Permissions
System Settings
Audit Log
Profile
```

Kemampuan:
- mengelola user;
- mengelola role dan permission;
- mengelola konfigurasi sistem;
- melihat audit log;
- mengelola pengaturan global.

---

# 6. ADMIN

Admin adalah pusat operasional.

Menu:
```text
Dashboard

WORK MANAGEMENT
├── Pekerjaan / SPK
├── Penugasan
├── Monitoring
└── Review

DOCUMENTATION
├── Dokumentasi
├── Kendala
└── Evidence

REPORTING
├── BA Opname
├── Laporan Pekerjaan
├── Laporan Vendor
└── Export

MASTER DATA
├── Vendor
├── Tim Lapangan
├── Area
└── Jenis Pekerjaan

SYSTEM
├── Template Dokumen
├── Notification
└── Profile
```

---

# 7. ADMIN DASHBOARD

Dashboard menjawab: **Apa yang sedang terjadi sekarang?**

KPI:
```text
TOTAL PEKERJAAN
BERJALAN
MENUNGGU CHECK-IN
MENUNGGU REVIEW
REVISION
TERLAMBAT
SELESAI
```

Alert:
```text
Pekerjaan melewati deadline
Pekerjaan menunggu revisi
Pekerjaan menunggu review
Pekerjaan belum check-in
```

Dashboard harus informatif, bukan penuh grafik yang tidak diperlukan.

---

# 8. PEMBUATAN SPK / WORK ORDER

Form sederhana.

Informasi:
```text
Nomor SPK
Nama Pekerjaan
Vendor
Area
Lokasi
Penanggung Jawab
Tanggal Mulai
Deadline
```

Dokumentasi:
```text
○ AFTER ONLY
○ BEFORE + PROCESS + AFTER
```

Check-in:
```text
☑ Wajib Check-in
```

Kemudian:
```text
[ASSIGN TIM]
```

Jangan memasukkan terlalu banyak konfigurasi ke form SPK.

---

# 9. PENUGASAN TIM

Admin menentukan:
```text
SPK → Tim Lapangan
```

Satu pekerjaan dapat memiliki:
- satu PIC utama;
- beberapa anggota.

Contoh:
```text
PIC: Andi
Anggota: Budi, Candra, Dedi
```

PIC harus jelas.

---

# 10. FIELD TEAM

UI Tim Lapangan sederhana.

Menu:
```text
Dashboard
Pekerjaan Saya
Riwayat
Profile
```

Fokus utama:
- pekerjaan;
- check-in;
- kamera;
- evidence;
- kendala;
- submit.

---

# 11. HALAMAN PEKERJAAN TIM LAPANGAN

Contoh:
```text
SPK-2026-00125
Pemasangan Palang Merek
Kabupaten X

Deadline: 18 Aug 2026
Status: READY

[MULAI PEKERJAAN]
```

---

# 12. CHECK-IN

Jika check-in diwajibkan, tombol **MULAI PEKERJAAN**:
1. meminta permission GPS;
2. mendapatkan latitude;
3. mendapatkan longitude;
4. mendapatkan GPS accuracy;
5. mengambil timestamp server;
6. menyimpan user;
7. menyimpan Work Order;
8. membuat Check-in Session.

Contoh:
```text
CHECK-IN BERHASIL
Lokasi tercatat
08:42
Andi

[LANJUTKAN DOKUMENTASI]
```

User tidak boleh mengetik GPS manual. Official GPS tidak boleh diedit user lapangan.

---

# 13. EVIDENCE / DOKUMENTASI

## AFTER_ONLY
```text
AFTER
[ + TAMBAH FOTO ]
```

## BEFORE_PROCESS_AFTER
```text
1. BEFORE
[ + TAMBAH FOTO ]
0 / 3 minimum

2. PROCESS
[ + TAMBAH FOTO ]
0 / 3 minimum

3. AFTER
[ + TAMBAH FOTO ]
0 / 3 minimum
```

Foto dapat ditambahkan satu per satu.

---

# 14. KENDALA TEKNIS

```text
ADA KENDALA?
○ Tidak
○ Ya
```

Jika Ya:
```text
Jenis Kendala
[................]

Catatan
[................]

Foto Pendukung
[ + Tambah Foto ]
```

Kendala menjadi data terstruktur agar dapat dilaporkan.

---

# 15. SUBMIT PEKERJAAN

Sebelum submit, server melakukan validation gate:

```text
✓ Check-in valid
✓ Assignment valid
✓ Dokumentasi lengkap
✓ Kendala telah diisi
✓ Persyaratan evidence terpenuhi
```

Server memeriksa:
- check-in;
- assignment;
- status;
- dokumentasi;
- required evidence;
- authorization.

Frontend bukan sumber kebenaran.

---

# 16. VENDOR

Menu:
```text
Dashboard
Pekerjaan
Progress
Dokumentasi
Laporan
Profile
```

Vendor dapat melihat pekerjaan miliknya, lokasi, progress, check-in, dokumentasi, status, dan laporan yang diperbolehkan.

Vendor tidak boleh melihat data vendor lain atau catatan internal yang tidak diperuntukkan baginya.

---

# 17. TRACKING PEKERJAAN VENDOR

```text
SPK-00125

✓ SPK dibuat
✓ Tim ditugaskan
✓ Check-in
✓ Pekerjaan dimulai
● Dokumentasi
○ Review Admin
○ BA Opname
○ Selesai
```

Progress ditampilkan sebagai hasil workflow/kriteria penyelesaian.

Progress tidak boleh diinput manual sesuka user.

---

# 18. REVIEW ADMIN

Admin melihat:
```text
INFORMASI
CHECK-IN
MAP
BEFORE
PROCESS
AFTER
KENDALA
TIM
AUDIT
```

Pilihan:
```text
[APPROVE]
[MINTA REVISI]
```

Revisi harus spesifik:
```text
Bagian: AFTER PHOTO
Catatan: Foto lokasi nomor 2 kurang jelas.
```

---

# 19. REVISION

Setiap revisi memiliki history:

```text
Revision #1
Target: AFTER PHOTO
Reason: Foto kurang jelas
Requested By: Admin A
Date: 16 Aug 2026
Status: OPEN
```

Setelah diperbaiki:
```text
RESUBMITTED
```

History revisi tidak dihapus.

---

# 20. APPROVAL

Jika lolos review:
```text
APPROVED
↓
BA_OPNAME
```

Approval adalah event resmi yang diaudit.

Tim lapangan tidak boleh menyetujui pekerjaannya sendiri.

---

# 21. BA OPNAME

Setelah approval:
```text
APPROVED
↓
[BUAT BA OPNAME]
```

Data otomatis:
```text
Nomor SPK
Vendor
Nama Pekerjaan
Lokasi
Tanggal
Dokumentasi
Tim
```

Admin:
```text
Preview PDF
Download
Print
```

Template kop perusahaan disimpan di **Template Dokumen**.

---

# 22. WHATSAPP GATEWAY

Fonnte diposisikan sebagai notification provider.

```text
Application
↓
Notification Service
↓
Fonnte
```

Workflow utama tidak boleh bergantung langsung pada Fonnte.

Contoh:
- Field Team: pekerjaan baru;
- Vendor: pekerjaan menunggu verifikasi;
- Admin: pekerjaan diajukan untuk review.

---

# 23. NOTIFICATION LOG

Simpan:
```text
Provider
Recipient
Message Type
Sent At
Status
Error
```

Status:
```text
PENDING
SENT
DELIVERED
FAILED
```

Notifikasi gagal tidak boleh merusak core workflow.

---

# 24. AUDIT LOG

Aktivitas penting harus tercatat.

Contoh:
```text
Admin A
Changed status
SPK-00125
READY → ASSIGNED

Andi
Check-in
SPK-00125
GPS recorded

Andi
Uploaded After Photo #3

Admin A
Requested Revision
```

Audit harus menjawab:
- siapa;
- melakukan apa;
- terhadap data apa;
- kapan;
- perubahan dari apa menjadi apa.

---

# 25. REPORTING

Menu:
```text
Laporan Pekerjaan
Laporan Vendor
Laporan Tim Lapangan
Laporan Deadline
Laporan Dokumentasi
Laporan Kendala
Laporan BA
```

Filter:
```text
Tanggal
Vendor
Area
Jenis Pekerjaan
Status
Tim
```

Export:
```text
PDF
Excel
```

---

# 26. MASTER DATA

Minimal:
```text
Vendor
Area
Jenis Pekerjaan
Tim Lapangan
User
Template Dokumen
```

Jangan membuat master data yang belum diperlukan.

---

# 27. STRUKTUR DATA INTI

```text
users
roles
vendors
areas
work_orders
work_order_assignments
check_ins
evidences
evidence_photos
issues
reviews
revisions
ba_documents
notifications
audit_logs
document_templates
```

Jangan membuat database terlalu besar pada MVP.

---

# 28. DATA PENTING

## Timestamp
Server timestamp adalah resmi. Client timestamp hanya informasi/audit tambahan.

## GPS
GPS resmi berasal dari perangkat/browser saat check-in atau evidence. User tidak boleh mengetik atau mengedit koordinat resmi.

Jika validasi GPS diperlukan:
- latitude;
- longitude;
- accuracy;
- jarak terhadap lokasi pekerjaan.

## Progress
Progress dikendalikan sistem dan berasal dari workflow/kriteria penyelesaian.

---

# 29. SECURITY

Authorization wajib di server. UI visibility bukan security boundary.

## Vendor
Hanya dapat mengakses pekerjaan milik vendor tersebut.

## Field Team
Hanya dapat bekerja pada pekerjaan dengan assignment aktif.

Field Team tidak boleh:
- mengakses pekerjaan yang tidak ditugaskan;
- mengubah check-in orang lain;
- upload evidence ke pekerjaan lain;
- menghapus evidence final;
- mengubah timestamp resmi;
- mengubah GPS resmi;
- menyetujui pekerjaan sendiri.

---

# 30. PHOTO EVIDENCE ENGINE

Setiap evidence photo sebaiknya memiliki:
```text
WORK ORDER ID
STAGE
USER
SERVER TIMESTAMP
GPS
GPS ACCURACY
SEQUENCE
FILE
HASH
```

Stage:
```text
BEFORE
PROCESS
AFTER
ISSUE
```

---

# 31. FILE SECURITY

Upload foto harus:
- validasi MIME type;
- batas ukuran;
- validasi file;
- menolak executable;
- menangani ekstensi palsu;
- dapat menggunakan hash;
- menggunakan storage aman;
- tidak memperbolehkan eksekusi file upload.

---

# 32. STATUS VOCABULARY

```text
DRAFT
READY
ASSIGNED
CHECKED_IN
IN_PROGRESS
SUBMITTED
UNDER_REVIEW
REVISION
APPROVED
BA_OPNAME
COMPLETED
```

Jangan membuat status berbeda untuk arti yang sama.

Setiap status harus memiliki definisi tunggal.

---

# 33. TRANSACTION INTEGRITY

Operasi bisnis multi-step menggunakan database transaction jika diperlukan.

Contoh:
```text
Submit Work Order
+
Update Status
+
Create Audit
```

harus konsisten.

---

# 34. CORE VS SUPPORTING VS FUTURE

## CORE — MVP
```text
SPK
Assignment
Check-in
Evidence
Before/Process/After
Issue
Submit
Review
Revision
Approval
BA
Vendor Tracking
Audit
```

## SUPPORTING
```text
WhatsApp
Reports
Dashboard
Notifications
Template
Export
```

## FUTURE — jangan dibangun pada MVP
```text
AI image verification
Facial recognition
Advanced GIS
Route optimization
Native mobile app
AI-generated BA
Complex offline synchronization
Advanced AI analytics
```

---

# 35. UI/UX PRINCIPLES

## Admin
Desktop-first. Fokus pada tabel, filter, status, review, dashboard, reporting.

## Field Team
Mobile-first. Fokus pada pekerjaan, check-in, kamera, evidence, kendala, submit.

## Vendor
Responsive. Fokus pada pekerjaan, progress, dokumentasi, status, laporan.

## Superuser
Desktop-first. Fokus pada users, roles, settings, audit.

---

# 36. NAVIGASI FINAL

## SUPERUSER
```text
Dashboard
Users
Roles & Permissions
System Settings
Audit Log
Profile
```

## ADMIN
```text
Dashboard
Pekerjaan / SPK
Penugasan
Monitoring
Review
Dokumentasi
Kendala
Evidence
BA Opname
Laporan
Export
Vendor
Tim Lapangan
Area
Jenis Pekerjaan
Template Dokumen
Notification
Profile
```

## FIELD TEAM
```text
Dashboard
Pekerjaan Saya
Riwayat
Profile
```

## VENDOR
```text
Dashboard
Pekerjaan
Progress
Dokumentasi
Laporan
Profile
```

---

# 37. ARSITEKTUR YANG DIINGINKAN

Aplikasi harus:
- modular;
- mudah dikembangkan;
- mudah diuji;
- mudah di-host;
- tidak terlalu bergantung pada vendor eksternal;
- memiliki service layer;
- memiliki server-side authorization;
- memiliki audit;
- memiliki notification abstraction;
- memiliki document generation abstraction;
- memiliki storage abstraction.

Fonnte tidak boleh menjadi dependency inti workflow.

---

# 38. ATURAN REBUILD

Aplikasi dibangun dari blueprint ini.

Jangan:
- menyalin business logic lama tanpa validasi;
- menyalin aturan lama hanya karena ada di aplikasi lama;
- melakukan refactoring aplikasi lama sebagai fondasi utama;
- membuat fitur hanya karena aplikasi lama memilikinya;
- menambahkan aturan tanpa alasan bisnis;
- membuat status baru tanpa definisi;
- membuat menu baru tanpa tujuan.

Setiap fitur harus menjawab:
1. Masalah apa yang diselesaikan?
2. Siapa yang menggunakannya?
3. Data apa yang diperlukan?
4. Apa dampaknya terhadap workflow?
5. Apa aturan validasinya?
6. Bagaimana cara mengujinya?

---

# 39. URUTAN PEMBANGUNAN

```text
FASE 01 — PRODUCT BLUEPRINT
↓
FASE 02 — BUSINESS WORKFLOW
↓
FASE 03 — ROLE & PERMISSION
↓
FASE 04 — DATABASE DESIGN
↓
FASE 05 — UI/UX DESIGN
↓
FASE 06 — TECHNICAL ARCHITECTURE
↓
FASE 07 — AI CONTEXT SYSTEM
↓
FASE 08 — CODING
↓
FASE 09 — TESTING
↓
FASE 10 — DEPLOYMENT
```

Blueprint ini menjadi dasar fase berikutnya.

---

# 40. AI DEVELOPMENT PRINCIPLE

AI coding agent tidak boleh membaca seluruh project setiap task.

## Always available
```text
AI_CONTEXT.md
CURRENT_STATE.md
CORE AI RULE
```

## On-demand
```text
RBAC.md
DATABASE.md
TESTING.md
DEPLOYMENT.md
BUSINESS_RULES.md
ARCHITECTURE.md
API.md
```

Agent hanya membaca dokumentasi on-demand yang relevan dengan task.

---

# 41. MINIMAL CONTEXT CODING

Untuk setiap task:

```text
TASK
↓
DETERMINE SCOPE
↓
READ ONLY RELEVANT CONTEXT
↓
DISCOVER RELEVANT CODE
↓
PLAN MINIMAL CHANGE
↓
IMPLEMENT
↓
RUN TARGETED TEST
↓
VERIFY
↓
UPDATE CURRENT_STATE IF NEEDED
```

Jangan:
```text
READ EVERYTHING
↓
SCAN EVERYTHING
↓
REFACTOR EVERYTHING
```

---

# 42. ATURAN PERUBAHAN

Untuk task biasa:
- hanya ubah file yang diperlukan;
- jangan refactor unrelated;
- jangan rename file/class tanpa kebutuhan;
- jangan mengubah struktur folder tanpa alasan;
- jangan mengubah database tanpa kebutuhan;
- jangan mengubah migration yang sudah pernah dijalankan;
- gunakan migration baru untuk schema change;
- jangan menghapus data production;
- jangan mengubah credential tanpa approval;
- jangan deployment tanpa approval.

---

# 43. DEFINITION OF DONE

Task selesai jika:
1. Scope sesuai permintaan.
2. Tidak ada perubahan unrelated.
3. Business rule terpenuhi.
4. Authorization benar.
5. Data integrity terjaga.
6. Test relevan berhasil.
7. Tidak ada error baru yang diketahui.
8. Dokumentasi diperbarui jika diperlukan.
9. Current state diperbarui jika diperlukan.
10. AI memberikan ringkasan perubahan.

---

# 44. REKOMENDASI NAMA PRODUK

**SGX Vendor Work Evidence**

Alternatif:
**SGX Vendor Management**

Subjudul:
**Digital Evidence & Reporting System**

Deskripsi:
> Sistem digital untuk mengendalikan pekerjaan vendor, merekam bukti pekerjaan berbasis lokasi dan waktu, memonitor progres, melakukan verifikasi, dan menghasilkan laporan pekerjaan secara terstruktur.

---

# 45. KESIMPULAN

Model bisnis utama:

```text
SPK
↓
PENUGASAN
↓
CHECK-IN
↓
PELAKSANAAN
↓
EVIDENCE
↓
SUBMIT
↓
REVIEW
↓
REVISION
↓
APPROVAL
↓
BA OPNAME
↓
COMPLETED
```

Semua fitur lain harus mendukung alur tersebut.

Prioritas:
1. Workflow jelas.
2. Evidence valid.
3. GPS dan timestamp dapat dipercaya.
4. Authorization aman.
5. Vendor isolation.
6. Review dan revision terstruktur.
7. BA dapat dibuat dari data pekerjaan.
8. Audit lengkap.
9. Sistem mudah dikembangkan.
10. AI development menggunakan minimal context.