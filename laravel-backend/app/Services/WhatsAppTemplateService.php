<?php

namespace App\Services;

class WhatsAppTemplateService
{
    /**
     * Predefined WhatsApp message templates.
     */
    const TEMPLATES = [
        'TEST_MESSAGE' => "🔔 *[SGX SYSTEM TEST]*\nHalo {user_name},\n\nIni adalah pesan uji coba WhatsApp Gateway Fonnte dari platform *SGX Vendor Work Evidence*.\n\n⏰ Waktu: {date}\n✅ Status: Gateway Berfungsi Normal\n\n_Pesan ini dikirim otomatis oleh sistem._",

        'WORK_ORDER_CREATED' => "📋 *[SPK BARU DIBUAT]*\nNomor SPK: *{spk_number}*\nPekerjaan: *{project_name}*\nLokasi: *{location_name}*\nMitra Klien: *{client_name}*\nBatas Waktu: *{date}*\n\nStatus: *{status}*\nSilakan periksa detail SPK di aplikasi SGX.",

        'WORK_ORDER_ASSIGNED' => "👷 *[PENUGASAN SPK]*\nHalo {user_name},\nAnda telah ditugaskan pada pekerjaan berikut:\n\nNomor SPK: *{spk_number}*\nPekerjaan: *{project_name}*\nLokasi: *{location_name}*\nTarget Selesai: *{date}*\n\nMohon lakukan Check-in GPS setibanya di lokasi toko.",

        'CHECK_IN_SUCCESS' => "📍 *[CHECK-IN BERHASIL]*\nTeknisi: *{user_name}*\nNomor SPK: *{spk_number}*\nLokasi: *{location_name}*\nWaktu: *{date}*\n\nStatus: Sedang Dalam Pengerjaan (IN_PROGRESS).",

        'SUBMISSION_RECEIVED' => "📸 *[BUKTI PEKERJAAN DISERAHKAN]*\nNomor SPK: *{spk_number}*\nLokasi: *{location_name}*\nTeknisi: *{user_name}*\nWaktu Submit: *{date}*\n\nFoto bukti pekerjaan telah lengkap dan menunggu review dari Pengawas SGX.",

        'REVIEW_APPROVED' => "🎉 *[PEKERJAAN DISETUJUI & SELESAI]*\nNomor SPK: *{spk_number}*\nLokasi: *{location_name}*\nMitra Klien: *{client_name}*\nWaktu Persetujuan: *{date}*\n\nStatus: *SELESAI (COMPLETED 100%)*.\nDokumen Berita Acara (BA Opname) telah otomatis diterbitkan.",

        'REVISION_REQUIRED' => "⚠️ *[PERMINTAAN REVISI BUKTI FOTO]*\nHalo {user_name},\nPengawas meminta revisi bukti pekerjaan untuk:\n\nNomor SPK: *{spk_number}*\nLokasi: *{location_name}*\nCatatan Revisi: _{notes}_\n\nMohon unggah foto perbaikan melalui aplikasi SGX.",

        'BA_ISSUED' => "📄 *[DOKUMEN BA OPNAME RESMI TERBIT]*\nNomor Dokumen: *{ba_number}*\nNomor SPK: *{spk_number}*\nLokasi Cabang: *{location_name}*\nMitra Klien: *{client_name}*\nTanggal Terbit: *{date}*\n\nDokumen Berita Acara Serah Terima telah disahkan secara digital dan siap diunduh.",
    ];

    /**
     * Render message template by replacing placeholders with given parameters.
     *
     * @param string $templateKey
     * @param array  $params
     * @return string
     */
    public static function render(string $templateKey, array $params = []): string
    {
        $template = self::TEMPLATES[$templateKey] ?? ($params['custom_message'] ?? "Pemberitahuan Sistem SGX: {spk_number}");

        // Auto-fill standard defaults if not present
        if (!isset($params['date'])) {
            $params['date'] = date('d-m-Y H:i') . ' WIB';
        }
        if (!isset($params['user_name'])) {
            $params['user_name'] = 'Rekan Mitra SGX';
        }

        foreach ($params as $key => $value) {
            $valStr = is_scalar($value) ? (string) $value : '';
            $template = str_replace("{{$key}}", $valStr, $template);
        }

        // Clean any leftover unresolved placeholders e.g. {notes} -> -
        $template = preg_replace('/\{[a-zA-Z0-9_]+\}/', '-', $template);

        return trim($template);
    }

    /**
     * Get all available template definitions and previews.
     *
     * @return array
     */
    public static function listTemplates(): array
    {
        $list = [];
        foreach (self::TEMPLATES as $key => $rawTemplate) {
            $list[] = [
                'key'         => $key,
                'raw_template'=> $rawTemplate,
                'sample'      => self::render($key, [
                    'user_name'     => 'Ahmad Fauzi',
                    'spk_number'    => 'SPK-SGX-20260820-0001',
                    'project_name'  => 'Pemasangan Plang Toko & Branding',
                    'location_name' => 'Alfamart / Indomaret Cabang Margonda',
                    'client_name'   => 'PT Sumber Alfaria Trijaya',
                    'status'        => 'READY',
                    'ba_number'     => 'BA-SGX-202608-0001',
                    'notes'         => 'Foto AFTER sudut kiri kurang terang, mohon ambil ulang di siang hari.',
                ]),
            ];
        }
        return $list;
    }
}
