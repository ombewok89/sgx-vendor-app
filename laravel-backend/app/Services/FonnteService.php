<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    /**
     * Normalizes phone number into standard 628xxxxxxxxxx format.
     * Handles:
     * - "081234567890"  -> "6281234567890"
     * - "+6281234567890" -> "6281234567890"
     * - "6281234567890"  -> "6281234567890"
     * - "81234567890"    -> "6281234567890"
     */
    public static function normalizePhoneNumber(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Strip non-numeric characters
        $clean = preg_replace('/[^0-9]/', '', (string)$phone);

        if (str_starts_with($clean, '08')) {
            return '628' . substr($clean, 2);
        } elseif (str_starts_with($clean, '628')) {
            return $clean;
        } elseif (str_starts_with($clean, '8')) {
            return '62' . $clean;
        }

        return $clean;
    }

    /**
     * Resolves the active Fonnte API Token.
     * Single Source of Truth Hierarchy:
     * 1. Primary: Database `system_settings.fonnte_api_key` (Runtime UI configurable)
     * 2. Fallback: `config('services.fonnte.token')` or `env('FONNTE_TOKEN')`
     */
    public static function getToken(): ?string
    {
        $dbToken = null;
        try {
            $dbToken = SystemSetting::whereIn('key', ['fonnte_api_key', 'FONNTE_API_KEY', 'fonnte_token', 'FONNTE_TOKEN'])->value('value');
        } catch (\Throwable $e) {
            // Fallback during migrations/unit tests without DB
        }

        $token = !empty($dbToken) ? trim((string)$dbToken) : (string)config('services.fonnte.token', env('FONNTE_TOKEN', env('FONNTE_API_KEY', '')));
        $token = trim($token, " \t\n\r\0\x0B\"'");
        
        // Ignore generic placeholder strings
        if (in_array($token, ['FONNTE_DEMO_KEY_SGX_2026', 'mock-token', 'YOUR_FONNTE_TOKEN_HERE', ''])) {
            return null;
        }

        return $token;
    }

    /**
     * Checks if a specific notification event is enabled in system settings.
     */
    public static function isEventEnabled(string $messageType): bool
    {
        // Custom or direct manual test messages are always allowed
        if (in_array(strtoupper($messageType), ['CUSTOM', 'TEST_MESSAGE', 'TEST', ''])) {
            return true;
        }

        $settingMap = [
            'SPK_CREATED'      => 'wa_notify_spk_assigned',
            'SPK_ASSIGNED'     => 'wa_notify_spk_assigned',
            'GPS_CHECKIN'      => 'wa_notify_gps_checkin',
            'EVIDENCE_UPLOAD'  => 'wa_notify_evidence_upload',
            'ISSUE_REPORTED'   => 'wa_notify_issue_reported',
            'SPK_SUBMITTED'    => 'wa_notify_spk_submitted',
            'BA_ISSUED'        => 'wa_notify_ba_issued',
            'BA_APPROVED'      => 'wa_notify_ba_issued',
        ];

        $key = $settingMap[strtoupper($messageType)] ?? null;
        if (!$key) {
            return true;
        }

        try {
            $val = SystemSetting::where('key', $key)->value('value');
            if ($val !== null) {
                return (string)$val === '1' || strtolower((string)$val) === 'true';
            }
        } catch (\Throwable $e) {
            // Default to true if DB error
        }

        return true;
    }

    /**
     * Checks if mock simulation mode is explicitly enabled.
     */
    public static function isMockEnabled(): bool
    {
        return (bool)config('services.fonnte.mock_enabled', false);
    }

    /**
     * Sends a WhatsApp message via Fonnte Gateway with honest delivery accounting.
     *
     * @param string $phone Target phone number
     * @param string $message Text message body
     * @param string $messageType Event classification (CUSTOM, SPK_CREATED, etc.)
     * @param int|null $notificationFeedId Optional relation to notifications_feed
     * @return array Result summary with status, success boolean, and message
     */
    public static function sendMessage(string $phone, string $message, string $messageType = 'CUSTOM', ?int $notificationFeedId = null): array
    {
        $normalizedPhone = self::normalizePhoneNumber($phone);

        if (empty($normalizedPhone)) {
            $err = 'Nomor telepon tujuan tidak valid.';
            self::recordLog($notificationFeedId, $phone, $messageType, $message, 'FAILED', null, $err, null);
            return ['success' => false, 'error' => $err, 'status' => 'FAILED'];
        }

        // Check if event notification is disabled by admin settings
        if (!self::isEventEnabled($messageType)) {
            self::recordLog(
                $notificationFeedId,
                $normalizedPhone,
                $messageType,
                $message,
                'SKIPPED',
                null,
                'Notifikasi dinonaktifkan di pengaturan sistem',
                ['skipped' => true, 'event' => $messageType]
            );

            return [
                'success' => true,
                'skipped' => true,
                'status' => 'SKIPPED',
                'message' => 'Pengiriman WhatsApp dilewati karena notifikasi event ini dinonaktifkan di pengaturan sistem.'
            ];
        }

        $isMock = self::isMockEnabled();
        $token = self::getToken();

        // 1. Explicit Mock Simulation Mode (when enabled)
        if ($isMock) {
            $mockId = 'MOCK-' . strtoupper(uniqid());
            self::recordLog(
                $notificationFeedId,
                $normalizedPhone,
                $messageType,
                $message,
                'SENT',
                $mockId,
                null,
                ['mock' => true, 'timestamp' => now()->toIso8601String()]
            );

            return [
                'success' => true,
                'mock' => true,
                'status' => 'SENT',
                'response_id' => $mockId,
                'message' => 'Pesan berhasil disimulasikan via Mock Mode (Testing).'
            ];
        }

        // 2. Unconfigured / Missing Token -> EXPLICIT FAILURE (No silent mock)
        if (empty($token)) {
            $err = 'FONNTE_TOKEN belum dikonfigurasi di dashboard maupun .env';
            self::recordLog(
                $notificationFeedId,
                $normalizedPhone,
                $messageType,
                $message,
                'FAILED',
                null,
                $err,
                null
            );

            return [
                'success' => false,
                'mock' => false,
                'status' => 'FAILED',
                'error' => $err,
                'message' => 'Gagal mengirim pesan: ' . $err
            ];
        }

        // Anti-Flood / Deduplication Check (Max 1 duplicate message per phone every 60 seconds)
        if (!in_array($messageType, ['TEST_MESSAGE', 'TEST', 'CUSTOM'])) {
            $cacheKey = 'wa_flood_' . md5($normalizedPhone . '_' . $messageType . '_' . substr($message, 0, 40));
            if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                return [
                    'success' => true,
                    'skipped' => true,
                    'status' => 'SKIPPED',
                    'message' => 'Pengiriman dilewati untuk mencegah spam berulang dalam waktu singkat (Anti-Flood Guard).'
                ];
            }
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addSeconds(60));
        }

        // 3. Live Fonnte API Dispatch with Natural Delay (Anti-Ban Safe Guard)
        try {
            $url = config('services.fonnte.url', 'https://api.fonnte.com/send');
            $randomDelaySeconds = rand(4, 9); // Natural jitter delay

            $payload = [
                'target' => $normalizedPhone,
                'message' => $message,
                'countryCode' => '62',
                'delay' => (string)$randomDelaySeconds,
                'schedule' => '0',
            ];

            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->timeout(20)->post($url, $payload);

            $body = $response->json();
            if (!$body && $response->body()) {
                $body = ['raw' => $response->body()];
            }

            // Check if Fonnte returned status: false (e.g. unknown token, device disconnected)
            if (isset($body['status']) && $body['status'] === false) {
                $reason = $body['reason'] ?? 'Gagal memproses pesan di server Fonnte';
                self::recordLog(
                    $notificationFeedId,
                    $normalizedPhone,
                    $messageType,
                    $message,
                    'FAILED',
                    null,
                    $reason,
                    $body
                );

                return [
                    'success' => false,
                    'mock' => false,
                    'status' => 'FAILED',
                    'error' => $reason,
                    'data' => $body,
                    'message' => 'Fonnte Gateway Error: ' . $reason
                ];
            }

            // Successful dispatch
            $fonnteId = null;
            if (!empty($body['id']) && is_array($body['id'])) {
                $fonnteId = (string)$body['id'][0];
            } elseif (!empty($body['requestid'])) {
                $fonnteId = (string)$body['requestid'];
            } else {
                $fonnteId = 'FONNTE-' . uniqid();
            }

            self::recordLog(
                $notificationFeedId,
                $normalizedPhone,
                $messageType,
                $message,
                'SENT',
                $fonnteId,
                null,
                $body
            );

            return [
                'success' => true,
                'mock' => false,
                'status' => 'SENT',
                'response_id' => $fonnteId,
                'data' => $body,
                'message' => 'Pesan WhatsApp berhasil dikirim ke antrean Fonnte (' . ($body['detail'] ?? 'OK') . ').'
            ];
        } catch (\Throwable $e) {
            $err = 'Koneksi ke gateway Fonnte gagal: ' . $e->getMessage();
            self::recordLog(
                $notificationFeedId,
                $normalizedPhone,
                $messageType,
                $message,
                'FAILED',
                null,
                $err,
                null
            );

            return [
                'success' => false,
                'mock' => false,
                'status' => 'FAILED',
                'error' => $err,
                'message' => $err
            ];
        }
    }

    /**
     * Helper to safely persist records in notification_logs table.
     */
    private static function recordLog(
        ?int $feedId,
        string $recipient,
        string $type,
        string $message,
        string $status,
        ?string $fonnteId,
        ?string $error,
        ?array $payload
    ): ?NotificationLog {
        try {
            return NotificationLog::create([
                'notification_feed_id' => $feedId,
                'recipient' => $recipient,
                'message_type' => $type,
                'message' => $message,
                'status' => $status,
                'fonnte_response_id' => $fonnteId,
                'error_message' => $error,
                'payload' => $payload,
                'sent_at' => $status === 'SENT' ? now() : null,
            ]);
        } catch (\Throwable $e) {
            Log::error('[FonnteService] Failed to record notification_log: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Computes real-time gateway health and transmission rate for dashboards.
     */
    public static function getGatewayStatus(): array
    {
        $token = self::getToken();
        $isMock = self::isMockEnabled();

        $state = 'UNCONFIGURED';
        if ($isMock) {
            $state = 'MOCK';
        } elseif (!empty($token)) {
            $state = 'ACTIVE';
        }

        $totalSent = 0;
        $totalFailed = 0;
        $totalLogs = 0;
        $rate = 0.0;

        try {
            $totalSent = NotificationLog::where('status', 'SENT')->count();
            $totalFailed = NotificationLog::where('status', 'FAILED')->count();
            $totalLogs = $totalSent + $totalFailed;

            if ($totalLogs > 0) {
                $rate = round(($totalSent / $totalLogs) * 100, 1);
            }
        } catch (\Throwable $e) {
            // Ignore if table not yet migrated
        }

        $maskedToken = null;
        if (!empty($token)) {
            $len = strlen($token);
            if ($len > 8) {
                $maskedToken = substr($token, 0, 4) . '••••' . substr($token, -4);
            } else {
                $maskedToken = '••••••••';
            }
        }

        return [
            'state' => $state, // 'ACTIVE' | 'MOCK' | 'UNCONFIGURED'
            'token_configured' => !empty($token),
            'masked_token' => $maskedToken,
            'mock_enabled' => $isMock,
            'total_sent' => $totalSent,
            'total_failed' => $totalFailed,
            'total_logs' => $totalLogs,
            'success_rate' => $rate,
            'has_logs' => $totalLogs > 0,
        ];
    }
}
