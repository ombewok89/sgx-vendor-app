<?php

namespace App\Services;

use App\Jobs\SendWhatsAppNotificationJob;
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
     * Helper to queue messages with human-like randomized spacing and spintax.
     */
    private static function queueWhatsAppBatch(array $recipients, string $spintaxTemplate, string $eventType, ?int $workOrderId = null): void
    {
        $recipients = array_unique(array_filter($recipients));
        if (empty($recipients)) {
            return;
        }

        $cumulativeDelay = 0;
        foreach ($recipients as $phone) {
            // 1. Render dynamic Spintax for this specific recipient
            $renderedText = FonnteService::renderSpintax($spintaxTemplate);

            // 2. Dispatch to Laravel Queue with staggered delay (5-12 seconds interval)
            SendWhatsAppNotificationJob::dispatch(
                $phone,
                $renderedText,
                $eventType,
                ['work_order_id' => $workOrderId],
                $workOrderId,
                rand(1, 4) // Jitter
            )->delay(now()->addSeconds($cumulativeDelay));

            // Increment delay for the next recipient to prevent concurrent burst floods
            $cumulativeDelay += rand(5, 12);
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

            // 2. Prepare WhatsApp Recipients
            $recipients = [];
            if ($workOrder->pic?->phone) {
                $recipients[] = $workOrder->pic->phone;
            }
            foreach ($workOrder->assignments as $assignee) {
                if (!empty($assignee->phone)) {
                    $recipients[] = $assignee->phone;
                }
            }

            $picName = $workOrder->pic?->name ?? 'Tim Lapangan';
            $clientName = $workOrder->vendor?->name ?? 'Client';
            $areaName = $workOrder->area?->name ?? '-';
            $deadline = $workOrder->deadline ? date('d/m/Y', strtotime($workOrder->deadline)) : '-';
            $greeting = FonnteService::getTimeGreeting($picName);

            $spintax = "{📋 *PENUGASAN SPK BARU*|📌 *ORDER KERJA BARU*|📝 *INFORMASI PENUGASAN SPK*} — *PT SINAR KREASINDO BENCOOLEN*\n\n"
                     . "{$greeting}, Anda telah ditugaskan untuk pengerjaan:\n"
                     . "• *No. SPK:* {$workOrder->spk_number}\n"
                     . "• *Judul:* {$workOrder->title}\n"
                     . "• *Lokasi:* {$workOrder->location_name} ({$areaName})\n"
                     . "• *Klien:* {$clientName}\n"
                     . "• *Target SLA:* {$deadline}\n\n"
                     . "{Mohon segera lakukan Check-In GPS di lokasi cabang dan unggah foto dokumentasi (Before/Process/After).|Harap menuju lokasi cabang, lakukan Presensi Check-In GPS dan ambil foto evidensi fisik.|Silakan lakukan verifikasi lokasi via Check-In GPS serta upload dokumentasi pengerjaan.}\n"
                     . "🔗 *Aplikasi:* https://vendor.sinargrafika.my.id/";

            self::queueWhatsAppBatch($recipients, $spintax, 'SPK_ASSIGNED', $workOrder->id);
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
            $greeting = FonnteService::getTimeGreeting();

            $spintax = "{📍 *PRESENSI GPS CHECK-IN LAPANGAN*|📌 *KONFIRMASI KEHADIRAN CABANG*|🛰️ *LOG PRESENSI GPS TEKNISI*}\n\n"
                     . "{$greeting}, berikut update kehadiran teknisi di lokasi:\n"
                     . "• *No. SPK:* {$workOrder->spk_number}\n"
                     . "• *Lokasi Toko:* {$workOrder->location_name}\n"
                     . "• *Teknisi:* {$userName}{$userPhone}\n"
                     . "• *Waktu:* {$waktu} WIB\n"
                     . "• *Koordinat:* {$lat}, {$lng} (Akurasi: ±{$accuracy}m)\n\n"
                     . "{Teknisi telah terkonfirmasi berada di radius lokasi cabang pekerjaan.|Presensi lokasi telah tervalidasi via satelit GPS.|Tim lapangan telah tiba di lokasi dan siap memulai pengerjaan.}";

            self::queueWhatsAppBatch($adminPhones, $spintax, 'GPS_CHECKIN', $workOrder->id);
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
            $greeting = FonnteService::getTimeGreeting();

            $spintax = "{📷 *PROGRES DOKUMENTASI FOTO LAPANGAN*|📸 *UPDATE EVIDENSI FISIK TOKO*|🖼️ *LOG UPLOAD FOTO PROYEK*}\n\n"
                     . "{$greeting}, terdapat pembaruan foto bukti pekerjaan:\n"
                     . "• *No. SPK:* {$workOrder->spk_number}\n"
                     . "• *Lokasi Toko:* {$workOrder->location_name}\n"
                     . "• *Tahap Terkini:* {$stage}\n"
                     . "• *Teknisi:* {$userName}\n"
                     . "• *Waktu:* {$waktu} WIB\n\n"
                     . "{Tim lapangan sedang aktif mendokumentasikan progres foto di lokasi pekerjaan.|Evidensi visual baru telah masuk dan tersimpan pada sistem.|Dokumentasi fisik telah diperbarui oleh tim teknisi.}";

            self::queueWhatsAppBatch($adminPhones, $spintax, 'EVIDENCE_UPLOAD', $workOrder->id);
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
            $greeting = FonnteService::getTimeGreeting();

            $spintax = "{⚠️ *LAPORAN KENDALA LAPANGAN (URGENT)*|🚨 *PEMBERITAHUAN KENDALA TEKNIS*|⚡ *LAPORAN MASALAH LAPANGAN*}\n\n"
                     . "{$greeting}, tim lapangan melaporkan adanya kendala kerja:\n"
                     . "• *No. SPK:* {$workOrder->spk_number}\n"
                     . "• *Lokasi Toko:* {$workOrder->location_name}\n"
                     . "• *Kategori Kendala:* {$category}\n"
                     . "• *Pelapor:* {$userName}\n"
                     . "• *Waktu:* {$waktu} WIB\n"
                     . "• *Rincian Kendala:* {$desc}\n\n"
                     . "{Mohon tindak lanjut dan koordinasi segera dengan tim lapangan terkait.|Harap segera dilakukan evaluasi dan tindak lanjut penanganan.|Pemberitahuan ini membutuhkan perhatian tim pengawas/admin.}";

            self::queueWhatsAppBatch($adminPhones, $spintax, 'ISSUE_REPORTED', $workOrder->id);
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
            $greeting = FonnteService::getTimeGreeting();

            $spintax = "{✅ *PENGAJUAN REVIEW HASIL PEKERJAAN*|🏁 *PEKERJAAN LAPANGAN SELESAI (SIAP REVIEW)*|📋 *PENGAJUAN APPROVAL SPK*}\n\n"
                     . "{$greeting}, tim lapangan telah menyelesaikan pekerjaan fisik:\n"
                     . "• *No. SPK:* {$workOrder->spk_number}\n"
                     . "• *Judul:* {$workOrder->title}\n"
                     . "• *Lokasi:* {$workOrder->location_name}\n"
                     . "• *Klien:* {$clientName}\n"
                     . "• *Diajukan Oleh:* {$userName}\n"
                     . "• *Total Foto Bukti:* {$totalPhotos} foto\n"
                     . "• *Waktu:* {$waktu} WIB\n\n"
                     . "{Tim lapangan telah menyelesaikan seluruh pekerjaan fisik dan mengajukan SPK untuk direview.|Seluruh evidensi telah lengkap dan menunggu approval pengawas.|Silakan lakukan peninjauan hasil kerja untuk penerbitan Berita Acara.}\n"
                     . "🔗 *Buka Review:* https://vendor.sinargrafika.my.id/";

            self::queueWhatsAppBatch($adminPhones, $spintax, 'SPK_SUBMITTED', $workOrder->id);
        } catch (\Throwable $e) {
            Log::error('WA onSpkSubmitted failed: ' . $e->getMessage());
        }
    }

    /**
     * 6. Trigger when Work Order is requested for revision by supervisor
     */
    public static function onRevisionRequested(WorkOrder $workOrder, string $reason, string $targetStage = 'ALL'): void
    {
        try {
            $workOrder->loadMissing(['pic', 'vendor']);

            // 1. Dispatch In-App Notification Feed
            NotificationFeed::create([
                'work_order_id' => $workOrder->id,
                'client_id' => $workOrder->vendor_id,
                'target_user_id' => $workOrder->pic_user_id,
                'target_role' => 'ALL',
                'category' => 'REVISION_REQUESTED',
                'title' => "Permintaan Revisi: {$workOrder->spk_number}",
                'message' => "Pekerjaan {$workOrder->location_name} memerlukan revisi/penyempurnaan mutu: {$reason}",
            ]);

            // 2. Dispatch WhatsApp to Field PIC
            $recipients = [];
            if ($workOrder->pic?->phone) {
                $recipients[] = $workOrder->pic->phone;
            }

            $picName = $workOrder->pic?->name ?? 'Tim Lapangan';
            $greeting = FonnteService::getTimeGreeting($picName);

            $spintax = "{🔄 *PERMINTAAN REVISI / PENYEMPURNAAN MUTU*|⚠️ *CATATAN KONTROL MUTU PEKERJAAN*|🛠️ *PENYESUAIAN TEKNIS SPK*}\n\n"
                     . "{$greeting}, hasil pengerjaan SPK berikut memerlukan penyempurnaan:\n"
                     . "• *No. SPK:* {$workOrder->spk_number}\n"
                     . "• *Lokasi:* {$workOrder->location_name}\n"
                     . "• *Tahap Target:* {$targetStage}\n"
                     . "• *Catatan Pengawas:* {$reason}\n\n"
                     . "{Mohon segera lakukan perbaikan fisik di lokasi dan unggah foto dokumentasi terbaru.|Harap lengkapi evidensi sesuai arahan pengawas di atas.|Silakan perbaiki kekurangan pekerjaan dan submit ulang untuk diverifikasi.}\n"
                     . "🔗 *Aplikasi:* https://vendor.sinargrafika.my.id/";

            self::queueWhatsAppBatch($recipients, $spintax, 'REVISION_REQUESTED', $workOrder->id);
        } catch (\Throwable $e) {
            Log::error('WA onRevisionRequested failed: ' . $e->getMessage());
        }
    }

    /**
     * 7. Trigger when Berita Acara (BA) Opname is approved / issued
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

            $greeting = FonnteService::getTimeGreeting();

            $spintax = "{📄 *BERITA ACARA (BA) OPNAME RESMI TERBIT*|🏆 *PENGESAHAN DOKUMEN BERITA ACARA*|📜 *BA OPNAME SELESAI & SAH*}\n\n"
                     . "{$greeting}, Berita Acara digital telah diterbitkan resmi:\n"
                     . "• *No. BA:* {$baNumber}\n"
                     . "• *No. SPK:* {$workOrder->spk_number}\n"
                     . "• *Pekerjaan:* {$workOrder->title}\n"
                     . "• *Lokasi Cabang:* {$workOrder->location_name}\n"
                     . "• *Klien:* {$clientName}\n"
                     . "• *Waktu Terbit:* {$waktu} WIB\n\n"
                     . "{Pekerjaan telah disetujui 100% dan Berita Acara (BA) digital resmi telah diterbitkan.|Seluruh hasil kerja fisik telah tervalidasi dan sah secara administratif.|Dokumen BA resmi ber-QR Code siap diunduh untuk kelancaran penagihan.}\n"
                     . "🔗 *Lihat Dokumen BA:* https://vendor.sinargrafika.my.id/";

            self::queueWhatsAppBatch($recipients, $spintax, 'BA_ISSUED', $workOrder->id);
        } catch (\Throwable $e) {
            Log::error('WA onBaIssued failed: ' . $e->getMessage());
        }
    }

    /**
     * 8. Trigger custom alert / Addendum Notification
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

            self::queueWhatsAppBatch($recipients, $message, 'CUSTOM_ALERT', $workOrder->id);
        } catch (\Throwable $e) {
            Log::error('WA onCustomAlert failed: ' . $e->getMessage());
        }
    }
}
