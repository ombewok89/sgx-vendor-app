# STANDAR BAKU TEMPLATE TIMESTAMP & EVINSI LAPANGAN
## PT SINAR GRAFIKA (SGX VENDOR PLATFORM)
*Dokumen Spesifikasi Teknis & Aturan Baku Desain Stempel Digital Forensik*

---

## 📌 1. PRINSIP DASAR & ARSITEKTUR WATERMARK

1. **Format Latar Belakang Stempel:**
   * Latar belakang stempel foto **$100\%$ Transparan** (tanpa kotak background gelap solid yang menutupi hasil foto).
   * Seluruh teks dilengkapi dengan **Drop Shadow pekat & Stroke kontras** hitam ganda agar terbaca sangat tajam di atas latar foto terang maupun gelap.

2. **Pembagian Area Kanvas Bawah (Tinggi $470\text{px}$ Scaled):**
   * **Total Ketinggian Area Stempel:** `totalBarH = Math.round(470 * s)` piksel.
   * **Main Content Area:** `385 * s` piksel.
   * **Footer Branding Strip:** `85 * s` piksel (Bar putih solid di dasar foto).
   * **Pembagian Kolom Horizontal:**
     * **Kolom Kiri ($70\%$ Lebar):** 4 Baris Teks Terstruktur.
     * **Kolom Kanan ($30\%$ Lebar):** Mini-Map Satelit Persegi ($1:1$).

---

## 📐 2. SPESIFIKASI TIPOGRAFI & POSISI 4 BARIS TEKS

```
+----------------------------------------------------------------------------------------------------+
|  [Logo Banner Sinar Grafika Transparan - Opacity 52%]                                              |
|                                                                                                    |
|  (Area Foto Dokumentasi Pekerjaan Lapangan)                                                        |
|                                                                                                    |
|  ================================================================================================  |
|  [KOLOM KIRI - 70% LEBAR]                                      | [KOLOM KANAN - 30% LEBAR]         |
|  BARIS 1: 14:25 | 23/08/2026 (Minggu)                          |                                   |
|  BARIS 2: Jl. Adam Malik No. 12, Pagar Dewa, Selebar, Bengkulu |       +-------------------+       |
|  BARIS 3: [ (📍 -3.824921, 102.286299) ] (CENTER)             |       |                   |       |
|  BARIS 4: 📌 Pemasangan Neon Box & Branding Toko (SEJAJAR MAP) |       |  MINI-MAP SATELIT |       |
|                                                                |       |   PERSEGI (1:1)   |       |
|                                                                |       |                   |       |
|  (Jarak Jeda Lega)                                             |       +-------------------+       |
|  ------------------------------------------------------------------------------------------------  |
|  [FOOTER STRIP PUTIH]: [SGX Logo] Sinar Grafika | 082388885251        vendor.sinargrafika.my.id   |
+----------------------------------------------------------------------------------------------------+
```

### A. Logo Atas (Top-Left Watermark)
* **Aset:** Banner Logo Resmi Sinar Grafika (`/sgx_banner_logo.png`).
* **Ukuran Lebar:** `270 * s` piksel.
* **Transparansi / Opacity:** `globalAlpha = 0.52` (Samar, elegan, tidak menghalangi objek foto).
* **Posisi:** Sudut kiri atas dengan margin `20 * s` piksel dari tepi foto.

### B. Baris 1 — Jam Digital, Garis Vertikal Emas, Tanggal & Hari
* **Jam Digital:**
  * **Ukuran Font:** **`120px`** (`Bold 900`, warna Putih `#FFFFFF`).
  * **Efek:** Double black shadow & stroke `8.5 * s` piksel.
* **Garis Pembatas Vertikal Emas:**
  * **Warna:** Emas Brand (`#EAB308` / `#EDC80A`).
  * **Ketinggian:** `92 * s` piksel (Membentang proporsional mengikuti tinggi jam).
  * **Ketebalan:** `6 * s` piksel.
* **Tanggal:**
  * **Ukuran Font:** **`40px`** (`Bold 800`, warna Putih `#FFFFFF`).
  * **Posisi:** Di sebelah kanan garis emas pada bagian atas.
* **Hari:**
  * **Ukuran Font:** **`30px`** (`Bold 800`, warna Emas `#FDE047`).
  * **Posisi:** Tepat di bawah teks tanggal.

### C. Baris 2 — Nama Jalan & Alamat Lengkap Satelit
* **Ukuran Font:** **`32px`** (`Bold 800`, warna Putih `#FFFFFF`).
* **Lebar Maksimal:** $100\%$ selebar area kolom kiri ($70\%$ dari lebar foto).
* **Line Height:** `40 * s` piksel dengan fasilitas *Word-Wrapping* maksimal 2 baris.
* **Aturan Data:** Wajib berasal murni $100\%$ dari hasil reverse-geocoding koordinat GPS satelit riil (dilarang menggunakan teks cadangan *hardcoded*).

### D. Baris 3 — Titik Koordinat GPS (Badge Pill Gelap Dinamis)
* **Ukuran Font:** **`35px`** (`Bold 800`, warna Soft Gold `#FEF08A`, font monospaced).
* **Format Teks:** `📍 [Latitude], [Longitude]`.
* **Badge Pembungkus:**
  * **Warna:** Dark Glass `rgba(0, 0, 0, 0.78)`.
  * **Ketinggian Badge:** `50 * s` piksel.
  * **Corner Radius:** `12 * s` piksel (Rounded Pill).
  * **Padding Horizontal:** `18 * s` piksel.
