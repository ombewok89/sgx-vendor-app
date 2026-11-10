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
     * Resolves the active WhatsApp Gateway provider.
     * Options: 'WAGATE' (wa.printpro.my.id) | 'FONNTE' (api.fonnte.com)
     * Default: 'WAGATE'
     */
    public static function getProvider(): string
    {
        try {
            $p = SystemSetting::whereIn('key', ['wa_provider', 'WA_PROVIDER'])->value('value');
            if (!empty($p)) {
                return strtoupper(trim((string)$p));
            }
        } catch (\Throwable $e) {
            // Fallback
        }

        return strtoupper((string)env('WA_PROVIDER', 'WAGATE'));
    }

    /**
     * Resolves WaGate Product Key / API Key.
     */
    public static function getWagateApiKey(): ?string
    {
        try {
            $key = SystemSetting::whereIn('key', ['wagate_api_key', 'WAGATE_API_KEY'])->value('value');
            if (!empty($key)) {
                return trim((string)$key);
            }
        } catch (\Throwable $e) {}

        $k = env('WAGATE_API_KEY', 'wg_e187053c1c9b48df991d8a0491e01cfa');
        return !empty($k) ? trim((string)$k) : null;
    }

    /**
     * Resolves WaGate Device ID.
     */
    public static function getWagateDeviceId(): ?string
    {
        try {
            $dev = SystemSetting::whereIn('key', ['wagate_device_id', 'WAGATE_DEVICE_ID'])->value('value');
            if (!empty($dev)) {
                return trim((string)$dev);
            }
        } catch (\Throwable $e) {}

        $d = env('WAGATE_DEVICE_ID', '68865734-16ca-42f7-bb59-282b59756898');
        return !empty($d) ? trim((string)$d) : null;
    }

    /**
     * Resolves WaGate API Endpoint URL.
     */
    public static function getWagateApiUrl(): string
    {
        try {
            $url = SystemSetting::whereIn('key', ['wagate_api_url', 'WAGATE_API_URL'])->value('value');
            if (!empty($url)) {
                return trim((string)$url);
            }
        } catch (\Throwable $e) {}

        return (string)env('WAGATE_API_URL', 'https://wa.printpro.my.id/api/v1/messages/send');
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
     * Checks if WhatsApp Gateway master power switch is enabled.
     */
    public static function isGatewayEnabled(): bool
    {
        try {
            $val = SystemSetting::whereIn('key', ['wa_gateway_enabled', 'wa_enabled', 'WA_ENABLED', 'fonnte_enabled'])->value('value');
            if ($val !== null) {
                return (string)$val === '1' || strtolower((string)$val) === 'true';
            }
        } catch (\Throwable $e) {
            // Fallback to true if DB error
        }

        return true;
    }

    /**
     * Renders Spintax syntax {Option 1|Option 2|Option 3} into randomized human-like variations.
     */
    public static function renderSpintax(string $text): string
    {
        return preg_replace_callback('/\{([^{}]+)\}/', function ($matches) {
            $options = explode('|', $matches[1]);
            return trim($options[array_rand($options)]);
        }, $text);
    }

    /**
     * Generates a dynamic greeting based on current server time in WIB (Western Indonesian Time).
     */
    public static function getTimeGreeting(string $personName = ''): string
    {
        $hour = (int)date('H');
        if ($hour >= 4 && $hour < 11) {
            $greetings = ['Selamat Pagi', 'Pagi', 'Salam Pagi', 'Semangat Pagi'];
        } elseif ($hour >= 11 && $hour < 15) {
            $greetings = ['Selamat Siang', 'Siang', 'Salam Siang'];
        } elseif ($hour >= 15 && $hour < 18) {
            $greetings = ['Selamat Sore', 'Sore', 'Salam Sore'];
        } else {
            $greetings = ['Selamat Malam', 'Malam', 'Salam'];
        }

        $greeting = $greetings[array_rand($greetings)];
        if (!empty(trim($personName))) {
            return "{$greeting}, *{$personName}*";
        }
        return $greeting;
    }

    /**
     * Appends an invisible-to-low-profile unique timestamp & reference signature to prevent spam hash duplicates.
     */
    public static function appendAntiSpamSignature(string $text, ?string $referenceId = null): string
    {
        // Don't append if already has a reference signature
        if (str_contains($text, '_Ref: SGX-')) {
            return $text;
        }

        $ref = !empty($referenceId) ? $referenceId : strtoupper(substr(md5(uniqid('', true)), 0, 6));
        $timestamp = date('d/m/Y H:i:s') . ' WIB';
        
        return rtrim($text) . "\n\n_Ref: SGX-#{$ref} • {$timestamp}_";
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

        // 0. Check if Master Gateway Power is turned OFF by admin (Except for direct manual test button)
        if (!self::isGatewayEnabled() && !in_array(strtoupper($messageType), ['TEST_MESSAGE', 'TEST'])) {
            self::recordLog(
                $notificationFeedId,
                $normalizedPhone,
                $messageType,
                $message,
                'SKIPPED',
                null,
                'WhatsApp Gateway dinonaktifkan oleh administrator (Power Switch: OFF)',
                ['disabled' => true]
            );

            return [
                'success' => true,
                'skipped' => true,
                'status' => 'SKIPPED',
                'message' => 'Pengiriman WhatsApp dilewati karena WhatsApp Gateway sedang dinonaktifkan (OFF).'
            ];
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

        $provider = self::getProvider();

        // =========================================================================
        // PROVIDER 1: WaGate SaaS (wa.printpro.my.id)
        // =========================================================================
        if ($provider === 'WAGATE') {
            $apiKey = self::getWagateApiKey();
            $deviceId = self::getWagateDeviceId();
            $apiUrl = self::getWagateApiUrl();

            if (empty($apiKey) || empty($deviceId)) {
                $err = 'WaGate Product Key atau Device ID belum dikonfigurasi di pengaturan sistem / .env';
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
                    'provider' => 'WAGATE',
                    'status' => 'FAILED',
                    'error' => $err,
                    'message' => 'Gagal mengirim pesan: ' . $err
                ];
            }

            try {
                $payload = [
                    'deviceId' => $deviceId,
                    'receiver' => $normalizedPhone,
                    'message'  => $message,
                ];

                $response = Http::withHeaders([
                    'x-api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->timeout(20)->post($apiUrl, $payload);

                $body = $response->json();
                if (!$body && $response->body()) {
                    $body = ['raw' => $response->body()];
                }

                if (!$response->successful() || (isset($body['success']) && $body['success'] === false)) {
                    $reason = $body['message'] ?? $body['error'] ?? ('HTTP Error ' . $response->status());
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
                        'provider' => 'WAGATE',
                        'status' => 'FAILED',
                        'error' => $reason,
                        'data' => $body,
                        'message' => 'WaGate Error: ' . $reason
                    ];
                }

                $wagateId = $body['id'] ?? ('WAGATE-' . uniqid());

                self::recordLog(
                    $notificationFeedId,
                    $normalizedPhone,
                    $messageType,
                    $message,
                    'SENT',
                    $wagateId,
                    null,
                    $body
                );

                return [
                    'success' => true,
                    'mock' => false,
                    'provider' => 'WAGATE',
                    'status' => 'SENT',
                    'response_id' => $wagateId,
                    'data' => $body,
                    'message' => 'Pesan WhatsApp berhasil dikirim via WaGate (' . ($body['message'] ?? 'Antrean Terjadwal') . ').'
                ];
            } catch (\Throwable $e) {
                $err = 'Koneksi ke gateway WaGate gagal: ' . $e->getMessage();
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
                    'provider' => 'WAGATE',
                    'status' => 'FAILED',
                    'error' => $err,
                    'message' => $err
                ];
            }
        }

        // =========================================================================
        // PROVIDER 2: Fonnte API (api.fonnte.com)
        // =========================================================================
        $token = self::getToken();
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
                'provider' => 'FONNTE',
                'status' => 'FAILED',
                'error' => $err,
                'message' => 'Gagal mengirim pesan: ' . $err
            ];
        }

        // Live Fonnte API Dispatch with Natural Delay (Anti-Ban Safe Guard)
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
        $provider = self::getProvider();
        $isMock = self::isMockEnabled();
        $isEnabled = self::isGatewayEnabled();

        $token = self::getToken();
        $wagateKey = self::getWagateApiKey();
        $wagateDevice = self::getWagateDeviceId();
        $wagateUrl = self::getWagateApiUrl();

        $isConfigured = false;
        if ($provider === 'WAGATE') {
            $isConfigured = !empty($wagateKey) && !empty($wagateDevice);
        } else {
            $isConfigured = !empty($token);
        }

        $state = 'UNCONFIGURED';
        if (!$isEnabled) {
            $state = 'DISABLED';
        } elseif ($isMock) {
            $state = 'MOCK';
        } elseif ($isConfigured) {
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
        if ($provider === 'WAGATE') {
            if (!empty($wagateKey)) {
                $len = strlen($wagateKey);
                $maskedToken = ($len > 8) ? substr($wagateKey, 0, 5) . '••••' . substr($wagateKey, -4) : '••••••••';
            }
        } else {
            if (!empty($token)) {
                $len = strlen($token);
                $maskedToken = ($len > 8) ? substr($token, 0, 4) . '••••' . substr($token, -4) : '••••••••';
            }
        }

        return [
            'state' => $state, // 'ACTIVE' | 'DISABLED' | 'MOCK' | 'UNCONFIGURED'
            'provider' => $provider, // 'WAGATE' | 'FONNTE'
            'is_enabled' => $isEnabled,
            'token_configured' => $isConfigured,
            'masked_token' => $maskedToken,
            'wagate_device_id' => $wagateDevice,
            'wagate_api_url' => $wagateUrl,
            'mock_enabled' => $isMock,
            'total_sent' => $totalSent,
            'total_failed' => $totalFailed,
            'total_logs' => $totalLogs,
            'success_rate' => $rate,
            'has_logs' => $totalLogs > 0,
        ];
    }
}
