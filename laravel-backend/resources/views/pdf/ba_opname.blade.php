<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Berita Acara Opname - {{ $ba->ba_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; line-height: 1.5; color: #1e293b; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { font-size: 16px; margin: 0; text-transform: uppercase; letter-spacing: 1px; color: #0f172a; }
        .header p { margin: 2px 0 0; font-size: 11px; font-weight: bold; color: #475569; }
        .table-meta { width: 100%; margin-bottom: 15px; }
        .table-meta td { padding: 3px 0; vertical-align: top; }
        .table-items { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .table-items th, .table-items td { border: 1px solid #cbd5e1; padding: 6px 8px; font-size: 10px; text-align: left; }
        .table-items th { background-color: #f1f5f9; font-weight: bold; }
        .clause { text-align: justify; margin-bottom: 20px; }
        .signatories { width: 100%; margin-top: 30px; }
        .signatories td { width: 50%; text-align: center; vertical-align: top; }
        .sign-space { height: 70px; }
        .sign-name { font-weight: bold; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="header">
        <h1>BERITA ACARA SERAH TERIMA & OPNAME PEKERJAAN</h1>
        <p>NOMOR: {{ $ba->ba_number }}</p>
    </div>

    <table class="table-meta">
        <tr>
            <td width="20%"><strong>Nomor SPK</strong></td>
            <td width="30%">: {{ $content['work_order']['spk_number'] ?? '-' }}</td>
            <td width="20%"><strong>Tanggal BA</strong></td>
            <td width="30%">: {{ date('d F Y', strtotime($ba->ba_date)) }}</td>
        </tr>
        <tr>
            <td><strong>Nama Pekerjaan</strong></td>
            <td>: {{ $content['work_order']['title'] ?? '-' }}</td>
            <td><strong>Lokasi Cabang</strong></td>
            <td>: {{ $content['work_order']['location_name'] ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Mitra Pelaksana</strong></td>
            <td>: {{ $content['work_order']['vendor_name'] ?? '-' }}</td>
            <td><strong>Penanggung Jawab</strong></td>
            <td>: {{ $content['work_order']['pic_name'] ?? '-' }}</td>
        </tr>
    </table>

    <div class="clause">
        <p>Pada hari ini tanggal <strong>{{ date('d F Y', strtotime($ba->ba_date)) }}</strong>, telah dilakukan pemeriksaan dan evaluasi opname lapangan terhadap realisasi pelaksanaan pekerjaan dengan rincian sub-pekerjaan sebagai berikut:</p>
    </div>

    <table class="table-items">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="45%">Item Sub-Pekerjaan</th>
                <th width="25%">Mode Dokumentasi</th>
                <th width="25%">Status Hasil</th>
            </tr>
        </thead>
        <tbody>
            @foreach($content['items'] ?? [] as $index => $item)
            <tr>
                <td align="center">{{ $index + 1 }}</td>
                <td><strong>{{ $item['item_name'] }}</strong></td>
                <td>{{ $item['doc_mode'] }}</td>
                <td style="color: #047857; font-weight: bold;">SELESAI 100% ✓</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="clause">
        <p>Berdasarkan hasil verifikasi bukti foto digital (Before, Process, After) yang telah divalidasi dengan segel integritas kriptografi SHA-256 dan koordinat GPS, kedua belah pihak menyatakan bahwa seluruh item pekerjaan telah <strong>SELESAI 100% SECARA BAIK, MEMENUHI SPESIFIKASI MUTU, DAN DITERIMA DENGAN BAIK</strong>.</p>
        <p>Mitra Pelaksana memberikan jaminan masa pemeliharaan mutu selama 90 (sembilan puluh) hari kalender terhitung sejak tanggal penandatanganan Berita Acara ini.</p>
    </div>

    <table class="signatories">
        <tr>
            <td>
                <p>Pihak Pertama (Mitra Pelaksana)<br><strong>{{ $content['work_order']['vendor_name'] ?? 'Mitra Vendor' }}</strong></p>
                <div class="sign-space"></div>
                <p class="sign-name">{{ $content['work_order']['pic_name'] ?? 'Andi Pratama' }}</p>
                <p>Penanggung Jawab Lapangan</p>
            </td>
            <td>
                <p>Pihak Kedua (SGX Management)<br><strong>PT SINAR GRAHA KONSTRUKSI</strong></p>
                <div class="sign-space"></div>
                <p class="sign-name">{{ $ba->generator?->name ?? 'Dian Anggraini' }}</p>
                <p>Quality Assurance & Operations</p>
            </td>
        </tr>
    </table>
</body>
</html>
