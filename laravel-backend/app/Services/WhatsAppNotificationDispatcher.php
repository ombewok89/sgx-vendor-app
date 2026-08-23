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

            // 1. Dispatch In-App Notification Feed
            NotificationFeed::create([
                'work_order_id' => $workOrder->id,
                'client_id' => $workOrder->vendor_id,
                'target_user_id' => $workOrder->pic_user_id,
                'target_role' => 'ALL',
                'category' => 'SPK_ASSIGNED',
                'title' => "SPK Ditugaskan: {$workOrder->spk_number}",
                'message' => "Surat Perintah Kerja {$workOrder->spk_number} ({$workOrder->location_name}) telah ditugaskan ke {$workOrder->pic?->name}.",
            ]);

            // 2. Dispatch WhatsApp Notification
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
            $userName = $user?->name ?? 'Teknisi Lapangan';
            $userPhone = $user?->phone ? " ({$user->phone})" : '';
            $lat = round((float)($checkIn->latitude ?? 0), 5);
            $lng = round((float)($checkIn->longitude ?? 0), 5);
            $accuracy = round((float)($checkIn->accuracy ?? 0));
            $waktu = now()->format('d/m/Y H:i:s');

            // 1. Dispatch In-App Notification Feed
            NotificationFeed::create([
                'work_order_id' => $workOrder->id,
                'client_id' => $workOrder->vendor_id,
                'target_role' => 'ALL',
                'category' => 'GPS_CHECKIN',
                'title' => "Presensi Check-In: {$workOrder->location_name}",
                'message' => "Teknisi {$userName} telah berhasil melakukan check-in GPS resmi di lokasi cabang.",
            ]);

            // 2. Dispatch WhatsApp Notification
            $adminPhones = self::getAdminSupervisorPhones();
            if (empty($adminPhones)) {
                return;
            }

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
     * 3. Trigger when Evidence Photo is uploaded (Smart Debounce: Max 1 notification per 15 min per SPK)
     */
    public static function onEvidenceUpload(WorkOrder $workOrder, $user, $photo): void
    {
        try {
            $userName = $user?->name ?? 'Teknisi';
            $stage = strtoupper($photo->stage ?? 'EVIDENCE');
            $waktu = now()->format('d/m/Y H:i:s');

            // 1. Dispatch In-App Notification Feed (Debounced 15m)
            $feedKey = 'feed_photo_throttle_' . $workOrder->id . '_' . $stage;
            if (!\Illuminate\Support\Facades\Cache::has($feedKey)) {
                NotificationFeed::create([
                    'work_order_id' => $workOrder->id,
                    'client_id' => $workOrder->vendor_id,
                    'target_role' => 'ALL',
                    'category' => 'EVIDENCE_UPLOAD',
                    'title' => "Dokumentasi Foto: {$stage}",
                    'message' => "Foto dokumentasi tahap {$stage} telah diunggah oleh {$userName} pada {$workOrder->spk_number}.",
                ]);
                \Illuminate\Support\Facades\Cache::put($feedKey, true, now()->addMinutes(15));
            }

            // 2. Dispatch WhatsApp Notification (Throttled)
            $throttleKey = 'wa_photo_throttle_' . $workOrder->id;
            if (\Illuminate\Support\Facades\Cache::has($throttleKey)) {
                return; // Skip repeated upload alerts for the same SPK session
            }
            \Illuminate\Support\Facades\Cache::put($throttleKey, true, now()->addMinutes(15));

            $adminPhones = self::getAdminSupervisorPhones();
            if (empty($adminPhones)) {
                return;
            }

            $msg = "📷 *PROGRES DOKUMENTASI FOTO LAPANGAN*\n\n"
                 . "• *No. SPK:* {$workOrder->spk_number}\n"
                 . "• *Lokasi Toko:* {$workOrder->location_name}\n"
                 . "• *Tahap Terkini:* {$stage}\n"
                 . "• *Teknisi:* {$userName}\n"
                 . "• *Waktu:* {$waktu} WIB\n\n"
                 . "Tim lapangan sedang aktif mendokumentasikan progres foto di lokasi pekerjaan.";

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
            $userName = $user?->name ?? 'Teknisi Lapangan';
            $category = $issue->category ?? $issue->issue_type ?? 'Kendala Lapangan';
            $desc = $issue->description ?? $issue->notes ?? '-';
            $waktu = now()->format('d/m/Y H:i:s');

            // 1. Dispatch In-App Notification Feed
            NotificationFeed::create([
                'work_order_id' => $workOrder->id,
                'client_id' => $workOrder->vendor_id,
                'target_role' => 'ALL',
                'category' => 'ISSUE_REPORTED',
                'title' => "Kendala Lapangan: {$workOrder->location_name}",
                'message' => "Kendala [{$category}]: {$desc} dilaporkan oleh {$userName}.",
            ]);

            // 2. Dispatch WhatsApp Notification
            $adminPhones = self::getAdminSupervisorPhones();
            if (empty($adminPhones)) {
                return;
            }

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
            $userName = $user?->name ?? 'Tim Lapangan';
            $clientName = $workOrder->vendor?->name ?? 'Client';
            $totalPhotos = $workOrder->evidencePhotos()->count();
            $waktu = now()->format('d/m/Y H:i:s');

            // 1. Dispatch In-App Notification Feed
            NotificationFeed::create([
                'work_order_id' => $workOrder->id,
                'client_id' => $workOrder->vendor_id,
                'target_role' => 'ALL',
                'category' => 'SPK_SUBMITTED',
                'title' => "Pengajuan Review: {$workOrder->spk_number}",
                'message' => "Tim lapangan telah selesai mengerjakan {$workOrder->location_name} dan mengajukan review.",
            ]);

            // 2. Dispatch WhatsApp Notification
            $adminPhones = self::getAdminSupervisorPhones();
            if (empty($adminPhones)) {
                return;
            }

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

            $baNumber = $baDocument->ba_number ?? "BA-{$workOrder->spk_number}";
            $clientName = $workOrder->vendor?->name ?? 'Client';
            $waktu = now()->format('d/m/Y H:i:s');

            // 1. Dispatch In-App Notification Feed
            NotificationFeed::create([
                'work_order_id' => $workOrder->id,
                'client_id' => $workOrder->vendor_id,
                'target_role' => 'ALL',
                'category' => 'BA_ISSUED',
                'title' => "BA Opname Terbit: {$baNumber}",
                'message' => "Pekerjaan {$workOrder->spk_number} ({$workOrder->location_name}) telah disetujui & BA resmi diterbitkan.",
            ]);

            // 2. Dispatch WhatsApp Notification
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

    /**
     * 7. Trigger custom alert / Addendum Notification
     */
    public static function onCustomAlert(WorkOrder $workOrder, string $message): void
    {
        try {
            $workOrder->loadMissing(['pic', 'vendor']);

            // 1. Dispatch In-App Notification Feed
            NotificationFeed::create([
                'work_order_id' => $workOrder->id,
                'client_id' => $workOrder->vendor_id,
                'target_user_id' => $workOrder->pic_user_id,
                'target_role' => 'ALL',
                'category' => 'CUSTOM_ALERT',
                'title' => "Update SPK: {$workOrder->spk_number}",
                'message' => $message,
            ]);

            // 2. Dispatch WhatsApp Notification to PIC and Admins
            $recipients = [];
            if ($workOrder->pic?->phone) {
                $recipients[] = $workOrder->pic->phone;
            }
            $adminPhones = self::getAdminSupervisorPhones();
            $recipients = array_unique(array_filter(array_merge($recipients, $adminPhones)));

            if (empty($recipients)) {
                return;
            }

            foreach ($recipients as $phone) {
                FonnteService::sendMessage($phone, $message, 'CUSTOM_ALERT');
            }
        } catch (\Throwable $e) {
            Log::error('WA onCustomAlert failed: ' . $e->getMessage());
        }
    }
}
