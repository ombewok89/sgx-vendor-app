<?php

namespace App\Services;

use App\Models\NotificationFeed;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationDispatcher
{
    /**
     * Helper to get recipient phone numbers for Admin & Supervisor roles.
     */
    public static function getAdminSupervisorPhones(): array
    {
        try {
            $phones = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['SUPERUSER', 'SUPERVISOR', 'ADMIN']);
            })->whereNotNull('phone')->where('phone', '!=', '')->pluck('phone')->toArray();

            // Fallback 1: Check setting in system_settings
            $settingPhone = \App\Models\SystemSetting::whereIn('key', ['wa_admin_phone', 'wa_notification_target', 'admin_phone'])->value('value');
            if (!empty($settingPhone)) {
                $phones[] = $settingPhone;
            }

            // Fallback 2: Check first super admin phone
            if (empty($phones)) {
                $firstAdminPhone = User::whereNotNull('phone')->where('phone', '!=', '')->value('phone');
                if (!empty($firstAdminPhone)) {
                    $phones[] = $firstAdminPhone;
                }
            }

            return array_unique(array_filter($phones));
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * 1. Trigger when a Work Order is Assigned to Field Team
     */
    public static function onSpkAssigned(WorkOrder $workOrder): void
    {
        try {
            $workOrder->loadMissing(['pic', 'vendor', 'area', 'jobType', 'assignments']);

            $recipients = [];
            if ($workOrder->pic?->phone) {
                $recipients[] = $workOrder->pic->phone;
            }
            foreach ($workOrder->assignments as $assignee) {
                if (!empty($assignee->phone)) {
                    $recipients[] = $assignee->phone;
                }
            }
            $recipients = array_unique(array_filter($recipients));

            if (empty($recipients)) {
                return;
            }

            $picName = $workOrder->pic?->name ?? 'Tim Lapangan';
            $clientName = $workOrder->vendor?->name ?? 'Client';
            $areaName = $workOrder->area?->name ?? '-';
            $deadline = $workOrder->deadline ? date('d/m/Y', strtotime($workOrder->deadline)) : '-';

            $msg = "📋 *PENUGASAN SPK BARU — PT SINAR GRAHA KREATIF*\n\n"
                 . "Halo *{$picName}*, Anda telah ditugaskan untuk pengerjaan:\n"
                 . "• *No. SPK:* {$workOrder->spk_number}\n"
                 . "• *Judul:* {$workOrder->title}\n"
                 . "• *Lokasi:* {$workOrder->location_name} ({$areaName})\n"
                 . "• *Klien:* {$clientName}\n"
                 . "• *Batas Waktu (SLA):* {$deadline}\n\n"
                 . "Mohon segera lakukan Check-In GPS di lokasi cabang dan unggah foto dokumentasi (Before/Process/After).\n"
                 . "🔗 *Aplikasi:* https://vendor.sinargrafika.my.id/";

            foreach ($recipients as $phone) {
                FonnteService::sendMessage($phone, $msg, 'SPK_ASSIGNED');
            }
        } catch (\Throwable $e) {
            Log::error('WA onSpkAssigned failed: ' . $e->getMessage());
        }
    }

    /**
     * 2. Trigger when Field Team performs GPS Check-in
     */
    public static function onGpsCheckIn(WorkOrder $workOrder, $user, $checkIn): void
    {
        try {
            $adminPhones = self::getAdminSupervisorPhones();
            if (empty($adminPhones)) {
                return;
            }

            $userName = $user?->name ?? 'Teknisi Lapangan';
            $userPhone = $user?->phone ? " ({$user->phone})" : '';
            $lat = round((float)($checkIn->latitude ?? 0), 5);
            $lng = round((float)($checkIn->longitude ?? 0), 5);
            $accuracy = round((float)($checkIn->accuracy ?? 0));
            $waktu = now()->format('d/m/Y H:i:s');

            $msg = "📍 *PRESENSI GPS CHECK-IN LAPANGAN*\n\n"
                 . "• *No. SPK:* {$workOrder->spk_number}\n"
                 . "• *Lokasi Toko:* {$workOrder->location_name}\n"
                 . "• *Teknisi:* {$userName}{$userPhone}\n"
                 . "• *Waktu:* {$waktu} WIB\n"
                 . "• *Koordinat:* {$lat}, {$lng} (Akurasi: ±{$accuracy}m)\n\n"
                 . "Teknisi telah terkonfirmasi berada di radius lokasi cabang pekerjaan.";

            foreach ($adminPhones as $phone) {
                FonnteService::sendMessage($phone, $msg, 'GPS_CHECKIN');
            }
        } catch (\Throwable $e) {
            Log::error('WA onGpsCheckIn failed: ' . $e->getMessage());
        }
    }

    /**
     * 3. Trigger when Evidence Photo is uploaded
     */
    public static function onEvidenceUpload(WorkOrder $workOrder, $user, $photo): void
    {
        try {
            $adminPhones = self::getAdminSupervisorPhones();
            if (empty($adminPhones)) {
                return;
            }

            $userName = $user?->name ?? 'Teknisi';
            $stage = strtoupper($photo->stage ?? 'EVIDENCE');
            $waktu = now()->format('d/m/Y H:i:s');

            $msg = "📷 *UPLOAD FOTO BUKTI PEKERJAAN*\n\n"
                 . "• *No. SPK:* {$workOrder->spk_number}\n"
                 . "• *Lokasi Toko:* {$workOrder->location_name}\n"
                 . "• *Tahap Dokumentasi:* {$stage}\n"
                 . "• *Diupload Oleh:* {$userName}\n"
                 . "• *Waktu:* {$waktu} WIB\n\n"
                 . "Foto bukti pengerjaan berhasil diunggah dengan segel metadata GPS & Timestamp.";

            foreach ($adminPhones as $phone) {
                FonnteService::sendMessage($phone, $msg, 'EVIDENCE_UPLOAD');
            }
        } catch (\Throwable $e) {
            Log::error('WA onEvidenceUpload failed: ' . $e->getMessage());
        }
    }

    /**
     * 4. Trigger when Field Issue is reported
     */
    public static function onIssueReported(WorkOrder $workOrder, $user, $issue): void
    {
        try {
            $adminPhones = self::getAdminSupervisorPhones();
            if (empty($adminPhones)) {
                return;
            }

            $userName = $user?->name ?? 'Teknisi Lapangan';
            $category = $issue->category ?? 'Kendala Teknis';
            $desc = $issue->description ?? '-';
            $waktu = now()->format('d/m/Y H:i:s');

            $msg = "⚠️ *LAPORAN KENDALA LAPANGAN (URGENT)*\n\n"
                 . "• *No. SPK:* {$workOrder->spk_number}\n"
                 . "• *Lokasi Toko:* {$workOrder->location_name}\n"
                 . "• *Kategori Kendala:* {$category}\n"
                 . "• *Pelapor:* {$userName}\n"
                 . "• *Waktu:* {$waktu} WIB\n"
                 . "• *Rincian Kendala:* {$desc}\n\n"
                 . "Mohon tindak lanjut dan koordinasi segera dengan tim lapangan terkait.";

            foreach ($adminPhones as $phone) {
                FonnteService::sendMessage($phone, $msg, 'ISSUE_REPORTED');
            }
        } catch (\Throwable $e) {
            Log::error('WA onIssueReported failed: ' . $e->getMessage());
        }
    }

    /**
     * 5. Trigger when Work Order is submitted for Admin review
     */
    public static function onSpkSubmitted(WorkOrder $workOrder, $user): void
    {
        try {
            $adminPhones = self::getAdminSupervisorPhones();
            if (empty($adminPhones)) {
                return;
            }

            $userName = $user?->name ?? 'Tim Lapangan';
            $clientName = $workOrder->vendor?->name ?? 'Client';
            $totalPhotos = $workOrder->evidencePhotos()->count();
            $waktu = now()->format('d/m/Y H:i:s');

            $msg = "✅ *PENGAJUAN REVIEW HASIL PEKERJAAN*\n\n"
                 . "• *No. SPK:* {$workOrder->spk_number}\n"
                 . "• *Judul:* {$workOrder->title}\n"
                 . "• *Lokasi:* {$workOrder->location_name}\n"
                 . "• *Klien:* {$clientName}\n"
                 . "• *Diajukan Oleh:* {$userName}\n"
                 . "• *Total Foto Bukti:* {$totalPhotos} foto\n"
                 . "• *Waktu:* {$waktu} WIB\n\n"
                 . "Tim lapangan telah menyelesaikan seluruh pekerjaan fisik dan mengajukan SPK untuk direview.\n"
                 . "🔗 *Buka Review:* https://vendor.sinargrafika.my.id/";

            foreach ($adminPhones as $phone) {
                FonnteService::sendMessage($phone, $msg, 'SPK_SUBMITTED');
            }
        } catch (\Throwable $e) {
            Log::error('WA onSpkSubmitted failed: ' . $e->getMessage());
        }
    }

    /**
     * 6. Trigger when Berita Acara (BA) Opname is approved / issued
     */
    public static function onBaIssued(WorkOrder $workOrder, $baDocument): void
    {
        try {
            $workOrder->loadMissing(['pic', 'vendor']);

            $recipients = self::getAdminSupervisorPhones();
            if ($workOrder->pic?->phone) {
                $recipients[] = $workOrder->pic->phone;
            }
            if ($workOrder->vendor?->phone) {
                $recipients[] = $workOrder->vendor->phone;
            }
            $recipients = array_unique(array_filter($recipients));

            if (empty($recipients)) {
                return;
            }

            $baNumber = $baDocument->ba_number ?? "BA-{$workOrder->spk_number}";
            $clientName = $workOrder->vendor?->name ?? 'Client';
            $waktu = now()->format('d/m/Y H:i:s');

            $msg = "📄 *BERITA ACARA (BA) OPNAME RESMI TERBIT*\n\n"
                 . "• *No. BA:* {$baNumber}\n"
                 . "• *No. SPK:* {$workOrder->spk_number}\n"
                 . "• *Pekerjaan:* {$workOrder->title}\n"
                 . "• *Lokasi Cabang:* {$workOrder->location_name}\n"
                 . "• *Klien:* {$clientName}\n"
                 . "• *Waktu Terbit:* {$waktu} WIB\n\n"
                 . "Pekerjaan telah disetujui 100% dan Berita Acara (BA) digital resmi telah diterbitkan.\n"
                 . "🔗 *Lihat Dokumen BA:* https://vendor.sinargrafika.my.id/";

            foreach ($recipients as $phone) {
                FonnteService::sendMessage($phone, $msg, 'BA_ISSUED');
            }
        } catch (\Throwable $e) {
            Log::error('WA onBaIssued failed: ' . $e->getMessage());
        }
    }
}