* **Aturan Penjajaran Vertikal (*Vertical Alignment Rule*):**
  * **Wajib Center Simetris:** Posisi vertikal Baris 3 dihitung otomatis berada tepat di tengah-tengah jarak antara batas bawah Baris 2 (Alamat) dan batas atas Baris 4 (Tag Pekerjaan):
    $$\text{midCenterY} = \frac{\text{lastAddressY} + \text{firstJobY}}{2}$$

### E. Baris 4 — Tag Keterangan Pekerjaan
* **Ukuran Font:** **`32px`** (`Bold 800`, warna Cyan Cerah `#38BDF8`).
* **Format Teks:** `📌 [Keterangan Pekerjaan yang diketik]`.
* **Aturan Penjajaran (*Baseline Alignment Rule*):**
  * Garis dasar (*baseline*) baris terakhir dari Tag Keterangan Pekerjaan **wajib sejajar horizontal secara presisi dengan garis dasar bagian bawah kotak Mini-Map satelit**:
    $$\text{lastLineY} = \text{mapBottomY} - (4 \times s)$$

---

## 🗺️ 3. SPESIFIKASI MINI-MAP SATELIT (KOLOM KANAN)

1. **Rasio Bentuk & Geometri:**
   * **Bentuk:** **Persegi Simetris Presisi ($1:1$ Aspect Ratio)**.
   * **Posisi Horizontal:** Tepat berada di tengah area kolom kanan $30\%$.
   * **Margin Bawah:** Diberikan jarak jeda `20 * s` piksel dari tepi atas footer strip putih.
2. **Elemen Peta Satelit:**
   * **Border Kotak:** Garis putih semi-transparan `2 * s` piksel dengan rounded corner `14 * s` piksel.
   * **Pin Lokasi:** Titik biru elektrik dengan lingkaran putih di titik pusat koordinat.
   * **Radar Visual:** Cincin radar hijau semi-transparan yang melambangkan arah dan jangkauan verifikasi satelit.
   * **Watermark Attribution:** Teks kecil *"Google / Esri Satellite"* di sudut bawah peta.

---

## 🏷️ 4. SPESIFIKASI FOOTER STRIP PUTIH (BRANDING BAR)

1. **Dimensi:**
   * **Tinggi:** `85 * s` piksel membentang $100\%$ selebar kanvas foto di posisi paling dasar.
   * **Latar Belakang:** Putih solid `#FFFFFF`.
2. **Elemen Sisi Kiri:**
   * **Logo Ikon:** Logo Diamond SGX Emas berukuran `50 * s` piksel.
   * **Nama Perusahaan:** "Sinar Grafika" (`Bold 900`, warna Hitam `#0F172A`, ukuran `30 * s` piksel).
   * **Garis Vertikal Pembatas:** Abu-abu `#CBD5E1`.
   * **Hotline / WhatsApp:** `082388885251` (Warna Slate `#334155`, ukuran `26 * s` piksel).
3. **Elemen Sisi Kanan:**
   * **Domain Resmi:** `vendor.sinargrafika.my.id` (Warna Emas Amber `#B45309`, font tebal `26 * s` piksel).

---

## ⚡ 5. STANDAR MESIN, GPS & KECEPATAN SATELIT (BACKEND ENGINE)

1. **Two-Pass Instant Fast-Lock GPS:**
   * **Pass 0 ($0\text{ ms}$):** Baca koordinat & alamat terakhir dari `localStorage` saat komponen pertama kali dibuka.
   * **Pass 1 ($< 1\text{ s}$):** Penguncian cepat jaringan seluler/BTS dengan `enableHighAccuracy: false, maximumAge: 120000`.
   * **Pass 2 (Live Keep-Alive):** Pemantauan GPS satelit aktif kontinu melalui `navigator.geolocation.watchPosition` dengan akurasi tinggi.
2. **Geocoding Engine:**
   * Menggunakan **OpenStreetMap Nominatim Engine pada Zoom Level 18**.
   * Format alamat baku: `Jl. [Nama Jalan] No. [Nomor], [Kelurahan], [Kecamatan], [Kota/Kabupaten], [Provinsi] [Kode Pos]`.
3. **Mesin Pemuatan Satelit Ultra-Cepat (Slippy XYZ CDN & Pre-Fetching):**
   * **Background Proactive Pre-Fetching:** Citra satelit otomatis diunduh di latar belakang saat kamera dibuka dan pengguna sedang membidik objek.
   * **Tile Statis CDN Global:** Menggunakan endpoint XYZ Slippy Tile (Google Satellite CDN & Esri World Imagery) berukuran $\approx 15-25\text{ KB}$ untuk pengiriman data $< 50\text{ ms}$.
   * **Zero-Delay Capture:** Saat foto dijepret, gambar satelit sudah $100\%$ tersedia di memori RAM perangkat ($0\text{ ms}$ render time).

---

## 📱 6. STANDAR PALET WARNA & UI KAMERA

* **Hitam Charcoal Utama:** `#333231` (Container & Glassmorphism Card).
* **Latar Belakang Gelap:** `#1E1E1D` (Body Viewport).
* **Kuning Emas Brand:** `#EDC80A` (Aksen, Border Glow, Icon, dan Status).
* **Oranye Dinamis:** `#F97316` (Tombol Aksi Utama & Shutter Ring).
* **Cyan Cerah:** `#38BDF8` (Teks Tag Keterangan Kerja).
* **Soft Gold:** `#FEF08A` (Teks Koordinat GPS).

---
*Dokumen ini merupakan standar baku resmi seluruh implementasi kamera timestamp di lingkungan PT Sinar Grafika.*
